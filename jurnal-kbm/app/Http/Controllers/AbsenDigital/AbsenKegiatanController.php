<?php

namespace App\Http\Controllers\AbsenDigital;

use App\Http\Controllers\Controller;
use App\Models\AbsenFoto;
use App\Models\AbsenKegiatan;
use App\Support\AbsenDigitalFiles;
use App\Support\AbsenDigitalQuery;
use App\Support\LmsDriveUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenKegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = AbsenKegiatan::query()
            ->withCount('kehadirans')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        return view('absen-digital.kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        return view('absen-digital.kegiatan.form', [
            'kegiatan' => new AbsenKegiatan([
                'tanggal' => now()->toDateString(),
                'jenis' => 'rapat',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['kode'] = AbsenDigitalQuery::makeKode();
        $data['dibuat_oleh'] = Auth::id();

        $kegiatan = AbsenKegiatan::create($data);

        return redirect()
            ->route('absen-digital.kegiatan.show', $kegiatan)
            ->with('success', 'Kegiatan tersimpan. Kode QR siap dipakai.');
    }

    public function show(AbsenKegiatan $kegiatan)
    {
        $kegiatan->load([
            'kehadirans' => fn ($q) => $q->orderBy('created_at'),
            'fotos' => fn ($q) => $q->latest(),
        ]);

        return view('absen-digital.kegiatan.show', compact('kegiatan'));
    }

    public function edit(AbsenKegiatan $kegiatan)
    {
        return view('absen-digital.kegiatan.form', compact('kegiatan'));
    }

    public function update(Request $request, AbsenKegiatan $kegiatan)
    {
        $kegiatan->update($this->validated($request));

        return redirect()
            ->route('absen-digital.kegiatan.show', $kegiatan)
            ->with('success', 'Data kegiatan diperbarui.');
    }

    public function destroy(AbsenKegiatan $kegiatan)
    {
        $kegiatan->delete();

        return redirect()
            ->route('absen-digital.kegiatan.index')
            ->with('success', 'Kegiatan dan data hadirnya dihapus.');
    }

    public function fotoStore(Request $request, AbsenKegiatan $kegiatan)
    {
        if ($kegiatan->fotos()->count() >= 12) {
            return back()->withErrors([
                'foto' => 'Maksimal 12 foto dokumentasi per kegiatan.',
            ]);
        }

        $data = $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ], [
            'foto.required' => 'Pilih foto yang akan diunggah.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 4 MB.',
        ]);

        $path = AbsenDigitalFiles::storeUpload($request->file('foto'), 'absen-digital/dokumentasi');

        AbsenFoto::create([
            'kegiatan_id' => $kegiatan->id,
            'path' => $path,
            'nama_asli' => AbsenDigitalFiles::safeOriginalName($request->file('foto'), 'foto-dokumentasi'),
            'keterangan' => $data['keterangan'] ?? null,
            'diunggah_oleh' => Auth::id(),
        ]);

        return back()->with('success', 'Foto dokumentasi tersimpan.');
    }

    public function fotoDestroy(AbsenKegiatan $kegiatan, AbsenFoto $foto)
    {
        if ((int) $foto->kegiatan_id !== (int) $kegiatan->id) {
            abort(404);
        }

        $foto->deleteStoredFile();
        $foto->delete();

        return back()->with('success', 'Foto dokumentasi dihapus.');
    }

    public function hasilStore(Request $request, AbsenKegiatan $kegiatan)
    {
        $data = $request->validate([
            'hasil_drive_url' => ['nullable', 'string', 'max:500'],
            'hasil_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip', 'max:10240'],
            'hasil_catatan' => ['nullable', 'string', 'max:2000'],
        ], [
            'hasil_file.max' => 'Berkas hasil rapat maksimal 10 MB.',
            'hasil_file.mimes' => 'Tipe berkas tidak didukung. Gunakan PDF, dokumen kantor, gambar, atau ZIP.',
        ]);

        $drive = trim((string) ($data['hasil_drive_url'] ?? ''));

        if ($drive !== '' && ! LmsDriveUrl::isValid($drive)) {
            return back()
                ->withInput()
                ->withErrors([
                    'hasil_drive_url' => 'Link harus HTTPS dari Google Drive atau Google Dokumen.',
                ]);
        }

        $filePath = $kegiatan->hasil_file_path;
        $fileName = $kegiatan->hasil_file_nama;

        if ($request->hasFile('hasil_file')) {
            AbsenDigitalFiles::delete($filePath);
            $filePath = AbsenDigitalFiles::storeUpload($request->file('hasil_file'), 'absen-digital/hasil');
            $fileName = AbsenDigitalFiles::safeOriginalName($request->file('hasil_file'), 'hasil-rapat');
        }

        if ($drive === '' && ! $filePath) {
            return back()
                ->withInput()
                ->withErrors([
                    'hasil_drive_url' => 'Isi link Google Drive atau unggah berkas hasil rapat. Keduanya boleh diisi sekaligus.',
                ]);
        }

        $kegiatan->update([
            'hasil_drive_url' => $drive !== '' ? LmsDriveUrl::normalize($drive) : null,
            'hasil_file_path' => $filePath,
            'hasil_file_nama' => $fileName,
            'hasil_catatan' => $data['hasil_catatan'] ?? null,
        ]);

        return back()->with('success', 'Hasil rapat tersimpan.');
    }

    public function hasilFileDestroy(AbsenKegiatan $kegiatan)
    {
        AbsenDigitalFiles::delete($kegiatan->hasil_file_path);
        $kegiatan->update([
            'hasil_file_path' => null,
            'hasil_file_nama' => null,
        ]);

        return back()->with('success', 'Berkas hasil rapat dihapus. Link Google Drive tidak diubah.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'jenis' => ['required', 'in:'.implode(',', array_keys(AbsenKegiatan::JENIS))],
            'tanggal' => ['required', 'date'],
            'waktu' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'tempat' => ['nullable', 'string', 'max:150'],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ], [
            'nama.required' => 'Nama kegiatan wajib diisi.',
            'jenis.required' => 'Pilih jenis kegiatan.',
            'tanggal.required' => 'Tanggal kegiatan wajib diisi.',
        ]);

        if (! empty($data['waktu'])) {
            $data['waktu'] = substr($data['waktu'], 0, 5);
        }

        return $data;
    }
}
