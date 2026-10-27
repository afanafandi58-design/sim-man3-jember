<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\JurnalAttachment;
use App\Services\AbsensiSiswaService;
use App\Services\AdminJurnalService;
use App\Services\JurnalAttachmentService;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    public function __construct(
        private AdminJurnalService $adminJurnalService,
        private JurnalAttachmentService $attachmentService,
        private AbsensiSiswaService $absensiSiswaService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'tanggal' => 'nullable|date',
            'guru_id' => 'nullable|exists:gurus,id',
            'kelas' => 'nullable|string|max:100',
            'mapel' => 'nullable|string|max:100',
            'q' => 'nullable|string|max:100',
        ]);

        $jurnals = Jurnal::query()
            ->with(['jadwal.guru'])
            ->withCount('attachments')
            ->with(['attachments' => fn ($query) => $query
                ->select('id', 'jurnal_id', 'path_file', 'nama_file')
                ->orderBy('created_at')
                ->limit(1),
            ])
            ->when($filters['tanggal'] ?? null, fn ($q, $tanggal) => $q->whereDate('tanggal', $tanggal))
            ->when($filters['guru_id'] ?? null, function ($q, $guruId) {
                $q->whereHas('jadwal', fn ($jadwal) => $jadwal->where('guru_id', $guruId));
            })
            ->when($filters['kelas'] ?? null, function ($q, $kelas) {
                $q->whereHas('jadwal', fn ($jadwal) => $jadwal->where('kelas', $kelas));
            })
            ->when($filters['mapel'] ?? null, function ($q, $mapel) {
                $q->whereHas('jadwal', fn ($jadwal) => $jadwal->where('mapel', $mapel));
            })
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('materi', 'like', '%'.$term.'%')
                        ->orWhere('catatan', 'like', '%'.$term.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $gurus = Guru::query()->orderBy('nama')->get(['id', 'nama']);
        $kelas = Jadwal::query()->distinct()->orderBy('kelas')->pluck('kelas');
        $mapels = Jadwal::query()->distinct()->orderBy('mapel')->pluck('mapel');

        return view('jurnals.index', compact('jurnals', 'gurus', 'kelas', 'mapels', 'filters'));
    }

    public function create()
    {
        $jadwals = Jadwal::with('guru')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('jurnals.create', compact('jadwals'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedJurnal($request);

        $jurnal = Jurnal::create($data);

        if ($request->hasFile('lampiran')) {
            $this->attachmentService->storeMany(
                $jurnal,
                $request->file('lampiran'),
                $request->input('lampiran_caption', [])
            );
        }

        return redirect()
            ->route('jurnals.index')
            ->with('success', 'Jurnal berhasil disimpan.');
    }

    public function show(Jurnal $jurnal)
    {
        $jurnal->load(['jadwal.guru', 'absensiSiswas.siswa', 'attachments']);
        $absensiDetail = $this->absensiSiswaService->groupedAbsentees($jurnal);

        return view('jurnals.show', compact('jurnal', 'absensiDetail'));
    }

    public function edit(Jurnal $jurnal)
    {
        $jurnal->load(['jadwal.guru', 'attachments']);
        $jadwals = Jadwal::with('guru')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('jurnals.edit', compact('jurnal', 'jadwals'));
    }

    public function update(Request $request, Jurnal $jurnal)
    {
        $data = $this->validatedJurnal($request);
        $jurnal->update($data);

        if ($request->filled('attachment_captions')) {
            $this->attachmentService->updateCaptions($jurnal, $request->input('attachment_captions', []));
        }

        if ($request->hasFile('lampiran')) {
            $this->attachmentService->storeMany(
                $jurnal,
                $request->file('lampiran'),
                $request->input('lampiran_caption', [])
            );
        }

        return redirect()
            ->route('jurnals.show', $jurnal)
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Jurnal $jurnal)
    {
        $this->adminJurnalService->delete(
            $jurnal,
            auth()->user(),
            request()->ip(),
        );

        return redirect()
            ->route('jurnals.index')
            ->with('success', 'Jurnal beserta lampiran berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedJurnal(Request $request): array
    {
        $data = $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'tanggal' => 'required|date',
            'jam_ke' => 'required|integer',
            'materi' => 'required',
            'hadir' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'alpha' => 'required|integer|min:0',
            'catatan' => 'nullable',
            'lampiran' => 'nullable|array|max:'.JurnalAttachment::MAX_PER_JURNAL,
            'lampiran.*' => 'file|mimes:jpg,jpeg,png,webp|max:2048',
            'lampiran_caption' => 'nullable|array',
            'lampiran_caption.*' => JurnalAttachmentService::captionFieldRules(),
            'attachment_captions' => 'nullable|array',
            'attachment_captions.*' => JurnalAttachmentService::captionFieldRules(),
        ]);

        return collect($data)->only([
            'jadwal_id', 'tanggal', 'jam_ke', 'materi',
            'hadir', 'izin', 'sakit', 'alpha', 'catatan',
        ])->all();
    }
}
