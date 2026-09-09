<?php

use App\Http\Controllers\Api\CounselingSessionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentViolationController;
use App\Http\Controllers\Api\ViolationTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardPageController;
use App\Http\Controllers\DashboardModuleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardPageController::class)->name('dashboard');
    Route::get('/dashboard/{module}', [DashboardModuleController::class, 'show'])
        ->whereIn('module', ['students', 'violations', 'agenda', 'assessments', 'reports', 'settings'])
        ->name('dashboard.module');
    Route::get('/dashboard/search', [DashboardModuleController::class, 'search'])->name('dashboard.search');
    Route::post('/dashboard/violations', [DashboardModuleController::class, 'storeViolation'])->name('dashboard.violation.store')->middleware('permission:input pelanggaran');
    Route::get('/dashboard/students/create', [DashboardModuleController::class, 'createStudent'])->name('dashboard.student.create');
    Route::post('/dashboard/students', [DashboardModuleController::class, 'storeStudent'])->name('dashboard.student.store');
    Route::get('/dashboard/students/{student}', [DashboardModuleController::class, 'student'])->name('dashboard.student');
    Route::get('/dashboard/students/{student}/export', [DashboardModuleController::class, 'exportStudent'])->name('dashboard.student.export');
    Route::get('/dashboard/export/violations', [DashboardModuleController::class, 'exportViolations'])->name('dashboard.export.violations');
});

/*
|--------------------------------------------------------------------------
| API Routes — Sistem Informasi BK SMA Mardisiswa
|--------------------------------------------------------------------------
| Semua route di bawah ini wajib login (Sanctum) & role tertentu.
| Role yang dipakai (lihat RoleSeeder): admin, guru_bk, guru_piket, wali_kelas.
*/

Route::middleware('auth:sanctum')->group(function () {

    // ---- Dashboard ----------------------------------------------------
    Route::prefix('dashboard')
        ->middleware('role:admin|guru_bk|wali_kelas')
        ->group(function () {
            Route::get('/summary', [DashboardController::class, 'summary']);
            Route::get('/attendance-trend', [DashboardController::class, 'attendanceTrend']);
            Route::get('/students-needing-attention', [DashboardController::class, 'studentsNeedingAttention']);
        });

    // ---- Modul Input & Pencatatan Pelanggaran --------------------------
    // Hanya Guru BK, Guru Piket, dan Admin yang boleh mencatat pelanggaran.
    Route::prefix('violations')
        ->middleware('role:admin|guru_bk|guru_piket')
        ->group(function () {
            Route::get('/', [StudentViolationController::class, 'index']);
            Route::get('/lookup-student', [StudentViolationController::class, 'lookupStudent']);
            Route::post('/', [StudentViolationController::class, 'store'])
                ->middleware('permission:input pelanggaran');
            Route::get('/{studentViolation}', [StudentViolationController::class, 'show']);
            Route::delete('/{studentViolation}', [StudentViolationController::class, 'destroy'])
                ->middleware('role:admin');
        });

    // ---- Master data jenis pelanggaran (read: semua; write: admin) ----
    Route::prefix('violation-types')->group(function () {
        Route::get('/', [ViolationTypeController::class, 'index']);
        Route::middleware('role:admin')->group(function () {
            Route::post('/', [ViolationTypeController::class, 'store']);
            Route::put('/{violationType}', [ViolationTypeController::class, 'update']);
            Route::delete('/{violationType}', [ViolationTypeController::class, 'destroy']);
        });
    });

    // ---- Buku Induk Siswa ----------------------------------------------
    Route::prefix('students')
        ->middleware('role:admin|guru_bk|wali_kelas|guru_piket')
        ->group(function () {
            Route::get('/', [StudentController::class, 'index']);
            Route::get('/{student}', [StudentController::class, 'show']); // profil + relasi lengkap
            Route::middleware('role:admin')->group(function () {
                Route::post('/', [StudentController::class, 'store']);
                Route::put('/{student}', [StudentController::class, 'update']);
            });
        });

    // ---- Sesi Konseling & Home Visit ------------------------------------
    Route::prefix('counseling-sessions')
        ->middleware('role:admin|guru_bk')
        ->group(function () {
            Route::get('/', [CounselingSessionController::class, 'index']);
            Route::post('/', [CounselingSessionController::class, 'store']);
            Route::put('/{counselingSession}', [CounselingSessionController::class, 'update']);
        });
});
