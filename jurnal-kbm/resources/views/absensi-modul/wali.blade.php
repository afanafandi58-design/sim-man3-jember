@extends('adminlte::page')

@section('title', 'Wali Kelas — Tidak Masuk')

@section('content_header')
    <h1><i class="fas fa-chalkboard mr-2"></i>Menu Wali Kelas</h1>
@stop

@section('content')
<p class="text-muted">Daftar siswa kelas Anda yang tidak masuk (dari Absensi Harian / jurnal).</p>

<form method="GET" class="form-inline mb-3">
    @if($assignments->isNotEmpty())
        <label class="mr-2">Kelas wali</label>
        <select name="wali_id" class="form-control mr-2" onchange="this.form.submit()">
            @foreach($assignments as $a)
                <option value="{{ $a->id }}" @selected(($selected->id ?? null) == $a->id)>
                    {{ $a->kelas }}{{ $a->rombel ? '/'.$a->rombel : '' }}
                </option>
            @endforeach
        </select>
    @endif
    <label class="mr-2">Tanggal</label>
    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control mr-2">
    <button class="btn btn-primary">Tampilkan</button>
</form>

<div class="card">
    <div class="card-header">
        Tidak masuk — {{ $kelas }}{{ $rombel ? '/'.$rombel : '' }} · {{ $tanggal }}
        <span class="badge badge-warning">{{ $tidakMasuk->count() }}</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>NIS</th><th>Nama</th><th>Status</th><th>Keterangan</th><th>Bukti</th></tr></thead>
            <tbody>
            @forelse($tidakMasuk as $row)
                <tr>
                    <td>{{ $row->siswa->nis ?? '-' }}</td>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                    <td>
                        @if($row->hasBukti())
                            <a href="{{ route('absensi-modul.bukti', $row) }}" target="_blank">Lihat</a>
                        @else - @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Tidak ada data / belum ada assignment wali.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
