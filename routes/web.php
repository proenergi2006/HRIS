<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Whistleblower\PublicWhistleblowerController;
use App\Http\Controllers\Whistleblower\WhistleblowerController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Appraisal\LevelController;
use App\Http\Controllers\Appraisal\EmployeeController;
use App\Http\Controllers\Appraisal\TemplateController;
use App\Http\Controllers\Appraisal\PerformanceCheckinController;
use App\Http\Controllers\Appraisal\CompanyObjectiveController;
use App\Http\Controllers\Appraisal\Feedback360Controller;
use App\Http\Controllers\Appraisal\PeriodController;
use App\Http\Controllers\Appraisal\AppraisalController;
use App\Http\Controllers\Appraisal\ReportController;
use App\Http\Controllers\Appraisal\EmployeeDocumentController;
use App\Http\Controllers\Appraisal\EmployeeDataChangeController;
use App\Http\Controllers\Appraisal\LetterTemplateController;
use App\Http\Controllers\Appraisal\EmployeeLetterController;
use App\Http\Controllers\Appraisal\EmployeeFamilyMemberController;
use App\Http\Controllers\Appraisal\EmployeeImportController;
use App\Http\Controllers\Appraisal\EmployeeNssfController;
use App\Http\Controllers\Appraisal\EmployeeEducationController;
use App\Http\Controllers\Appraisal\EmployeeWorkExperienceController;
use App\Http\Controllers\Appraisal\EmployeeSkillController;
use App\Http\Controllers\Appraisal\EmployeeOrgExperienceController;
use App\Http\Controllers\Appraisal\EmployeeFacilityController;
use App\Http\Controllers\Appraisal\EmployeeBankAccountController;
use App\Http\Controllers\Appraisal\EmployeeContractController;
use App\Http\Controllers\Appraisal\DepartmentController;
use App\Http\Controllers\Appraisal\PositionController;
use App\Http\Controllers\Appraisal\OrgChartController;
use App\Http\Controllers\Appraisal\DivisionController;
use App\Http\Controllers\Appraisal\SectionController;
use App\Http\Controllers\Appraisal\OrgChangeLogController;
use App\Http\Controllers\Master\ReligionController;
use App\Http\Controllers\Master\EducationLevelController;
use App\Http\Controllers\Master\EducationMajorController;
use App\Http\Controllers\Master\MaritalStatusController;
use App\Http\Controllers\Master\BloodTypeController;
use App\Http\Controllers\Master\EmployeeTypeController;
use App\Http\Controllers\Master\BankController;
use App\Http\Controllers\Master\CompanyBankController;
use App\Http\Controllers\Master\RegionController;
use App\Http\Controllers\Master\TerBracketController;
use App\Http\Controllers\GA\PublicVehicleController;
use App\Http\Controllers\GA\PublicVaultController;
use App\Http\Controllers\GA\GaVehicleController;
use App\Http\Controllers\GA\GaUsageController;
use App\Http\Controllers\GA\PublicRoomController;
use App\Http\Controllers\GA\GaRoomController;
use App\Http\Controllers\GA\GaCleaningLogController;
use App\Http\Controllers\GA\GaVaultCategoryController;
use App\Http\Controllers\GA\GaVaultController;
use App\Http\Controllers\GA\GaVaultDocumentController;
use App\Http\Controllers\GA\GaVaultTransactionController;
use App\Http\Controllers\Reimbursement\ReimbursementController;
use App\Http\Controllers\Reimbursement\ReimbursementAdminController;
use App\Http\Controllers\Reimbursement\ReimbursementBalanceController;
use App\Http\Controllers\Perdin\PerdinController;
use App\Http\Controllers\Perdin\PerdinAdminController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\HR\AttendanceController;
use App\Http\Controllers\HR\OvertimeController;
use App\Http\Controllers\HR\OvertimeRequestController;
use App\Http\Controllers\HR\LeaveController;
use App\Http\Controllers\HR\CompensationController;
use App\Http\Controllers\HR\PayrollController;
use App\Http\Controllers\HR\ThrController;
use App\Http\Controllers\HR\BonusController;
use App\Http\Controllers\HR\LoanController;
use App\Http\Controllers\HR\BuktiPotongController;
use App\Http\Controllers\HR\RosterController;
use App\Http\Controllers\HR\OffboardingController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\KudosController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\Appraisal\LetterRequestController;
use App\Http\Controllers\Recruitment\RecruitmentCostController;
use App\Http\Controllers\Approval\ApprovalWorkflowController;
use App\Http\Controllers\Approval\ApprovalInboxController;
use App\Http\Controllers\Approval\HrRequestController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Manpower\ManpowerPlanController;
use App\Http\Controllers\Recruitment\JobRequisitionController;
use App\Http\Controllers\Recruitment\CandidateController;
use App\Http\Controllers\Recruitment\CandidateInterviewController;
use App\Http\Controllers\Recruitment\ReferralController;
use App\Http\Controllers\Recruitment\CandidateOfferController;
use App\Http\Controllers\Recruitment\CandidateDocumentController;
use App\Http\Controllers\Recruitment\CandidateProfileController;
use App\Http\Controllers\Recruitment\CandidatePreEmploymentController;
use App\Http\Controllers\Recruitment\OnboardingController;
use App\Http\Controllers\Training\TrainingProgramController;
use App\Http\Controllers\Competency\CompetencyController;
use App\Http\Controllers\Competency\PositionCompetencyController;
use App\Http\Controllers\Competency\EmployeeCompetencyController;
use App\Http\Controllers\Training\TrainingParticipantController;
use App\Http\Controllers\Career\CareerPathController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\HR\ProbationReviewController;
use App\Http\Controllers\HR\SuccessionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

// ── GA Kendaraan — public (QR scan, no auth) ──────────────────────────
Route::get('/ga/kendaraan/{vehicle}',          [PublicVehicleController::class, 'scan'])->name('ga.scan');
Route::post('/ga/kendaraan/{vehicle}/checkin', [PublicVehicleController::class, 'checkin'])->name('ga.checkin')->middleware('throttle:30,60');
Route::post('/ga/kendaraan/{vehicle}/checkout',[PublicVehicleController::class, 'checkout'])->name('ga.checkout')->middleware('throttle:30,60');

// ── GA Ruang Meeting — public (QR scan, no auth) ──────────────────────
Route::get('/ga/ruangan/{room}',         [PublicRoomController::class, 'scan'])->name('ga.room.scan');
Route::post('/ga/ruangan/{room}/submit', [PublicRoomController::class, 'submit'])->name('ga.room.submit')->middleware('throttle:30,60');
Route::get('/ga/ruangan/{room}/sukses',  [PublicRoomController::class, 'success'])->name('ga.room.success');

// ── GA Barcode Dokumen Brankas — public (QR scan, no auth) ────────────
// QR discan per berangkas (satu berangkas berisi banyak dokumen).
Route::get('/ga/berangkas/{vault}',                          [PublicVaultController::class, 'scan'])->name('ga.vault.scan');
Route::get('/ga/berangkas/{vault}/dokumen/{document}',       [PublicVaultController::class, 'document'])->name('ga.vault.document');
Route::post('/ga/berangkas/{vault}/dokumen/{document}/submit', [PublicVaultController::class, 'submit'])->name('ga.vault.submit')->middleware('throttle:30,60');
Route::get('/ga/berangkas/{vault}/dokumen/{document}/sukses', [PublicVaultController::class, 'success'])->name('ga.vault.success');

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

    // Notification Center — bel notifikasi in-app, semua user login.
    Route::get('/notifications',            [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read',  [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all',  [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Slip Gaji — self-service ESS (PRD Bab 4: "lihat slip gaji"), cuma periode closed.
    Route::prefix('my-payslips')->name('payroll.my.')->group(function () {
        Route::get('/',                       [PayrollController::class, 'mySlips'])->name('index');
        Route::get('/{slip}/pdf',             [PayrollController::class, 'mySlipPdf'])->name('pdf');
        Route::get('/bukti-potong/{year}/pdf', [BuktiPotongController::class, 'myPdf'])->name('bukti-potong-pdf');
    });

    // Total Rewards Statement — self-service ESS.
    Route::prefix('my-total-rewards')->name('payroll.my.rewards.')->group(function () {
        Route::get('/',    [CompensationController::class, 'myRewardsStatement'])->name('index');
        Route::get('/pdf', [CompensationController::class, 'myRewardsStatementPdf'])->name('pdf');
    });

    // Employee Referral — ESS: referensikan kandidat ke lowongan terbuka.
    Route::prefix('referrals')->name('recruitment.referrals.')->group(function () {
        Route::get('/',  [ReferralController::class, 'index'])->name('index');
        Route::post('/', [ReferralController::class, 'store'])->name('store');
    });

    // Onboarding Saya — ESS: materi induction + konfirmasi karyawan baru.
    Route::get('onboarding-saya',                           [OnboardingController::class, 'mine'])->name('onboarding.mine');
    Route::post('onboarding-saya/tasks/{task}/acknowledge', [OnboardingController::class, 'acknowledge'])->name('onboarding.acknowledge');
    Route::get('onboarding-materi/{item}',                  [OnboardingController::class, 'viewMaterial'])->name('onboarding.material');
    Route::get('onboarding-materi/{item}/unduh',             [OnboardingController::class, 'downloadMaterial'])->name('onboarding.material.download');

    // 1-on-1 Saya
    Route::get('checkins-saya', [PerformanceCheckinController::class, 'mine'])->name('appraisal.checkins.mine');

    // 360° Feedback Saya (isi penilaian sebagai rater)
    Route::prefix('feedback-360-saya')->name('feedback360.')->group(function () {
        Route::get('/',                     [Feedback360Controller::class, 'mine'])->name('mine');
        Route::get('/{review}',             [Feedback360Controller::class, 'fill'])->name('fill');
        Route::post('/{review}/submit',     [Feedback360Controller::class, 'submit'])->name('submit');
    });

    // Pengajuan Lembur — self-service (Attendance & Leave, Bab 3.2: overtime butuh
    // Dynamic Approval Workflow — mengisi gap transaction type 'overtime_request'
    // yang sudah ada di ApprovalWorkflowSeeder sejak awal tapi belum ada modelnya).
    Route::prefix('hr/overtime-requests')->name('hr.overtime-requests.')->group(function () {
        Route::get('/',            [OvertimeRequestController::class, 'index'])->name('index');
        Route::get('/create',      [OvertimeRequestController::class, 'create'])->name('create');
        Route::post('/',           [OvertimeRequestController::class, 'store'])->name('store');
        Route::get('/{overtimeRequest}',        [OvertimeRequestController::class, 'show'])->name('show');
        Route::post('/{overtimeRequest}/submit',[OvertimeRequestController::class, 'submit'])->name('submit');
        Route::delete('/{overtimeRequest}',     [OvertimeRequestController::class, 'destroy'])->name('destroy');
    });

    // === MODUL PENILAIAN KINERJA — akses semua user ===
    Route::prefix('appraisal')->name('appraisal.')->group(function () {

        // Form penilaian (evaluator buat, semua bisa lihat). Approve/reject lewat
        // Kotak Persetujuan terpadu (approval.inbox.*) — tidak ada route khusus di sini.
        Route::resource('appraisals', AppraisalController::class);
        Route::get('appraisals/{appraisal}/pdf',     [AppraisalController::class, 'pdf'])->name('appraisals.pdf');
        Route::post('appraisals/{appraisal}/submit', [AppraisalController::class, 'submit'])->name('appraisals.submit');

        // Laporan (semua user bisa lihat)
        Route::get('report',        [ReportController::class, 'index'])->name('report.index');
        Route::get('report/export', [ReportController::class, 'export'])->name('report.export');

        // Pengajuan Perubahan Data — self-service (Employee Administration, Bab 3 modul #7)
        Route::prefix('employee-data-changes')->name('employee-data-changes.')->group(function () {
            Route::get('/',                [EmployeeDataChangeController::class, 'index'])->name('index');
            Route::get('/create',          [EmployeeDataChangeController::class, 'create'])->name('create');
            Route::post('/',               [EmployeeDataChangeController::class, 'store'])->name('store');
            Route::get('/{employeeDataChange}',        [EmployeeDataChangeController::class, 'show'])->name('show');
            Route::post('/{employeeDataChange}/submit',[EmployeeDataChangeController::class, 'submit'])->name('submit');
            Route::delete('/{employeeDataChange}',     [EmployeeDataChangeController::class, 'destroy'])->name('destroy');
        });

        // Permintaan Surat — self-service (lightweight, HR-managed langsung)
        Route::prefix('letter-requests')->name('letter-requests.')->group(function () {
            Route::get('/',                     [LetterRequestController::class, 'index'])->name('index');
            Route::get('/create',               [LetterRequestController::class, 'create'])->name('create');
            Route::post('/',                    [LetterRequestController::class, 'store'])->name('store');
            Route::post('/{letterRequest}/cancel',  [LetterRequestController::class, 'cancel'])->name('cancel');
            Route::post('/{letterRequest}/process', [LetterRequestController::class, 'process'])->name('process');
            Route::post('/{letterRequest}/reject',  [LetterRequestController::class, 'reject'])->name('reject');
        });
    });
});

// ── Pengumuman — semua user login lihat; kelola pakai permission announcement.edit ──
Route::middleware('auth')->group(function () {
    Route::get('/pengumuman',              [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/pengumuman/kelola',       [AnnouncementController::class, 'manage'])->name('announcements.manage')->middleware('permission:announcement.edit');
    Route::get('/pengumuman/baru',         [AnnouncementController::class, 'create'])->name('announcements.create')->middleware('permission:announcement.edit');
    Route::post('/pengumuman',             [AnnouncementController::class, 'store'])->name('announcements.store')->middleware('permission:announcement.edit');
    Route::get('/pengumuman/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit')->middleware('permission:announcement.edit');
    Route::put('/pengumuman/{announcement}',      [AnnouncementController::class, 'update'])->name('announcements.update')->middleware('permission:announcement.edit');
    Route::delete('/pengumuman/{announcement}',   [AnnouncementController::class, 'destroy'])->name('announcements.destroy')->middleware('permission:announcement.edit');
    Route::post('/pengumuman/{announcement}/toggle-publish', [AnnouncementController::class, 'togglePublish'])->name('announcements.toggle-publish')->middleware('permission:announcement.edit');
    Route::get('/pengumuman/{announcement}/attachment', [AnnouncementController::class, 'attachment'])->name('announcements.attachment');
    Route::get('/pengumuman/{announcement}',      [AnnouncementController::class, 'show'])->name('announcements.show');
});

// ── Recognition / Kudos — semua user login, tanpa permission khusus ──────
Route::middleware('auth')->prefix('kudos')->name('kudos.')->group(function () {
    Route::get('/',  [KudosController::class, 'index'])->name('index');
    Route::post('/', [KudosController::class, 'store'])->name('store');
});

// ── Survey Engagement — semua user login isi; kelola pakai permission survey.edit ──
Route::middleware('auth')->group(function () {
    Route::get('/survey',              [SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/survey/{survey}',     [SurveyController::class, 'show'])->name('surveys.show');
    Route::post('/survey/{survey}',    [SurveyController::class, 'submit'])->name('surveys.submit');

    Route::middleware('permission:survey.edit')->prefix('admin/surveys')->name('surveys.manage.')->group(function () {
        Route::get('/',                    [SurveyController::class, 'manage'])->name('index');
        Route::get('/create',              [SurveyController::class, 'create'])->name('create');
        Route::post('/',                   [SurveyController::class, 'store'])->name('store');
        Route::get('/enps-trend',          [SurveyController::class, 'enpsTrend'])->name('enps-trend');
        Route::get('/{survey}/edit',       [SurveyController::class, 'edit'])->name('edit');
        Route::put('/{survey}',            [SurveyController::class, 'update'])->name('update');
        Route::delete('/{survey}',         [SurveyController::class, 'destroy'])->name('destroy');
        Route::post('/{survey}/open',      [SurveyController::class, 'open'])->name('open');
        Route::post('/{survey}/close',     [SurveyController::class, 'close'])->name('close');
        Route::post('/{survey}/duplicate', [SurveyController::class, 'duplicate'])->name('duplicate');
        Route::get('/{survey}/results',    [SurveyController::class, 'results'])->name('results');
    });
});

// ── GA Admin — permission ga.* ─────────────────────────────────────────
Route::middleware(['auth', 'permission:ga.view'])->prefix('admin/ga')->name('ga.admin.')->group(function () {
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

    // Barcode Dokumen Brankas
    Route::resource('vault-categories', GaVaultCategoryController::class)
        ->parameters(['vault-categories' => 'category'])->except(['show', 'create', 'edit']);

    Route::resource('vaults', GaVaultController::class)
        ->parameters(['vaults' => 'vault']);
    Route::get('vaults/{vault}/qrcode', [GaVaultController::class, 'qrcode'])->name('vaults.qrcode');

    Route::resource('vault-documents', GaVaultDocumentController::class)
        ->parameters(['vault-documents' => 'document']);
    Route::post('vault-documents/{document}/transactions', [GaVaultTransactionController::class, 'store'])->name('vault-documents.transactions.store');

    Route::get('vault-transactions',                      [GaVaultTransactionController::class, 'index'])->name('vault-transactions.index');
    Route::get('vault-transactions/{transaction}/photo',  [GaVaultTransactionController::class, 'photo'])->name('vault-transactions.photo');
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
    Route::post('/{reimbursement}/cancel',                   [ReimbursementController::class, 'cancel'])->name('cancel');
    Route::get('/{reimbursement}/pdf',                       [ReimbursementController::class, 'pdf'])->name('pdf');
    Route::get('/{reimbursement}/attachment/{attachment}',    [ReimbursementController::class, 'attachment'])->name('attachment');
    Route::delete('/{reimbursement}/attachment/{attachment}', [ReimbursementController::class, 'destroyAttachment'])->name('attachment.destroy');
});

// ── Medical Reimbursement Admin — permission reimbursement-admin.* ─────
Route::middleware(['auth', 'permission:reimbursement-admin.view'])->prefix('admin/reimbursement')->name('reimbursement.admin.')->group(function () {
    Route::get('/',                                                          [ReimbursementAdminController::class, 'index'])->name('index');
    Route::get('/balances',                                                  [ReimbursementBalanceController::class, 'index'])->name('balances');
    Route::post('/balances',                                                 [ReimbursementBalanceController::class, 'upsert'])->name('balances.upsert');
    Route::get('/{reimbursement}',                                           [ReimbursementAdminController::class, 'show'])->name('show');
    Route::put('/{reimbursement}/items/{item}',                              [ReimbursementAdminController::class, 'updateItem'])->name('items.update');
    Route::delete('/{reimbursement}/items/{item}',                           [ReimbursementAdminController::class, 'destroyItem'])->name('items.destroy');
    Route::post('/{reimbursement}/payment-period',                           [ReimbursementAdminController::class, 'setPaymentPeriod'])->name('payment-period');
    Route::get('/{reimbursement}/pdf',                                       [ReimbursementAdminController::class, 'pdf'])->name('pdf');
    Route::get('/{reimbursement}/attachment/{attachment}',                   [ReimbursementAdminController::class, 'attachment'])->name('attachment');
    Route::post('/{reimbursement}/attachment/{attachment}/doc-type',         [ReimbursementAdminController::class, 'updateAttachmentType'])->name('attachment.doc-type');
});

// ── Perjalanan Dinas (Perdin) — semua user yang login ─────────────────
Route::middleware('auth')->prefix('perdin')->name('perdin.')->group(function () {
    // Persetujuan Perdin kini lewat Kotak Persetujuan terpadu (approval.inbox).
    Route::get('/approvals', fn () => redirect()->route('approval.inbox.index'))->name('approvals.index');

    Route::get('/',                          [PerdinController::class, 'index'])->name('index');
    Route::get('/create',                    [PerdinController::class, 'create'])->name('create');
    Route::post('/',                         [PerdinController::class, 'store'])->name('store');
    Route::get('/{perdin}',                  [PerdinController::class, 'show'])->name('show');
    Route::get('/{perdin}/edit',             [PerdinController::class, 'edit'])->name('edit');
    Route::put('/{perdin}',                  [PerdinController::class, 'update'])->name('update');
    Route::delete('/{perdin}',               [PerdinController::class, 'destroy'])->name('destroy');
    Route::post('/{perdin}/submit',          [PerdinController::class, 'submit'])->name('submit');
    Route::post('/{perdin}/cancel',          [PerdinController::class, 'cancel'])->name('cancel');
    Route::get('/{perdin}/pdf',              [PerdinController::class, 'pdf'])->name('pdf');
});

// ── Perjalanan Dinas Admin — permission perdin-admin.view ─────────────
Route::middleware(['auth', 'permission:perdin-admin.view'])->prefix('admin/perdin')->name('perdin.admin.')->group(function () {
    Route::get('/',                 [PerdinAdminController::class, 'requests'])->name('requests');
});

// ── Laporan & Export — permission laporan.view ───────────────────────
Route::middleware(['auth', 'permission:laporan.view'])->prefix('admin/laporan')->name('laporan.')->group(function () {
    Route::get('/',                    [LaporanController::class, 'index'])->name('index');
    Route::get('/pdf',                 [LaporanController::class, 'pdf'])->name('pdf');
    Route::get('/absensi-cuti',        [LaporanController::class, 'attendanceLeave'])->name('attendance-leave');
    Route::get('/absensi-cuti/pdf',    [LaporanController::class, 'attendanceLeavePdf'])->name('attendance-leave.pdf');
    Route::get('/payroll',            [LaporanController::class, 'payrollSummary'])->name('payroll');
    Route::get('/payroll/pdf',        [LaporanController::class, 'payrollSummaryPdf'])->name('payroll.pdf');
    Route::get('/headcount',          [LaporanController::class, 'headcount'])->name('headcount');
    Route::get('/headcount/pdf',      [LaporanController::class, 'headcountPdf'])->name('headcount.pdf');
    Route::get('/analytics',          [LaporanController::class, 'analytics'])->name('analytics');
    Route::get('/analytics/pdf',      [LaporanController::class, 'analyticsPdf'])->name('analytics.pdf');
    Route::get('/report-builder',      [LaporanController::class, 'reportBuilder'])->name('report-builder');
    Route::get('/report-builder/export', [LaporanController::class, 'reportBuilderExport'])->name('report-builder.export');
    Route::get('/export/karyawan',     [LaporanController::class, 'exportEmployees'])->name('export.karyawan');
    Route::get('/export/reimbursement',[LaporanController::class, 'exportReimbursements'])->name('export.reimb');
    Route::get('/export/perdin',       [LaporanController::class, 'exportPerdin'])->name('export.perdin');
});

// ── HR: Absensi, Lembur, Cuti, Penggajian — permission per sub-modul ──
// (dulu 1 grup role:admin|hr_manager bersama; dipecah supaya masing-masing
// bisa diizinkan/dilarang terpisah lewat Role & Hak Akses)
Route::middleware('auth')->prefix('hr')->name('hr.')->group(function () {

    // Absensi
    Route::middleware('permission:attendance.view')->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',                [AttendanceController::class, 'index'])->name('index');
        Route::get('/input',           [AttendanceController::class, 'create'])->name('create');
        Route::post('/input',          [AttendanceController::class, 'store'])->name('store');
        Route::post('/bulk',           [AttendanceController::class, 'bulkStore'])->name('bulk');
        Route::get('/import',          [AttendanceController::class, 'importForm'])->name('import.form');
        Route::post('/import',         [AttendanceController::class, 'import'])->name('import');
    });

    // Lembur
    Route::middleware('permission:overtime.view')->prefix('overtime')->name('overtime.')->group(function () {
        Route::get('/',        [OvertimeController::class, 'index'])->name('index');
        Route::get('/input',   [OvertimeController::class, 'create'])->name('create');
        Route::post('/input',  [OvertimeController::class, 'store'])->name('store');
    });

    // Kalender Cuti Tim — HR (leave-admin.view) atau manager (punya bawahan langsung);
    // otorisasi dicek di controller sendiri, bukan middleware permission (supaya manager
    // biasa tanpa permission leave-admin tetap bisa lihat kalender timnya).
    Route::get('leave-team-calendar', [LeaveController::class, 'teamCalendar'])->name('leave.team-calendar');

    // Cuti
    Route::middleware('permission:leave-admin.view')->prefix('leave')->name('leave.')->group(function () {
        Route::get('/',                [LeaveController::class, 'index'])->name('index');
        Route::get('/create',          [LeaveController::class, 'create'])->name('create');
        Route::post('/',               [LeaveController::class, 'store'])->name('store');
        Route::get('/{leave}',         [LeaveController::class, 'show'])->name('show');
        Route::post('/{leave}/cancel',          [LeaveController::class, 'cancel'])->name('cancel');
        Route::get('/{leave}/attachment',       [LeaveController::class, 'attachment'])->name('attachment');
        Route::get('/balances/index',  [LeaveController::class, 'balances'])->name('balances');
        Route::post('/balances/upsert',[LeaveController::class, 'upsertBalance'])->name('balances.upsert');
    });

    // Penggajian
    Route::middleware('permission:payroll.view')->prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                     [PayrollController::class, 'periods'])->name('index');
        Route::get('/create',               [PayrollController::class, 'createPeriod'])->name('create');
        Route::post('/',                    [PayrollController::class, 'storePeriod'])->name('store');

        // Bukti Potong PPh21 tahunan (ringkas internal) — sebelum /{period} agar tidak tertangkap route model binding
        Route::get('/bukti-potong',          [BuktiPotongController::class, 'index'])->name('bukti-potong');
        Route::get('/bukti-potong/{company}/{employee}/{year}/pdf', [BuktiPotongController::class, 'pdf'])->name('bukti-potong.pdf');

        Route::get('/{period}',             [PayrollController::class, 'show'])->name('show');
        Route::post('/{period}/generate',   [PayrollController::class, 'generate'])->name('generate');
        Route::post('/{period}/close',      [PayrollController::class, 'close'])->name('close');
        Route::get('/{period}/slip/{slip}/pdf', [PayrollController::class, 'slipPdf'])->name('slip.pdf');
        Route::get('/{period}/disbursement', [PayrollController::class, 'disbursement'])->name('disbursement');

        // Komponen gaji master
        Route::get('/components/index',     [PayrollController::class, 'components'])->name('components');
        Route::post('/components',          [PayrollController::class, 'storeComponent'])->name('components.store');
        Route::put('/components/{component}/rate', [PayrollController::class, 'updateComponentRate'])->name('components.rate');
        Route::delete('/components/{component}', [PayrollController::class, 'destroyComponent'])->name('components.destroy');

        // Struktur gaji per karyawan
        Route::get('/salary/index',         [PayrollController::class, 'salaryIndex'])->name('salary.index');
        Route::get('/salary/{employee}',    [PayrollController::class, 'salaryEdit'])->name('salary.edit');
        Route::post('/salary/{employee}',   [PayrollController::class, 'salaryUpdate'])->name('salary.update');
    });

    // THR (Tunjangan Hari Raya)
    Route::middleware('permission:payroll.view')->prefix('thr')->name('thr.')->group(function () {
        Route::get('/',                  [ThrController::class, 'index'])->name('index');
        Route::get('/create',            [ThrController::class, 'create'])->name('create');
        Route::post('/',                 [ThrController::class, 'store'])->name('store');
        Route::get('/{period}',          [ThrController::class, 'show'])->name('show');
        Route::post('/{period}/generate',[ThrController::class, 'generate'])->name('generate');
        Route::post('/{period}/close',   [ThrController::class, 'close'])->name('close');
        Route::get('/{period}/payments/{payment}/pdf', [ThrController::class, 'pdf'])->name('pdf');
    });

    // Kasbon / Pinjaman karyawan
    Route::middleware('permission:payroll.view')->prefix('loans')->name('loans.')->group(function () {
        Route::get('/',                       [LoanController::class, 'index'])->name('index');
        Route::get('/create',                 [LoanController::class, 'create'])->name('create');
        Route::post('/',                      [LoanController::class, 'store'])->name('store');
        Route::get('/{loan}',                 [LoanController::class, 'show'])->name('show');
        Route::post('/{loan}/cancel',         [LoanController::class, 'cancel'])->name('cancel');
        Route::post('/{loan}/installments/{installment}/waive', [LoanController::class, 'waiveInstallment'])->name('installments.waive');
    });

    // Bonus / Insentif run
    Route::middleware('permission:payroll.view')->prefix('bonus')->name('bonus.')->group(function () {
        Route::get('/',                   [BonusController::class, 'index'])->name('index');
        Route::get('/create',             [BonusController::class, 'create'])->name('create');
        Route::post('/',                  [BonusController::class, 'store'])->name('store');
        Route::get('/{period}',           [BonusController::class, 'show'])->name('show');
        Route::post('/{period}/generate', [BonusController::class, 'generate'])->name('generate');
        Route::post('/{period}/amounts',  [BonusController::class, 'updateAmounts'])->name('amounts');
        Route::post('/{period}/close',    [BonusController::class, 'close'])->name('close');
        Route::get('/{period}/payments/{payment}/pdf', [BonusController::class, 'pdf'])->name('pdf');
    });

    // Probation Review — evaluasi akhir masa probation
    Route::middleware('permission:employee-master.edit')->prefix('probation')->name('probation.')->group(function () {
        Route::get('/',            [ProbationReviewController::class, 'index'])->name('index');
        Route::get('/{employee}',  [ProbationReviewController::class, 'show'])->name('show');
        Route::post('/{employee}', [ProbationReviewController::class, 'store'])->name('store');
    });

    // Compensation — benchmark gaji pasar per Level + Total Rewards Statement
    Route::middleware('permission:payroll.view')->prefix('compensation')->name('compensation.')->group(function () {
        Route::get('/benchmarks',             [CompensationController::class, 'benchmarks'])->name('benchmarks');
        Route::put('/benchmarks/{level}',     [CompensationController::class, 'updateBenchmark'])->name('benchmarks.update');
        Route::get('/comparison',             [CompensationController::class, 'comparison'])->name('comparison');
        Route::get('/rewards/{employee}',     [CompensationController::class, 'rewardsStatement'])->name('rewards');
        Route::get('/rewards/{employee}/pdf', [CompensationController::class, 'rewardsStatementPdf'])->name('rewards.pdf');
        Route::get('/grades',                 [CompensationController::class, 'grades'])->name('grades');
        Route::put('/grades/{level}',         [CompensationController::class, 'updateGrade'])->name('grades.update');
        Route::get('/grades/{level}/history', [CompensationController::class, 'gradeHistory'])->name('grades.history');
        Route::get('/grade-position',         [CompensationController::class, 'gradePosition'])->name('grade-position');
    });

    // Shift & Roster
    Route::middleware('permission:shift.view')->prefix('roster')->name('roster.')->group(function () {
        Route::get('/',                [RosterController::class, 'index'])->name('index');
        Route::post('/bulk',           [RosterController::class, 'bulkAssign'])->name('bulk');
        Route::post('/cell',           [RosterController::class, 'updateCell'])->name('cell');
        Route::post('/clear',          [RosterController::class, 'clear'])->name('clear');
        Route::get('/shifts',          [RosterController::class, 'shifts'])->name('shifts');
        Route::post('/shifts',         [RosterController::class, 'storeShift'])->name('shifts.store');
        Route::put('/shifts/{shift}',  [RosterController::class, 'updateShift'])->name('shifts.update');
        Route::delete('/shifts/{shift}',[RosterController::class, 'destroyShift'])->name('shifts.destroy');
    });

    // Clearance saat resign (offboarding)
    Route::middleware('permission:offboarding.view')->prefix('offboarding')->name('offboarding.')->group(function () {
        Route::get('/templates',                  [OffboardingController::class, 'templates'])->name('templates');
        Route::post('/templates',                 [OffboardingController::class, 'storeTemplate'])->name('templates.store');
        Route::put('/templates/{item}',           [OffboardingController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('/templates/{item}',        [OffboardingController::class, 'destroyTemplate'])->name('templates.destroy');
        Route::get('/',                           [OffboardingController::class, 'index'])->name('index');
        Route::post('/{employee}/start',          [OffboardingController::class, 'start'])->name('start');
        Route::get('/{employee}',                 [OffboardingController::class, 'show'])->name('show');
        Route::post('/{employee}/tasks/{task}/toggle', [OffboardingController::class, 'toggleTask'])->name('tasks.toggle');
        Route::put('/{employee}/exit',            [OffboardingController::class, 'updateExit'])->name('exit.update');
    });

    // Kebijakan cuti (carry-forward / kuota per golongan)
    Route::middleware('permission:leave-admin.view')->prefix('leave-policies')->name('leave.policies.')->group(function () {
        Route::get('/',            [LeaveController::class, 'policies'])->name('index');
        Route::post('/',           [LeaveController::class, 'storePolicy'])->name('store');
        Route::put('/{policy}',    [LeaveController::class, 'updatePolicy'])->name('update');
        Route::delete('/{policy}', [LeaveController::class, 'destroyPolicy'])->name('destroy');
        Route::post('/generate',   [LeaveController::class, 'generateBalances'])->name('generate');
    });
});

// ── Approval Engine ──────────────────────────────────────────────────
Route::middleware('auth')->prefix('approval')->name('approval.')->group(function () {
    // Inbox — semua user yang login
    Route::get('inbox',                   [ApprovalInboxController::class, 'index'])->name('inbox.index');
    Route::get('inbox/history',           [ApprovalInboxController::class, 'history'])->name('inbox.history');
    Route::get('inbox/{step}',            [ApprovalInboxController::class, 'show'])->name('inbox.show');
    Route::post('inbox/{step}/approve',   [ApprovalInboxController::class, 'approve'])->name('inbox.approve');
    Route::post('inbox/{step}/reject',    [ApprovalInboxController::class, 'reject'])->name('inbox.reject');

    Route::get('delegations',                  [ApprovalInboxController::class, 'delegations'])->name('delegations.index');
    Route::post('delegations',                 [ApprovalInboxController::class, 'storeDelegation'])->name('delegations.store');
    Route::delete('delegations/{delegation}',  [ApprovalInboxController::class, 'destroyDelegation'])->name('delegations.destroy');
});

// ── Approval — pengaturan alur persetujuan — permission approval-workflow.view
Route::middleware(['auth', 'permission:approval-workflow.view'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('workflows',                          [ApprovalWorkflowController::class, 'index'])->name('workflows.index');
    Route::get('workflows/log',                      [ApprovalWorkflowController::class, 'log'])->name('workflows.log');
    Route::post('workflows/copy',                    [ApprovalWorkflowController::class, 'copy'])->name('workflows.copy');
    Route::get('workflows/{company}/{type}',         [ApprovalWorkflowController::class, 'edit'])->name('workflows.edit');
    Route::put('workflows/{company}/{type}',         [ApprovalWorkflowController::class, 'update'])->name('workflows.update');
    Route::post('workflows/{company}/{type}/simulate', [ApprovalWorkflowController::class, 'simulate'])->name('workflows.simulate');
});

// ── Pengajuan HR: Reward / Punishment / Promosi & Rotasi / Termination —
// permission hr-request.view ───────────────────────────────────────────
Route::middleware(['auth', 'permission:hr-request.view'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('requests/{kind}',                 [HrRequestController::class, 'index'])->name('hr-request.index');
    Route::get('requests/{kind}/create',          [HrRequestController::class, 'create'])->name('hr-request.create');
    Route::post('requests/{kind}',                [HrRequestController::class, 'store'])->name('hr-request.store');
    Route::get('requests/{kind}/{id}',            [HrRequestController::class, 'show'])->name('hr-request.show');
    Route::get('requests/{kind}/{id}/edit',       [HrRequestController::class, 'edit'])->name('hr-request.edit');
    Route::put('requests/{kind}/{id}',            [HrRequestController::class, 'update'])->name('hr-request.update');
    Route::post('requests/{kind}/{id}/submit',    [HrRequestController::class, 'submit'])->name('hr-request.submit');
    Route::delete('requests/{kind}/{id}',         [HrRequestController::class, 'destroy'])->name('hr-request.destroy');
});

// ── Whistleblower admin — permission whistleblower-admin.view ─────────
Route::middleware(['auth', 'permission:whistleblower-admin.view'])->prefix('admin/whistleblower')->name('whistleblower.admin.')->group(function () {
    Route::get('/',                                      [WhistleblowerController::class, 'index'])->name('index');
    Route::get('/qrcode',                               [WhistleblowerController::class, 'qrcode'])->name('qrcode');
    Route::get('/{report}',                             [WhistleblowerController::class, 'show'])->name('show');
    Route::patch('/{report}/status',                    [WhistleblowerController::class, 'updateStatus'])->name('update-status');
    Route::get('/{report}/download',                    [WhistleblowerController::class, 'download'])->name('download');
});

// ── Activity Log — permission activity-log.view ────────────────────────
Route::middleware(['auth', 'permission:activity-log.view'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// ── Role & Hak Akses — permission roles-manage.view ────────────────────
Route::middleware(['auth', 'permission:roles-manage.view'])->prefix('admin/roles')->name('admin.roles.')->group(function () {
    Route::get('/',            [RoleController::class, 'index'])->name('index');
    Route::get('/create',      [RoleController::class, 'create'])->name('create');
    Route::post('/',           [RoleController::class, 'store'])->name('store');
    Route::get('/{role}',      [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}',      [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}',   [RoleController::class, 'destroy'])->name('destroy');
});

// ── Master Data referensi — permission master-data.view ────────────────
Route::middleware(['auth', 'permission:master-data.view'])->prefix('master')->name('master.')->group(function () {
    Route::resource('religions',        ReligionController::class)->except(['show', 'create', 'edit'])->parameters(['religions' => 'id']);
    Route::resource('education-levels',  EducationLevelController::class)->except(['show', 'create', 'edit'])->parameters(['education-levels' => 'id']);
    Route::resource('education-majors',  EducationMajorController::class)->except(['show', 'create', 'edit'])->parameters(['education-majors' => 'id']);
    Route::resource('marital-statuses',  MaritalStatusController::class)->except(['show', 'create', 'edit'])->parameters(['marital-statuses' => 'id']);
    Route::resource('blood-types',       BloodTypeController::class)->except(['show', 'create', 'edit'])->parameters(['blood-types' => 'id']);
    Route::resource('employee-types',    EmployeeTypeController::class)->except(['show', 'create', 'edit'])->parameters(['employee-types' => 'id']);
    Route::resource('banks',             BankController::class)->except(['show', 'create', 'edit'])->parameters(['banks' => 'id']);
    Route::resource('company-banks',     CompanyBankController::class)->except(['show', 'create', 'edit']);

    Route::get('regions',                       [RegionController::class, 'index'])->name('regions.index');
    Route::post('regions',                      [RegionController::class, 'store'])->name('regions.store');
    Route::put('regions/{city}',                [RegionController::class, 'update'])->name('regions.update');
    Route::delete('regions/{city}',             [RegionController::class, 'destroy'])->name('regions.destroy');
    Route::post('regions/districts',            [RegionController::class, 'storeDistrict'])->name('regions.districts.store');
    Route::put('regions/districts/{district}',  [RegionController::class, 'updateDistrict'])->name('regions.districts.update');
    Route::delete('regions/districts/{district}', [RegionController::class, 'destroyDistrict'])->name('regions.districts.destroy');
    Route::post('regions/villages',             [RegionController::class, 'storeVillage'])->name('regions.villages.store');
    Route::put('regions/villages/{village}',    [RegionController::class, 'updateVillage'])->name('regions.villages.update');
    Route::delete('regions/villages/{village}', [RegionController::class, 'destroyVillage'])->name('regions.villages.destroy');

    Route::resource('ter-brackets', TerBracketController::class)->except(['show', 'create', 'edit']);
});

// ── Cascade wilayah (AJAX untuk form karyawan) — auth saja, data non-sensitif ──
Route::middleware('auth')->prefix('region-api')->name('region-api.')->group(function () {
    Route::get('cities',    [RegionController::class, 'apiCities'])->name('cities');
    Route::get('districts', [RegionController::class, 'apiDistricts'])->name('districts');
    Route::get('villages',  [RegionController::class, 'apiVillages'])->name('villages');
});

// ── Manajemen User — permission user-management.view ────────────────────
Route::middleware(['auth', 'permission:user-management.view'])->group(function () {
    Route::get('/users',           [UserController::class, 'index'])->name('user.index');
    Route::get('/users/create',    [UserController::class, 'create'])->name('user.create');
    Route::post('/users/create',   [UserController::class, 'store'])->name('user.store');
    Route::get('/users/{user}',    [UserController::class, 'edit'])->name('user.edit');
    Route::patch('/users/{user}',  [UserController::class, 'update'])->name('user.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('user.destroy');
});

// ── Data Karyawan — permission employee-master.view ─────────────────────
Route::middleware(['auth', 'permission:employee-master.view'])->prefix('appraisal')->name('appraisal.')->group(function () {
    Route::resource('employees',    EmployeeController::class);
    Route::get('employees/{employee}/photo', [EmployeeController::class, 'photo'])->name('employees.photo');

    // Anggota Keluarga (istri/anak) — dipakai sbg pilihan Nama Pasien di Reimbursement
    Route::post('employees/{employee}/family-members', [EmployeeFamilyMemberController::class, 'store'])->name('employees.family-members.store');
    Route::put('employees/{employee}/family-members/{familyMember}', [EmployeeFamilyMemberController::class, 'update'])->name('employees.family-members.update');
    Route::delete('employees/{employee}/family-members/{familyMember}', [EmployeeFamilyMemberController::class, 'destroy'])->name('employees.family-members.destroy');

    // Tab data karyawan — sub-data 1-ke-banyak (pola inline di form Edit Karyawan)
    Route::put('employees/{employee}/nssf', [EmployeeNssfController::class, 'update'])->name('employees.nssf.update');

    foreach ([
        'educations'       => EmployeeEducationController::class,
        'work-experiences' => EmployeeWorkExperienceController::class,
        'skills'           => EmployeeSkillController::class,
        'org-experiences'  => EmployeeOrgExperienceController::class,
        'facilities'       => EmployeeFacilityController::class,
        'bank-accounts'    => EmployeeBankAccountController::class,
    ] as $slug => $controller) {
        Route::post("employees/{employee}/{$slug}", [$controller, 'store'])->name("employees.{$slug}.store");
        Route::put("employees/{employee}/{$slug}/{child}", [$controller, 'update'])->name("employees.{$slug}.update");
        Route::delete("employees/{employee}/{$slug}/{child}", [$controller, 'destroy'])->name("employees.{$slug}.destroy");
    }

    // Kontrak — pakai route-model-binding ({contract}) karena ada file upload
    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])->name('employees.contracts.store');
    Route::put('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'update'])->name('employees.contracts.update');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])->name('employees.contracts.destroy');
    Route::get('employees/{employee}/contracts/{contract}/download', [EmployeeContractController::class, 'download'])->name('employees.contracts.download');

    // Import data karyawan (Excel)
    Route::get('employees-import/template', [EmployeeImportController::class, 'template'])->name('employees.import.template');
    Route::get('employees-import',           [EmployeeImportController::class, 'form'])->name('employees.import.form');
    Route::post('employees-import',          [EmployeeImportController::class, 'import'])->name('employees.import');

    // Dokumen karyawan (nested under employee)
    Route::prefix('employees/{employee}/documents')->name('employees.documents.')->group(function () {
        Route::get('/',            [EmployeeDocumentController::class, 'index'])->name('index');
        Route::get('/tambah',      [EmployeeDocumentController::class, 'create'])->name('create');
        Route::post('/',           [EmployeeDocumentController::class, 'store'])->name('store');
        Route::get('/{document}/download', [EmployeeDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}',       [EmployeeDocumentController::class, 'destroy'])->name('destroy');
    });

    // Template Surat — Employee Administration (Bab 3 modul #7 — "surat")
    Route::resource('letter-templates', LetterTemplateController::class)->except(['show', 'create', 'edit']);

    // Surat terbit per karyawan
    Route::prefix('employee-letters')->name('employee-letters.')->group(function () {
        Route::get('/',           [EmployeeLetterController::class, 'index'])->name('index');
        Route::get('/create',     [EmployeeLetterController::class, 'create'])->name('create');
        Route::post('/preview',   [EmployeeLetterController::class, 'preview'])->name('preview');
        Route::post('/',          [EmployeeLetterController::class, 'store'])->name('store');
        Route::get('/{employeeLetter}',     [EmployeeLetterController::class, 'show'])->name('show');
        Route::get('/{employeeLetter}/pdf', [EmployeeLetterController::class, 'pdf'])->name('pdf');
        Route::delete('/{employeeLetter}',  [EmployeeLetterController::class, 'destroy'])->name('destroy');
    });
});

// ── Struktur Organisasi — permission org-structure.view ──────────────────
Route::middleware(['auth', 'permission:org-structure.view'])->prefix('appraisal')->name('appraisal.')->group(function () {
    Route::get('org-chart', [OrgChartController::class, 'index'])->name('org-chart.index');
    Route::get('org-chart/pdf', [OrgChartController::class, 'pdf'])->name('org-chart.pdf');

    Route::resource('divisions',   DivisionController::class)->except(['show', 'create', 'edit']);
    Route::resource('departments', DepartmentController::class)->except(['show', 'create', 'edit']);
    Route::resource('sections',    SectionController::class)->except(['show', 'create', 'edit']);
    Route::resource('positions',   PositionController::class)->except(['show', 'create', 'edit']);

    // Drag & drop reparenting di bagan organisasi (PRD Bab 6.2) — permission edit terpisah.
    Route::middleware('permission:org-structure.edit')->group(function () {
        Route::put('departments/{department}/reparent', [DepartmentController::class, 'reparent'])->name('departments.reparent');
        Route::put('sections/{section}/reparent',       [SectionController::class, 'reparent'])->name('sections.reparent');
    });
    Route::get('org-log', [OrgChangeLogController::class, 'index'])->name('org-log.index');
});

// ── Manpower Planning — permission manpower-plan.view ─────────────────
Route::middleware(['auth', 'permission:manpower-plan.view'])->prefix('manpower/plans')->name('manpower.plans.')->group(function () {
    Route::get('/',            [ManpowerPlanController::class, 'index'])->name('index');
    Route::get('/create',      [ManpowerPlanController::class, 'create'])->name('create');
    Route::post('/',           [ManpowerPlanController::class, 'store'])->name('store');
    Route::get('/{plan}',      [ManpowerPlanController::class, 'show'])->name('show');
    Route::get('/{plan}/edit', [ManpowerPlanController::class, 'edit'])->name('edit');
    Route::put('/{plan}',      [ManpowerPlanController::class, 'update'])->name('update');
    Route::post('/{plan}/submit', [ManpowerPlanController::class, 'submit'])->name('submit');
    Route::put('/{plan}/revise',  [ManpowerPlanController::class, 'reviseQuota'])->name('revise');
    Route::delete('/{plan}',   [ManpowerPlanController::class, 'destroy'])->name('destroy');
});

// ── Recruitment + Pre-Employment + Onboarding — permission recruitment.view ──
Route::middleware(['auth', 'permission:recruitment.view'])->prefix('recruitment')->name('recruitment.')->group(function () {
    Route::get('requisitions',                 [JobRequisitionController::class, 'index'])->name('requisitions.index');
    Route::get('requisitions/create',          [JobRequisitionController::class, 'create'])->name('requisitions.create');
    Route::post('requisitions',                [JobRequisitionController::class, 'store'])->name('requisitions.store');
    Route::get('requisitions/{requisition}',       [JobRequisitionController::class, 'show'])->name('requisitions.show');
    Route::get('requisitions/{requisition}/edit',  [JobRequisitionController::class, 'edit'])->name('requisitions.edit');
    Route::put('requisitions/{requisition}',       [JobRequisitionController::class, 'update'])->name('requisitions.update');
    Route::post('requisitions/{requisition}/submit', [JobRequisitionController::class, 'submit'])->name('requisitions.submit');
    Route::delete('requisitions/{requisition}',    [JobRequisitionController::class, 'destroy'])->name('requisitions.destroy');

    Route::get('candidates',              [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('candidates/create',       [CandidateController::class, 'create'])->name('candidates.create');
    Route::post('candidates',             [CandidateController::class, 'store'])->name('candidates.store');
    Route::get('candidates/{candidate}',           [CandidateController::class, 'show'])->name('candidates.show');
    Route::get('candidates/{candidate}/edit',      [CandidateController::class, 'edit'])->name('candidates.edit');
    Route::put('candidates/{candidate}',           [CandidateController::class, 'update'])->name('candidates.update');
    Route::post('candidates/{candidate}/status',   [CandidateController::class, 'updateStatus'])->name('candidates.status');
    Route::put('candidates/{candidate}/referral-bonus', [CandidateController::class, 'updateReferralBonus'])->name('candidates.referral-bonus');
    Route::post('candidates/{candidate}/convert',  [CandidateController::class, 'convert'])->name('candidates.convert');
    Route::delete('candidates/{candidate}',        [CandidateController::class, 'destroy'])->name('candidates.destroy');

    Route::get('interview-calendar', [CandidateInterviewController::class, 'calendar'])->name('interview-calendar');
    Route::post('candidates/{candidate}/interviews',              [CandidateInterviewController::class, 'store'])->name('candidates.interviews.store');
    Route::put('candidates/{candidate}/interviews/{interview}',   [CandidateInterviewController::class, 'update'])->name('candidates.interviews.update');
    Route::delete('candidates/{candidate}/interviews/{interview}',[CandidateInterviewController::class, 'destroy'])->name('candidates.interviews.destroy');

    Route::post('candidates/{candidate}/offers',           [CandidateOfferController::class, 'store'])->name('candidates.offers.store');
    Route::put('candidates/{candidate}/offers/{offer}',    [CandidateOfferController::class, 'update'])->name('candidates.offers.update');
    Route::delete('candidates/{candidate}/offers/{offer}', [CandidateOfferController::class, 'destroy'])->name('candidates.offers.destroy');

    Route::post('candidates/{candidate}/documents',                       [CandidateDocumentController::class, 'store'])->name('candidates.documents.store');
    Route::get('candidates/{candidate}/documents/{document}/download',    [CandidateDocumentController::class, 'download'])->name('candidates.documents.download');
    Route::delete('candidates/{candidate}/documents/{document}',          [CandidateDocumentController::class, 'destroy'])->name('candidates.documents.destroy');

    // CV terstruktur kandidat — {type} = education|experience|skill|certification
    Route::post('candidates/{candidate}/profile/{type}',           [CandidateProfileController::class, 'store'])->name('candidates.profile.store');
    Route::put('candidates/{candidate}/profile/{type}/{id}',       [CandidateProfileController::class, 'update'])->name('candidates.profile.update');
    Route::delete('candidates/{candidate}/profile/{type}/{id}',    [CandidateProfileController::class, 'destroy'])->name('candidates.profile.destroy');

    // Pre-Employment — data terstruktur + checklist wajib sebelum konversi
    Route::put('candidates/{candidate}/preemployment',                    [CandidatePreEmploymentController::class, 'update'])->name('candidates.preemployment.update');
    Route::post('candidates/{candidate}/preemployment/tasks/{task}',      [CandidatePreEmploymentController::class, 'toggleTask'])->name('candidates.preemployment.toggle');

    // Statis dulu sebelum {employee} supaya 'templates' tidak ketangkep jadi hashid employee.
    Route::get('onboarding/templates',              [OnboardingController::class, 'templates'])->name('onboarding.templates');
    Route::post('onboarding/templates',              [OnboardingController::class, 'storeTemplate'])->name('onboarding.templates.store');
    Route::put('onboarding/templates/{item}',        [OnboardingController::class, 'updateTemplate'])->name('onboarding.templates.update');
    Route::delete('onboarding/templates/{item}',     [OnboardingController::class, 'destroyTemplate'])->name('onboarding.templates.destroy');

    Route::get('onboarding',                         [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('onboarding/{employee}/start',       [OnboardingController::class, 'start'])->name('onboarding.start');
    Route::get('onboarding/{employee}',              [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding/{employee}/tasks/{task}/toggle', [OnboardingController::class, 'toggleTask'])->name('onboarding.tasks.toggle');

    // Biaya rekrutmen (input metrik cost-per-hire)
    Route::get('costs',    [RecruitmentCostController::class, 'index'])->name('costs.index');
    Route::post('costs',   [RecruitmentCostController::class, 'store'])->name('costs.store');
    Route::delete('costs/{recruitmentCost}', [RecruitmentCostController::class, 'destroy'])->name('costs.destroy');
});

// ── Training & Development — permission training.view ─────────────────
Route::middleware(['auth', 'permission:training.view'])->prefix('training')->name('training.')->group(function () {
    Route::resource('programs', TrainingProgramController::class)->except(['show', 'create', 'edit']);
    Route::resource('participants', TrainingParticipantController::class)->except(['show', 'create', 'edit']);
});

// ── Competency Framework — permission competency.view ─────────────────
Route::middleware(['auth', 'permission:competency.view'])->prefix('competency')->name('competency.')->group(function () {
    Route::get('dictionary',                [CompetencyController::class, 'index'])->name('dictionary.index');
    Route::post('dictionary',               [CompetencyController::class, 'store'])->name('dictionary.store');
    Route::put('dictionary/{competency}',   [CompetencyController::class, 'update'])->name('dictionary.update');
    Route::delete('dictionary/{competency}',[CompetencyController::class, 'destroy'])->name('dictionary.destroy');

    Route::get('positions',                 [PositionCompetencyController::class, 'index'])->name('positions.index');
    Route::get('positions/{position}',      [PositionCompetencyController::class, 'show'])->name('positions.show');
    Route::post('positions/{position}/requirements',               [PositionCompetencyController::class, 'store'])->name('positions.requirements.store');
    Route::put('positions/{position}/requirements/{requirement}',   [PositionCompetencyController::class, 'update'])->name('positions.requirements.update');
    Route::delete('positions/{position}/requirements/{requirement}',[PositionCompetencyController::class, 'destroy'])->name('positions.requirements.destroy');

    Route::get('assessments',               [EmployeeCompetencyController::class, 'index'])->name('assessments.index');
    Route::get('assessments/{employee}',    [EmployeeCompetencyController::class, 'employee'])->name('assessments.employee');
    Route::post('assessments/{employee}',   [EmployeeCompetencyController::class, 'upsert'])->name('assessments.upsert');

    Route::get('gap',                       [EmployeeCompetencyController::class, 'gap'])->name('gap');
    Route::get('gap/pdf',                   [EmployeeCompetencyController::class, 'gapPdf'])->name('gap.pdf');
});

// ── Career Management — permission career.view ─────────────────────────
Route::middleware(['auth', 'permission:career.view'])->prefix('career')->name('career.')->group(function () {
    Route::resource('paths', CareerPathController::class)->except(['show', 'create', 'edit']);
    Route::post('paths/{path}/steps',            [CareerPathController::class, 'storeStep'])->name('paths.steps.store');
    Route::delete('paths/{path}/steps/{step}',   [CareerPathController::class, 'destroyStep'])->name('paths.steps.destroy');

    Route::get('/',            [CareerController::class, 'index'])->name('index');
    Route::get('/{employee}',  [CareerController::class, 'show'])->name('show');
    Route::post('/{employee}/assign-path', [CareerController::class, 'assignPath'])->name('assign-path');
});

// ── Succession Planning — bagian Career Management, permission career.* ─────
Route::middleware(['auth', 'permission:career.view'])->prefix('succession')->name('succession.')->group(function () {
    Route::get('/',                       [SuccessionController::class, 'positions'])->name('positions');
    Route::get('/matrix',                 [SuccessionController::class, 'matrix'])->name('matrix');
    Route::put('/positions/{position}',   [SuccessionController::class, 'toggleCritical'])->name('positions.update');
    Route::get('/positions/{position}',   [SuccessionController::class, 'show'])->name('show');
    Route::post('/positions/{position}/pool', [SuccessionController::class, 'storePoolMember'])->name('pool.store');
    Route::put('/pool/{member}',          [SuccessionController::class, 'updatePoolMember'])->name('pool.update');
    Route::delete('/pool/{member}',       [SuccessionController::class, 'destroyPoolMember'])->name('pool.destroy');
    Route::get('/nine-box',               [SuccessionController::class, 'nineBox'])->name('nine-box');
    Route::put('/nine-box/{employee}/potential', [SuccessionController::class, 'updatePotential'])->name('potential.update');
    Route::get('/nine-box/{employee}/history',   [SuccessionController::class, 'potentialHistory'])->name('potential.history');
});

// ── Konfigurasi Penilaian Kinerja — permission appraisal-config.view ─────
Route::middleware(['auth', 'permission:appraisal-config.view'])->prefix('appraisal')->name('appraisal.')->group(function () {
    Route::resource('levels',       LevelController::class);
    Route::resource('templates',    TemplateController::class);
    Route::resource('periods',      PeriodController::class);
    Route::patch('periods/{period}/toggle', [PeriodController::class, 'toggle'])->name('periods.toggle');

    // OKR — Sasaran Perusahaan/Departemen (goal cascading)
    Route::prefix('okr')->name('okr.')->group(function () {
        Route::get('/',            [CompanyObjectiveController::class, 'index'])->name('index');
        Route::get('/create',      [CompanyObjectiveController::class, 'create'])->name('create');
        Route::post('/',           [CompanyObjectiveController::class, 'store'])->name('store');
        Route::get('/{okr}/edit',  [CompanyObjectiveController::class, 'edit'])->name('edit');
        Route::put('/{okr}',       [CompanyObjectiveController::class, 'update'])->name('update');
        Route::delete('/{okr}',    [CompanyObjectiveController::class, 'destroy'])->name('destroy');
    });

    // 360° Feedback — kelola cycle (HR)
    Route::prefix('feedback-360')->name('feedback360.')->group(function () {
        Route::get('/',                              [Feedback360Controller::class, 'index'])->name('index');
        Route::get('/create',                        [Feedback360Controller::class, 'create'])->name('create');
        Route::post('/',                              [Feedback360Controller::class, 'store'])->name('store');
        Route::get('/{cycle}',                        [Feedback360Controller::class, 'show'])->name('show');
        Route::post('/{cycle}/subjects',               [Feedback360Controller::class, 'addSubject'])->name('add-subject');
        Route::delete('/{cycle}/reviews/{review}',      [Feedback360Controller::class, 'removeReview'])->name('remove-review');
        Route::post('/{cycle}/open',                   [Feedback360Controller::class, 'openCycle'])->name('open');
        Route::post('/{cycle}/close',                  [Feedback360Controller::class, 'closeCycle'])->name('close');
        Route::get('/{cycle}/results/{employee}',       [Feedback360Controller::class, 'results'])->name('results');
    });
});

// ── 1-on-1 / Continuous Feedback — HR & manager (auth saja, cakupan diatur di controller) ──
Route::middleware('auth')->prefix('appraisal/checkins')->name('appraisal.checkins.')->group(function () {
    Route::get('/',                [PerformanceCheckinController::class, 'index'])->name('index');
    Route::get('/{employee}',      [PerformanceCheckinController::class, 'show'])->name('show');
    Route::post('/{employee}',     [PerformanceCheckinController::class, 'store'])->name('store');
    Route::delete('/entry/{checkin}', [PerformanceCheckinController::class, 'destroy'])->name('destroy');
    Route::post('/entry/{checkin}/comment', [PerformanceCheckinController::class, 'comment'])->name('comment');
});
