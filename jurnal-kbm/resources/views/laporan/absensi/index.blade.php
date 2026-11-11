@extends('adminlte::page')

@section('title', 'Rekap Absensi Siswa')

@section('content_header')
    <h1>Rekap Absensi Siswa</h1>
@stop

@section('content')
@include('partials.cetak-pdf-feedback')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>
    <form id="form-cetak-absensi" action="{{ route('laporan.absensi.cetak') }}" method="POST">
        @csrf
        <div class="card-body">
            <p class="text-muted small">Maksimal 14 hari / ~1200 baris. Rentang &gt; 3 hari wajib pilih filter guru, mapel, kelas, atau rombel.</p>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Guru</label>
                        <select name="guru_id" class="form-control">
                            <option value="">Semua</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Mapel</label>
                        <select name="mapel" class="form-control">
                            <option value="">Semua</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel }}">{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" class="form-control">
                            <option value="">Semua</option>
                            @foreach($kelas as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Rombel</label>
                        <select name="rombel" class="form-control">
                            <option value="">Semua</option>
                            @foreach($rombels as $rombel)
                                <option value="{{ $rombel }}">{{ $rombel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" id="btn-cetak-absensi" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak Rekap PDF
            </button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
document.getElementById('form-cetak-absensi').addEventListener('submit', function () {
    const btn = document.getElementById('btn-cetak-absensi');
    const loading = document.getElementById('cetak-pdf-loading');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF…';
    if (loading) loading.classList.remove('d-none');
});
</script>
@stop
