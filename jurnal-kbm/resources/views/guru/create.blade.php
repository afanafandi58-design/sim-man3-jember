@extends('adminlte::page')

@section('title', 'Tambah Guru')

@section('content_header')
    <h1>Tambah Guru</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Form Tambah Guru</h3>
    </div>

    <form action="{{ route('gurus.store') }}" method="POST">

        @csrf

        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label>Nama Guru</label>
                <input type="text"
                       name="nama"
                       class="form-control"
                       required>
            </div>

            <div class="form-group">
                <label>NIP</label>
                <input type="text"
                       name="nip"
                       class="form-control"
                       required>
            </div>

            <div class="form-group">
                <label>Mata Pelajaran</label>
                <input type="text"
                       name="mapel"
                       class="form-control"
                       value="{{ old('mapel') }}">
                <small class="text-muted">Opsional. Kosongkan jika tidak tersedia.</small>
            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-success">
                Simpan
            </button>

            <a href="{{ route('gurus.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </form>

</div>

@stop