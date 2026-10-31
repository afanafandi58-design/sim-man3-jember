@extends('adminlte::page')

@section('title', 'Edit Tugas')

@section('content_header')
    <h1>Edit Tugas</h1>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.assignments.update', $assignment) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @include('lms.partials.jadwal-fields', ['jadwals' => $jadwals, 'model' => $assignment])
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="judul" class="form-control" required value="{{ old('judul', $assignment->judul) }}">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $assignment->deskripsi) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Jatuh tempo</label>
                        <input type="datetime-local" name="due_at" class="form-control"
                               value="{{ old('due_at', $assignment->due_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Nilai maks</label>
                        <input type="number" name="max_score" class="form-control" value="{{ old('max_score', $assignment->max_score) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>URL Drive</label>
                    <input type="url" name="drive_url" class="form-control" value="{{ old('drive_url', $assignment->drive_url) }}">
                </div>
                <p class="text-muted small">Halaman siswa:
                    <a href="{{ route('lms.public.assignment', $assignment->share_token) }}" target="_blank">buka</a>
                </p>
                <div class="form-check">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub"
                        @checked(old('is_published', $assignment->is_published))>
                    <label class="form-check-label" for="pub">Publikasikan</label>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('lms.assignments.index') }}" class="btn btn-default">Batal</a>
                </div>
                <form method="POST" action="{{ route('lms.assignments.destroy', $assignment) }}" onsubmit="return confirm('Hapus tugas?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </form>
    </div>
@stop
