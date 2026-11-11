@extends('adminlte::page')

@section('title', 'Data Guru')

@section('content_header')
    <h1>Data Guru</h1>
    <small class="text-muted">Tahun Pelajaran {{ \App\Services\GuruService::DEFAULT_TAHUN_PELAJARAN }}</small>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title">Filter Data Guru</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('gurus.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="nama" class="form-control" placeholder="Cari Nama" value="{{ request('nama') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nip" class="form-control" placeholder="Cari NIP" value="{{ request('nip') }}">
                </div>
                <div class="col-md-2">
                    <select name="mapel" class="form-control">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') === $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Punya Akun</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-search"></i> Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Master Data Guru</h3>
        <a href="{{ route('gurus.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Guru
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Kode Guru</th>
                    <th>Nama Guru</th>
                    <th>NIP</th>
                    <th>Mata Pelajaran</th>
                    <th>Jam Mengajar</th>
                    <th>Username SIM</th>
                    <th>Status Akun</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gurus as $guru)
                    @php
                        $status = $guru->user
                            ? ($guru->user->is_active ? 'Aktif' : 'Nonaktif')
                            : 'Belum Memiliki Akun';
                        $badge = match ($status) {
                            'Aktif' => 'success',
                            'Nonaktif' => 'secondary',
                            default => 'warning',
                        };
                    @endphp
                    <tr>
                        <td>{{ $gurus->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $guru->kode_guru }}</strong></td>
                        <td>{{ $guru->displayName() }}</td>
                        <td>{{ $guru->nip }}</td>
                        <td>{{ $guru->mapel }}</td>
                        <td>{{ $guru->jumlah_jam_mengajar }} JP</td>
                        <td>
                            @if ($guru->user)
                                <code>{{ $guru->user->email }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $badge }}">{{ $status }}</span></td>
                        <td class="text-nowrap">
                            <a href="{{ route('gurus.edit', $guru) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('gurus.destroy', $guru) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data guru ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data guru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-center">
        {{ $gurus->appends(request()->query())->links() }}
    </div>
</div>

@stop
