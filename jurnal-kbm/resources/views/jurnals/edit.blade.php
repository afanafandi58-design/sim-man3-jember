@extends('adminlte::page')

@section('title', 'Edit Jurnal')

@section('content_header')
    <h1>Edit Jurnal KBM</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <h3 class="card-title">Form Edit Jurnal #{{ $jurnal->id }}</h3>
    </div>

    <form action="{{ route('jurnals.update', $jurnal) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group mb-3">
                <label>Jadwal</label>
                <select id="jadwal_id" name="jadwal_id" class="form-control" required>
                    <option value="">-- Pilih Jadwal Mengajar --</option>
                    @foreach($jadwals as $jadwal)
                        <option value="{{ $jadwal->id }}" @selected(old('jadwal_id', $jurnal->jadwal_id) == $jadwal->id)>
                            {{ $jadwal->kelas }} | {{ $jadwal->mapel }} | {{ $jadwal->guru->nama ?? '-' }} | {{ $jadwal->hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control"
                       value="{{ old('tanggal', \Carbon\Carbon::parse($jurnal->tanggal)->format('Y-m-d')) }}" required>
            </div>

            <div class="form-group mb-3">
                <label>Jam Ke</label>
                <input type="number" name="jam_ke" class="form-control"
                       value="{{ old('jam_ke', $jurnal->jam_ke) }}" required>
            </div>

            <div class="form-group mb-3">
                <label>Materi</label>
                <textarea name="materi" rows="4" class="form-control" required>{{ old('materi', $jurnal->materi) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label>Hadir</label>
                    <input type="number" name="hadir" class="form-control" value="{{ old('hadir', $jurnal->hadir) }}">
                </div>
                <div class="col-md-3">
                    <label>Izin</label>
                    <input type="number" name="izin" class="form-control" value="{{ old('izin', $jurnal->izin) }}">
                </div>
                <div class="col-md-3">
                    <label>Sakit</label>
                    <input type="number" name="sakit" class="form-control" value="{{ old('sakit', $jurnal->sakit) }}">
                </div>
                <div class="col-md-3">
                    <label>Alpha</label>
                    <input type="number" name="alpha" class="form-control" value="{{ old('alpha', $jurnal->alpha) }}">
                </div>
            </div>

            <div class="form-group mt-3">
                <label>Catatan</label>
                <textarea name="catatan" rows="3" class="form-control">{{ old('catatan', $jurnal->catatan) }}</textarea>
            </div>

            @include('guru.jurnal.partials.lampiran-foto-form', ['jurnal' => $jurnal])
        </div>

        <div class="card-footer">
            <button class="btn btn-success" type="submit">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('jurnals.show', $jurnal) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
