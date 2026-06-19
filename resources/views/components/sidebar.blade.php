@php
    $sidebarUser = auth()->user();
    $pendingCount = 0;
    if ($sidebarUser?->hasRole('user_ii')) {
        $pendingCount = \App\Models\Appraisal\Appraisal::where('status', 'submitted')->count();
    } elseif ($sidebarUser?->hasAnyRole(['cfo','ceo'])) {
        $pendingCount = \App\Models\Appraisal\Appraisal::where('status', 'approved_user2')->count();
    } elseif ($sidebarUser?->hasRole('evaluator')) {
        $pendingCount = \App\Models\Appraisal\Appraisal::where('status', 'rejected')
            ->where('evaluator_id', $sidebarUser->id)->count();
    }
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

  {{-- ── GA Module — hanya admin_ga (dan admin) ── --}}
  @if($sidebarUser?->hasAnyRole(['admin_ga','admin']))
  <li class="sidebar-heading h6">General Affairs</li>
  <li class="side-nav-menu-item {{ Request::is('admin/ga/vehicles*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('ga.admin.vehicles.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-car"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Kendaraan</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('admin/ga/usages*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('ga.admin.usages.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-list"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Penggunaan
        @php $activeVehicles = \App\Models\GA\VehicleUsage::where('status','checked_in')->count(); @endphp
        @if($activeVehicles)
          <span class="badge badge-warning badge-pill ml-1" style="font-size:.7rem">{{ $activeVehicles }}</span>
        @endif
      </span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('admin/ga/rooms*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('ga.admin.rooms.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-layout"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Ruang Meeting</span>
    </a>
  </li>
  <li class="side-nav-menu-item {{ Request::is('admin/ga/cleaning-logs*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('ga.admin.cleaning-logs.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-check-box"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">Riwayat Kebersihan</span>
    </a>
  </li>
  @endif

  {{-- ── Appraisal Module — semua kecuali admin_ga ── --}}
  @if(! $sidebarUser?->hasRole('admin_ga'))
  <li class="sidebar-heading h6">{{ __('nav.appraisal') }}</li>

  @if(auth()->user()?->hasRole('admin'))
  {{-- Master Data — hanya admin HRD --}}
  <li class="side-nav-menu-item side-nav-has-menu {{ Request::is('appraisal/employees*','appraisal/levels*','appraisal/templates*','appraisal/flow-configs*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subMaster">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-settings"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.master_data') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subMaster" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('appraisal/employees*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.employees.index') }}">{{ __('nav.employees') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/levels*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.levels.index') }}">{{ __('nav.job_levels') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/templates*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.templates.index') }}">{{ __('nav.templates') }}</a>
      </li>
      <li class="side-nav-menu-item {{ Request::is('appraisal/flow-configs*') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('appraisal.flow-configs.index') }}">{{ __('nav.approval_flow') }}</a>
      </li>
    </ul>
  </li>

  {{-- Periode — hanya admin HRD --}}
  <li class="side-nav-menu-item {{ Request::is('appraisal/periods*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('appraisal.periods.index') }}">
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.periods') }}</span>
    </a>
  </li>
  @endif

  {{-- Data Penilaian — semua user --}}
  <li class="side-nav-menu-item {{ Request::is('appraisal/appraisals*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('appraisal.appraisals.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-check-box"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.appraisals') }}
        @if($pendingCount > 0)
          <span class="badge badge-warning badge-pill ml-1" style="font-size:0.7rem;">{{ $pendingCount }}</span>
        @endif
      </span>
    </a>
  </li>

  {{-- Laporan — semua user --}}
  <li class="side-nav-menu-item {{ Request::is('appraisal/report*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('appraisal.report.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-search"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.reports') }}</span>
    </a>
  </li>

  {{-- Whistleblower — admin only --}}
  @if(auth()->user()?->hasRole('admin'))
  <li class="sidebar-heading h6">{{ __('nav.hr_tools') }}</li>
  <li class="side-nav-menu-item {{ Request::is('admin/whistleblower*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="{{ route('whistleblower.admin.index') }}">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-alert"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.whistleblower') }}
        @php $newWb = \App\Models\WhistleblowerReport::where('status','new')->count(); @endphp
        @if($newWb > 0)
          <span class="badge badge-danger badge-pill ml-1" style="font-size:0.7rem;">{{ $newWb }}</span>
        @endif
      </span>
    </a>
  </li>
  @endif

  @endif {{-- end !admin_ga --}}

  @if(auth()->user()?->hasRole('admin'))
  <li class="sidebar-heading h6">{{ __('nav.system') }}</li>
  <li class="side-nav-menu-item side-nav-has-menu {{ Request::is('users*') ? 'active' : '' }}">
    <a class="side-nav-menu-link media align-items-center" href="#" data-target="#subUsers">
      <span class="side-nav-menu-icon d-flex mr-3"><i class="gd-user"></i></span>
      <span class="side-nav-fadeout-on-closed media-body">{{ __('nav.users') }}</span>
      <span class="side-nav-control-icon d-flex"><i class="gd-angle-right side-nav-fadeout-on-closed"></i></span>
      <span class="side-nav__indicator side-nav-fadeout-on-closed"></span>
    </a>
    <ul id="subUsers" class="side-nav-menu side-nav-menu-second-level mb-0">
      <li class="side-nav-menu-item {{ Request::is('users') ? 'active' : '' }}">
        <a class="side-nav-menu-link" href="{{ route('user.index') }}">{{ __('nav.all_users') }}</a>
      </li>
    </ul>
  </li>
  @endif

</ul>
</aside>
<!-- End Sidebar Nav -->
