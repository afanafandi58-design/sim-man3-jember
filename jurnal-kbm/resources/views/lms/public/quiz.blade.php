@extends('lms.public.layout')

@section('title', $quiz->judul)

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>{{ e($quiz->judul) }}</h5>
            <p class="text-muted">{{ e($quiz->kelas) }} · {{ e($quiz->mapel) }}</p>
            @if ($quiz->deskripsi)<p>{!! nl2br(e($quiz->deskripsi)) !!}</p>@endif
            <a class="btn btn-info btn-lg" href="{{ e($quiz->drive_url) }}" target="_blank" rel="noopener">Mulai Kuis (Google Form/Drive)</a>
        </div>
    </div>
@endsection
