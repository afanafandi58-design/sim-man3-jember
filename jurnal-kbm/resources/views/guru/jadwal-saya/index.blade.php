@extends('adminlte::page')

@section('title', 'Jadwal Saya')

@section('content_header')
    <h1>Jadwal Saya</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Data Guru</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4"><strong>NIP:</strong> {{ $guru->nip ?? '-' }}</div>
            <div class="col-md-4"><strong>Nama:</strong> {{ $guru->nama }}</div>
            <div class="col-md-4"><strong>Mapel:</strong> {{ $guru->mapel ?? '-' }}</div>
        </div>
    </div>
</div>

@if ($errors->has('reset'))
    <div class="alert alert-danger">{{ $errors->first('reset') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">Daftar Jadwal Aktif</h3>
        <div class="mt-1 mt-md-0">
            <a href="{{ route('guru.jadwal-saya.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <label class="mr-2">Tahun Pelajaran</label>
            <input type="text" name="tahun_pelajaran" class="form-control form-control-sm mr-3"
                   value="{{ $tahunPelajaran }}" placeholder="2026/2027">

            <label class="mr-2">Semester</label>
            <select name="semester" class="form-control form-control-sm mr-3">
                <option value="">Semua</option>
                <option value="Ganjil" {{ $semester === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="Genap" {{ $semester === 'Genap' ? 'selected' : '' }}>Genap</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>

        @if($jadwals->total() > 0)
            <div class="alert alert-light border mb-3">
                <form action="{{ route('guru.jadwal-saya.reset-aktif') }}" method="POST" class="mb-0"
                      onsubmit="return confirm('Keluarkan SEMUA slot aktif pada filter ini dari daftar? Jurnal, absensi, dan jejak tetap tersimpan — tidak dihapus.');">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran" value="{{ $tahunPelajaran }}">
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="confirm-reset" name="confirm" value="1" required>
                        <label class="custom-control-label" for="confirm-reset">
                            Saya paham slot akan dikeluarkan dari daftar aktif (bukan dihapus), hanya jadwal milik saya.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-broom"></i> Reset daftar aktif
                        ({{ $tahunPelajaran }}{{ $semester ? ' / '.$semester : '' }} — {{ $jadwals->total() }} slot)
                    </button>
                    <small class="text-muted d-block mt-2">
                        Setelah reset, daftar ini kosong supaya bisa disusun ulang. Riwayat jurnal/lampiran/rekap pada slot lama tetap bisa dibuka.
                    </small>
                </form>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Hari</th>
                        <th>Rentang Jam</th>
                        <th>Jam</th>
                        <th>TP</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwals as $jadwal)
                        @php
                            $hasPending = $jadwal->pendingPerbaikanRequest !== null;
                            $statusLabel = $hasPending ? 'MENUNGGU PERBAIKAN' : ($jadwal->is_locked ? 'TERKUNCI' : 'TERBUKA');
                            $statusClass = match($statusLabel) {
                                'TERKUNCI' => 'badge-success',
                                'MENUNGGU PERBAIKAN' => 'badge-warning',
                                default => 'badge-info',
                            };
                        @endphp
                        <tr>
                            <td>{{ $jadwals->firstItem() + $loop->index }}</td>
                            <td>{{ $jadwal->kelas }}</td>
                            <td>{{ $jadwal->mapel }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>{{ $jadwal->jamRangeLabel() }}</td>
                            <td>{{ substr((string) $jadwal->jam_mulai, 0, 5) }} - {{ substr((string) $jadwal->jam_selesai, 0, 5) }}</td>
                            <td>{{ $jadwal->tahun_pelajaran }}</td>
                            <td>{{ $jadwal->semester }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm">
                                    @if($jadwal->is_locked && ! $jadwal->pendingPerbaikanRequest)
                                        <a href="{{ route('guru.jadwal-saya.perbaikan', $jadwal) }}"
                                           class="btn btn-warning btn-sm mb-1">
                                            <i class="fas fa-edit"></i> Perbaiki Slot
                                        </a>
                                    @elseif($jadwal->pendingPerbaikanRequest)
                                        <span class="text-muted small mb-1">Menunggu admin</span>
                                    @endif

                                    @if(!empty($jadwal->is_moving_class))
                                        <span class="badge badge-primary mb-1">
                                            MOVING
                                            @if($jadwal->moving_kelas_nama)
                                                · {{ \Illuminate\Support\Str::limit($jadwal->moving_kelas_nama, 18) }}
                                            @endif
                                        </span>
                                        <a href="{{ route('guru.jadwal-saya.moving', $jadwal) }}"
                                           class="btn btn-outline-primary btn-sm mb-1">
                                            <i class="fas fa-users"></i> Atur Kelas Moving
                                        </a>
                                        <form action="{{ route('guru.jadwal-saya.moving-toggle', $jadwal) }}" method="POST"
                                              onsubmit="return confirm('Hapus penanda moving class dari slot ini? Daftar gabungan dikosongkan; data rombel/siswa tetap ada.');">
                                            @csrf
                                            <input type="hidden" name="enabled" value="0">
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">Lepas Moving</button>
                                        </form>
                                    @else
                                        <form action="{{ route('guru.jadwal-saya.moving-toggle', $jadwal) }}" method="POST"
                                              onsubmit="return confirm('Tandai slot ini sebagai moving class?');">
                                            @csrf
                                            <input type="hidden" name="enabled" value="1">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-exchange-alt"></i> Tandai Moving
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                Belum ada jadwal. Klik <strong>Tambah Jadwal</strong> untuk mulai menyusun jadwal semester.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $jadwals->links() }}
    </div>
</div>

@stop
