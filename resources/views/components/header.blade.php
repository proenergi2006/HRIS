@php
use App\Models\Appraisal\Appraisal;

$currentLocale = app()->getLocale();
$authUser = auth()->user();

// Notifikasi per role
$notifItems = collect();
if ($authUser) {
    if ($authUser->hasRole('user_ii')) {
        $notifItems = Appraisal::where('status', Appraisal::STATUS_SUBMITTED)
            ->with(['employee', 'period'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn($a) => [
                'icon'  => 'gd-clock',
                'color' => 'text-warning',
                'title' => __('notifications.pending_approval'),
                'body'  => __('notifications.pending_approval_body', [
                    'name'   => $a->employee->name,
                    'period' => $a->period->name,
                ]),
                'url'   => route('appraisal.appraisals.show', $a),
                'time'  => $a->updated_at->diffForHumans(),
            ]);
    } elseif ($authUser->hasAnyRole(['cfo', 'ceo'])) {
        $notifItems = Appraisal::where('status', Appraisal::STATUS_APPROVED_U2)
            ->with(['employee', 'period'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn($a) => [
                'icon'  => 'gd-arrow-circle-right',
                'color' => 'text-info',
                'title' => __('notifications.pending_final'),
                'body'  => __('notifications.pending_final_body', [
                    'name'   => $a->employee->name,
                    'period' => $a->period->name,
                ]),
                'url'   => route('appraisal.appraisals.show', $a),
                'time'  => $a->updated_at->diffForHumans(),
            ]);
    } elseif ($authUser->hasRole('evaluator')) {
        $notifItems = Appraisal::where('status', Appraisal::STATUS_REJECTED)
            ->where('evaluator_id', $authUser->id)
            ->with(['employee', 'period'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn($a) => [
                'icon'  => 'gd-alert',
                'color' => 'text-danger',
                'title' => __('notifications.appraisal_rejected'),
                'body'  => __('notifications.appraisal_rejected_body', [
                    'name'   => $a->employee->name,
                    'period' => $a->period->name,
                ]),
                'url'   => route('appraisal.appraisals.show', $a),
                'time'  => $a->updated_at->diffForHumans(),
            ]);
    } elseif ($authUser->hasRole('admin')) {
        $notifItems = Appraisal::where('status', Appraisal::STATUS_APPROVED_CFO)
            ->with(['employee', 'period'])
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn($a) => [
                'icon'  => 'gd-check',
                'color' => 'text-success',
                'title' => __('notifications.appraisal_final'),
                'body'  => __('notifications.appraisal_final_body', [
                    'name'   => $a->employee->name,
                    'period' => $a->period->name,
                ]),
                'url'   => route('appraisal.appraisals.show', $a),
                'time'  => $a->updated_at->diffForHumans(),
            ]);
    }
}
$notifCount = $notifItems->count();
@endphp

<!-- Header -->
<header class="header bg-body">
  <nav class="navbar flex-nowrap p-0">
    <div class="navbar-brand-wrapper d-flex align-items-center col-auto">
      <!-- Brand Mobile -->
      <a class="navbar-brand navbar-brand-mobile" href="{{ route('dashboard') }}"
         style="font-size:1.4rem;font-weight:900;letter-spacing:3px;color:#e8a020;text-decoration:none;">
        SI
      </a>

      <!-- Brand Desktop -->
      <a class="navbar-brand navbar-brand-desktop" href="{{ route('dashboard') }}"
         style="text-decoration:none;line-height:1;">
        <span class="side-nav-show-on-closed"
              style="font-size:1.4rem;font-weight:900;letter-spacing:3px;color:#e8a020;">SI</span>
        <span class="side-nav-hide-on-closed d-flex flex-column" style="line-height:1.2;">
          <span style="font-size:1.6rem;font-weight:900;letter-spacing:4px;color:#e8a020;">SIPRO</span>
          <span style="font-size:0.65rem;font-weight:600;letter-spacing:1.5px;color:rgba(255,255,255,0.55);text-transform:uppercase;">PT. Pro Energi</span>
        </span>
      </a>
    </div>

    <div class="header-content col px-md-3">
      <div class="d-flex align-items-center">
        <!-- Side Nav Toggle -->
        <a class="js-side-nav header-invoker d-flex mr-md-2" href="#"
           data-close-invoker="#sidebarClose"
           data-target="#sidebar"
           data-target-wrapper="body">
          <i class="gd-align-left"></i>
        </a>

        <div class="d-flex align-items-center ml-auto" style="gap:4px;">

          {{-- ── Language Switcher ── --}}
          <div class="dropdown">
            <a class="header-invoker d-flex align-items-center px-2" href="#"
               aria-haspopup="true" aria-expanded="false"
               data-unfold-event="click"
               data-unfold-target="#langMenu"
               data-unfold-type="css-animation"
               data-unfold-duration="200"
               data-unfold-animation-in="fadeIn"
               data-unfold-animation-out="fadeOut"
               title="{{ __('common.language') }}"
               style="font-size:0.75rem;font-weight:700;letter-spacing:1px;color:inherit;">
              {{ strtoupper($currentLocale) }}
              <i class="gd-angle-down ml-1" style="font-size:0.65rem;"></i>
            </a>
            <ul id="langMenu"
                class="unfold unfold-light unfold-top unfold-centered position-absolute pt-1 pb-1 mt-4 unfold-css-animation unfold-hidden fadeOut"
                style="min-width:100px;animation-duration:200ms;">
              <li class="unfold-item {{ $currentLocale === 'id' ? 'active' : '' }}">
                <a class="unfold-link d-flex align-items-center" href="{{ route('locale.switch', 'id') }}">
                  <span style="font-size:1rem;margin-right:6px;">🇮🇩</span> Indonesia
                  @if($currentLocale === 'id') <i class="gd-check ml-auto text-success" style="font-size:0.7rem;"></i> @endif
                </a>
              </li>
              <li class="unfold-item {{ $currentLocale === 'en' ? 'active' : '' }}">
                <a class="unfold-link d-flex align-items-center" href="{{ route('locale.switch', 'en') }}">
                  <span style="font-size:1rem;margin-right:6px;">🇬🇧</span> English
                  @if($currentLocale === 'en') <i class="gd-check ml-auto text-success" style="font-size:0.7rem;"></i> @endif
                </a>
              </li>
            </ul>
          </div>

          {{-- ── Notification Bell ── --}}
          <div class="dropdown">
            <a id="notificationsInvoker" class="header-invoker position-relative" href="#"
               aria-controls="notifications" aria-haspopup="true" aria-expanded="false"
               data-unfold-event="click"
               data-unfold-target="#notifications"
               data-unfold-type="css-animation"
               data-unfold-duration="300"
               data-unfold-animation-in="fadeIn"
               data-unfold-animation-out="fadeOut">
              <i class="gd-bell"></i>
              @if($notifCount > 0)
                <span style="
                  position:absolute;top:2px;right:2px;
                  background:#ef5b5b;color:#fff;
                  border-radius:50%;width:16px;height:16px;
                  font-size:0.6rem;font-weight:700;
                  display:flex;align-items:center;justify-content:center;
                  line-height:1;">{{ $notifCount > 9 ? '9+' : $notifCount }}</span>
              @endif
            </a>

            <div id="notifications"
                 class="dropdown-menu dropdown-menu-right py-0 mt-4 unfold-css-animation unfold-hidden"
                 aria-labelledby="notificationsInvoker"
                 style="animation-duration:300ms;width:340px;max-width:90vw;">
              <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center border-bottom py-3">
                  <h6 class="mb-0 font-weight-bold">{{ __('notifications.title') }}</h6>
                  @if($notifCount > 0)
                    <span class="badge badge-danger badge-pill ml-2">{{ $notifCount }}</span>
                  @endif
                </div>

                <div class="card-body p-0" style="max-height:320px;overflow-y:auto;">
                  @if($notifItems->isEmpty())
                    <div class="text-center text-muted py-4" style="font-size:0.85rem;">
                      <i class="gd-bell d-block mb-2" style="font-size:1.5rem;opacity:.3;"></i>
                      {{ __('notifications.no_notifications') }}
                    </div>
                  @else
                    <div class="list-group list-group-flush">
                      @foreach($notifItems as $notif)
                        <a href="{{ $notif['url'] }}"
                           class="list-group-item list-group-item-action py-3 px-3"
                           style="border-left:3px solid transparent;"
                           onmouseover="this.style.borderLeftColor='#265df1'"
                           onmouseout="this.style.borderLeftColor='transparent'">
                          <div class="d-flex align-items-start">
                            <div class="mr-3 mt-1">
                              <i class="{{ $notif['icon'] }} {{ $notif['color'] }}" style="font-size:1.1rem;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width:0;">
                              <div class="font-weight-bold" style="font-size:0.82rem;">{{ $notif['title'] }}</div>
                              <div class="text-muted" style="font-size:0.78rem;line-height:1.4;white-space:normal;">
                                {{ $notif['body'] }}
                              </div>
                              <div class="text-muted mt-1" style="font-size:0.72rem;">
                                <i class="gd-clock mr-1"></i>{{ $notif['time'] }}
                              </div>
                            </div>
                          </div>
                        </a>
                      @endforeach
                    </div>
                  @endif
                </div>

                <div class="card-footer py-2 text-center" style="font-size:0.8rem;">
                  <a href="{{ route('appraisal.appraisals.index') }}" class="text-primary font-weight-bold">
                    {{ __('notifications.view_all') }} &rarr;
                  </a>
                </div>
              </div>
            </div>
          </div>

          {{-- ── User Avatar ── --}}
          <div class="dropdown mx-2">
            <a id="profileMenuInvoker" class="header-complex-invoker" href="#"
               aria-controls="profileMenu" aria-haspopup="true" aria-expanded="false"
               data-unfold-event="click"
               data-unfold-target="#profileMenu"
               data-unfold-type="css-animation"
               data-unfold-duration="300"
               data-unfold-animation-in="fadeIn"
               data-unfold-animation-out="fadeOut">
              <span class="mr-md-2 avatar-placeholder">{{ substr(Auth::user()->name, 0, 1) }}</span>
              <span class="d-none d-md-block">{{ Auth::user()->name }}</span>
              <i class="gd-angle-down d-none d-md-block ml-2"></i>
            </a>

            <ul id="profileMenu"
                class="unfold unfold-user unfold-light unfold-top unfold-centered position-absolute pt-2 pb-1 mt-4 unfold-css-animation unfold-hidden fadeOut"
                aria-labelledby="profileMenuInvoker"
                style="animation-duration:300ms;">
              <li class="unfold-item">
                <a class="unfold-link d-flex align-items-center text-nowrap" href="{{ route('profile.edit') }}">
                  <span class="unfold-item-icon mr-3"><i class="gd-user"></i></span>
                  {{ __('common.profile') }}
                </a>
              </li>
              <li class="unfold-item unfold-item-has-divider">
                <a class="unfold-link d-flex align-items-center text-nowrap" href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                  <span class="unfold-item-icon mr-3"><i class="gd-power-off"></i></span>
                  {{ __('common.logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                  @csrf
                </form>
              </li>
            </ul>
          </div>

        </div>{{-- /d-flex ml-auto --}}
      </div>
    </div>
  </nav>
</header>
<!-- End Header -->
