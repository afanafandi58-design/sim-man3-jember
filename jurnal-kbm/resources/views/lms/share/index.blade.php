@extends('lms.page')

@section('title', 'Link Berbagi Siswa')

@section('content_header')<h1>Link Berbagi untuk Siswa (tanpa login)</h1>@stop

@section('content')
    <p class="text-muted">Bagikan URL halaman publik berikut ke siswa. Mereka hanya membuka link Google Drive / mengirim link jawaban.</p>

    <div class="card card-success"><div class="card-header">Materi</div><ul class="list-group list-group-flush">
        @forelse ($materials as $m)
            <li class="list-group-item"><strong>{{ e($m->judul) }}</strong><br>
                <code>{{ route('lms.public.material', $m->share_token) }}</code></li>
        @empty<li class="list-group-item text-muted">—</li>@endforelse
    </ul></div>

    <div class="card card-warning mt-3"><div class="card-header">Tugas</div><ul class="list-group list-group-flush">
        @forelse ($assignments as $a)
            <li class="list-group-item"><strong>{{ e($a->judul) }}</strong><br>
                Lihat: <code>{{ route('lms.public.assignment', $a->share_token) }}</code><br>
                Kirim: <code>{{ route('lms.public.submit.form', $a->share_token) }}</code></li>
        @empty<li class="list-group-item text-muted">—</li>@endforelse
    </ul></div>

    <div class="card card-info mt-3"><div class="card-header">Kuis</div><ul class="list-group list-group-flush">
        @forelse ($quizzes as $q)
            <li class="list-group-item"><strong>{{ e($q->judul) }}</strong><br><code>{{ route('lms.public.quiz', $q->share_token) }}</code></li>
        @empty<li class="list-group-item text-muted">—</li>@endforelse
    </ul></div>
@stop
