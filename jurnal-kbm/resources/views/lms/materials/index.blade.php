@extends('adminlte::page')

@section('title', 'Materi LMS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Materi Pembelajaran</h1>
        @can('role-lms-guru-or-admin')
            <a href="{{ route('lms.materials.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah</a>
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
                        <th>Mapel</th>
                        <th>Guru</th>
                        <th>Link</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $m)
                        <tr>
                            <td>{{ e($m->judul) }}</td>
                            <td>{{ e($m->kelas) }}</td>
                            <td>{{ e($m->mapel) }}</td>
                            <td>{{ e($m->guru?->displayName() ?? '—') }}</td>
                            <td><a href="{{ e($m->drive_url) }}" target="_blank" rel="noopener">Drive</a></td>
                            <td class="text-right">
                                @can('role-lms-guru-or-admin')
                                    <a href="{{ route('lms.materials.edit', $m) }}" class="btn btn-xs btn-primary">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada materi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($materials->hasPages())
            <div class="card-footer">{{ $materials->links() }}</div>
        @endif
    </div>
@stop
