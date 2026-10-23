@extends('adminlte::page')

@section('title', 'Hasil Import Jadwal')

@section('content_header')
    <h1>Hasil Import Jadwal</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progress Import</h3>
            </div>
            <div class="card-body">
                <div class="progress mb-3">
                    <div class="progress-bar bg-success"
                         role="progressbar"
                         style="width: {{ $result['progress'] }}%"
                         aria-valuenow="{{ $result['progress'] }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $result['progress'] }}%
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Diproses</span>
                                <span class="info-box-number">{{ $result['total'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Berhasil</span>
                                <span class="info-box-number">{{ $result['success'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Gagal</span>
                                <span class="info-box-number">{{ $result['failed'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (! empty($result['errors']))
    <div class="card card-danger">
        <div class="card-header">
            <h3 class="card-title">Pesan Error</h3>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                @foreach ($result['errors'] as $error)
                    <li class="list-group-item">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@else
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> Semua data berhasil diimport tanpa error.
    </div>
@endif

<div class="mb-3">
    <a href="{{ route('import-jadwal.index') }}" class="btn btn-success">
        <i class="fas fa-file-import"></i> Import Lagi
    </a>
    <a href="{{ route('jadwals.index') }}" class="btn btn-secondary">
        <i class="fas fa-calendar-alt"></i> Lihat Jadwal
    </a>
</div>
@stop
