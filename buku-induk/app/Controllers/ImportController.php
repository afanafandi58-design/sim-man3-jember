<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Database;
use App\Services\ExcelImportService;

class ImportController
{
    public function form(): void
    {
        require_login();
        view('layout/main', [
            'title' => 'Upload Excel',
            'content' => 'import/form',
            'success' => flash('success'),
            'error' => flash('error'),
            'result' => isset($_SESSION['import_result']) ? $_SESSION['import_result'] : null,
        ]);
        unset($_SESSION['import_result']);
    }

    public function upload(): void
    {
        require_login();
        verify_csrf();

        if (!isset($_FILES['excel']) || !is_array($_FILES['excel'])) {
            flash('error', 'File tidak ditemukan.');
            redirect('/import');
        }

        $file = $_FILES['excel'];
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Upload gagal. Periksa ukuran file dan izin folder.');
            redirect('/import');
        }

        $name = isset($file['name']) ? (string) $file['name'] : 'upload.xlsx';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xlsm', 'xls'], true)) {
            flash('error', 'Format harus .xlsx / .xlsm / .xls');
            redirect('/import');
        }

        $dir = dirname(__DIR__, 2) . '/storage/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $dest = $dir . '/' . date('YmdHis') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            flash('error', 'Gagal menyimpan file upload.');
            redirect('/import');
        }

        try {
            $service = new ExcelImportService();
            $result = $service->import($dest);

            $user = current_user();
            $stmt = Database::pdo()->prepare('INSERT INTO import_log (user_id, filename, total, sukses, gagal, detail_json, created_at) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([
                $user ? $user['id'] : null,
                $name,
                $result['total'],
                $result['sukses'],
                $result['gagal'],
                json_encode($result, JSON_UNESCAPED_UNICODE),
                date('Y-m-d H:i:s'),
            ]);

            $_SESSION['import_result'] = $result;
            flash('success', 'Impor selesai: ' . $result['sukses'] . ' sukses, ' . $result['gagal'] . ' gagal.');
        } catch (\Throwable $e) {
            flash('error', 'Gagal impor: ' . $e->getMessage());
        }

        // Hapus file sementara
        if (is_file($dest)) {
            @unlink($dest);
        }

        redirect('/import');
    }
}
