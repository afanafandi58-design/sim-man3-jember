@extends('adminlte::page')

@section('title', 'Edit Pengumuman')

@section('content_header')<h1>Edit Pengumuman</h1>@stop

@section('content')
    @include('lms.partials.flash')
    <div class="card">
        <form method="POST" action="{{ route('lms.announcements.update', $announcement) }}">@csrf @method('PUT')
            <div class="card-body">
                <div class="form-group"><label>Scope</label>
                    <select name="scope" class="form-control"><option value="all" @selected($announcement->scope==='all')>Semua</option><option value="kelas" @selected($announcement->scope==='kelas')>Kelas</option></select>
                </div>
                <div class="form-group"><label>Kelas</label><input name="kelas" class="form-control" value="{{ old('kelas', $announcement->kelas) }}"></div>
                <div class="form-group"><label>Judul</label><input name="judul" class="form-control" required value="{{ old('judul', $announcement->judul) }}"></div>
                <div class="form-group"><label>Isi</label><textarea name="isi" class="form-control" rows="5" required>{{ old('isi', $announcement->isi) }}</textarea></div>
                <div class="form-group"><label>URL Drive</label><input type="url" name="drive_url" class="form-control" value="{{ old('drive_url', $announcement->drive_url) }}"></div>
                <div class="form-check"><input type="checkbox" name="is_active" value="1" @checked($announcement->is_active)><label>Aktif</label></div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button class="btn btn-primary">Simpan</button>
                <form method="POST" action="{{ route('lms.announcements.destroy', $announcement) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-danger">Hapus</button></form>
            </div>
        </form>
    </div>
@stop
