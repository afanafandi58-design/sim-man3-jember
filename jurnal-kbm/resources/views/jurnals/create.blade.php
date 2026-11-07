@extends('adminlte::page')

@section('title', 'Tambah Jurnal')

@section('content_header')
<h1>Tambah Jurnal KBM</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header bg-primary">
        <h3 class="card-title">Form Jurnal KBM</h3>
    </div>

    <form action="{{ route('jurnals.store') }}" method="POST" enctype="multipart/form-data">

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

            <div class="form-group mb-3">
                <label>Jadwal</label>

                <select id="jadwal_id"
        name="jadwal_id"
        class="form-control"
        required>

    <option value="">-- Pilih Jadwal Mengajar --</option>

    @foreach($jadwals as $jadwal)

        <option value="{{ $jadwal->id }}">
            {{ $jadwal->kelas }}
            |
            {{ $jadwal->mapel }}
            |
            {{ $jadwal->guru->nama }}
            |
            {{ $jadwal->hari }}
            |
            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}
            -
            {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
        </option>

    @endforeach

</select>
<div class="alert alert-info mt-3" id="infoJadwal" style="display:none;">

    <h5><i class="fas fa-info-circle"></i> Informasi Jadwal</h5>

    <table class="table table-borderless mb-0">

        <tr>
            <th width="150">Guru</th>
            <td id="guru"></td>
        </tr>

        <tr>
            <th>Kelas</th>
            <td id="kelas"></td>
        </tr>

        <tr>
            <th>Mata Pelajaran</th>
            <td id="mapel"></td>
        </tr>

        <tr>
            <th>Hari</th>
            <td id="hari"></td>
        </tr>

        <tr>
            <th>Jam</th>
            <td id="jam"></td>
        </tr>

    </table>

</div>
            </div>

            <div class="form-group mb-3">
                <label>Tanggal</label>

                <input
                    type="date"
                    name="tanggal"
                    class="form-control"
                    value="{{ date('Y-m-d') }}"
                    required>

            </div>

            <div class="form-group mb-3">
                <label>Jam Ke</label>

                <input
                    type="number"
                    name="jam_ke"
                    class="form-control"
                    required>

            </div>

            <div class="form-group mb-3">
                <label>Materi</label>

                <textarea
                    name="materi"
                    rows="4"
                    class="form-control"
                    required></textarea>

            </div>

            <div class="row">

                <div class="col-md-3">
                    <label>Hadir</label>
                    <input type="number" name="hadir" class="form-control" value="0">
                </div>

                <div class="col-md-3">
                    <label>Izin</label>
                    <input type="number" name="izin" class="form-control" value="0">
                </div>

                <div class="col-md-3">
                    <label>Sakit</label>
                    <input type="number" name="sakit" class="form-control" value="0">
                </div>

                <div class="col-md-3">
                    <label>Alpha</label>
                    <input type="number" name="alpha" class="form-control" value="0">
                </div>

            </div>

            <div class="form-group mt-3">
                <label>Catatan</label>

                <textarea
                    name="catatan"
                    rows="3"
                    class="form-control"></textarea>

            </div>

            @include('guru.jurnal.partials.lampiran-foto-form', ['jurnal' => null])

        </div>

        <div class="card-footer">
        
            <button class="btn btn-success">
                <i class="fas fa-save"></i>
                Simpan
            </button>

            <a href="{{ route('jurnals.index') }}" class="btn btn-secondary">
                Kembali
            </a>
            </div>

</form>

</div>

@stop
@section('js')

<script>

$(document).ready(function () {

    $('#jadwal_id').on('change', function () {

        let id = $(this).val();

        if (id == '') {
            $('#infoJadwal').hide();
            return;
        }

        $.get('/jadwal/' + id, function (data) {

            $('#guru').text(data.guru.nama);
            $('#kelas').text(data.kelas);
            $('#mapel').text(data.mapel);
            $('#hari').text(data.hari);
            $('#jam').text(data.jam_mulai + ' - ' + data.jam_selesai);

            $('#infoJadwal').show();

        });

    });

});

</script>

@stop