<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Sekolah;
use App\Models\Siswa;
use App\Services\PdfService;

class CetakController
{
    public function show(int $id): void
    {
        require_login();
        $siswa = Siswa::find($id);
        if (!$siswa) {
            flash('error', 'Siswa tidak ditemukan.');
            redirect('/siswa');
        }
        $sekolah = Sekolah::get();
        $pdf = new PdfService();
        $html = $pdf->renderBukuIndukHtml($siswa, $sekolah, false);
        // Mode cetak browser (tombol PDF terpisah)
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    public function pdf(int $id): void
    {
        require_login();
        $siswa = Siswa::find($id);
        if (!$siswa) {
            flash('error', 'Siswa tidak ditemukan.');
            redirect('/siswa');
        }
        $sekolah = Sekolah::get();
        $pdf = new PdfService();
        $html = $pdf->renderBukuIndukHtml($siswa, $sekolah, true);
        $filename = 'Buku_Induk_' . preg_replace('/[^0-9A-Za-z_-]/', '_', (string) $siswa['nis_lokal']) . '.pdf';
        $pdf->outputPdf($html, $filename);
        exit;
    }
}
