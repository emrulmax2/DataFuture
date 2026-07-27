@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
    @php
        $yearName = $academicyear?->name ?? 'Academic Year';
        $fromDate = $academicyear?->from_date ? date('d-m-Y', strtotime($academicyear->from_date)) : '-';
        $toDate = $academicyear?->to_date ? date('d-m-Y', strtotime($academicyear->to_date)) : '-';
    @endphp

    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Course Parameters</span>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('academicyears') }}">Academic Years</a>
            <i data-lucide="chevron-right"></i>
            <span>{{ $yearName }}</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="calendar-range"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage academic years, bank holidays, term types, assessments and awarding bodies.</p>
                    </div>
                </div>
                <a href="{{ route('academicyears') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back To Academic Years
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @if(isset(auth()->user()->priv()['course_parameters']) && auth()->user()->priv()['course_parameters'] == 1)
                        @if($academicyear)
                            <div class="ss-year-detail-card">
                                <div class="ss-year-detail-card__hero">
                                    <div class="ss-year-detail-card__identity">
                                        <span class="ss-year-detail-card__icon">
                                            <i data-lucide="calendar-days"></i>
                                        </span>
                                        <div>
                                            <span>Academic Year</span>
                                            <h2>{{ $yearName }}</h2>
                                        </div>
                                    </div>
                                    <div class="ss-year-detail-card__dates">
                                        <div class="ss-year-date">
                                            <span><i data-lucide="calendar"></i></span>
                                            <div>
                                                <small>From Date</small>
                                                <strong>{{ $fromDate }}</strong>
                                            </div>
                                        </div>
                                        <div class="ss-year-date">
                                            <span><i data-lucide="calendar-check"></i></span>
                                            <div>
                                                <small>To Date</small>
                                                <strong>{{ $toDate }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="ss-year-detail-card__tabs">
                                    <button id="bankholiday-tab" type="button" class="ss-year-tab is-active">
                                        <i data-lucide="calendar-days"></i>
                                        Bank Holidays
                                    </button>
                                </div>
                            </div>

                            <div id="bankholiday" role="tabpanel" aria-labelledby="bankholiday-tab">
                                @include('pages.settings.academicyears.details.bankholiday')
                            </div>

                            @include('pages.settings.academicyears.details.bankholiday-modal')
                        @else
                            <div class="ss-empty-state" role="alert">
                                <span><i data-lucide="alert-triangle"></i></span>
                                <div>
                                    <h2>Academic Year Not Found</h2>
                                    <p>The selected academic year could not be found. Please return to the academic years list and choose another record.</p>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="ss-empty-state" role="alert">
                            <span><i data-lucide="alert-triangle"></i></span>
                            <div>
                                <h2>Permission Required</h2>
                                <p>You do not have enough permission to view this page's content. Please navigate to the menus on the left.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/bankholiday.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
