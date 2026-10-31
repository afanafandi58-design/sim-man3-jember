@extends('lms.page')

@section('title', 'Dashboard LMS')

@section('content_header')
    <h1><i class="fas fa-graduation-cap mr-2"></i>Dashboard LMS</h1>
@stop

@section('content')
    @include('lms.partials.flash')

    <div class="row">
        @foreach ([
            ['Materi', $stats['materials'], 'book-reader', 'primary'],
            ['Tugas', $stats['assignments'], 'tasks', 'success'],
            ['Kuis', $stats['quizzes'], 'question-circle', 'info'],
            ['Pengumpulan', $stats['submissions'], 'inbox', 'warning'],
        ] as [$label, $count, $icon, $color])
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-{{ $color }}">
                    <div class="inner">
                        <h3>{{ $count }}</h3>
                        <p>{{ $label }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-{{ $icon }}"></i></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card card-outline card-success">
        <div class="card-header"><h3 class="card-title">Pengumuman terbaru</h3></div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @forelse ($announcements as $a)
                    <li class="list-group-item">
                        <strong>{{ e($a->judul) }}</strong>
                        <span class="text-muted small d-block">{{ $a->published_at?->format('d/m/Y H:i') }}</span>
                        <p class="mb-0 mt-1">{{ e(\Illuminate\Support\Str::limit(strip_tags($a->isi), 200)) }}</p>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada pengumuman.</li>
                @endforelse
            </ul>
        </div>
    </div>
@stop
