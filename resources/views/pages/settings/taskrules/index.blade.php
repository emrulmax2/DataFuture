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
    {{-- The condition vocabulary, resolved server-side, for the builder. --}}
    <script type="application/json" id="taskRuleCatalogue">@json($catalogue)</script>
    @php
        $isStudent = $subject === 'student';
        $who = $isStudent ? 'student' : 'applicant';
    @endphp

    <div id="siteSettingsPage" class="ss-page" data-taskrule-subject="{{ $subject }}">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Applicant Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Task Automation</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="zap"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>{{ $isStudent ? 'Which student tasks are assigned automatically when a student is created or their status changes, and under what conditions.' : 'Which admission tasks are assigned automatically when an application is submitted, and under what conditions.' }}</p>
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
                    {{-- Applicant rules (Applicant-phase tasks) | student rules (Live-phase tasks). --}}
                    <nav class="ss-rule-subjects" aria-label="Whose tasks">
                        <a href="{{ route('taskrules') }}" class="ss-rule-subjects__tab {{ !$isStudent ? 'is-active' : '' }}" @if(!$isStudent) aria-current="page" @endif>
                            <i data-lucide="file-text"></i>
                            <span>Applicant</span>
                            <b>{{ $subjectCounts['applicant'] }}</b>
                        </a>
                        <a href="{{ route('taskrules', ['subject' => 'student']) }}" class="ss-rule-subjects__tab {{ $isStudent ? 'is-active' : '' }}" @if($isStudent) aria-current="page" @endif>
                            <i data-lucide="graduation-cap"></i>
                            <span>Student</span>
                            <b>{{ $subjectCounts['student'] }}</b>
                        </a>
                    </nav>

                    <div class="ss-table-card ss-rules-card">
                        <div class="ss-table-card__header">
                            <h2>Assignment rules <span class="ss-rule-mode ss-rule-mode--{{ $mode }}" tabindex="0" data-ss-tooltip="{{ $isStudent
                                ? ($mode === 'live' ? 'Rules create tasks when a student is created or their status changes.' : 'Shadow mode: every student trigger is evaluated and logged under Recent runs, but no task is created. Set TASK_STUDENT_ASSIGNMENT_MODE=live to switch on.')
                                : ($mode === 'live' ? 'Rules create tasks when an application is submitted.' : 'Shadow mode: every submission is evaluated and logged under Recent runs, but no task is created. Set TASK_ASSIGNMENT_MODE=live to switch on.') }}">{{ $mode === 'live' ? 'Live' : 'Shadow' }}</span></h2>
                            <button id="addRuleBtn" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add rule
                            </button>
                        </div>

                        <div class="ss-rules-help">
                            <i data-lucide="info"></i>
                            <p>
                                A rule assigns one task when its trigger fires and <b>all</b> of its conditions hold. A rule with no
                                conditions always applies. Rules sharing an <b>exclusive group</b> assign at most one task between
                                them, lowest priority number first &mdash; use it for either/or pairs such as Academic vs Mature entry.
                                @if($isStudent)
                                    <br><b>Student created</b> runs as the last step of moving an applicant to students, once the course and
                                    funding are copied. <b>Student status changed</b> runs when a status is changed on the student record;
                                    add the condition <i>Status is &hellip;</i> to pick the status. A task the student already has, or that
                                    staff removed, is never added again. <b>Auto Complete</b> Yes means the rule's action finishes the
                                    task during that move (student email, welcome letter, induction invitation); No means staff complete it,
                                    or, for SLC New, it completes itself once every other task of the process is done.
                                @endif
                            </p>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Task or group...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>
                        </div>

                        <div class="ss-tabulator-wrap">
                            <div id="ruleTableId" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>

                    {{-- Dry run: what the rules would do for one applicant, without writing. --}}
                    <div class="ss-table-card ss-preview-card">
                        <div class="ss-table-card__header">
                            <h2>{{ $isStudent ? 'Preview for a student' : 'Preview a submission' }}</h2>
                        </div>
                        <form id="previewForm" class="ss-preview-form">
                            <div class="ss-filter-field">
                                <span>{{ $isStudent ? 'Student id or registration no.' : 'Applicant id or reference' }}</span>
                                <label class="ss-filter-input" for="previewApplicant">
                                    <i data-lucide="user"></i>
                                    <input id="previewApplicant" type="text" placeholder="{{ $isStudent ? 'e.g. 20050 or LCC20261093' : 'e.g. 3559 or 203559' }}" autocomplete="off">
                                </label>
                            </div>
                            @if($isStudent)
                                <div class="ss-filter-field">
                                    <span>Trigger</span>
                                    <label class="ss-filter-select" for="previewTrigger">
                                        <select id="previewTrigger">
                                            @foreach($triggers as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                            @endif
                            <button type="submit" class="ss-btn ss-btn--primary ss-btn--tool">Run preview</button>
                            <span class="ss-preview-note">Reads only &mdash; nothing is assigned.</span>
                        </form>
                        <div id="previewResult" class="ss-preview-result" hidden></div>
                    </div>

                    {{-- What the engine actually did, newest first. --}}
                    <div class="ss-table-card ss-runs-card">
                        <div class="ss-table-card__header">
                            <h2>Recent runs</h2>
                            <button id="refreshRunsBtn" type="button" class="ss-btn ss-btn--light ss-btn--compact">
                                <i data-lucide="refresh-cw"></i>
                                Refresh
                            </button>
                        </div>
                        <div class="ss-runs-table" role="table" aria-label="Recent runs">
                            <div class="ss-runs-row ss-runs-row--head" role="row">
                                <span>When</span><span>{{ $isStudent ? 'Student' : 'Applicant' }}</span><span>Mode</span><span>Rules matched</span><span>Assigned</span>
                            </div>
                            <div id="runsBody"></div>
                            <div id="runsEmpty" class="ss-rule-conditions__empty" hidden>{{ $isStudent ? 'No runs yet. The next student created or status change will appear here.' : 'No runs yet. The first submission after a rule exists will appear here.' }}</div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        {{-- One dialog serves add and edit; `id` is 0 for a new rule. --}}
        <div id="ruleModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--rules">
                <form method="POST" action="#" id="ruleForm" autocomplete="off">
                    <input type="hidden" name="id" value="0">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2 data-rule-modal-title>Add rule</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>

                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="rule_process_id">Process <span>*</span></label>
                                    <label class="ss-modal-select" for="rule_process_id">
                                        <select id="rule_process_id" class="process_id" data-rule-process>
                                            <option value="">Please Select</option>
                                            @foreach($processes as $pr)
                                                <option value="{{ $pr->id }}">{{ $pr->name }}{{ ($pr->auto_feed ?? 'No') === 'Yes' ? ' (Auto feed)' : '' }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <div class="ss-modal-help">{{ $isStudent ? 'Student (Live) processes.' : 'Applicant processes.' }} The task list follows the process.</div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="rule_task_list_id">Assign task <span>*</span></label>
                                    <label class="ss-modal-select" for="rule_task_list_id">
                                        <select id="rule_task_list_id" name="task_list_id" class="task_list_id">
                                            <option value="">Please Select</option>
                                            @foreach($tasks as $t)
                                                <option value="{{ $t->id }}" data-process="{{ $t->process_list_id }}">{{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <div class="acc__input-error error-task_list_id"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="rule_trigger">When <span>*</span></label>
                                    <label class="ss-modal-select" for="rule_trigger">
                                        <select id="rule_trigger" name="trigger" class="trigger">
                                            @foreach($triggers as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <div class="acc__input-error error-trigger"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="rule_exclusive_group">
                                        Exclusive group
                                        <span class="ss-field-info" tabindex="0" role="img" aria-label="About exclusive groups"
                                              data-ss-tooltip-title="Either / or"
                                              data-ss-tooltip="Give two or more rules the same group name and the engine assigns at most one of their tasks: it checks them in priority order (lowest number first) and stops at the first rule whose conditions hold. Use it for pairs where only one task makes sense, such as Course Entry Documentation (Academic) vs (Mature). Leave it blank for a rule that stands on its own."><i data-lucide="info"></i></span>
                                    </label>
                                    <input id="rule_exclusive_group" type="text" name="exclusive_group" class="ss-modal-input exclusive_group" list="ruleGroups" placeholder="e.g. entry_documentation">
                                    <datalist id="ruleGroups">
                                        @foreach($groups as $g)<option value="{{ $g }}"></option>@endforeach
                                    </datalist>
                                    <div class="ss-modal-help">Optional. Same name on two rules = only one of them assigns.</div>
                                    <div class="acc__input-error error-exclusive_group"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="rule_priority">
                                        Priority <span>*</span>
                                        <span class="ss-field-info" tabindex="0" role="img" aria-label="About priority"
                                              data-ss-tooltip-title="Order inside a group"
                                              data-ss-tooltip="Only matters when the rule is in an exclusive group: rules in the same group are checked lowest number first, and the first one that matches wins. Outside a group every matching rule assigns its task regardless of priority."><i data-lucide="info"></i></span>
                                    </label>
                                    <input id="rule_priority" type="number" name="priority" class="ss-modal-input priority" value="100" min="1" max="9999">
                                    <div class="ss-modal-help">Lower runs first. Only matters inside an exclusive group.</div>
                                    <div class="acc__input-error error-priority"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full">
                                    <label class="ss-status-toggle ss-status-toggle--inline" for="rule_active">
                                        <input id="rule_active" type="checkbox" name="active" value="1" checked>
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Inactive rules are kept but never applied.</small>
                                        </span>
                                    </label>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-rule-conditions-field">
                                    <div class="ss-rule-conditions__head">
                                        <label>Conditions</label>
                                        <button type="button" id="addConditionBtn" class="ss-btn ss-btn--light ss-btn--compact">
                                            <i data-lucide="plus"></i>
                                            Add condition
                                        </button>
                                    </div>
                                    <div id="ruleConditions" class="ss-rule-conditions"></div>
                                    <div class="ss-rule-conditions__empty" data-rule-empty>No conditions &mdash; this rule always applies.</div>
                                    <div class="acc__input-error error-conditions"></div>
                                </div>

                                {{-- Follow-up jobs exist for applicant rules only so far (config tasks.*assignment_jobs). --}}
                                @if(count($jobs))
                                <div class="ss-modal-field ss-modal-field--full">
                                    {{-- one-line form on purpose: a php block after the one-liners above breaks Blade's block matching --}}
                                    @php($completionJobs = $subject === \App\Models\TaskAssignmentRule::SUBJECT_STUDENT ? [] : (array) config('tasks.completion_jobs', []))
                                    <label for="rule_after_assign_job">
                                        {{-- After assigning, run --}}
                                        Action
                                        <span class="ss-field-info" tabindex="0" role="img" aria-label="About rule actions"
                                              data-ss-tooltip-title="Action"
                                              data-ss-tooltip="A job linked to this rule's task. &quot;When the task is assigned&quot; runs right after the engine creates the task (for example sending the e-signature link once Signed Application form is assigned), never when the applicant already has it or staff removed it.{{ count($completionJobs) ? ' &quot;When the task is completed&quot; runs when staff mark the task Completed (for example Update student photo), also for the same task added by hand.' : '' }} Leave blank for no action."><i data-lucide="info"></i></span>
                                    </label>
                                    <label class="ss-modal-select" for="rule_after_assign_job">
                                        <select id="rule_after_assign_job" name="after_assign_job" class="after_assign_job">
                                            <option value="">Nothing</option>
                                            {{-- @foreach($jobs as $class => $label)
                                                <option value="{{ $class }}">{{ $label }}</option>
                                            @endforeach --}}
                                            @if(count($completionJobs))
                                                <optgroup label="When the task is assigned">
                                                    @foreach($jobs as $class => $label)
                                                        @continue(isset($completionJobs[$class]))
                                                        <option value="{{ $class }}">{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="When the task is completed">
                                                    @foreach($completionJobs as $class => $label)
                                                        <option value="{{ $class }}">{{ $label }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @else
                                                @foreach($jobs as $class => $label)
                                                    <option value="{{ $class }}">{{ $label }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                    <div class="acc__input-error error-after_assign_job"></div>
                                </div>
                                @else
                                    <input type="hidden" id="rule_after_assign_job" name="after_assign_job" value="">
                                @endif

                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="rule_notes">Notes</label>
                                    <textarea id="rule_notes" name="notes" class="ss-modal-textarea notes" rows="2" placeholder="Why this rule exists (optional)"></textarea>
                                    <div class="acc__input-error error-notes"></div>
                                </div>
                            </div>
                        </div>

                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveRule" class="ss-btn ss-btn--primary">
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
                                Save rule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
    @vite('resources/js/taskrules.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
