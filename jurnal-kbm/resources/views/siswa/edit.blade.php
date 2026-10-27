@extends('adminlte::page')

@section('title', 'Edit Siswa')

@section('content_header')
    <h1>Edit Siswa</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Edit Siswa</h3>
    </div>
    <form action="{{ route('siswas.update', $siswa) }}" method="POST">
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

            @include('siswa._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            <a href="{{ route('siswas.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@stop
