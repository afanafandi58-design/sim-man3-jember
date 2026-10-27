<?php

namespace App\Http\Controllers;

use App\Models\KodeKelas;
use App\Models\Siswa;
use App\Models\User;
use App\Services\SiswaAdminService;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function __construct(
        private SiswaAdminService $siswaAdminService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $siswas = $this->siswaAdminService->paginate($user, $request->all());
        $kelasOptions = $this->siswaAdminService->kelasOptions($user);
        $statusOptions = $this->siswaAdminService->statusOptions();

        return view('siswa.index', [
            'siswas' => $siswas,
            'kelasOptions' => $kelasOptions,
            'statusOptions' => $statusOptions,
            'canManage' => $user->role === 'admin',
            'routes' => $this->routesFor($user),
        ]);
    }

    public function create()
    {
        $this->assertAdmin();

        return view('siswa.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $validated = $this->validatePayload($request);

        $this->siswaAdminService->create(
            $validated,
            $request->user(),
            $request->ip(),
        );

        return redirect()
            ->route('siswas.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Request $request, Siswa $siswa)
    {
        $user = $request->user();
        $this->siswaAdminService->assertCanView($user, $siswa);

        return view('siswa.show', [
            'siswa' => $siswa,
            'canManage' => $user->role === 'admin',
            'routes' => $this->routesFor($user),
        ]);
    }

    public function edit(Siswa $siswa)
    {
        $this->assertAdmin();

        return view('siswa.edit', array_merge(
            ['siswa' => $siswa],
            $this->formData(),
        ));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->assertAdmin();

        $validated = $this->validatePayload($request, $siswa);

        $this->siswaAdminService->update(
            $siswa,
            $validated,
            $request->user(),
            $request->ip(),
        );

        return redirect()
            ->route('siswas.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Request $request, Siswa $siswa)
    {
        $this->assertAdmin();

        $this->siswaAdminService->deactivate(
            $siswa,
            $request->user(),
            $request->ip(),
        );

        return redirect()
            ->route('siswas.index')
            ->with('success', 'Data siswa berhasil dinonaktifkan.');
    }

    public function export(Request $request)
    {
        $this->assertAdmin();

        return $this->siswaAdminService->exportExcel(
            $request->user(),
            $request->all(),
        );
    }

    /**
     * @return array<string, string>
     */
    private function routesFor(User $user): array
    {
        return match ($user->role) {
            'guru' => [
                'index' => route('guru.siswas.index'),
                'show' => 'guru.siswas.show',
            ],
            'kepala' => [
                'index' => route('kepala.siswas.index'),
                'show' => 'kepala.siswas.show',
            ],
            default => [
                'index' => route('siswas.index'),
                'show' => 'siswas.show',
                'create' => route('siswas.create'),
                'edit' => 'siswas.edit',
                'export' => route('siswas.export'),
                'import' => route('import-siswa.index'),
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'kelasOptions' => KodeKelas::query()->orderBy('kelas')->orderBy('rombel')->get(),
            'statusOptions' => $this->siswaAdminService->statusOptions(),
            'defaultTahunPelajaran' => SiswaAdminService::DEFAULT_TAHUN_PELAJARAN,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?Siswa $siswa = null): array
    {
        return $request->validate([
            'nis' => 'required|string|max:50',
            'nisn' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'kode_kelas' => 'required|string|max:100',
            'tahun_pelajaran' => 'nullable|string|max:20',
            'status' => 'nullable|in:'.implode(',', Siswa::STATUSES),
        ]);
    }

    private function assertAdmin(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }
    }
}
