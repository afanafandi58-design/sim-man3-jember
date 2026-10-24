@extends('adminlte::page')

@section('title', 'Tool Hari Efektif — Tidak Masuk')

@section('content_header')
    <h1><i class="fas fa-user-clock mr-2"></i>Tool Hari Efektif</h1>
@stop

@section('content')
<div class="alert alert-primary">
    Hari operasional: <strong>{{ $hari }}</strong> · Tanggal: <strong>{{ $tanggal }}</strong>.
    Guru pengajar hari ini dapat melihat siswa tidak masuk + bukti untuk checklist kehadiran manual di jurnal.
</div>

@if(! $canOpenTool)
    <div class="alert alert-warning">Anda tidak terjadwal mengajar hari ini dan bukan wali kelas. Data tetap dapat dilihat admin/kepala/staff.</div>
@endif

<form method="GET" class="form-inline mb-3">
    <label class="mr-2">Tanggal</label>
    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control mr-2">
    <label class="mr-2">Kelas</label>
    <input type="text" name="kelas" value="{{ $kelasFilter }}" class="form-control mr-2" placeholder="mis. X">
    <button class="btn btn-primary">Muat ulang</button>
    <a href="{{ route('absensi-modul.input', ['tanggal' => $tanggal]) }}" class="btn btn-outline-success ml-2">Input absensi</a>
</form>

@if($jadwals->isNotEmpty())
<div class="card card-outline card-success mb-3">
    <div class="card-header">Jadwal Anda hari ini</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Mapel</th><th>Kelas</th><th>Jam</th><th></th></tr></thead>
            <tbody>
            @foreach($jadwals as $j)
                <tr>
                    <td>{{ $j->mapel }}</td>
                    <td>{{ $j->kelas }}</td>
                    <td>{{ substr($j->jam_mulai,0,5) }}–{{ substr($j->jam_selesai,0,5) }}</td>
                    <td>
                        <a class="btn btn-xs btn-primary"
                           href="{{ route('guru.jurnal.create', $j->id) }}?tanggal={{ urlencode($tanggal) }}">
                            Buka checklist jurnal
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        Siswa tidak masuk
        <span class="badge badge-warning">{{ $tidakMasuk->count() }}</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
            @forelse($tidakMasuk as $row)
                <tr>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
                    <td>{{ $row->siswa->kelas ?? '-' }}/{{ $row->siswa->rombel ?? '-' }}</td>
                    <td><span class="badge badge-danger">{{ $row->status }}</span></td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                    <td>
                        @if($row->hasBukti())
                            <a href="{{ route('absensi-modul.bukti', $row) }}" target="_blank" class="btn btn-xs btn-info">Lihat bukti</a>
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>
                    <td>{{ $row->sumber }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada laporan tidak masuk untuk filter ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
