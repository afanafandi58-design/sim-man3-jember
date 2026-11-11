@extends('adminlte::page')

@section('title', 'Edit Jadwal')

@section('content_header')
<h1>Edit Jadwal</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">
        <h3 class="card-title">Form Edit Jadwal</h3>
    </div>

    <form action="{{ route('jadwals.update', $jadwal->id) }}" method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="form-group">
                <label>Guru</label>

                <select name="guru_id" class="form-control">

                    @foreach($gurus as $guru)

                        <option value="{{ $guru->id }}"
                            {{ $jadwal->guru_id == $guru->id ? 'selected' : '' }}>

                            {{ $guru->nama }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="form-group">
                <label>Kelas</label>
                <input type="text"
                       name="kelas"
                       class="form-control"
                       value="{{ $jadwal->kelas }}">
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="is_moving_class" value="0">
                    <input type="checkbox"
                           class="custom-control-input"
                           id="is_moving_class"
                           name="is_moving_class"
                           value="1"
                           @checked(old('is_moving_class', $jadwal->is_moving_class ?? false))>
                    <label class="custom-control-label" for="is_moving_class">
                        Tandai sebagai <strong>moving class</strong>
                        (gabungan siswa dari rombel existing; tidak mengganti data siswa)
                    </label>
                </div>
                @if(!empty($jadwal->is_moving_class) && $jadwal->moving_kelas_nama)
                    <small class="text-muted d-block mt-1">
                        Nama moving saat ini: {{ $jadwal->moving_kelas_nama }}.
                        Guru pemilik mengatur daftar siswa lewat portal guru.
                    </small>
                @endif
            </div>

            <div class="form-group">
                <label>Mapel</label>
                <input type="text"
                       name="mapel"
                       class="form-control"
                       value="{{ $jadwal->mapel }}">
            </div>

            <div class="form-group">
                <label>Hari</label>

                <select name="hari" id="hari-select" class="form-control">

                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)

                        <option value="{{ $hari }}"
                            {{ old('hari', $jadwal->hari) == $hari ? 'selected' : '' }}>

                            {{ $hari }}

                        </option>

                    @endforeach

                </select>

            </div>

            @include('partials.jadwal-jam-range-fields', [
                'selectedJamKe' => old('jam_ke', $selectedJamKe ?? $jadwal->jam_ke),
                'selectedJamKeAkhir' => old('jam_ke_akhir', $selectedJamKeAkhir ?? $jadwal->jam_ke),
                'selectedJamMulai' => $selectedJamMulai ?? substr((string) $jadwal->jam_mulai, 0, 5),
                'selectedJamSelesai' => $selectedJamSelesai ?? substr((string) $jadwal->jam_selesai, 0, 5),
                'polaHariMap' => $polaHariMap ?? [],
                'jamMapByPola' => $jamMapByPola ?? [],
                'hariSelectId' => 'hari-select',
                'showPolaDisplay' => true,
            ])

            <div class="form-group">
                <label>Tahun Pelajaran</label>
                <input type="text"
                       name="tahun_pelajaran"
                       class="form-control"
                       value="{{ $jadwal->tahun_pelajaran }}">
            </div>

            <div class="form-group">
                <label>Semester</label>

                <select name="semester" class="form-control">

                    <option value="Ganjil"
                        {{ $jadwal->semester == 'Ganjil' ? 'selected' : '' }}>
                        Ganjil
                    </option>

                    <option value="Genap"
                        {{ $jadwal->semester == 'Genap' ? 'selected' : '' }}>
                        Genap
                    </option>

                </select>

            </div>

        </div>

        <div class="card-footer">

            <button class="btn btn-primary">
                Update
            </button>

            <a href="{{ route('jadwals.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </div>

    </form>

</div>

@stop