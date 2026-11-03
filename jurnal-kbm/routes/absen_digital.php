<?php

use App\Http\Controllers\AbsenDigital\AbsenBerkasController;
use App\Http\Controllers\AbsenDigital\AbsenKegiatanController;
use App\Http\Controllers\AbsenDigital\AbsenKehadiranController;
use App\Http\Controllers\AbsenDigital\AbsenRingkasanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:guru,staff,admin,kepala'])
    ->prefix('absen-digital')
    ->name('absen-digital.')
    ->group(function () {
        Route::get('/kegiatan', [AbsenKegiatanController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}', [AbsenKegiatanController::class, 'show'])->name('kegiatan.show');

        Route::get('/scan', [AbsenKehadiranController::class, 'scan'])->name('scan');
        Route::post('/scan', [AbsenKehadiranController::class, 'scanStore'])->name('scan.store');

        Route::get('/absen', [AbsenKehadiranController::class, 'create'])->name('form');
        Route::post('/absen', [AbsenKehadiranController::class, 'store'])->name('form.store');

        Route::get('/berkas/{jenis}/{id}', [AbsenBerkasController::class, 'show'])
            ->whereIn('jenis', ['foto', 'ttd', 'dokumentasi', 'hasil'])
            ->whereNumber('id')
            ->name('berkas');

        Route::middleware('role:admin')->group(function () {
            Route::get('/kegiatan-baru', [AbsenKegiatanController::class, 'create'])->name('kegiatan.create');
            Route::post('/kegiatan', [AbsenKegiatanController::class, 'store'])->name('kegiatan.store');
            Route::get('/kegiatan/{kegiatan}/ubah', [AbsenKegiatanController::class, 'edit'])->name('kegiatan.edit');
            Route::put('/kegiatan/{kegiatan}', [AbsenKegiatanController::class, 'update'])->name('kegiatan.update');
            Route::delete('/kegiatan/{kegiatan}', [AbsenKegiatanController::class, 'destroy'])->name('kegiatan.destroy');

            Route::post('/kegiatan/{kegiatan}/foto', [AbsenKegiatanController::class, 'fotoStore'])->name('kegiatan.foto.store');
            Route::delete('/kegiatan/{kegiatan}/foto/{foto}', [AbsenKegiatanController::class, 'fotoDestroy'])->name('kegiatan.foto.destroy');

            Route::post('/kegiatan/{kegiatan}/hasil', [AbsenKegiatanController::class, 'hasilStore'])->name('kegiatan.hasil.store');
            Route::delete('/kegiatan/{kegiatan}/hasil-berkas', [AbsenKegiatanController::class, 'hasilFileDestroy'])->name('kegiatan.hasil.file.destroy');

            Route::get('/ringkasan', [AbsenRingkasanController::class, 'index'])->name('ringkasan');
            Route::get('/cetak', [AbsenRingkasanController::class, 'cetakForm'])->name('cetak');
            Route::get('/cetak/halaman', [AbsenRingkasanController::class, 'cetakHalaman'])->name('cetak.halaman');
        });
    });
