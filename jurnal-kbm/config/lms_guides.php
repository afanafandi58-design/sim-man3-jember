<?php

/**
 * Tata cara pengisian LMS per halaman & per role (admin, guru, kepala).
 * Ditampilkan lewat view lms.partials.guide — siswa tidak punya akun LMS.
 */
return [
    'pages' => [
        'dashboard' => [
            'title' => 'Tata cara — Dashboard LMS',
            'intro' => 'Ringkasan jumlah materi, tugas, kuis, dan pengumpulan. Siswa mengakses konten lewat link berbagi (tanpa login).',
            'steps' => [
                'common' => [
                    'Pastikan jadwal & data guru/siswa di SIM sudah benar — LMS mengacu kelas, mapel, dan NIS siswa.',
                    'Link Google Drive wajib HTTPS dari domain resmi: drive.google.com, docs.google.com, atau forms.google.com / forms.gle.',
                ],
                'admin' => [
                    'Gunakan angka di kotak statistik untuk memantau aktivitas seluruh guru.',
                    'Jika angka 0 padahal guru sudah input, minta guru cek centang Publikasikan dan jalankan migrate bila perlu.',
                    'Atur kebijakan Drive di menu Pengaturan LMS sebelum guru massal.',
                ],
                'guru' => [
                    'Angka yang tampil hanya untuk kelas/mapel yang Anda ampu (sesuai jadwal & guru_id).',
                    'Mulai dari Materi Pembelajaran → buat materi → salin link di Link Berbagi Siswa.',
                    'Setelah uji link di tab incognito, bagikan ke siswa (WA/kelas), jangan posting di feed publik.',
                ],
                'kepala' => [
                    'Angka menampilkan seluruh sekolah — gunakan untuk cek apakah guru sudah mengisi LMS.',
                    'Detail per guru ada di menu Monitoring LMS.',
                    'Anda tidak perlu membuat materi; fokus pantau dan ingatkan guru yang masih kosong.',
                ],
            ],
        ],
        'materials' => [
            'title' => 'Tata cara — Materi Pembelajaran',
            'intro' => 'Menyimpan link Google Drive materi per kelas & mapel. File tetap di Drive, bukan di server SIM.',
            'steps' => [
                'common' => [
                    'Setiap materi: pilih kelas & mapel (ideal dari jadwal), judul jelas, URL Drive HTTPS, centang Publikasikan agar link siswa aktif.',
                    'Set sharing Drive ke “Siapa saja dengan link” (viewer) atau domain sekolah — siswa tidak login Google wajib.',
                    'Unpublish jika link bocor atau materi perlu diganti.',
                ],
                'admin' => [
                    'Anda dapat menambah materi untuk guru mana pun — pilih jadwal yang valid agar guru_id terisi otomatis.',
                    'Edit/hapus materi guru jika diminta (koordinasi dengan guru terkait).',
                ],
                'guru' => [
                    'Klik Tambah → isi jadwal/kelas/mapel → tempel link Drive slide/PDF/video.',
                    'Setelah simpan, buka Link Berbagi Siswa untuk URL publik materi.',
                    'Hanya edit materi milik Anda; admin dapat membantu jika ada kesalahan.',
                ],
                'kepala' => [
                    'Anda dapat melihat daftar semua materi — tidak perlu menambah kecuali diminta admin.',
                    'Gunakan untuk sampling kualitas materi guru.',
                ],
            ],
        ],
        'materials_form' => [
            'title' => 'Tata cara — Form Materi',
            'intro' => 'Isi form dari atas ke bawah; simpan dulu, uji link publik sebelum dibagikan.',
            'steps' => [
                'common' => [
                    'Jadwal (jika ada): pilih baris jadwal Anda — kelas, mapel, dan tahun pelajaran terisi otomatis.',
                    'Judul: singkat & spesifik (contoh: “Persamaan Linear — Bab 1”).',
                    'URL Drive: buka file di Drive → Share → copy link → tempel di sini (harus https://).',
                    'Publikasikan: aktifkan agar muncul di Link Berbagi; nonaktifkan untuk draft.',
                ],
                'admin' => [
                    'Pastikan jadwal yang dipilih memiliki guru pengampu — itu menentukan kepemilikan data.',
                ],
                'guru' => [
                    'Tanpa jadwal valid, isi kelas & mapel manual sesuai rombel Anda.',
                ],
                'kepala' => [],
            ],
        ],
        'assignments' => [
            'title' => 'Tata cara — Tugas',
            'intro' => 'Tugas berisi instruksi + link Drive; siswa mengirim jawaban lewat halaman publik dengan NIS + link Drive jawaban.',
            'steps' => [
                'common' => [
                    'Buat tugas: kelas/mapel, judul, deskripsi, link petunjuk (Drive), deadline opsional, Publikasikan.',
                    'Bagikan link “Lihat tugas” dan “Kirim jawaban” dari menu Link Berbagi Siswa — bukan link admin /lms.',
                    'NIS harus siswa aktif di database SIM; jawaban juga link Drive HTTPS.',
                ],
                'admin' => [
                    'Dapat membuat tugas atas nama guru via pilihan jadwal.',
                    'Pantau Pengumpulan Tugas seluruh sekolah.',
                ],
                'guru' => [
                    'Setelah deadline, cek Pengumpulan Tugas → beri nilai/catatan lewat Edit.',
                    'Catat manual hanya jika siswa tidak bisa kirim online (jarang).',
                ],
                'kepala' => [
                    'Lihat daftar tugas seluruh guru; arahkan guru yang belum membuat tugas.',
                ],
            ],
        ],
        'assignments_form' => [
            'title' => 'Tata cara — Form Tugas',
            'steps' => [
                'common' => [
                    'Isi kelas/mapel/judul/deskripsi seperti materi.',
                    'Link Drive: dokumen soal atau folder pengumpulan (jelaskan di deskripsi cara siswa kirim).',
                    'Deadline: opsional; kosongkan jika tidak ada batas waktu.',
                ],
                'admin' => [],
                'guru' => [],
                'kepala' => [],
            ],
        ],
        'submissions' => [
            'title' => 'Tata cara — Pengumpulan Tugas',
            'intro' => 'Daftar jawaban siswa yang masuk lewat form publik (NIS + link Drive) atau input manual guru/admin.',
            'steps' => [
                'common' => [
                    'Siswa tidak login SIM — identitas dari NIS yang diketik di halaman kirim.',
                    'Setiap baris: tugas, nama/NIS siswa, waktu kirim, nilai (jika sudah dinilai).',
                ],
                'admin' => [
                    'Lihat semua pengumpulan; edit nilai jika guru minta bantuan teknis.',
                ],
                'guru' => [
                    'Filter mental per tugas/kelas Anda; klik Edit untuk nilai & catatan.',
                    'Jika siswa salah NIS, minta kirim ulang dengan NIS benar atau catat manual.',
                ],
                'kepala' => [
                    'Pantau apakah guru sudah menilai — jumlah pengumpulan vs tugas aktif.',
                ],
            ],
        ],
        'submissions_form' => [
            'title' => 'Tata cara — Catat / Edit Pengumpulan',
            'steps' => [
                'common' => [
                    'Catat manual: pilih tugas & siswa, tempel link Drive jawaban, isi nilai bila perlu.',
                    'Edit: perbarui nilai, catatan guru, atau link jika siswa mengirim revisi.',
                ],
                'admin' => [],
                'guru' => [
                    'Nilai disarankan diisi setelah koreksi agar siswa bisa dilaporkan ke wali/kelas.',
                ],
                'kepala' => [],
            ],
        ],
        'quizzes' => [
            'title' => 'Tata cara — Kuis',
            'intro' => 'Kuis berbasis link (Google Form / Drive) — bukan kuis acak di server.',
            'steps' => [
                'common' => [
                    'Buat Form di Google Forms → set respons → copy link HTTPS → simpan di LMS.',
                    'Bagikan link publik kuis ke siswa (menu Link Berbagi), sama seperti materi.',
                ],
                'admin' => [
                    'Pastikan form tidak meminta login email siswa jika siswa tidak punya akun Google sekolah.',
                ],
                'guru' => [
                    'Judul kuis sejajar materi/pembelajaran; publikasikan hanya saat siap dibuka kelas.',
                ],
                'kepala' => [
                    'Monitoring jumlah kuis per guru lewat Monitoring LMS.',
                ],
            ],
        ],
        'quizzes_form' => [
            'title' => 'Tata cara — Form Kuis',
            'steps' => [
                'common' => [
                    'URL wajib forms.google.com, forms.gle, atau Drive/Docs sesuai validasi sistem.',
                    'Centang Publikasikan saat form sudah diuji sendiri.',
                ],
                'admin' => [],
                'guru' => [],
                'kepala' => [],
            ],
        ],
        'announcements' => [
            'title' => 'Tata cara — Pengumuman',
            'intro' => 'Pengumuman tampil di Dashboard LMS (daftar terbaru) untuk admin/guru/kepala.',
            'steps' => [
                'common' => [
                    'Isi judul, isi pengumuman, tanggal terbit, status aktif.',
                    'Gunakan untuk info libur, deadline kolektif, atau instruksi sekolah.',
                ],
                'admin' => [
                    'Anda dapat mengumumkan ke seluruh sekolah atau scope kelas (sesuai form).',
                ],
                'guru' => [
                    'Buat pengumuman untuk kelas/mapel Anda; hindari duplikasi dengan pengumuman admin.',
                ],
                'kepala' => [
                    'Anda dapat melihat semua pengumuman; buat jika perlu instruksi dari pimpinan.',
                ],
            ],
        ],
        'announcements_form' => [
            'title' => 'Tata cara — Form Pengumuman',
            'steps' => [
                'common' => [
                    'Judul singkat; isi jelas tanpa data pribadi siswa.',
                    'Nonaktifkan pengumuman lama agar dashboard tidak penuh informasi usang.',
                ],
                'admin' => [],
                'guru' => [],
                'kepala' => [],
            ],
        ],
        'my_classes' => [
            'title' => 'Tata cara — Kelas & Jadwal Saya',
            'intro' => 'Daftar jadwal mengajar Anda dari data SIM — acuan kelas/mapel saat membuat materi/tugas.',
            'steps' => [
                'common' => [
                    'Jika jadwal kosong, hubungi admin: perbaiki data jadwal/guru_id di SIM.',
                    'Saat buat materi/tugas, pilih jadwal yang sama agar konsisten.',
                ],
                'admin' => [
                    'Menu ini untuk guru; admin memakai master Jadwal di menu utama SIM.',
                ],
                'guru' => [
                    'Cocokkan hari/jam dengan jadwal di sekolah; gunakan sebagai checklist rombel yang wajib diisi LMS.',
                ],
                'kepala' => [
                    'Anda tidak melihat menu ini (khusus guru/admin pengampu).',
                ],
            ],
        ],
        'share' => [
            'title' => 'Tata cara — Link Berbagi Siswa',
            'intro' => 'URL publik tanpa login — satu-satunya cara siswa akses materi/tugas/kuis.',
            'steps' => [
                'common' => [
                    'Salin URL materi/tugas/kuis → kirim ke siswa (grup kelas/WA), jangan unggah di media sosial publik.',
                    'Untuk tugas ada dua link: Lihat (baca soal) dan Kirim (NIS + link jawaban).',
                    'Uji setiap link di tab incognito sebelum disebar.',
                ],
                'admin' => [
                    'Anda melihat link semua guru — hanya bagikan ke siswa jika koordinasi dengan guru pengampu.',
                ],
                'guru' => [
                    'Hanya link materi/tugas/kuis Anda yang muncul di daftar ini.',
                    'Jika link tidak muncul, cek Publikasikan di form edit.',
                ],
                'kepala' => [
                    'Menu ini khusus guru/admin pengampu — kepala pantau lewat Monitoring.',
                ],
            ],
        ],
        'monitoring' => [
            'title' => 'Tata cara — Monitoring LMS',
            'intro' => 'Rekap jumlah materi, tugas, kuis, dan pengumpulan per guru.',
            'steps' => [
                'common' => [
                    'Angka 0 berarti guru belum input atau belum publikasi.',
                    'Gunakan untuk rapat evaluasi KBM daring, bukan sebagai satu-satunya penilaian kinerja.',
                ],
                'admin' => [
                    'Follow-up guru dengan angka rendah; pastikan jadwal & akun guru_id benar.',
                ],
                'guru' => [],
                'kepala' => [
                    'Prioritaskan guru dengan jadwal hari ini tapi materi/tugas masih 0.',
                    'Koordinasi dengan admin SIM jika ada guru yang tidak muncul di tabel.',
                ],
            ],
        ],
        'settings' => [
            'title' => 'Tata cara — Pengaturan LMS',
            'intro' => 'Kebijakan Drive & teks bantuan untuk guru (admin saja).',
            'steps' => [
                'common' => [],
                'admin' => [
                    'Folder Drive induk: opsional, untuk dokumentasi struktur folder sekolah.',
                    'Instruksi untuk guru: tulis SOP sekolah (contoh: naming file, hak akses Drive).',
                    'Wajib link manual: biarkan aktif kecuali OAuth Google sudah dikonfigurasi di .env server.',
                    'Setelah ubah, minta satu guru uji buat materi + link berbagi.',
                ],
                'guru' => [],
                'kepala' => [],
            ],
        ],
    ],
];
