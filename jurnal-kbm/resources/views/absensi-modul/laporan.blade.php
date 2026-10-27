@extends('adminlte::page')

@section('title', 'Laporan Absensi Harian')

@section('content_header')
    <h1><i class="fas fa-chart-pie mr-2"></i>Laporan Absensi Harian</h1>
@stop

@section('content')
<form method="GET" class="card card-body mb-3">
    <div class="form-row">
        <div class="form-group col-md-2">
            <label>Periode</label>
            <select name="periode" class="form-control">
                @foreach(['harian'=>'Harian','mingguan'=>'Mingguan','bulanan'=>'Bulanan','semester'=>'Semester'] as $k=>$v)
                    <option value="{{ $k }}" @selected($periode===$k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ $filters['tanggal'] ?? now()->toDateString() }}">
        </div>
        <div class="form-group col-md-1">
            <label>Bulan</label>
            <input type="number" name="bulan" min="1" max="12" class="form-control" value="{{ $filters['bulan'] ?? now()->month }}">
        </div>
        <div class="form-group col-md-1">
            <label>Tahun</label>
            <input type="number" name="tahun" class="form-control" value="{{ $filters['tahun'] ?? now()->year }}">
        </div>
        <div class="form-group col-md-2">
            <label>Semester</label>
            <select name="semester" class="form-control">
                <option value="Ganjil" @selected(($filters['semester'] ?? '')==='Ganjil')>Ganjil</option>
                <option value="Genap" @selected(($filters['semester'] ?? '')==='Genap')>Genap</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label>Kelas</label>
            <select name="kelas" class="form-control">
                <option value="">Semua</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k }}" @selected(($filters['kelas'] ?? '')==$k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-2 d-flex align-items-end">
            <button class="btn btn-primary btn-block">Tampilkan</button>
        </div>
    </div>
</form>

<div class="row mb-3">
    @foreach([
        'total'=>'Total','hadir'=>'Hadir','sakit'=>'Sakit','izin'=>'Izin','alpha'=>'Alpha','dengan_bukti'=>'Ada bukti'
    ] as $key=>$label)
        <div class="col-md-2">
            <div class="small-box bg-light border">
                <div class="inner">
                    <h3>{{ $summary[$key] ?? 0 }}</h3>
                    <p>{{ $label }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<p class="text-muted">{{ $periode_label }} · {{ $start }} s/d {{ $end }} (max 5000 baris)</p>

<div class="card">
    <div class="card-body p-0 table-responsive" style="max-height:480px;overflow:auto">
        <table class="table table-sm table-striped mb-0">
            <thead class="thead-light"><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Status</th><th>Bukti</th><th>Sumber</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') }}</td>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
                    <td>{{ $row->siswa->kelas ?? '-' }}</td>
                    <td>{{ $row->status }}</td>
                    <td>
                        @if($row->hasBukti())
                            <a href="{{ route('absensi-modul.bukti', $row) }}" target="_blank">Ya</a>
                        @else Tidak @endif
                    </td>
                    <td>{{ $row->sumber }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
