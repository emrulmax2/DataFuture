@php
    $accountSiteLogo = \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $accountLogoUrl = ($accountSiteLogo && \Illuminate\Support\Facades\Storage::disk('local')->exists('public/'.$accountSiteLogo))
        ? \Illuminate\Support\Facades\Storage::disk('local')->url('public/'.$accountSiteLogo)
        : null;
    $accountUser = auth()->user();
    $accountEmployee = $employee ?? optional($accountUser)->employee;
    $accountTitle = isset($accountEmployee->title->name) ? $accountEmployee->title->name.' ' : '';
    $accountName = trim($accountTitle.($accountEmployee->first_name ?? '').' '.($accountEmployee->last_name ?? ''));
    $accountName = $accountName ?: (optional($accountUser)->name ?: optional($accountUser)->email ?: 'User');
    $accountEmail = optional($accountUser)->email ?: ($accountEmployee->email ?? '');
    $nameForInitials = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i', '', $accountName);
    $nameParts = preg_split('/\s+/', trim($nameForInitials), -1, PREG_SPLIT_NO_EMPTY);
    $firstInitial = isset($nameParts[0]) ? substr($nameParts[0], 0, 1) : 'U';
    $lastInitial = count($nameParts) > 1 ? substr($nameParts[count($nameParts) - 1], 0, 1) : '';
    $accountInitials = strtoupper($firstInitial.$lastInitial);
    // Employee::photo_url returns a real storage URL when a photo exists and a
    // generated data: SVG (initials) when it does not, so a "data:" prefix means
    // "no uploaded photo" — fall back to this header's own initials chip.
    $accountPhotoUrl = optional($accountEmployee)->photo_url;
    $accountHasPhoto = !empty($accountPhotoUrl) && !str_starts_with($accountPhotoUrl, 'data:');
    $routeUrl = function ($name, $fallback = 'javascript:void(0);') {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name) : $fallback;
    };
@endphp

<header class="my-account-topbar">
    <div class="my-account-topbar__primary">
        <a href="{{ url('/') }}" class="my-account-brand {{ $accountLogoUrl ? 'my-account-brand--logo' : '' }}" aria-label="London Churchill College dashboard">
            @if($accountLogoUrl)
                <img src="{{ $accountLogoUrl }}" alt="London Churchill College" class="my-account-brand__logo">
            @else
                <span class="my-account-brand__mark">LC</span>
                <span class="my-account-brand__text">LONDON<br>CHURCHILL COLLEGE</span>
            @endif
        </a>

        <nav class="my-account-breadcrumb" aria-label="breadcrumb">
            <span>User</span>
            <i data-lucide="chevron-right"></i>
            <a href="{{ url('/') }}">Dashboard</a>
            <i data-lucide="chevron-right"></i>
            <strong>My HR</strong>
        </nav>

        <div class="my-account-topbar__actions">
            @impersonating($guard=null)
                <a href="{{ route('impersonate.leave') }}" class="my-account-impersonate">
                    Leave impersonating
                    <i data-lucide="log-out"></i>
                </a>
            @endImpersonating

            <div class="dropdown">
                <button class="dropdown-toggle my-account-user" type="button" aria-expanded="false" data-tw-toggle="dropdown">
                    <span class="my-account-user__name">{{ $accountName }}</span>
                    @if($accountHasPhoto)
                        <span class="my-account-user__avatar my-account-user__avatar--photo">
                            <img src="{{ $accountPhotoUrl }}" alt="{{ $accountName }}">
                        </span>
                    @else
                        <span class="my-account-user__avatar">{{ $accountInitials }}</span>
                    @endif
                </button>
                <div class="dropdown-menu w-56">
                    <ul class="dropdown-content my-account-user-menu">
                        <li class="my-account-user-menu__head">
                            <div class="my-account-user-menu__name">{{ $accountName }}</div>
                            @if(!empty($accountEmail))
                                <div class="my-account-user-menu__email">{{ $accountEmail }}</div>
                            @endif
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="{{ route('user.account') }}" class="dropdown-item">
                                <i data-lucide="user"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="{{ route('logout') }}" class="dropdown-item">
                                <i data-lucide="toggle-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="my-account-topbar__gold" aria-hidden="true"></div>

    <nav class="my-account-main-nav" aria-label="Primary">
        <a href="{{ url('/') }}">
            <i data-lucide="home"></i>
            Dashboard
        </a>
        <a href="{{ $routeUrl('course.management') }}">
            <i data-lucide="book-open"></i>
            Courses Management
        </a>
        <div class="my-account-main-nav__group">
            <a href="{{ $routeUrl('student') }}">
                <i data-lucide="users"></i>
                Student Management
                <i data-lucide="chevron-down" class="my-account-main-nav__chev"></i>
            </a>
            <div class="my-account-main-nav-menu">
                <a href="{{ $routeUrl('admission') }}"><i data-lucide="file-plus-2"></i> Admission</a>
                <a href="{{ $routeUrl('student') }}"><i data-lucide="radio"></i> Live</a>
                <a href="{{ $routeUrl('agent.management') }}"><i data-lucide="headphones"></i> Agent Management</a>
            </div>
        </div>
        <a href="{{ $routeUrl('site.setting') }}">
            <i data-lucide="settings"></i>
            Settings
        </a>
    </nav>
</header>
