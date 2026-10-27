@extends('adminlte::page')

@section('title', 'Edit Mata Pelajaran')

@section('content_header')<h1>Edit Mata Pelajaran</h1>@stop

@section('content')
<div class="card">
    <form action="{{ route('mata-pelajaran.update', $mataPelajaran) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nama Mata Pelajaran</label>
                <input type="text" name="nama" class="form-control"
                       value="{{ old('nama', $mataPelajaran->nama) }}" required>
                @error('nama')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $mataPelajaran->is_active) ? 'checked' : '' }}>
                    Aktif
                </label>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('mata-pelajaran.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
