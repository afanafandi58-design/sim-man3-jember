@extends('adminlte::page')

@section('title', 'Master Mata Pelajaran')

@section('content_header')
    <h1>Master Mata Pelajaran</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Daftar Mata Pelajaran</h3>
        <a href="{{ route('mata-pelajaran.create') }}" class="btn btn-success btn-sm">+ Tambah</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th width="150">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mataPelajarans as $item)
                    <tr>
                        <td>{{ $mataPelajarans->firstItem() + $loop->index }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>
                            <span class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }}">
                                {{ $item->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('mata-pelajaran.edit', $item) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('mata-pelajaran.destroy', $item) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Nonaktifkan mapel ini?')">Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $mataPelajarans->links() }}
    </div>
</div>

@stop
