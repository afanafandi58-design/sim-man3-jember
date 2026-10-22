# SIM MAN 3 Jember

Repository Sistem Informasi / frontend modernisasi tampilan MAN 3 Jember.

## Gap penting

Situs live [man3jember.sch.id](https://man3jember.sch.id/) adalah **WordPress** (tema `akademi`). Repo ini **bukan** tema WordPress tersebut — sebelumnya hampir kosong (README/LICENSE saja).

Yang ada di repo sekarang: prototype frontend modern (Vite + React) di folder `web/`, dengan konten publik dari situs live (brand, sambutan, kutipan, berita, menu, tautan). Deploy ke `.sch.id` memerlukan akses hosting/FTP/tema WP atau hosting terpisah.

## Menjalankan preview

```bash
cd web
npm install
npm run dev
```

Build produksi:

```bash
cd web
npm run build
npm run preview
```

Dibuat oleh Ahmad Afandi / modernisasi visual agen Cursor.
