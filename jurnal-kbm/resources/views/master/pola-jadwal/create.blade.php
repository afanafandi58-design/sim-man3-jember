@extends('adminlte::page')

@section('title', 'Tambah Pola Jadwal')

@section('content_header')<h1>Tambah Pola Jadwal</h1>@stop

@section('content')
<div class="card">
    <form action="{{ route('pola-jadwal.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" name="kode" class="form-control" maxlength="10" value="{{ old('kode') }}" required placeholder="REG, JUM, RAM">
                @error('kode')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                @error('nama')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('pola-jadwal.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
