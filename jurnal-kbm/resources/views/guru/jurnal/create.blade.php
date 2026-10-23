@extends('adminlte::page')

@section('title', 'Isi Jurnal KBM')

@section('content_header')
    <h1>Isi Jurnal KBM</h1>
@stop

@section('content')

<div class="card card-success">

    <div class="card-header">
        <h3 class="card-title">
            Form Jurnal KBM
        </h3>
    </div>

    <form action="{{ route('guru.jurnal.store') }}" method="POST" id="form-jurnal" enctype="multipart/form-data">

        @csrf

        <input type="hidden"
               name="jadwal_id"
               value="{{ $jadwal->id }}">

        <div class="card-body">
        <div class="alert alert-info">

<h5>
    <i class="fas fa-chalkboard-teacher"></i>
    Informasi Mengajar Hari Ini
</h5>

<hr>

<div class="row">

    <div class="col-md-6">

        <table class="table table-sm table-borderless">

            <tr>
                <th width="160">Guru</th>
                <td>: {{ $jadwal->guru->nama }}</td>
            </tr>

            <tr>
                <th>Mata Pelajaran</th>
                <td>: {{ $jadwal->mapel }}</td>
            </tr>

            <tr>
                <th>Kelas</th>
                <td>: {{ $jadwal->kelas }}</td>
            </tr>

            <tr>
                <th>Jam Ke</th>
                <td>: {{ $jadwal->jam_ke ?? 1 }}</td>
            </tr>

        </table>

    </div>

    <div class="col-md-6">

        <table class="table table-sm table-borderless">

            <tr>
                <th width="160">Tanggal</th>
                <td>: {{ $operationalDate['tanggalSistem'] ?? '-' }}</td>
            </tr>

            <tr>
                <th>Hari</th>
                <td>: {{ $operationalDate['hariOperasional'] ?? '-' }}</td>
            </tr>

            <tr>
                <th>Jam Mengajar</th>
                <td>
                    :
                    {{ substr($jadwal->jam_mulai,0,5) }}
                    -
                    {{ substr($jadwal->jam_selesai,0,5) }}
                </td>
            </tr>

        </table>

    </div>

</div>

</div>
            @if ($errors->any())
                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>
            @endif

            

            </div>

            <hr>

            <div class="form-group">

    @include('guru.jurnal.partials.jp-smart-selection', [
        'jpOptions' => $jpOptions ?? [],
        'selectedJp' => $selectedJp ?? [],
    ])

</div>

            @include('guru.jurnal.partials.arabic-language-support')

            <div class="form-group">

                <label>Materi Pembelajaran</label>

                <textarea
                    name="materi"
                    rows="4"
                    class="form-control jurnal-i18n-field jurnal-lang-id"
                    dir="ltr"
                    lang="id"
                    required>{{ old('materi') }}</textarea>

            </div>

            <div class="mb-3">
                <button type="button" class="btn btn-info" id="btn-absensi-siswa">
                    <i class="fas fa-user-check"></i> ABSENSI SISWA
                </button>
            </div>

            @php
                $absensiMap = collect();
                $keteranganMap = collect();
            @endphp
            @include('guru.jurnal.partials.absensi-form', ['jadwal' => $jadwal])

            <div class="row">

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Hadir</label>

                        <input
                            type="number"
                            name="hadir"
                            value="{{ old('hadir', $siswas->count()) }}"
                            class="form-control"
                            required>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Izin</label>

                        <input
                            type="number"
                            name="izin"
                            value="{{ old('izin',0) }}"
                            class="form-control"
                            required>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Sakit</label>

                        <input
                            type="number"
                            name="sakit"
                            value="{{ old('sakit',0) }}"
                            class="form-control"
                            required>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="form-group">
                        <label>Alpha</label>

                        <input
                            type="number"
                            name="alpha"
                            value="{{ old('alpha',0) }}"
                            class="form-control"
                            required>
                    </div>

                </div>

            </div>

            <div class="form-group">

                <label>Status KBM</label>

                <select
                    name="status_kbm"
                    class="form-control">

                    <option value="Selesai">Selesai</option>
                    <option value="Belum Selesai">Belum Selesai</option>
                    <option value="Guru Pengganti">Guru Pengganti</option>
                    <option value="Libur">Libur</option>

                </select>

            </div>

            <div class="form-group">

                <label>Catatan Guru</label>

                <textarea
                    name="catatan"
                    rows="3"
                    class="form-control jurnal-i18n-field jurnal-lang-id"
                    dir="ltr"
                    lang="id">{{ old('catatan') }}</textarea>

            </div>

            @include('guru.jurnal.partials.lampiran-foto-form')

        </div>

        <div class="card-footer">

            <button
                type="submit"
                class="btn btn-success">

                <i class="fas fa-save"></i>
                Simpan Jurnal

            </button>

            <a href="{{ route('guru.dashboard') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left"></i>
                Kembali

            </a>

        </div>

    </form>

</div>

@stop