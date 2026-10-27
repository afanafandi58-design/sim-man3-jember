@extends('adminlte::page')

@section('title', 'Permintaan Perubahan Jadwal')

@section('content_header')
    <h1>Permintaan Perubahan Jadwal</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

@if($pendingCount > 0)
    <div class="alert alert-info">
        <i class="fas fa-bell"></i>
        Ada <strong>{{ $pendingCount }}</strong> permintaan perbaikan yang menunggu tindakan (Setujui / Tolak).
    </div>
@endif

@if(($needEditCount ?? 0) > 0)
    <div class="alert alert-warning">
        <i class="fas fa-edit"></i>
        Ada <strong>{{ $needEditCount }}</strong> permintaan sudah disetujui tetapi jadwal masih terbuka —
        selesaikan lewat <strong>Ubah Jadwal</strong> (lalu simpan agar terkunci lagi).
        <a href="{{ route('jadwal-perbaikan.index', ['status' => 'perlu_ubah']) }}" class="alert-link ml-1">Filter: Perlu ubah jadwal</a>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan</h3>
    </div>

    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <label class="mr-2">Status</label>
            <select name="status" class="form-control form-control-sm mr-3">
                <option value="">Semua</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="perlu_ubah" {{ $status === 'perlu_ubah' ? 'selected' : '' }}>Perlu ubah jadwal</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Guru</th>
                        <th>Jadwal</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $statusClass = match($req->status) {
                                'pending' => 'badge-warning',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger',
                                default => 'badge-secondary',
                            };
                            $statusText = match($req->status) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => $req->status,
                            };
                        @endphp
                        <tr>
                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $req->guru->nama ?? '-' }}</td>
                            <td>
                                {{ $req->jadwal->kelas ?? '-' }} /
                                {{ $req->jadwal->mapel ?? '-' }} /
                                {{ $req->jadwal->hari ?? '-' }} Jam {{ $req->jadwal->jam_ke ?? '-' }}
                            </td>
                            <td>{{ Str::limit($req->alasan, 80) }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusText }}</span></td>
                            <td>
                                @if($req->status === 'pending')
                                    <form action="{{ route('jadwal-perbaikan.approve', $req) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm"
                                                onclick="return confirm('Setujui permintaan ini? Jadwal akan dibuka sementara untuk diedit.')">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            data-toggle="modal" data-target="#rejectModal{{ $req->id }}">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                @elseif($req->status === 'approved' && $req->jadwal && ! $req->jadwal->is_locked)
                                    <a href="{{ route('jadwal-perbaikan.edit', $req) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Ubah Jadwal
                                    </a>
                                @else
                                    <span class="text-muted small">
                                        @if($req->admin_catatan)
                                            {{ Str::limit($req->admin_catatan, 40) }}
                                        @else
                                            Selesai
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>

                        @if($req->status === 'pending')
                        <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('jadwal-perbaikan.reject', $req) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak Permintaan</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Guru: <strong>{{ $req->guru->nama ?? '-' }}</strong></p>
                                            <div class="form-group">
                                                <label>Catatan Admin (opsional)</label>
                                                <textarea name="admin_catatan" class="form-control" rows="3"
                                                          placeholder="Alasan penolakan..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada permintaan perubahan jadwal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requests->links() }}
    </div>
</div>

@stop
