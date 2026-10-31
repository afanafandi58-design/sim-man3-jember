@extends('adminlte::page')

@section('title', 'Tambah Materi')

@section('content_header')
    <h1>Tambah Materi</h1>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.materials.store') }}">
            @csrf
            <div class="card-body">
                @php $model = null; @endphp
                @include('lms.partials.jadwal-fields', ['jadwals' => $jadwals, 'model' => $model])
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="judul" class="form-control" required value="{{ old('judul') }}">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="form-group">
                    <label>URL Google Drive (HTTPS)</label>
                    <input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url') }}">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub" checked>
                    <label class="form-check-label" for="pub">Publikasikan</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('lms.materials.index') }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
@stop
