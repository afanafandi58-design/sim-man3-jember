@extends('adminlte::page')

@section('title', 'Import Data')

@section('content_header')
    <h1>Import Data</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="callout callout-info">
            <h5><i class="fas fa-info-circle"></i> Alur Setup Data</h5>
            <p class="mb-0">
                Ikuti langkah berurutan di bawah untuk menyiapkan SIM MAN 3 Jember.
                Progress: <strong>{{ $completed_steps }}/{{ $total_steps }}</strong> langkah selesai.
            </p>
        </div>
    </div>
</div>

<div class="row">
    @foreach ($steps as $step)
        <div class="col-lg-6 mb-3">
            <div class="card {{ $step['done'] ? 'card-success' : 'card-outline card-secondary' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="badge badge-{{ $step['done'] ? 'success' : 'secondary' }} mr-2">{{ $step['number'] }}</span>
                        <i class="{{ $step['icon'] }} mr-1"></i> {{ $step['title'] }}
                        @if($step['done'])
                            <i class="fas fa-check-circle text-success float-right"></i>
                        @endif
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">{{ $step['summary'] }}</p>
                    <div class="mb-3">
                        @foreach ($step['features'] as $feature)
                            <span class="badge badge-light border mr-1">
                                <i class="fas fa-check text-success"></i> {{ $feature }}
                            </span>
                        @endforeach
                    </div>
                    <a href="{{ route($step['route']) }}" class="btn btn-sm btn-{{ $step['done'] ? 'outline-success' : 'primary' }}">
                        <i class="fas fa-arrow-right"></i>
                        {{ $step['done'] ? 'Kelola' : 'Mulai' }}
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card {{ $verification['ready'] ? 'card-success' : 'card-warning' }}">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-check"></i> 6. Verifikasi
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            @foreach ($verification['checks'] as $key => $check)
                <div class="col-md-4 mb-3">
                    <div class="info-box mb-0 {{ $check['ok'] ? 'bg-success' : 'bg-warning' }}">
                        <span class="info-box-icon">
                            <i class="fas fa-{{ $check['ok'] ? 'check' : 'exclamation-triangle' }}"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $check['label'] }}</span>
                            <span class="info-box-number" style="font-size: 0.9rem;">{{ $check['message'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="alert alert-{{ $verification['ready'] ? 'success' : 'warning' }} mb-0 mt-2">
            <i class="fas fa-{{ $verification['ready'] ? 'flag-checkered' : 'hourglass-half' }}"></i>
            <strong>{{ $verification['ready'] ? 'Siap Digunakan' : 'Belum Siap' }}</strong> —
            {{ $verification['ready_message'] }}
        </div>
    </div>
    @if($verification['ready'])
        <div class="card-footer">
            <a href="{{ route('dashboard') }}" class="btn btn-success">
                <i class="fas fa-tachometer-alt"></i> Ke Dashboard Monitoring
            </a>
        </div>
    @endif
</div>

<div class="card card-secondary collapsed-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-database"></i> Ringkasan Data</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered">
            <tr><th>Guru</th><td>{{ $counts['guru'] }}</td></tr>
            <tr><th>Akun Guru</th><td>{{ $counts['guru_with_account'] }}</td></tr>
            <tr><th>Kode Kelas</th><td>{{ $counts['kode_kelas'] }}</td></tr>
            <tr><th>Siswa</th><td>{{ $counts['siswa'] }}</td></tr>
            <tr><th>Jadwal</th><td>{{ $counts['jadwal'] }}</td></tr>
        </table>
    </div>
</div>
@stop
