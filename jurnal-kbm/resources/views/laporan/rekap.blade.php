<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: normal;
            src: url('{{ \App\Support\ArabicPdfText::amiriFontPath() }}') format('truetype');
        }
        body { font-family: DejaVu Sans, Amiri, sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        p.subtitle { text-align: center; margin-top: 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #eee; }
        /* utf8Glyphs already emits visual-order presentation forms — keep LTR stream */
        td.materi-arab {
            font-family: Amiri, DejaVu Sans, sans-serif;
            direction: ltr;
            text-align: right;
            unicode-bidi: normal;
        }
        .footer { margin-top: 20px; font-size: 10px; text-align: right; }
    </style>
</head>
<body>
    <h2>SIM MAN 3 JEMBER</h2>
    <p class="subtitle">{{ $title }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Guru</th>
                <th>Pengganti</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Materi</th>
                <th>Hadir</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jurnals as $index => $jurnal)
                @php
                    $materi = $jurnal->materi ?? '';
                    $hasArab = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $materi);
                    $materiPdf = \App\Support\ArabicPdfText::shape(\Illuminate\Support\Str::limit($materi, 80, ''));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $jurnal->jadwal->guru->nama }}</td>
                    <td>{{ $jurnal->guru_pengganti ?: '-' }}</td>
                    <td>{{ $jurnal->jadwal->kelas }}</td>
                    <td>{{ $jurnal->jadwal->mapel }}</td>
                    <td @class(['materi-arab' => $hasArab])>{{ $materiPdf }}</td>
                    <td>{{ $jurnal->hadir }}</td>
                    <td>{{ $jurnal->status_kbm }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">Tidak ada data jurnal pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak: {{ now()->format('d/m/Y H:i') }} | Total: {{ $jurnals->count() }} jurnal
    </div>

    @php
        $kamadNama = config('madrasah.kepala.nama', 'Imam Syahroni, S.Pd., M.Pd.');
        $kamadNip = config('madrasah.kepala.nip', '198309202009011007');
        $kamadJabatan = config('madrasah.kepala.jabatan', 'Kepala Madrasah');
    @endphp
    <table style="width:100%; margin-top:28px; border-collapse:collapse;">
        <tr>
            <td style="width:50%; text-align:center; font-size:10px; line-height:1.4;">
                Mengetahui,<br>
                {{ $kamadJabatan }}
                <div style="margin-top:48px; font-weight:bold; text-decoration:underline;">{{ $kamadNama }}</div>
                <div style="font-size:9px;">NIP. {{ $kamadNip }}</div>
            </td>
            <td style="width:50%; text-align:center; font-size:10px; line-height:1.4;">
                Jember, {{ now()->translatedFormat('d F Y') }}<br>
                Petugas Laporan
                <div style="margin-top:48px; font-weight:bold; text-decoration:underline;">................................</div>
                <div style="font-size:9px;">NIP. ....................................</div>
            </td>
        </tr>
    </table>
</body>
</html>

