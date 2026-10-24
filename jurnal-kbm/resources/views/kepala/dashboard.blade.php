@extends('adminlte::page')

@section('title', 'Monitoring Kepala Madrasah')

@section('content_header')
    <h1>Monitoring Kepala Madrasah</h1>
    <small class="text-muted">{{ $summary['hari'] }}, {{ $summary['tanggal']->format('d F Y') }}</small>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <a href="{{ route('kepala.dashboard.detail', 'guru-all') }}" class="text-dark">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $summary['jumlahGuru'] }}</h3>
                    <p>Jumlah Guru</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-6">
        <a href="{{ route('kepala.dashboard.detail', 'guru-mengajar') }}" class="text-dark">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $summary['guruMengajar'] }}</h3>
                    <p>Guru Mengajar Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-6">
        <a href="{{ route('kepala.dashboard.detail', 'sudah-mengisi') }}" class="text-dark">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['sudahMengisi'] }}</h3>
                    <p>Jurnal Terisi</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </a>
    </div>

    <div class="col-lg-3 col-6">
        <a href="{{ route('kepala.dashboard.detail', 'belum-mengisi') }}" class="text-dark">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['belumMengisi'] }}</h3>
                    <p>Jadwal Belum Diisi</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </a>
    </div>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Klik setiap kartu untuk melihat detail data.
</div>
@stop
