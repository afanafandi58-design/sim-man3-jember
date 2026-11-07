# SIM MAN 3 Jember

**Sistem Informasi Madrasah** — MAN 3 Jember

Aplikasi web untuk mengelola data guru, jadwal mengajar, jurnal KBM, monitoring kegiatan belajar mengajar, dan pelaporan di Madrasah Aliyah Negeri 3 Jember.

---

## Tech Stack

| Komponen | Versi / Package |
|----------|-----------------|
| **Framework** | Laravel 12 |
| **Admin Panel** | AdminLTE 3 (`jeroennoten/laravel-adminlte`) |
| **PHP** | 8.3 |
| **Database** | MySQL |
| **Auth** | Laravel Breeze |
| **PDF** | DomPDF (`barryvdh/laravel-dompdf`) |
| **Excel** | Maatwebsite Excel (`maatwebsite/excel`) |
| **Frontend Build** | Vite + Tailwind CSS (auth pages) |

---

## Modul yang Dikembangkan

| Modul | Deskripsi |
|-------|-----------|
| **Login** | Autentikasi multi-role (admin, guru, kepala madrasah) |
| **Master Guru** | CRUD data guru (NIP, nama, mata pelajaran) |
| **Mata Pelajaran** | Manajemen data mata pelajaran |
| **Kelas** | Manajemen data kelas |
| **Jadwal** | CRUD jadwal mengajar per guru |
| **Jurnal Mengajar** | Pencatatan KBM harian (materi, kehadiran, status) |
| **Monitoring Kepala Madrasah** | Dashboard monitoring aktivitas guru & KBM |
| **Laporan PDF** | Export jurnal dan laporan ke PDF |
| **Export Excel** | Import/export data via Excel |

---

## Struktur Project

Project mengikuti **clean architecture** dengan pemisahan layer:

```
Controller → Service → Repository → Model
```

Detail lengkap: [`docs/PROJECT_STRUCTURE.md`](docs/PROJECT_STRUCTURE.md)

---

## Persyaratan Sistem

- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL 8.x
- Ekstensi PHP: `openssl`, `pdo_mysql`, `mbstring`, `zip`, `gd`, `xml`

---

## Instalasi (Development)

```bash
# Clone repository
git clone <repository-url> SIM-MAN3-JEMBER
cd SIM-MAN3-JEMBER

# Install dependency PHP
composer install

# Install dependency frontend
npm install

# Salin environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=sim_man3_jember
# DB_USERNAME=root
# DB_PASSWORD=

# Jalankan migration
php artisan migrate

# (Opsional) Seed data awal
php artisan db:seed

# Build asset frontend
npm run build

# Jalankan server development
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

---

## Instalasi (Production)

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

Pastikan:
- `APP_ENV=production`
- `APP_DEBUG=false`
- Document root mengarah ke folder `public/`
- Permission `storage/` dan `bootstrap/cache/` writable

---

## Akun Default (Development)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@man3jember.sch.id | admin123 |

> Ganti password default sebelum deploy production.

---

## Struktur Folder Utama

```
app/
├── Http/Controllers/    # HTTP handlers
├── Http/Requests/       # Form validation
├── Services/            # Business logic
├── Repositories/        # Data access layer
├── Imports/             # Excel import classes
├── Models/              # Eloquent models
├── DTO/                 # Data transfer objects
└── Traits/              # Reusable traits

config/adminlte.php      # Sidebar menu & theme
docs/                    # Project documentation
resources/views/         # Blade templates
routes/web.php           # Web routes
```

---

## Git Workflow

```bash
# Feature branch
git checkout -b feature/nama-fitur

# Commit perubahan
git add .
git commit -m "Add: deskripsi perubahan"

# Push & pull request
git push origin feature/nama-fitur
```

---

## Lisensi

Project internal — MAN 3 Jember.

---

## Kontak

**MAN 3 Jember**  
Madrasah Aliyah Negeri 3 Jember, Jawa Timur
