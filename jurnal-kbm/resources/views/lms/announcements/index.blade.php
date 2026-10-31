@extends('lms.page')

@section('title', 'Pengumuman LMS')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Pengumuman</h1>
        @can('role-lms-guru-or-admin')
            <a href="{{ route('lms.announcements.create') }}" class="btn btn-success btn-sm">Tambah</a>
        @endcan
    </div>
@stop

@section('content')
    @include('lms.partials.flash')
    @foreach ($announcements as $a)
        <div class="card card-outline card-success mb-2">
            <div class="card-header"><strong>{{ e($a->judul) }}</strong> <span class="float-right text-muted small">{{ $a->published_at?->format('d/m/Y') }}</span></div>
            <div class="card-body">{!! nl2br(e($a->isi)) !!}
                @if ($a->drive_url)
                    <p class="mt-2"><a href="{{ e($a->drive_url) }}" target="_blank" rel="noopener">Lampiran Drive</a></p>
                @endif
            </div>
            @can('role-lms-guru-or-admin')
                @if (auth()->user()->role === 'admin' || (int) $a->guru_id === (int) auth()->user()->guru_id)
                    <div class="card-footer"><a href="{{ route('lms.announcements.edit', $a) }}" class="btn btn-xs btn-primary">Edit</a></div>
                @endif
            @endcan
        </div>
    @endforeach
    {{ $announcements->links() }}
@stop
