<?php
declare(strict_types=1);

final class WaktuUjian
{
    /** Detik. Klien mengetuk tiap 5 detik. Lebih lama dari ini dianggap putus. */
    public const AMBANG_PUTUS = 15;

    /**
     * @param array{sisa_detik:int|string,checkpoint_pada:string,terakhir_ping:string,status:string} $baris
     * @return array{sisa_detik:int,status:string,putus:bool}
     */
    public static function hitung(array $baris, int $sekarang): array
    {
        $sisa = (int) $baris['sisa_detik'];
        $status = (string) $baris['status'];
        if ($status !== 'berjalan') {
            return ['sisa_detik' => max(0, $sisa), 'status' => $status, 'putus' => false];
        }
        $ping = strtotime((string) $baris['terakhir_ping']);
        $cek = strtotime((string) $baris['checkpoint_pada']);
        if ($ping === false || $cek === false) {
            return ['sisa_detik' => max(0, $sisa), 'status' => 'berjalan', 'putus' => false];
        }
        if (($sekarang - $ping) > self::AMBANG_PUTUS) {
            $terpakai = max(0, $ping - $cek);
            return [
                'sisa_detik' => max(0, $sisa - $terpakai),
                'status' => 'berjalan',
                'putus' => true,
            ];
        }
        return [
            'sisa_detik' => max(0, $sisa - max(0, $sekarang - $cek)),
            'status' => 'berjalan',
            'putus' => false,
        ];
    }
}
