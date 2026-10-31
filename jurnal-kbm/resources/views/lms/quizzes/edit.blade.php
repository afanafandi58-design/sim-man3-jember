@extends('lms.page')

@section('title', 'Edit Kuis')

@section('content_header')<h1>Edit Kuis</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.quizzes.update', $quiz) }}">@csrf @method('PUT')
            <div class="card-body">
                @include('lms.partials.jadwal-fields', ['jadwals' => $jadwals, 'model' => $quiz])
                <div class="form-group"><label>Judul</label><input name="judul" class="form-control" required value="{{ old('judul', $quiz->judul) }}"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $quiz->deskripsi) }}</textarea></div>
                <div class="form-group"><label>URL</label><input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url', $quiz->drive_url) }}"></div>
                <p class="small text-muted">Link siswa: <a href="{{ route('lms.public.quiz', $quiz->share_token) }}" target="_blank">buka</a></p>
                <div class="form-check"><input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub" @checked($quiz->is_published)><label for="pub">Publikasikan</label></div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div><button class="btn btn-success">Simpan</button></div>
                <form method="POST" action="{{ route('lms.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
            </div>
        </form>
    </div>
@stop
