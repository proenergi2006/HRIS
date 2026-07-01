@php
    use Illuminate\Support\Facades\Cache;

    $sidebarUser = auth()->user();
    $uid = $sidebarUser?->id;

    $pendingCount      = 0;
    $pendingBadgeClass = 'badge-warning';

    if ($sidebarUser?->hasRole('admin')) {
        $pendingCount = Cache::remember('sb_appraisal_admin', 60, fn() =>
            \App\Models\Appraisal\Appraisal::whereIn('status', ['submitted', 'approved_user2'])->count()
        );
        $pendingBadgeClass = 'badge-danger';

    } elseif ($sidebarUser?->hasRole('user_ii')) {
        $pendingCount = Cache::remember('sb_appraisal_user_ii', 60, fn() =>
            \App\Models\Appraisal\Appraisal::where('status', 'submitted')->count()
        );
        $pendingBadgeClass = 'badge-danger';

    } elseif ($sidebarUser?->hasAnyRole(['cfo','ceo'])) {
        $pendingCount = Cache::remember('sb_appraisal_cfo', 60, fn() =>
            \App\Models\Appraisal\Appraisal::where('status', 'approved_user2')->count()
        );
        $pendingBadgeClass = 'badge-danger';

    } elseif ($sidebarUser?->hasRole('evaluator')) {
        $pendingCount = Cache::remember("sb_appraisal_evaluator_{$uid}", 60, fn() =>
            \App\Models\Appraisal\Appraisal::where('status', 'rejected')
                ->where('evaluator_id', $uid)->count()
        );
        $pendingBadgeClass = 'badge-warning';
    }

    // Reimbursement counts
    $pendingReim = ($sidebarUser && !$sidebarUser->hasRole('admin_ga'))
        ? Cache::remember("sb_reim_user_{$uid}", 60, fn() =>
            \App\Models\Reimbursement\ReimbursementRequest::where('user_id', $uid)->where('status','submitted')->count()
          )
        : 0;

    $pendingAllReim = $sidebarUser?->hasRole('admin')
        ? Cache::remember('sb_reim_admin', 60, fn() =>
            \App\Models\Reimbursement\ReimbursementRequest::where('status','submitted')->count()
          )
        : 0;

    // Perdin — permohonan menunggu persetujuan user ini
    $pendingPerdin = 0;
    if ($sidebarUser && !$sidebarUser->hasRole('admin_ga')) {
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
            if ($sidebarUser->hasRole('hr_manager') || $sidebarUser->hasRole('admin')) {
                $count += \App\Models\Perdin\PerdinRequest::where('status', 'reviewed_manager')->count();
            }
            if ($sidebarUser->hasRole('ceo') || $sidebarUser->hasRole('admin')) {
                $count += \App\Models\Perdin\PerdinRequest::where('status', 'reviewed_hr')->count();
            }
            return $count;
        });
    }

    // Whistleblower (hanya admin)
    $newWb = $sidebarUser?->hasRole('admin')
        ? Cache::remember('sb_wb_new', 60, fn() =>
            \App\Models\WhistleblowerReport::where('status','new')->count()
          )
        : 0;

    // GA — kendaraan aktif
    $activeVehicles = $sidebarUser?->hasAnyRole(['admin_ga','admin'])
        ? Cache::remember('sb_ga_vehicles', 60, fn() =>
            \App\Models\GA\VehicleUsage::where('status','checked_in')->count()
          )
        : 0;
@endphp
<!-- Sidebar Nav -->
<aside id="sidebar" class="js-custom-scroll side-nav">
<ul id="sideNav" class="side-nav-menu side-nav-menu-top-level mb-0">

  <li class="sidebar-heading h6">{{ __('nav.home') }}</li>
  <li class="side-nav-menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('dashboard') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-dashboard"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.dashboard') }}</span>
    </a>
  </li>

  {{-- ── GA Module — admin_ga & admin ── --}}
  @if($sidebarUser?->hasAnyRole(['admin_ga','admin']))
  @php $gaActive = Request::is('admin/ga/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $gaActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subGA">
      {{-- Icon + mini badge (selalu terlihat) --}}
      <span class="side-nav-menu-icon d-flex mr-3 position-relative">
        <i class="gd-layout"></i>
        @if($activeVehicles > 0)
          <span class="sidebar-icon-badge badge-warning">{{ $activeVehicles > 9 ? '9+' : $activeVehicles }}</span>
        @endif
      </span>
      {{-- Label + badge (hilang saat sidebar compact) --}}
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.general_affairs') }}
        @if($activeVehicles > 0)
          <span class="badge badge-warning badge-pill ml-1" style="font-size:.7rem">{{ $activeVehicles }}</span>
        @endif
      </span>
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
    </ul>
  </li>
  @endif

  {{-- ── Appraisal + Reimbursement — semua kecuali admin_ga ── --}}
  @if(! $sidebarUser?->hasRole('admin_ga'))

  {{-- Penilaian Kinerja --}}
  @php $appraisalActive = Request::is('appraisal/*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $appraisalActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subAppraisal">
      {{-- Icon + mini badge --}}
      <span class="side-nav-menu-icon d-flex mr-3 position-relative">
        <i class="gd-check-box"></i>
        @if($pendingCount > 0)
          <span class="sidebar-icon-badge {{ $pendingBadgeClass === 'badge-danger' ? 'badge-danger' : 'badge-warning' }}">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
        @endif
      </span>
      {{-- Label + badge --}}
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.appraisal') }}
        @if($pendingCount > 0)
          <span class="badge {{ $pendingBadgeClass }} badge-pill ml-1" style="font-size:.7rem">{{ $pendingCount }}</span>
        @endif
      </span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subAppraisal" class="side-nav-menu side-nav-menu-second-level mb-0">
      @if(auth()->user()?->hasRole('admin'))
      <li class="side-nav-menu-item {{ Request::is('appraisal/employees*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.employees.index') }}"><i class="gd-user mr-2"></i>{{ __('nav.employees') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/levels*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.levels.index') }}"><i class="gd-layers mr-2"></i>{{ __('nav.job_levels') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/templates*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.templates.index') }}"><i class="gd-files mr-2"></i>{{ __('nav.templates') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/flow-configs*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.flow-configs.index') }}"><i class="gd-share-alt mr-2"></i>{{ __('nav.approval_flow') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/periods*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.periods.index') }}"><i class="gd-calendar mr-2"></i>{{ __('nav.periods') }}</a>
      </li>
      @endif
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
    </ul>
  </li>

  {{-- Reimbursement --}}
  @php
    $reimActive = Request::is('reimbursement*') || Request::is('admin/reimbursement*');
    $reimBadgeVal   = $pendingAllReim > 0 ? $pendingAllReim : ($pendingReim > 0 ? $pendingReim : 0);
    $reimBadgeColor = $pendingAllReim > 0 ? 'badge-danger' : 'badge-warning';
  @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $reimActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subReim">
      {{-- Icon + mini badge --}}
      <span class="side-nav-menu-icon d-flex mr-3 position-relative">
        <i class="gd-wallet"></i>
        @if($reimBadgeVal > 0)
          <span class="sidebar-icon-badge {{ $pendingAllReim > 0 ? 'badge-danger' : 'badge-warning' }}">{{ $reimBadgeVal > 9 ? '9+' : $reimBadgeVal }}</span>
        @endif
      </span>
      {{-- Label + badge --}}
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.reimbursement') }}
        @if($reimBadgeVal > 0)
          <span class="badge {{ $reimBadgeColor }} badge-pill ml-1" style="font-size:.7rem">{{ $reimBadgeVal }}</span>
        @endif
      </span>
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
      @if($sidebarUser?->hasRole('admin'))
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

  {{-- Perjalanan Dinas --}}
  @php
    $perdinActive = Request::is('perdin*') || Request::is('admin/perdin*');
  @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $perdinActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subPerdin">
      <span class="side-nav-menu-icon d-flex mr-3 position-relative">
        <i class="gd-briefcase"></i>
        @if($pendingPerdin > 0)
          <span class="sidebar-icon-badge badge-danger">{{ $pendingPerdin > 9 ? '9+' : $pendingPerdin }}</span>
        @endif
      </span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.perdin') }}
        @if($pendingPerdin > 0)
          <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $pendingPerdin }}</span>
        @endif
      </span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subPerdin" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('perdin') || Request::is('perdin/create') || Request::is('perdin/*') && !Request::is('perdin/approvals') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('perdin.index') }}"><i class="gd-file mr-2"></i>{{ __('nav.perdin_my') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('perdin/approvals') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('perdin.approvals.index') }}"><i class="gd-check mr-2"></i>{{ __('nav.perdin_approvals') }}
          @if($pendingPerdin > 0)
            <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $pendingPerdin }}</span>
          @endif
        </a>
      </li>
      @if($sidebarUser?->hasAnyRole(['admin','hr_manager']))
      <li class="side-nav-menu-item {{ Request::is('admin/perdin*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('perdin.admin.requests') }}"><i class="gd-list mr-2"></i>{{ __('nav.perdin_admin') }}</a>
      </li>
      @endif
    </ul>
  </li>

  @endif {{-- end !admin_ga --}}

  {{-- ── System — admin only (gabung Users + Whistleblower) ── --}}
  @if($sidebarUser?->hasRole('admin'))
  @php $systemActive = Request::is('users*') || Request::is('admin/whistleblower*') || Request::is('admin/activity-log*'); @endphp
  <li class="side-nav-menu-item side-nav-has-menu {{ $systemActive ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subSystem">
      {{-- Icon + mini badge --}}
      <span class="side-nav-menu-icon d-flex mr-3 position-relative">
        <i class="gd-settings"></i>
        @if($newWb > 0)
          <span class="sidebar-icon-badge badge-danger">{{ $newWb > 9 ? '9+' : $newWb }}</span>
        @endif
      </span>
      {{-- Label + badge --}}
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.system') }}
        @if($newWb > 0)
          <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $newWb }}</span>
        @endif
      </span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subSystem" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('users*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('user.index') }}"><i class="gd-user mr-2"></i>{{ __('nav.all_users') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/whistleblower*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('whistleblower.admin.index') }}"><i class="gd-announcement mr-2"></i>{{ __('nav.whistleblower') }}
          @if($newWb > 0)
            <span class="badge badge-danger badge-pill ml-1" style="font-size:.7rem">{{ $newWb }}</span>
          @endif
        </a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('admin/activity-log*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('admin.activity-log.index') }}">
          <i class="gd-list mr-2"></i>Activity Log
        </a>
      </li>
    </ul>
  </li>
  @endif

</ul>
</aside>
<!-- End Sidebar Nav -->
