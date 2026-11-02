<?php

use App\Http\Controllers\Lms\LmsAnnouncementController;
use App\Http\Controllers\Lms\LmsAssignmentController;
use App\Http\Controllers\Lms\LmsDashboardController;
use App\Http\Controllers\Lms\LmsMaterialController;
use App\Http\Controllers\Lms\LmsMonitoringController;
use App\Http\Controllers\Lms\LmsMyClassController;
use App\Http\Controllers\Lms\LmsPublicController;
use App\Http\Controllers\Lms\LmsQuizController;
use App\Http\Controllers\Lms\LmsSettingController;
use App\Http\Controllers\Lms\LmsShareLinksController;
use App\Http\Controllers\Lms\LmsSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:90,1'])->prefix('lms')->name('lms.')->group(function () {
    Route::get('/materi/{token}', [LmsPublicController::class, 'showMaterial'])->name('public.material');
    Route::get('/tugas/{token}', [LmsPublicController::class, 'showAssignment'])->name('public.assignment');
    Route::get('/kuis/{token}', [LmsPublicController::class, 'showQuiz'])->name('public.quiz');
    Route::get('/tugas/{token}/kirim', [LmsPublicController::class, 'submitAssignmentForm'])->name('public.submit.form');
    Route::post('/tugas/{token}/kirim', [LmsPublicController::class, 'submitAssignmentStore'])->name('public.submit.store');
});

Route::middleware(['auth', 'throttle:120,1'])->prefix('lms')->name('lms.')->group(function () {
    Route::middleware('role:admin,guru,kepala')->group(function () {
        Route::get('/', [LmsDashboardController::class, 'index'])->name('dashboard');
        Route::get('/materi', [LmsMaterialController::class, 'index'])->name('materials.index');
        Route::get('/tugas', [LmsAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/pengumpulan', [LmsSubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/kuis', [LmsQuizController::class, 'index'])->name('quizzes.index');
        Route::get('/pengumuman', [LmsAnnouncementController::class, 'index'])->name('announcements.index');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/kelas-saya', [LmsMyClassController::class, 'index'])->name('my-classes.index');
        Route::get('/link-berbagi', [LmsShareLinksController::class, 'index'])->name('share.index');

        Route::resource('materials', LmsMaterialController::class)->except(['index', 'show']);
        Route::resource('assignments', LmsAssignmentController::class)->except(['index', 'show']);
        Route::resource('quizzes', LmsQuizController::class)->except(['index', 'show']);
        Route::resource('submissions', LmsSubmissionController::class)->except(['index', 'show']);
        Route::resource('announcements', LmsAnnouncementController::class)->except(['index', 'show']);
    });

    Route::middleware('role:admin,kepala')->group(function () {
        Route::get('/monitoring', [LmsMonitoringController::class, 'index'])->name('monitoring.index');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/pengaturan', [LmsSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [LmsSettingController::class, 'update'])->name('settings.update');
    });
});
