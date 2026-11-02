@extends('adminlte::page')

@section('title', 'Monitor Perubahan Jadwal')

@section('content_header')
    <h1>Monitor Perubahan Jadwal</h1>
@stop

@section('content')

<div class="alert alert-secondary">
    Guru mengubah jadwal <strong>langsung</strong> (mode terbuka). Daftar ini untuk pemantauan admin — bukan antre persetujuan.
    Permintaan lama ada di <a href="{{ route('jadwal-perbaikan.index') }}">Permintaan Perubahan Jadwal</a>.
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="form-row mb-3">
            <div class="form-group col-md-4">
                <label>Guru</label>
                <select name="guru_id" class="form-control form-control-sm">
                    <option value="">Semua guru</option>
                    @foreach ($gurus as $g)
                        <option value="{{ $g->id }}" @selected($guruId === $g->id)>{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Aksi</label>
                <select name="action" class="form-control form-control-sm">
                    <option value="">Semua</option>
                    @foreach (['guru_create', 'guru_update', 'guru_delete'] as $act)
                        <option value="{{ $act }}" @selected($action === $act)>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Guru</th>
                        <th>Aksi</th>
                        <th>Sebelum</th>
                        <th>Sesudah</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->guru?->nama ?? '—' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td class="small">
                                @if ($log->snapshot_before)
                                    {{ $log->snapshot_before['hari'] ?? '' }}
                                    {{ $log->snapshot_before['kelas'] ?? '' }}
                                    {{ $log->snapshot_before['mapel'] ?? '' }}
                                    (Jam {{ $log->snapshot_before['jam_ke'] ?? '?' }})
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small">
                                @if ($log->snapshot_after)
                                    {{ $log->snapshot_after['hari'] ?? '' }}
                                    {{ $log->snapshot_after['kelas'] ?? '' }}
                                    {{ $log->snapshot_after['mapel'] ?? '' }}
                                    (Jam {{ $log->snapshot_after['jam_ke'] ?? '?' }})
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small">{{ $log->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada log (setelah migrate).</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </div>
</div>

@stop
