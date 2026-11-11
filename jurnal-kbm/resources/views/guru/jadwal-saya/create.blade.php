@extends('adminlte::page')

@section('title', 'Tambah Jadwal Saya')

@section('content_header')
    <h1>Tambah Jadwal</h1>
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

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Data Guru (Otomatis)</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="text-muted small">NIP</label>
                <p class="font-weight-bold">{{ $guru->nip ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small">Nama Guru</label>
                <p class="font-weight-bold">{{ $guru->nama }}</p>
            </div>
            <div class="col-md-4">
                <label class="text-muted small">Mata Pelajaran Utama</label>
                <p class="font-weight-bold">{{ $guru->mapel ?? '-' }}</p>
            </div>
        </div>
        <p class="text-muted small mb-0">
            <i class="fas fa-info-circle"></i>
            Data guru diambil otomatis dari akun login. Kelas, mapel, dan jam diambil dari master data.
            Setelah disimpan, jadwal langsung berstatus <strong>TERKUNCI</strong>.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Jadwal</h3>
    </div>

    <form action="{{ route('guru.jadwal-saya.store') }}" method="POST">
        @csrf

        <div class="card-body">
            @include('partials.jadwal-form-masters', [
                'selectedMapel' => old('mapel', $guru->mapel),
            ])

            <div class="form-group">
                <label>Tahun Pelajaran</label>
                <input type="text" name="tahun_pelajaran" class="form-control"
                       placeholder="2026/2027"
                       value="{{ old('tahun_pelajaran', \App\Services\GuruService::DEFAULT_TAHUN_PELAJARAN) }}" required>
                @error('tahun_pelajaran')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester" class="form-control" required>
                    <option value="Ganjil" {{ old('semester', 'Ganjil') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
                @error('semester')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@stop
