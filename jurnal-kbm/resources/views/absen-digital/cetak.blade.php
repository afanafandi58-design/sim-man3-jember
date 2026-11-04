@extends('adminlte::page')

@section('title', 'Cetak Absen Digital')

@section('content_header')
    <h1>Cetak Absen</h1>
@stop

@section('content')
    @include('absen-digital.partials.alerts')
    <div class="card card-success">
        <div class="card-header"><h3 class="card-title">Siapkan halaman cetak</h3></div>
        <form method="GET" action="{{ route('absen-digital.cetak.halaman') }}" target="_blank">
            <div class="card-body">
                @include('absen-digital.partials.filter-fields', ['filters' => $filters, 'semuaKegiatan' => $semuaKegiatan])
                <p class="text-muted mb-0">Halaman yang terbuka memakai tata letak cetak. Gunakan menu cetak browser (Ctrl+P atau menu Android).</p>
            </div>
            <div class="card-footer">
                <button class="btn btn-success btn-lg" type="submit"><i class="fas fa-print"></i> Buka halaman cetak</button>
            </div>
        </form>
    </div>
@stop
