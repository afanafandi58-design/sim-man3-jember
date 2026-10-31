@extends('lms.page')

@section('title', 'Pengumpulan Tugas')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Pengumpulan Tugas</h1>
        @can('role-lms-guru-or-admin')
            <a href="{{ route('lms.submissions.create') }}" class="btn btn-success btn-sm">Catat manual</a>
        @endcan
    </div>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card">
        <table class="table table-striped mb-0">
            <thead><tr><th>Tugas</th><th>Siswa</th><th>Waktu</th><th>Nilai</th><th></th></tr></thead>
            <tbody>
                @forelse ($submissions as $s)
                    <tr>
                        <td>{{ e($s->assignment?->judul) }}</td>
                        <td>{{ e($s->siswa?->nama) }} ({{ e($s->siswa?->nis) }})</td>
                        <td>{{ $s->submitted_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $s->score ?? '—' }}</td>
                        <td>
                            @can('role-lms-guru-or-admin')
                                <a href="{{ route('lms.submissions.edit', $s) }}" class="btn btn-xs btn-primary">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Belum ada pengumpulan.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($submissions->hasPages())<div class="card-footer">{{ $submissions->links() }}</div>@endif
    </div>
@stop
