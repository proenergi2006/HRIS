<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Whistleblower\PublicWhistleblowerController;
use App\Http\Controllers\Whistleblower\WhistleblowerController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Appraisal\LevelController;
use App\Http\Controllers\Appraisal\EmployeeController;
use App\Http\Controllers\Appraisal\TemplateController;
use App\Http\Controllers\Appraisal\PeriodController;
use App\Http\Controllers\Appraisal\AppraisalController;
use App\Http\Controllers\Appraisal\ApprovalController;
use App\Http\Controllers\Appraisal\FlowConfigController;
use App\Http\Controllers\Appraisal\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Whistleblower — public (no auth) ──────────────────────────────────
Route::get('/whistleblower',                  [PublicWhistleblowerController::class, 'show'])->name('whistleblower.form');
Route::post('/whistleblower',                 [PublicWhistleblowerController::class, 'store'])->name('whistleblower.store')->middleware('throttle:3,60');
Route::get('/whistleblower/success/{ticket}', [PublicWhistleblowerController::class, 'success'])->name('whistleblower.success');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/home', fn() => redirect()->route('dashboard'));

Auth::routes();

// ── Semua user yang sudah login ────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // === MODUL PENILAIAN KINERJA — akses semua user ===
    Route::prefix('appraisal')->name('appraisal.')->group(function () {

        // Form penilaian (evaluator buat, semua bisa lihat)
        Route::resource('appraisals', AppraisalController::class);
        Route::get('appraisals/{appraisal}/pdf',     [AppraisalController::class, 'pdf'])->name('appraisals.pdf');
        Route::post('appraisals/{appraisal}/submit',  [ApprovalController::class, 'submit'])->name('appraisals.submit');
        Route::post('appraisals/{appraisal}/approve', [ApprovalController::class, 'approve'])->name('appraisals.approve');
        Route::post('appraisals/{appraisal}/reject',  [ApprovalController::class, 'reject'])->name('appraisals.reject');

        // Laporan (semua user bisa lihat)
        Route::get('report',        [ReportController::class, 'index'])->name('report.index');
        Route::get('report/export', [ReportController::class, 'export'])->name('report.export');
    });
});

// ── Whistleblower admin — auth only ───────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin/whistleblower')->name('whistleblower.admin.')->group(function () {
    Route::get('/',                                      [WhistleblowerController::class, 'index'])->name('index');
    Route::get('/qrcode',                               [WhistleblowerController::class, 'qrcode'])->name('qrcode');
    Route::get('/{report}',                             [WhistleblowerController::class, 'show'])->name('show');
    Route::patch('/{report}/status',                    [WhistleblowerController::class, 'updateStatus'])->name('update-status');
    Route::get('/{report}/download',                    [WhistleblowerController::class, 'download'])->name('download');
});

// ── Admin HRD only ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Manajemen user sistem
    Route::get('/users',           [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create',    [UserController::class, 'create'])->name('user.create');
    Route::post('/users/create',   [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}',    [UserController::class, 'edit'])->name('user.edit');
    Route::patch('/users/{user}',  [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');

    // Master data & konfigurasi — admin HRD only
    Route::prefix('appraisal')->name('appraisal.')->group(function () {
        Route::resource('levels',       LevelController::class);
        Route::resource('employees',    EmployeeController::class);
        Route::resource('templates',    TemplateController::class);
        Route::resource('periods',      PeriodController::class);
        Route::patch('periods/{period}/toggle', [PeriodController::class, 'toggle'])->name('periods.toggle');
        Route::resource('flow-configs', FlowConfigController::class);
    });
});
