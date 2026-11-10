@extends('adminlte::page')

@section('title', 'Absen Saya')

@php
    $peran = ['admin' => 'Admin', 'guru' => 'Guru', 'staff' => 'Staff', 'kepala' => 'Kepala'];
    $mode = old('mode_peserta', 'user');
@endphp

@section('content_header')
    <h1>Absen Saya</h1>
@stop

@section('content')
    @include('absen-digital.partials.alerts')

    @if ($kegiatans->isEmpty())
        <div class="alert alert-warning">Belum ada kegiatan. Admin perlu membuat kegiatan terlebih dahulu.</div>
    @endif

    <div class="card card-success">
        <div class="card-header"><h3 class="card-title">Catat kehadiran</h3></div>
        <form method="POST" action="{{ route('absen-digital.form.store') }}" enctype="multipart/form-data" id="form-absen">
            @csrf
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="jenis-filter">Jenis kegiatan</label>
                        <select id="jenis-filter" class="form-control">
                            <option value="">Semua jenis</option>
                            @foreach (\App\Models\AbsenKegiatan::JENIS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="kegiatan-id">Kegiatan</label>
                        <select id="kegiatan-id" name="kegiatan_id" class="form-control" required>
                            <option value="">Pilih kegiatan</option>
                            @foreach ($kegiatans as $kegiatan)
                                <option value="{{ $kegiatan->id }}" data-jenis="{{ $kegiatan->jenis }}" @selected((string) old('kegiatan_id') === (string) $kegiatan->id)>
                                    {{ $kegiatan->tanggalLabel() }} — {{ $kegiatan->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Peserta</label>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" name="mode_peserta" id="mode-user" value="user" @checked($mode === 'user')>
                        <label class="custom-control-label" for="mode-user">Pilih pengguna</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" name="mode_peserta" id="mode-nik" value="nik" @checked($mode === 'nik')>
                        <label class="custom-control-label" for="mode-nik">Isi NIK</label>
                    </div>
                </div>

                <div class="form-group" id="box-user">
                    <label for="user-id">Pengguna</label>
                    <select id="user-id" name="user_id" class="form-control">
                        @foreach ($peserta as $orang)
                            <option value="{{ $orang->id }}" @selected((string) old('user_id', auth()->id()) === (string) $orang->id)>
                                {{ $orang->name }} ({{ $peran[$orang->role] ?? $orang->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row" id="box-nik">
                    <div class="form-group col-md-5">
                        <label for="nik">NIK</label>
                        <input id="nik" name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" class="form-control" value="{{ old('nik') }}" placeholder="16 digit">
                    </div>
                    <div class="form-group col-md-7">
                        <label for="nama">Nama</label>
                        <input id="nama" name="nama" class="form-control" maxlength="150" value="{{ old('nama') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto">Foto kehadiran</label>
                    <input id="foto" type="file" name="foto" accept="image/*" capture="environment" class="form-control-file" required>
                    <small class="form-text text-muted">Ambil dari kamera atau pilih berkas gambar. Maksimal 4 MB.</small>
                </div>

                <div class="form-group">
                    <label>Titik lokasi</label>
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-success btn-lg" id="btn-lokasi">Ambil lokasi GPS</button>
                    </div>
                    <p id="lokasi-status" class="mb-2">Jika GPS tidak tersedia atau ditolak, isi lintang dan bujur di bawah. Nilai yang sama yang akan disimpan.</p>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="latitude">Lintang</label>
                            <input id="latitude" name="latitude" type="number" step="any" inputmode="decimal" class="form-control" required value="{{ old('latitude') }}" placeholder="-8.170000">
                        </div>
                        <div class="form-group col-6">
                            <label for="longitude">Bujur</label>
                            <input id="longitude" name="longitude" type="number" step="any" inputmode="decimal" class="form-control" required value="{{ old('longitude') }}" placeholder="113.700000">
                        </div>
                    </div>
                    <p id="lokasi-tampil" class="mb-0 font-weight-bold"></p>
                </div>

                <div class="form-group mb-0">
                    <label>Tanda tangan</label>
                    <p class="text-muted mb-2">Gambar dengan jari di layar Android, atau dengan mouse. Kotak ini tidak ikut tergeser saat ditandatangani.</p>
                    <canvas id="ttd-canvas" aria-label="Kanvas tanda tangan"></canvas>
                    <input type="hidden" name="ttd" id="ttd-input">
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="ttd-clear">Hapus tanda tangan</button>
                    </div>
                    <div id="ttd-error" class="text-danger mt-2"></div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success btn-lg btn-block">Simpan absen</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Absen yang baru tercatat</h3></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $hadir)
                        <tr>
                            <td>{{ $hadir->kegiatan?->nama }}</td>
                            <td>{{ $hadir->nama }}</td>
                            <td>
                                @if ($hadir->koordinatLabel())
                                    <a href="{{ $hadir->mapsUrl() }}" target="_blank" rel="noopener">{{ $hadir->koordinatLabel() }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $hadir->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada absen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/absen-digital.css') }}">
@stop

@section('js')
    <script src="{{ asset('js/absen-digital.js') }}"></script>
@stop
