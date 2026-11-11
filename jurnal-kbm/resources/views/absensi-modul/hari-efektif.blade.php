@extends('adminlte::page')

@section('title', 'Tool Hari Efektif — Tidak Masuk')

@section('content_header')
    <h1><i class="fas fa-user-clock mr-2"></i>Tool Hari Efektif</h1>
@stop

@section('content')
<div class="alert alert-primary">
    Hari operasional: <strong>{{ $hari }}</strong> · Tanggal daftar: <strong>{{ $tanggal }}</strong>.
    Guru pengajar dan wali kelas melihat siswa tidak masuk (Sakit/Izin/Alpha) pada kelas yang relevan, diurutkan per kelas.
    Input absensi tetap hanya H/H-1; daftar ini boleh dilihat untuk tanggal yang dipilih.
</div>

@if(! $canOpenTool)
    <div class="alert alert-warning">Anda belum punya kelas ajar aktif atau assignment wali. Hubungi admin bila perlu akses.</div>
@endif

@if(($isWali ?? false) && ($isPengajar ?? false))
    <div class="alert alert-info py-2">
        Anda wali kelas sekaligus pengajar hari ini — filter kelas mencakup keduanya.
        <a href="{{ route('absensi-modul.wali', ['tanggal' => $tanggal]) }}">Buka menu wali kelas</a>
    </div>
@endif

<form method="GET" class="form-inline mb-3 flex-wrap">
    <label class="mr-2">Tanggal</label>
    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control mr-2 mb-2">
    <label class="mr-2">Kelas</label>
    <select name="kelas" class="form-control mr-2 mb-2" onchange="this.form.submit()">
        <option value="">Semua kelas saya</option>
        @foreach($kelasList as $k)
            <option value="{{ $k }}" @selected(($kelasFilter ?? '') == $k)>{{ $k }}</option>
        @endforeach
    </select>
    <label class="mr-2">Rombel</label>
    <select name="rombel" class="form-control mr-2 mb-2">
        <option value="">Semua</option>
        @foreach($rombelList as $r)
            <option value="{{ $r }}" @selected(($rombelFilter ?? '') == $r)>{{ $r }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary mb-2">Filter / urutkan</button>
    <a href="{{ route('absensi-modul.input', ['tanggal' => $tanggal, 'kelas' => $kelasFilter, 'rombel' => $rombelFilter]) }}" class="btn btn-outline-success ml-2 mb-2">Input absensi</a>
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
                        <a class="btn btn-xs btn-outline-secondary"
                           href="{{ route('absensi-modul.hari-efektif', ['tanggal' => $tanggal, 'kelas' => $j->kelas]) }}">
                            Filter kelas ini
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
        <small class="text-muted ml-2">diurutkan per kelas → rombel → nama</small>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Kelas</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
            @forelse($tidakMasuk as $row)
                <tr>
                    <td>{{ $row->siswa->kelas ?? '-' }}{{ ($row->siswa->rombel ?? null) ? '/'.$row->siswa->rombel : '' }}</td>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
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
                <tr><td colspan="6" class="text-center text-muted">Tidak ada laporan tidak masuk untuk filter kelas ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
