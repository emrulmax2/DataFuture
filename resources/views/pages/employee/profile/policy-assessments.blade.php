@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }} </title>
@endsection

@section('styles')
    @vite('resources/css/policy-assessment-admin.css')
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid ep-doc-page pa-ep-page">
    <div class="ep-col">
        <div class="ep-doc-shell">

            <!-- ============= POLICY ASSESSMENTS ============= -->
            <section class="ep-doc-card ep-doc-card--accent-teal">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Policy Assessments</h2>
                            <p id="employeePolicySummary-PA" class="ep-doc-card__meta">Policies this member of staff must read and pass.</p>
                            <div id="policySummaryChips-PA" class="pa-ep-chips">
                                <span class="pa-ep-chip"><strong data-pa-count="assigned">{{ $summary['assigned'] }}</strong> assigned</span>
                                <span class="pa-ep-chip pa-ep-chip--green"><strong data-pa-count="passed">{{ $summary['passed'] }}</strong> passed</span>
                                <span class="pa-ep-chip pa-ep-chip--red"><strong data-pa-count="overdue">{{ $summary['overdue'] }}</strong> overdue</span>
                                <span class="pa-ep-chip pa-ep-chip--blue"><strong data-pa-count="in_progress">{{ $summary['in_progress'] }}</strong> in progress</span>
                                <span class="pa-ep-chip pa-ep-chip--amber"><strong data-pa-count="locked">{{ $summary['locked'] }}</strong> no attempts left</span>
                                <span class="pa-ep-chip pa-ep-chip--gold"><strong data-pa-badge-count="total">{{ $summary['badges']['total'] }}</strong> {{ $summary['badges']['total'] == 1 ? 'badge' : 'badges' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="ep-doc-card__head-actions">
                        <button data-tw-toggle="modal" data-tw-target="#assignPolicyModal-PA" type="button" class="ep-doc-btn ep-doc-btn--soft">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Assign policies
                        </button>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <!-- Badges: drawn here first; the tab's JS redraws them from policy.assessment.badge.list after a revoke / restore or the Show revoked switch. -->
                    <section id="policyBadges-PA" class="pa-ep-badges" data-employee="{{ $employee->id }}" aria-labelledby="policyBadgesTitle-PA">
                        <div class="pa-ep-badges__head">
                            <div class="pa-ep-badges__heading">
                                <h3 id="policyBadgesTitle-PA" class="pa-ep-badges__title">
                                    <i data-lucide="award" class="w-4 h-4"></i>
                                    Badges
                                </h3>
                                <span class="pa-badge-counts" data-badge-counts role="img" aria-label="{{ $summary['badges']['total'] }} {{ $summary['badges']['total'] == 1 ? 'badge' : 'badges' }}: {{ $summary['badges']['beginner'] }} bronze, {{ $summary['badges']['intermediate'] }} silver, {{ $summary['badges']['expert'] }} gold">
                                    @foreach(\App\Support\PolicyLevel::all() as $paCountLevel)
                                        <span class="pa-badge-count pa-badge-count--{{ $paCountLevel }}{{ $summary['badges'][$paCountLevel] > 0 ? '' : ' is-zero' }}" title="{{ \App\Support\PolicyLevel::badgeName($paCountLevel) }} ({{ \App\Support\PolicyLevel::label($paCountLevel) }})">@include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paCountLevel, 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])<strong>{{ $summary['badges'][$paCountLevel] }}</strong></span>
                                    @endforeach
                                </span>
                            </div>
                            <div class="ep-doc-switch pa-ep-badges__switch">
                                <label class="form-check form-switch mb-0" for="showRevoked-PA">
                                    <input id="showRevoked-PA" class="form-check-input" type="checkbox" autocomplete="off">
                                    <span class="ep-doc-switch__label">Show revoked</span>
                                </label>
                            </div>
                        </div>
                        <ul class="pa-ep-badges__grid" data-badge-grid>
                            @foreach($badges as $paBadge)
                                @php
                                    $paBadgeScore = ($paBadge->score !== null ? (float) $paBadge->score : null);
                                    $paBadgeScoreText = ($paBadgeScore === null ? '' : (floor($paBadgeScore) == $paBadgeScore ? (string) (int) $paBadgeScore : rtrim(rtrim(number_format($paBadgeScore, 2, '.', ''), '0'), '.')).'%');
                                @endphp
                                <li class="pa-ep-badge pa-ep-badge--{{ \App\Support\PolicyLevel::isValid($paBadge->level) ? $paBadge->level : 'beginner' }}" data-badge-id="{{ $paBadge->id }}">@include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paBadge->level, 'size' => 'md', 'title' => (isset($paBadge->policy->title) ? $paBadge->policy->title : 'Deleted policy'), 'date' => ($paBadge->awarded_at ? $paBadge->awarded_at->format('d M Y') : null), 'revoked' => false])<span class="pa-ep-badge__meta">{{ $paBadgeScoreText !== '' ? 'Scored '.$paBadgeScoreText : 'Awarded' }}</span><button type="button" class="pa-ep-badge__btn pa-ep-badge__btn--revoke revoke_badge_btn" data-id="{{ $paBadge->id }}"><i data-lucide="ban" class="w-4 h-4"></i>Revoke</button></li>
                            @endforeach
                        </ul>
                        <p class="pa-ep-badges__empty" data-badge-empty{{ $badges->count() > 0 ? ' hidden' : '' }}>No badges yet. A badge is awarded the first time they pass a policy test.</p>
                    </section>

                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm-PA" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="level-PA">Level</label>
                                <select id="level-PA" name="level" class="form-select">
                                    <option selected value="">All</option>
                                    @foreach($levels as $paLevelKey => $paLevelLabel)
                                        <option value="{{ $paLevelKey }}">{{ $paLevelLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="role-PA">Role</label>
                                <select id="role-PA" name="role" class="form-select">
                                    <option selected value="">All</option>
                                    @foreach($filterRoles as $paFilterRole)
                                        <option value="{{ $paFilterRole['id'] }}">{{ $paFilterRole['name'] }}</option>
                                    @endforeach
                                    <option value="{{ \App\Http\Controllers\HR\PolicyAssessment\PolicyAssignmentController::ROLE_FILTER_NONE }}">No role</option>
                                </select>
                            </div>
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="status-PA">Status</label>
                                <select id="status-PA" name="status" class="form-select">
                                    <option selected value="">All</option>
                                    <option value="pending">Not started</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="failed">Failed</option>
                                    <option value="passed">Passed</option>
                                    <option value="overdue">Overdue</option>
                                    <option value="locked">No attempts left</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="ep-doc-toolbar__filters">
                                <button id="tabulator-html-filter-go-PA" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                <button id="tabulator-html-filter-reset-PA" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                            </div>
                        </form>

                        <div class="ep-doc-toolbar__actions">
                            <button id="tabulator-print-PA" type="button" class="ep-doc-btn ep-doc-btn--ghost">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                Print
                            </button>
                            <div class="dropdown ep-doc-export">
                                <button class="dropdown-toggle ep-doc-btn ep-doc-btn--ghost" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Export
                                    <i data-lucide="chevron-down" class="w-4 h-4 opacity-70"></i>
                                </button>
                                <div class="dropdown-menu ep-doc-export__dropdown w-44">
                                    <ul class="dropdown-content ep-doc-export__menu">
                                        <li>
                                            <a id="tabulator-export-csv-PA" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-PA" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                                Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-doc-table-wrap">
                        <div id="employeePolicyAssessmentTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table pa-tabulator"></div>
                    </div>
                </div>
            </section>

        </div>

        <!-- BEGIN: Assign Policies Modal -->
        <div id="assignPolicyModal-PA" class="modal ep-doc-modal ep-doc-modal--form" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="assignPolicyForm-PA" autocomplete="off">
                    {{-- The policy picker is exactly what is assigned: an emptied picker is an error, never "the whole role". --}}
                    <input type="hidden" name="policies_listed" value="1">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Assign policies</h2>
                                    <p>Ask {{ $employee->full_name }} to read and pass these policies.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-doc-form-grid">
                                <div class="ep-doc-form-grid__full">
                                    @include('pages.hr.policy-assessment.partials.results-level-choice', ['levelChoiceId' => 'pa_ep_level'])
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label id="pa_ep_role_label" class="form-label">Role</label>
                                    @include('pages.hr.policy-assessment.partials.results-role-picker', ['rolePickerId' => 'pa_ep_role'])
                                    <div class="acc__input-error error-role_ids text-danger mt-2"></div>
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label for="pa_ep_policies" class="form-label">Policies <small class="pa-assign-label-note">(filled from the role — add or remove if needed)</small></label>
                                    <select id="pa_ep_policies" name="policy_ids[]" multiple class="w-full policy_ids" placeholder="Search policies...">
                                        @include('pages.hr.policy-assessment.partials.results-policy-options')
                                    </select>
                                    <small class="pa-assign-count" data-policy-count hidden></small>
                                    <div class="acc__input-error error-policy_ids text-danger mt-2"></div>
                                    <p class="pa-assign-level-note" data-level-note hidden>
                                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                        <span></span>
                                    </p>
                                </div>
                                <div>
                                    <label for="pa_ep_due" class="form-label">Due date <span class="text-danger">*</span></label>
                                    <input id="pa_ep_due" type="text" name="due_date" class="form-control w-full datepicker due_date" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                                    <div class="acc__input-error error-due_date text-danger mt-2"></div>
                                </div>
                                <div class="pa-ep-email-field">
                                    <div class="ep-doc-switch">
                                        <label class="form-check form-switch mb-0">
                                            <input id="pa_ep_email" class="form-check-input" name="send_email" value="1" type="checkbox" checked>
                                            <span class="ep-doc-switch__label">Email the staff member</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label for="pa_ep_note" class="form-label">Note</label>
                                    <textarea id="pa_ep_note" name="note" rows="2" maxlength="1000" class="form-control w-full note" placeholder="Optional, for HR only"></textarea>
                                    <div class="acc__input-error error-note text-danger mt-2"></div>
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <div class="acc__input-error error-employee_ids text-danger"></div>
                                    <p class="pa-assign-hint">
                                        The policies listed above are what gets assigned. A policy they already have at this level is not assigned twice: a new due date replaces the old one unless they have passed. Another level is a separate exam.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="assignPolicySave-PA" class="btn btn-primary">
                                <i data-lucide="send" class="w-4 h-4 mr-2"></i>Assign
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="pa-spinner w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </button>
                            <input type="hidden" name="employee_ids[]" value="{{ $employee->id }}"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END: Assign Policies Modal -->

        <!-- BEGIN: Attempts Modal -->
        <div id="attemptsModal-PA" class="modal ep-doc-modal ep-doc-modal--form pa-ep-detail-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header ep-doc-modal__header">
                        <div class="ep-doc-modal__intro">
                            <span class="ep-doc-modal__icon">
                                <i data-lucide="list-checks" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="attemptsModalTitle">Attempts</h2>
                                <p class="attemptsModalSubtitle"></p>
                            </div>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="attemptsModalBody pa-detail-modal__body"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                            <i data-lucide="x" class="w-4 h-4 mr-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Attempts Modal -->

        <!-- BEGIN: Attempt Review Modal -->
        <div id="attemptReviewModal-PA" class="modal ep-doc-modal ep-doc-modal--form pa-ep-detail-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header ep-doc-modal__header">
                        <div class="ep-doc-modal__intro">
                            <span class="ep-doc-modal__icon">
                                <i data-lucide="file-search" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="attemptReviewTitle">Attempt review</h2>
                                <p class="attemptReviewSubtitle"></p>
                            </div>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="attemptReviewBody pa-detail-modal__body"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ep-doc-modal__cancel attemptReviewBack">
                            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>Back
                        </button>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Attempt Review Modal -->

        <!-- BEGIN: Success Modal Content -->
        <div id="successModal-PA" class="modal ep-holiday-state-modal" tabindex="-1" aria-hidden="true">
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
                            <button type="button" data-tw-dismiss="modal" class="successCloser btn btn-primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Success Modal Content -->

        <!-- BEGIN: Warning Modal Content -->
        <div id="warningModal-PA" class="modal ep-holiday-state-modal ep-holiday-state-modal--warning" tabindex="-1" aria-hidden="true">
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
                            <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Warning Modal Content -->

        <!-- BEGIN: Confirm Modal Content -->
        <div id="confirmModal-PA" class="modal ep-holiday-state-modal ep-holiday-state-modal--danger" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="ep-holiday-state-modal__body">
                            <div class="ep-holiday-state-modal__icon">
                                <i data-lucide="alert-triangle" class="w-10 h-10"></i>
                            </div>
                            <div class="ep-holiday-state-modal__title confModTitle">Are you sure?</div>
                            <div class="ep-holiday-state-modal__desc confModDesc"></div>
                        </div>
                        <div class="ep-holiday-state-modal__actions">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">No, Cancel</button>
                            <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger">Yes, I agree</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Confirm Modal Content -->

    </div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-policy-assessment.js')
@endsection
