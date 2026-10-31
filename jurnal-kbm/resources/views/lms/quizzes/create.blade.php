@extends('lms.page')

@section('title', 'Tambah Kuis')

@section('content_header')<h1>Tambah Kuis</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.quizzes.store') }}">@csrf
            <div class="card-body">
                @php $model = null; @endphp
                @include('lms.partials.jadwal-fields', ['jadwals' => $jadwals, 'model' => $model])
                <div class="form-group"><label>Judul</label><input name="judul" class="form-control" required value="{{ old('judul') }}"></div>
                <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi') }}</textarea></div>
                <div class="form-group"><label>URL Google Form / Drive</label><input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url') }}"></div>
                <div class="form-group"><label>Jatuh tempo</label><input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at') }}"></div>
                <div class="form-check"><input type="checkbox" name="is_published" value="1" checked class="form-check-input" id="pub"><label for="pub">Publikasikan</label></div>
            </div>
            <div class="card-footer"><button class="btn btn-success">Simpan</button> <a href="{{ route('lms.quizzes.index') }}" class="btn btn-default">Batal</a></div>
        </form>
    </div>
@stop
