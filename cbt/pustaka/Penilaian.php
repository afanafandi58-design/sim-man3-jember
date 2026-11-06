<?php
declare(strict_types=1);

final class Penilaian
{
    public static function nilai(string $tipe, ?array $isi, array $kunci): string
    {
        $isi = $isi ?? [];
        if ($tipe === 'pg') {
            return self::pilihanGanda(trim((string) ($isi['pilihan'] ?? '')), (string) ($kunci['pg'] ?? ''));
        }
        if ($tipe === 'pgk') {
            $dipilih = $isi['pilihan'] ?? [];
            if (!is_array($dipilih)) {
                $dipilih = [];
            }
            return self::pilihanGandaKompleks($dipilih, $kunci['pgk'] ?? []);
        }
        $pasangan = $isi['pasangan'] ?? [];
        if (!is_array($pasangan)) {
            $pasangan = [];
        }
        return self::menjodohkan($pasangan, $kunci['pasangan'] ?? []);
    }

    public static function pilihanGanda(string $pilihan, string $kunci): string
    {
        if ($pilihan === '') {
            return 'kosong';
        }
        return strcasecmp($pilihan, $kunci) === 0 ? 'benar' : 'salah';
    }

    public static function pilihanGandaKompleks(array $dipilih, array $kunci): string
    {
        $milik = self::himpunan($dipilih);
        if ($milik === []) {
            return 'kosong';
        }
        return $milik === self::himpunan($kunci) ? 'benar' : 'salah';
    }

    public static function menjodohkan(array $jawaban, array $kunci): string
    {
        $bersih = [];
        foreach ($jawaban as $kiri => $kanan) {
            $kanan = trim((string) $kanan);
            if ($kanan !== '') {
                $bersih[(string) $kiri] = $kanan;
            }
        }
        if ($bersih === []) {
            return 'kosong';
        }
        if ($kunci === []) {
            return 'salah';
        }
        foreach ($kunci as $kiri => $kanan) {
            $punya = $bersih[(string) $kiri] ?? '';
            if ($punya === '' || strcasecmp($punya, (string) $kanan) !== 0) {
                return 'salah';
            }
        }
        return count($bersih) === count($kunci) ? 'benar' : 'salah';
    }

    private static function himpunan(array $nilai): array
    {
        $hasil = [];
        foreach ($nilai as $item) {
            $item = strtoupper(trim((string) $item));
            if ($item !== '') {
                $hasil[$item] = $item;
            }
        }
        $hasil = array_values($hasil);
        sort($hasil);
        return $hasil;
    }
}
