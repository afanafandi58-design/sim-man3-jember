@extends('adminlte::page')

@section('title', 'Data Siswa')

@section('content_header')
    <h1>Data Siswa</h1>
    <small class="text-muted">Tahun Pelajaran {{ \App\Services\SiswaAdminService::DEFAULT_TAHUN_PELAJARAN }}</small>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title">Filter & Pencarian</h3>
    </div>
    <div class="card-body">
        <form action="{{ $routes['index'] }}" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari NIS / NISN / Nama" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-3">
                    <select name="kelas" class="form-control">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasOptions as $kodeKelas)
                            <option value="{{ $kodeKelas }}" {{ request('kelas') === $kodeKelas ? 'selected' : '' }}>{{ $kodeKelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ \App\Models\Siswa::STATUS_LABELS[$status] ?? ucfirst($status) }}
                            </option>
                        @endforeach
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
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2 mb-md-0">Daftar Siswa</h3>
        @if($canManage)
            <div class="d-flex flex-wrap">
                <a href="{{ $routes['create'] ?? route('siswas.create') }}" class="btn btn-primary btn-sm mr-1 mb-1">
                    <i class="fas fa-plus"></i> Tambah Siswa
                </a>
                <a href="{{ $routes['import'] ?? route('import-siswa.index') }}" class="btn btn-info btn-sm mr-1 mb-1">
                    <i class="fas fa-file-upload"></i> Import Excel
                </a>
                <a href="{{ $routes['export'] ?? route('siswas.export', request()->query()) }}" class="btn btn-success btn-sm mb-1">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
            </div>
        @endif
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Jenis Kelamin</th>
                    <th>Kelas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($siswas as $siswa)
                    <tr>
                        <td>{{ $siswas->firstItem() + $loop->index }}</td>
                        <td>{{ $siswa->nis }}</td>
                        <td>{{ $siswa->nisn ?: '-' }}</td>
                        <td>{{ $siswa->nama }}</td>
                        <td>{{ $siswa->jenisKelaminLabel() }}</td>
                        <td>{{ $siswa->kelasDisplay() }}</td>
                        <td>
                            <span class="badge badge-{{ $siswa->status === 'aktif' ? 'success' : 'secondary' }}">
                                {{ $siswa->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route($routes['show'], $siswa) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @if($canManage && $siswa->status !== \App\Models\Siswa::STATUS_NONAKTIF)
                                <a href="{{ route('siswas.edit', $siswa) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button type="button"
                                        class="btn btn-danger btn-sm"
                                        data-toggle="modal"
                                        data-target="#nonaktifSiswaModal"
                                        data-action="{{ route('siswas.destroy', $siswa) }}"
                                        data-label="{{ $siswa->nama }} ({{ $siswa->nis }})">
                                    <i class="fas fa-user-slash"></i> Nonaktifkan
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data siswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($siswas->hasPages())
        <div class="card-footer">
            {{ $siswas->links() }}
        </div>
    @endif
</div>

@if($canManage)
<div class="modal fade" id="nonaktifSiswaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Nonaktifkan Siswa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Batal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Data siswa akan dinonaktifkan (bukan dihapus permanen).</p>
                <p class="mb-0">Apakah Anda yakin?</p>
                <p class="text-muted small mt-2 mb-0" id="nonaktifSiswaLabel"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="nonaktifSiswaForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya, Nonaktifkan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#nonaktifSiswaModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            $('#nonaktifSiswaLabel').text(button.data('label') || '');
            $('#nonaktifSiswaForm').attr('action', button.data('action'));
        });
    });
</script>
@endpush
@endif

@stop
