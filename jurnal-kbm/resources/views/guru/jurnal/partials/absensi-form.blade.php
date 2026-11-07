@php
    $absensiMap = $absensiMap ?? collect();
    $keteranganMap = $keteranganMap ?? collect();
@endphp

@if($siswas->isNotEmpty())
<div class="card card-outline card-info d-none" id="absensi-siswa-section">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-user-check"></i> Absensi Siswa
        </h3>
        <div class="card-tools">
            <span class="badge badge-info">
                Kelas {{ $kelasMeta['kelas'] }} | Rombel {{ $kelasMeta['rombel'] }} | {{ $kelasMeta['kode_kelas'] }}
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="50">No</th>
                        <th width="120">NIS</th>
                        <th>Nama Siswa</th>
                        <th width="160">Status</th>
                        <th width="200">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($siswas as $siswa)
                        @php
                            $currentStatus = $absensiMap[$siswa->id] ?? 'Hadir';
                            $currentKeterangan = $keteranganMap[$siswa->id] ?? '';
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $siswa->nis }}</td>
                            <td>{{ $siswa->nama }}</td>
                            <td>
                                <select
                                    name="absensi[siswa][{{ $siswa->id }}]"
                                    class="form-control form-control-sm absensi-status-select"
                                    data-siswa-id="{{ $siswa->id }}">
                                    @foreach (['Hadir', 'Sakit', 'Izin', 'Alpha'] as $status)
                                        <option value="{{ $status }}" @selected(old("absensi.siswa.{$siswa->id}", $currentStatus) === $status)>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="absensi[keterangan][{{ $siswa->id }}]"
                                    class="form-control form-control-sm"
                                    value="{{ old("absensi.keterangan.{$siswa->id}", $currentKeterangan) }}"
                                    placeholder="Opsional">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer text-muted">
        <small>Default semua siswa <strong>Hadir</strong>. Ubah hanya siswa yang tidak hadir.</small>
    </div>
</div>
@else
<div class="alert alert-warning d-none" id="absensi-siswa-section">
    <i class="fas fa-exclamation-triangle"></i>
    Data siswa untuk kelas <strong>{{ $jadwal->kelas ?? $jurnal->jadwal->kelas ?? '-' }}</strong> belum tersedia.
    Hubungi admin untuk mengimpor data siswa.
</div>
@endif

@push('js')
<script>
    function toggleAbsensiSection() {
        const section = document.getElementById('absensi-siswa-section');
        if (!section) return;
        section.classList.toggle('d-none');
    }

    function recalculateAttendanceCounts() {
        const counts = { hadir: 0, sakit: 0, izin: 0, alpha: 0 };
        document.querySelectorAll('.absensi-status-select').forEach(select => {
            const status = (select.value || 'Hadir').toLowerCase();
            if (counts[status] !== undefined) {
                counts[status]++;
            }
        });

        ['hadir', 'sakit', 'izin', 'alpha'].forEach(field => {
            const input = document.querySelector(`input[name="${field}"]`);
            if (input && document.querySelectorAll('.absensi-status-select').length > 0) {
                input.value = counts[field];
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('btn-absensi-siswa');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleAbsensiSection);
        }

        document.querySelectorAll('.absensi-status-select').forEach(select => {
            select.addEventListener('change', recalculateAttendanceCounts);
        });

        recalculateAttendanceCounts();
    });
</script>
@endpush
