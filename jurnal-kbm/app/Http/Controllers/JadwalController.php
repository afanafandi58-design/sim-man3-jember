<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Services\AdminJadwalService;
use App\Services\GuruService;
use App\Services\JadwalGridService;
use App\Services\JadwalMasterDataService;
use App\Services\JadwalScheduleService;
use App\Services\JurnalJamSelectionService;
use App\Services\OperationalDateService;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JadwalController extends Controller
{
    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private JadwalMasterDataService $jadwalMasterDataService,
        private JadwalGridService $jadwalGridService,
        private JurnalJamSelectionService $jamSelectionService,
        private AdminJadwalService $adminJadwalService,
        private GuruService $guruService,
        private OperationalDateService $operationalDateService,
    ) {}

    public function index(Request $request)
    {
        $keyword = $request->keyword;

        $jadwals = Jadwal::with('guru')
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('kelas', 'like', "%{$keyword}%")
                    ->orWhere('hari', 'like', "%{$keyword}%")
                    ->orWhereHas('guru', function ($q) use ($keyword) {
                        $q->where('nama', 'like', "%{$keyword}%");
                    });
            })
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(10);

        $deleteChecks = $jadwals->getCollection()->mapWithKeys(function (Jadwal $jadwal) {
            return [$jadwal->id => $this->adminJadwalService->canDelete($jadwal)];
        });

        return view('jadwal.index', compact('jadwals', 'deleteChecks'));
    }

    public function create()
    {
        $gurus = Guru::orderBy('nama')->get();

        return view('jadwal.create', array_merge(
            compact('gurus'),
            $this->jadwalMasterDataService->jamRangeFormData(),
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas' => 'required',
            'mapel' => 'required',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'tahun_pelajaran' => 'required',
            'semester' => 'required',
        ]);

        $payload = $this->jadwalMasterDataService->mergeJamRangeIntoData($validated);

        if ($this->jadwalScheduleService->hasTimeConflict(
            (int) $payload['guru_id'],
            $payload['hari'],
            $payload['jam_mulai'],
            $payload['jam_selesai'],
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_ke' => 'Guru sudah memiliki jadwal pada waktu tersebut.',
                ]);
        }

        Jadwal::create([
            'guru_id' => $payload['guru_id'],
            'kelas' => $payload['kelas'],
            'mapel' => $payload['mapel'],
            'hari' => $payload['hari'],
            'pola_jadwal_id' => $payload['pola_jadwal_id'],
            'jam_ke' => $payload['jam_ke'],
            'jam_mulai' => $payload['jam_mulai'],
            'jam_selesai' => $payload['jam_selesai'],
            'tahun_pelajaran' => $payload['tahun_pelajaran'],
            'semester' => $payload['semester'],
        ]);
        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($payload['guru_id']));

        return redirect()
            ->route('jadwals.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function show(Jadwal $jadwal)
    {
        //
    }

    public function edit(Jadwal $jadwal)
    {
        $gurus = Guru::orderBy('nama')->get();

        return view('jadwal.edit', array_merge(
            compact('jadwal', 'gurus'),
            $this->jadwalMasterDataService->jamRangeFormData(),
            [
                'selectedJamKe' => $jadwal->jam_ke,
                'selectedJamKeAkhir' => $this->jamSelectionService->jamAkhirKeForJadwal($jadwal),
                'selectedHari' => $jadwal->hari,
                'selectedJamMulai' => substr((string) $jadwal->jam_mulai, 0, 5),
                'selectedJamSelesai' => substr((string) $jadwal->jam_selesai, 0, 5),
            ],
        ));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas' => 'required',
            'mapel' => 'required',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:0',
            'jam_ke_akhir' => 'nullable|integer|min:0|gte:jam_ke',
            'tahun_pelajaran' => 'required',
            'semester' => 'required',
        ]);

        $payload = $this->jadwalMasterDataService->mergeJamRangeIntoData($validated);

        if ($this->jadwalScheduleService->hasTimeConflict(
            (int) $payload['guru_id'],
            $payload['hari'],
            $payload['jam_mulai'],
            $payload['jam_selesai'],
            $jadwal->id,
        )) {
            return back()
                ->withInput()
                ->withErrors([
                    'jam_ke' => 'Guru sudah memiliki jadwal pada waktu tersebut.',
                ]);
        }

        $jadwal->update([
            'guru_id' => $payload['guru_id'],
            'kelas' => $payload['kelas'],
            'mapel' => $payload['mapel'],
            'hari' => $payload['hari'],
            'pola_jadwal_id' => $payload['pola_jadwal_id'],
            'jam_ke' => $payload['jam_ke'],
            'jam_mulai' => $payload['jam_mulai'],
            'jam_selesai' => $payload['jam_selesai'],
            'tahun_pelajaran' => $payload['tahun_pelajaran'],
            'semester' => $payload['semester'],
        ]);
        $this->guruService->syncJumlahJamMengajar(Guru::findOrFail($payload['guru_id']));

        return redirect()->route('jadwals.index')
            ->with('success', 'Jadwal berhasil diupdate.');
    }

    public function grid(Request $request)
    {
        $gurus = Guru::orderBy('nama')->get();
        $guruId = (int) $request->query('guru_id', 0);
        $tahunPelajaran = $request->query(
            'tahun_pelajaran',
            $this->operationalDateService->tahunPelajaran(),
        );
        $semester = $request->query(
            'semester',
            $this->operationalDateService->semester(),
        );

        $grid = null;
        $selectedGuru = null;
        $deleteChecks = [];

        if ($guruId > 0) {
            $selectedGuru = Guru::find($guruId);
            if ($selectedGuru) {
                $grid = $this->jadwalGridService->buildGridData($guruId, $tahunPelajaran, $semester);
                foreach ($grid['cells'] as $cell) {
                    if ($cell['jadwal']) {
                        $deleteChecks[$cell['jadwal']->id] = $this->adminJadwalService->canDelete($cell['jadwal']);
                    }
                }
            }
        }

        return view('jadwal.grid', [
            'gurus' => $gurus,
            'guruId' => $guruId,
            'selectedGuru' => $selectedGuru,
            'tahunPelajaran' => $tahunPelajaran,
            'semester' => $semester,
            'grid' => $grid,
            'deleteChecks' => $deleteChecks,
            'kelasOptions' => $this->jadwalMasterDataService->kelasOptions(),
            'mapelOptions' => $this->jadwalMasterDataService->mapelOptions(),
        ]);
    }

    public function upsertGridCell(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_ke' => 'required|integer|min:0',
            'tahun_pelajaran' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'kode_kelas' => 'required|string',
            'mapel' => 'required|string',
        ]);

        try {
            $this->jadwalGridService->upsertGridCell($validated);
        } catch (ValidationException $e) {
            return redirect()
                ->route('jadwals.grid', [
                    'guru_id' => $validated['guru_id'],
                    'tahun_pelajaran' => $validated['tahun_pelajaran'],
                    'semester' => $validated['semester'],
                ])
                ->withInput()
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('jadwals.grid', [
                'guru_id' => $validated['guru_id'],
                'tahun_pelajaran' => $validated['tahun_pelajaran'],
                'semester' => $validated['semester'],
            ])
            ->with('success', 'Sel jadwal berhasil disimpan.');
    }

    public function clearGridCell(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'tahun_pelajaran' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
        ]);

        try {
            $this->adminJadwalService->delete(
                $jadwal,
                $request->user(),
                $request->ip(),
                'Dihapus dari tampilan grid Master Jadwal.',
            );
        } catch (ValidationException $e) {
            return redirect()
                ->route('jadwals.grid', [
                    'guru_id' => $validated['guru_id'],
                    'tahun_pelajaran' => $validated['tahun_pelajaran'],
                    'semester' => $validated['semester'],
                ])
                ->withErrors($e->errors());
        }

        return redirect()
            ->route('jadwals.grid', [
                'guru_id' => $validated['guru_id'],
                'tahun_pelajaran' => $validated['tahun_pelajaran'],
                'semester' => $validated['semester'],
            ])
            ->with('success', 'Sel jadwal berhasil dikosongkan.');
    }

    public function destroy(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'alasan_hapus' => 'nullable|string|max:500',
        ]);

        try {
            $this->adminJadwalService->delete(
                $jadwal,
                $request->user(),
                $request->ip(),
                $validated['alasan_hapus'] ?? null,
            );
        } catch (ValidationException $e) {
            return redirect()
                ->route('jadwals.index')
                ->withErrors($e->errors());
        }

        return redirect()->route('jadwals.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
