@extends('adminlte::page')

@section('title', 'Jurnal KBM')

@section('content_header')
    <h1>Jurnal KBM</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3 align-items-center">
        <h3 class="mb-0">Daftar Jurnal KBM</h3>
        <a href="{{ route('jurnals.create') }}" class="btn btn-primary">+ Tambah Jurnal</a>
    </div>

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('jurnals.index') }}" class="row g-2">
                <div class="col-md-2">
                    <label class="small mb-1">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $filters['tanggal'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="small mb-1">Guru</label>
                    <select name="guru_id" class="form-control">
                        <option value="">Semua</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" @selected(($filters['guru_id'] ?? '') == $guru->id)>{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small mb-1">Kelas</label>
                    <select name="kelas" class="form-control">
                        <option value="">Semua</option>
                        @foreach($kelas as $item)
                            <option value="{{ $item }}" @selected(($filters['kelas'] ?? '') == $item)>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="small mb-1">Mapel</label>
                    <select name="mapel" class="form-control">
                        <option value="">Semua</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel }}" @selected(($filters['mapel'] ?? '') == $mapel)>{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small mb-1">Cari materi</label>
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Kata kunci materi..." value="{{ $filters['q'] ?? '' }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            <a href="{{ route('jurnals.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-success">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>Pengganti</th>
                <th>Jam</th>
                <th>Materi</th>
                <th>Dokumentasi</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($jurnals as $jurnal)
            <tr>
                <td>{{ $jurnals->firstItem() + $loop->index }}</td>
                <td>{{ $jurnal->tanggal }}</td>
                <td>{{ $jurnal->jadwal->kelas ?? '-' }}</td>
                <td>{{ $jurnal->guru_pengganti ?: '-' }}</td>
                <td>{{ $jurnal->jam_ke }}</td>
                <td>{{ \Illuminate\Support\Str::limit($jurnal->materi, 40) }}</td>
                <td>@include('guru.jurnal.partials.lampiran-foto-index', ['jurnal' => $jurnal])</td>
                <td>{{ $jurnal->hadir }}</td>
                <td>{{ $jurnal->izin }}</td>
                <td>{{ $jurnal->sakit }}</td>
                <td>{{ $jurnal->alpha }}</td>
                <td class="text-nowrap">
                    <a href="{{ route('jurnals.show', $jurnal) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                    <a href="{{ route('jurnals.edit', $jurnal) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-danger"
                            data-toggle="modal"
                            data-target="#hapusJurnalModal"
                            data-jurnal-id="{{ $jurnal->id }}"
                            data-jurnal-label="{{ $jurnal->tanggal }} — {{ $jurnal->jadwal->kelas ?? '-' }}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="text-center">Belum ada data jurnal.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $jurnals->links() }}
    </div>
</div>

<div class="modal fade" id="hapusJurnalModal" tabindex="-1" role="dialog" aria-labelledby="hapusJurnalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hapusJurnalModalLabel">Konfirmasi Hapus Jurnal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Batal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Data Jurnal beserta lampiran akan dihapus.</p>
                <p class="mb-0">Apakah Anda yakin?</p>
                <p class="text-muted small mt-2 mb-0" id="hapusJurnalLabel"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="hapusJurnalForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Ya Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#hapusJurnalModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const jurnalId = button.data('jurnal-id');
            const jurnalLabel = button.data('jurnal-label') || '';
            const actionUrl = @json(url('jurnals')) + '/' + jurnalId;
            $('#hapusJurnalLabel').text(jurnalLabel);
            $('#hapusJurnalForm').attr('action', actionUrl);
        });
    });
</script>
@endpush
@stop
