<?php
declare(strict_types=1);

final class Xlsx
{
    public static function baca(string $jalur): array
    {
        $zip = new ZipArchive();
        if ($zip->open($jalur) !== true) {
            throw new RuntimeException('Berkas Excel tidak bisa dibaca.');
        }
        $bersama = $zip->getFromName('xl/sharedStrings.xml');
        $lembar = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($lembar === false) {
            throw new RuntimeException('Lembar pertama Excel kosong.');
        }
        $string = self::stringBersama($bersama === false ? '' : $bersama);
        return self::barisLembar($lembar, $string);
    }

    public static function tulis(array $baris): string
    {
        $sementara = tempnam(sys_get_temp_dir(), 'cbtxlsx');
        if ($sementara === false) {
            throw new RuntimeException('Berkas sementara tidak bisa dibuat.');
        }
        unlink($sementara);
        $zip = new ZipArchive();
        if ($zip->open($sementara, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Excel tidak bisa dibuat.');
        }
        $string = [];
        $indeks = [];
        $xmlBaris = '';
        foreach (array_values($baris) as $i => $kolom) {
            $sel = '';
            foreach (array_values($kolom) as $k => $nilai) {
                $teks = (string) $nilai;
                if (!array_key_exists($teks, $indeks)) {
                    $indeks[$teks] = count($string);
                    $string[] = $teks;
                }
                $sel .= '<c r="' . self::huruf($k) . ($i + 1) . '" t="s"><v>' . $indeks[$teks] . '</v></c>';
            }
            $xmlBaris .= '<row r="' . ($i + 1) . '">' . $sel . '</row>';
        }
        $xmlString = '';
        foreach ($string as $teks) {
            $xmlString .= '<si><t xml:space="preserve">' . self::xml($teks) . '</t></si>';
        }
        $zip->addFromString('[Content_Types].xml', self::tipe());
        $zip->addFromString('_rels/.rels', self::relAkar());
        $zip->addFromString('xl/workbook.xml', self::buku());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::relBuku());
        $zip->addFromString('xl/styles.xml', self::gaya());
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($string) . '" uniqueCount="' . count($string) . '">'
            . $xmlString . '</sst>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
            . $xmlBaris . '</sheetData></worksheet>');
        $zip->close();
        $isi = file_get_contents($sementara);
        unlink($sementara);
        if ($isi === false) {
            throw new RuntimeException('Excel tidak bisa dibaca kembali.');
        }
        return $isi;
    }

    private static function stringBersama(string $xml): array
    {
        if ($xml === '') {
            return [];
        }
        $dom = new DOMDocument();
        if (!$dom->loadXML($xml)) {
            throw new RuntimeException('Daftar teks Excel rusak.');
        }
        $hasil = [];
        foreach ($dom->getElementsByTagName('si') as $si) {
            $teks = '';
            foreach ($si->childNodes as $anak) {
                if (!$anak instanceof DOMElement) {
                    continue;
                }
                if ($anak->localName === 't') {
                    $teks .= $anak->textContent;
                } elseif ($anak->localName === 'r') {
                    foreach ($anak->getElementsByTagName('t') as $potong) {
                        $teks .= $potong->textContent;
                    }
                }
            }
            $hasil[] = $teks;
        }
        return $hasil;
    }

    private static function barisLembar(string $xml, array $string): array
    {
        $dom = new DOMDocument();
        if (!$dom->loadXML($xml)) {
            throw new RuntimeException('Lembar Excel rusak.');
        }
        $baris = [];
        foreach ($dom->getElementsByTagName('row') as $row) {
            $isi = [];
            foreach ($row->childNodes as $sel) {
                if (!$sel instanceof DOMElement || $sel->localName !== 'c') {
                    continue;
                }
                $ref = (string) $sel->getAttribute('r');
                $huruf = preg_replace('/[0-9]/', '', $ref) ?? '';
                $kolom = self::indeks($huruf);
                $tipe = (string) $sel->getAttribute('t');
                $nilai = '';
                if ($tipe === 'inlineStr') {
                    $nilai = $sel->textContent;
                } else {
                    $v = '';
                    foreach ($sel->childNodes as $anak) {
                        if ($anak instanceof DOMElement && $anak->localName === 'v') {
                            $v = $anak->textContent;
                            break;
                        }
                    }
                    $nilai = $tipe === 's' ? ($string[(int) $v] ?? '') : $v;
                }
                $isi[$kolom] = $nilai;
            }
            if ($isi === []) {
                continue;
            }
            ksort($isi);
            $maks = max(array_keys($isi));
            $rapi = [];
            for ($i = 0; $i <= $maks; $i++) {
                $rapi[] = (string) ($isi[$i] ?? '');
            }
            $baris[] = $rapi;
        }
        return $baris;
    }

    public static function indeks(string $huruf): int
    {
        $huruf = strtoupper($huruf);
        $n = 0;
        $panjang = strlen($huruf);
        for ($i = 0; $i < $panjang; $i++) {
            $n = $n * 26 + (ord($huruf[$i]) - 64);
        }
        return $n - 1;
    }

    private static function huruf(int $indeks): string
    {
        $s = '';
        $indeks++;
        while ($indeks > 0) {
            $s = chr(65 + (($indeks - 1) % 26)) . $s;
            $indeks = intdiv($indeks - 1, 26);
        }
        return $s;
    }

    private static function xml(string $nilai): string
    {
        return htmlspecialchars($nilai, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function tipe(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function relAkar(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function relBuku(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function buku(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Soal" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private static function gaya(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
            . '<cellXfs count="1"><xf xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }
}
