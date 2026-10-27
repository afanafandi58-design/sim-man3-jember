<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Siswa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #333; padding: 5px; }
        th { background: #eee; }
        h2, h4 { margin: 0 0 8px; }
        .summary { margin-top: 12px; }
    </style>
</head>
<body>
    <h2>Rekap Absensi Siswa</h2>
    <h4>SIM MAN 3 Jember</h4>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Guru</th>
                <th>Mapel</th>
                <th>Kelas</th>
                <th>Rombel</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($row->jurnal)->tanggal }}</td>
                    <td>{{ $row->jadwal->guru->nama ?? '-' }}</td>
                    <td>{{ $row->jadwal->mapel ?? '-' }}</td>
                    <td>{{ $row->siswa->kelas ?? '-' }}</td>
                    <td>{{ $row->siswa->rombel ?? '-' }}</td>
                    <td>{{ $row->siswa->nis ?? '-' }}</td>
                    <td>{{ $row->siswa->nama ?? '-' }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <strong>Ringkasan:</strong>
        Total {{ $summary['total'] }} |
        Hadir {{ $summary['hadir'] }} |
        Sakit {{ $summary['sakit'] }} |
        Izin {{ $summary['izin'] }} |
        Alpha {{ $summary['alpha'] }}
    </div>
</body>
</html>
