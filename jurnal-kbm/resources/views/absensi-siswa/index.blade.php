@extends('adminlte::page')

@section('title', 'Absensi Siswa')

@section('content_header')
    <h1>Absensi Siswa</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Filter Absensi</h3>
    </div>
    <form method="GET" action="{{ route('absensi-siswa.index') }}">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ $filters['tanggal'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Guru</label>
                        <select name="guru_id" class="form-control">
                            <option value="">Semua</option>
                            @foreach($gurus as $guru)
                                <option value="{{ $guru->id }}" @selected(($filters['guru_id'] ?? '') == $guru->id)>{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Mapel</label>
                        <select name="mapel" class="form-control">
                            <option value="">Semua</option>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel }}" @selected(($filters['mapel'] ?? '') === $mapel)>{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="kelas" class="form-control">
                            <option value="">Semua</option>
                            @foreach($kelas as $item)
                                <option value="{{ $item }}" @selected(($filters['kelas'] ?? '') === $item)>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Rombel</label>
                        <select name="rombel" class="form-control">
                            <option value="">Semua</option>
                            @foreach($rombels as $rombel)
                                <option value="{{ $rombel }}" @selected(($filters['rombel'] ?? '') === $rombel)>{{ $rombel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">Semua</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            <a href="{{ route('absensi-siswa.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Guru</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Rombel</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $row)
                    <tr>
                        <td>{{ $absensi->firstItem() + $loop->index }}</td>
                        <td>{{ optional($row->jurnal)->tanggal }}</td>
                        <td>{{ $row->jadwal->guru->nama ?? '-' }}</td>
                        <td>{{ $row->jadwal->mapel ?? '-' }}</td>
                        <td>{{ $row->siswa->kelas ?? '-' }}</td>
                        <td>{{ $row->siswa->rombel ?? '-' }}</td>
                        <td>{{ $row->siswa->nis ?? '-' }}</td>
                        <td>{{ $row->siswa->nama ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $row->status === 'Hadir' ? 'success' : ($row->status === 'Alpha' ? 'danger' : 'warning') }}">
                                {{ $row->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Belum ada data absensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($absensi->hasPages())
        <div class="card-footer">
            {{ $absensi->links() }}
        </div>
    @endif
</div>
@stop
