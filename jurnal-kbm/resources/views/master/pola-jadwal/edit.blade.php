@extends('adminlte::page')

@section('title', 'Edit Pola Jadwal')

@section('content_header')<h1>Edit Pola Jadwal</h1>@stop

@section('content')
<div class="card">
    <form action="{{ route('pola-jadwal.update', $polaJadwal) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" class="form-control" value="{{ $polaJadwal->kode }}" readonly>
                <small class="text-muted">Kode tidak dapat diubah agar relasi jadwal tetap aman.</small>
            </div>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $polaJadwal->nama) }}" required>
                @error('nama')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="aktif" {{ old('status', $polaJadwal->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status', $polaJadwal->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('pola-jadwal.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
