@extends('lms.public.layout')

@section('title', $assignment->judul)

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>{{ e($assignment->judul) }}</h5>
            <p class="text-muted">{{ e($assignment->kelas) }} · {{ e($assignment->mapel) }}
                @if ($assignment->due_at) · Jatuh tempo {{ $assignment->due_at->format('d/m/Y H:i') }}@endif
            </p>
            @if ($assignment->deskripsi)<p>{!! nl2br(e($assignment->deskripsi)) !!}</p>@endif
            @if ($assignment->drive_url)
                <a class="btn btn-outline-success mb-2" href="{{ e($assignment->drive_url) }}" target="_blank" rel="noopener">Lihat instruksi / template</a>
            @endif
            <a class="btn btn-success" href="{{ route('lms.public.submit.form', $assignment->share_token) }}">Kirim link jawaban Drive</a>
        </div>
    </div>
@endsection
