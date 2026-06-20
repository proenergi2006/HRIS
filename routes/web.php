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
use App\Http\Controllers\GA\PublicVehicleController;
use App\Http\Controllers\GA\GaVehicleController;
use App\Http\Controllers\GA\GaUsageController;
use App\Http\Controllers\GA\PublicRoomController;
use App\Http\Controllers\GA\GaRoomController;
use App\Http\Controllers\GA\GaCleaningLogController;
use App\Http\Controllers\Reimbursement\ReimbursementController;
use App\Http\Controllers\Reimbursement\ReimbursementAdminController;
use App\Http\Controllers\Reimbursement\ReimbursementBalanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── GA Kendaraan — public (QR scan, no auth) ──────────────────────────
Route::get('/ga/kendaraan/{vehicle}',          [PublicVehicleController::class, 'scan'])->name('ga.scan');
Route::post('/ga/kendaraan/{vehicle}/checkin', [PublicVehicleController::class, 'checkin'])->name('ga.checkin')->middleware('throttle:30,60');
Route::post('/ga/kendaraan/{vehicle}/checkout',[PublicVehicleController::class, 'checkout'])->name('ga.checkout')->middleware('throttle:30,60');

// ── GA Ruang Meeting — public (QR scan, no auth) ──────────────────────
Route::get('/ga/ruangan/{room}',         [PublicRoomController::class, 'scan'])->name('ga.room.scan');
Route::post('/ga/ruangan/{room}/submit', [PublicRoomController::class, 'submit'])->name('ga.room.submit')->middleware('throttle:30,60');
Route::get('/ga/ruangan/{room}/sukses',  [PublicRoomController::class, 'success'])->name('ga.room.success');

// ── Whistleblower — public (no auth) ──────────────────────────────────
Route::get('/whistleblower',                  [PublicWhistleblowerController::class, 'show'])->name('whistleblower.form');
Route::post('/whistleblower',                 [PublicWhistleblowerController::class, 'store'])->name('whistleblower.store')->middleware('throttle:20,60');
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

// ── GA Admin — admin_ga & admin ───────────────────────────────────────
Route::middleware(['auth', 'role:admin_ga|admin'])->prefix('admin/ga')->name('ga.admin.')->group(function () {
    // Kendaraan
    Route::resource('vehicles', GaVehicleController::class)->except(['show']);
    Route::get('vehicles/{vehicle}/qrcode', [GaVehicleController::class, 'qrcode'])->name('vehicles.qrcode');
    Route::get('usages',                    [GaUsageController::class, 'index'])->name('usages.index');
    Route::get('usages/{usage}',            [GaUsageController::class, 'show'])->name('usages.show');
    Route::get('usages/{usage}/photo/{side}',[GaUsageController::class, 'photo'])->name('usages.photo');

    // Ruang Meeting
    Route::resource('rooms', GaRoomController::class)->except(['show']);
    Route::get('rooms/{room}/qrcode', [GaRoomController::class, 'qrcode'])->name('rooms.qrcode');
    Route::post('rooms/{room}/items',                     [GaRoomController::class, 'storeItem'])->name('rooms.items.store');
    Route::put('rooms/{room}/items/{item}',               [GaRoomController::class, 'updateItem'])->name('rooms.items.update');
    Route::delete('rooms/{room}/items/{item}',            [GaRoomController::class, 'destroyItem'])->name('rooms.items.destroy');

    // Riwayat Kebersihan
    Route::get('cleaning-logs',                           [GaCleaningLogController::class, 'index'])->name('cleaning-logs.index');
    Route::get('cleaning-logs/{log}',                     [GaCleaningLogController::class, 'show'])->name('cleaning-logs.show');
    Route::get('cleaning-logs/{log}/photo/{photo}',       [GaCleaningLogController::class, 'photo'])->name('cleaning-logs.photo');
});

// ── Medical Reimbursement — semua user yang login ─────────────────────
Route::middleware('auth')->prefix('reimbursement')->name('reimbursement.')->group(function () {
    Route::get('/',                                          [ReimbursementController::class, 'index'])->name('index');
    Route::get('/create',                                    [ReimbursementController::class, 'create'])->name('create');
    Route::post('/',                                         [ReimbursementController::class, 'store'])->name('store');
    Route::get('/{reimbursement}',                           [ReimbursementController::class, 'show'])->name('show');
    Route::get('/{reimbursement}/edit',                      [ReimbursementController::class, 'edit'])->name('edit');
    Route::put('/{reimbursement}',                           [ReimbursementController::class, 'update'])->name('update');
    Route::post('/{reimbursement}/submit',                   [ReimbursementController::class, 'submit'])->name('submit');
    Route::get('/{reimbursement}/pdf',                       [ReimbursementController::class, 'pdf'])->name('pdf');
    Route::get('/{reimbursement}/attachment/{attachment}',   [ReimbursementController::class, 'attachment'])->name('attachment');
});

// ── Medical Reimbursement Admin — role:admin ──────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin/reimbursement')->name('reimbursement.admin.')->group(function () {
    Route::get('/',                                                          [ReimbursementAdminController::class, 'index'])->name('index');
    Route::get('/balances',                                                  [ReimbursementBalanceController::class, 'index'])->name('balances');
    Route::post('/balances',                                                 [ReimbursementBalanceController::class, 'upsert'])->name('balances.upsert');
    Route::get('/{reimbursement}',                                           [ReimbursementAdminController::class, 'show'])->name('show');
    Route::post('/{reimbursement}/approve',                                  [ReimbursementAdminController::class, 'approve'])->name('approve');
    Route::post('/{reimbursement}/reject',                                   [ReimbursementAdminController::class, 'reject'])->name('reject');
    Route::get('/{reimbursement}/pdf',                                       [ReimbursementAdminController::class, 'pdf'])->name('pdf');
    Route::get('/{reimbursement}/attachment/{attachment}',                   [ReimbursementAdminController::class, 'attachment'])->name('attachment');
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
