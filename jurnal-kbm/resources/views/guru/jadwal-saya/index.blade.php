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

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Jadwal</h3>
        <a href="{{ route('guru.jadwal-saya.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </a>
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
                        <th width="160">Aksi</th>
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
                                @if($jadwal->is_locked && ! $jadwal->pendingPerbaikanRequest)
                                    <a href="{{ route('guru.jadwal-saya.perbaikan', $jadwal) }}"
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-paper-plane"></i> Ajukan Perbaikan
                                    </a>
                                @elseif($jadwal->pendingPerbaikanRequest)
                                    <span class="text-muted small">Menunggu admin</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
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
