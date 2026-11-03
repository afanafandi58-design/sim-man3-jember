@extends('adminlte::page')

@section('title', 'Scan Barcode')

@php
    $peran = ['admin' => 'Admin', 'guru' => 'Guru', 'staff' => 'Staff', 'kepala' => 'Kepala'];
@endphp

@section('content_header')
    <h1>Scan Barcode</h1>
@stop

@section('content')
    @include('absen-digital.partials.alerts')

    <div class="card">
        <div class="card-header"><h3 class="card-title">Kamera atau kode manual</h3></div>
        <div class="card-body">
            <p>Arahkan kamera ke QR kegiatan, atau tempel hasil scan / ketik kode. Keduanya menyimpan kehadiran yang sama.</p>
            <button type="button" class="btn btn-success btn-lg mb-3" id="btn-kamera">Buka kamera</button>
            <p id="scan-status" class="text-muted"></p>
            <video id="scan-video" class="absen-video" playsinline muted></video>
            <div class="form-group mt-3 mb-0">
                <label for="scan-file">Atau pilih gambar QR</label>
                <input id="scan-file" type="file" accept="image/*" capture="environment" class="form-control-file">
            </div>
        </div>
    </div>

    <div class="card card-success">
        <div class="card-header"><h3 class="card-title">Cari kegiatan</h3></div>
        <form method="GET" action="{{ route('absen-digital.scan') }}" id="form-cari-kode">
            <div class="card-body">
                <label for="kode-input">Kode atau tautan hasil scan</label>
                <input id="kode-input" name="kode" class="form-control form-control-lg" value="{{ $kode }}" placeholder="Contoh: AB12CD34" autocomplete="off">
            </div>
            <div class="card-footer">
                <button class="btn btn-success btn-lg" type="submit">Cari</button>
            </div>
        </form>
    </div>

    @if ($kodeTidakDitemukan)
        <div class="alert alert-warning">Kode <strong>{{ $kode }}</strong> tidak ditemukan.</div>
    @endif

    @if ($kegiatan)
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">{{ $kegiatan->nama }}</h3>
            </div>
            <form method="POST" action="{{ route('absen-digital.scan.store') }}">
                @csrf
                <div class="card-body">
                    <p class="mb-1">{{ $kegiatan->jenisLabel() }} · {{ $kegiatan->tanggalLabel() }}{{ $kegiatan->waktu ? ' · '.$kegiatan->waktu : '' }}</p>
                    <p class="text-muted">{{ $kegiatan->tempat }}</p>
                    <input type="hidden" name="kode" value="{{ $kegiatan->kode }}">
                    <div class="form-group">
                        <label for="scan-user">Catat kehadiran untuk</label>
                        <select id="scan-user" name="user_id" class="form-control form-control-lg" required>
                            @foreach ($peserta as $orang)
                                <option value="{{ $orang->id }}" @selected((string) old('user_id', auth()->id()) === (string) $orang->id)>
                                    {{ $orang->name }} ({{ $peran[$orang->role] ?? $orang->role }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Bawaan mengikuti akun yang sedang login. Ganti jika mencatat orang lain.</small>
                    </div>
                    @if ($sudahHadir->isNotEmpty())
                        <p class="mb-1"><strong>Sudah hadir:</strong></p>
                        <ul class="mb-0">
                            @foreach ($sudahHadir as $hadir)
                                <li>{{ $hadir->nama }} <span class="text-muted">{{ $hadir->created_at?->format('H:i') }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="card-footer">
                    <button class="btn btn-success btn-lg btn-block" type="submit">Catat kehadiran</button>
                </div>
            </form>
        </div>
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/absen-digital.css') }}">
@stop

@section('js')
    <script src="{{ asset('js/absen-digital.js') }}"></script>
@stop
