@extends('adminlte::page')

@section('title', 'Ajukan Perbaikan Jadwal')

@section('content_header')
    <h1>Ajukan Perbaikan Jadwal</h1>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan!</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card card-outline card-secondary">
    <div class="card-header">
        <h3 class="card-title">Slot Jadwal Saat Ini</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Guru:</strong> {{ $guru->nama }}</div>
            <div class="col-md-3"><strong>Kelas:</strong> {{ $jadwal->kelas }}</div>
            <div class="col-md-3"><strong>Mapel:</strong> {{ $jadwal->mapel }}</div>
            <div class="col-md-3"><strong>Hari:</strong> {{ $jadwal->hari }}</div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><strong>Jam Ke:</strong> {{ $jadwal->jam_ke }}</div>
            <div class="col-md-3"><strong>Jam:</strong> {{ \Illuminate\Support\Str::substr($jadwal->jam_mulai, 0, 5) }} - {{ \Illuminate\Support\Str::substr($jadwal->jam_selesai, 0, 5) }}</div>
            <div class="col-md-3"><strong>TP:</strong> {{ $jadwal->tahun_pelajaran }}</div>
            <div class="col-md-3"><strong>Semester:</strong> {{ $jadwal->semester }}</div>
        </div>
        <p class="text-muted small mt-3 mb-0">
            Ubah kelas, hari, jam, dan/atau mapel di bawah. Saat disimpan, <strong>data pada slot ini langsung ditimpa</strong>
            (bukan baris baru). Jurnal yang sudah terikat slot tetap ada. Tahun pelajaran &amp; semester tidak diubah lewat form ini.
        </p>
    </div>
</div>

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title">Form Perbaikan (langsung diterapkan)</h3>
    </div>

    <form action="{{ route('guru.jadwal-saya.perbaikan.store', $jadwal) }}" method="POST">
        @csrf

        <div class="card-body">
            @include('partials.jadwal-form-masters', [
                'kelasOptions' => $kelasOptions,
                'mapelOptions' => $mapelOptions,
                'polaHariMap' => $polaHariMap,
                'jamMapByPola' => $jamMapByPola,
                'selectedKodeKelas' => old('kode_kelas', $selectedKodeKelas),
                'selectedMapel' => old('mapel', $selectedMapel),
                'selectedHari' => old('hari', $selectedHari),
                'selectedJamKe' => old('jam_ke', $selectedJamKe),
                'selectedJamKeAkhir' => old('jam_ke_akhir', $selectedJamKeAkhir),
                'selectedJamMulai' => old('jam_mulai', $selectedJamMulai ?? ''),
                'selectedJamSelesai' => old('jam_selesai', $selectedJamSelesai ?? ''),
                'showMapelLainnya' => $showMapelLainnya ?? false,
            ])

            <div class="form-group">
                <label>Alasan Perbaikan <span class="text-danger">*</span></label>
                <textarea name="alasan" class="form-control" rows="4"
                          placeholder="Jelaskan mengapa data slot ini perlu diganti..."
                          required>{{ old('alasan') }}</textarea>
                @error('alasan')<small class="text-danger">{{ $message }}</small>@enderror
                <small class="text-muted">Minimal 10 karakter. Jejak perubahan (nilai lama → baru) akan tersimpan otomatis.</small>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('Timpa data jadwal pada slot ini sesuai pilihan Anda? Jurnal yang sudah ada tidak dihapus.');">
                <i class="fas fa-save"></i> Simpan &amp; Timpa Slot
            </button>
            <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@stop
