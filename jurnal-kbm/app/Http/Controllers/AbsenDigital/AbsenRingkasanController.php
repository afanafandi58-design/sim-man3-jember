<?php

namespace App\Http\Controllers\AbsenDigital;

use App\Http\Controllers\Controller;
use App\Models\AbsenKegiatan;
use App\Models\AbsenKehadiran;
use App\Support\AbsenDigitalQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AbsenRingkasanController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $kegiatans = AbsenDigitalQuery::filteredKegiatan($filters)
            ->withCount('kehadirans')
            ->orderByDesc('tanggal')
            ->orderBy('nama')
            ->get();

        $perJenis = $kegiatans
            ->groupBy('jenis')
            ->map(fn ($rows) => $rows->sum('kehadirans_count'));

        return view('absen-digital.ringkasan', [
            'filters' => $filters,
            'kegiatans' => $kegiatans,
            'totalHadir' => $kegiatans->sum('kehadirans_count'),
            'totalKegiatan' => $kegiatans->count(),
            'perJenis' => $perJenis,
            'semuaKegiatan' => AbsenKegiatan::query()->orderByDesc('tanggal')->get(['id', 'nama', 'tanggal']),
        ]);
    }

    public function cetakForm(Request $request)
    {
        $filters = $this->filters($request);

        return view('absen-digital.cetak', [
            'filters' => $filters,
            'semuaKegiatan' => AbsenKegiatan::query()->orderByDesc('tanggal')->get(['id', 'nama', 'tanggal']),
        ]);
    }

    public function cetakHalaman(Request $request)
    {
        $filters = $this->filters($request);

        $rows = AbsenKehadiran::query()
            ->with('kegiatan')
            ->whereHas('kegiatan', function ($query) use ($filters) {
                $query->when($filters['tanggal_dari'], fn ($q, $dari) => $q->whereDate('tanggal', '>=', $dari))
                    ->when($filters['tanggal_sampai'], fn ($q, $sampai) => $q->whereDate('tanggal', '<=', $sampai))
                    ->when($filters['jenis'], fn ($q, $jenis) => $q->where('jenis', $jenis))
                    ->when($filters['kegiatan_id'], fn ($q, $id) => $q->whereKey($id));
            })
            ->orderBy('kegiatan_id')
            ->orderBy('nama')
            ->get()
            ->groupBy('kegiatan_id');

        return view('absen-digital.cetak-halaman', [
            'filters' => $filters,
            'groups' => $rows,
        ]);
    }

    /** @return array{tanggal_dari: ?string, tanggal_sampai: ?string, jenis: ?string, kegiatan_id: ?int} */
    private function filters(Request $request): array
    {
        if (! $request->hasAny(['tanggal_dari', 'tanggal_sampai', 'jenis', 'kegiatan_id'])) {
            $request->merge([
                'tanggal_dari' => now()->startOfMonth()->toDateString(),
                'tanggal_sampai' => now()->toDateString(),
            ]);
        }

        $data = $request->validate([
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date'],
            'jenis' => ['nullable', 'in:'.implode(',', array_keys(AbsenKegiatan::JENIS))],
            'kegiatan_id' => ['nullable', 'integer'],
        ]);

        $dari = $data['tanggal_dari'] ?? null;
        $sampai = $data['tanggal_sampai'] ?? null;

        if ($dari && $sampai) {
            $start = Carbon::parse($dari)->startOfDay();
            $end = Carbon::parse($sampai)->startOfDay();

            if ($end->lt($start)) {
                throw ValidationException::withMessages([
                    'tanggal_sampai' => 'Tanggal sampai tidak boleh lebih awal dari tanggal dari.',
                ]);
            }

            if ((int) abs($start->diffInDays($end)) > 366) {
                throw ValidationException::withMessages([
                    'tanggal_sampai' => 'Rentang tanggal maksimal 366 hari.',
                ]);
            }
        }

        if (! empty($data['kegiatan_id']) && ! AbsenKegiatan::query()->whereKey($data['kegiatan_id'])->exists()) {
            $data['kegiatan_id'] = null;
        }

        return [
            'tanggal_dari' => $dari,
            'tanggal_sampai' => $sampai,
            'jenis' => $data['jenis'] ?? null,
            'kegiatan_id' => isset($data['kegiatan_id']) ? (int) $data['kegiatan_id'] : null,
        ];
    }
}
