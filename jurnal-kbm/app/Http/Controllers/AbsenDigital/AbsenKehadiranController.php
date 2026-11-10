<?php

namespace App\Http\Controllers\AbsenDigital;

use App\Http\Controllers\Controller;
use App\Models\AbsenKegiatan;
use App\Models\AbsenKehadiran;
use App\Support\AbsenDigitalFiles;
use App\Support\AbsenDigitalQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbsenKehadiranController extends Controller
{
    public function create()
    {
        $kegiatans = AbsenKegiatan::query()
            ->orderByDesc('tanggal')
            ->orderBy('nama')
            ->get();

        $peserta = AbsenDigitalQuery::peserta()->get(['id', 'name', 'role']);

        $riwayat = AbsenKehadiran::query()
            ->with('kegiatan')
            ->where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere('dicatat_oleh', Auth::id());
            })
            ->latest()
            ->limit(15)
            ->get();

        return view('absen-digital.form', compact('kegiatans', 'peserta', 'riwayat'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kegiatan_id' => ['required', 'exists:absen_kegiatans,id'],
            'mode_peserta' => ['required', 'in:user,nik'],
            'user_id' => ['nullable', 'integer'],
            'nik' => ['nullable', 'string'],
            'nama' => ['nullable', 'string', 'max:150'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'ttd' => ['required', 'string'],
        ], [
            'kegiatan_id.required' => 'Pilih kegiatan.',
            'foto.required' => 'Unggah foto kehadiran.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 4 MB.',
            'latitude.required' => 'Titik lokasi wajib diisi. Gunakan GPS atau isi lintang dan bujur secara manual.',
            'longitude.required' => 'Titik lokasi wajib diisi. Gunakan GPS atau isi lintang dan bujur secara manual.',
            'ttd.required' => 'Tanda tangan di layar wajib diisi.',
        ]);

        $userId = null;
        $nik = null;
        $nama = null;

        if ($data['mode_peserta'] === 'user') {
            $peserta = AbsenDigitalQuery::findPeserta((int) $request->input('user_id'));

            if (! $peserta) {
                throw ValidationException::withMessages([
                    'user_id' => 'Pilih pengguna yang aktif.',
                ]);
            }

            $userId = $peserta->id;
            $nama = $peserta->name;
        } else {
            $nik = preg_replace('/\s+/', '', (string) $request->input('nik'));
            $nama = trim((string) $request->input('nama'));

            if (! is_string($nik) || preg_match('/^[0-9]{16}$/', $nik) !== 1) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK harus 16 digit angka.',
                ]);
            }

            if ($nama === '') {
                throw ValidationException::withMessages([
                    'nama' => 'Nama peserta wajib diisi jika memakai NIK.',
                ]);
            }
        }

        if (AbsenDigitalQuery::alreadyAttended((int) $data['kegiatan_id'], $userId, $nik)) {
            return back()
                ->withInput()
                ->withErrors([
                    'kegiatan_id' => 'Peserta ini sudah tercatat hadir pada kegiatan tersebut.',
                ]);
        }

        $signature = AbsenDigitalFiles::decodeSignature($data['ttd']);
        $fotoPath = AbsenDigitalFiles::storeUpload($request->file('foto'), 'absen-digital/foto');
        $ttdPath = 'absen-digital/ttd/'.Str::uuid()->toString().'.png';
        Storage::disk(AbsenDigitalFiles::DISK)->put($ttdPath, $signature);

        try {
            DB::transaction(function () use ($data, $userId, $nik, $nama, $fotoPath, $ttdPath) {
                AbsenKehadiran::create([
                    'kegiatan_id' => $data['kegiatan_id'],
                    'user_id' => $userId,
                    'nik' => $nik,
                    'nama' => $nama,
                    'metode' => 'form',
                    'foto_path' => $fotoPath,
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'ttd_path' => $ttdPath,
                    'dicatat_oleh' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            AbsenDigitalFiles::delete($fotoPath);
            AbsenDigitalFiles::delete($ttdPath);

            throw $e;
        }

        return redirect()
            ->route('absen-digital.form')
            ->with('success', 'Absen tersimpan untuk '.$nama.'.');
    }

    public function scan(Request $request)
    {
        $kode = AbsenDigitalQuery::normalizeKode($request->query('kode'));
        $kegiatan = $kode !== ''
            ? AbsenKegiatan::query()->where('kode', $kode)->first()
            : null;

        $peserta = AbsenDigitalQuery::peserta()->get(['id', 'name', 'role']);
        $sudahHadir = $kegiatan
            ? $kegiatan->kehadirans()->orderBy('nama')->get(['id', 'user_id', 'nama', 'created_at'])
            : collect();

        return view('absen-digital.scan', [
            'kode' => $kode,
            'kegiatan' => $kegiatan,
            'kodeTidakDitemukan' => $kode !== '' && ! $kegiatan,
            'peserta' => $peserta,
            'sudahHadir' => $sudahHadir,
        ]);
    }

    public function scanStore(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:500'],
            'user_id' => ['required', 'integer'],
        ], [
            'kode.required' => 'Kode hasil scan wajib diisi.',
            'user_id.required' => 'Pilih pengguna yang dicatat hadir.',
        ]);

        $kode = AbsenDigitalQuery::normalizeKode($data['kode']);
        $kegiatan = AbsenKegiatan::query()->where('kode', $kode)->first();

        if (! $kegiatan) {
            return back()
                ->withInput()
                ->withErrors(['kode' => 'Kode kegiatan tidak ditemukan.']);
        }

        $peserta = AbsenDigitalQuery::findPeserta((int) $data['user_id']);

        if (! $peserta) {
            return back()
                ->withInput()
                ->withErrors(['user_id' => 'Pilih pengguna yang aktif.']);
        }

        if (AbsenDigitalQuery::alreadyAttended($kegiatan->id, $peserta->id, null)) {
            return redirect()
                ->route('absen-digital.scan', ['kode' => $kegiatan->kode])
                ->withErrors(['user_id' => $peserta->name.' sudah tercatat hadir.']);
        }

        AbsenKehadiran::create([
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $peserta->id,
            'nama' => $peserta->name,
            'metode' => 'qr',
            'dicatat_oleh' => Auth::id(),
        ]);

        return redirect()
            ->route('absen-digital.scan', ['kode' => $kegiatan->kode])
            ->with('success', 'Kehadiran '.$peserta->name.' tercatat lewat scan.');
    }
}
