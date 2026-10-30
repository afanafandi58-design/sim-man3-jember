@extends('adminlte::page')

@section('title', 'Kuis LMS')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Kuis</h1>
        @can('role-lms-guru-or-admin')
            <a href="{{ route('lms.quizzes.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Tambah</a>
        @endcan
    </div>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Judul</th><th>Kelas</th><th>Link</th><th></th></tr></thead>
                <tbody>
                    @forelse ($quizzes as $q)
                        <tr>
                            <td>{{ e($q->judul) }}</td>
                            <td>{{ e($q->kelas) }} · {{ e($q->mapel) }}</td>
                            <td><a href="{{ e($q->drive_url) }}" target="_blank" rel="noopener">Buka</a></td>
                            <td class="text-right">
                                @can('role-lms-guru-or-admin')
                                    <a href="{{ route('lms.quizzes.edit', $q) }}" class="btn btn-xs btn-primary">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada kuis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($quizzes->hasPages())<div class="card-footer">{{ $quizzes->links() }}</div>@endif
    </div>
@stop
