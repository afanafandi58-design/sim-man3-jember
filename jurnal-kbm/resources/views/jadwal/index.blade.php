@extends('adminlte::page')

@section('title', 'Master Jadwal')

@section('content_header')
    <h1>Master Jadwal</h1>
@stop

@section('content')

<div class="container">

    <div class="d-flex justify-content-between mb-3">
        <h3>Master Jadwal</h3>

        <a href="{{ route('jadwals.create') }}" class="btn btn-success">
            + Tambah Jadwal
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <table class="table table-bordered table-striped">

        <thead>

            <tr>
            <th>No</th>
    <th>Guru</th>
    <th>Kelas</th>
    <th>Mapel</th>
    <th>Hari</th>
    <th>Jam</th>
    <th width="220">Aksi</th>
            </tr>

        </thead>

        <tbody>

        @forelse($jadwals as $jadwal)
        @php
            $deleteCheck = $deleteChecks[$jadwal->id] ?? ['allowed' => false, 'reason' => 'Jadwal tidak dapat dihapus karena masih digunakan.'];
        @endphp

<tr>

    <td>{{ $loop->iteration }}</td>

    <td>{{ $jadwal->guru->nama ?? '-' }}</td>

    <td>{{ $jadwal->kelas }}</td>

    <td>{{ $jadwal->mapel }}</td>

    <td>{{ $jadwal->hari }}</td>

    <td>{{ $jadwal->jamRangeLabel() }} ({{ substr((string) $jadwal->jam_mulai, 0, 5) }} - {{ substr((string) $jadwal->jam_selesai, 0, 5) }})</td>

    <td>

        <a href="{{ route('jadwals.edit', $jadwal->id) }}"
           class="btn btn-warning btn-sm">
            Edit
        </a>

        @if($deleteCheck['allowed'])
            <button type="button"
                    class="btn btn-danger btn-sm btn-hapus-jadwal"
                    data-toggle="modal"
                    data-target="#hapusJadwalModal"
                    data-jadwal-id="{{ $jadwal->id }}"
                    data-jadwal-label="{{ $jadwal->guru->nama ?? '-' }} — {{ $jadwal->kelas }} / {{ $jadwal->mapel }} / {{ $jadwal->hari }}">
                Hapus
            </button>
        @else
            <button type="button"
                    class="btn btn-secondary btn-sm"
                    disabled
                    title="{{ $deleteCheck['reason'] }}">
                Hapus
            </button>
        @endif

    </td>

</tr>

@empty

<tr>
    <td colspan="7" class="text-center">
        Belum ada data jadwal.
    </td>
</tr>

@endforelse

</tbody>

    </table>

    {{ $jadwals->links() }}

</div>

<div class="modal fade" id="hapusJadwalModal" tabindex="-1" role="dialog" aria-labelledby="hapusJadwalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="hapus-jadwal-form" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="hapusJadwalModalLabel">PERINGATAN</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="mb-2">Anda akan menghapus jadwal ini.</p>
                    <p class="font-weight-bold mb-3" id="hapus-jadwal-label">-</p>
                    <p class="text-muted mb-3">Pastikan jadwal sudah tidak digunakan.</p>

                    <div class="form-group mb-0">
                        <label for="alasan_hapus">Alasan Penghapusan (opsional)</label>
                        <textarea name="alasan_hapus" id="alasan_hapus" class="form-control" rows="2"
                                  maxlength="500" placeholder="Contoh: jadwal duplikat setelah perbaikan"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">BATAL</button>
                    <button type="submit" class="btn btn-danger">YA, HAPUS</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@push('js')
<script>
(function () {
    var modal = document.getElementById('hapusJadwalModal');
    var form = document.getElementById('hapus-jadwal-form');
    var label = document.getElementById('hapus-jadwal-label');
    var alasan = document.getElementById('alasan_hapus');

    document.querySelectorAll('.btn-hapus-jadwal').forEach(function (button) {
        button.addEventListener('click', function () {
            var jadwalId = button.getAttribute('data-jadwal-id');
            var jadwalLabel = button.getAttribute('data-jadwal-label');

            if (form) {
                form.action = '{{ url('jadwals') }}/' + jadwalId;
            }

            if (label) {
                label.textContent = jadwalLabel || '-';
            }

            if (alasan) {
                alasan.value = '';
            }
        });
    });

    if (modal && form) {
        modal.addEventListener('hidden.bs.modal', function () {
            form.action = '';
            if (alasan) {
                alasan.value = '';
            }
        });
    }
})();
</script>
@endpush
