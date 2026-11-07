@extends('adminlte::page')

@section('title', $detail['title'])

@section('content_header')
    <h1>{{ $detail['title'] }}</h1>
    <small class="text-muted">{{ $detail['hari'] }}, {{ $detail['tanggal']->format('d F Y') }}</small>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Data</h3>
        <div class="card-tools">
            <a href="{{ route('kepala.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        @if ($detail['type'] === 'guru-all' || $detail['type'] === 'guru-mengajar')
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama Guru</th>
                        <th>Mapel</th>
                        @if ($detail['type'] === 'guru-mengajar')
                            <th>Jadwal Hari Ini</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail['items'] as $index => $guru)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $guru->nip }}</td>
                            <td>{{ $guru->nama }}</td>
                            <td>{{ $guru->mapel }}</td>
                            @if ($detail['type'] === 'guru-mengajar')
                                <td>
                                    @foreach ($guru->jadwals as $jadwal)
                                        <span class="badge badge-primary mr-1">
                                            {{ $jadwal->kelas }} — {{ $jadwal->mapel }}
                                            ({{ substr($jadwal->jam_mulai, 0, 5) }}-{{ substr($jadwal->jam_selesai, 0, 5) }})
                                        </span>
                                    @endforeach
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @elseif ($detail['type'] === 'sudah-mengisi')
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Materi</th>
                        <th>Dokumentasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail['items'] as $index => $jurnal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jurnal->jadwal->guru->nama }}</td>
                            <td>{{ $jurnal->jadwal->kelas }}</td>
                            <td>{{ $jurnal->jadwal->mapel }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($jurnal->materi, 50) }}</td>
                            <td>@include('guru.jurnal.partials.lampiran-foto-kepala', ['jurnal' => $jurnal])</td>
                            <td><span class="badge badge-success">{{ $jurnal->status_kbm }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Belum ada guru yang mengisi jurnal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Jam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail['items'] as $index => $jadwal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $jadwal->guru->nama }}</td>
                            <td>{{ $jadwal->kelas }}</td>
                            <td>{{ $jadwal->mapel }}</td>
                            <td>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Semua guru sudah mengisi jurnal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
@stop
