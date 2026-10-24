<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jurnal KBM</title>

    <style>

@page{
    margin-top:18px;
    margin-bottom:18px;
    margin-left:32px;
    margin-right:32px;
}

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:10px;
    color:#000;
    line-height:1.28;
}

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        td{
            vertical-align:top;
        }

        .header-table{
            width:100%;
            margin-bottom:5px;
        }

        .logo{
            width:95px;
        }

        .logo img{
            width:74px;
        }

        .identitas{
            text-align:center;
            line-height:1.4;
        }

        .kemenag{
            font-size:14px;
            font-weight:bold;
        }

        .kantor{
            font-size:12px;
            font-weight:bold;
        }

        .madrasah{
            font-size:19px;
            font-weight:bold;
            color:#006400;
        }

        .alamat{
            font-size:9px;
            margin-top:2px;
        }

        .garis1{
            border:0;
            border-top:2px solid #000;
            margin-top:8px;
        }

        .garis2{
            border:0;
            border-top:1px solid #000;
            margin-top:2px;
            margin-bottom:10px;
        }

        .judul{
            text-align:center;
            font-size:15px;
            font-weight:bold;
            margin-top:10px;
            margin-bottom:10px;
            letter-spacing:0.5px;
        }

        .identitas-jurnal td{
            padding:3px 5px;
            font-size:10px;
        }

        .label{
            width:165px;
            font-weight:bold;
        }

        .titik{
            width:18px;
            text-align:center;
        }

        .isi{
            padding-left:5px;
        }

        .section-title{
            font-size:10.5px;
            font-weight:bold;
            background:#ECECEC;
            border:1px solid #000;
            padding:4px 8px;
            margin-top:8px;
            margin-bottom:0;
            letter-spacing:0.4px;
            page-break-after:avoid;
        }

        .section-group{
            page-break-inside:avoid;
            margin-bottom:6px;
        }

        .section-group-long{
            page-break-inside:auto;
        }

        .materi-box{
            border:1px solid #000;
            border-top:none;
            padding:6px 8px;
            min-height:0;
            line-height:1.4;
            margin-bottom:0;
            page-break-inside:auto;
        }

        .kehadiran{
            width:100%;
            margin-top:0;
            margin-bottom:6px;
        }

        .kehadiran th{
            border:1px solid #000;
            background:#ECECEC;
            padding:5px;
            text-align:center;
            font-size:10px;
            font-weight:bold;
        }

        .kehadiran td{
            border:1px solid #000;
            padding:6px;
            text-align:center;
            font-size:10.5px;
        }

        .catatan-box{
            border:1px solid #000;
            border-top:none;
            padding:6px 8px;
            min-height:0;
            line-height:1.4;
            margin-bottom:0;
            page-break-inside:auto;
        }

        .signature-block{
            page-break-inside:avoid;
            margin-top:10px;
        }

        .footer td{
            width:50%;

            text-align:center;

            vertical-align:top;
        }

        .ttd{
            margin-top:38px;
            font-weight:bold;
            text-decoration:underline;
            font-size:11px;
        }

        .nip{
            margin-top:5px;
            font-size:10px;
        }

        .footer{
            width:100%;
            margin-top:12px;
            font-size:10px;
        }

.footer table{
    width:100%;
    border-collapse:collapse;
}

.footer td{
    vertical-align:top;
}

.small{
    font-size:10px;
    color:#555;
}

.center{
    text-align:center;
}

.right{
    text-align:right;
}

.bold{
    font-weight:bold;
}

.qr{
    width:70px;
    height:70px;
}

        .page-number{
            position:fixed;
            bottom:-10px;
            right:0;
            font-size:10px;
            color:#666;
        }

        .foto-grid{
            border:1px solid #000;
            border-top:none;
            padding:6px;
            margin-bottom:0;
            page-break-inside:avoid;
        }

        .foto-grid table{
            width:100%;
        }

        .foto-grid td{
            text-align:center;
            padding:4px;
            vertical-align:top;
        }

        .foto-grid img{
            max-width:88px;
            max-height:66px;
            width:auto;
            height:auto;
            object-fit:contain;
            border:1px solid #ccc;
        }

        .foto-grid.foto-count-1 img{
            max-width:110px;
            max-height:82px;
        }

        .foto-grid.foto-count-2 img{
            max-width:95px;
            max-height:71px;
        }

        .foto-empty{
            border:1px solid #000;
            border-top:none;
            padding:6px 8px;
            margin-bottom:0;
            font-style:italic;
            color:#555;
            font-size:9.5px;
        }

        .uat-pdf-footer{
            margin-top:10px;
            padding-top:6px;
            border-top:1px dashed #999;
            font-size:8.5px;
            color:#666;
            text-align:center;
            line-height:1.35;
        }

    </style>

</head>

<div class="footer">

<table>

<tr>

<td width="33%">

<div class="small">
Dicetak :
<br>

{{ now()->translatedFormat('d F Y H:i') }}

</div>

</td>

<td width="34%" class="center">

{{-- QR Code nanti diletakkan di sini --}}

</td>

<td width="33%" class="right">

<div class="small">

Dokumen dibuat oleh

<br>

SIM MAN 3 JEMBER

</div>

</td>

</tr>

</table>

</div>

<div class="page-number">

Halaman 1

</div>

<body>

    <!-- ===========================
         HEADER MADRASAH
    ============================ -->

    <table class="header-table">

        <tr>

            <td class="logo">

                <img src="{{ public_path('images/LOGO_KEMENAG-removebg-preview.png') }}">

            </td>

            <td class="identitas">

                <div class="kemenag">
                    KEMENTERIAN AGAMA REPUBLIK INDONESIA
                </div>

                <div class="kantor">
                    KANTOR KEMENTERIAN AGAMA KABUPATEN JEMBER
                </div>

                <div class="madrasah">
                    MADRASAH ALIYAH NEGERI 3 JEMBER
                </div>

                <div class="alamat">
                    Jl. Jend. A. Yani no 40, kec. Jombang, Kab. jember
                </div>

                <div class="alamat">
                    
                </div>

            </td>

        </tr>

    </table>

    <hr class="garis1">

    <hr class="garis2">

    <div class="judul">

        JURNAL KEGIATAN BELAJAR MENGAJAR

    </div>



<!-- =========================================
     IDENTITAS JURNAL
========================================== -->

<table class="identitas-jurnal">

    <tr>
        <td class="label">Nama Guru (Pengampu)</td>
        <td class="titik">:</td>
        <td class="isi">{{ $jurnal->jadwal->guru->nama }}</td>

        <td class="label">Hari</td>
        <td class="titik">:</td>
        <td class="isi">{{ $jurnal->jadwal->hari }}</td>
    </tr>

    <tr>
        <td class="label">Guru Pengganti</td>
        <td class="titik">:</td>
        <td class="isi">{{ $jurnal->guru_pengganti ?: '-' }}</td>

        <td class="label">Tanggal</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d F Y') }}
        </td>
    </tr>

    <tr>
        <td class="label">NIP Pengampu</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ $jurnal->jadwal->guru->nip ?? '-' }}
        </td>

        <td class="label"></td>
        <td class="titik"></td>
        <td class="isi"></td>
    </tr>

    <tr>
        <td class="label">Mata Pelajaran</td>
        <td class="titik">:</td>
        <td class="isi">{{ $jurnal->jadwal->mapel }}</td>

        <td class="label">Jam Pelajaran</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ $jurnal->jamKeLabel() }}
        </td>
    </tr>

    <tr>
        <td class="label">Kelas</td>
        <td class="titik">:</td>
        <td class="isi">{{ $jurnal->jadwal->kelas }}</td>

        <td class="label">Waktu</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ $jurnal->effectiveJamMulai() }}
            -
            {{ $jurnal->effectiveJamSelesai() }}
        </td>
    </tr>

    <tr>
        <td class="label">Semester</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ $jurnal->jadwal->semester }}
        </td>

        <td class="label">Tahun Pelajaran</td>
        <td class="titik">:</td>
        <td class="isi">
            {{ $jurnal->jadwal->tahun_pelajaran }}
        </td>
    </tr>

    <tr>
        <td class="label">Status KBM</td>
        <td class="titik">:</td>
        <td colspan="4" class="isi">

            <strong>{{ $jurnal->status_kbm }}</strong>

        </td>
    </tr>

</table>

<!-- =========================================
     MATERI PEMBELAJARAN
========================================== -->

<div class="section-group section-group-long">

<div class="section-title">
    MATERI PEMBELAJARAN
</div>

<div class="materi-box" style="font-family: DejaVu Sans, sans-serif;">
    @php
        $materiRaw = (string) ($jurnal->materi ?? '');
        $materiHasArab = (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $materiRaw);
        $materiPdf = $materiHasArab
            ? \App\Support\ArabicPdfText::shape($materiRaw, 200)
            : $materiRaw;
    @endphp
    <div @if($materiHasArab) style="direction:ltr; text-align:right; unicode-bidi:normal;" @endif>
        {!! nl2br(e($materiPdf)) !!}
    </div>
</div>

</div>

<!-- =========================================
     KEHADIRAN SISWA
========================================== -->

<div class="section-group">

<div class="section-title">
    KEHADIRAN SISWA
</div>

<table class="kehadiran">

    <thead>

        <tr>

            <th>Hadir</th>
            <th>Izin</th>
            <th>Sakit</th>
            <th>Alpha</th>

        </tr>

    </thead>

    <tbody>

        <tr>

            <td>{{ $jurnal->hadir }}</td>
            <td>{{ $jurnal->izin }}</td>
            <td>{{ $jurnal->sakit }}</td>
            <td>{{ $jurnal->alpha }}</td>

        </tr>

    </tbody>

</table>

</div>

<!-- =========================================
     CATATAN GURU
========================================== -->

<div class="section-group section-group-long">

<div class="section-title">
    CATATAN GURU
</div>

<div class="catatan-box">

    {{ $jurnal->catatan ?: '-' }}

</div>

</div>

<!-- =========================================
     DOKUMENTASI PEMBELAJARAN
========================================== -->

@php
    $fotoDokumentasi = $jurnal->attachments->take(\App\Models\JurnalAttachment::MAX_PER_JURNAL);
    $fotoCount = $fotoDokumentasi->count();
@endphp

<div class="section-group">

<div class="section-title">
    DOKUMENTASI PEMBELAJARAN
</div>

@if ($fotoDokumentasi->isNotEmpty())
    <div class="foto-grid foto-count-{{ min($fotoCount, 3) }}">
        <table>
            <tr>
                @foreach ($fotoDokumentasi as $attachment)
                    <td>
                        @if (file_exists($attachment->thumbAbsolutePath()))
                            <img src="{{ $attachment->thumbAbsolutePath() }}" alt="Foto kegiatan">
                            @if ($attachment->caption)
                                <div style="font-size:9px;margin-top:4px;">{{ e($attachment->caption) }}</div>
                            @endif
                        @endif
                    </td>
                    @if ($loop->iteration % 3 === 0 && ! $loop->last)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>
@else
    <div class="foto-empty">
        Tidak ada dokumentasi pembelajaran.
    </div>
@endif

</div>

<!-- =========================================
     TANDA TANGAN
========================================== -->

<table class="footer signature-block">

    <tr>

        <td>

            Mengetahui,

            <br>

            Kepala Madrasah

            <div class="ttd">

                Imam Syahroni, S.Pd., M.Pd.

            </div>

            <div class="nip">

                NIP. ....................................

            </div>

        </td>

        <td>

            Jember,
            {{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d F Y') }}

            <br>

            Guru Mata Pelajaran

            <div class="ttd">

                {{ $jurnal->jadwal->guru->nama }}

            </div>

            <div class="nip">

                NIP.
                {{ $jurnal->jadwal->guru->nip ?? '-' }}

            </div>

        </td>

    </tr>

</table>

@if ($showUatPdfFooter ?? false)
    <div class="uat-pdf-footer">
        Dokumen dibuat menggunakan<br>
        UAT Simulation Mode<br>
        SIM MAN 3 Jember
    </div>
@endif

</body>

</html>