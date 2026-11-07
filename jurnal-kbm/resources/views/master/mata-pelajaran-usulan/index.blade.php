@extends('adminlte::page')

@section('title', 'Master Usulan Mata Pelajaran')

@section('content_header')
    <h1>Master Usulan Mata Pelajaran</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($pendingCount > 0)
    <div class="alert alert-info">
        <i class="fas fa-bell"></i> Ada <strong>{{ $pendingCount }}</strong> usulan mapel menunggu persetujuan.
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="GET" class="form-inline mb-3">
            <label class="mr-2">Status</label>
            <select name="status" class="form-control form-control-sm mr-3">
                <option value="">Semua</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Menunggu</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button class="btn btn-primary btn-sm">Filter</button>
        </form>

        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Guru</th>
                    <th>Nama Mapel</th>
                    <th>Status</th>
                    <th width="200">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usulans as $usulan)
                    <tr>
                        <td>{{ $usulans->firstItem() + $loop->index }}</td>
                        <td>{{ $usulan->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $usulan->guru->nama ?? '-' }}</td>
                        <td>{{ $usulan->nama }}</td>
                        <td>
                            @php
                                $badge = match($usulan->status) {
                                    'pending' => 'badge-warning',
                                    'approved' => 'badge-success',
                                    'rejected' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($usulan->status) }}</span>
                        </td>
                        <td>
                            @if($usulan->status === 'pending')
                                <form action="{{ route('mata-pelajaran-usulan.approve', $usulan) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm" onclick="return confirm('Setujui usulan ini?')">Setujui</button>
                                </form>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject{{ $usulan->id }}">Tolak</button>
                            @else
                                <small class="text-muted">{{ Str::limit($usulan->admin_catatan, 40) ?: 'Selesai' }}</small>
                            @endif
                        </td>
                    </tr>
                    @if($usulan->status === 'pending')
                    <div class="modal fade" id="reject{{ $usulan->id }}">
                        <div class="modal-dialog">
                            <form action="{{ route('mata-pelajaran-usulan.reject', $usulan) }}" method="POST" class="modal-content">
                                @csrf
                                <div class="modal-header"><h5 class="modal-title">Tolak Usulan</h5></div>
                                <div class="modal-body">
                                    <textarea name="admin_catatan" class="form-control" rows="3" placeholder="Catatan admin..."></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                    <button class="btn btn-danger">Tolak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada usulan.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $usulans->links() }}
    </div>
</div>

@stop
