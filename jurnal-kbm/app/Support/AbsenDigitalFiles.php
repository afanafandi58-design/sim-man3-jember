<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbsenDigitalFiles
{
    public const DISK = 'local';

    public static function safeOriginalName(UploadedFile $file, string $fallback): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[^\p{L}\p{N}._ -]/u', '', $name) ?? '';
        $name = trim($name);

        if ($name === '' || $name === '.' || $name === '..') {
            return $fallback;
        }

        return mb_substr($name, 0, 150);
    }

    public static function storeUpload(UploadedFile $file, string $directory): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs(trim($directory, '/'), $name, self::DISK);
    }

    public static function storeSignature(string $dataUrl): string
    {
        $binary = self::decodeSignature($dataUrl);
        $path = 'absen-digital/ttd/'.Str::uuid()->toString().'.png';
        Storage::disk(self::DISK)->put($path, $binary);

        return $path;
    }

    public static function decodeSignature(string $dataUrl): string
    {
        if (! str_starts_with($dataUrl, 'data:image/png;base64,')) {
            throw ValidationException::withMessages([
                'ttd' => 'Tanda tangan harus digambar di layar.',
            ]);
        }

        $raw = base64_decode(substr($dataUrl, strlen('data:image/png;base64,')), true);

        if ($raw === false || strlen($raw) < 80 || strlen($raw) > 700_000 || ! str_starts_with($raw, "\x89PNG\r\n\x1a\n")) {
            throw ValidationException::withMessages([
                'ttd' => 'Tanda tangan tidak valid atau terlalu besar.',
            ]);
        }

        $image = @imagecreatefromstring($raw);

        if ($image === false) {
            throw ValidationException::withMessages([
                'ttd' => 'Tanda tangan tidak dapat dibaca.',
            ]);
        }

        $ink = 0;
        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y += 3) {
            for ($x = 0; $x < $width; $x += 3) {
                $color = imagecolorat($image, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;
                $alpha = ($color >> 24) & 0x7F;

                if ($alpha < 100 && $red < 90 && $green < 90 && $blue < 90) {
                    $ink++;
                }

                if ($ink >= 8) {
                    break 2;
                }
            }
        }

        imagedestroy($image);

        if ($ink < 8) {
            throw ValidationException::withMessages([
                'ttd' => 'Tanda tangan masih kosong. Gambar di kotak yang tersedia.',
            ]);
        }

        return $raw;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function assertOwnedPath(string $path): void
    {
        if (! str_starts_with($path, 'absen-digital/') || str_contains($path, '..')) {
            abort(404);
        }
    }
}
