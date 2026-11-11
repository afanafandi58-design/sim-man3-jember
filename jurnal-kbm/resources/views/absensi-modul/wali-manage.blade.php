@extends('adminlte::page')

@section('title', 'Kelola Wali Kelas')

@section('content_header')
    <h1>Kelola Assignment Wali Kelas</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card card-primary">
    <div class="card-header">Tambah / update</div>
    <div class="card-body">
        <form method="POST" action="{{ route('absensi-modul.wali.store') }}" class="form-row">
            @csrf
            <div class="form-group col-md-4">
                <label>Guru</label>
                <select name="guru_id" class="form-control" required>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Kelas</label>
                <select name="kelas" class="form-control" required>
                    @foreach($kelasList as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Rombel</label>
                <input type="text" name="rombel" class="form-control" placeholder="opsional">
            </div>
            <div class="form-group col-md-2">
                <label>TP</label>
                <input type="text" name="tahun_pelajaran" class="form-control" placeholder="2026/2027">
            </div>
            <div class="form-group col-md-2 d-flex align-items-end">
                <button class="btn btn-success btn-block">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Guru</th><th>Kelas</th><th>Rombel</th><th>TP</th><th></th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->guru->nama ?? '-' }}</td>
                    <td>{{ $row->kelas }}</td>
                    <td>{{ $row->rombel ?: '-' }}</td>
                    <td>{{ $row->tahun_pelajaran ?: '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('absensi-modul.wali.destroy', $row) }}" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
