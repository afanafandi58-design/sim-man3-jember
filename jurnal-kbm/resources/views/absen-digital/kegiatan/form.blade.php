@extends('adminlte::page')

@section('title', $kegiatan->exists ? 'Ubah Kegiatan' : 'Kegiatan Baru')

@section('content_header')
    <h1>{{ $kegiatan->exists ? 'Ubah Kegiatan' : 'Kegiatan Baru' }}</h1>
@stop

@section('content')
    @include('absen-digital.partials.alerts')
    <div class="card card-success">
        <form method="POST" action="{{ $kegiatan->exists ? route('absen-digital.kegiatan.update', $kegiatan) : route('absen-digital.kegiatan.store') }}">
            @csrf
            @if ($kegiatan->exists)
                @method('PUT')
            @endif
            <div class="card-body">
                <div class="form-group">
                    <label for="nama">Nama kegiatan</label>
                    <input id="nama" type="text" name="nama" class="form-control" required maxlength="150" value="{{ old('nama', $kegiatan->nama) }}">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="jenis">Jenis</label>
                        <select id="jenis" name="jenis" class="form-control" required>
                            @foreach (\App\Models\AbsenKegiatan::JENIS as $key => $label)
                                <option value="{{ $key }}" @selected(old('jenis', $kegiatan->jenis) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="tanggal">Tanggal</label>
                        <input id="tanggal" type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', $kegiatan->tanggal?->format('Y-m-d')) }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="waktu">Waktu</label>
                        <input id="waktu" type="time" name="waktu" class="form-control" value="{{ old('waktu', $kegiatan->waktu) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="tempat">Tempat</label>
                    <input id="tempat" type="text" name="tempat" class="form-control" maxlength="150" value="{{ old('tempat', $kegiatan->tempat) }}">
                </div>
                <div class="form-group mb-0">
                    <label for="keterangan">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" class="form-control" rows="3" maxlength="2000">{{ old('keterangan', $kegiatan->keterangan) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-lg">Simpan</button>
                <a href="{{ $kegiatan->exists ? route('absen-digital.kegiatan.show', $kegiatan) : route('absen-digital.kegiatan.index') }}" class="btn btn-default btn-lg">Batal</a>
            </div>
        </form>
    </div>
@stop
