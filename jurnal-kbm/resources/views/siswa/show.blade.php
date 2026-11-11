@extends('adminlte::page')

@section('title', 'Detail Siswa')

@section('content_header')
    <h1>Detail Siswa</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">{{ $siswa->nama }}</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th width="200">NIS</th>
                <td>{{ $siswa->nis }}</td>
            </tr>
            <tr>
                <th>NISN</th>
                <td>{{ $siswa->nisn ?: '-' }}</td>
            </tr>
            <tr>
                <th>Nama</th>
                <td>{{ $siswa->nama }}</td>
            </tr>
            <tr>
                <th>Jenis Kelamin</th>
                <td>{{ $siswa->jenisKelaminLabel() }}</td>
            </tr>
            <tr>
                <th>Tempat, Tanggal Lahir</th>
                <td>
                    {{ $siswa->tempat_lahir ?: '-' }}{{ $siswa->tanggal_lahir ? ', '.$siswa->tanggal_lahir->format('d F Y') : '' }}
                </td>
            </tr>
            <tr>
                <th>Kelas</th>
                <td>{{ $siswa->kelasDisplay() }}</td>
            </tr>
            <tr>
                <th>Kode Kelas</th>
                <td>{{ $siswa->kode_kelas }}</td>
            </tr>
            <tr>
                <th>Tahun Pelajaran</th>
                <td>{{ $siswa->tahun_pelajaran }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $siswa->statusLabel() }}</td>
            </tr>
        </table>
    </div>
    <div class="card-footer">
        <a href="{{ $routes['index'] }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        @if($canManage && $siswa->status !== \App\Models\Siswa::STATUS_NONAKTIF)
            <a href="{{ route('siswas.edit', $siswa) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
        @endif
    </div>
</div>
@stop
