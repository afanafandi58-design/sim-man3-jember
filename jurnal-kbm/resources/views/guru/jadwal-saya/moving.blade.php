@extends('adminlte::page')

@section('title', 'Atur Kelas Moving')

@section('content_header')
    <h1>Atur Kelas Moving</h1>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            Slot: {{ $jadwal->mapel }} · {{ $jadwal->hari }} · {{ $jadwal->jamRangeLabel() }}
        </h3>
        <div class="card-tools">
            <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
        </div>
    </div>

    <form action="{{ route('guru.jadwal-saya.moving.store', $jadwal) }}" method="POST">
        @csrf
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="alert alert-info">
                Kelas moving adalah <strong>gabungan siswa dari rombel yang sudah ada</strong>.
                Data rombel dan siswa master tidak diganti atau dihapus.
                Hanya pemilik slot ini yang dapat mengubah daftar.
            </div>

            <div class="form-group">
                <label for="moving_kelas_nama"><strong>Nama kelas moving</strong></label>
                <input type="text"
                       name="moving_kelas_nama"
                       id="moving_kelas_nama"
                       class="form-control"
                       maxlength="150"
                       required
                       value="{{ old('moving_kelas_nama', $jadwal->moving_kelas_nama) }}"
                       placeholder="Contoh: Moving Bahasa X Gabungan">
            </div>

            <p class="mb-2">
                <strong>Pilih siswa</strong>
                <span class="text-muted">(centang siswa dari beberapa rombel)</span>
                · terpilih: <span id="moving-count">{{ count($selectedIds) }}</span>
            </p>

            @php
                $selectedLookup = array_fill_keys(array_map('intval', $selectedIds), true);
                $oldIds = collect(old('siswa_ids', []))->map(fn ($id) => (int) $id)->all();
                if (! empty($oldIds)) {
                    $selectedLookup = array_fill_keys($oldIds, true);
                }
            @endphp

            @forelse ($groupedSiswa as $groupLabel => $siswas)
                <div class="card card-outline card-secondary mb-2">
                    <div class="card-header py-2">
                        <strong>{{ $groupLabel }}</strong>
                        <span class="badge badge-light">{{ $siswas->count() }} siswa</span>
                        <button type="button" class="btn btn-xs btn-outline-primary float-right moving-toggle-group" data-group="{{ md5($groupLabel) }}">
                            Centang semua di rombel ini
                        </button>
                    </div>
                    <div class="card-body py-2" style="max-height:220px;overflow:auto;">
                        <div class="row">
                            @foreach ($siswas as $siswa)
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input moving-siswa-cb"
                                               data-group="{{ md5($groupLabel) }}"
                                               id="siswa-{{ $siswa->id }}"
                                               name="siswa_ids[]"
                                               value="{{ $siswa->id }}"
                                               @checked(!empty($selectedLookup[(int) $siswa->id]))>
                                        <label class="custom-control-label" for="siswa-{{ $siswa->id }}">
                                            {{ $siswa->nama }}
                                            <small class="text-muted">({{ $siswa->nis }})</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-warning">Belum ada siswa aktif di master. Hubungi admin.</div>
            @endforelse
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan kelas moving
            </button>
            <a href="{{ route('guru.jadwal-saya.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@stop

@section('js')
<script>
(function () {
    function refreshCount() {
        var n = document.querySelectorAll('.moving-siswa-cb:checked').length;
        var el = document.getElementById('moving-count');
        if (el) el.textContent = String(n);
    }
    document.querySelectorAll('.moving-toggle-group').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var g = btn.getAttribute('data-group');
            var boxes = document.querySelectorAll('.moving-siswa-cb[data-group="' + g + '"]');
            var allOn = Array.prototype.every.call(boxes, function (b) { return b.checked; });
            boxes.forEach(function (b) { b.checked = !allOn; });
            refreshCount();
        });
    });
    document.querySelectorAll('.moving-siswa-cb').forEach(function (cb) {
        cb.addEventListener('change', refreshCount);
    });
    refreshCount();
})();
</script>
@stop
