@extends('adminlte::page')

@section('title', 'Ringkasan Absen Digital')

@section('content_header')
    <h1>Ringkasan Absen</h1>
@stop

@section('content')
    @include('absen-digital.partials.alerts')
    @include('absen-digital.partials.filter', ['filters' => $filters, 'semuaKegiatan' => $semuaKegiatan, 'action' => route('absen-digital.ringkasan')])

    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalHadir }}</h3>
                    <p>Total hadir</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalKegiatan }}</h3>
                    <p>Kegiatan</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body py-2">
                    <strong>Per jenis</strong>
                    <ul class="mb-0 pl-3">
                        @forelse ($perJenis as $jenis => $jumlah)
                            <li>{{ \App\Models\AbsenKegiatan::JENIS[$jenis] ?? $jenis }}: {{ $jumlah }}</li>
                        @empty
                            <li class="text-muted">Tidak ada data pada filter ini.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Kegiatan</th>
                        <th class="text-right">Hadir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kegiatans as $kegiatan)
                        <tr>
                            <td>{{ $kegiatan->tanggalLabel() }}</td>
                            <td>{{ $kegiatan->jenisLabel() }}</td>
                            <td>{{ $kegiatan->nama }}</td>
                            <td class="text-right">{{ $kegiatan->kehadirans_count }}</td>
                            <td class="text-right text-nowrap">
                                <a class="btn btn-xs btn-primary" href="{{ route('absen-digital.kegiatan.show', $kegiatan) }}">Detail</a>
                                <a class="btn btn-xs btn-default" href="{{ route('absen-digital.cetak.halaman', ['kegiatan_id' => $kegiatan->id]) }}" target="_blank" rel="noopener">Cetak</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada kegiatan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
