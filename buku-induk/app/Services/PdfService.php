<?php

declare(strict_types=1);

namespace App\Services;

class PdfService
{
    public function renderBukuIndukHtml(array $siswa, array $sekolah): string
    {
        ob_start();
        $viewFile = dirname(__DIR__, 2) . '/views/cetak/buku_induk.php';
        require $viewFile;
        return (string) ob_get_clean();
    }

    public function outputPdf(string $html, string $filename): void
    {
        if (!class_exists('\\Mpdf\\Mpdf')) {
            // Fallback: tampilkan HTML cetak
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            return;
        }

        $tmpDir = dirname(__DIR__, 2) . '/storage/cache/mpdf';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => $tmpDir,
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    }
}
