@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="man3-page-header">
        <h1>{{ $title }}</h1>
        <p>Modul ini sedang dalam pengembangan.</p>
    </div>

    <div class="content-card">
        <div class="card-body-custom text-center py-5">
            <i class="bi bi-tools fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Segera Hadir</h5>
            <p class="text-muted mb-4">Fitur {{ $title }} akan segera tersedia di sistem informasi MAN 3 Jember.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-success">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>
@endsection
