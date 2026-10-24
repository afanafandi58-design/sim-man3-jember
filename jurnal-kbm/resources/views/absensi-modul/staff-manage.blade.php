@extends('adminlte::page')

@section('title', 'Akun Staff Absensi')

@section('content_header')
    <h1>Akun Staff Absensi</h1>
@stop

@section('content')
<div class="alert alert-info">
    Role <code>staff</code> hanya untuk modul absensi harian (input + tool + laporan).
    Tidak punya akses master data / jurnal admin.
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('generated_credentials'))
    @php $c = session('generated_credentials'); @endphp
    <div class="alert alert-warning">
        Simpan kredensial (tampil sekali):
        <strong>{{ $c['nama'] ?? '' }}</strong> —
        email <code>{{ $c['email'] }}</code> /
        password <code>{{ $c['password'] }}</code>
    </div>
@endif

<div class="card card-primary">
    <div class="card-header">Buat akun staff</div>
    <div class="card-body">
        <form method="POST" action="{{ route('absensi-modul.staff.store') }}" class="form-row">
            @csrf
            <div class="form-group col-md-4">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required maxlength="120" value="{{ old('name') }}">
            </div>
            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required maxlength="190" value="{{ old('email') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Password (opsional)</label>
                <input type="text" name="password" class="form-control" minlength="8" placeholder="kosong = auto">
            </div>
            <div class="form-group col-md-1 d-flex align-items-end">
                <button class="btn btn-success btn-block">Buat</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Nama</th><th>Email</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($staff as $u)
                <tr>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if($u->is_active)
                            <span class="badge badge-success">aktif</span>
                        @else
                            <span class="badge badge-secondary">nonaktif</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <form method="POST" action="{{ route('absensi-modul.staff.reset', $u) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-warning">Reset password</button>
                        </form>
                        <form method="POST" action="{{ route('absensi-modul.staff.toggle', $u) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-outline-secondary">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted p-3">Belum ada akun staff.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
