@extends('adminlte::page')

@section('title', $kegiatan->nama)

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div>
            <h1 class="mb-1">{{ $kegiatan->nama }}</h1>
            <p class="text-muted mb-0">{{ $kegiatan->jenisLabel() }} · {{ $kegiatan->tanggalLabel() }}{{ $kegiatan->waktu ? ' · '.$kegiatan->waktu : '' }}</p>
        </div>
        @can('role-absen-admin')
            <a href="{{ route('absen-digital.kegiatan.edit', $kegiatan) }}" class="btn btn-default">Ubah</a>
        @endcan
    </div>
@stop

@section('content')
    @include('absen-digital.partials.alerts')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Kode QR</h3></div>
                <div class="card-body text-center">
                    <div id="qrcode" class="d-inline-block" data-text="{{ $kegiatan->scanUrl() }}"></div>
                    <p class="mt-3 mb-1"><strong>Kode:</strong> <span class="h4">{{ $kegiatan->kode }}</span></p>
                    <p class="text-muted small mb-0">Tampilkan QR ini saat acara. Peserta juga bisa mengetik kode di menu Scan Barcode.</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <p class="mb-1"><strong>Tempat:</strong> {{ $kegiatan->tempat ?: '—' }}</p>
                    <p class="mb-0"><strong>Keterangan:</strong> {{ $kegiatan->keterangan ?: '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Daftar hadir ({{ $kegiatan->kehadirans->count() }})</h3></div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Cara</th>
                                <th>Waktu</th>
                                @can('role-absen-admin')
                                    <th>Lokasi</th>
                                    <th>Bukti</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kegiatan->kehadirans as $hadir)
                                <tr>
                                    <td>
                                        {{ $hadir->nama }}
                                        @if ($hadir->nik)
                                            <div class="text-muted small">NIK {{ $hadir->nik }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $hadir->metodeLabel() }}</td>
                                    <td>{{ $hadir->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                                    @can('role-absen-admin')
                                        <td>
                                            @if ($hadir->koordinatLabel())
                                                <a href="{{ $hadir->mapsUrl() }}" target="_blank" rel="noopener">{{ $hadir->koordinatLabel() }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            @if ($hadir->foto_path)
                                                <a href="{{ route('absen-digital.berkas', ['jenis' => 'foto', 'id' => $hadir->id]) }}" target="_blank" rel="noopener">Foto</a>
                                            @endif
                                            @if ($hadir->ttd_path)
                                                <a href="{{ route('absen-digital.berkas', ['jenis' => 'ttd', 'id' => $hadir->id]) }}" target="_blank" rel="noopener">TTD</a>
                                            @endif
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr><td colspan="{{ auth()->user()->role === 'admin' ? 5 : 3 }}" class="text-center text-muted">Belum ada yang hadir.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @can('role-absen-admin')
                <div class="card card-success">
                    <div class="card-header"><h3 class="card-title">Foto dokumentasi</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('absen-digital.kegiatan.foto.store', $kegiatan) }}" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-5">
                                    <label for="foto">Unggah foto</label>
                                    <input id="foto" type="file" name="foto" accept="image/*" capture="environment" class="form-control-file" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label for="keterangan-foto">Keterangan</label>
                                    <input id="keterangan-foto" type="text" name="keterangan" class="form-control" maxlength="150">
                                </div>
                                <div class="form-group col-md-2">
                                    <button class="btn btn-success btn-block" type="submit">Unggah</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            @forelse ($kegiatan->fotos as $foto)
                                <div class="col-6 col-md-4 mb-3">
                                    <a href="{{ route('absen-digital.berkas', ['jenis' => 'dokumentasi', 'id' => $foto->id]) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('absen-digital.berkas', ['jenis' => 'dokumentasi', 'id' => $foto->id]) }}" alt="{{ $foto->keterangan ?: 'Foto dokumentasi' }}" class="img-fluid img-thumbnail">
                                    </a>
                                    <div class="small mt-1">{{ $foto->keterangan ?: 'Tanpa keterangan' }}</div>
                                    <form method="POST" action="{{ route('absen-digital.kegiatan.foto.destroy', [$kegiatan, $foto]) }}" onsubmit="return confirm('Hapus foto ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            @empty
                                <div class="col-12 text-muted">Belum ada foto dokumentasi.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-success">
                    <div class="card-header"><h3 class="card-title">Hasil rapat</h3></div>
                    <form method="POST" action="{{ route('absen-digital.kegiatan.hasil.store', $kegiatan) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted">Isi link Google Drive, unggah berkas ke hosting aplikasi, atau keduanya.</p>
                            <div class="form-group">
                                <label for="hasil_drive_url">Link Google Drive</label>
                                <input id="hasil_drive_url" type="url" name="hasil_drive_url" class="form-control" placeholder="https://drive.google.com/..." value="{{ old('hasil_drive_url', $kegiatan->hasil_drive_url) }}">
                                @if ($kegiatan->hasil_drive_url)
                                    <a href="{{ $kegiatan->hasil_drive_url }}" target="_blank" rel="noopener">Buka tautan yang tersimpan</a>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="hasil_file">Berkas di hosting</label>
                                <input id="hasil_file" type="file" name="hasil_file" class="form-control-file">
                                @if ($kegiatan->hasil_file_path)
                                    <div class="mt-2">
                                        Tersimpan:
                                        <a href="{{ route('absen-digital.berkas', ['jenis' => 'hasil', 'id' => $kegiatan->id]) }}">{{ $kegiatan->hasil_file_nama ?: 'Unduh berkas' }}</a>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group mb-0">
                                <label for="hasil_catatan">Catatan</label>
                                <textarea id="hasil_catatan" name="hasil_catatan" class="form-control" rows="3" maxlength="2000">{{ old('hasil_catatan', $kegiatan->hasil_catatan) }}</textarea>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between flex-wrap">
                            <button class="btn btn-success mb-2" type="submit">Simpan hasil rapat</button>
                        </div>
                    </form>
                    @if ($kegiatan->hasil_file_path)
                        <div class="card-footer">
                            <form method="POST" action="{{ route('absen-digital.kegiatan.hasil.file.destroy', $kegiatan) }}" onsubmit="return confirm('Hapus berkas hasil rapat dari hosting?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" type="submit">Hapus berkas hosting</button>
                            </form>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('absen-digital.kegiatan.destroy', $kegiatan) }}" onsubmit="return confirm('Hapus kegiatan ini beserta daftar hadirnya?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Hapus kegiatan</button>
                </form>
            @endcan
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/absen-digital.css') }}">
@stop

@section('js')
    <script src="{{ asset('vendor/absen-digital/qrcode.min.js') }}"></script>
    <script src="{{ asset('js/absen-digital.js') }}"></script>
@stop
