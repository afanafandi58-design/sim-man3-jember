# Pasang di XAMPP

Untuk XAMPP dengan PHP 8, Apache, dan MariaDB. Arahkan kerja di folder proyek.

1. Nyalakan Apache dan MySQL dari panel XAMPP.
2. Salin proyek ke `C:\xampp\htdocs\sim-man3-jember`.
3. Salin `cbt\.env.example` menjadi `cbt\.env`.
4. Isi `CBT_DB_*` sesuai MariaDB server. Di XAMPP baru, pengguna `root` sering tanpa kata sandi. Ganti itu di server sungguhan, dan tulis kata sandinya hanya di `.env`.
5. Isi `CBT_ADMIN_PASSWORD` (minimal 8 karakter) hanya di `.env`. Jangan masukkan `.env` ke git.
6. Buka shell XAMPP dan jalankan:
   - `php cbt\bin\pasang.php`
   - `php cbt\bin\pasang.php --contoh` jika ingin mapel dan soal fiktif
   - `php cbt\bin\buat-admin.php`
7. Buka `http://localhost/sim-man3-jember/cbt/public/` untuk siswa, dan `index.php?r=admin/masuk` untuk pengawas.
8. Beri Apache izin menulis `cbt\public\unggah\audio` dan `cbt\storage\log`.
9. Audio besar: naikkan `upload_max_filesize` dan `post_max_size` di `php.ini` (batas aplikasi 8 MB per berkas).

Periksa skema dan penilaian: `php cbt\bin\periksa.php`.

DocumentRoot boleh diarahkan ke `cbt\public`. Tidak wajib. Jangan jadikan folder `cbt` sendiri sebagai document root.

## Token, reset, dan jeda

- Token enam karakter terbit saat pengawas menekan **Mulai ujian**, dan hanya tampil selama status berjalan.
- Reset satu siswa menghapus percobaan siswa itu. Token sesi tidak berubah. Siswa masuk lagi dan melanjutkan sebagai percobaan baru.
- Reset seluruh sesi menghapus semua percobaan. Jika ujian sedang berjalan, token diganti. Jika tidak, sesi kembali ke draf.
- Menutup ujian menyembunyikan token. Ketukan siswa berikutnya mengakhiri percobaannya dan menghitung skor.
- Klien mengetuk server tiap 5 detik. Jika permintaan gagal, waktu di layar berhenti. Jika sambungan terputus lebih dari 15 detik, server tidak menghitung jeda itu. Jawaban yang sudah masuk tidak dihapus. Jawaban yang masih di peramban dikirim saat `lanjut` berhasil.
- Satu soal dimuat sekali. Audio tidak diunduh sebelum diputar.

## Templat soal

Kolom: `nomor`, `tipe` (`pg`, `pgk`, `menjodohkan`), `pertanyaan`, `audio`, `opsi_a`–`opsi_e`, `kunci`, `kiri_1`–`kanan_5`.

- Pilihan ganda: kunci `B`.
- Kompleks: kunci `B,D`.
- Menjodohkan: kunci `1-B;2-A`. Kosong berarti kolom sebaris adalah pasangan yang benar.
- `audio` adalah nama berkas yang sudah diunggah, misalnya `ab12cd34ef56.mp3`.

Berkas Word tidak didukung. Simpan sebagai `.xlsx` atau `.csv`. CSV koma dan titik koma sama-sama diterima. Kunci lama diperbarui lewat templat kunci (`nomor`, `kunci`) tanpa mengulang teks soal.
