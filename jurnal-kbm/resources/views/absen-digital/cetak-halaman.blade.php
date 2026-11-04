<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir — MAN 3 Jember</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; color: #111; margin: 20px; }
        h1, h2, p { margin: 0; }
        .center { text-align: center; }
        .muted { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
        th, td { border: 1px solid #222; padding: 4px 6px; vertical-align: middle; }
        th { background: #f2f2f2; }
        .ttd img { height: 48px; max-width: 120px; }
        .grup { page-break-inside: avoid; margin-top: 22px; }
        .no-print { margin-bottom: 16px; }
        button { font: inherit; padding: 8px 14px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()">Cetak</button>
    </div>
    <div class="center">
        <h1>Madrasah Aliyah Negeri 3 Jember</h1>
        <h2>Daftar Hadir</h2>
        <p class="muted">
            @if ($filters['tanggal_dari'] || $filters['tanggal_sampai'])
                Periode {{ $filters['tanggal_dari'] ?: '...' }} s.d. {{ $filters['tanggal_sampai'] ?: '...' }}
            @else
                Semua tanggal pada filter
            @endif
        </p>
    </div>

    @forelse ($groups as $kegiatanId => $rows)
        @php $kegiatan = $rows->first()->kegiatan; @endphp
        <section class="grup">
            <p><strong>{{ $kegiatan?->nama }}</strong></p>
            <p>{{ $kegiatan?->jenisLabel() }} · {{ $kegiatan?->tanggalLabel() }}{{ $kegiatan?->waktu ? ' · '.$kegiatan->waktu : '' }}{{ $kegiatan?->tempat ? ' · '.$kegiatan->tempat : '' }}</p>
            <p>Jumlah hadir: {{ $rows->count() }}</p>
            <table>
                <thead>
                    <tr>
                        <th style="width:36px">No</th>
                        <th>Nama</th>
                        <th>NIK / akun</th>
                        <th>Waktu</th>
                        <th>Cara</th>
                        <th>Lokasi</th>
                        <th>Tanda tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $index => $hadir)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $hadir->nama }}</td>
                            <td>{{ $hadir->nik ?: ($hadir->user_id ? 'Akun #'.$hadir->user_id : '—') }}</td>
                            <td>{{ $hadir->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $hadir->metodeLabel() }}</td>
                            <td>{{ $hadir->koordinatLabel() ?: '—' }}</td>
                            <td class="ttd">
                                @if ($hadir->ttd_path)
                                    <img src="{{ route('absen-digital.berkas', ['jenis' => 'ttd', 'id' => $hadir->id]) }}" alt="Tanda tangan {{ $hadir->nama }}">
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <p class="center" style="margin-top:24px">Tidak ada data hadir untuk filter ini.</p>
    @endforelse
</body>
</html>
