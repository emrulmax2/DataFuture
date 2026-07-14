@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

@php
    $privilegeEmployeeName = trim(implode(' ', array_filter([
        optional($employee->title)->name ?? null,
        $employee->first_name ?? null,
        $employee->last_name ?? null,
    ])));
    $privilegeEmployeeName = $privilegeEmployeeName ?: ($employee->full_name ?? 'Employee');
    $privilegeWorksNumber = $employment->works_number ?? null;
    $privilegeStatus = isset($employee->status) && $employee->status == 1 ? 'Active' : 'Inactive';
    $privilegeStatusClass = $privilegeStatus === 'Active' ? 'is-active' : 'is-inactive';
    $privilegeJobTitle = ($employment && $employment->employeeJobTitle) ? $employment->employeeJobTitle->name : null;
    $privilegeDepartment = ($employment && $employment->department) ? $employment->department->name : null;
    $privilegeLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->pluck('value', 'name')->toArray();
    $privilegeLogoUrl = (isset($privilegeLogo['site_logo']) && !empty($privilegeLogo['site_logo']) && Storage::disk('local')->exists('public/'.$privilegeLogo['site_logo']))
        ? Storage::disk('local')->url('public/'.$privilegeLogo['site_logo'])
        : asset('build/assets/images/L1_logo.svg');
    $privilegeAddressBits = [];
    if(isset($employee->address) && $employee->address) {
        foreach(['address_line_1', 'address_line_2', 'city', 'post_code', 'country'] as $addressKey) {
            if(!empty($employee->address->{$addressKey})) {
                $privilegeAddressBits[] = $employee->address->{$addressKey};
            }
        }
    }
    $privilegeAddress = implode(', ', $privilegeAddressBits);
@endphp

<div class="ep-grid ep-privilege-page">
    <div class="ep-col ep-privilege-shell">

    <div class="ep-privilege-print-root print-root" id="privilegePrintRoot" aria-hidden="true">
        <div class="ep-privilege-print-sheet a4">
            <div class="ep-privilege-print-header print-only">
                <div class="ep-privilege-print-header__top">
                    <span><strong>London Churchill College</strong> · Access Privileges Report</span>
                    <span>{{ $privilegeEmployeeName }}{{ $privilegeWorksNumber ? ' · Employee No. '.$privilegeWorksNumber : '' }}</span>
                </div>
                <div class="ep-privilege-print-header__brand-row">
                    <div class="ep-privilege-print-header__brand">
                        <div class="ep-privilege-print-header__logo-wrap">
                            <img src="{{ $privilegeLogoUrl }}" alt="London Churchill College crest" class="ep-privilege-print-header__logo">
                        </div>
                        <div class="ep-privilege-print-header__college">London<br>Churchill College</div>
                    </div>
                    <div class="ep-privilege-print-header__title-wrap">
                        <div class="ep-privilege-print-header__title">{{ $privilegeEmployeeName }}'s<br>Access Privileges</div>
                        <div class="ep-privilege-print-header__date">Date of issue: {{ now()->format('jS F, Y') }}</div>
                    </div>
                </div>
                <div class="ep-privilege-print-header__rule"></div>
                <div class="ep-privilege-print-info print-info">
                    <div class="ep-privilege-print-info__item">
                        <span>Employee</span>
                        <strong>{{ $privilegeEmployeeName }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Employee No.</span>
                        <strong>{{ $privilegeWorksNumber ?: '-' }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Status</span>
                        <strong class="{{ $privilegeStatusClass }}">{{ $privilegeStatus }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Job Title</span>
                        <strong>{{ $privilegeJobTitle ?: '-' }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Email</span>
                        <strong>{{ $employee->email ?: '-' }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Address</span>
                        <strong>{{ $privilegeAddress ?: '-' }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Department</span>
                        <strong>{{ $privilegeDepartment ?: '-' }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Mobile</span>
                        <strong>{{ $employee->mobile ?: ($employee->telephone ?: '-') }}</strong>
                    </div>
                    <div class="ep-privilege-print-info__item">
                        <span>Permissions Enabled</span>
                        <strong id="privilegePrintSummary">Loading privilege summary...</strong>
                    </div>
                </div>
            </div>
            <div class="ep-privilege-print-body priv-body">
                <div class="ep-privilege-print-cards cards-col" id="privilegePrintCards"></div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Permission Template Selector -->
    <div class="ep-privilege-toolbar ep-privilege-toolbar--template" aria-label="Permission template">
        <div class="ep-privilege-toolbar__summary">
            <div class="ep-privilege-toolbar__icon">
                <i data-lucide="layout-template" class="w-4 h-4"></i>
            </div>
            <div>
                <div class="ep-privilege-toolbar__title">Permission Template</div>
                <div class="ep-privilege-toolbar__meta">
                    @if($departments->isEmpty())
                        No department templates exist yet. Create one under Site Settings &rsaquo; Permissions.
                    @else
                        Loading a template replaces the permissions currently ticked below.
                    @endif
                </div>
            </div>
        </div>
        <div class="ep-privilege-toolbar__actions">
            <div class="ep-privilege-toolbar__select">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <select id="department_id_select" class="lccTom lcc-tom-select" name="department_id">
                    <option value="" selected>Please Select</option>
                    @foreach($departments as $department)
                        <option {{ ($department_id == $department->id ? 'selected' : '') }} value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" id="loadPermissionTemplateBtn" class="ep-privilege-btn ep-privilege-btn--primary hidden">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Load Template
                <svg class="hidden ml-2 theLoader" width="18" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </button>
        </div>
    </div>
    <!-- END: Permission Template Selector -->

    <div class="ep-privilege-toolbar" aria-label="Privilege actions">
        <div class="ep-privilege-toolbar__summary">
            <div class="ep-privilege-toolbar__icon">
                <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
            </div>
            <div>
                <div class="ep-privilege-toolbar__title">Access Privileges</div>
                <div class="ep-privilege-toolbar__meta" id="privilegeGlobalSummary">Loading privilege summary...</div>
            </div>
        </div>
        <div class="ep-privilege-toolbar__search">
            <i data-lucide="search" class="w-4 h-4"></i>
            <input type="search" id="privilegeSearchInput" placeholder="Filter privileges..." autocomplete="off">
        </div>
        <div class="ep-privilege-toolbar__actions">
            <button type="button" class="ep-privilege-btn ep-privilege-btn--ghost" id="privilegeExpandAll">Expand all</button>
            <button type="button" class="ep-privilege-btn ep-privilege-btn--ghost" id="privilegeCollapseAll">Collapse all</button>
            <button type="button" class="ep-privilege-btn ep-privilege-btn--ghost" id="privilegePrint">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print
            </button>
            <button type="button" class="ep-privilege-btn ep-privilege-btn--danger" id="privilegeResetBtn">
                <i data-lucide="shield-off" class="w-4 h-4"></i>
                Revoke All
                <svg class="hidden ml-2 theResetLoader" width="18" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </button>
            <button type="submit" form="employeePrivilegeForm" id="savePermissionBtn" class="ep-privilege-btn ep-privilege-btn--primary">
                <i data-lucide="save" class="w-4 h-4"></i>
                Save All Changes
                <svg class="hidden ml-2 theSaveLoader" width="18" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </button>
        </div>
    </div>

    <form method="post" action="#" id="employeePrivilegeForm" class="ep-privilege-form">
        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
        <div class="ep-privilege-layout">
            <nav class="ep-privilege-rail" id="employeePrivilegeRail" aria-label="Privilege groups">
                <div class="ep-privilege-rail__label">Privilege Groups</div>
                <div class="ep-privilege-rail__list"></div>
            </nav>
            <div class="ep-privilege-sections" id="permission-template-wrapper">
                {!! $permissionHtml !!}
            </div>
        </div>
    </form>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal ep-holiday-state-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="ep-holiday-state-modal__body">
                        <div class="ep-holiday-state-modal__icon">
                            <i data-lucide="check" class="w-10 h-10"></i>
                        </div>
                        <div class="ep-holiday-state-modal__title successModalTitle"></div>
                        <div class="ep-holiday-state-modal__desc successModalDesc"></div>
                    </div>
                    <div class="ep-holiday-state-modal__actions">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Revoke Confirmation Modal -->
    <div id="revokeConfirmModal" class="modal ep-holiday-state-modal ep-holiday-state-modal--warning" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="ep-holiday-state-modal__body">
                        <div class="ep-holiday-state-modal__icon">
                            <i data-lucide="shield-off" class="w-10 h-10"></i>
                        </div>
                        <div class="ep-holiday-state-modal__title">Revoke all permissions?</div>
                        <div class="ep-holiday-state-modal__desc">
                            This removes <strong>every</strong> permission from
                            <strong>{{ $privilegeEmployeeName }}</strong>.
                            They will lose access to everything the portal gates, immediately.
                            <br><br>
                            This cannot be undone from here &mdash; the permissions would have to be set again by hand,
                            or reloaded from a department template.
                            @if(auth()->id() === $employee->user_id)
                                <br><br>
                                <span class="text-danger font-medium">
                                    This is your own account. You will revoke your own access.
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="ep-holiday-state-modal__actions">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary mr-2">Cancel</button>
                        <button type="button" class="btn btn-danger" id="privilegeResetConfirmBtn">Yes, revoke everything</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Revoke Confirmation Modal -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal ep-holiday-state-modal ep-holiday-state-modal--warning" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="ep-holiday-state-modal__body">
                        <div class="ep-holiday-state-modal__icon">
                            <i data-lucide="alert-octagon" class="w-10 h-10"></i>
                        </div>
                        <div class="ep-holiday-state-modal__title warningModalTitle"></div>
                        <div class="ep-holiday-state-modal__desc warningModalDesc"></div>
                    </div>
                    <div class="ep-holiday-state-modal__actions">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    </div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-privilege-new.js')
@endsection
