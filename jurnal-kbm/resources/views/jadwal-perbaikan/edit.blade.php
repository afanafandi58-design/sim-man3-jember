@extends('adminlte::page')

@section('title', 'Ubah Jadwal (Perbaikan)')

@section('content_header')
    <h1>Ubah Jadwal — Permintaan Perbaikan</h1>
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

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php $jadwal = $jadwalPerbaikan->jadwal; @endphp

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Informasi Permintaan</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>Guru:</strong> {{ $jadwalPerbaikan->guru->nama ?? '-' }}</div>
            <div class="col-md-4"><strong>NIP:</strong> {{ $jadwalPerbaikan->guru->nip ?? '-' }}</div>
            <div class="col-md-4"><strong>Status:</strong> <span class="badge badge-success">Disetujui — Edit Sementara</span></div>
        </div>
        <div class="mt-2">
            <strong>Alasan Guru:</strong>
            <p class="mb-0">{{ $jadwalPerbaikan->alasan }}</p>
        </div>
        <p class="text-muted small mt-3 mb-0">
            Setelah disimpan, jadwal akan <strong>terkunci kembali</strong> secara otomatis.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Ubah Jadwal</h3>
    </div>

    <form action="{{ route('jadwal-perbaikan.update', $jadwalPerbaikan) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="form-group">
                <label>Guru</label>
                <input type="text" class="form-control" readonly
                       value="{{ $jadwal->guru->nama ?? '-' }} — {{ $jadwal->guru->nip ?? '-' }}">
            </div>

            @include('partials.jadwal-form-masters', [
                'selectedKodeKelas' => old('kode_kelas', $selectedKodeKelas),
                'selectedMapel' => old('mapel', $selectedMapel),
                'selectedJamKe' => old('jam_ke', $selectedJamKe),
                'selectedJamKeAkhir' => old('jam_ke_akhir', $selectedJamKeAkhir ?? $selectedJamKe),
                'selectedHari' => old('hari', $selectedHari ?? $jadwal->hari),
                'selectedJamMulai' => substr((string) $jadwal->jam_mulai, 0, 5),
                'selectedJamSelesai' => substr((string) $jadwal->jam_selesai, 0, 5),
                'showMapelLainnya' => false,
            ])

            <div class="form-group">
                <label>Tahun Pelajaran</label>
                <input type="text" name="tahun_pelajaran" class="form-control"
                       value="{{ old('tahun_pelajaran', $jadwal->tahun_pelajaran) }}" required>
                @error('tahun_pelajaran')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester" class="form-control" required>
                    <option value="Ganjil" {{ old('semester', $jadwal->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="Genap" {{ old('semester', $jadwal->semester) == 'Genap' ? 'selected' : '' }}>Genap</option>
                </select>
                @error('semester')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan & Kunci Kembali
            </button>
            <a href="{{ route('jadwal-perbaikan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@stop
