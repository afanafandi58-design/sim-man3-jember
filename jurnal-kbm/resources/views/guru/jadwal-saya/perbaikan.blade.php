@extends('adminlte::page')

@section('title', 'Ajukan Perbaikan Jadwal')

@section('content_header')
    <h1>Ajukan Perbaikan Jadwal</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Detail Jadwal</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Guru:</strong> {{ $guru->nama }}</div>
            <div class="col-md-3"><strong>Kelas:</strong> {{ $jadwal->kelas }}</div>
            <div class="col-md-3"><strong>Mapel:</strong> {{ $jadwal->mapel }}</div>
            <div class="col-md-3"><strong>Hari:</strong> {{ $jadwal->hari }}</div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><strong>Jam Ke:</strong> {{ $jadwal->jam_ke }}</div>
            <div class="col-md-3"><strong>Jam:</strong> {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</div>
            <div class="col-md-3"><strong>TP:</strong> {{ $jadwal->tahun_pelajaran }}</div>
            <div class="col-md-3"><strong>Semester:</strong> {{ $jadwal->semester }}</div>
        </div>
        <p class="text-muted small mt-3 mb-0">
            Jadwal terkunci tidak dapat diedit langsung. Ajukan perbaikan agar admin meninjau dan memperbaiki data.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Permintaan Perbaikan</h3>
    </div>

    <form action="{{ route('guru.jadwal-saya.perbaikan.store', $jadwal) }}" method="POST">
        @csrf

        <div class="card-body">
            <div class="form-group">
                <label>Alasan Perbaikan <span class="text-danger">*</span></label>
                <textarea name="alasan" class="form-control" rows="5"
                          placeholder="Jelaskan data jadwal mana yang perlu diperbaiki dan alasan perubahannya..."
                          required>{{ old('alasan') }}</textarea>
                @error('alasan')<small class="text-danger">{{ $message }}</small>@enderror
                <small class="text-muted">Minimal 10 karakter.</small>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-paper-plane"></i> Ajukan Perbaikan
            </button>
            <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@stop
