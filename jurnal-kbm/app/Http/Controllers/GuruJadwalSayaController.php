<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Services\GuruJadwalSayaService;
use App\Services\GuruService;
use App\Services\MovingKelasService;
use App\Support\JadwalMovingSchema;
use Illuminate\Http\Request;

class GuruJadwalSayaController extends Controller
{
    public function __construct(
        private GuruJadwalSayaService $guruJadwalSayaService,
        private MovingKelasService $movingKelasService,
    ) {}

    public function index(Request $request)
    {
        JadwalMovingSchema::ensure();
        $guru = $this->guruJadwalSayaService->getGuru();
        $jadwals = $this->guruJadwalSayaService->paginateForGuru([
            'tahun_pelajaran' => $request->get('tahun_pelajaran', GuruService::DEFAULT_TAHUN_PELAJARAN),
            'semester' => $request->get('semester'),
        ]);

        $tahunPelajaran = $request->get('tahun_pelajaran', GuruService::DEFAULT_TAHUN_PELAJARAN);
        $semester = $request->get('semester');

        return view('guru.jadwal-saya.index', compact('guru', 'jadwals', 'tahunPelajaran', 'semester'));
    }

    public function create()
    {
        $guru = $this->guruJadwalSayaService->getGuru();
        $formData = $this->guruJadwalSayaService->formData();

        return view('guru.jadwal-saya.create', array_merge(compact('guru'), $formData));
    }

    public function store(Request $request)
    {
        $rules = [
            'kode_kelas' => 'required|string|max:50',
            'mapel' => 'required|string|max:100',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'tahun_pelajaran' => 'required|string|max:20',
            'semester' => 'required|in:Ganjil,Genap',
        ];

        if ($request->input('mapel') === '__lainnya__') {
            $rules['mapel_baru'] = 'required|string|min:2|max:100';
        }

        $validated = $request->validate($rules);

        $this->guruJadwalSayaService->store($validated);

        return redirect()
            ->route('guru.jadwal-saya.index')
            ->with('success', 'Jadwal berhasil disimpan dan status menjadi TERKUNCI.');
    }

    public function perbaikanForm(Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);
        $this->guruJadwalSayaService->assertActiveSlot($jadwal);

        if (! $jadwal->is_locked) {
            return redirect()
                ->route('guru.jadwal-saya.index')
                ->with('warning', 'Jadwal ini sedang dalam proses perbaikan oleh admin.');
        }

        if ($jadwal->pendingPerbaikanRequest()->exists()) {
            return redirect()
                ->route('guru.jadwal-saya.index')
                ->with('warning', 'Permintaan perbaikan untuk jadwal ini sudah diajukan.');
        }

        $guru = $this->guruJadwalSayaService->getGuru();
        $formData = $this->guruJadwalSayaService->perbaikanFormData($jadwal);

        return view('guru.jadwal-saya.perbaikan', array_merge(compact('jadwal', 'guru'), $formData));
    }

    public function perbaikanStore(Request $request, Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);
        $this->guruJadwalSayaService->assertActiveSlot($jadwal);

        $validated = $request->validate([
            'kode_kelas' => 'required|string|max:50',
            'mapel' => 'required|string|max:100',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'alasan' => 'required|string|min:10|max:1000',
        ]);

        $this->guruJadwalSayaService->submitPerbaikan($jadwal, $validated);

        return redirect()
            ->route('guru.jadwal-saya.index')
            ->with('success', 'Perbaikan jadwal langsung diterapkan pada slot yang sama. Jejak permintaan tersimpan.');
    }

    public function resetAktif(Request $request)
    {
        $validated = $request->validate([
            'tahun_pelajaran' => 'required|string|max:20',
            'semester' => 'nullable|in:Ganjil,Genap',
            'confirm' => 'accepted',
        ]);

        $result = $this->guruJadwalSayaService->resetAktif([
            'tahun_pelajaran' => $validated['tahun_pelajaran'],
            'semester' => $validated['semester'] ?? null,
        ]);

        return redirect()
            ->route('guru.jadwal-saya.index', [
                'tahun_pelajaran' => $validated['tahun_pelajaran'],
                'semester' => $validated['semester'] ?? null,
            ])
            ->with(
                'success',
                "Reset selesai: {$result['count']} slot dikeluarkan dari daftar aktif. "
                ."Jurnal terkait tetap ada ({$result['jurnal_tetap']} entri). Data tidak dihapus."
            );
    }

    public function toggleMoving(Request $request, Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);
        $this->guruJadwalSayaService->assertActiveSlot($jadwal);

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $this->movingKelasService->setMovingFlag($jadwal, (bool) $validated['enabled']);

        $msg = $validated['enabled']
            ? 'Slot ditandai sebagai moving class. Atur nama kelas dan siswa di halaman Kelas Moving.'
            : 'Penanda moving class dihapus dari slot ini. Daftar siswa gabungan dikosongkan; rombel/siswa master tetap utuh.';

        return redirect()
            ->route('guru.jadwal-saya.index')
            ->with('success', $msg);
    }

    public function movingForm(Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);
        $this->guruJadwalSayaService->assertActiveSlot($jadwal);
        $this->movingKelasService->assertCanManage($jadwal);
        JadwalMovingSchema::ensure();

        if (! $jadwal->is_moving_class) {
            return redirect()
                ->route('guru.jadwal-saya.index')
                ->with('warning', 'Tandai slot sebagai moving class terlebih dahulu.');
        }

        $guru = $this->guruJadwalSayaService->getGuru();
        $groupedSiswa = $this->movingKelasService->candidateSiswaGrouped();
        $selectedIds = $this->movingKelasService->selectedSiswaIds($jadwal);

        return view('guru.jadwal-saya.moving', compact(
            'guru',
            'jadwal',
            'groupedSiswa',
            'selectedIds',
        ));
    }

    public function movingStore(Request $request, Jadwal $jadwal)
    {
        $this->guruJadwalSayaService->assertOwnership($jadwal);
        $this->guruJadwalSayaService->assertActiveSlot($jadwal);

        $validated = $request->validate([
            'moving_kelas_nama' => 'required|string|min:2|max:150',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'integer|exists:siswas,id',
        ]);

        $this->movingKelasService->saveRoster(
            $jadwal,
            $validated['moving_kelas_nama'],
            $validated['siswa_ids'],
        );

        return redirect()
            ->route('guru.jadwal-saya.moving', $jadwal)
            ->with('success', 'Kelas moving disimpan. Nama dan siswa akan dipakai saat isi jurnal slot ini.');
    }
}
