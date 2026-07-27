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
    <div id="siteSettingsPage" class="ss-page ss-workplacement-page ss-wp-companies-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Workplacement</span>
            <i data-lucide="chevron-right"></i>
            <span>Workplacement Companies / Supervisor</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="building-2"></i>
                    </span>
                    <div>
                        <h1>Workplacement Companies / Supervisor</h1>
                        <p>Maintain the employers students are placed with, and the supervisors who sign off their hours.</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Dashboard
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
                    <div class="ss-table-card ss-wp-card">
                        <div class="ss-table-card__header">
                            <div class="ss-wp-card__heading">
                                <h2>Workplacement Companies / Supervisor</h2>
                                <span>{{ $companies->count() }} {{ Str::plural('company', $companies->count()) }}</span>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addWPCompanyModal" type="button" class="add_btn ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add Company
                            </button>
                        </div>

                        <div class="ss-wp-list">
                            <form id="searchForm" action="{{ route('workplacement.companies.search') }}" method="GET" class="ss-wp-search">
                                <i data-lucide="search"></i>
                                <input type="text" placeholder="Search company..." id="search" name="search" value="{{ request('search') }}" autocomplete="off">
                            </form>

                            <div class="companyListContainer ss-wp-company-list">
                                @include('pages.settings.workplacement.partials.company-list')
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('pages.settings.workplacement.wp-company-modal')
    </div>
@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/wp-company-supervisor.js')
@vite('resources/js/site-settings-redesign.js')
@endsection
