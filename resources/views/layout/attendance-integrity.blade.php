{{--
    Layout for Attendance Integrity (/attendance-integrity): a standalone console with
    its own sidebar navigation and top bar. Selected by passing
    'layout' => 'attendance-integrity' from the controller (MenuComposer honours it),
    so no other screen changes.

    View data: $nav (grouped links the user may open), $pageTitle.
--}}
@extends('../layout/base')

@section('head')
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/attendance-integrity.css')
@endsection

@section('body')
    @php
        $aimUser = Auth::user();
        $aimEmployee = $aimUser ? $aimUser->loadMissing('employee')->employee : null;
        $aimName = trim(($aimEmployee->first_name ?? '').' '.($aimEmployee->last_name ?? '')) ?: ($aimUser->name ?? '');
        $aimRole = $aimEmployee?->employment?->employeeJobTitle?->name ?? '';
        $aimWords = preg_split('/\s+/', trim($aimName)) ?: ['A'];
        $aimInitials = strtoupper(mb_substr($aimWords[0] ?? 'A', 0, 1).mb_substr(count($aimWords) > 1 ? end($aimWords) : '', 0, 1));

        // Settings > Branding: the white wordmark reads on the navy sidebar.
        $aimLogo = null;
        foreach (App\Models\Option::where('category', 'SITE_SETTINGS')->whereIn('name', ['site_logo', 'site_dark_logo'])->pluck('value', 'name')->toArray() as $value) {
            if (!$aimLogo && !empty($value) && Storage::disk('local')->exists('public/'.$value)) {
                $aimLogo = Storage::disk('local')->url('public/'.$value);
            }
        }
        $aimHome = Route::has('staff.dashboard') ? route('staff.dashboard') : route('dashboard');
        $aimCurrent = Route::currentRouteName();
    @endphp
    <body class="aim-body">
        <div class="aim-app">
            <aside class="aim-side" id="aimSide" aria-label="Attendance Integrity">
                <div class="aim-side__brand">
                    @if($aimLogo)
                        <img src="{{ $aimLogo }}" alt="London Churchill College">
                    @else
                        <span class="aim-side__mark">LCC</span>
                    @endif
                </div>
                <div class="aim-side__title">
                    <strong>Attendance Integrity</strong>
                    <span>Monitoring &amp; investigations</span>
                </div>
                <nav class="aim-side__nav">
                    @foreach($nav ?? [] as $group => $links)
                        <div class="aim-side__group">{{ $group }}</div>
                        @foreach($links as $item)
                            @php $active = $aimCurrent === $item['route'] || str_starts_with((string) $aimCurrent, $item['route'].'.'); @endphp
                            <a href="{{ $item['url'] }}" class="aim-side__link {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
                                <i data-lucide="{{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    @endforeach
                </nav>
                <div class="aim-side__foot">
                    <a href="{{ $aimHome }}" class="aim-side__link"><i data-lucide="arrow-left"></i><span>Staff dashboard</span></a>
                </div>
            </aside>
            <button type="button" class="aim-side__scrim" data-aim-side-close aria-label="Close menu"></button>

            <div class="aim-main">
                <header class="aim-bar">
                    <button type="button" class="aim-bar__menu" data-aim-side-open aria-label="Open menu"><i data-lucide="menu"></i></button>
                    <nav class="aim-bar__crumbs" aria-label="Breadcrumb">
                        <a href="{{ $aimHome }}">Dashboard</a>
                        <i data-lucide="chevron-right"></i>
                        <a href="{{ route('attendance.integrity') }}">Attendance Integrity</a>
                        <i data-lucide="chevron-right"></i>
                        <span>{{ $pageTitle ?? '' }}</span>
                    </nav>
                    <div class="aim-bar__right">
                        <span class="aim-bar__time" data-aim-clock>{{ now('Europe/London')->format('D j M Y · H:i') }} UK</span>
                        <div class="aim-bar__user">
                            <span class="aim-avatar">{{ $aimInitials }}</span>
                            <span class="aim-bar__who"><strong>{{ $aimName }}</strong>@if($aimRole)<small>{{ $aimRole }}</small>@endif</span>
                        </div>
                    </div>
                </header>
                <main class="aim-content">
                    @yield('subcontent')
                </main>
            </div>
        </div>

        @vite('resources/js/app.js')
        @yield('script')
    </body>
@endsection
