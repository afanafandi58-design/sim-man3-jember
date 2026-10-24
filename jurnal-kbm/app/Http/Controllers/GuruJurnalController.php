<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\JurnalAttachment;
use App\Services\AbsensiHarianService;
use App\Services\AbsensiSiswaService;
use App\Services\GuruJurnalService;
use App\Services\JurnalJamSelectionService;
use App\Services\JurnalAttachmentService;
use App\Services\KodeKelasResolverService;
use App\Services\OperationalDateService;
use App\Services\SiswaService;
use App\Support\TitikESchema;
use Illuminate\Http\Request;

class GuruJurnalController extends Controller
{
    public function __construct(
        private GuruJurnalService $guruJurnalService,
        private JurnalAttachmentService $attachmentService,
        private SiswaService $siswaService,
        private AbsensiSiswaService $absensiSiswaService,
        private AbsensiHarianService $absensiHarianService,
        private KodeKelasResolverService $kodeKelasResolverService,
        private OperationalDateService $operationalDate,
        private JurnalJamSelectionService $jamSelectionService,
    ) {}

    public function create(Jadwal $jadwal, Request $request)
    {
        TitikESchema::ensure();
        $this->guruJurnalService->assertJadwalAccess($jadwal);

        $tanggal = (string) $request->query('tanggal', $this->operationalDate->today()->toDateString());
        if (! $this->operationalDate->isFillableDate($tanggal)) {
            $tanggal = $this->operationalDate->today()->toDateString();
        }

        if ($existingId = $this->guruJurnalService->findJurnalIdForDate($jadwal, $tanggal)) {
            return redirect()
                ->route('guru.jurnal.edit', $existingId)
                ->with('warning', 'Jurnal untuk jadwal & tanggal tersebut sudah ada — membuka form edit.');
        }

        $jadwal->load('guru');
        $siswas = $this->siswaService->forJadwal($jadwal);
        $kelasMeta = $this->kodeKelasResolverService->resolveMeta($jadwal);
        $operationalDate = $this->operationalDate->context();
        $jpOptions = $this->jamSelectionService->jpOptionsForJadwal($jadwal);
        $selectedJp = old('jp_selected', $this->jamSelectionService->preselectedJp($jadwal));
        $isOwner = $this->guruJurnalService->isJadwalOwner($jadwal);
        $fillableDates = $this->operationalDate->fillableDateStrings();
        $jurnalRulesNotice = $this->operationalDate->jurnalRulesNotice();
        $defaultPengganti = ! $isOwner
            ? (auth()->user()->guru?->nama ?? auth()->user()->name)
            : old('guru_pengganti');

        $prefill = $this->absensiHarianService->prefillMapForKelas(
            $tanggal,
            (string) ($kelasMeta['kelas'] ?? $jadwal->kelas),
            $kelasMeta['rombel'] ?? null,
        );
        $absensiMap = collect($prefill['status']);
        $keteranganMap = collect($prefill['keterangan']);
        $tidakMasukHarian = $this->absensiHarianService->tidakMasuk(
            $tanggal,
            (string) ($kelasMeta['kelas'] ?? $jadwal->kelas),
            $kelasMeta['rombel'] ?? null,
        );

        return view('guru.jurnal.create', compact(
            'jadwal',
            'siswas',
            'kelasMeta',
            'operationalDate',
            'jpOptions',
            'selectedJp',
            'isOwner',
            'fillableDates',
            'tanggal',
            'jurnalRulesNotice',
            'defaultPengganti',
            'absensiMap',
            'keteranganMap',
            'tidakMasukHarian',
        ));
    }

    public function edit(Jurnal $jurnal)
    {
        TitikESchema::ensure();
        $this->guruJurnalService->assertJurnalAccess($jurnal);
        $jurnal->load(['jadwal.guru', 'absensiSiswas.siswa', 'attachments']);

        $siswas = $this->siswaService->forJadwal($jurnal->jadwal);
        $kelasMeta = $this->kodeKelasResolverService->resolveMeta($jurnal->jadwal);
        $absensiMap = $jurnal->absensiSiswas->pluck('status', 'siswa_id');
        $keteranganMap = $jurnal->absensiSiswas->pluck('keterangan', 'siswa_id');
        $absensiDetail = $this->absensiSiswaService->groupedAbsentees($jurnal);
        $jpOptions = $this->jamSelectionService->jpOptionsForJadwal($jurnal->jadwal);
        $selectedJp = old('jp_selected', $this->jamSelectionService->selectedJpForJurnal($jurnal, $jurnal->jadwal));
        $isOwner = $this->guruJurnalService->isJadwalOwner($jurnal->jadwal);
        $jurnalRulesNotice = $this->operationalDate->jurnalRulesNotice();
        $isLocked = $jurnal->isLocked();

        return view('guru.jurnal.edit', compact(
            'jurnal',
            'siswas',
            'kelasMeta',
            'absensiMap',
            'keteranganMap',
            'absensiDetail',
            'jpOptions',
            'selectedJp',
            'isOwner',
            'jurnalRulesNotice',
            'isLocked',
        ));
    }

    public function update(Request $request, Jurnal $jurnal)
    {
        TitikESchema::ensure();
        $data = $request->validate([
            'materi' => 'required',
            'jp_selected' => 'required|array|min:1',
            'jp_selected.*' => 'integer|min:0',
            'hadir' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'alpha' => 'required|integer|min:0',
            'status_kbm' => 'required|in:Selesai,Belum Selesai,Guru Pengganti,Libur',
            'guru_pengganti' => 'nullable|string|max:150',
            'catatan' => 'nullable',
            'absensi.siswa' => 'nullable|array',
            'absensi.siswa.*' => 'in:Hadir,Sakit,Izin,Alpha',
            'absensi.keterangan' => 'nullable|array',
            'absensi.keterangan.*' => 'nullable|string|max:255',
            'lampiran' => 'nullable|array|max:'.JurnalAttachment::MAX_PER_JURNAL,
            'lampiran.*' => 'file|mimes:jpg,jpeg,png,webp|max:2048',
            'lampiran_caption' => 'nullable|array',
            'lampiran_caption.*' => JurnalAttachmentService::captionFieldRules(),
            'attachment_captions' => 'nullable|array',
            'attachment_captions.*' => JurnalAttachmentService::captionFieldRules(),
        ]);

        $absensiInput = $request->input('absensi');
        $data = $this->mergeJamSelection($data, $jurnal->jadwal);

        $this->guruJurnalService->update($jurnal, $data, $absensiInput);

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
            ->route('guru.dashboard')
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function store(Request $request)
    {
        TitikESchema::ensure();
        $data = $request->validate([
            'jadwal_id' => 'required|exists:jadwals,id',
            'tanggal' => 'required|date',
            'materi' => 'required',
            'jp_selected' => 'required|array|min:1',
            'jp_selected.*' => 'integer|min:0',
            'hadir' => 'required|integer|min:0',
            'izin' => 'required|integer|min:0',
            'sakit' => 'required|integer|min:0',
            'alpha' => 'required|integer|min:0',
            'status_kbm' => 'required|in:Selesai,Belum Selesai,Guru Pengganti,Libur',
            'guru_pengganti' => 'nullable|string|max:150',
            'catatan' => 'nullable',
            'absensi.siswa' => 'nullable|array',
            'absensi.siswa.*' => 'in:Hadir,Sakit,Izin,Alpha',
            'absensi.keterangan' => 'nullable|array',
            'absensi.keterangan.*' => 'nullable|string|max:255',
            'lampiran' => 'nullable|array|max:'.JurnalAttachment::MAX_PER_JURNAL,
            'lampiran.*' => 'file|mimes:jpg,jpeg,png,webp|max:2048',
            'lampiran_caption' => 'nullable|array',
            'lampiran_caption.*' => JurnalAttachmentService::captionFieldRules(),
        ]);

        $this->operationalDate->assertFillableDate($data['tanggal']);
        $absensiInput = $request->input('absensi');
        $jadwal = Jadwal::findOrFail($data['jadwal_id']);
        $data = $this->mergeJamSelection($data, $jadwal);

        $jurnal = $this->guruJurnalService->create($data, $absensiInput);

        if ($request->hasFile('lampiran')) {
            $this->attachmentService->storeMany(
                $jurnal,
                $request->file('lampiran'),
                $request->input('lampiran_caption', [])
            );
        }

        return redirect()
            ->route('guru.dashboard')
            ->with('success', 'Jurnal berhasil disimpan.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergeJamSelection(array $data, Jadwal $jadwal): array
    {
        $resolved = $this->jamSelectionService->resolveFromSelection(
            $data['jp_selected'] ?? [],
            $jadwal,
        );

        unset($data['jp_selected']);

        return array_merge($data, [
            'jam_ke' => $resolved['jam_ke'],
            'jam_mulai' => $resolved['jam_mulai'],
            'jam_selesai' => $resolved['jam_selesai'],
        ]);
    }
}
