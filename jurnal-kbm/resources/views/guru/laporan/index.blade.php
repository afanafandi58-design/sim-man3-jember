@extends('adminlte::page')

@section('title', 'Rekap Jurnal Saya')

@section('content_header')
    <h1><i class="fas fa-file-pdf mr-2"></i>Rekap Jurnal Saya</h1>
@stop

@section('content')
@include('partials.cetak-pdf-feedback')

<div class="alert alert-info">
    Cetak rekap jurnal KBM <strong>milik Anda saja</strong> (pengampu = {{ $guru->nama ?? '-' }}).
    Periode: Mingguan, Bulanan, atau Semester — selaras laporan admin, tanpa bisa memilih guru lain.
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter &amp; cetak PDF</h3>
    </div>

    <form id="form-cetak-rekap-guru" action="{{ route('guru.laporan.cetak') }}" method="POST" target="_blank">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Periode</label>
                        <select name="periode" id="periode" class="form-control" required>
                            @foreach ($periodes as $value => $label)
                                <option value="{{ $value }}" @selected(old('periode', 'mingguan') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4 filter-mingguan">
                    <div class="form-group">
                        <label>Tanggal (dalam minggu)</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', date('Y-m-d')) }}">
                    </div>
                </div>

                <div class="col-md-2 filter-bulanan d-none">
                    <div class="form-group">
                        <label>Bulan</label>
                        <input type="number" name="bulan" class="form-control" min="1" max="12" value="{{ old('bulan', date('n')) }}">
                    </div>
                </div>

                <div class="col-md-2 filter-bulanan d-none">
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" class="form-control" value="{{ old('tahun', date('Y')) }}">
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester" class="form-control">
                            <option value="">Semua</option>
                            <option value="Ganjil" @selected(old('semester') === 'Ganjil')>Ganjil</option>
                            <option value="Genap" @selected(old('semester') === 'Genap')>Genap</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <input type="text" name="tahun_pelajaran" class="form-control" placeholder="2026/2027" value="{{ old('tahun_pelajaran') }}">
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}">
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Mata Pelajaran (opsional)</label>
                        <select name="mapel" class="form-control">
                            <option value="">Semua Mapel saya</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel }}" @selected(old('mapel') === $mapel)>{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kelas (opsional)</label>
                        <select name="kelas" class="form-control">
                            <option value="">Semua Kelas saya</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item }}" @selected(old('kelas') === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" id="btn-cetak-rekap-guru" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak PDF Rekap
            </button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    function toggleFilters() {
        const periode = document.getElementById('periode').value;
        document.querySelectorAll('.filter-mingguan, .filter-bulanan, .filter-semester').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.filter-' + periode).forEach(el => el.classList.remove('d-none'));
    }
    document.getElementById('periode').addEventListener('change', toggleFilters);
    toggleFilters();
    document.getElementById('form-cetak-rekap-guru').addEventListener('submit', function () {
        const btn = document.getElementById('btn-cetak-rekap-guru');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF…';
        setTimeout(function () { btn.disabled = false; btn.innerHTML = '<i class="fas fa-file-pdf"></i> Cetak PDF Rekap'; }, 8000);
    });
</script>
@stop
