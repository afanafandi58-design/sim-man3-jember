@extends('lms.public.layout')

@section('title', $material->judul)

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>{{ e($material->judul) }}</h5>
            <p class="text-muted">{{ e($material->kelas) }} · {{ e($material->mapel) }}</p>
            @if ($material->deskripsi)<p>{!! nl2br(e($material->deskripsi)) !!}</p>@endif
            <a class="btn btn-success btn-lg" href="{{ e($material->drive_url) }}" target="_blank" rel="noopener">Buka Materi di Google Drive</a>
        </div>
    </div>
@endsection
