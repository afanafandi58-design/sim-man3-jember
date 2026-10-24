# SIM MAN 3 Jember — Struktur Project

Dokumentasi ini menjadi acuan pengembangan dan pemeliharaan aplikasi **SIM MAN 3 Jember** (Sistem Informasi Madrasah). Project mengikuti prinsip **clean architecture** agar mudah di-scale, di-test, dan dipelihara di lingkungan production.

---

## Struktur Folder

```
SIM-MAN3-JEMBER/
├── app/
│   ├── Console/Commands/       # Artisan commands (scheduled job, import CLI, dll.)
│   ├── DTO/                    # Data Transfer Object — struktur data antar layer
│   ├── Http/
│   │   ├── Controllers/        # HTTP handler — tipis, tanpa business logic
│   │   └── Requests/           # Form Request — validasi input HTTP
│   ├── Imports/                # Class import Excel (Maatwebsite Excel)
│   ├── Models/                 # Eloquent Model — representasi tabel database
│   ├── Providers/              # Service provider Laravel
│   ├── Repositories/           # Akses data terpusat (query, CRUD database)
│   ├── Services/               # Business logic aplikasi
│   └── Traits/                 # Reusable behavior antar class
├── config/                     # Konfigurasi aplikasi (adminlte, database, dll.)
├── database/
│   ├── migrations/             # Skema database
│   ├── seeders/                # Data awal
│   └── factories/              # Factory untuk testing
├── docs/                       # Dokumentasi project
├── public/                     # Entry point web & asset publik
├── resources/
│   └── views/                  # Blade template (AdminLTE)
├── routes/
│   ├── web.php                 # Route web aplikasi
│   └── auth.php                # Route autentikasi (Breeze)
├── storage/                    # File upload, cache, log
└── tests/                      # Unit & feature test
```

### Layer Aplikasi

| Layer | Lokasi | Tanggung Jawab |
|-------|--------|----------------|
| **Controller** | `app/Http/Controllers/` | Terima request HTTP, panggil Service, return response/view |
| **Request** | `app/Http/Requests/` | Validasi & sanitasi input dari form/API |
| **Service** | `app/Services/` | Business logic, orchestration, aturan bisnis |
| **Repository** | `app/Repositories/` | Query database, abstraksi akses data |
| **Model** | `app/Models/` | Relasi Eloquent, fillable, cast |
| **Import** | `app/Imports/` | Pembacaan & mapping data Excel |
| **DTO** | `app/DTO/` | Object hasil operasi kompleks (import, laporan, dll.) |
| **Trait** | `app/Traits/` | Method reusable (format tanggal, scope, dll.) |

---

## Konvensi Penamaan

### File & Class

| Jenis | Konvensi | Contoh |
|-------|----------|--------|
| Controller | `{Modul}Controller` | `JadwalController`, `ImportJadwalController` |
| Service | `{Modul}Service` | `ImportJadwalService`, `DashboardService` |
| Repository | `{Modul}Repository` | `GuruRepository`, `JadwalRepository` |
| Model | Singular PascalCase | `Guru`, `Jadwal`, `Jurnal` |
| Import | `{Modul}Import` / `{Modul}SheetImport` | `JadwalSheetImport` |
| DTO | `{Modul}Result` / `{Modul}DTO` | `ImportJadwalResult` |
| Migration | `{timestamp}_{action}_{table}` | `create_jadwals_table` |
| View folder | kebab-case / singular | `jadwal/`, `import-jadwal/` |

### Route

- Resource route menggunakan **plural**: `gurus`, `jadwals`, `jurnals`
- Route name mengikuti resource: `jadwals.index`, `import-jadwal.store`
- Route khusus modul: `{modul}.{action}` — contoh: `guru.jurnal.create`

### Database

- Tabel: **plural snake_case** — `gurus`, `jadwals`, `jurnals`
- Kolom: **snake_case** — `guru_id`, `tahun_pelajaran`, `jam_mulai`
- Foreign key: `{model}_id` — `guru_id`, `jadwal_id`

### Variabel & Method

- Variabel/method PHP: **camelCase** — `$tahunPelajaran`, `getDashboardData()`
- Konstanta: **UPPER_SNAKE_CASE** — `SHEET_NAME`, `VALID_HARI`

---

## Alur Controller → Service → Repository → Model

```
Request HTTP
    │
    ▼
Controller          ← Validasi file/route, delegasi ke Service
    │
    ▼
Service             ← Business logic, aturan bisnis, transaksi
    │
    ▼
Repository          ← Query & persistensi data
    │
    ▼
Model (Eloquent)    ← Relasi & representasi tabel
    │
    ▼
Database (MySQL)
```

### Contoh Alur: Import Jadwal

```
ImportJadwalController::store()
    → validasi file (.xlsx)
    → ImportJadwalService::import($file)
        → baca sheet "Jadwal" via JadwalSheetImport
        → validasi setiap baris (NIP guru, hari, semester, dll.)
        → DB::transaction() → Jadwal::create() per baris valid
        → return ImportJadwalResult
    → redirect ke halaman hasil dengan session
```

### Contoh Alur: CRUD Jadwal (target refactor)

```
JadwalController::store()
    → JadwalStoreRequest (validasi input)
    → JadwalService::create($data)
        → cek bentrok jadwal (business rule)
        → JadwalRepository::create($data)
    → redirect dengan flash message
```

### Prinsip Layer

1. **Controller** hanya: terima input, panggil service, return response.
2. **Service** berisi: aturan bisnis, transaksi, koordinasi antar repository.
3. **Repository** berisi: query Eloquent, filter, pagination — tanpa aturan bisnis.
4. **Model** berisi: relasi, fillable, cast — tanpa query kompleks.

---

## Standar Coding

### PHP & Laravel

- Gunakan **PHP 8.3** dengan type hinting dan return type.
- Ikuti **PSR-12** coding standard.
- Gunakan **constructor injection** untuk Service dan Repository di Controller.
- Validasi input HTTP melalui **Form Request** (`app/Http/Requests/`).
- Operasi multi-table wajib menggunakan **`DB::transaction()`**.
- Pesan error dan UI menggunakan **Bahasa Indonesia**.
- Hindari query Eloquent langsung di Controller — delegasikan ke Repository/Service.

### Blade & Frontend

- View admin menggunakan **`@extends('adminlte::page')`**.
- Flash message: `session('success')`, `session('error')`.
- Form: Bootstrap 4 card pattern (card-header, card-body, card-footer).
- Tabel: `table table-bordered table-striped`.

### Git & Branching (Production)

- Branch utama: `main` atau `master`
- Feature branch: `feature/{nama-fitur}` — contoh: `feature/import-jadwal`
- Bugfix branch: `fix/{deskripsi-singkat}`
- Commit message: imperative, jelas — contoh: `Add import jadwal service and controller`

### Keamanan

- File `.env` **tidak** di-commit (sudah ada di `.gitignore`).
- Route admin dilindungi middleware **`auth`**.
- Validasi file upload: mime type, ukuran maksimal.
- Jangan expose query raw ke view.

---

## Larangan: Business Logic di Controller

Controller **TIDAK BOLEH** berisi:

| Larangan | Alasan | Tempat yang Benar |
|----------|--------|-------------------|
| Query Eloquent kompleks | Sulit di-test & di-reuse | Repository |
| Validasi aturan bisnis | Bukan tanggung jawab HTTP layer | Service |
| Transaksi database | Orchestration logic | Service |
| Parsing/import file | Proses domain | Service + Import class |
| Perhitungan KPI/dashboard | Business logic | Service |
| Cek bentrok jadwal | Aturan bisnis madrasah | Service |

### Controller yang Benar (tipis)

```php
public function store(JadwalStoreRequest $request)
{
    $this->jadwalService->create($request->validated());

    return redirect()
        ->route('jadwals.index')
        ->with('success', 'Jadwal berhasil ditambahkan.');
}
```

### Controller yang Salah (terlalu gemuk)

```php
public function store(Request $request)
{
    $request->validate([...]);

    $cekBentrok = Jadwal::where('guru_id', $request->guru_id)
        ->where('hari', $request->hari)
        ->where(...)
        ->exists();

    if ($cekBentrok) { ... }

    Jadwal::create($request->only([...]));
}
```

> **Catatan:** Beberapa controller lama mungkin masih memuat logic langsung. Saat pengembangan fitur baru, **wajib** mengikuti pola Service/Repository. Refactor controller lama dilakukan bertahap tanpa mengganggu fitur yang sudah berjalan.

---

## Modul Aplikasi

| Modul | Status | Layer Target |
|-------|--------|--------------|
| Login & Auth | Aktif | Breeze + Controller |
| Master Guru | Aktif | Controller → Service → Repository |
| Jadwal | Aktif | Controller → Service → Repository |
| Import Jadwal | Aktif | Controller → Service → Import |
| Jurnal Mengajar | Aktif | Controller → Service → Repository |
| Dashboard Admin | Aktif | Controller → Service |
| Dashboard Guru | Aktif | Controller → Service |
| Mata Pelajaran | Planned | — |
| Kelas | Planned | — |
| Monitoring Kepala Madrasah | Planned | — |
| Laporan PDF | Aktif (partial) | Controller → Service |
| Export Excel | Aktif (partial) | Controller → Service → Import |

---

## Referensi Cepat

- Konfigurasi AdminLTE: `config/adminlte.php`
- Route web: `routes/web.php`
- Dokumentasi umum project: `README.md`
- Dependency utama: Laravel, AdminLTE 3, DomPDF, Maatwebsite Excel

---

*Terakhir diperbarui: Juli 2026 — SIM MAN 3 Jember Production Setup*
