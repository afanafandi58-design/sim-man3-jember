@extends('adminlte::page')

@section('title', 'Tambah Mata Pelajaran')

@section('content_header')<h1>Tambah Mata Pelajaran</h1>@stop

@section('content')
<div class="card">
    <form action="{{ route('mata-pelajaran.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nama Mata Pelajaran</label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                @error('nama')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('mata-pelajaran.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
