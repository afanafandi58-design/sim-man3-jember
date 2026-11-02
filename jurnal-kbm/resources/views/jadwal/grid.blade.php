@extends('adminlte::page')

@section('title', 'Master Jadwal — Grid')

@section('content_header')
    <h1>Master Jadwal — Tampilan Grid</h1>
@stop

@push('css')
<style>
    .jadwal-grid-table th,
    .jadwal-grid-table td {
        vertical-align: middle;
        text-align: center;
    }
    .jadwal-grid-table .jam-label {
        text-align: left;
        white-space: nowrap;
        font-size: 0.85rem;
    }
    .jadwal-grid-cell {
        min-width: 120px;
        min-height: 64px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: box-shadow 0.15s ease;
    }
    .jadwal-grid-cell:hover {
        box-shadow: inset 0 0 0 2px rgba(0, 123, 255, 0.35);
    }
    .jadwal-grid-cell--empty {
        background-color: #e9ecef;
        color: #6c757d;
    }
    .jadwal-grid-cell--filled {
        background-color: #f0fff4;
    }
    .jadwal-grid-cell--warning {
        background-color: #fff3cd;
        border-color: #ffc107 !important;
    }
    .jadwal-grid-cell--recent {
        border-color: #ffeb3b !important;
        box-shadow: 0 0 0 1px #fdd835;
    }
    .jadwal-grid-cell-content {
        font-size: 0.8rem;
        line-height: 1.25;
        padding: 4px;
    }
    .jadwal-grid-cell-content .kelas {
        font-weight: 600;
    }
    .jadwal-grid-legend span {
        display: inline-block;
        width: 1.25rem;
        height: 1rem;
        vertical-align: middle;
        margin-right: 0.35rem;
        border: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('jadwals.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                ← Daftar Jadwal
            </a>
            <p class="text-muted mb-0 small">Klik sel untuk mengisi atau mengubah jadwal per guru.</p>
        </div>
        <a href="{{ route('jadwals.create') }}" class="btn btn-success btn-sm">+ Tambah Jadwal</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('jadwals.grid') }}" class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label for="guru_id">Guru</label>
                    <select name="guru_id" id="guru_id" class="form-control" required>
                        <option value="">— Pilih Guru —</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" @selected($guruId === (int) $guru->id)>
                                {{ $guru->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="tahun_pelajaran">Tahun Pelajaran</label>
                    <input type="text" name="tahun_pelajaran" id="tahun_pelajaran" class="form-control"
                           value="{{ $tahunPelajaran }}" placeholder="2026/2027">
                </div>
                <div class="form-group col-md-2">
                    <label for="semester">Semester</label>
                    <select name="semester" id="semester" class="form-control">
                        <option value="Ganjil" @selected($semester === 'Ganjil')>Ganjil</option>
                        <option value="Genap" @selected($semester === 'Genap')>Genap</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <button type="submit" class="btn btn-primary btn-block">Tampilkan Grid</button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedGuru && $grid)
        <div class="card">
            <div class="card-header">
                <strong>{{ $selectedGuru->nama }}</strong>
                <span class="text-muted ml-2">— TP {{ $tahunPelajaran }}, Semester {{ $semester }}</span>
            </div>
            <div class="card-body p-0 table-responsive">
                @if($grid['jamRows']->isEmpty())
                    <p class="p-3 mb-0 text-warning">Master Jam Pelajaran (pola REG) belum tersedia.</p>
                @else
                    <table class="table table-bordered jadwal-grid-table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="jam-label">Jam (REG)</th>
                                @foreach($grid['hariColumns'] as $hari)
                                    <th>{{ $hari }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grid['jamRows'] as $jam)
                                <tr>
                                    <td class="jam-label">
                                        <div><strong>Jam {{ $jam->jam_ke }}</strong></div>
                                        @if($jam->nama_sesi)
                                            <div class="text-muted">{{ $jam->nama_sesi }}</div>
                                        @endif
                                        <div class="text-muted">
                                            {{ substr((string) $jam->jam_mulai, 0, 5) }}–{{ substr((string) $jam->jam_selesai, 0, 5) }}
                                        </div>
                                    </td>
                                    @foreach($grid['hariColumns'] as $hari)
                                        @php
                                            $key = $hari.'|'.(int) $jam->jam_ke;
                                            $cell = $grid['cells'][$key] ?? ['jadwal' => null, 'css_classes' => ['jadwal-grid-cell--empty'], 'kode_kelas' => null];
                                            $jadwal = $cell['jadwal'] ?? null;
                                            $cssClasses = implode(' ', $cell['css_classes'] ?? ['jadwal-grid-cell--empty']);
                                            $deleteCheck = $jadwal ? ($deleteChecks[$jadwal->id] ?? ['allowed' => false]) : ['allowed' => false];
                                        @endphp
                                        <td class="jadwal-grid-cell {{ $cssClasses }} p-0"
                                            role="button"
                                            tabindex="0"
                                            data-toggle="modal"
                                            data-target="#gridCellModal"
                                            data-hari="{{ $hari }}"
                                            data-jam-ke="{{ $jam->jam_ke }}"
                                            data-jam-label="Jam {{ $jam->jam_ke }} ({{ substr((string) $jam->jam_mulai, 0, 5) }}–{{ substr((string) $jam->jam_selesai, 0, 5) }})"
                                            data-jadwal-id="{{ $jadwal?->id ?? '' }}"
                                            data-kelas="{{ e($cell['kode_kelas'] ?? $jadwal?->kelas ?? '') }}"
                                            data-mapel="{{ $jadwal?->mapel ?? '' }}"
                                            data-can-delete="{{ $deleteCheck['allowed'] ? '1' : '0' }}"
                                            data-delete-reason="{{ e($deleteCheck['reason'] ?? '') }}">
                                            <div class="jadwal-grid-cell-content">
                                                @if($jadwal)
                                                    <div class="kelas">{{ $jadwal->kelas }}</div>
                                                    <div>{{ $jadwal->mapel }}</div>
                                                @else
                                                    <span class="text-muted">+ Isi</span>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="card-footer jadwal-grid-legend small text-muted">
                <strong>Keterangan:</strong>
                <span class="ml-3"><span style="background:#e9ecef;"></span> Kosong</span>
                <span class="ml-3"><span style="background:#f0fff4;"></span> Terisi</span>
                <span class="ml-3"><span style="background:#fff3cd;border-color:#ffc107;"></span> Perlu perhatian (terbuka / menunggu perbaikan)</span>
                <span class="ml-3"><span style="background:#fff;border-color:#ffeb3b;"></span> Diubah dalam 24 jam</span>
            </div>
        </div>
    @elseif($guruId > 0 && ! $selectedGuru)
        <div class="alert alert-warning">Guru tidak ditemukan.</div>
    @else
        <div class="alert alert-info">Pilih guru, tahun pelajaran, dan semester lalu klik <strong>Tampilkan Grid</strong>.</div>
    @endif

</div>

<div class="modal fade" id="gridCellModal" tabindex="-1" role="dialog" aria-labelledby="gridCellModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gridCellModalLabel">Edit Sel Jadwal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="grid-upsert-form" method="POST" action="{{ route('jadwals.grid.upsert') }}">
                @csrf
                <input type="hidden" name="guru_id" value="{{ $guruId }}">
                <input type="hidden" name="tahun_pelajaran" value="{{ $tahunPelajaran }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <input type="hidden" name="hari" id="modal-hari" value="">
                <input type="hidden" name="jam_ke" id="modal-jam-ke" value="">

                <div class="modal-body">
                    <p class="mb-3">
                        <strong id="modal-slot-label">—</strong>
                        @if($selectedGuru)
                            <br><span class="text-muted">{{ $selectedGuru->nama }}</span>
                        @endif
                    </p>

                    <div class="form-group">
                        <label for="modal-kode-kelas">Kelas / Rombel</label>
                        <select name="kode_kelas" id="modal-kode-kelas" class="form-control" required>
                            <option value="">— Pilih Kelas —</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->kode_kelas }}">
                                    {{ $kelas->kelas }} {{ $kelas->rombel }} ({{ $kelas->kode_kelas }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="modal-mapel">Mata Pelajaran</label>
                        <select name="mapel" id="modal-mapel" class="form-control" required>
                            <option value="">— Pilih Mata Pelajaran —</option>
                            @foreach($mapelOptions as $mapelItem)
                                <option value="{{ $mapelItem->nama }}">{{ $mapelItem->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
            <form id="grid-clear-form" method="POST" action="" class="px-3 pb-3">
                @csrf
                @method('DELETE')
                <input type="hidden" name="guru_id" value="{{ $guruId }}">
                <input type="hidden" name="tahun_pelajaran" value="{{ $tahunPelajaran }}">
                <input type="hidden" name="semester" value="{{ $semester }}">
                <button type="submit" id="grid-clear-btn" class="btn btn-outline-danger btn-sm d-none">
                    Kosongkan Sel
                </button>
                <small id="grid-clear-hint" class="text-muted d-none d-block mt-1"></small>
            </form>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
(function () {
    var modal = document.getElementById('gridCellModal');
    if (!modal) return;

    var hariInput = document.getElementById('modal-hari');
    var jamKeInput = document.getElementById('modal-jam-ke');
    var slotLabel = document.getElementById('modal-slot-label');
    var kelasSelect = document.getElementById('modal-kode-kelas');
    var mapelSelect = document.getElementById('modal-mapel');
    var clearForm = document.getElementById('grid-clear-form');
    var clearBtn = document.getElementById('grid-clear-btn');
    var clearHint = document.getElementById('grid-clear-hint');

    document.querySelectorAll('.jadwal-grid-cell').forEach(function (cell) {
        cell.addEventListener('click', function () {
            var hari = cell.getAttribute('data-hari') || '';
            var jamKe = cell.getAttribute('data-jam-ke') || '';
            var jamLabel = cell.getAttribute('data-jam-label') || '';
            var jadwalId = cell.getAttribute('data-jadwal-id') || '';
            var kelas = cell.getAttribute('data-kelas') || '';
            var mapel = cell.getAttribute('data-mapel') || '';
            var canDelete = cell.getAttribute('data-can-delete') === '1';
            var deleteReason = cell.getAttribute('data-delete-reason') || '';

            if (hariInput) hariInput.value = hari;
            if (jamKeInput) jamKeInput.value = jamKe;
            if (slotLabel) slotLabel.textContent = hari + ' — ' + jamLabel;

            if (kelasSelect) kelasSelect.value = kelas;
            if (mapelSelect) mapelSelect.value = mapel;

            if (clearForm && clearBtn) {
                if (jadwalId) {
                    clearForm.action = '{{ url('jadwals') }}/' + jadwalId + '/grid-cell';
                    clearBtn.classList.toggle('d-none', !canDelete);
                    if (clearHint) {
                        clearHint.classList.toggle('d-none', canDelete || !deleteReason);
                        clearHint.textContent = canDelete ? '' : deleteReason;
                    }
                } else {
                    clearForm.action = '';
                    clearBtn.classList.add('d-none');
                    if (clearHint) clearHint.classList.add('d-none');
                }
            }
        });
    });
})();
</script>
@endpush
