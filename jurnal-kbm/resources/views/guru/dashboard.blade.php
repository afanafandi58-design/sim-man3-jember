@extends('adminlte::page')

@section('title', 'Dashboard Guru')

@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap">

    <div>

        <h1 class="mb-0 font-weight-bold text-success">

            <i class="fas fa-chalkboard-teacher mr-2"></i>

            Dashboard Guru

        </h1>

        <small class="text-muted">

            SIM MAN 3 Jember

        </small>

    </div>

    <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">

        <li class="breadcrumb-item">

            <a href="#">

                <i class="fas fa-home"></i>

                Home

            </a>

        </li>

        <li class="breadcrumb-item active">

            Dashboard Guru

        </li>

    </ol>

</div>

@stop

@section('content')

@include('guru.partials.uat-watermark')
@include('guru.partials.operational-date-badge')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@php

$jam = now()->format('H');

if ($jam < 11) {

    $salam = 'Selamat Pagi';

} elseif ($jam < 15) {

    $salam = 'Selamat Siang';

} elseif ($jam < 18) {

    $salam = 'Selamat Sore';

} else {

    $salam = 'Selamat Malam';

}

@endphp

<div class="row">

    {{-- ========================= PROFIL GURU ========================= --}}

    <div class="col-lg-8">

        <div class="card card-success shadow">

            <div class="card-header border-0">

                <h3 class="card-title font-weight-bold">

                    <i class="fas fa-user-circle mr-2"></i>

                    Profil Guru

                </h3>

            </div>

            <div class="card-body">

                <div class="row align-items-center">

                    {{-- ================= FOTO ================= --}}

                    <div class="col-md-3 text-center mb-3 mb-md-0">

                        <div class="mb-3">

                            <i class="fas fa-user-circle text-success"

                               style="font-size:110px;"></i>

                        </div>

                        <span class="badge badge-success px-3 py-2">

                            Guru Aktif

                        </span>

                    </div>

                    {{-- ================= IDENTITAS ================= --}}

                    <div class="col-md-9">

                        <h3 class="font-weight-bold text-success mb-1">

                            {{ strtoupper($guru->nama) }}

                        </h3>

                        <p class="text-muted mb-3">

                            {{ $salam }},

                            semoga aktivitas mengajar hari ini berjalan lancar.

                        </p>

                        <hr>

                        <div class="row">

                            <div class="col-md-6">

                                <table class="table table-borderless table-sm mb-0">

                                    <tr>

                                        <th width="130">

                                            <i class="fas fa-calendar-day text-success mr-2"></i>

                                            Hari

                                        </th>

                                        <td>{{ $hari }}</td>

                                    </tr>

                                    <tr>

                                        <th>

                                            <i class="fas fa-calendar text-success mr-2"></i>

                                            Tanggal

                                        </th>

                                        <td>{{ $operationalDate['tanggalSistem'] ?? $hari }}</td>

                                    </tr>

                                    <tr>

                                        <th>

                                            <i class="fas fa-layer-group text-success mr-2"></i>

                                            Semester

                                        </th>

                                        <td>{{ $operationalDate['semester'] ?? 'Ganjil' }}</td>

                                    </tr>

                                    <tr>

                                        <th>

                                            <i class="fas fa-school text-success mr-2"></i>

                                            TP

                                        </th>

                                        <td>{{ str_replace('/', ' / ', $operationalDate['tahunPelajaran'] ?? '2026/2027') }}</td>

                                    </tr>

                                </table>

                            </div>

                            <div class="col-md-6 text-center">

                                <div class="border rounded p-3 bg-light">

                                    <small class="text-muted d-block">

                                        Waktu Saat Ini

                                    </small>

                                    <h2 id="clock"

                                        class="font-weight-bold text-success mb-0">

                                    </h2>

                                    <small class="text-muted">

                                        WIB

                                    </small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================= AKTIVITAS ========================= --}}

<div class="col-lg-4">

    <div class="card card-success shadow h-100">

        <div class="card-header border-0">

            <h3 class="card-title font-weight-bold">

                <i class="fas fa-history mr-2"></i>

                Aktivitas Hari Ini

            </h3>

        </div>

        <div class="card-body">

            <div class="text-center mb-4">

                <i class="fas fa-clipboard-check text-success"

                    style="font-size:65px;"></i>

            </div>

            @if($sudahIsi > 0)

                <div class="alert alert-success">

                    <i class="fas fa-check-circle mr-2"></i>

                    Anda telah mengisi

                    <strong>{{ $sudahIsi }}</strong>

                    jurnal hari ini.

                </div>

            @else

                <div class="alert alert-warning">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    Anda belum mengisi jurnal hari ini.

                </div>

            @endif

            <table class="table table-borderless table-sm">

                <tr>

                    <th width="45%">

                        Jadwal Hari Ini

                    </th>

                    <td>

                        {{ $jadwalHariIni }}

                    </td>

                </tr>

                <tr>

                    <th>

                        Sudah Mengisi

                    </th>

                    <td>

                        {{ $sudahIsi }}

                    </td>

                </tr>

                <tr>

                    <th>

                        Belum Mengisi

                    </th>

                    <td>

                        {{ $belumIsi }}

                    </td>

                </tr>

            </table>

        </div>

    </div>

</div>

</div>

{{-- ========================= KPI ========================= --}}

@php
    $persen = $jadwalHariIni > 0
        ? round(($sudahIsi / $jadwalHariIni) * 100)
        : 0;
@endphp

<div class="row mt-3">

    {{-- Jadwal Hari Ini --}}
    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-primary shadow-sm kpi-card">

            <div class="inner">

                <h3 class="counter">{{ $jadwalHariIni }}</h3>

                <h6 class="font-weight-bold mb-1">
                    Jadwal Hari Ini
                </h6>

                <small>
                    {{ $hari }}
                </small>

            </div>

            <div class="icon">

                <i class="fas fa-calendar-alt"></i>

            </div>

        </div>

    </div>

    {{-- Sudah Mengisi --}}
    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-success shadow-sm kpi-card">

            <div class="inner">

                <h3 class="counter">{{ $sudahIsi }}</h3>

                <h6 class="font-weight-bold mb-1">
                    Sudah Mengisi
                </h6>

                <small>

                    {{ $persen }}% selesai

                </small>

            </div>

            <div class="icon">

                <i class="fas fa-check-circle"></i>

            </div>

        </div>

    </div>

    {{-- Belum Mengisi --}}
    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-warning shadow-sm kpi-card">

            <div class="inner">

                <h3 class="counter">{{ $belumIsi }}</h3>

                <h6 class="font-weight-bold mb-1">
                    Belum Mengisi
                </h6>

                <small>

                    Segera Diselesaikan

                </small>

            </div>

            <div class="icon">

                <i class="fas fa-pencil-alt"></i>

            </div>

        </div>

    </div>

    {{-- Total Jurnal --}}
    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-danger shadow-sm kpi-card">

            <div class="inner">

                <h3 class="counter">{{ $totalJurnal }}</h3>

                <h6 class="font-weight-bold mb-1">
                    Total Jurnal
                </h6>

                <small>

                    Semester Ganjil

                </small>

            </div>

            <div class="icon">

                <i class="fas fa-book-open"></i>

            </div>

        </div>

    </div>

</div>


<div class="row mt-3">

{{-- ==================== JADWAL MENGAJAR ==================== --}}
<div class="col-lg-8">

    <div class="card card-success shadow">

        <div class="card-header border-0">

            <h3 class="card-title font-weight-bold">

                <i class="fas fa-book-reader mr-2"></i>

                Jadwal Mengajar Hari Ini

            </h3>

        </div>

        <div class="card-body">

        @forelse($jadwals as $jadwal)

<div class="card card-outline card-success mb-3 shadow-sm">

    <div class="card-body">

        <div class="row">

            <div class="col-md-9">

                <h5 class="font-weight-bold text-success mb-3">

                    <i class="fas fa-book-open mr-2"></i>

                    {{ strtoupper($jadwal->mapel) }}

                </h5>

                <div class="row">

                    <div class="col-md-4 mb-2">

                        <small class="text-muted">

                            <i class="fas fa-users mr-1 text-success"></i>

                            Kelas

                        </small>

                        <h6 class="mb-0 font-weight-bold">

                            {{ strtoupper($jadwal->kelas) }}

                        </h6>

                    </div>

                    <div class="col-md-4 mb-2">

                        <small class="text-muted">

                            <i class="far fa-clock mr-1 text-success"></i>

                            Jam Mengajar

                        </small>

                        <h6 class="mb-0 font-weight-bold">

                            {{ substr($jadwal->jam_mulai,0,5) }}

                            -

                            {{ substr($jadwal->jam_selesai,0,5) }}

                        </h6>

                    </div>

                    <div class="col-md-4 mb-2">

                        <small class="text-muted">

                            <i class="fas fa-info-circle mr-1 text-success"></i>

                            Status

                        </small>

                        <br>

                        @if($jadwal->sudah_diisi)

                            <span class="badge badge-success px-3 py-2">

                                <i class="fas fa-check mr-1"></i>

                                Sudah Mengisi

                            </span>

                        @else

                            <span class="badge badge-warning px-3 py-2">

                                <i class="fas fa-edit mr-1"></i>

                                Belum Mengisi

                            </span>

                        @endif

                    </div>

                </div>

            </div>

            <div class="col-md-3 d-flex align-items-center">

                @if($jadwal->sudah_diisi)

                    <a href="{{ route('guru.jurnal.edit', $jadwal->jurnal_id) }}"
                        class="btn btn-success btn-block mr-1">

                        <i class="fas fa-eye mr-1"></i>

                        Lihat Jurnal

                    </a>

                @else

                    <a href="{{ route('guru.jurnal.create',$jadwal->id) }}"

                        class="btn btn-primary btn-block">

                        <i class="fas fa-pen mr-1"></i>

                        Isi Jurnal

                    </a>

                @endif

            </div>

        </div>

    </div>

</div>

@empty

<div class="alert alert-warning">

    <i class="fas fa-info-circle mr-2"></i>

    Tidak ada jadwal mengajar hari ini.

</div>

@endforelse

        </div>

    </div>

</div>

{{-- ==================== PROGRESS ==================== --}}

<div class="col-lg-4">

    <div class="card card-success shadow-sm">

        <div class="card-header border-0">

            <h3 class="card-title font-weight-bold">

                <i class="fas fa-chart-pie mr-2"></i>

                Progress Mengajar Hari Ini

            </h3>

        </div>

        <div class="card-body">

            {{-- Persentase --}}
            <div class="text-center mb-4">

                <div class="mb-2">

                    <h1 class="display-4 font-weight-bold text-success counter">

                        {{ $persen }}%

                    </h1>

                </div>

                <small class="text-muted">

                    Progress Pengisian Jurnal Hari Ini

                </small>

            </div>

            {{-- Progress Bar --}}
            <div class="progress mb-4"
                 style="height:22px;border-radius:15px;">

                <div class="progress-bar bg-success"

                     role="progressbar"

                     style="width: {{ $persen }}%;">

                </div>

            </div>

            <div class="row text-center">

                <div class="col-4">

                    <div class="border rounded p-2">

                        <h4 class="text-primary font-weight-bold mb-1">

                            {{ $jadwalHariIni }}

                        </h4>

                        <small class="text-muted">

                            Jadwal

                        </small>

                    </div>

                </div>

                <div class="col-4">

                    <div class="border rounded p-2">

                        <h4 class="text-success font-weight-bold mb-1">

                            {{ $sudahIsi }}

                        </h4>

                        <small class="text-muted">

                            Selesai

                        </small>

                    </div>

                </div>

                <div class="col-4">

                    <div class="border rounded p-2">

                        <h4 class="text-warning font-weight-bold mb-1">

                            {{ $belumIsi }}

                        </h4>

                        <small class="text-muted">

                            Belum

                        </small>

                    </div>

                </div>

            </div>

            <hr>

            @if($belumIsi > 0)

                <div class="alert alert-warning mb-0">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    Masih ada

                    <strong>{{ $belumIsi }}</strong>

                    jurnal yang belum diisi.

                </div>

            @else

                <div class="alert alert-success mb-0">

                    <i class="fas fa-check-circle mr-2"></i>

                    Seluruh jurnal hari ini telah selesai diisi.

                </div>

            @endif

        </div>

    </div>

</div>

</div>

{{-- ==================== RIWAYAT JURNAL ==================== --}}

<div class="row mt-4 mb-3">

    <div class="col-lg-12">

        <div class="card card-success shadow-sm" id="riwayat-jurnal">

            <div class="card-header border-0">

                <h3 class="card-title font-weight-bold">

                    <i class="fas fa-history mr-2"></i>

                    Riwayat Jurnal Terbaru

                </h3>

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="bg-light">

                            <tr>

                                <th width="120">Tanggal</th>

                                <th>Kelas</th>

                                <th>Mata Pelajaran</th>

                                <th>Materi</th>

                                <th width="120">Status</th>
                                <th width="170" class="text-center">Aksi</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($riwayatJurnal as $jurnal)

                            <tr>

                                <td>

                                    {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d-m-Y') }}

                                </td>

                                <td>

                                    {{ $jurnal->jadwal->kelas }}

                                </td>

                                <td>

                                    {{ $jurnal->jadwal->mapel }}

                                </td>

                                <td>

                                    {{ \Illuminate\Support\Str::limit($jurnal->materi,60) }}

                                </td>

                                <td>

<span class="badge badge-success">

    Tersimpan

</span>

</td>

<td class="text-center">

    <a href="{{ route('guru.jurnal.edit', $jurnal->id) }}"
       class="btn btn-warning btn-sm mb-1">

        <i class="fas fa-edit"></i>

        Edit

    </a>

    {{-- Preview PDF --}}
    <a href="{{ route('guru.jurnal.pdf', $jurnal->id) }}"
       target="_blank"
       class="btn btn-info btn-sm mb-1">

        <i class="fas fa-eye"></i>

        Preview

    </a>

    {{-- Download PDF --}}
    <a href="{{ route('guru.jurnal.pdf', $jurnal->id) }}?download=1"
       class="btn btn-danger btn-sm">

        <i class="fas fa-download"></i>

        Download

    </a>

</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center text-muted p-4">

                                    <i class="fas fa-folder-open fa-2x mb-3"></i>

                                    <br>

                                    Belum ada jurnal yang pernah diisi.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@stop

@section('css')

<style>

.kpi-card{

    transition:.25s ease;

    border-radius:10px;

}

.kpi-card:hover{

    transform:translateY(-4px);

    box-shadow:0 .6rem 1rem rgba(0,0,0,.18)!important;

}

.kpi-card .icon{

    opacity:.18;

    transition:.25s;

}

.kpi-card:hover .icon{

    opacity:.28;

}

.kpi-card h3{

    font-weight:700;

}

.kpi-card small{

    color:rgba(255,255,255,.9);

}

</style>

@stop

@section('js')

<script>

function updateClock(){

    const now = new Date();

    const jam = String(now.getHours()).padStart(2,'0');
    const menit = String(now.getMinutes()).padStart(2,'0');
    const detik = String(now.getSeconds()).padStart(2,'0');

    const clock = document.getElementById('clock');

    if(clock){

        clock.innerHTML = jam + ":" + menit + ":" + detik;

    }

}

updateClock();

setInterval(updateClock,1000);



document.addEventListener('DOMContentLoaded',function(){

    const counters=document.querySelectorAll('.counter');

    counters.forEach(counter=>{

        const target=parseInt(counter.innerText);

        let number=0;

        const speed=Math.max(1,Math.ceil(target/25));

        const interval=setInterval(()=>{

            number+=speed;

            if(number>=target){

                counter.innerText=target;

                clearInterval(interval);

            }else{

                counter.innerText=number;

            }

        },20);

    });

});

</script>

@stop
