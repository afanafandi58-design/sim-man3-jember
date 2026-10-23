@extends('adminlte::page')

@section('title', 'Tambah Jadwal')

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
<div class="card">

    <div class="card-header">
        <h3 class="card-title">Form Tambah Jadwal</h3>
    </div>

    <form action="{{ route('jadwals.store') }}" method="POST">
        @csrf

        <div class="card-body">

            {{-- Guru --}}
            <div class="form-group">
                <label>Guru</label>

                <select name="guru_id" class="form-control">
                    <option value="">-- Pilih Guru --</option>

                    @foreach($gurus as $guru)
                        <option value="{{ $guru->id }}"
                            {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama }} - {{ $guru->mapel }}
                        </option>
                    @endforeach

                </select>

               
            </div>

            {{-- Kelas --}}
            <div class="form-group">
                <label>Kelas</label>

                <input type="text"
                       name="kelas"
                       class="form-control"
                       value="{{ old('kelas') }}">

                @error('kelas')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Mapel --}}
            <div class="form-group">
                <label>Mata Pelajaran</label>

                <input type="text"
                       name="mapel"
                       class="form-control"
                       value="{{ old('mapel') }}">

                @error('mapel')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Hari --}}
            <div class="form-group">
                <label>Hari</label>

                <select name="hari" id="hari-select" class="form-control">

                    <option value="">-- Pilih Hari --</option>

                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                        <option value="{{ $hari }}"
                            {{ old('hari') == $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach

                </select>

                @error('hari')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            @include('partials.jadwal-jam-range-fields', [
                'selectedJamKe' => old('jam_ke'),
                'selectedJamKeAkhir' => old('jam_ke_akhir', old('jam_ke')),
                'polaHariMap' => $polaHariMap ?? [],
                'jamMapByPola' => $jamMapByPola ?? [],
                'hariSelectId' => 'hari-select',
                'showPolaDisplay' => true,
            ])

            {{-- Tahun Pelajaran --}}
            <div class="form-group">
                <label>Tahun Pelajaran</label>

                <input type="text"
                       name="tahun_pelajaran"
                       class="form-control"
                       placeholder="2026/2027"
                       value="{{ old('tahun_pelajaran') }}">

                @error('tahun_pelajaran')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Semester --}}
            <div class="form-group">
                <label>Semester</label>

                <select name="semester" class="form-control">

                    <option value="Ganjil"
                        {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>
                        Ganjil
                    </option>

                    <option value="Genap"
                        {{ old('semester') == 'Genap' ? 'selected' : '' }}>
                        Genap
                    </option>

                </select>

                @error('semester')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
            </button>

            <a href="{{ route('jadwals.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </form>

</div>

@stop