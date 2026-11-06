-- Data fiktif untuk latihan. Tidak ada akun, kata sandi, atau token.

INSERT INTO mapel (kode, nama, aktif) VALUES
('MAT', 'Matematika', 1),
('BIN', 'Bahasa Indonesia', 1),
('BIG', 'Bahasa Inggris', 1),
('BAR', 'Bahasa Arab', 1)
ON DUPLICATE KEY UPDATE nama = VALUES(nama), aktif = VALUES(aktif);
