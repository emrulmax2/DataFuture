@php
    use App\Models\Option;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    /*
     |--------------------------------------------------------------------------
     | HR Dashboard Top Bar
     |--------------------------------------------------------------------------
     | Reusable brand + navigation + account bar for the HR dashboard (v2) pages.
     | Self-contained: computes the logo and the signed-in user's identity on its
     | own so any HR dashboard page can drop it in with a single @include.
     |
     | Optional param:
     |   $active  string  which nav item to highlight
     |                    (dashboard | courses | students | settings). Default: dashboard
     */

    $hrdActive = $active ?? 'dashboard';
    $hrdBreadcrumbs = $breadcrumbs ?? [];
    $hrdDashboardUrl = url('/');

    $hrdSiteLogo = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $hrdLogoUrl = ($hrdSiteLogo && Storage::disk('local')->exists('public/'.$hrdSiteLogo))
        ? Storage::disk('local')->url('public/'.$hrdSiteLogo)
        : null;

    $hrdEmployeeProfile = optional(auth()->user())->employee;
    $hrdUserName = trim(
        (optional(optional($hrdEmployeeProfile)->title)->name ? optional(optional($hrdEmployeeProfile)->title)->name.' ' : '').
        (optional($hrdEmployeeProfile)->first_name ?? '').' '.
        (optional($hrdEmployeeProfile)->last_name ?? '')
    );
    $hrdUserName = $hrdUserName !== '' ? $hrdUserName : (optional(auth()->user())->name ?? 'Super Admin');
    $hrdUserEmail = optional(auth()->user())->email ?? '';

    // The avatar (photo, or the initials chip fallback) is rendered by
    // pages.hr.portal.partials.avatar, which owns the initials/colour logic.

    $hrdRouteUrl = function ($name, $fallback = 'javascript:void(0);') {
        return Route::has($name) ? route($name) : $fallback;
    };
@endphp

<header class="hrd-topbar">
    <div class="hrd-topbar__inner">
        <a href="{{ route('hr.portal') }}" class="hrd-brand {{ $hrdLogoUrl ? 'hrd-brand--logo' : '' }}">
            @if($hrdLogoUrl)
                <img src="{{ $hrdLogoUrl }}" alt="London Churchill College" class="hrd-brand__logo">
            @else
                <span class="hrd-brand__mark">LC</span>
                <span class="hrd-brand__text">LONDON<br>CHURCHILL COLLEGE</span>
            @endif
        </a>

        <nav class="hrd-nav" aria-label="HR portal navigation">
            <a href="{{ $hrdDashboardUrl }}" class="hrd-nav__item {{ $hrdActive === 'dashboard' ? 'hrd-nav__item--active' : '' }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <a href="{{ $hrdRouteUrl('course.creation') }}" class="hrd-nav__item {{ $hrdActive === 'courses' ? 'hrd-nav__item--active' : '' }}">
                <i data-lucide="book-open"></i>
                Courses Management
            </a>
            <div class="hrd-nav__group">
                <a href="{{ $hrdRouteUrl('student') }}" class="hrd-nav__item {{ $hrdActive === 'students' ? 'hrd-nav__item--active' : '' }}">
                    <i data-lucide="users"></i>
                    Student Management
                    <i data-lucide="chevron-down" class="hrd-nav__chev"></i>
                </a>
                <div class="hrd-nav-menu">
                    <a href="{{ $hrdRouteUrl('admission') }}"><i data-lucide="file-plus-2"></i> Admission</a>
                    <a href="{{ $hrdRouteUrl('student') }}"><i data-lucide="radio"></i> Live</a>
                    <a href="{{ $hrdRouteUrl('agent.management') }}"><i data-lucide="headphones"></i> Agent Management</a>
                </div>
            </div>
            <a href="{{ $hrdRouteUrl('site.setting') }}" class="hrd-nav__item {{ $hrdActive === 'settings' ? 'hrd-nav__item--active' : '' }}">
                <i data-lucide="settings"></i>
                Settings
            </a>
        </nav>

        <div class="hrd-topbar__end">
        {{-- HR pages are behind the `web` guard, so only the default guard can be
             impersonated here (agent/applicant/student never reach this bar). --}}
        @impersonating($guard=null)
            <a href="{{ route('impersonate.leave') }}" class="hrd-impersonate">
                <i data-lucide="log-out"></i>
                <span>Leave Impersonate...</span>
            </a>
        @endImpersonating

        <div class="dropdown hrd-account">
            <button class="dropdown-toggle hrd-account__button" aria-expanded="false" data-tw-toggle="dropdown">
                <span>{{ $hrdUserName }}</span>
                @include('pages.hr.portal.partials.avatar', [
                    'name' => $hrdUserName,
                    'photoUrl' => optional($hrdEmployeeProfile)->photo_url,
                    'avatarSize' => 'md',
                ])
            </button>
            <div class="dropdown-menu hrd-account-menu">
                <div class="dropdown-content">
                    <div class="hrd-account-menu__head">
                        <strong>{{ $hrdUserName }}</strong>
                        <span>{{ $hrdUserEmail }}</span>
                    </div>
                    <a href="{{ route('user.account') }}" class="dropdown-item">
                        <i data-lucide="user"></i> Profile
                    </a>
                    <a href="{{ route('logout') }}" class="dropdown-item">
                        <i data-lucide="log-out"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        </div>
    </div>
</header>

<nav class="hrd-breadcrumb" aria-label="Breadcrumb">
    <div class="hrd-breadcrumb__inner">
        <a href="{{ $hrdDashboardUrl }}">Dashboard</a>
        @foreach($hrdBreadcrumbs as $crumb)
            <i data-lucide="chevron-right" class="hrd-breadcrumb__sep"></i>
            @if(!$loop->last && !empty($crumb['href']) && $crumb['href'] !== 'javascript:void(0);')
                <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
            @else
                <span class="hrd-breadcrumb__current" aria-current="page">{{ $crumb['label'] }}</span>
            @endif
        @endforeach
    </div>
</nav>
