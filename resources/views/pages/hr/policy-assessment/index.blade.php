@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
    @vite('resources/css/policy-assessment-admin.css')
@endsection

@section('content')
    @php
        $paKpiCards = [
            ['icon' => 'users', 'label' => 'Staff with assignments', 'value' => number_format($kpis['staff']), 'hint' => 'Current staff only', 'tone' => 'navy'],
            ['icon' => 'clipboard-list', 'label' => 'Total assignments', 'value' => number_format($kpis['total']), 'hint' => 'Live policies only', 'tone' => 'navy'],
            ['icon' => 'badge-check', 'label' => 'Passed', 'value' => $kpis['passed_percent'].'%', 'hint' => number_format($kpis['passed']).' of '.number_format($kpis['total']).' passed', 'tone' => 'green'],
            ['icon' => 'alarm-clock', 'label' => 'Overdue', 'value' => number_format($kpis['overdue']), 'hint' => 'Past due and not passed', 'tone' => ($kpis['overdue'] > 0 ? 'red' : 'muted')],
            ['icon' => 'timer', 'label' => 'In progress', 'value' => number_format($kpis['in_progress']), 'hint' => 'Test started, not submitted', 'tone' => 'blue'],
            ['icon' => 'lock', 'label' => 'No attempts left', 'value' => number_format($kpis['locked']), 'hint' => 'Need HR to allow a retake', 'tone' => ($kpis['locked'] > 0 ? 'amber' : 'muted')],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page pa-page pa-results-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('hr.portal') }}">HR Portal</a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('policy.assessment') }}">Policy Assessments</a>
            <i data-lucide="chevron-right"></i>
            <span>Overview &amp; Results</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="clipboard-check"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>How staff are getting on with reading and passing the college policies.</p>
                    </div>
                </div>
                <a href="{{ route('hr.portal') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to HR Portal
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php
                        $settingsSidebarIcon = 'clipboard-check';
                        $settingsSidebarSubtitle = 'Policy assessments';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @include('pages.hr.policy-assessment.partials.nav', ['active' => 'results'])

                    <section class="pa-kpi-grid" aria-label="Headline numbers">
                        @foreach($paKpiCards as $paKpi)
                            <div class="pa-kpi pa-kpi--{{ $paKpi['tone'] }}">
                                <span class="pa-kpi__icon"><i data-lucide="{{ $paKpi['icon'] }}"></i></span>
                                <div class="pa-kpi__copy">
                                    <span class="pa-kpi__label">{{ $paKpi['label'] }}</span>
                                    <strong class="pa-kpi__value">{{ $paKpi['value'] }}</strong>
                                    <small class="pa-kpi__hint">{{ $paKpi['hint'] }}</small>
                                </div>
                            </div>
                        @endforeach
                        <div class="pa-kpi pa-kpi--badges">
                            <span class="pa-kpi__icon"><i data-lucide="award"></i></span>
                            <div class="pa-kpi__copy">
                                <span class="pa-kpi__label">Badges awarded</span>
                                <strong class="pa-kpi__value">{{ number_format($badgeKpi['total']) }}</strong>
                                <small class="pa-kpi__hint">Current staff, not revoked</small>
                            </div>
                            <ul class="pa-kpi-badges" aria-label="Badges awarded by level">
                                @foreach($badgeKpi['levels'] as $paKpiLevel => $paKpiCount)
                                    <li class="pa-kpi-badges__item pa-kpi-badges__item--{{ $paKpiLevel }}{{ $paKpiCount > 0 ? '' : ' is-zero' }}">
                                        @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paKpiLevel, 'size' => 'md', 'title' => null, 'date' => null, 'revoked' => false])
                                        <span class="pa-kpi-badges__copy">
                                            <strong>{{ number_format($paKpiCount) }}</strong>
                                            <small>{{ \App\Support\PolicyLevel::badgeName($paKpiLevel) }} &middot; {{ \App\Support\PolicyLevel::label($paKpiLevel) }}</small>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>

                    <div class="ss-table-card pa-category-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Completion by category</h2>
                                <p class="pa-card-subtitle">Passed assignments out of all assignments, current staff only.</p>
                            </div>
                        </div>
                        <div class="pa-category-bars">
                            @forelse($categoryBars as $paBar)
                                <div class="pa-category-bar">
                                    <div class="pa-category-bar__head">
                                        <strong>
                                            {{ $paBar['name'] }}
                                            @if(!$paBar['is_active'])
                                                <em class="pa-muted-tag">Inactive</em>
                                            @endif
                                        </strong>
                                        <span>
                                            @if($paBar['assigned'] > 0)
                                                {{ number_format($paBar['passed']) }} of {{ number_format($paBar['assigned']) }} passed
                                            @else
                                                Not assigned yet
                                            @endif
                                            &middot; {{ $paBar['policies'] }} {{ \Illuminate\Support\Str::plural('policy', $paBar['policies']) }}
                                        </span>
                                    </div>
                                    <div class="pa-category-bar__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $paBar['percent'] }}" aria-label="{{ $paBar['name'] }} completion">
                                        <span class="pa-category-bar__fill" style="width: {{ $paBar['percent'] }}%;"></span>
                                    </div>
                                    <span class="pa-category-bar__percent">{{ $paBar['assigned'] > 0 ? $paBar['percent'].'%' : '—' }}</span>
                                </div>
                            @empty
                                <p class="pa-empty-note">
                                    No categories yet.
                                    <a href="{{ route('policy.assessment.category') }}">Add a category</a> to get started.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="ss-table-card pa-results-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Staff results</h2>
                                <p class="pa-card-subtitle">Click a row to see that person's policies and review their attempts.</p>
                            </div>
                            <a href="{{ route('policy.assessment.assignment', ['assign' => 1]) }}" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Assign policies
                            </a>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter pa-filter-form">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Staff name...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Department</span>
                                    <label class="ss-filter-select pa-filter-select--narrow" for="filter_department">
                                        <select id="filter_department" name="department">
                                            <option value="">All</option>
                                            @foreach($departments as $paDepartment)
                                                <option value="{{ $paDepartment->id }}">{{ $paDepartment->name }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Staff</span>
                                    <label class="ss-filter-select" for="filter_staff">
                                        <select id="filter_staff" name="staff">
                                            <option value="current">Current staff</option>
                                            <option value="all">All staff</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="pa-filter-buttons">
                                    <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                    <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </div>
                            </form>

                            <div class="ss-table-actions">
                                <div class="dropdown ss-export-dropdown">
                                    <button type="button" class="dropdown-toggle ss-btn ss-btn--light ss-btn--tool" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="download"></i>
                                        Export
                                        <i data-lucide="chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu ss-export-menu">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-spreadsheet"></i>
                                                    Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ss-tabulator-wrap">
                            <div id="policyResultsTable" class="ss-tabulator table-report table-report--tabulator pa-tabulator pa-tabulator--clickable"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="employeeResultsModal" class="modal ss-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog pa-wide-modal-dialog">
                <div class="modal-content ss-settings-modal pa-detail-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <div class="pa-detail-modal__heading">
                                <h2 class="employeeResultsTitle">Staff results</h2>
                                <small class="employeeResultsSubtitle"></small>
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="modal-body ss-settings-modal__body">
                        <div class="employeeResultsBody pa-detail-modal__body"></div>
                    </div>
                    <div class="modal-footer ss-settings-modal__footer">
                        <a href="#" class="ss-btn ss-btn--light employeeResultsProfile">
                            <i data-lucide="user"></i>
                            Open profile tab
                        </a>
                        <a href="#" class="ss-btn ss-btn--light employeeResultsAssign">
                            <i data-lucide="plus"></i>
                            Assign more
                        </a>
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('pages.hr.policy-assessment.partials.results-attempt-modals', ['withAttemptsList' => false])

        <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal pa-warning-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="alert-octagon" class="ss-success-modal__icon"></i>
                            <div class="warningModalTitle"></div>
                            <p class="warningModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/policy-assessment-results.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
