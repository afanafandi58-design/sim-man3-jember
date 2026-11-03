<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelImportService
{
    /** Mapping header (normalized) -> field DB */
    private static function headerMap(): array
    {
        return [
            'nis lokal' => 'nis_lokal',
            'nis_lokal' => 'nis_lokal',
            'nis' => 'nis_lokal',
            'nisn' => 'nisn',
            'nama' => 'nama',
            'jenis kelamin' => 'jenis_kelamin',
            'tempat lahir' => 'tempat_lahir',
            'tgl lahir (dd-mm-yyyy)' => 'tanggal_lahir',
            'tanggal lahir' => 'tanggal_lahir',
            'alamat siswa' => 'alamat',
            'alamat' => 'alamat',
            'agama' => 'agama',
            'status keluarga' => 'status_keluarga',
            'anak ke' => 'anak_ke',
            'nomor hp' => 'nomor_hp',
            'sekolah asal' => 'sekolah_asal',
            'tgl terima' => 'tanggal_terima',
            'tanggal terima' => 'tanggal_terima',
            'tingkat awal' => 'tingkat_awal',
            'nama ayah' => 'nama_ayah',
            'nama ibu' => 'nama_ibu',
            'pekerjaan ayah' => 'pekerjaan_ayah',
            'pekerjaan ibu' => 'pekerjaan_ibu',
            'alamat orang tua' => 'alamat_orang_tua',
            'nama wali' => 'nama_wali',
            'pekerjaan wali' => 'pekerjaan_wali',
            'alamat wali' => 'alamat_wali',
            'agama ayah' => 'agama_ayah',
            'agama ibu' => 'agama_ibu',
            'agama wali' => 'agama_wali',
            'hp wali' => 'hp_wali',
            'nomor ijazah' => 'nomor_ijazah',
            'nilai ujian akhir' => 'nilai_ujian_akhir',
            'mutasi dari' => 'mutasi_dari',
            'diterima di kelas' => 'mutasi_diterima_kelas',
            'alamat sekolah asal' => 'alamat_sekolah_asal',
            'surat mutasi nomor' => 'mutasi_surat_nomor',
            'pada tanggal' => 'mutasi_tanggal',
            'alasan' => 'keluar_alasan',
            'nomor surat mutasi' => 'keluar_surat_nomor',
        ];
    }

    private static function normalizeHeader($value): string
    {
        $s = strtolower(trim((string) $value));
        $s = preg_replace('/\s+/', ' ', $s);
        return rtrim($s, " \t:");
    }

    /**
     * @return array{total:int,sukses:int,gagal:int,errors:array,created:int,updated:int}
     */
    public function import(string $filePath): array
    {
        if (!class_exists(IOFactory::class)) {
            throw new \RuntimeException('Library PhpSpreadsheet belum terpasang. Jalankan composer install.');
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = null;
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strtoupper($name) === 'DATA_SISWA') {
                $sheet = $spreadsheet->getSheetByName($name);
                break;
            }
        }
        if ($sheet === null) {
            $sheet = $spreadsheet->getActiveSheet();
        }

        $highestRow = (int) $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        // Cari baris header (cari sel berisi "NISN" atau "Nis Lokal")
        $headerRow = 1;
        $map = [];
        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $tmp = [];
            for ($c = 1; $c <= $highestColIndex; $c++) {
                $val = $sheet->getCellByColumnAndRow($c, $r)->getValue();
                $norm = self::normalizeHeader($val);
                if ($norm !== '' && isset(self::headerMap()[$norm])) {
                    $tmp[$c] = self::headerMap()[$norm];
                }
            }
            if (in_array('nis_lokal', $tmp, true) || in_array('nama', $tmp, true)) {
                $headerRow = $r;
                $map = $tmp;
                break;
            }
        }

        if ($map === []) {
            throw new \RuntimeException('Header kolom siswa tidak ditemukan. Pastikan ada kolom Nis Lokal / NAMA.');
        }

        $result = [
            'total' => 0,
            'sukses' => 0,
            'gagal' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
        ];

        $dateFields = ['tanggal_lahir', 'tanggal_terima', 'mutasi_tanggal', 'keluar_tanggal'];

        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $rowData = [];
            foreach ($map as $colIndex => $field) {
                $cell = $sheet->getCellByColumnAndRow($colIndex, $r);
                $val = $cell->getValue();
                if ($val instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $val = $val->getPlainText();
                }
                // Prefer calculated / formatted for dates
                if (in_array($field, $dateFields, true)) {
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                        try {
                            $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cell->getValue());
                            $val = $dt->format('Y-m-d');
                        } catch (\Exception $e) {
                            $val = parse_tanggal_flexible($cell->getFormattedValue());
                        }
                    } else {
                        $val = parse_tanggal_flexible($val !== null ? $val : $cell->getFormattedValue());
                    }
                } else {
                    if (is_string($val)) {
                        $val = trim($val);
                    } elseif (is_float($val) || is_int($val)) {
                        // NISN dll sering terbaca angka
                        if (in_array($field, ['nisn', 'nis_lokal', 'nomor_hp', 'hp_wali'], true)) {
                            $val = rtrim(rtrim(sprintf('%.0f', (float) $val), '0'), '.');
                            if ($val === '') {
                                $val = (string) $cell->getFormattedValue();
                            }
                        } else {
                            $val = (string) $val;
                        }
                    }
                }
                // Jika field sudah dipetakan sebelumnya (header duplikat seperti "Pada Tanggal" / "Alasan"),
                // jangan timpa nilai yang sudah terisi kecuali kosong.
                if (!array_key_exists($field, $rowData) || $rowData[$field] === null || $rowData[$field] === '') {
                    $rowData[$field] = $val;
                }
            }

            $nama = isset($rowData['nama']) ? trim((string) $rowData['nama']) : '';
            $nis = isset($rowData['nis_lokal']) ? trim((string) $rowData['nis_lokal']) : '';
            if ($nama === '' && $nis === '') {
                continue;
            }

            $result['total']++;
            try {
                if ($nis === '' || $nama === '') {
                    throw new \InvalidArgumentException('NIS Lokal dan Nama wajib');
                }
                if (isset($rowData['jenis_kelamin'])) {
                    $rowData['jenis_kelamin'] = normalize_jk((string) $rowData['jenis_kelamin']);
                }
                if (isset($rowData['anak_ke']) && $rowData['anak_ke'] !== null && $rowData['anak_ke'] !== '') {
                    $rowData['anak_ke'] = (int) $rowData['anak_ke'];
                } else {
                    $rowData['anak_ke'] = null;
                }
                $action = Siswa::upsertByNis($rowData);
                $result['sukses']++;
                if ($action === 'created') {
                    $result['created']++;
                } else {
                    $result['updated']++;
                }
            } catch (\Throwable $e) {
                $result['gagal']++;
                if (count($result['errors']) < 50) {
                    $result['errors'][] = 'Baris ' . $r . ': ' . $e->getMessage();
                }
            }
        }

        return $result;
    }
}
