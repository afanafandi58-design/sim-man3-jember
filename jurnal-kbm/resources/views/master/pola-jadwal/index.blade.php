@extends('adminlte::page')

@section('title', 'Master Pola Jadwal')

@section('content_header')<h1>Master Pola Jadwal</h1>@stop

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Pola Jadwal</h3>
        <a href="{{ route('pola-jadwal.create') }}" class="btn btn-success btn-sm">+ Tambah</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr><th>Kode</th><th>Nama</th><th>Status</th><th width="100">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($polaJadwals as $pola)
                    <tr>
                        <td><strong>{{ $pola->kode }}</strong></td>
                        <td>{{ $pola->nama }}</td>
                        <td><span class="badge {{ $pola->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">{{ ucfirst($pola->status) }}</span></td>
                        <td><a href="{{ route('pola-jadwal.edit', $pola) }}" class="btn btn-warning btn-sm">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $polaJadwals->links() }}
    </div>
</div>
@stop
