@extends('adminlte::page')

@section('title', 'Dashboard LMS')

@section('content_header')
    <h1><i class="fas fa-graduation-cap mr-2"></i>Dashboard LMS</h1>
@stop

@section('content')
    @include('lms.partials.flash')

    <div class="row">
        @foreach ([
            ['label' => 'Materi', 'count' => $stats['materials'], 'icon' => 'book-reader', 'color' => 'primary'],
            ['label' => 'Tugas', 'count' => $stats['assignments'], 'icon' => 'tasks', 'color' => 'success'],
            ['label' => 'Kuis', 'count' => $stats['quizzes'], 'icon' => 'question-circle', 'color' => 'info'],
            ['label' => 'Pengumpulan', 'count' => $stats['submissions'], 'icon' => 'inbox', 'color' => 'warning'],
        ] as $card)
            <div class="col-md-3 col-sm-6">
                <div class="small-box bg-{{ $card['color'] }}">
                    <div class="inner">
                        <h3>{{ $card['count'] }}</h3>
                        <p>{{ $card['label'] }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-{{ $card['icon'] }}"></i></div>
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
