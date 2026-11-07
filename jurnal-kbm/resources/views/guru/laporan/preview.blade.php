@extends('adminlte::page')

@section('title', 'Pratinjau Rekap Jurnal')

@section('content_header')
    <h1><i class="fas fa-eye mr-2"></i>Pratinjau Rekap Jurnal</h1>
@stop

@section('content')
<div class="alert alert-info">
    Periksa data di bawah. Jika sudah sesuai, klik <strong>Unduh / Cetak PDF</strong>.
    Data hanya jurnal milik Anda ({{ $guru->nama ?? '-' }}).
</div>

<div class="card card-outline card-primary mb-3">
    <div class="card-body py-3">
        <div class="row">
            <div class="col-md-8">
                <h5 class="mb-1 font-weight-bold">{{ $title }}</h5>
                <p class="mb-0 text-muted">
                    Periode: <strong>{{ ucfirst($periode) }}</strong>
                    &middot; Rentang: <strong>{{ $startDate->format('d/m/Y') }} – {{ $endDate->format('d/m/Y') }}</strong>
                    &middot; Total: <strong>{{ $jurnals->count() }}</strong> jurnal
                </p>
            </div>
            <div class="col-md-4 text-md-right mt-2 mt-md-0">
                <a href="{{ route('guru.laporan.index') }}" class="btn btn-secondary btn-sm mb-1">
                    <i class="fas fa-arrow-left"></i> Ubah filter
                </a>
                <form id="form-cetak-setelah-preview" action="{{ route('guru.laporan.cetak') }}" method="POST" target="_blank" class="d-inline">
                    @csrf
                    @foreach ($filters as $key => $value)
                        @if ($key !== 'guru_id' && filled($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" id="btn-cetak-setelah-preview" class="btn btn-danger btn-sm mb-1">
                        <i class="fas fa-file-pdf"></i> Unduh / Cetak PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar jurnal yang akan dicetak</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Tanggal</th>
                        <th>Pengganti</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Materi</th>
                        <th>Hadir</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jurnals as $index => $jurnal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $jurnal->guru_pengganti ?: '-' }}</td>
                            <td>{{ $jurnal->jadwal->kelas ?? '-' }}</td>
                            <td>{{ $jurnal->jadwal->mapel ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($jurnal->materi, 80) }}</td>
                            <td>{{ $jurnal->hadir }}</td>
                            <td>{{ $jurnal->status_kbm }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
        <span class="text-muted small">PDF akan memakai kop Kemenag/MAN 3 + TTD Guru &amp; Kepala Madrasah.</span>
        <button type="submit" form="form-cetak-setelah-preview" class="btn btn-danger">
            <i class="fas fa-file-pdf"></i> Unduh / Cetak PDF
        </button>
    </div>
</div>
@stop

@section('js')
<script>
document.getElementById('form-cetak-setelah-preview').addEventListener('submit', function () {
    const btn = document.getElementById('btn-cetak-setelah-preview');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan PDF…';
    setTimeout(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Unduh / Cetak PDF';
    }, 8000);
});
</script>
@stop
