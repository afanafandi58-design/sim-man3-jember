<?php

return [

    'default_semester' => 'Ganjil',

    'default_tahun_pelajaran' => '2026/2027',

    'uat' => [
        'enabled' => env('UAT_SIMULATION_ENABLED', false),
        'guru_nip' => env('UAT_SIMULATION_GURU_NIP', '198501152010012001'),
        'guru_nama' => env('UAT_SIMULATION_GURU_NAMA', ''),
        'hari' => env('UAT_SIMULATION_HARI', 'Senin'),
    ],

];
