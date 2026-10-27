<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Services\AdminJadwalService;
use App\Services\GuruService;
use App\Services\JadwalMasterDataService;
use App\Services\JadwalScheduleService;
use App\Services\JurnalJamSelectionService;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JadwalController extends Controller
{
    public function __construct(
        private JadwalScheduleService $jadwalScheduleService,
        private JadwalMasterDataService $jadwalMasterDataService,
        private JurnalJamSelectionService $jamSelectionService,
        private AdminJadwalService $adminJadwalService,
        private GuruService $guruService,
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
