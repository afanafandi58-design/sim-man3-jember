<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 18px 24px; }
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: normal;
            src: url('{{ \App\Support\ArabicPdfText::amiriFontPath() }}') format('truetype');
        }
        body { font-family: DejaVu Sans, Amiri, sans-serif; font-size: 10px; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .header-table { margin-bottom: 4px; }
        .logo { width: 78px; }
        .logo img { width: 62px; }
        .identitas { text-align: center; line-height: 1.35; }
        .kemenag { font-size: 12px; font-weight: bold; }
        .kantor { font-size: 11px; font-weight: bold; }
        .madrasah { font-size: 15px; font-weight: bold; color: #006400; }
        .alamat { font-size: 8px; margin-top: 1px; }
        .garis1 { border: 0; border-top: 2px solid #000; margin: 4px 0 1px; }
        .garis2 { border: 0; border-top: 1px solid #000; margin: 0 0 8px; }
        h3.judul { text-align: center; margin: 4px 0 2px; font-size: 13px; }
        p.subtitle { text-align: center; margin: 0 0 8px; color: #333; font-size: 10px; }
        .meta { margin-bottom: 8px; font-size: 10px; }
        .data th, .data td { border: 1px solid #333; padding: 4px 5px; text-align: left; vertical-align: top; }
        .data th { background: #eee; font-size: 9px; }
        td.materi-arab {
            font-family: Amiri, DejaVu Sans, sans-serif;
            direction: ltr;
            text-align: right;
            unicode-bidi: normal;
        }
        .footer-info { margin-top: 10px; font-size: 9px; text-align: right; }
        .signature-block { margin-top: 28px; }
        .signature-block td { width: 50%; text-align: center; font-size: 10px; line-height: 1.4; }
        .ttd { margin-top: 48px; font-weight: bold; text-decoration: underline; }
        .nip { font-size: 9px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo">
                <img src="{{ public_path('images/LOGO_KEMENAG-removebg-preview.png') }}">
            </td>
            <td class="identitas">
                <div class="kemenag">KEMENTERIAN AGAMA REPUBLIK INDONESIA</div>
                <div class="kantor">KANTOR KEMENTERIAN AGAMA KABUPATEN JEMBER</div>
                <div class="madrasah">MADRASAH ALIYAH NEGERI 3 JEMBER</div>
                <div class="alamat">Jl. Jend. A. Yani no 40, kec. Jombang, Kab. jember</div>
            </td>
        </tr>
    </table>
    <hr class="garis1">
    <hr class="garis2">

    <h3 class="judul">REKAP JURNAL KEGIATAN BELAJAR MENGAJAR</h3>
    <p class="subtitle">{{ $title }}</p>

    <div class="meta">
        <strong>Guru Pengampu:</strong> {{ $guru->nama ?? '-' }}
        &nbsp;|&nbsp; <strong>NIP:</strong> {{ $guru->nip ?? '-' }}
        &nbsp;|&nbsp; <strong>Periode:</strong> {{ ucfirst($periode) }}
        &nbsp;|&nbsp; <strong>Rentang:</strong> {{ $startDate->format('d/m/Y') }} – {{ $endDate->format('d/m/Y') }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
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
                    $materiPdf = \App\Support\ArabicPdfText::shape(\Illuminate\Support\Str::limit($materi, 90, ''));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                    <td>{{ $jurnal->guru_pengganti ?: '-' }}</td>
                    <td>{{ $jurnal->jadwal->kelas }}</td>
                    <td>{{ $jurnal->jadwal->mapel }}</td>
                    <td @class(['materi-arab' => $hasArab])>{{ $materiPdf }}</td>
                    <td>{{ $jurnal->hadir }}</td>
                    <td>{{ $jurnal->status_kbm }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Tidak ada data jurnal pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-info">
        Dicetak: {{ now()->format('d/m/Y H:i') }} | Total: {{ $jurnals->count() }} jurnal
    </div>

    <table class="signature-block">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah
                <div class="ttd">Imam Syahroni, S.Pd., M.Pd.</div>
                <div class="nip">NIP. ....................................</div>
            </td>
            <td>
                Jember, {{ now()->translatedFormat('d F Y') }}<br>
                Guru Mata Pelajaran
                <div class="ttd">{{ $guru->nama ?? '-' }}</div>
                <div class="nip">NIP. {{ $guru->nip ?? '-' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
