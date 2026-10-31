@extends('adminlte::page')

@section('title', 'Tambah Tugas')

@section('content_header')
    <h1>Tambah Tugas</h1>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.assignments.store') }}">
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
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Jatuh tempo</label>
                        <input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Nilai maks (opsional)</label>
                        <input type="number" name="max_score" class="form-control" value="{{ old('max_score') }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>URL instruksi / template Drive (opsional)</label>
                    <input type="url" name="drive_url" class="form-control" value="{{ old('drive_url') }}">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub" checked>
                    <label class="form-check-label" for="pub">Publikasikan</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="{{ route('lms.assignments.index') }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
@stop
