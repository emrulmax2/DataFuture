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
    <div id="siteSettingsPage" class="ss-page pa-bank-page pa-roles-page">
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
            <span>Roles</span>
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
                        <p>Set up each job role once with the policies its staff must pass, then assign by role.</p>
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
                        $settingsSidebarSubtitle = 'Policy Assessments';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @include('pages.hr.policy-assessment.partials.nav', ['active' => 'roles'])

                    <div class="ss-table-card pa-bank-card pa-roles-card" id="policyRolesCard" data-has-roles="{{ $hasRoles ? 1 : 0 }}">
                        <div class="ss-table-card__header">
                            <div>
                                <h2>Roles</h2>
                                <p>A role is a job role, such as Lecturer, Admissions Officer or Finance, with the policies its staff must pass ticked.</p>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact" @if($policyTotal < 1) disabled title="Add a policy to the Question Bank first" @endif>
                                <i data-lucide="plus"></i>
                                Add Role
                            </button>
                        </div>

                        <div class="pa-role-help" role="note" aria-label="How roles work">
                            <span class="pa-role-help__icon"><i data-lucide="info"></i></span>
                            <ul>
                                <li>When you assign, pick the role and its policies are selected for you. You can still add or remove policies before assigning.</li>
                                <li><strong>Changing a role later does not change assessments that are already assigned.</strong></li>
                                <li>Only active policies are assigned. A ticked policy that is archived drops out of the role, and comes back if the policy is restored.</li>
                            </ul>
                        </div>

                        <div id="rolesEmptyState" class="pa-roles-empty" @if($hasRoles) hidden @endif>
                            <span class="pa-roles-empty__icon"><i data-lucide="briefcase"></i></span>
                            <h3>No roles yet</h3>
                            @if($policyTotal > 0)
                                <p>Create your first role, for example &ldquo;Lecturer&rdquo;, and tick the policies its staff must pass. From then on, assigning those policies is one pick.</p>
                                <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="ss-btn ss-btn--primary">
                                    <i data-lucide="plus"></i>
                                    Create the first role
                                </button>
                            @else
                                <p>Roles are made of policies, and there are no policies in the Question Bank yet. Add the policies first, then come back to create a role.</p>
                                <a href="{{ route('policy.assessment.policy') }}" class="ss-btn ss-btn--primary">
                                    <i data-lucide="library"></i>
                                    Go to the Question Bank
                                </a>
                            @endif
                        </div>

                        <div id="rolesTableArea" class="pa-roles-table-area" @if(!$hasRoles) hidden @endif>
                            <div class="ss-table-tools">
                                <form id="tabulatorFilterForm" class="ss-table-filter">
                                    <div class="ss-filter-field">
                                        <span>Query</span>
                                        <label class="ss-filter-input" for="query">
                                            <i data-lucide="search"></i>
                                            <input id="query" name="query" type="text" placeholder="Search roles...">
                                        </label>
                                    </div>
                                    <div class="ss-filter-field">
                                        <span>Status</span>
                                        <label class="ss-filter-select" for="status">
                                            <select id="status" name="status">
                                                <option value="3">All</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                                <option value="2">Archived</option>
                                            </select>
                                            <i data-lucide="chevron-down"></i>
                                        </label>
                                    </div>
                                    <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                    <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </form>

                                <div class="ss-table-actions">
                                    <button id="tabulator-print" type="button" class="ss-btn ss-btn--light ss-btn--tool">
                                        <i data-lucide="printer"></i>
                                        Print
                                    </button>
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
                                <div id="policyRoleTable" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide pa-modal-dialog--wide pa-role-modal-dialog">
                <form method="POST" action="#" id="addForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal pa-role-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Role</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            @include('pages.hr.policy-assessment.partials.role-fields', ['prefix' => 'add'])
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="save" class="ss-btn ss-btn--primary">
                                @include('pages.hr.policy-assessment.partials.bank-spinner')
                                <i data-lucide="check"></i>
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide pa-modal-dialog--wide pa-role-modal-dialog">
                <form method="POST" action="#" id="editForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal pa-role-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Role</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            @include('pages.hr.policy-assessment.partials.role-fields', ['prefix' => 'edit'])
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="update" class="ss-btn ss-btn--primary">
                                @include('pages.hr.policy-assessment.partials.bank-spinner')
                                <i data-lucide="check"></i>
                                Update
                            </button>
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('pages.hr.policy-assessment.partials.bank-modals')
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/policy-assessment-roles.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
