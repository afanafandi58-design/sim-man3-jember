@extends('adminlte::page')

@section('title', 'Profil')

@section('content_header')
    <h1>Profil Saya</h1>
@stop

@section('content')
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">Profil berhasil diperbarui.</div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="alert alert-success">Password berhasil diubah.</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Informasi Profil</h3></div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header"><h3 class="card-title">Ganti Password</h3></div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-2">
        Lupa password? Hubungi admin untuk reset melalui menu Manajemen Akun Guru (jika akun guru),
        atau minta reset manual di cPanel/hosting untuk akun administrator.
    </p>
@stop
