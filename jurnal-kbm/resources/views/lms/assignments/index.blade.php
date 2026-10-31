@extends('lms.page')

@section('title', 'Tugas LMS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tugas</h1>
        @can('role-lms-guru-or-admin')
            <a href="{{ route('lms.assignments.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah</a>
        @endcan
    </div>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Kelas</th>
                        <th>Jatuh tempo</th>
                        <th>Pengumpulan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $a)
                        <tr>
                            <td>{{ e($a->judul) }}</td>
                            <td>{{ e($a->kelas) }} · {{ e($a->mapel) }}</td>
                            <td>{{ $a->due_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $a->submissions_count }}</td>
                            <td class="text-right">
                                @can('role-lms-guru-or-admin')
                                    <a href="{{ route('lms.assignments.edit', $a) }}" class="btn btn-xs btn-primary">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada tugas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($assignments->hasPages())
            <div class="card-footer">{{ $assignments->links() }}</div>
        @endif
    </div>
@stop
