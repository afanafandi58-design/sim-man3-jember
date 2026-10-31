@extends('adminlte::page')

@section('title', 'Edit Materi')

@section('content_header')
    <h1>Edit Materi</h1>
@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card card-success">
        <form method="POST" action="{{ route('lms.materials.update', $material) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @include('lms.partials.jadwal-fields', ['jadwals' => $jadwals, 'model' => $material])
                <div class="form-group">
                    <label>Judul</label>
                    <input type="text" name="judul" class="form-control" required value="{{ old('judul', $material->judul) }}">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $material->deskripsi) }}</textarea>
                </div>
                <div class="form-group">
                    <label>URL Google Drive</label>
                    <input type="url" name="drive_url" class="form-control" required value="{{ old('drive_url', $material->drive_url) }}">
                </div>
                <p class="text-muted small">Link siswa:
                    <a href="{{ route('lms.public.material', $material->share_token) }}" target="_blank">{{ route('lms.public.material', $material->share_token) }}</a>
                </p>
                <div class="form-check">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub"
                        @checked(old('is_published', $material->is_published))>
                    <label class="form-check-label" for="pub">Publikasikan</label>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="{{ route('lms.materials.index') }}" class="btn btn-default">Batal</a>
                </div>
                <form method="POST" action="{{ route('lms.materials.destroy', $material) }}" onsubmit="return confirm('Hapus materi?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </form>
    </div>
@stop
