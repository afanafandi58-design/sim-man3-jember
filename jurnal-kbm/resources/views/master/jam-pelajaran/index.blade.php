@extends('adminlte::page')

@section('title', 'Master Jam Pelajaran')

@section('content_header')
    <h1>Master Jam Pelajaran</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Jam Pelajaran</h3>
        <a href="{{ route('jam-pelajaran.create') }}" class="btn btn-success btn-sm">+ Tambah</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>Pola</th>
                    <th>Jam Ke</th>
                    <th>Nama Sesi</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jamPelajarans as $jam)
                    <tr>
                        <td>{{ $jam->polaJadwal->kode ?? '-' }}</td>
                        <td>{{ $jam->jam_ke }}</td>
                        <td>{{ $jam->nama_sesi ?: '-' }}</td>
                        <td>{{ substr($jam->jam_mulai, 0, 5) }}</td>
                        <td>{{ substr($jam->jam_selesai, 0, 5) }}</td>
                        <td>
                            <span class="badge {{ $jam->status === 'aktif' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($jam->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('jam-pelajaran.edit', $jam) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('jam-pelajaran.destroy', $jam) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan jam ini?')">Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $jamPelajarans->links() }}
    </div>
</div>

@stop
