<?php

use App\Http\Controllers\AbsensiModulController;
use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruAccountController;
use App\Http\Controllers\GuruBebanMengajarController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruDashboardController;
use App\Http\Controllers\GuruJurnalAttachmentController;
use App\Http\Controllers\GuruJurnalController;
use App\Http\Controllers\GuruJadwalSayaController;
use App\Http\Controllers\GuruPdfController;
use App\Http\Controllers\ImportTemplateController;
use App\Http\Controllers\ImportDataController;
use App\Http\Controllers\ImportGuruController;
use App\Http\Controllers\ImportJadwalController;
use App\Http\Controllers\ImportSiswaController;
use App\Http\Controllers\JamPelajaranController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JadwalPerbaikanController;
use App\Models\JadwalPerbaikanRequest;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\MataPelajaranUsulanController;
use App\Http\Controllers\PolaJadwalController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\KepalaDashboardController;
use App\Http\Controllers\LaporanAbsensiController;
use App\Http\Controllers\LaporanPdfController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::redirect('/home', '/dashboard');

Route::bind('jadwalPerbaikan', fn (string $value) => JadwalPerbaikanRequest::findOrFail($value));
Route::bind('mataPelajaranUsulan', fn (string $value) => \App\Models\MataPelajaranUsulan::findOrFail($value));

Route::middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/guru/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/guru/jadwal-saya', [GuruJadwalSayaController::class, 'index'])->name('guru.jadwal-saya.index');
    Route::get('/guru/jadwal-saya/create', [GuruJadwalSayaController::class, 'create'])->name('guru.jadwal-saya.create');
    Route::post('/guru/jadwal-saya', [GuruJadwalSayaController::class, 'store'])->name('guru.jadwal-saya.store');
    Route::get('/guru/jadwal-saya/{jadwal}/perbaikan', [GuruJadwalSayaController::class, 'perbaikanForm'])->name('guru.jadwal-saya.perbaikan');
    Route::post('/guru/jadwal-saya/{jadwal}/perbaikan', [GuruJadwalSayaController::class, 'perbaikanStore'])->name('guru.jadwal-saya.perbaikan.store');
    Route::get('/guru/jurnal/{jadwal}/create', [GuruJurnalController::class, 'create'])->name('guru.jurnal.create');
    Route::post('/guru/jurnal/store', [GuruJurnalController::class, 'store'])->name('guru.jurnal.store');
    Route::get('/guru/jurnal/{jurnal}/edit', [GuruJurnalController::class, 'edit'])->name('guru.jurnal.edit');
    Route::put('/guru/jurnal/{jurnal}', [GuruJurnalController::class, 'update'])->name('guru.jurnal.update');
    Route::post('/guru/jurnal/{jurnal}/lampiran', [GuruJurnalAttachmentController::class, 'store'])->name('guru.jurnal.lampiran.store');
    Route::delete('/guru/jurnal/{jurnal}/lampiran/{attachment}', [GuruJurnalAttachmentController::class, 'destroy'])->name('guru.jurnal.lampiran.destroy');
    Route::get('/guru/jurnal/{jurnal}/pdf', [GuruPdfController::class, 'jurnal'])->name('guru.jurnal.pdf');
    Route::get('/guru/data-siswa', [SiswaController::class, 'index'])->name('guru.siswas.index');
    Route::get('/guru/data-siswa/{siswa}', [SiswaController::class, 'show'])->name('guru.siswas.show');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('gurus', GuruController::class);
    Route::resource('jadwals', JadwalController::class);
    Route::resource('jurnals', JurnalController::class);

    Route::get('siswas/export', [SiswaController::class, 'export'])->name('siswas.export');
    Route::resource('siswas', SiswaController::class);

    Route::get('/import-data', [ImportDataController::class, 'index'])->name('import-data.index');

    Route::get('/import-guru/template', [ImportTemplateController::class, 'guru'])->name('import-guru.template');
    Route::get('/import-guru', [ImportGuruController::class, 'index'])->name('import-guru.index');
    Route::post('/import-guru/preview', [ImportGuruController::class, 'preview'])->name('import-guru.preview');
    Route::post('/import-guru', [ImportGuruController::class, 'store'])->name('import-guru.store');
    Route::get('/import-guru/result', [ImportGuruController::class, 'result'])->name('import-guru.result');

    Route::get('/import-jadwal/template', [ImportTemplateController::class, 'jadwal'])->name('import-jadwal.template');
    Route::get('/import-jadwal', [ImportJadwalController::class, 'index'])->name('import-jadwal.index');
    Route::post('/import-jadwal/preview', [ImportJadwalController::class, 'preview'])->name('import-jadwal.preview');
    Route::post('/import-jadwal', [ImportJadwalController::class, 'store'])->name('import-jadwal.store');
    Route::get('/import-jadwal/result', [ImportJadwalController::class, 'result'])->name('import-jadwal.result');

    Route::get('/jadwal-perbaikan', [JadwalPerbaikanController::class, 'index'])->name('jadwal-perbaikan.index');
    Route::post('/jadwal-perbaikan/{jadwalPerbaikan}/approve', [JadwalPerbaikanController::class, 'approve'])->name('jadwal-perbaikan.approve');
    Route::post('/jadwal-perbaikan/{jadwalPerbaikan}/reject', [JadwalPerbaikanController::class, 'reject'])->name('jadwal-perbaikan.reject');
    Route::get('/jadwal-perbaikan/{jadwalPerbaikan}/edit', [JadwalPerbaikanController::class, 'edit'])->name('jadwal-perbaikan.edit');
    Route::put('/jadwal-perbaikan/{jadwalPerbaikan}', [JadwalPerbaikanController::class, 'update'])->name('jadwal-perbaikan.update');

    Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['show']);
    Route::get('/mata-pelajaran-usulan', [MataPelajaranUsulanController::class, 'index'])->name('mata-pelajaran-usulan.index');
    Route::post('/mata-pelajaran-usulan/{mataPelajaranUsulan}/approve', [MataPelajaranUsulanController::class, 'approve'])->name('mata-pelajaran-usulan.approve');
    Route::post('/mata-pelajaran-usulan/{mataPelajaranUsulan}/reject', [MataPelajaranUsulanController::class, 'reject'])->name('mata-pelajaran-usulan.reject');
    Route::resource('jam-pelajaran', JamPelajaranController::class)->except(['show']);
    Route::resource('pola-jadwal', PolaJadwalController::class)->except(['show', 'destroy']);

    Route::get('/guru-accounts', [GuruAccountController::class, 'index'])->name('guru-accounts.index');
    Route::post('/guru-accounts/{guru}/create', [GuruAccountController::class, 'store'])->name('guru-accounts.store');
    Route::post('/guru-accounts/{user}/reset-password', [GuruAccountController::class, 'resetPassword'])->name('guru-accounts.reset-password');
    Route::post('/guru-accounts/{user}/deactivate', [GuruAccountController::class, 'deactivate'])->name('guru-accounts.deactivate');
    Route::post('/guru-accounts/{user}/activate', [GuruAccountController::class, 'activate'])->name('guru-accounts.activate');
    Route::get('/guru-accounts/cetak', [GuruAccountController::class, 'cetak'])->name('guru-accounts.cetak');

    Route::get('/guru-beban-mengajar', [GuruBebanMengajarController::class, 'index'])->name('guru-beban-mengajar.index');

    Route::get('/import-siswa/template/kode-kelas', [ImportTemplateController::class, 'kelas'])->name('import-siswa.template.kode-kelas');
    Route::get('/import-siswa/template/siswa', [ImportTemplateController::class, 'siswa'])->name('import-siswa.template.siswa');
    Route::get('/import-siswa', [ImportSiswaController::class, 'index'])->name('import-siswa.index');
    Route::post('/import-siswa/kode-kelas/preview', [ImportSiswaController::class, 'previewKodeKelas'])->name('import-siswa.kode-kelas.preview');
    Route::post('/import-siswa/kode-kelas', [ImportSiswaController::class, 'storeKodeKelas'])->name('import-siswa.kode-kelas');
    Route::post('/import-siswa/siswa/preview', [ImportSiswaController::class, 'previewSiswa'])->name('import-siswa.siswa.preview');
    Route::post('/import-siswa/siswa', [ImportSiswaController::class, 'storeSiswa'])->name('import-siswa.siswa');

    Route::get('/absensi-siswa', [AbsensiSiswaController::class, 'index'])->name('absensi-siswa.index');
});

Route::middleware(['auth', 'role:kepala,admin'])->group(function () {
    Route::get('/kepala/dashboard', [KepalaDashboardController::class, 'index'])->name('kepala.dashboard');
    Route::get('/kepala/dashboard/{type}', [KepalaDashboardController::class, 'detail'])->name('kepala.dashboard.detail');

    Route::get('/laporan', [LaporanPdfController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/cetak', [LaporanPdfController::class, 'cetak'])->name('laporan.cetak');

    Route::get('/laporan/absensi', [LaporanAbsensiController::class, 'index'])->name('laporan.absensi.index');
    Route::post('/laporan/absensi/cetak', [LaporanAbsensiController::class, 'cetak'])->name('laporan.absensi.cetak');

    Route::get('/kepala/data-siswa', [SiswaController::class, 'index'])->name('kepala.siswas.index');
    Route::get('/kepala/data-siswa/{siswa}', [SiswaController::class, 'show'])->name('kepala.siswas.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Titik F — Modul Absensi Harian (terhubung jurnal; guru/staff/admin/kepala)
Route::middleware(['auth', 'role:guru,staff,admin,kepala'])->prefix('absensi-modul')->name('absensi-modul.')->group(function () {
    Route::get('/input', [AbsensiModulController::class, 'inputForm'])->name('input');
    Route::post('/input', [AbsensiModulController::class, 'inputStore'])->name('input.store');
    Route::get('/hari-efektif', [AbsensiModulController::class, 'hariEfektif'])->name('hari-efektif');
    Route::get('/wali-kelas', [AbsensiModulController::class, 'waliKelas'])->name('wali');
    Route::get('/laporan', [AbsensiModulController::class, 'laporan'])->name('laporan');
    Route::get('/bukti/{absensiHarian}', [AbsensiModulController::class, 'bukti'])->name('bukti');
});

Route::middleware(['auth', 'role:admin'])->prefix('absensi-modul')->name('absensi-modul.')->group(function () {
    Route::get('/wali-kelas/kelola', [AbsensiModulController::class, 'waliManage'])->name('wali.manage');
    Route::post('/wali-kelas/kelola', [AbsensiModulController::class, 'waliStore'])->name('wali.store');
    Route::delete('/wali-kelas/kelola/{waliKelas}', [AbsensiModulController::class, 'waliDestroy'])->name('wali.destroy');
    Route::get('/staff', [AbsensiModulController::class, 'staffManage'])->name('staff.manage');
    Route::post('/staff', [AbsensiModulController::class, 'staffStore'])->name('staff.store');
    Route::post('/staff/{user}/reset', [AbsensiModulController::class, 'staffReset'])->name('staff.reset');
    Route::post('/staff/{user}/toggle', [AbsensiModulController::class, 'staffToggle'])->name('staff.toggle');
});

require __DIR__.'/auth.php';
