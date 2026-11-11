<?php

namespace App\Http\Controllers;

use App\Models\AbsensiHarian;
use App\Models\Siswa;
use App\Models\User;
use App\Models\WaliKelas;
use App\Services\AbsensiHarianService;
use App\Services\OperationalDateService;
use App\Support\TitikFSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Modul Absensi Harian (titik F) — terhubung jurnal, bukan silo terpisah.
 */
class AbsensiModulController extends Controller
{
    public function __construct(
        private AbsensiHarianService $absensiHarianService,
        private OperationalDateService $operationalDate,
    ) {
        TitikFSchema::ensure();
    }

    /** Input laporan online + upload bukti (guru/staff/admin/kepala). */
    public function inputForm(Request $request)
    {
        $user = $request->user();
        $tanggal = (string) $request->get('tanggal', $this->operationalDate->today()->toDateString());
        if (! $this->operationalDate->isFillableDate($tanggal)) {
            $tanggal = $this->operationalDate->today()->toDateString();
        }

        $kelasKey = $request->get('kelas');
        $allowed = $this->absensiHarianService->allowedKelasForUser($user);
        $selectedSlot = $kelasKey ? $allowed->firstWhere('key', $kelasKey) : null;
        if ($kelasKey && ! $selectedSlot && $user->role === 'guru') {
            $kelasKey = null;
        }

        $siswas = $this->absensiHarianService->siswaOptionsForUser($user, $kelasKey);
        $existing = AbsensiHarian::query()
            ->with('siswa')
            ->whereDate('tanggal', $tanggal)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get()
            ->sortBy([
                fn ($row) => $row->siswa?->kelas ?? '',
                fn ($row) => $row->siswa?->rombel ?? '',
                fn ($row) => $row->siswa?->nama ?? '',
            ])
            ->values()
            ->keyBy('siswa_id');

        return view('absensi-modul.input', [
            'tanggal' => $tanggal,
            'fillableDates' => $this->operationalDate->fillableDateStrings(),
            'siswas' => $siswas,
            'existing' => $existing,
            'kelasOptions' => $allowed,
            'filters' => ['kelas' => $kelasKey],
            'statuses' => AbsensiHarian::STATUSES,
            'sortByKelas' => true,
        ]);
    }

    public function inputStore(Request $request)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'siswa_id' => 'required|exists:siswas,id',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpha',
            'keterangan' => 'nullable|string|max:255',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
            'bukti_caption' => 'nullable|string|max:150',
        ]);

        $siswa = Siswa::query()->findOrFail((int) $data['siswa_id']);
        abort_unless(
            $this->absensiHarianService->userCanAccessKelas(
                $request->user(),
                $siswa->kelas,
                $siswa->rombel,
                $siswa->kode_kelas
            ),
            403,
            'Anda tidak berhak mengisi absensi untuk kelas siswa ini.'
        );

        $this->absensiHarianService->upsertLaporanOnline(
            $data['tanggal'],
            (int) $data['siswa_id'],
            $data['status'],
            $data['keterangan'] ?? null,
            $request->user(),
            $request->file('bukti'),
            $data['bukti_caption'] ?? null,
        );

        return redirect()
            ->route('absensi-modul.input', [
                'tanggal' => $data['tanggal'],
                'kelas' => $request->get('kelas'),
            ])
            ->with('success', 'Absensi harian tersimpan dan terhubung ke modul jurnal.');
    }

    /** Tool hari efektif: daftar tidak masuk per kelas (guru ajar/wali / admin / kepala / staff). */
    public function hariEfektif(Request $request)
    {
        $user = $request->user();
        $tanggal = (string) $request->get('tanggal', $this->operationalDate->today()->toDateString());
        $isPengajar = in_array($user->role, ['admin', 'kepala', 'staff'], true)
            || $this->absensiHarianService->guruMengajarHariIni($user);
        $isWali = $this->absensiHarianService->isWaliKelas($user);

        $jadwals = $user->guru_id
            ? $this->absensiHarianService->jadwalGuruHariIni((int) $user->guru_id)
            : collect();

        $allowedKelas = $this->absensiHarianService->allowedKelasForUser($user);
        $kelasFilter = $request->get('kelas');
        if ($kelasFilter && $user->role === 'guru' && ! $allowedKelas->firstWhere('key', $kelasFilter)) {
            $kelasFilter = null;
        }

        $tidakMasuk = $this->absensiHarianService->tidakMasukForUser(
            $user,
            $tanggal,
            $kelasFilter
        );

        return view('absensi-modul.hari-efektif', [
            'tanggal' => $tanggal,
            'hari' => $this->operationalDate->hari(),
            'isPengajar' => $isPengajar,
            'isWali' => $isWali,
            'jadwals' => $jadwals,
            'tidakMasuk' => $tidakMasuk,
            'kelasFilter' => $kelasFilter,
            'allowedKelas' => $allowedKelas,
            'canOpenTool' => $isPengajar || $isWali || $allowedKelas->isNotEmpty()
                || in_array($user->role, ['admin', 'kepala', 'staff'], true),
        ]);
    }

    /** Menu wali kelas. */
    public function waliKelas(Request $request)
    {
        $user = $request->user();
        $isAdminLike = in_array($user->role, ['admin', 'kepala'], true);
        $assignments = $this->absensiHarianService->waliAssignments($user);
        if ($assignments->isEmpty() && ! $isAdminLike) {
            abort(403, 'Anda belum ditetapkan sebagai wali kelas.');
        }

        $tanggal = (string) $request->get('tanggal', $this->operationalDate->today()->toDateString());
        $selected = $assignments->first();
        if ($request->filled('wali_id')) {
            $selected = $assignments->firstWhere('id', (int) $request->get('wali_id')) ?? $selected;
        }

        $allowedAll = $this->absensiHarianService->allowedKelasForUser($user);
        $waliSlots = $allowedAll->where('sumber', 'wali')->values();
        $slot = null;
        if ($selected) {
            $slot = $waliSlots->first(function (array $row) use ($selected) {
                return $row['kelas'] === $selected->kelas;
            }) ?? $waliSlots->first();
        }

        // Admin/kepala tanpa filter: tampilkan semua tidak masuk hari itu.
        if ($isAdminLike && ! $request->filled('kelas') && ! $request->filled('wali_id') && ! $selected) {
            $tidakMasuk = $this->absensiHarianService->tidakMasuk($tanggal, null, null);
            $kelas = $request->get('kelas');
            $rombel = $request->get('rombel');
        } elseif ($slot) {
            $tidakMasuk = $this->absensiHarianService->tidakMasuk($tanggal, $slot['kelas'], $slot['rombel']);
            $kelas = $slot['kelas'];
            $rombel = $slot['rombel'];
        } elseif ($isAdminLike && $request->filled('kelas')) {
            $kelas = $request->get('kelas');
            $rombel = $request->get('rombel');
            $tidakMasuk = $this->absensiHarianService->tidakMasuk($tanggal, $kelas, $rombel);
        } else {
            $kelas = $selected?->kelas;
            $rombel = $selected?->rombel;
            $tidakMasuk = collect();
        }

        $mengajarKelas = $user->role === 'guru'
            ? $this->absensiHarianService->allowedKelasForUser($user)->where('sumber', 'mengajar')->values()
            : collect();

        return view('absensi-modul.wali', [
            'assignments' => $assignments,
            'selected' => $selected,
            'tanggal' => $tanggal,
            'tidakMasuk' => $tidakMasuk,
            'kelas' => $kelas,
            'rombel' => $rombel,
            'isAdminLike' => $isAdminLike,
            'mengajarKelas' => $mengajarKelas,
            'resolvedSlot' => $slot,
        ]);
    }

    public function laporan(Request $request)
    {
        $periode = $request->get('periode', 'harian');
        $filters = $request->validate([
            'periode' => 'nullable|in:harian,mingguan,bulanan,semester',
            'tanggal' => 'nullable|date',
            'bulan' => 'nullable|integer|min:1|max:12',
            'tahun' => 'nullable|integer|min:2020|max:2100',
            'semester' => 'nullable|in:Ganjil,Genap',
            'tahun_pelajaran' => 'nullable|string|max:20',
            'kelas' => 'nullable|string|max:50',
            'rombel' => 'nullable|string|max:50',
            'status' => 'nullable|in:Hadir,Sakit,Izin,Alpha',
        ]);
        $periode = $filters['periode'] ?? 'harian';
        $report = $this->absensiHarianService->laporan($periode, $filters);

        return view('absensi-modul.laporan', array_merge($report, [
            'periode' => $periode,
            'filters' => $filters,
            'kelasList' => Siswa::query()->distinct()->orderBy('kelas')->pluck('kelas'),
        ]));
    }

    public function bukti(AbsensiHarian $absensiHarian)
    {
        abort_unless(
            $this->absensiHarianService->canViewBukti(request()->user(), $absensiHarian),
            403,
            'Anda tidak berhak melihat bukti absensi ini.'
        );

        if (! $absensiHarian->hasBukti()) {
            abort(404);
        }

        $relative = (string) $absensiHarian->bukti_path;
        if ($relative === '' || str_contains($relative, '..') || str_starts_with($relative, '/') || str_contains($relative, "\0")) {
            abort(404);
        }

        $diskRoot = realpath(Storage::disk('public')->path(''));
        $path = realpath(Storage::disk('public')->path($relative));
        if (! $diskRoot || ! $path || ! str_starts_with($path, $diskRoot.DIRECTORY_SEPARATOR) || ! is_file($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    /** Admin: kelola assignment wali kelas (minimal). */
    public function waliManage()
    {
        $rows = WaliKelas::query()->with('guru')->orderBy('kelas')->get();
        $gurus = \App\Models\Guru::query()->orderBy('nama')->get(['id', 'nama']);
        $kelasList = Siswa::query()->distinct()->orderBy('kelas')->pluck('kelas');

        return view('absensi-modul.wali-manage', compact('rows', 'gurus', 'kelasList'));
    }

    public function waliStore(Request $request)
    {
        $data = $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas' => 'required|string|max:50',
            'rombel' => 'nullable|string|max:50',
            'tahun_pelajaran' => 'nullable|string|max:20',
        ]);
        WaliKelas::query()->updateOrCreate(
            [
                'guru_id' => $data['guru_id'],
                'kelas' => $data['kelas'],
                'rombel' => $data['rombel'] ?? null,
                'tahun_pelajaran' => $data['tahun_pelajaran'] ?? null,
            ],
            []
        );

        return back()->with('success', 'Wali kelas disimpan.');
    }

    public function waliDestroy(WaliKelas $waliKelas)
    {
        $waliKelas->delete();

        return back()->with('success', 'Assignment wali kelas dihapus.');
    }

    /** Admin: akun staff (izin terbatas modul absensi, bukan master data). */
    public function staffManage()
    {
        $staff = User::query()->where('role', 'staff')->orderBy('name')->get();

        return view('absensi-modul.staff-manage', compact('staff'));
    }

    public function staffStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => 'nullable|string|min:8|max:64',
        ]);

        $plain = $data['password'] ?? ('Stf'.Str::lower(Str::random(8)).'!');
        $user = User::forceCreate([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($plain),
            'role' => 'staff',
            'is_active' => true,
        ]);

        return redirect()
            ->route('absensi-modul.staff.manage')
            ->with('success', 'Akun staff absensi dibuat.')
            ->with('generated_credentials', [
                'email' => $user->email,
                'password' => $plain,
                'nama' => $user->name,
            ]);
    }

    public function staffReset(User $user)
    {
        abort_unless($user->role === 'staff', 404);
        $plain = 'Stf'.Str::lower(Str::random(8)).'!';
        $user->forceFill([
            'password' => Hash::make($plain),
            'password_reset_at' => now(),
        ])->save();

        return redirect()
            ->route('absensi-modul.staff.manage')
            ->with('success', "Password staff {$user->email} direset.")
            ->with('generated_credentials', [
                'email' => $user->email,
                'password' => $plain,
                'nama' => $user->name,
            ]);
    }

    public function staffToggle(User $user)
    {
        abort_unless($user->role === 'staff', 404);
        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('success', $user->is_active ? 'Akun staff diaktifkan.' : 'Akun staff dinonaktifkan.');
    }
}
