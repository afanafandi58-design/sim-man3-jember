# CBT MAN 3 Jember

Aplikasi ujian semi-online yang berdiri sendiri di folder `cbt/`. Server sekolah adalah sumber kebenaran. Aplikasi ini tidak mengubah fitur SIM lain.

Siswa masuk dengan nama pengguna dan kata sandi, lalu memasukkan token ujian. Layar ujian menampilkan waktu, soal satu per satu, daftar nomor, serta tombol **Kembali**, **Ragu-ragu**, dan **Lanjut**. Jika koneksi putus atau server gagal, waktu siswa itu dijeda dan jawaban yang sudah tersimpan tetap ada. Jawaban yang belum terkirim disimpan di peramban dan dikirim lagi saat tersambung.

## Tipe soal

- Pilihan ganda: satu jawaban. Benar hanya jika pilihan itu tepat.
- Pilihan ganda kompleks: lebih dari satu jawaban. Benar hanya jika himpunan pilihan persis sama dengan kunci.
- Menjodohkan: semua pasangan harus tepat. Butir kanan diacak di layar siswa.
- Kosong jika tidak diisi. Salah jika diisi tetapi tidak tepat. Tidak ada nilai parsial.

Audio menyimak (Bahasa Arab dan Bahasa Inggris) diunggah pengawas, namanya ditulis di kolom `audio`, dan diputar sebagai berkas statis.

## Yang tidak ikut

- Impor Word. Jalurnya Excel (`.xlsx`) dan CSV. Templat bisa diunduh dari halaman impor.
- Peramban ujian terkunci, pengawasan kamera, dan acak soal per siswa.
- Bobot berbeda per soal. Setiap soal bernilai satu.
- Akun, token, atau data siswa. Contoh env tidak berisi kata sandi. Data contoh bersifat fiktif.

Pasang di XAMPP: [PANDUAN-XAMPP.md](PANDUAN-XAMPP.md).
