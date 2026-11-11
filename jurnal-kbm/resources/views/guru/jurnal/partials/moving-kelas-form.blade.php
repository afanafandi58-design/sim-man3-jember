@php
    $isMoving = !empty($jadwal?->is_moving_class);
    $canEditMoving = !empty($canEditMoving);
    $groupedSiswa = $groupedSiswa ?? collect();
    $selectedIds = $selectedIds ?? [];
    $selectedLookup = array_fill_keys(array_map('intval', $selectedIds), true);
    $oldIds = collect(old('siswa_ids', []))->map(fn ($id) => (int) $id)->all();
    if (! empty($oldIds)) {
        $selectedLookup = array_fill_keys($oldIds, true);
    }
@endphp

@if ($isMoving)
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exchange-alt"></i> Kelas Moving
        </h3>
        <div class="card-tools">
            <span class="badge badge-primary">Gabungan rombel existing</span>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">
            Pilih siswa dari rombel yang sudah ada dan beri nama kelas moving.
            Data rombel/siswa master tidak dihapus. Hanya pemilik jadwal yang dapat mengubah pilihan.
        </p>

        @if ($canEditMoving)
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
                @error('moving_kelas_nama')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <p class="mb-2">
                <strong>Siswa peserta</strong>
                · terpilih: <span id="jurnal-moving-count">{{ count($selectedLookup) }}</span>
            </p>
            @error('siswa_ids')
                <div class="alert alert-danger py-2">{{ $message }}</div>
            @enderror

            <div style="max-height:320px;overflow:auto;border:1px solid #dee2e6;border-radius:.25rem;padding:.5rem;">
                @forelse ($groupedSiswa as $groupLabel => $siswas)
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>{{ $groupLabel }}</strong>
                            <button type="button" class="btn btn-xs btn-outline-secondary jurnal-moving-toggle" data-group="{{ md5($groupLabel) }}">
                                Centang rombel
                            </button>
                        </div>
                        <div class="row">
                            @foreach ($siswas as $siswa)
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox"
                                               class="custom-control-input jurnal-moving-cb"
                                               data-group="{{ md5($groupLabel) }}"
                                               id="jurnal-siswa-{{ $siswa->id }}"
                                               name="siswa_ids[]"
                                               value="{{ $siswa->id }}"
                                               @checked(!empty($selectedLookup[(int) $siswa->id]))>
                                        <label class="custom-control-label" for="jurnal-siswa-{{ $siswa->id }}">
                                            {{ $siswa->nama }}
                                            <small class="text-muted">({{ $siswa->nis }})</small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <hr class="my-2">
                    </div>
                @empty
                    <div class="text-warning">Belum ada siswa aktif.</div>
                @endforelse
            </div>
        @else
            <div class="alert alert-secondary mb-0">
                <strong>{{ $jadwal->moving_kelas_nama ?: '(Nama moving belum diisi pemilik)' }}</strong><br>
                {{ count($selectedIds) }} siswa terpilih oleh pemilik jadwal.
                @if(empty($selectedIds))
                    <br><span class="text-danger">Pemilik belum memilih siswa — absensi kosong sampai diatur.</span>
                @endif
            </div>
        @endif
    </div>
</div>

@if ($canEditMoving)
@push('js')
<script>
(function () {
    function refreshCount() {
        var n = document.querySelectorAll('.jurnal-moving-cb:checked').length;
        var el = document.getElementById('jurnal-moving-count');
        if (el) el.textContent = String(n);
    }
    document.querySelectorAll('.jurnal-moving-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var g = btn.getAttribute('data-group');
            var boxes = document.querySelectorAll('.jurnal-moving-cb[data-group="' + g + '"]');
            var allOn = Array.prototype.every.call(boxes, function (b) { return b.checked; });
            boxes.forEach(function (b) { b.checked = !allOn; });
            refreshCount();
        });
    });
    document.querySelectorAll('.jurnal-moving-cb').forEach(function (cb) {
        cb.addEventListener('change', refreshCount);
    });
    refreshCount();
})();
</script>
@endpush
@endif
@endif
