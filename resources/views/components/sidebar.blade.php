@php
    use Illuminate\Support\Facades\Cache;

    $sidebarUser = auth()->user();
    $uid = $sidebarUser?->id;

    $pendingCount      = 0;
    $pendingBadgeClass = 'badge-warning';

    // Penilaian Kinerja sudah dimigrasi ke Approval Engine — badge admin/user_ii/cfo/ceo
    // dihitung dari step approval yang benar-benar bisa ditindak user ini (bukan status
    // mentah + role tetap lagi, karena approver appraisal sekarang dikonfigurasi dinamis
    // lewat Pengaturan Alur Persetujuan seperti modul lain).
    if ($sidebarUser?->hasRole('admin')) {
        $pendingCount = Cache::remember('sb_appraisal_admin', 60, fn() =>
            \App\Models\Appraisal\Appraisal::where('status', 'pending')->count()
        );
        $pendingBadgeClass = 'badge-danger';

    } elseif ($sidebarUser?->hasAnyRole(['hr_manager', 'user_ii', 'cfo', 'ceo'])) {
        $pendingCount = Cache::remember("sb_appraisal_approver_{$uid}", 60, function () use ($sidebarUser) {
            try {
                $engine = app(\App\Services\ApprovalEngine::class);
                return $engine->pendingStepsFor($sidebarUser)
                    ->whereHas('request', fn($q) => $q->where('transaction_type', 'appraisal'))
                    ->get()
                    ->filter(fn($s) => $engine->canActOn($s, $sidebarUser))
                    ->count();
            } catch (\Throwable $e) { return 0; }
        });
        $pendingBadgeClass = 'badge-danger';

    } elseif ($sidebarUser?->hasRole('evaluator')) {
        $pendingCount = Cache::remember("sb_appraisal_evaluator_{$uid}", 60, fn() =>
            \App\Models\Appraisal\Appraisal::where('status', 'rejected')
                ->where('evaluator_id', $uid)->count()
        );
        $pendingBadgeClass = 'badge-warning';
    }

    // Reimbursement counts
    $pendingReim = $sidebarUser?->can('reimbursement-participate.view')
        ? Cache::remember("sb_reim_user_{$uid}", 60, fn() =>
            \App\Models\Reimbursement\ReimbursementRequest::where('user_id', $uid)->where('status','pending')->count()
          )
        : 0;

    $pendingAllReim = $sidebarUser?->can('reimbursement-admin.view')
        ? Cache::remember('sb_reim_admin', 60, fn() =>
            \App\Models\Reimbursement\ReimbursementRequest::where('status','pending')->count()
          )
        : 0;

    // Perdin — permohonan menunggu persetujuan user ini
    $pendingPerdin = 0;
    if ($sidebarUser?->can('perdin-participate.view')) {
        $pendingPerdin = Cache::remember("sb_perdin_appr_{$uid}", 60, function () use ($sidebarUser, $uid) {
            $count = 0;
            // Sebagai atasan langsung: permohonan submitted dari bawahan
            $subUserIds = \App\Models\Employee::where('manager_id', function ($q) use ($uid) {
                $q->select('id')->from('employees')->where('user_id', $uid)->limit(1);
            })->pluck('user_id')->filter();
            if ($subUserIds->isNotEmpty()) {
                $count += \App\Models\Perdin\PerdinRequest::where('status', 'submitted')
                    ->whereIn('user_id', $subUserIds)->count();
            }
            // NB: 'reviewed_manager'/'reviewed_hr' adalah status Perdin LAMA dari sebelum
            // migrasi ke Approval Engine (kini draft/pending/approved/rejected/cancelled) —
            // baris ini sudah dead code (tidak ada lagi row berstatus itu), sengaja tidak
            // disentuh/dikonversi ke permission karena di luar scope migrasi Role & Permission.
            if ($sidebarUser->hasRole('hr_manager') || $sidebarUser->hasRole('admin')) {
                $count += \App\Models\Perdin\PerdinRequest::where('status', 'reviewed_manager')->count();
            }
            if ($sidebarUser->hasRole('ceo') || $sidebarUser->hasRole('admin')) {
                $count += \App\Models\Perdin\PerdinRequest::where('status', 'reviewed_hr')->count();
            }
            return $count;
        });
    }

    // Whistleblower (hanya yang punya akses whistleblower-admin)
    $newWb = $sidebarUser?->can('whistleblower-admin.view')
        ? Cache::remember('sb_wb_new', 60, fn() =>
            \App\Models\WhistleblowerReport::where('status','new')->count()
          )
        : 0;

    // GA — kendaraan aktif
    $activeVehicles = $sidebarUser?->can('ga.view')
        ? Cache::remember('sb_ga_vehicles', 60, fn() =>
            \App\Models\GA\VehicleUsage::where('status','checked_in')->count()
          )
        : 0;

    // Approval Engine — jumlah step yang menunggu tindakan user ini
    $inboxCount = Cache::remember('sb_approval_'.$uid, 60, function () use ($sidebarUser) {
        try { return app(\App\Services\ApprovalEngine::class)->pendingStepsFor($sidebarUser)->get()
            ->filter(fn ($s) => app(\App\Services\ApprovalEngine::class)->canActOn($s, $sidebarUser))->count();
        } catch (\Throwable $e) { return 0; }
    });

    $unreadAnnouncements = Cache::remember('sb_ann_'.$uid, 60, function () use ($sidebarUser) {
        try {
            return \App\Models\Announcement::published()->visibleTo($sidebarUser)
                ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $sidebarUser->id))->count();
        } catch (\Throwable $e) { return 0; }
    });

    // Onboarding Saya — tampil selama karyawan PUNYA task onboarding (bukan cuma
    // saat masih ada yang pending), supaya tetap bisa buka lagi materi yang sudah
    // dibaca / lihat progres setelah semua selesai. Badge cuma muncul kalau masih ada yg pending.
    $onboardingPending = 0;
    $hasOnboarding = false;
    if ($sidebarUser?->employee) {
        $onb = Cache::remember('sb_onb_'.$uid, 60, function () use ($sidebarUser) {
            try {
                $tasks = $sidebarUser->employee->onboardingTasks();
                return [
                    'total'   => (clone $tasks)->count(),
                    'pending' => (clone $tasks)->where('is_done', false)
                        ->whereHas('item', fn ($q) => $q->where('requires_acknowledgement', true))->count(),
                ];
            } catch (\Throwable $e) { return ['total' => 0, 'pending' => 0]; }
        });
        $hasOnboarding = $onb['total'] > 0;
        $onboardingPending = $onb['pending'];
    }

    // ── Kondisi tampil per-heading (heading disembunyikan kalau tidak ada item di bawahnya) ──
    $can = fn ($p) => (bool) $sidebarUser?->can($p);
    $showManajemenSdm = $can('employee-master.view') || $can('appraisal-config.view') || $can('org-structure.view')
        || $can('attendance.view') || $can('overtime.view') || $can('leave-admin.view') || $can('payroll.view')
        || $can('shift.view') || $can('offboarding.view') || $can('manpower-plan.view') || $can('recruitment.view')
        || $can('training.view') || $can('competency.view') || $can('career.view')
        || (bool) $sidebarUser?->hasRole('hr_manager');
    $showPengaturan = $can('master-data.view') || $can('user-management.view') || $can('roles-manage.view')
        || $can('whistleblower-admin.view') || $can('activity-log.view') || $can('announcement.edit') || $can('survey.edit');
@endphp
<!-- Sidebar Nav -->
<aside id="sidebar" class="js-custom-scroll side-nav">
<ul id="sideNav" class="side-nav-menu side-nav-menu-top-level mb-0">

  {{-- ══════════ UTAMA ══════════ --}}
  <li class="sidebar-heading h6">{{ __('nav.home') }}</li>
  <li class="side-nav-menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('dashboard') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-dashboard"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.dashboard') }}</span>
    </a>
  </li>

  {{-- ══════════ LAYANAN MANDIRI ══════════ --}}
  <li class="sidebar-heading h6 mt-3">Layanan Mandiri</li>

  {{-- Self-service — akun yang terhubung ke data karyawan (bukan permission modul). --}}
  @if($sidebarUser?->employee)
  @if($hasOnboarding || Request::is('onboarding-saya*'))
  <li class="side-nav-menu-item {{ Request::is('onboarding-saya*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('onboarding.mine') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-book"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Onboarding Saya
        @if($onboardingPending > 0)<span class="badge badge-warning ml-1">{{ $onboardingPending }}</span>@endif
      </span>
    </a>
  </li>
  @endif
  <li class="side-nav-menu-item {{ Request::is('my-payslips*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('payroll.my.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-wallet"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Slip Gaji Saya</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('my-total-rewards*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('payroll.my.rewards.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-bar-chart"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Total Rewards Saya</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('hr/overtime-requests*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('hr.overtime-requests.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-alarm-clock"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Pengajuan Lembur</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('appraisal/letter-requests*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('appraisal.letter-requests.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-receipt"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Permintaan Surat</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('appraisal/employee-data-changes*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('appraisal.employee-data-changes.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-write"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Perubahan Data</span>
    </a>
  </li>
  @endif

  {{-- Reimbursement — permission reimbursement-participate.view --}}
  @if($sidebarUser?->can('reimbursement-participate.view'))
  @php
    $reimActive = Request::is('reimbursement*') || Request::is('admin/reimbursement*');
  @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $reimActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subReim">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-wallet"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.reimbursement') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subReim" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('reimbursement*') && !Request::is('admin/reimbursement*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('reimbursement.index') }}"><i class="gd-heart mr-2"></i>{{ __('nav.medical_reimbursement') }}
          @if($pendingReim > 0)
            <span class="badge badge-warning badge-pill ml-1" style="font-size:.7rem">{{ $pendingReim }}</span>
          @endif
        </a>
      </li>
      @if($sidebarUser?->can('reimbursement-admin.view'))
      <li class="side-nav-menu-item {{ Request::is('admin/reimbursement*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('reimbursement.admin.index') }}"><i class="gd-list mr-2"></i>{{ __('nav.all_requests') }}
          @if($pendingAllReim > 0)
            <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $pendingAllReim }}</span>
          @endif
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  {{-- Perjalanan Dinas — permission perdin-participate.view --}}
  @if($sidebarUser?->can('perdin-participate.view'))
  @php
    $perdinActive = Request::is('perdin*') || Request::is('admin/perdin*');
  @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $perdinActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subPerdin">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-briefcase"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.perdin') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subPerdin" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('perdin') || Request::is('perdin/create') || Request::is('perdin/*') && !Request::is('perdin/approvals') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('perdin.index') }}"><i class="gd-file mr-2"></i>{{ __('nav.perdin_my') }}</a>
      </li>
      <li class="side-nav-menu-item">
        <a class="side-nav-menu-link" href="{{ route('approval.inbox.index') }}"><i class="gd-check mr-2"></i>{{ __('nav.perdin_approvals') }}
          @if($inboxCount > 0)
            <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $inboxCount }}</span>
          @endif
        </a>
      </li>
      @if($sidebarUser?->can('perdin-admin.view'))
      <li class="side-nav-menu-item {{ Request::is('admin/perdin*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('perdin.admin.requests') }}"><i class="gd-list mr-2"></i>{{ __('nav.perdin_admin') }}</a>
      </li>
      @endif
    </ul>
  </li>
  @endif {{-- end perdin-participate --}}

  {{-- Penilaian Kinerja — permission appraisal-participate.view --}}
  @if($sidebarUser?->can('appraisal-participate.view'))
  @php $appraisalActive = Request::is('appraisal/appraisals*') || Request::is('appraisal/report*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $appraisalActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subAppraisal">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-check-box"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.appraisal') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subAppraisal" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('appraisal/appraisals*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.appraisals.index') }}"><i class="gd-check-box mr-2"></i>{{ __('nav.appraisals') }}
          @if($pendingCount > 0)
            <span class="badge {{ $pendingBadgeClass }} badge-pill ml-1" style="font-size:.7rem">{{ $pendingCount }}</span>
          @endif
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/report*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.report.index') }}"><i class="gd-bar-chart mr-2"></i>{{ __('nav.reports') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('checkins-saya*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.checkins.mine') }}"><i class="gd-comment mr-2"></i>1-on-1 Saya</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('feedback-360-saya*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('feedback360.mine') }}"><i class="gd-loop mr-2"></i>360° Feedback Saya</a>
      </li>
    </ul>
  </li>
  @endif

  {{-- 1-on-1 Tim — siapa saja yang punya bawahan langsung --}}
  @if($sidebarUser?->employee?->subordinates()->exists())
  <li class="side-nav-menu-item {{ Request::is('appraisal/checkins*') && !Request::is('checkins-saya*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('appraisal.checkins.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-comment"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">1-on-1 Tim</span>
    </a>
  </li>
  @endif

  {{-- Pengumuman — semua user login --}}
  <li class="side-nav-menu-item {{ Request::is('pengumuman*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('announcements.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-announcement"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Pengumuman
        @if($unreadAnnouncements > 0)<span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $unreadAnnouncements }}</span>@endif
      </span>
    </a>
  </li>

  {{-- Survey — semua user login isi; kelola pakai permission survey.edit --}}
  <li class="side-nav-menu-item {{ Request::is('survey*') && !Request::is('admin/surveys*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('surveys.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-clipboard"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Survey</span>
    </a>
  </li>

  {{-- Kudos — apresiasi antar karyawan, semua user login --}}
  <li class="side-nav-menu-item {{ Request::is('kudos*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('kudos.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-heart"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Apresiasi / Kudos</span>
    </a>
  </li>

  {{-- ══════════ PERSETUJUAN ══════════ --}}
  <li class="sidebar-heading h6 mt-3">Persetujuan</li>

  {{-- Approval Engine --}}
  @php $approvalActive = Request::is('approval/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $approvalActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subApproval">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-check-box"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.approval') }}
        @if($inboxCount > 0)<span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $inboxCount }}</span>@endif
      </span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subApproval" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('approval/inbox*') || Request::is('approval/delegations*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('approval.inbox.index') }}"><i class="gd-check-box mr-2"></i>{{ __('nav.approval_inbox') }}
          @if($inboxCount > 0)<span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $inboxCount }}</span>@endif
        </a>
      </li>
      @if($sidebarUser?->can('approval-workflow.view'))
        <li class="side-nav-menu-item {{ Request::is('approval/workflows*') ? 'active' : '' }}">
          <a class="side-nav-menu-link" href="{{ route('approval.workflows.index') }}"><i class="gd-settings mr-2"></i>{{ __('nav.approval_setting') }}</a>
        </li>
      @endif
      @if($sidebarUser?->can('hr-request.view'))
        <li class="side-nav-menu-item {{ Request::is('approval/requests/reward*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('approval.hr-request.index', 'reward') }}"><i class="gd-star mr-2"></i>{{ __('nav.reward') }}</a></li>
        <li class="side-nav-menu-item {{ Request::is('approval/requests/punishment*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('approval.hr-request.index', 'punishment') }}"><i class="gd-alert mr-2"></i>{{ __('nav.punishment') }}</a></li>
        <li class="side-nav-menu-item {{ Request::is('approval/requests/promotion-rotation*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('approval.hr-request.index', 'promotion-rotation') }}"><i class="gd-stats-up mr-2"></i>{{ __('nav.promotion_rotation') }}</a></li>
        <li class="side-nav-menu-item {{ Request::is('approval/requests/termination*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('approval.hr-request.index', 'termination') }}"><i class="gd-power-off mr-2"></i>{{ __('nav.termination') }}</a></li>
      @endif
    </ul>
  </li>

  {{-- ══════════ MANAJEMEN SDM ══════════ --}}
  @if($showManajemenSdm)
  <li class="sidebar-heading h6 mt-3">Manajemen SDM</li>
  @endif

  {{-- Data Karyawan — permission employee-master.view / appraisal-config.view --}}
  @if($sidebarUser?->can('employee-master.view') || $sidebarUser?->can('appraisal-config.view'))
  @php $employeeDataActive = Request::is('appraisal/employees*') || Request::is('appraisal/levels*') || Request::is('appraisal/templates*') || Request::is('appraisal/periods*') || Request::is('appraisal/employee-letters*') || Request::is('appraisal/letter-templates*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $employeeDataActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subEmployeeData">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-user"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Data Karyawan</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subEmployeeData" class="side-nav-menu side-nav-menu-second-level mb-0">
      @if($sidebarUser?->can('employee-master.view'))
      <li class="side-nav-menu-item {{ Request::is('appraisal/employees*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.employees.index') }}"><i class="gd-user mr-2"></i>{{ __('nav.employees') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/employee-letters*') || Request::is('appraisal/letter-templates*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.employee-letters.index') }}"><i class="gd-receipt mr-2"></i>Surat</a>
      </li>
      @endif
      @if($sidebarUser?->can('appraisal-config.view'))
      <li class="side-nav-menu-item {{ Request::is('appraisal/levels*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.levels.index') }}"><i class="gd-layers mr-2"></i>{{ __('nav.job_levels') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/templates*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.templates.index') }}"><i class="gd-layout mr-2"></i>Template Penilaian</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/periods*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.periods.index') }}"><i class="gd-calendar mr-2"></i>Periode Penilaian</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/okr*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.okr.index') }}"><i class="gd-target mr-2"></i>OKR / Sasaran</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/feedback-360*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.feedback360.index') }}"><i class="gd-loop mr-2"></i>360° Feedback</a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  {{-- Struktur Organisasi — permission org-structure.view --}}
  @if($sidebarUser?->can('org-structure.view'))
  @php $orgActive = Request::is('appraisal/divisions*') || Request::is('appraisal/departments*') || Request::is('appraisal/sections*') || Request::is('appraisal/positions*') || Request::is('appraisal/org-chart*') || Request::is('appraisal/org-log*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $orgActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subOrg">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-layers-alt"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.organization') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subOrg" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('appraisal/divisions*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.divisions.index') }}"><i class="gd-layout mr-2"></i>{{ __('nav.divisions') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/departments*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.departments.index') }}"><i class="gd-layers mr-2"></i>{{ __('nav.departments') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/sections*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.sections.index') }}"><i class="gd-layers mr-2"></i>{{ __('nav.sections') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/positions*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.positions.index') }}"><i class="gd-id-badge mr-2"></i>{{ __('nav.positions') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/org-chart*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.org-chart.index') }}"><i class="gd-layers-alt mr-2"></i>{{ __('nav.org_chart') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/org-log*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.org-log.index') }}"><i class="gd-list mr-2"></i>{{ __('nav.org_history') }}</a>
      </li>
    </ul>
  </li>
  @endif

  {{-- HR: Absensi, Lembur, Cuti, Penggajian — permission per item --}}
  @if($sidebarUser?->can('attendance.view') || $sidebarUser?->can('overtime.view') || $sidebarUser?->can('leave-admin.view') || $sidebarUser?->can('payroll.view') || $sidebarUser?->can('shift.view') || $sidebarUser?->can('offboarding.view'))
  @php $hrActive = Request::is('hr/*') && !Request::is('hr/overtime-requests*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $hrActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subHR">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-user"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">HR Manajemen</span>
      <span class="side-nav-control-icon ml-auto"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
    </a>
    <ul class="side-nav-menu side-nav-menu-second-level collapse {{ $hrActive ? 'show' : '' }}" id="subHR">
      @can('attendance.view')
      <li class="side-nav-menu-item {{ Request::is('hr/attendance*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.attendance.index') }}">
          <i class="gd-calendar mr-2"></i>Absensi
        </a>
      </li>
      @endcan
      @can('overtime.view')
      <li class="side-nav-menu-item {{ Request::is('hr/overtime') || Request::is('hr/overtime/*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.overtime.index') }}">
          <i class="gd-alarm-clock mr-2"></i>Lembur
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/overtime-requests*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.overtime-requests.index') }}">
          <i class="gd-list mr-2"></i>Pengajuan Lembur
        </a>
      </li>
      @endcan
      @can('leave-admin.view')
      <li class="side-nav-menu-item {{ Request::is('hr/leave*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.leave.index') }}">
          <i class="gd-check mr-2"></i>Cuti
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/leave-policies*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.leave.policies.index') }}">
          <i class="gd-settings mr-2"></i>Kebijakan Cuti
        </a>
      </li>
      @endcan
      @can('shift.view')
      <li class="side-nav-menu-item {{ Request::is('hr/roster*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.roster.index') }}">
          <i class="gd-time mr-2"></i>Shift &amp; Roster
        </a>
      </li>
      @endcan
      @can('payroll.view')
      <li class="side-nav-menu-item {{ Request::is('hr/payroll*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.payroll.index') }}">
          <i class="gd-wallet mr-2"></i>Penggajian
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/loans*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.loans.index') }}">
          <i class="gd-briefcase mr-2"></i>Kasbon / Pinjaman
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/bonus*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.bonus.index') }}">
          <i class="gd-money mr-2"></i>Bonus / Insentif
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/thr*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.thr.index') }}">
          <i class="gd-star mr-2"></i>THR
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('hr/compensation*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.compensation.comparison') }}">
          <i class="gd-bar-chart mr-2"></i>Kompensasi &amp; Benchmark
        </a>
      </li>
      @endcan
      @can('offboarding.view')
      <li class="side-nav-menu-item {{ Request::is('hr/offboarding*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('hr.offboarding.index') }}">
          <i class="gd-power-off mr-2"></i>Clearance Resign
        </a>
      </li>
      @endcan
    </ul>
  </li>
  @endif

  {{-- Manpower Planning — permission manpower-plan.view --}}
  @if($sidebarUser?->can('manpower-plan.view'))
  <li class="side-nav-menu-item {{ Request::is('manpower/*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('manpower.plans.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-stats-up"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Manpower Planning</span>
    </a>
  </li>
  @endif

  {{-- Rekrutmen (Requisition, Kandidat, Onboarding) — permission recruitment.view --}}
  @if($sidebarUser?->can('recruitment.view'))
  @php $recruitmentActive = Request::is('recruitment/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $recruitmentActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subRecruitment">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-briefcase"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Rekrutmen</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subRecruitment" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('recruitment/requisitions*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('recruitment.requisitions.index') }}"><i class="gd-file mr-2"></i>Job Requisition</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('recruitment/candidates*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('recruitment.candidates.index') }}"><i class="gd-user mr-2"></i>Kandidat</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('recruitment/interview-calendar*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('recruitment.interview-calendar') }}"><i class="gd-calendar mr-2"></i>Kalender Interview</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('recruitment/onboarding*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('recruitment.onboarding.index') }}"><i class="gd-check-box mr-2"></i>Onboarding</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('recruitment/costs*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('recruitment.costs.index') }}"><i class="gd-wallet mr-2"></i>Biaya Rekrutmen</a>
      </li>
    </ul>
  </li>
  @endif

  {{-- Training & Development — permission training.view --}}
  @if($sidebarUser?->can('training.view'))
  <li class="side-nav-menu-item {{ Request::is('training/*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('training.participants.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-blackboard"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Training &amp; Development</span>
    </a>
  </li>
  @endif

  {{-- Competency Framework — permission competency.view --}}
  @if($sidebarUser?->can('competency.view'))
  @php $competencyActive = Request::is('competency/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $competencyActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subCompetency">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-target"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Competency Framework</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subCompetency" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('competency/dictionary*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('competency.dictionary.index') }}"><i class="gd-list mr-2"></i>Kamus Kompetensi</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('competency/positions*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('competency.positions.index') }}"><i class="gd-layers mr-2"></i>Profil Jabatan</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('competency/assessments*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('competency.assessments.index') }}"><i class="gd-check mr-2"></i>Penilaian Kompetensi</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('competency/gap*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('competency.gap') }}"><i class="gd-bar-chart mr-2"></i>Analisis Gap</a>
      </li>
    </ul>
  </li>
  @endif

  {{-- Career Management — permission career.view --}}
  @if($sidebarUser?->can('career.view'))
  <li class="side-nav-menu-item {{ Request::is('career*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('career.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-stats-up"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Career Management</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('succession*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('succession.positions') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-target"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Succession Planning</span>
    </a>
  </li>
  @endif

  {{-- Laporan & Export --}}
  {{-- SENGAJA TETAP literal hasRole('hr_manager') — admin memang disembunyikan dari
       menu ini sejak awal, bukan bagian dari migrasi permission. --}}
  @if($sidebarUser?->hasRole('hr_manager'))
  <li class="side-nav-menu-item {{ Request::is('admin/laporan*') ? 'active' : '' }}">
    <a class="side-nav-menu-link" href="{{ route('laporan.index') }}">
      <span class="side-nav-menu-icon mr-3"><i class="gd-bar-chart"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Laporan &amp; Export</span>
    </a>
  </li>
  @endif

  {{-- ══════════ GENERAL AFFAIRS ══════════ --}}
  @if($sidebarUser?->can('ga.view'))
  <li class="sidebar-heading h6 mt-3">General Affairs</li>
  @php $gaActive = Request::is('admin/ga/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $gaActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subGA">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-layout"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.general_affairs') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subGA" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('admin/ga/vehicles*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.vehicles.index') }}">
          <i class="gd-car mr-2"></i>{{ __('nav.vehicles') }}
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/usages*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.usages.index') }}">
          <i class="gd-list mr-2"></i>{{ __('nav.vehicle_usage') }}
          @if($activeVehicles > 0)
            <span class="badge badge-warning badge-pill ml-1" style="font-size:.7rem">{{ $activeVehicles }}</span>
          @endif
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/rooms*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.rooms.index') }}">
          <i class="gd-layout mr-2"></i>{{ __('nav.meeting_rooms') }}
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/cleaning-logs*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.cleaning-logs.index') }}">
          <i class="gd-check-box mr-2"></i>{{ __('nav.cleaning_history') }}
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/vaults*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.vaults.index') }}">
          <i class="gd-lock mr-2"></i>{{ __('nav.vaults') }}
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/vault-documents*') || Request::is('admin/ga/vault-categories*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.vault-documents.index') }}">
          <i class="gd-file mr-2"></i>{{ __('nav.vault_documents') }}
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/ga/vault-transactions*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('ga.admin.vault-transactions.index') }}">
          <i class="gd-list mr-2"></i>{{ __('nav.vault_transactions') }}
        </a>
      </li>
    </ul>
  </li>
  @endif

  {{-- ══════════ PENGATURAN ══════════ --}}
  @if($showPengaturan)
  <li class="sidebar-heading h6 mt-3">Pengaturan</li>
  @endif

  {{-- Master Data — permission master-data.view --}}
  @if($sidebarUser?->can('master-data.view'))
  @php $masterActive = Request::is('master/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $masterActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subMaster">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-settings"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.master_data') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subMaster" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('master/religions*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.religions.index') }}"><i class="gd-world mr-2"></i>{{ __('nav.m_religion') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/education-levels*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.education-levels.index') }}"><i class="gd-blackboard mr-2"></i>{{ __('nav.m_edu_level') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/education-majors*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.education-majors.index') }}"><i class="gd-book mr-2"></i>{{ __('nav.m_edu_major') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/marital-statuses*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.marital-statuses.index') }}"><i class="gd-heart mr-2"></i>{{ __('nav.m_marital') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/blood-types*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.blood-types.index') }}"><i class="gd-heart mr-2"></i>{{ __('nav.m_blood') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/employee-types*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.employee-types.index') }}"><i class="gd-user mr-2"></i>{{ __('nav.m_emp_type') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/banks*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.banks.index') }}"><i class="gd-wallet mr-2"></i>{{ __('nav.m_bank') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/company-banks*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.company-banks.index') }}"><i class="gd-briefcase mr-2"></i>{{ __('nav.m_company_bank') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/regions*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.regions.index') }}"><i class="gd-location-pin mr-2"></i>{{ __('nav.m_region') }}</a></li>
      <li class="side-nav-menu-item {{ Request::is('master/ter-brackets*') ? 'active' : '' }}"><a class="side-nav-menu-link" href="{{ route('master.ter-brackets.index') }}"><i class="gd-receipt mr-2"></i>Tarif PPh21 (TER)</a></li>
    </ul>
  </li>
  @endif

  {{-- Sistem (Users, Role & Hak Akses, Whistleblower, Activity Log, Kelola Pengumuman/Survey) --}}
  @if($sidebarUser?->can('user-management.view') || $sidebarUser?->can('roles-manage.view') || $sidebarUser?->can('whistleblower-admin.view') || $sidebarUser?->can('activity-log.view') || $sidebarUser?->can('announcement.edit') || $sidebarUser?->can('survey.edit'))
  @php $systemActive = Request::is('users*') || Request::is('admin/roles*') || Request::is('admin/whistleblower*') || Request::is('admin/activity-log*') || Request::is('pengumuman/kelola*') || Request::is('admin/surveys*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $systemActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subSystem">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-settings"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.system') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subSystem" class="side-nav-menu side-nav-menu-second-level mb-0">
      @if($sidebarUser?->can('user-management.view'))
      <li class="side-nav-menu-item {{ Request::is('users*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('user.index') }}"><i class="gd-user mr-2"></i>{{ __('nav.all_users') }}</a>
      </li>
      @endif
      @if($sidebarUser?->can('roles-manage.view'))
      <li class="side-nav-menu-item {{ Request::is('admin/roles*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('admin.roles.index') }}"><i class="gd-lock mr-2"></i>Role & Hak Akses</a>
      </li>
      @endif
      @if($sidebarUser?->can('whistleblower-admin.view'))
      <li class="side-nav-menu-item {{ Request::is('admin/whistleblower*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('whistleblower.admin.index') }}"><i class="gd-announcement mr-2"></i>{{ __('nav.whistleblower') }}
          @if($newWb > 0)
            <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $newWb }}</span>
          @endif
        </a>
      </li>
      @endif
      @if($sidebarUser?->can('activity-log.view'))
      <li class="side-nav-menu-item {{ Request::is('admin/activity-log*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('admin.activity-log.index') }}">
          <i class="gd-list mr-2"></i>Activity Log
        </a>
      </li>
      @endif
      @if($sidebarUser?->can('announcement.edit'))
      <li class="side-nav-menu-item {{ Request::is('pengumuman/kelola*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('announcements.manage') }}">
          <i class="gd-announcement mr-2"></i>Kelola Pengumuman
        </a>
      </li>
      @endif
      @if($sidebarUser?->can('survey.edit'))
      <li class="side-nav-menu-item {{ Request::is('admin/surveys*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('surveys.manage.index') }}">
          <i class="gd-clipboard mr-2"></i>Kelola Survey
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

</ul>
</aside>
<!-- End Sidebar Nav -->
