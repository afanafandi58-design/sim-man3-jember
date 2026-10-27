@extends('adminlte::page')

@section('title', 'Tambah Jam Pelajaran')

@section('content_header')<h1>Tambah Jam Pelajaran</h1>@stop

@section('content')
<div class="card">
    <form action="{{ route('jam-pelajaran.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Pola Jadwal</label>
                <select name="pola_jadwal_id" class="form-control" required>
                    <option value="">-- Pilih Pola --</option>
                    @foreach($polaOptions as $pola)
                        <option value="{{ $pola->id }}" {{ old('pola_jadwal_id') == $pola->id ? 'selected' : '' }}>
                            {{ $pola->label }}
                        </option>
                    @endforeach
                </select>
                @error('pola_jadwal_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Jam Ke</label>
                <input type="number" name="jam_ke" class="form-control" min="0" value="{{ old('jam_ke') }}" required>
                @error('jam_ke')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Nama Sesi</label>
                <input type="text" name="nama_sesi" class="form-control"
                       value="{{ old('nama_sesi') }}" placeholder="Pembiasaan, Tahfidz, Tadarus, dll.">
                @error('nama_sesi')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required>
                @error('jam_mulai')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label>Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required>
                @error('jam_selesai')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('jam-pelajaran.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
