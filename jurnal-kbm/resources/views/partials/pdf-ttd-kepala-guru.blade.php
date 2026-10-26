{{--
  Blok tanda tangan PDF: Mengetahui Kepala Madrasah + Guru.
  Variabel opsional: $guruNama, $guruNip, $tanggalTtd (Carbon|string|null)
--}}
@php
    $kamadNama = config('madrasah.kepala.nama', 'Imam Syahroni, S.Pd., M.Pd.');
    $kamadNip = config('madrasah.kepala.nip', '198309202009011007');
    $kamadJabatan = config('madrasah.kepala.jabatan', 'Kepala Madrasah');
    $ttdDate = isset($tanggalTtd)
        ? \Carbon\Carbon::parse($tanggalTtd)->translatedFormat('d F Y')
        : now()->translatedFormat('d F Y');
@endphp
<table class="footer signature-block" style="width:100%; margin-top:28px; border-collapse:collapse;">
    <tr>
        <td style="width:50%; text-align:center; font-size:10px; line-height:1.4; vertical-align:top;">
            Mengetahui,<br>
            {{ $kamadJabatan }}
            <div class="ttd" style="margin-top:48px; font-weight:bold; text-decoration:underline;">
                {{ $kamadNama }}
            </div>
            <div class="nip" style="font-size:9px;">
                NIP. {{ $kamadNip }}
            </div>
        </td>
        <td style="width:50%; text-align:center; font-size:10px; line-height:1.4; vertical-align:top;">
            Jember, {{ $ttdDate }}<br>
            Guru Mata Pelajaran
            <div class="ttd" style="margin-top:48px; font-weight:bold; text-decoration:underline;">
                {{ $guruNama ?? '-' }}
            </div>
            <div class="nip" style="font-size:9px;">
                NIP. {{ $guruNip ?? '-' }}
            </div>
        </td>
    </tr>
</table>
