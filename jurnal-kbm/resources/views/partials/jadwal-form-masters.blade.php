{{-- Partial form master data jadwal (kelas, mapel, jam) --}}
@php
    $selectedKodeKelas = $selectedKodeKelas ?? old('kode_kelas');
    $selectedMapel = $selectedMapel ?? old('mapel');
    $selectedJamKe = $selectedJamKe ?? old('jam_ke');
    $selectedHari = $selectedHari ?? old('hari');
    $showMapelLainnya = $showMapelLainnya ?? true;
    $polaHariMap = $polaHariMap ?? [];
    $jamMapByPola = $jamMapByPola ?? [];
@endphp

<div class="form-group">
    <label>Kelas / Rombel</label>
    <select name="kode_kelas" class="form-control" required>
        <option value="">-- Pilih Kelas --</option>
        @foreach($kelasOptions as $kelas)
            <option value="{{ $kelas->kode_kelas }}"
                {{ $selectedKodeKelas == $kelas->kode_kelas ? 'selected' : '' }}>
                {{ $kelas->kelas }} {{ $kelas->rombel }} ({{ $kelas->kode_kelas }})
            </option>
        @endforeach
    </select>
    @error('kode_kelas')<small class="text-danger">{{ $message }}</small>@enderror
    @if($kelasOptions->isEmpty())
        <small class="text-warning">Master kelas belum tersedia. Hubungi admin.</small>
    @endif
</div>

<div class="form-group">
    <label>Mata Pelajaran</label>
    <select name="mapel" id="mapel-select" class="form-control" required>
        <option value="">-- Pilih Mata Pelajaran --</option>
        @foreach($mapelOptions as $mapelItem)
            <option value="{{ $mapelItem->nama }}"
                {{ $selectedMapel == $mapelItem->nama ? 'selected' : '' }}>
                {{ $mapelItem->nama }}
            </option>
        @endforeach
        @if($showMapelLainnya)
            <option value="__lainnya__" {{ old('mapel') === '__lainnya__' ? 'selected' : '' }}>
                Mapel Lainnya...
            </option>
        @endif
    </select>
    @error('mapel')<small class="text-danger">{{ $message }}</small>@enderror
</div>

@if($showMapelLainnya)
<div class="form-group" id="mapel-baru-group" style="display:none;">
    <label>Nama Mata Pelajaran Baru</label>
    <input type="text" name="mapel_baru" id="mapel-baru-input" class="form-control"
           value="{{ old('mapel_baru') }}" placeholder="Usulkan nama mapel baru">
    @error('mapel_baru')<small class="text-danger">{{ $message }}</small>@enderror
    <small class="text-muted">Usulan akan ditinjau admin. Mapel belum dapat dipakai sebelum disetujui.</small>
</div>
@endif

<div class="form-group">
    <label>Hari</label>
    <select name="hari" id="hari-select" class="form-control" required>
        <option value="">-- Pilih Hari --</option>
        @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
            <option value="{{ $hari }}" {{ $selectedHari == $hari ? 'selected' : '' }}>
                {{ $hari }}
            </option>
        @endforeach
    </select>
    @error('hari')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-group">
    <label>Pola Jadwal (Otomatis)</label>
    <input type="text" id="pola-display" class="form-control" readonly
           placeholder="Pilih hari terlebih dahulu">
    <small class="text-muted">Senin–Kamis & Sabtu → REG. Jumat → JUM. Guru tidak memilih pola secara manual.</small>
</div>

@include('partials.jadwal-jam-range-fields', [
    'selectedJamKe' => $selectedJamKe,
    'selectedJamKeAkhir' => $selectedJamKeAkhir ?? old('jam_ke_akhir', $selectedJamKe),
    'selectedJamMulai' => old('jam_mulai', $selectedJamMulai ?? ''),
    'selectedJamSelesai' => old('jam_selesai', $selectedJamSelesai ?? ''),
    'selectedNamaSesi' => old('nama_sesi', $selectedNamaSesi ?? ''),
    'polaHariMap' => $polaHariMap,
    'jamMapByPola' => $jamMapByPola,
    'hariSelectId' => 'hari-select',
    'showPolaDisplay' => false,
])

@push('js')
<script>
(function () {
    const mapelSelect = document.getElementById('mapel-select');
    const mapelBaruGroup = document.getElementById('mapel-baru-group');
    const mapelBaruInput = document.getElementById('mapel-baru-input');

    function toggleMapelBaru() {
        if (!mapelSelect || !mapelBaruGroup) return;
        const isLainnya = mapelSelect.value === '__lainnya__';
        mapelBaruGroup.style.display = isLainnya ? 'block' : 'none';
        if (mapelBaruInput) {
            mapelBaruInput.required = isLainnya;
        }
    }

    if (mapelSelect) {
        mapelSelect.addEventListener('change', toggleMapelBaru);
        toggleMapelBaru();
    }
})();
</script>
@endpush
