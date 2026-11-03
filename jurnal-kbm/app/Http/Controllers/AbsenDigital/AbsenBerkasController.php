<?php

namespace App\Http\Controllers\AbsenDigital;

use App\Http\Controllers\Controller;
use App\Models\AbsenFoto;
use App\Models\AbsenKegiatan;
use App\Models\AbsenKehadiran;
use App\Support\AbsenDigitalFiles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsenBerkasController extends Controller
{
    public function show(string $jenis, int $id): StreamedResponse
    {
        [$path, $downloadName, $adminOnly] = match ($jenis) {
            'foto' => $this->kehadiranPath($id, 'foto_path', 'foto-kehadiran'),
            'ttd' => $this->kehadiranPath($id, 'ttd_path', 'tanda-tangan'),
            'dokumentasi' => $this->dokumentasiPath($id),
            'hasil' => $this->hasilPath($id),
            default => abort(404),
        };

        if ($adminOnly && Auth::user()?->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke berkas ini.');
        }

        AbsenDigitalFiles::assertOwnedPath($path);

        if (! Storage::disk(AbsenDigitalFiles::DISK)->exists($path)) {
            abort(404);
        }

        if ($jenis === 'hasil') {
            return Storage::disk(AbsenDigitalFiles::DISK)->download($path, $downloadName);
        }

        return Storage::disk(AbsenDigitalFiles::DISK)->response($path, $downloadName);
    }

    /** @return array{0: string, 1: string, 2: bool} */
    private function kehadiranPath(int $id, string $column, string $fallback): array
    {
        $row = AbsenKehadiran::query()->findOrFail($id);
        $path = $row->{$column};

        if (! is_string($path) || $path === '') {
            abort(404);
        }

        $user = Auth::user();
        $allowed = $user && (
            $user->role === 'admin'
            || (int) $row->user_id === (int) $user->id
            || (int) $row->dicatat_oleh === (int) $user->id
        );

        if (! $allowed) {
            abort(403, 'Anda tidak memiliki akses ke berkas ini.');
        }

        return [$path, $fallback.'-'.$row->id, false];
    }

    /** @return array{0: string, 1: string, 2: bool} */
    private function dokumentasiPath(int $id): array
    {
        $foto = AbsenFoto::query()->findOrFail($id);

        return [$foto->path, $foto->nama_asli ?: 'foto-dokumentasi-'.$foto->id, true];
    }

    /** @return array{0: string, 1: string, 2: bool} */
    private function hasilPath(int $id): array
    {
        $kegiatan = AbsenKegiatan::query()->findOrFail($id);

        if (! $kegiatan->hasil_file_path) {
            abort(404);
        }

        return [$kegiatan->hasil_file_path, $kegiatan->hasil_file_nama ?: 'hasil-rapat', true];
    }
}
