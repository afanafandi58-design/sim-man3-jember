@extends('adminlte::page')

@section('title', 'Manajemen Akun Guru')

@section('content_header')
    <h1>Manajemen Akun Guru</h1>
    <small class="text-muted">Tahun Pelajaran {{ \App\Services\GuruService::DEFAULT_TAHUN_PELAJARAN }}</small>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if(session('generated_credentials'))
    @php $credentials = session('generated_credentials'); @endphp
    <div class="alert alert-warning alert-dismissible fade show">
        <h5 class="alert-heading"><i class="fas fa-key mr-1"></i> Kredensial Login (ditampilkan satu kali)</h5>
        <p class="mb-1"><strong>Kode:</strong> {{ $credentials['kode_guru'] ?? '-' }}</p>
        <p class="mb-1"><strong>Nama:</strong> {{ $credentials['nama'] }}</p>
        <p class="mb-1"><strong>Username:</strong> <code>{{ $credentials['email'] }}</code></p>
        <p class="mb-1"><strong>Password:</strong> <code>{{ $credentials['password'] }}</code></p>
        <hr>
        <p class="mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            Simpan password sekarang. Guru wajib segera mengganti password setelah login pertama.
        </p>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <a href="{{ route('guru-accounts.cetak') }}" target="_blank" class="btn btn-danger">
        <i class="fas fa-file-pdf"></i> Cetak Akun Guru
    </a>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Filter</h3></div>
    <div class="card-body">
        <form action="{{ route('guru-accounts.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="nama" class="form-control" placeholder="Cari Nama" value="{{ request('nama') }}">
                </div>
                <div class="col-md-3">
                    <input type="text" name="nip" class="form-control" placeholder="Cari NIP" value="{{ request('nip') }}">
                </div>
                <div class="col-md-2">
                    <select name="mapel" class="form-control">
                        <option value="">Semua Mapel</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel }}" {{ request('mapel') === $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Punya Akun</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success btn-block"><i class="fas fa-search"></i> Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">Daftar Akun Guru</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Guru</th>
                    <th>NIP</th>
                    <th>Mapel</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Reset Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($gurus as $guru)
                    @php
                        $user = $guru->user;
                        $status = $user ? ($user->is_active ? 'Aktif' : 'Nonaktif') : 'Belum Memiliki Akun';
                        $badge = match ($status) {
                            'Aktif' => 'success',
                            'Nonaktif' => 'secondary',
                            default => 'warning',
                        };
                    @endphp
                    <tr>
                        <td>{{ $gurus->firstItem() + $loop->index }}</td>
                        <td>{{ $guru->kode_guru }}</td>
                        <td>{{ $guru->displayName() }}</td>
                        <td>{{ $guru->nip }}</td>
                        <td>{{ $guru->mapel }}</td>
                        <td>@if($user)<code>{{ $user->email }}</code>@else<span class="text-muted">-</span>@endif</td>
                        <td><span class="badge badge-{{ $badge }}">{{ $status }}</span></td>
                        <td>{{ $user?->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $user?->password_reset_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="text-nowrap">
                            @unless ($user)
                                <form action="{{ route('guru-accounts.store', $guru) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Buat akun untuk {{ $guru->displayName() }}?')">
                                        <i class="fas fa-user-plus"></i> Buat Akun
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('guru-accounts.reset-password', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Reset password {{ $user->email }}?')">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </form>
                                @if ($user->is_active)
                                    <form action="{{ route('guru-accounts.deactivate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Nonaktifkan akun ini?')">Nonaktifkan</button>
                                    </form>
                                @else
                                    <form action="{{ route('guru-accounts.activate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Aktifkan akun ini?')">Aktifkan</button>
                                    </form>
                                @endif
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-center">
        {{ $gurus->appends(request()->query())->links() }}
    </div>
</div>

@stop
