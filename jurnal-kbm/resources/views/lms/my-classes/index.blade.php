@extends('lms.page')

@section('title', 'Kelas & Jadwal Saya')

@section('content_header')<h1>Kelas & Jadwal Saya</h1>@stop

@section('content')
    <div class="card">
        <table class="table table-striped mb-0">
            <thead><tr><th>Kelas</th><th>Mapel</th><th>Hari</th><th>Jam</th><th>TP</th></tr></thead>
            <tbody>
                @forelse ($jadwals as $j)
                    <tr>
                        <td>{{ e($j->kelas) }}</td>
                        <td>{{ e($j->mapel) }}</td>
                        <td>{{ e($j->hari) }}</td>
                        <td>{{ $j->jam_mulai }} – {{ $j->jam_selesai }}</td>
                        <td>{{ e($j->tahun_pelajaran) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted text-center">Tidak ada jadwal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@stop
