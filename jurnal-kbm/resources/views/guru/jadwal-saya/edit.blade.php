@extends('adminlte::page')

@section('title', 'Ubah Jadwal Saya')

@section('content_header')
    <h1>Ubah Jadwal</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info">
    <i class="fas fa-edit"></i>
    Jadwal <strong>terbuka</strong> — perubahan langsung tersimpan. Admin memantau lewat menu <em>Monitor Perubahan Jadwal</em>.
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-body py-2">
        <strong>{{ $guru->nama }}</strong>
        <span class="text-muted">— {{ $jadwal->kelas }} / {{ $jadwal->mapel }} / {{ $jadwal->hari }}</span>
    </div>
</div>

<div class="card">
    <form action="{{ route('guru.jadwal-saya.update', $jadwal) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            @include('partials.jadwal-form-masters', [
                'selectedKodeKelas' => $selectedKodeKelas,
                'selectedMapel' => $selectedMapel,
                'selectedJamKe' => $selectedJamKe,
                'selectedJamKeAkhir' => $selectedJamKeAkhir,
                'selectedHari' => $selectedHari,
                'showMapelLainnya' => false,
                'polaHariMap' => $polaHariMap,
                'jamMapByPola' => $jamMapByPola,
            ])

            <div class="form-group">
                <label>Tahun Pelajaran</label>
                <input type="text" name="tahun_pelajaran" class="form-control" required
                       value="{{ old('tahun_pelajaran', $jadwal->tahun_pelajaran) }}">
            </div>
            <div class="form-group">
                <label>Semester</label>
                <select name="semester" class="form-control" required>
                    <option value="Ganjil" @selected(old('semester', $jadwal->semester) === 'Ganjil')>Ganjil</option>
                    <option value="Genap" @selected(old('semester', $jadwal->semester) === 'Genap')>Genap</option>
                </select>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <div>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Perubahan</button>
                <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </form>
</div>

@php
    $deleteCheck = app(\App\Services\AdminJadwalService::class)->canDelete($jadwal);
@endphp
@if ($deleteCheck['allowed'])
    <form action="{{ route('guru.jadwal-saya.destroy', $jadwal) }}" method="POST" class="mt-3"
          onsubmit="return confirm('Hapus jadwal ini?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Hapus jadwal</button>
    </form>
@else
    <p class="text-muted small mt-3">{{ $deleteCheck['reason'] }}</p>
@endif

@stop
