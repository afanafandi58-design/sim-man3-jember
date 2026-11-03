# Buku Induk Online — MAN 3 Jember

Aplikasi PHP 7.4 untuk mengelola Buku Induk siswa: input manual, upload Excel, cetak/PDF.

## Persyaratan

- PHP 7.4+ (kode kompatibel 7.4; hindari fitur PHP 8+)
- Ekstensi: `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `xml`, `gd`, `zip`
- Composer (untuk instalasi dependensi)

## Instalasi lokal

```bash
cd buku-induk
cp config/config.example.php config/config.local.php
# edit seed_admin password
composer install
php -S localhost:8080 -t public
```

Buka `http://localhost:8080` — login dengan kredensial `seed_admin` di config.

## Deploy cPanel (subdomain Buku Induk)

1. Subdomain: `bukuinduk.man3jember.sch.id`
2. Document root mengarah ke folder `public/` aplikasi (bukan ke jurnal-kbm)
3. Salin `config.local.php` di server (jangan commit secret)
4. Untuk MySQL: set `db_driver` = `mysql` dan isi kredensial DB
5. Pastikan `storage/` writable
6. Jalankan `composer install --no-dev` di server, atau unggah folder `vendor/`

## Fitur MVP

- Login admin (session + CSRF)
- CRUD siswa Buku Induk
- Impor Excel sheet `DATA_SISWA`
- Cetak HTML + unduh PDF (mPDF)
- Pengaturan identitas madrasah
