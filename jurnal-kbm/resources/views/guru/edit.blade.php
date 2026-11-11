@extends('adminlte::page')

@section('title', 'Edit Guru')

@section('content_header')
    <h1>Edit Guru</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Form Edit Guru</h3>
    </div>

    <form action="{{ route('gurus.update', $guru) }}" method="POST">

        @csrf
        @method('PUT')

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
                       value="{{ old('nama', $guru->nama) }}"
                       required>
            </div>

            <div class="form-group">
                <label>NIP</label>
                <input type="text"
                       name="nip"
                       class="form-control"
                       value="{{ old('nip', $guru->nip) }}"
                       required>
            </div>

            <div class="form-group">
                <label>Mata Pelajaran</label>
                <input type="text"
                       name="mapel"
                       class="form-control"
                       value="{{ old('mapel', $guru->mapel) }}">
                <small class="text-muted">Opsional. Kosongkan jika tidak tersedia.</small>
            </div>

        </div>

        <div class="card-footer">

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="{{ route('gurus.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </form>

</div>

@stop
