<?php

namespace App\Services;

use App\Models\Jurnal;
use App\Models\JurnalAttachment;
use Carbon\Carbon;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class JurnalAttachmentService
{
    public const MAX_PHOTOS = 3;

    private const MAX_DIMENSION = 1280;

    private const THUMB_DIMENSION = 200;

    private const JPEG_QUALITY = 80;

    private const WEBP_QUALITY = 80;

    private const DISK = 'public';

    private const DIRECTORY = 'jurnal-foto';

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function assertCanModify(Jurnal $jurnal): void
    {
        if ($jurnal->isLocked()) {
            throw ValidationException::withMessages([
                'lampiran' => 'Jurnal sudah terkunci. Lampiran tidak dapat diubah.',
            ]);
        }
    }

    /**
     * @param  list<UploadedFile>  $files
     * @param  list<string|null>  $captions
     */
    public function storeMany(Jurnal $jurnal, array $files, array $captions = []): void
    {
        $this->assertCanModify($jurnal);

        $validFiles = array_values(array_filter(
            $files,
            fn ($file) => $file instanceof UploadedFile && $file->isValid()
        ));

        if ($validFiles === []) {
            return;
        }

        $this->assertWithinLimit($jurnal, count($validFiles));

        foreach ($validFiles as $index => $file) {
            $caption = $captions[$index] ?? null;
            $this->store($jurnal, $file, $caption);
        }
    }

    public function store(Jurnal $jurnal, UploadedFile $file, ?string $caption = null): JurnalAttachment
    {
        $this->assertCanModify($jurnal);
        $this->assertWithinLimit($jurnal, 1);
        $this->validateFile($file);

        $processed = $this->processImage($file);
        $extension = $processed['extension'];
        $baseName = $this->generateBaseName($jurnal);
        $fullName = $baseName.'.'.$extension;

        $relativePath = self::DIRECTORY.'/'.$jurnal->id.'/'.$fullName;

        Storage::disk(self::DISK)->put($relativePath, $processed['full']['binary']);
        Storage::disk(self::DISK)->put(
            JurnalAttachment::deriveThumbPath($relativePath),
            $processed['thumb']['binary']
        );

        return JurnalAttachment::create([
            'jurnal_id' => $jurnal->id,
            'nama_file' => $fullName,
            'caption' => $this->normalizeCaption($caption),
            'path_file' => $relativePath,
            'mime_type' => $processed['full']['mime'],
            'file_size' => $processed['full']['size'],
            'created_at' => now(),
        ]);
    }

    public function delete(JurnalAttachment $attachment): void
    {
        $attachment->loadMissing('jurnal');
        $this->assertCanModify($attachment->jurnal);

        $this->purgeStorageFiles($attachment);
        $attachment->delete();
    }

    public function purgeStorageFiles(JurnalAttachment $attachment): void
    {
        if (Storage::disk(self::DISK)->exists($attachment->path_file)) {
            Storage::disk(self::DISK)->delete($attachment->path_file);
        }

        $thumbPath = JurnalAttachment::deriveThumbPath($attachment->path_file);
        if (Storage::disk(self::DISK)->exists($thumbPath)) {
            Storage::disk(self::DISK)->delete($thumbPath);
        }
    }

    /**
     * @param  array<int|string, string|null>  $captions
     */
    public function updateCaptions(Jurnal $jurnal, array $captions): void
    {
        $this->assertCanModify($jurnal);

        foreach ($captions as $id => $caption) {
            $attachment = $jurnal->attachments()->find($id);
            if ($attachment) {
                $attachment->update([
                    'caption' => $this->normalizeCaption($caption),
                ]);
            }
        }
    }

    private function assertWithinLimit(Jurnal $jurnal, int $adding): void
    {
        $current = $jurnal->attachments()->count();

        if ($current + $adding > JurnalAttachment::MAX_PER_JURNAL) {
            $remaining = max(0, JurnalAttachment::MAX_PER_JURNAL - $current);

            throw ValidationException::withMessages([
                'lampiran' => $remaining > 0
                    ? "Maksimal 3 foto per jurnal. Sisa slot: {$remaining}."
                    : 'Maksimal 3 foto per jurnal sudah tercapai.',
            ]);
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'lampiran' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP.',
            ]);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'lampiran' => 'Ukuran file maksimal 2 MB.',
            ]);
        }
    }

    /**
     * Re-encode via GD strips EXIF metadata including GPS location.
     *
     * @return array{extension: string, full: array{binary: string, mime: string, size: int}, thumb: array{binary: string, mime: string, size: int}}
     */
    private function processImage(UploadedFile $file): array
    {
        if (! extension_loaded('gd')) {
            throw ValidationException::withMessages([
                'lampiran' => 'Server tidak mendukung pemrosesan gambar.',
            ]);
        }

        $source = $this->createImageResource($file);
        $width = imagesx($source);
        $height = imagesy($source);

        [$fullWidth, $fullHeight] = $this->scaledDimensions($width, $height, self::MAX_DIMENSION);
        $fullCanvas = $this->createCanvas($fullWidth, $fullHeight, $file);

        if (! imagecopyresampled($fullCanvas, $source, 0, 0, 0, 0, $fullWidth, $fullHeight, $width, $height)) {
            imagedestroy($source);
            imagedestroy($fullCanvas);
            throw ValidationException::withMessages(['lampiran' => 'Gagal mengubah ukuran gambar.']);
        }

        imagedestroy($source);

        [$thumbWidth, $thumbHeight] = $this->scaledDimensions($fullWidth, $fullHeight, self::THUMB_DIMENSION);
        $thumbCanvas = $this->createCanvas($thumbWidth, $thumbHeight, $file);

        if (! imagecopyresampled($thumbCanvas, $fullCanvas, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $fullWidth, $fullHeight)) {
            imagedestroy($fullCanvas);
            imagedestroy($thumbCanvas);
            throw ValidationException::withMessages(['lampiran' => 'Gagal membuat thumbnail.']);
        }

        $useWebp = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $mime = $useWebp ? 'image/webp' : 'image/jpeg';

        $fullBinary = $this->encodeCanvas($fullCanvas, $useWebp);
        $thumbBinary = $this->encodeCanvas($thumbCanvas, $useWebp);

        imagedestroy($fullCanvas);
        imagedestroy($thumbCanvas);

        return [
            'extension' => $extension,
            'full' => [
                'binary' => $fullBinary,
                'mime' => $mime,
                'size' => strlen($fullBinary),
            ],
            'thumb' => [
                'binary' => $thumbBinary,
                'mime' => $mime,
                'size' => strlen($thumbBinary),
            ],
        ];
    }

    private function encodeCanvas(GdImage $canvas, bool $useWebp): string
    {
        ob_start();

        if ($useWebp) {
            imagewebp($canvas, null, self::WEBP_QUALITY);
        } else {
            imagejpeg($canvas, null, self::JPEG_QUALITY);
        }

        return ob_get_clean() ?: '';
    }

    private function createImageResource(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw ValidationException::withMessages(['lampiran' => 'File tidak valid.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($image === false) {
            throw ValidationException::withMessages(['lampiran' => 'File gambar tidak dapat dibaca.']);
        }

        return $image;
    }

    private function createCanvas(int $width, int $height, UploadedFile $file): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            throw ValidationException::withMessages(['lampiran' => 'Gagal memproses gambar.']);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            if ($transparent !== false) {
                imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
            }
            imagealphablending($canvas, true);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            if ($white !== false) {
                imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
            }
        }

        return $canvas;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height, int $maxDimension): array
    {
        $longest = max($width, $height);

        if ($longest <= $maxDimension) {
            return [$width, $height];
        }

        $ratio = $maxDimension / $longest;

        return [
            (int) round($width * $ratio),
            (int) round($height * $ratio),
        ];
    }

    private function generateBaseName(Jurnal $jurnal): string
    {
        $seq = $jurnal->attachments()->count() + 1;
        $tanggal = Carbon::parse($jurnal->tanggal)->format('Ymd');

        return sprintf('JRN-%s-%06d-%02d', $tanggal, $jurnal->id, $seq);
    }

    /** @return list<string> */
    public static function captionFieldRules(): array
    {
        return [
            'nullable',
            'string',
            'max:100',
            'not_regex:/[<>]/',
            'not_regex:/javascript\s*:/i',
            'not_regex:/on\w+\s*=/i',
        ];
    }

    private function normalizeCaption(?string $caption): ?string
    {
        if ($caption === null) {
            return null;
        }

        $caption = trim(strip_tags($caption));
        $caption = preg_replace('/javascript\s*:/i', '', $caption) ?? '';
        $caption = preg_replace('/on\w+\s*=/i', '', $caption) ?? '';
        $caption = trim($caption);

        if ($caption === '') {
            return null;
        }

        return Str::limit($caption, 100, '');
    }
}
