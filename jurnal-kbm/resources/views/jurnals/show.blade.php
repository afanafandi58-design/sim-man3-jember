@extends('adminlte::page')

@section('title', 'Detail Jurnal KBM')

@section('content_header')
    <h1>Detail Jurnal KBM</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Informasi Jurnal</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th width="180">Tanggal</th>
                <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d F Y') }}</td>
            </tr>
            <tr>
                <th>Guru Pengampu</th>
                <td>{{ $jurnal->jadwal->guru->nama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Guru Pengganti</th>
                <td>{{ $jurnal->guru_pengganti ?: '-' }}</td>
            </tr>
            <tr>
                <th>Mata Pelajaran</th>
                <td>{{ $jurnal->jadwal->mapel ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kelas</th>
                <td>{{ $jurnal->jadwal->kelas ?? '-' }}</td>
            </tr>
            <tr>
                <th>Jam Ke</th>
                <td>{{ $jurnal->jam_ke }}</td>
            </tr>
            <tr>
                <th>Materi</th>
                <td>{{ $jurnal->materi }}</td>
            </tr>
            <tr>
                <th>Status KBM</th>
                <td>{{ $jurnal->status_kbm }}</td>
            </tr>
            <tr>
                <th>Catatan</th>
                <td>{{ $jurnal->catatan ?: '-' }}</td>
            </tr>
        </table>

        @include('guru.jurnal.partials.absensi-detail')

        @include('guru.jurnal.partials.lampiran-foto-gallery')
    </div>
    <div class="card-footer">
        <a href="{{ route('jurnals.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('jurnals.edit', $jurnal) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>
</div>
@stop
