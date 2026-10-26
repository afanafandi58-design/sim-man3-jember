<?php

/**
 * Identitas madrasah untuk kop surat & blok tanda tangan PDF.
 * Ubah via .env bila perlu tanpa menyentuh blade.
 */
return [

    'nama' => env('MADRASAH_NAMA', 'Madrasah Aliyah Negeri 3 Jember'),

    'kepala' => [
        'nama' => env('MADRASAH_KEPALA_NAMA', 'Imam Syahroni, S.Pd., M.Pd.'),
        'nip' => env('MADRASAH_KEPALA_NIP', '198309202009011007'),
        'jabatan' => env('MADRASAH_KEPALA_JABATAN', 'Kepala Madrasah'),
    ],

];
