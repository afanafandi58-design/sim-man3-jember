<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Sekolah;

class SettingsController
{
    public function form(): void
    {
        require_login();
        view('layout/main', [
            'title' => 'Pengaturan Madrasah',
            'content' => 'settings/form',
            'sekolah' => Sekolah::get(),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public function save(): void
    {
        require_login();
        verify_csrf();
        Sekolah::save([
            'nama' => isset($_POST['nama']) ? trim((string) $_POST['nama']) : '',
            'nsm' => isset($_POST['nsm']) ? trim((string) $_POST['nsm']) : '',
            'npsn' => isset($_POST['npsn']) ? trim((string) $_POST['npsn']) : '',
            'alamat' => isset($_POST['alamat']) ? trim((string) $_POST['alamat']) : '',
            'tahun_angkatan_default' => isset($_POST['tahun_angkatan_default']) ? trim((string) $_POST['tahun_angkatan_default']) : '',
        ]);
        flash('success', 'Pengaturan disimpan.');
        redirect('/settings');
    }
}
