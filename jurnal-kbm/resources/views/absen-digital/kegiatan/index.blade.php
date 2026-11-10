@extends('adminlte::page')

@section('title', 'Kegiatan Absen Digital')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-2">Kegiatan</h1>
        @can('role-absen-admin')
            <a href="{{ route('absen-digital.kegiatan.create') }}" class="btn btn-success mb-2">
                <i class="fas fa-plus"></i> Tambah kegiatan
            </a>
        @endcan
    </div>
@stop

@section('content')
    @include('absen-digital.partials.alerts')
    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Nama</th>
                        <th>Tempat</th>
                        <th class="text-right">Hadir</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kegiatans as $kegiatan)
                        <tr>
                            <td>{{ $kegiatan->tanggalLabel() }}</td>
                            <td>{{ $kegiatan->jenisLabel() }}</td>
                            <td>{{ $kegiatan->nama }}</td>
                            <td>{{ $kegiatan->tempat ?: '—' }}</td>
                            <td class="text-right">{{ $kegiatan->kehadirans_count }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('absen-digital.kegiatan.show', $kegiatan) }}" class="btn btn-xs btn-primary">Detail</a>
                                @can('role-absen-admin')
                                    <a href="{{ route('absen-digital.kegiatan.edit', $kegiatan) }}" class="btn btn-xs btn-default">Ubah</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada kegiatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kegiatans->hasPages())
            <div class="card-footer">{{ $kegiatans->links() }}</div>
        @endif
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/absen-digital.css') }}">
@stop
