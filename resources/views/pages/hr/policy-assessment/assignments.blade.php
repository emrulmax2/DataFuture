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
    <div id="siteSettingsPage" class="ss-page pa-page pa-assignments-page" data-auto-open-assign="{{ $autoOpenAssign }}" data-preselect-employee="{{ $preselectEmployee }}">
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
            <span>Assignments</span>
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
                        <p>See who has been asked to read each policy and meet its target, at which level and through which role, and chase anything outstanding.</p>
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
                    @include('pages.hr.policy-assessment.partials.nav', ['active' => 'assignments'])

                    <div class="ss-table-card pa-assignments-card">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Assignments</h2>
                                <p class="pa-card-subtitle">Every policy assigned to a member of staff, with where they have got to.</p>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#assignModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Assign policies
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter pa-filter-form">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Staff name or policy...">
                                    </label>
                                </div>
                                <div class="ss-filter-field pa-filter-employee">
                                    <span>Staff</span>
                                    <select id="filter_employee" name="employee" placeholder="All staff">
                                        <option value="">All staff</option>
                                        @foreach($filterEmployees as $filterEmployee)
                                            <option value="{{ $filterEmployee->id }}">{{ $filterEmployee->full_name }}{{ $filterEmployee->status == 1 ? '' : ' (inactive)' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Category</span>
                                    <label class="ss-filter-select" for="filter_category">
                                        <select id="filter_category" name="category">
                                            <option value="">All</option>
                                            @foreach($filterCategories as $filterCategory)
                                                <option value="{{ $filterCategory->id }}">{{ $filterCategory->name }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Policy</span>
                                    <label class="ss-filter-select pa-filter-select--wide" for="filter_policy">
                                        <select id="filter_policy" name="policy">
                                            <option value="">All</option>
                                            @foreach($allPolicies as $filterPolicy)
                                                <option value="{{ $filterPolicy->id }}">{{ $filterPolicy->title }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Role</span>
                                    <label class="ss-filter-select pa-filter-select--narrow" for="filter_role">
                                        <select id="filter_role" name="role">
                                            <option value="">All</option>
                                            @foreach($filterRoles as $filterRole)
                                                <option value="{{ $filterRole['id'] }}">{{ $filterRole['name'] }}</option>
                                            @endforeach
                                            <option value="{{ \App\Http\Controllers\HR\PolicyAssessment\PolicyAssignmentController::ROLE_FILTER_NONE }}">No role</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Level</span>
                                    <label class="ss-filter-select pa-filter-select--level" for="filter_level">
                                        <select id="filter_level" name="level">
                                            <option value="">All</option>
                                            @foreach($levels as $levelKey => $levelLabel)
                                                <option value="{{ $levelKey }}">{{ $levelLabel }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="">All</option>
                                            <option value="pending">Not started</option>
                                            <option value="in_progress">In progress</option>
                                            <option value="failed">Target not met</option>
                                            <option value="passed">Target met</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="locked">No attempts left</option>
                                            <option value="archived">Archived</option>
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
                            <div id="policyAssignmentTable" class="ss-tabulator table-report table-report--tabulator pa-tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        @include('pages.hr.policy-assessment.partials.assign-modal')

        <div id="editAssignmentModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="editAssignmentForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <div class="pa-detail-modal__heading">
                                    <h2>Edit assignment</h2>
                                    <small class="editAssignmentSubtitle"></small>
                                </div>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="edit_due_date">Due date <span>*</span></label>
                                <input id="edit_due_date" type="text" name="due_date" class="ss-modal-input datepicker due_date" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                                <div class="acc__input-error error-due_date"></div>
                            </div>
                            <div class="ss-modal-field">
                                <label for="edit_note">Note</label>
                                <textarea id="edit_note" name="note" rows="3" maxlength="1000" class="ss-modal-input ss-modal-textarea note" placeholder="Optional, for HR only"></textarea>
                                <div class="acc__input-error error-note"></div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateAssignment" class="ss-btn ss-btn--primary">
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                                <i data-lucide="check"></i>
                                Update
                            </button>
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('pages.hr.policy-assessment.partials.results-attempt-modals')

        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                            <div class="successModalTitle"></div>
                            <p class="successModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-confirm-modal__dialog">
                <div class="modal-content ss-confirm-modal">
                    <div class="ss-confirm-modal__hero">
                        <span><i data-lucide="alert-triangle"></i></span>
                        <h2 class="confModTitle">Are you sure?</h2>
                    </div>
                    <div class="ss-confirm-modal__body">
                        <p class="confModDesc"></p>
                    </div>
                    <div class="ss-confirm-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/policy-assessment-assignments.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
