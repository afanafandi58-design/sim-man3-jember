@php
    $selectedJamKe = $selectedJamKe ?? old('jam_ke');
    $selectedJamKeAkhir = $selectedJamKeAkhir ?? old('jam_ke_akhir', $selectedJamKe);
    $selectedJamMulai = $selectedJamMulai ?? old('jam_mulai');
    $selectedJamSelesai = $selectedJamSelesai ?? old('jam_selesai');
    $selectedNamaSesi = $selectedNamaSesi ?? old('nama_sesi');
    $polaHariMap = $polaHariMap ?? [];
    $jamMapByPola = $jamMapByPola ?? [];
    $hariSelectId = $hariSelectId ?? 'hari-select';
    $showPolaDisplay = $showPolaDisplay ?? false;
@endphp

@if($showPolaDisplay)
<div class="form-group">
    <label>Pola Jadwal (Otomatis)</label>
    <input type="text" id="pola-display" class="form-control" readonly
           placeholder="Pilih hari terlebih dahulu">
    <small class="text-muted">Senin–Kamis & Sabtu → REG. Jumat → JUM.</small>
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Jam Awal</label>
            <select name="jam_ke" id="jam-ke-awal-select" class="form-control" required>
                <option value="">-- Pilih Jam Awal --</option>
            </select>
            @error('jam_ke')<small class="text-danger d-block">{{ $message }}</small>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Jam Akhir</label>
            <select name="jam_ke_akhir" id="jam-ke-akhir-select" class="form-control" required>
                <option value="">-- Pilih Jam Akhir --</option>
            </select>
            @error('jam_ke_akhir')<small class="text-danger d-block">{{ $message }}</small>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Rentang Jam Mengajar</label>
    <input type="text" id="jam-range-preview" class="form-control" readonly
           placeholder="Pilih Jam Awal dan Jam Akhir">
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Nama Sesi</label>
            <input type="text" id="nama-sesi-display" class="form-control" readonly
                   value="{{ $selectedNamaSesi }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Jam Mulai</label>
            <input type="text" id="jam-mulai-display" class="form-control" readonly
                   value="{{ $selectedJamMulai }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Jam Selesai</label>
            <input type="text" id="jam-selesai-display" class="form-control" readonly
                   value="{{ $selectedJamSelesai }}">
        </div>
    </div>
</div>
<small class="text-muted d-block mb-3">Waktu diisi otomatis dari Master Jam sesuai pola jadwal hari yang dipilih.</small>

@push('js')
<script>
(function () {
    const polaHariMap = @json($polaHariMap);
    const jamMapByPola = @json($jamMapByPola);
    const selectedJamKe = @json((string) ($selectedJamKe ?? ''));
    const selectedJamKeAkhir = @json((string) ($selectedJamKeAkhir ?? ''));
    const hariSelectId = @json($hariSelectId);
    const hariSelect = document.getElementById(hariSelectId);
    const polaDisplay = document.getElementById('pola-display');
    const jamAwalSelect = document.getElementById('jam-ke-awal-select');
    const jamAkhirSelect = document.getElementById('jam-ke-akhir-select');
    const jamRangePreview = document.getElementById('jam-range-preview');
    const jamMulaiDisplay = document.getElementById('jam-mulai-display');
    const jamSelesaiDisplay = document.getElementById('jam-selesai-display');
    const namaSesiDisplay = document.getElementById('nama-sesi-display');

    function getJamsForHari() {
        const hari = hariSelect ? hariSelect.value : '';
        const pola = polaHariMap[hari];

        if (!pola || !pola.pola_id) {
            return { pola: null, jams: null };
        }

        return {
            pola: pola,
            jams: jamMapByPola[String(pola.pola_id)] || null,
        };
    }

    function populateJamSelect(select, jams, minJamKe, placeholder, selectedValue, currentValue) {
        if (!select) return;

        select.innerHTML = '<option value="">' + placeholder + '</option>';

        if (!jams) return;

        Object.keys(jams).sort(function (a, b) { return Number(a) - Number(b); }).forEach(function (jamKe) {
            if (minJamKe !== null && Number(jamKe) < Number(minJamKe)) {
                return;
            }

            const data = jams[jamKe];
            const option = document.createElement('option');
            option.value = jamKe;
            option.textContent = data.label || ('Jam Ke ' + jamKe);

            if (jamKe === selectedValue || jamKe === currentValue) {
                option.selected = true;
            }

            select.appendChild(option);
        });
    }

    function updateJamRangeFields() {
        const { pola, jams } = getJamsForHari();

        if (polaDisplay) {
            polaDisplay.value = pola ? (pola.kode + ' — ' + pola.nama) : '';
        }

        const currentAwal = jamAwalSelect ? jamAwalSelect.value : '';
        const currentAkhir = jamAkhirSelect ? jamAkhirSelect.value : '';

        populateJamSelect(jamAwalSelect, jams, null, '-- Pilih Jam Awal --', selectedJamKe, currentAwal);

        const awalValue = jamAwalSelect ? jamAwalSelect.value : '';
        populateJamSelect(
            jamAkhirSelect,
            jams,
            awalValue || null,
            '-- Pilih Jam Akhir --',
            selectedJamKeAkhir,
            currentAkhir
        );

        if (jamAkhirSelect && awalValue && !jamAkhirSelect.value) {
            jamAkhirSelect.value = awalValue;
        }

        updateJamRangeDisplay();
    }

    function updateJamRangeDisplay() {
        const { jams } = getJamsForHari();
        const awal = jamAwalSelect ? jamAwalSelect.value : '';
        const akhir = jamAkhirSelect ? jamAkhirSelect.value : '';

        if (!jams || !awal || !akhir) {
            if (jamRangePreview) jamRangePreview.value = '';
            return;
        }

        if (Number(akhir) < Number(awal)) {
            if (jamRangePreview) jamRangePreview.value = 'Jam Akhir tidak valid';
            return;
        }

        const first = jams[awal];
        const last = jams[akhir];

        if (jamRangePreview) {
            jamRangePreview.value = awal === akhir ? ('Jam ' + awal) : ('Jam ' + awal + '–' + akhir);
        }

        if (first && last && jamMulaiDisplay && jamSelesaiDisplay) {
            jamMulaiDisplay.value = first.jam_mulai;
            jamSelesaiDisplay.value = last.jam_selesai;
            if (namaSesiDisplay) {
                namaSesiDisplay.value = first.nama_sesi || '';
            }
        }
    }

    function onJamAwalChange() {
        const awalValue = jamAwalSelect ? jamAwalSelect.value : '';
        const { jams } = getJamsForHari();
        const currentAkhir = jamAkhirSelect ? jamAkhirSelect.value : '';

        populateJamSelect(
            jamAkhirSelect,
            jams,
            awalValue || null,
            '-- Pilih Jam Akhir --',
            selectedJamKeAkhir,
            currentAkhir
        );

        if (jamAkhirSelect && awalValue) {
            if (!jamAkhirSelect.value || Number(jamAkhirSelect.value) < Number(awalValue)) {
                jamAkhirSelect.value = awalValue;
            }
        }

        updateJamRangeDisplay();
    }

    if (hariSelect) {
        hariSelect.addEventListener('change', updateJamRangeFields);
        updateJamRangeFields();
    }

    if (jamAwalSelect) {
        jamAwalSelect.addEventListener('change', onJamAwalChange);
    }

    if (jamAkhirSelect) {
        jamAkhirSelect.addEventListener('change', updateJamRangeDisplay);
    }
})();
</script>
@endpush
