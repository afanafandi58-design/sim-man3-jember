@extends('adminlte::page')

@section('title', 'Laporan PDF')

@section('content_header')
    <h1>Laporan Jurnal KBM</h1>
@stop

@section('content')
@include('partials.cetak-pdf-feedback')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Laporan</h3>
    </div>

    <form id="form-cetak-jurnal" action="{{ route('laporan.cetak') }}" method="POST">
        @csrf
        <div class="card-body">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Periode</label>
                        <select name="periode" id="periode" class="form-control" required>
                            @foreach ($periodes as $value => $label)
                                <option value="{{ $value }}" @selected(old('periode') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4 filter-harian filter-mingguan">
                    <div class="form-group">
                        <label>Tanggal</label>
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
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tahun Pelajaran</label>
                        <input type="text" name="tahun_pelajaran" class="form-control" placeholder="2026/2027">
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control">
                    </div>
                </div>

                <div class="col-md-3 filter-semester d-none">
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control">
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Guru</label>
                        <select name="guru_id" class="form-control">
                            <option value="">Semua Guru</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        <select name="mapel" class="form-control">
                            <option value="">Semua Mapel</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel }}">{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" class="form-control">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item }}">{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" id="btn-cetak-jurnal" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </button>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
    function toggleFilters() {
        const periode = document.getElementById('periode').value;
        document.querySelectorAll('[class*="filter-"]').forEach(el => el.classList.add('d-none'));
        document.querySelectorAll('.filter-' + periode).forEach(el => el.classList.remove('d-none'));
        if (periode === 'harian' || periode === 'mingguan') {
            document.querySelectorAll('.filter-harian, .filter-mingguan').forEach(el => el.classList.remove('d-none'));
        }
    }
    document.getElementById('periode').addEventListener('change', toggleFilters);
    toggleFilters();
    document.getElementById('form-cetak-jurnal').addEventListener('submit', function () {
        const btn = document.getElementById('btn-cetak-jurnal');
        const loading = document.getElementById('cetak-pdf-loading');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF…';
        if (loading) loading.classList.remove('d-none');
    });
</script>
@stop
