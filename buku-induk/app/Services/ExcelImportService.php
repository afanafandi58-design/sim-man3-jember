<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Siswa;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            // Alias lama Excel makro (duplikat "Pada Tanggal" → mutasi)
            'pada tanggal' => 'mutasi_tanggal',
            'pada tanggal mutasi' => 'mutasi_tanggal',
            'tanggal mutasi' => 'mutasi_tanggal',
            'mutasi tanggal' => 'mutasi_tanggal',
            'alasan' => 'keluar_alasan',
            'alasan keluar' => 'keluar_alasan',
            'nomor surat mutasi' => 'keluar_surat_nomor',
            'tanggal keluar' => 'keluar_tanggal',
            'pada tanggal keluar' => 'keluar_tanggal',
            'keluar tanggal' => 'keluar_tanggal',
            'keterangan keluar' => 'keluar_keterangan',
            'keluar keterangan' => 'keluar_keterangan',
        ];
    }

    /**
     * Header resmi template impor (urutan kolom) — selaras headerMap / field DB.
     *
     * @return list<string>
     */
    public static function templateColumnHeaders(): array
    {
        return [
            'Nis Lokal',
            'NISN',
            'Nama',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat Siswa',
            'Agama',
            'Status Keluarga',
            'Anak Ke',
            'Nomor HP',
            'Sekolah Asal',
            'Tanggal Terima',
            'Tingkat Awal',
            'Nama Ayah',
            'Nama Ibu',
            'Pekerjaan Ayah',
            'Pekerjaan Ibu',
            'Alamat Orang Tua',
            'Nama Wali',
            'Pekerjaan Wali',
            'Alamat Wali',
            'Agama Ayah',
            'Agama Ibu',
            'Agama Wali',
            'HP Wali',
            'Nomor Ijazah',
            'Nilai Ujian Akhir',
            'Mutasi Dari',
            'Diterima Di Kelas',
            'Pada Tanggal Mutasi',
            'Surat Mutasi Nomor',
            'Alamat Sekolah Asal',
            'Tanggal Keluar',
            'Alasan',
            'Nomor Surat Mutasi',
            'Keterangan Keluar',
        ];
    }

    /**
     * Baris contoh realistis MAN 3 Jember (bukan kosong).
     *
     * @return list<string|int>
     */
    public static function templateSampleRow(): array
    {
        return [
            '2526001',
            '0051234567',
            'Ahmad Fauzan Pratama',
            'Laki-laki',
            'Jember',
            '15-03-2009',
            'Jl. MH Thamrin No. 12, Kec. Jombang, Kab. Jember',
            'Islam',
            'Anak Kandung',
            2,
            '081234567890',
            'MTsN 2 Jember',
            '15-07-2024',
            'X.A',
            'H. Samsul Arifin',
            'Siti Aminah',
            'Petani',
            'Ibu Rumah Tangga',
            'Jl. MH Thamrin No. 12, Kec. Jombang, Kab. Jember',
            '',
            '',
            '',
            'Islam',
            'Islam',
            '',
            '',
            'MTs-2024/131/089',
            '85.50',
            '',
            '',
            '',
            '',
            'Jl. Argopuro No. 45, Kab. Jember',
            '',
            '',
            '',
            '',
        ];
    }

    /**
     * Bangun Spreadsheet template sheet DATA_SISWA.
     */
    public static function buildTemplateSpreadsheet(): Spreadsheet
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new \RuntimeException('Library PhpSpreadsheet belum terpasang. Jalankan composer install.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DATA_SISWA');

        $headers = self::templateColumnHeaders();
        $sample = self::templateSampleRow();

        foreach ($headers as $i => $header) {
            $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
        }
        foreach ($sample as $i => $value) {
            $sheet->setCellValueByColumnAndRow($i + 1, 2, $value);
        }

        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        return $spreadsheet;
    }

    /**
     * Tulis file .xlsx template ke path tujuan.
     */
    public static function writeTemplateFile(string $path): void
    {
        $spreadsheet = self::buildTemplateSpreadsheet();
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
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
