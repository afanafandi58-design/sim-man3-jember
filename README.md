# SIM MAN 3 Jember

Repository Sistem Informasi / modernisasi tampilan MAN 3 Jember.

## Isi repo

| Path | Keterangan |
|------|------------|
| `web/` | Prototype Vite + React (preview lokal) |
| `wordpress-theme/man3-hijau-dingin/` | Tema WordPress lengkap (hijau dingin) |
| `releases/man3-hijau-dingin.zip` | **Zip siap upload** ke Appearance → Themes |

## Upload ke man3jember.sch.id

1. Ambil `releases/man3-hijau-dingin.zip` (~1.5 MB)
2. wp-admin → **Appearance → Themes → Add New → Upload Theme** → Install → Activate
3. Detail langkah: lihat `docs` di project store / catatan PR

Belum login otomatis — tunggu kredensial dari user.

## Preview lokal (React)

```bash
cd web && npm install && npm run build && npm run preview
```

Dibuat oleh Ahmad Afandi / modernisasi visual agen Cursor.
