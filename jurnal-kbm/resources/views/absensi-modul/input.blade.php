@extends('adminlte::page')

@section('title', 'Input Absensi Harian')

@section('content_header')
    <h1><i class="fas fa-clipboard-check mr-2"></i>Input Absensi Harian + Bukti</h1>
@stop

@section('content')
<div class="alert alert-info">
    Modul Absensi Harian (titik F) terhubung ke jurnal KBM.
    Tanggal isi: hari H atau H-1. Upload bukti screenshot laporan online (opsional per siswa).
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-primary card-outline">
    <div class="card-header">Filter siswa</div>
    <div class="card-body">
        <form method="GET" class="form-inline">
            <label class="mr-2">Tanggal</label>
            <select name="tanggal" class="form-control mr-3">
                @foreach($fillableDates as $d)
                    <option value="{{ $d }}" @selected($tanggal === $d)>{{ $d }}</option>
                @endforeach
            </select>
            <label class="mr-2">Kelas</label>
            <select name="kelas" class="form-control mr-3">
                <option value="">Semua</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k }}" @selected(($filters['kelas'] ?? '') == $k)>{{ $k }}</option>
                @endforeach
            </select>
            <label class="mr-2">Rombel</label>
            <select name="rombel" class="form-control mr-3">
                <option value="">Semua</option>
                @foreach($rombelList as $r)
                    <option value="{{ $r }}" @selected(($filters['rombel'] ?? '') == $r)>{{ $r }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Tampilkan</button>
            <a href="{{ route('absensi-modul.hari-efektif', ['tanggal' => $tanggal]) }}" class="btn btn-outline-secondary ml-2">Tool hari efektif</a>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Form laporan absensi</div>
    <div class="card-body">
        <form action="{{ route('absensi-modul.input.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="kelas" value="{{ $filters['kelas'] ?? '' }}">
            <input type="hidden" name="rombel" value="{{ $filters['rombel'] ?? '' }}">
            <div class="form-row">
                <div class="form-group col-md-5">
                    <label>Siswa</label>
                    <select name="siswa_id" class="form-control" required>
                        <option value="">— pilih —</option>
                        @foreach($siswas as $siswa)
                            <option value="{{ $siswa->id }}">
                                {{ $siswa->nama }} ({{ $siswa->nis }}) — {{ $siswa->kelas }}/{{ $siswa->rombel }}
                                @if(isset($existing[$siswa->id])) [{{ $existing[$siswa->id]->status }}] @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" @selected($st === 'Alpha')>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-5">
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" maxlength="255" placeholder="Alasan / sumber laporan">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Bukti screenshot (jpg/png, max 2MB)</label>
                    <input type="file" name="bukti" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div class="form-group col-md-6">
                    <label>Caption bukti</label>
                    <input type="text" name="bukti_caption" class="form-control" maxlength="150">
                </div>
            </div>
            <button class="btn btn-success"><i class="fas fa-save"></i> Simpan absensi</button>
        </form>
    </div>
</div>

@if($existing->isNotEmpty())
<div class="card card-outline card-secondary">
    <div class="card-header">Data tersimpan tanggal {{ $tanggal }} (filter aktif)</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Siswa</th><th>Status</th><th>Keterangan</th><th>Bukti</th></tr></thead>
            <tbody>
            @foreach($existing as $row)
                <tr>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
                    <td><span class="badge badge-{{ $row->status === 'Hadir' ? 'success' : 'warning' }}">{{ $row->status }}</span></td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                    <td>
                        @if($row->hasBukti())
                            <a href="{{ route('absensi-modul.bukti', $row) }}" target="_blank">Lihat</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@stop
