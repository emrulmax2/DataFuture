@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
@php
    $phase = (isset($task->processlist->phase) && !empty($task->processlist->phase) ? $task->processlist->phase : 'Live');
    $isApplicant = $phase == 'Applicant';

    // Feature flags off the TaskList. Each one adds a dialog and a row action;
    // a plain task (the common case) renders none of them, which is what keeps
    // this screen down to the three dialogs in the design.
    $hasInterview = $task->interview == 'Yes';
    // list() forces has_task_upload / has_task_status / is_completable off for
    // interview tasks and hides the actions column, so those two dialogs are
    // unreachable there — don't render them.
    $hasUpload   = $task->upload == 'Yes' && !$hasInterview;
    $hasOutcome  = $task->status == 'Yes' && !$hasInterview;
    $hasIdCard   = $task->id_card == 'Yes';
    $hasOrgEmail = $task->org_email == 'Yes';
    $hasPearson  = $task->pearson_reg == 'Yes';
    $hasExcuse   = $task->attendance_excuses == 'Yes';
    $hasAddress  = $task->address_request == 'Yes';
    $hasDocReq   = (bool) ($hasDocumentRequests ?? false);

    // Mirrors the old `rowSelectionChanged`: which buttons a ticked row can
    // reveal. Excuse / address-request tasks reveal none, so those pages get
    // no selection bar at all.
    $hasBulkActions = $hasOrgEmail || $hasPearson || (!$hasExcuse && !$hasAddress);

    $letterTags = [
        'Student' => [
            '[DATA=students]full_name[/DATA]', '[DATA=titles]name[/DATA]',
            '[DATA=students]first_name[/DATA]', '[DATA=students]last_name[/DATA]',
            '[DATA=students]registration_no[/DATA]', '[DATA=students]date_of_birth[/DATA]',
            '[DATA=student_contacts]term_address[/DATA]', '[DATA=student_contacts]permanent_address[/DATA]',
            '[DATA=student_contacts]mobile[/DATA]', '[DATA=student_contacts]personal_email[/DATA]',
            '[DATA=courses]name[/DATA]', '[DATA=courses]degree_offered[/DATA]',
            '[DATA=semesters]name[/DATA]', '[DATA=student_proposed_courses]awarding_body[/DATA]',
            '[DATA=student_proposed_courses]class_startdate[/DATA]', '[DATA=student_proposed_courses]class_enddate[/DATA]',
            '[DATA=student_proposed_courses]fees[/DATA]', '[DATA=result]W,P,M,D,R,C,U,A[/DATA]',
        ],
        'Common' => [
            '[DATA=signatories]sign_url[/DATA]', '[DATA=signatories]name[/DATA]',
            '[DATA=signatories]post[/DATA]', '[DATA=letter_issuing]issued_date[/DATA]',
            '[DATA=today_date]today_date[/DATA]',
        ],
    ];
@endphp

<main class="tkm-main">

    {{-- ── page head ─────────────────────────────────────────────── --}}
    <div class="tkm-pagehead">
        <div>
            <div class="tkm-pagehead__eyebrow">{{ $isApplicant ? 'Applicant list for' : 'Student list for' }}</div>
            <h1 class="tkm-pagehead__title" data-tkm-taskname>{{ $task->name }}</h1>
        </div>
        <div class="tkm-pagehead__tail">
            <span class="tkm-count">
                <span class="tkm-count__label" data-tkm-countlabel>Pending</span>
                <span class="tkm-count__value" data-tkm-counttotal>—</span>
            </span>
            <a href="{{ route('task.manager') }}" class="tkm-btn tkm-btn--green">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6l-6 6 6 6"></path></svg>
                Back to Task Manager
            </a>
        </div>
    </div>

    {{-- ── filters ───────────────────────────────────────────────── --}}
    <div class="tkm-card tkm-card--flow">
        <form class="tkm-filters" data-tkm-filters onsubmit="return false;">
            <div>
                <label class="tkm-label" for="tkm-refno">{{ $isApplicant ? 'Ref no' : 'Reg / Ref no' }}</label>
                <input class="tkm-input" type="text" id="tkm-refno" name="reg_or_ref" placeholder="Reg no or ref no" autocomplete="off">
            </div>
            <div>
                <label class="tkm-label" for="tkm-status">Status</label>
                <select class="tkm-select" id="tkm-status" name="status">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>
            <div>
                <label class="tkm-label" for="tkm-venue">Venue</label>
                <select class="tkm-select" id="tkm-venue" name="venue">
                    <option value="">All venues</option>
                    @foreach($venues as $vnu)
                        <option value="{{ $vnu->id }}">{{ $vnu->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tkm-label" for="tkm-courses">Courses</label>
                <select class="tkm-select" id="tkm-courses" name="courses">
                    <option value="">All courses</option>
                    @foreach($courses as $crs)
                        <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tkm-filters__actions">
                <button type="button" class="tkm-btn tkm-btn--gold" data-tkm-act="filter-go">Go</button>
                <button type="button" class="tkm-btn" data-tkm-act="filter-reset">Reset</button>
            </div>

            {{--
                Selection bar — appears only once rows are ticked, and carries
                exactly the buttons the old `rowSelectionChanged` faded in:

                  org_email = Yes            → Export Students Email + Complete & Send Email, nothing else
                  otherwise, pearson_reg=Yes → Export Pearson Registration
                  otherwise, no excuse/address → Export Student List + Update Task Status

                An attendance-excuse or address-request task therefore has no
                bulk actions at all, so the bar is not rendered for it.
            --}}
            @if($hasBulkActions)
            <div class="tkm-selbar" data-tkm-selbar hidden>
                <span class="tkm-selbar__label" data-tkm-sellabel>No students selected</span>
                <div class="tkm-selbar__tail">
                    @if($hasOrgEmail)
                        <button type="button" class="tkm-btn" data-tkm-act="export-emails">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="16" rx="2"></rect><path d="M3.5 9.5h17M9.5 9.5V20M15 9.5V20"></path></svg>
                            Export Students Email
                            <span class="tkm-btn__spin"></span>
                        </button>
                        <button type="button" class="tkm-btn tkm-btn--gold" data-tkm-act="complete-emails">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m8.5 12.2 2.4 2.4 4.6-5"></path></svg>
                            Complete &amp; Send Email
                            <span class="tkm-btn__spin"></span>
                        </button>
                    @else
                        @if($hasPearson)
                            <button type="button" class="tkm-btn" data-tkm-act="export-pearson">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="16" rx="2"></rect><path d="M3.5 9.5h17M9.5 9.5V20M15 9.5V20"></path></svg>
                                Export Pearson Registration
                                <span class="tkm-btn__spin"></span>
                            </button>
                        @endif

                        @if(!$hasExcuse && !$hasAddress)
                            <button type="button" class="tkm-btn" data-tkm-act="export-list">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="16" rx="2"></rect><path d="M3.5 9.5h17M9.5 9.5V20M15 9.5V20"></path></svg>
                                Export Student List
                                <span class="tkm-btn__spin"></span>
                            </button>

                            <div class="tkm-pop" data-tkm-pop>
                                <button type="button" class="tkm-btn tkm-btn--gold" data-tkm-act="pop-toggle" aria-expanded="false">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h9M17 7h3M4 17h3M11 17h9"></path><circle cx="15" cy="7" r="2"></circle><circle cx="9" cy="17" r="2"></circle></svg>
                                    Update Task Status
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                                </button>
                                <div class="tkm-pop__panel">
                                    <button type="button" class="tkm-pop__item tkm-pop__item--green" data-tkm-act="bulk-complete">
                                        <span class="tkm-pop__ico"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m8.5 12.2 2.4 2.4 4.6-5"></path></svg></span>
                                        Mark as completed
                                    </button>
                                    <button type="button" class="tkm-pop__item tkm-pop__item--danger" data-tkm-act="bulk-cancel">
                                        <span class="tkm-pop__ico"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m9 9 6 6M15 9l-6 6"></path></svg></span>
                                        Mark as cancelled
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </form>
    </div>

    {{-- ── table ─────────────────────────────────────────────────── --}}
    <div class="tkm-card tkm-card--flow">
        <div class="tkm-tablehead">
            <h2 class="tkm-tablehead__title">{{ $isApplicant ? 'Applicants' : 'Students' }}</h2>
            <span class="tkm-tablehead__note" data-tkm-scope>Pending · all venues</span>

            @if($hasPearson)
                <button type="button" class="tkm-btn" style="margin-left:auto" data-tkm-act="open" data-tkm-target="pearson">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="16" rx="2"></rect><path d="M3.5 9.5h17M9.5 9.5V20M15 9.5V20"></path></svg>
                    Upload Pearson Reg. Confirmation
                </button>
                <span class="tkm-tablehead__count"><span data-tkm-records>0</span> records</span>
            @else
                <span class="tkm-tablehead__count"><span data-tkm-records>0</span> records</span>
            @endif
        </div>

        <div class="tkm-tablewrap" data-tkm-tablewrap>
            <table class="tkm-table">
                <thead>
                    <tr>
                        <th class="tkm-col-check">
                            <label class="tkm-check">
                                <input type="checkbox" data-tkm-act="select-all" aria-label="Select all rows on this page">
                                <span class="tkm-check__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg></span>
                            </label>
                        </th>
                        <th class="is-sortable" data-tkm-sort="{{ $isApplicant ? 'application_no' : 'registration_no' }}">
                            {{ $isApplicant ? 'Ref no' : 'Reg no' }}
                            <svg class="tkm-sort" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                        </th>
                        <th class="is-sortable" data-tkm-sort="first_name">
                            Name
                            <svg class="tkm-sort" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                        </th>
                        <th>Course</th>
                        {{-- Venue carried a sort arrow in the old table, but `venue_name`
                             is a joined label rather than a column, so sorting on it 500s.
                             Left unsortable deliberately. --}}
                        <th>Venue</th>
                        <th class="is-sortable" data-tkm-sort="status_id">
                            Status
                            <svg class="tkm-sort" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                        </th>
                        @if($hasInterview)
                            {{-- shown only on the In Progress / Completed filters, as in the old table --}}
                            <th data-tkm-col="interview" hidden>Interview</th>
                        @endif
                        <th>Task status</th>
                        <th>Task type</th>
                        @unless($hasInterview)
                            <th class="tkm-col-act" data-tkm-col="actions">Actions</th>
                        @endunless
                    </tr>
                </thead>
                <tbody data-tkm-rows>
                    <tr><td colspan="11"><div class="tkm-modal__loader"></div></td></tr>
                </tbody>
            </table>
        </div>

        <div class="tkm-tablefoot">
            <div class="tkm-pagesize">
                <span class="tkm-pagesize__label">Page size</span>
                <select class="tkm-select" data-tkm-act="page-size" aria-label="Rows per page">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <span class="tkm-showing" data-tkm-showing>Showing <strong>0</strong> of 0</span>
            <div class="tkm-pager" data-tkm-pager></div>
        </div>
    </div>
</main>

{{-- ═══════════════════════════════════════════════════════════════
     Dialogs. Every one is a plain hidden div driven by the same
     open/close handler in task-manager-detail.js — no theme modal,
     no per-dialog wiring. Flags decide which even reach the page.
     ═══════════════════════════════════════════════════════════════ --}}

{{-- feedback: success / warning / error all share this one card --}}
<div class="tkm-modal" data-tkm-modal="feedback" hidden>
    <div class="tkm-modal__box tkm-modal__box--sm">
        <div class="tkm-modal__body tkm-modal__body--center">
            <div class="tkm-modal__crest" data-tkm-feedback-crest>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.6"></circle><path d="m8.4 12.2 2.5 2.5 4.7-5"></path></svg>
            </div>
            <h2 class="tkm-modal__lead" data-tkm-feedback-title>Task status updated</h2>
            <p class="tkm-modal__copy" data-tkm-feedback-copy></p>
            <div class="tkm-modal__pill" data-tkm-feedback-pill hidden><span></span><em data-tkm-feedback-pilltext style="font-style:normal">Completed</em></div>
        </div>
        <div class="tkm-modal__foot tkm-modal__foot--center">
            <button type="button" class="tkm-btn tkm-btn--green tkm-btn--wide" data-tkm-act="close">Done</button>
        </div>
    </div>
</div>

{{-- cancellation reason --}}
<div class="tkm-modal" data-tkm-modal="cancel" hidden>
    <form class="tkm-modal__box tkm-modal__box--md" data-tkm-form="cancel">
        <div class="tkm-modal__head">
            <h2 class="tkm-modal__title">Cancellation reason</h2>
            <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <div class="tkm-modal__body">
            <div class="tkm-field">
                <label class="tkm-label" for="tkm-cancel-reason">Why is this task cancelled? <span class="tkm-label__req">*</span></label>
                <textarea class="tkm-textarea" id="tkm-cancel-reason" name="canceled_reason" placeholder="Give a short reason — this is recorded against the student's task"></textarea>
                <div class="tkm-field__error" data-tkm-error="canceled_reason"></div>
            </div>
            <div class="tkm-modal__hint" data-tkm-sellabel>No students selected</div>
            <input type="hidden" name="phase" value="{{ $phase }}">
            <input type="hidden" name="task_id" value="{{ $task->id }}">
            <input type="hidden" name="ids" value="">
        </div>
        <div class="tkm-modal__foot">
            <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
            <button type="submit" class="tkm-btn tkm-btn--red">Submit<span class="tkm-btn__spin"></span></button>
        </div>
    </form>
</div>

@if($hasUpload)
    {{-- upload documents --}}
    <div class="tkm-modal" data-tkm-modal="upload" hidden>
        <form class="tkm-modal__box" data-tkm-form="upload">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Upload documents</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <label class="tkm-drop" data-tkm-drop>
                    <div class="tkm-drop__ico">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16.5V6M7.5 10 12 5.5l4.5 4.5"></path><path d="M4.5 15.5v2.5a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-2.5"></path></svg>
                    </div>
                    <div class="tkm-drop__lead">Drop files here or click to upload</div>
                    <div class="tkm-drop__note">Max 5 MB per file · up to 10 files</div>
                    <input type="file" multiple accept=".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt" data-tkm-files>
                </label>
                <div class="tkm-files" data-tkm-filelist></div>

                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-doc-name">Document name</label>
                    <input class="tkm-input" type="text" id="tkm-doc-name" name="display_file_name" placeholder="e.g. Tenancy agreement">
                </div>
                <div class="tkm-field">
                    <span class="tkm-label">Hard copy checked?</span>
                    <div class="tkm-radios">
                        <label class="tkm-radio">
                            <input type="radio" name="hard_copy_check" value="1">
                            <span class="tkm-radio__dot"></span>
                            <span>Yes</span>
                        </label>
                        <label class="tkm-radio">
                            <input type="radio" name="hard_copy_check" value="0" checked>
                            <span class="tkm-radio__dot"></span>
                            <span>No</span>
                        </label>
                    </div>
                </div>
                <input type="hidden" name="student_id" value="0">
                <input type="hidden" name="task_id" value="{{ $task->id }}">
                <input type="hidden" name="phase" value="{{ $phase }}">
            </div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Upload<span class="tkm-btn__spin"></span></button>
            </div>
        </form>
    </div>
@endif

@if($hasOutcome)
    {{-- task outcome (body markup comes from task.manager.outcome.statuses) --}}
    <div class="tkm-modal" data-tkm-modal="outcome" hidden>
        <form class="tkm-modal__box" data-tkm-form="outcome">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Update outcome</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body" data-tkm-slot><div class="tkm-modal__loader"></div></div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Update<span class="tkm-btn__spin"></span></button>
                <input type="hidden" name="student_id" value="0">
                <input type="hidden" name="task_id" value="0">
                <input type="hidden" name="phase" value="{{ $phase }}">
            </div>
        </form>
    </div>
@endif

@if($hasExcuse)
    {{-- attendance excuse (body markup comes from student.process.task.view.excuse) --}}
    <div class="tkm-modal" data-tkm-modal="excuse" hidden>
        <form class="tkm-modal__box tkm-modal__box--lg" data-tkm-form="excuse">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Attendance excuse</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body" data-tkm-slot><div class="tkm-modal__loader"></div></div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Update<span class="tkm-btn__spin"></span></button>
                <input type="hidden" name="student_id" value="0">
                <input type="hidden" name="student_task_id" value="0">
                <input type="hidden" name="attendance_excuse_id" value="0">
            </div>
        </form>
    </div>
@endif

@if($hasAddress)
    {{-- address update request (body markup comes from student.process.task.view.address.request) --}}
    <div class="tkm-modal" data-tkm-modal="address" hidden>
        <form class="tkm-modal__box tkm-modal__box--lg" data-tkm-form="address">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Address update request</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <div data-tkm-slot><div class="tkm-modal__loader"></div></div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-addr-status">Decision</label>
                    <select class="tkm-select" id="tkm-addr-status" name="task_status" data-tkm-act="address-status">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">Hold</option>
                        <option value="Completed">Approve &amp; complete</option>
                        <option value="Canceled">Cancel</option>
                    </select>
                </div>
                <div class="tkm-field" data-tkm-addr-note hidden>
                    <label class="tkm-label" for="tkm-addr-note">Notes</label>
                    <textarea class="tkm-textarea" id="tkm-addr-note" name="note" placeholder="Why is this on hold?"></textarea>
                </div>
            </div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Close</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Submit<span class="tkm-btn__spin"></span></button>
                <input type="hidden" name="student_id" value="0">
                <input type="hidden" name="student_task_id" value="0">
                <input type="hidden" name="student_address_update_request_id" value="0">
            </div>
        </form>
    </div>
@endif

@if($hasIdCard)
    {{-- ID card preview (body markup comes from task.manager.download.id.card) --}}
    <div class="tkm-modal" data-tkm-modal="idcard" hidden>
        <div class="tkm-modal__box tkm-modal__box--lg">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">ID card</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body" data-tkm-slot><div class="tkm-modal__loader"></div></div>
        </div>
    </div>
@endif

@if($hasInterview)
    {{-- unlock an applicant profile with their date of birth --}}
    <div class="tkm-modal" data-tkm-modal="unlock" hidden>
        <form class="tkm-modal__box tkm-modal__box--md" data-tkm-form="unlock">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Unlock profile for interview</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-dob">Applicant date of birth <span class="tkm-label__req">*</span></label>
                    <input class="tkm-input" type="date" id="tkm-dob" name="dob">
                    <div class="tkm-field__error" data-tkm-error="dob"></div>
                </div>
                <input type="hidden" name="applicantId" value="">
                <input type="hidden" name="taskListId" value="{{ $task->id }}">
            </div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Unlock<span class="tkm-btn__spin"></span></button>
            </div>
        </form>
    </div>
@endif

@if($hasPearson)
    {{-- Pearson registration confirmations --}}
    <div class="tkm-modal" data-tkm-modal="pearson" hidden>
        <form class="tkm-modal__box" data-tkm-form="pearson">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Upload Pearson registration confirmations</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-pearson-status">Status <span class="tkm-label__req">*</span></label>
                    <select class="tkm-select" id="tkm-pearson-status" name="status_id">
                        <option value="">Please select</option>
                        @foreach($statuses as $stst)
                            <option value="{{ $stst->id }}">{{ $stst->name }}</option>
                        @endforeach
                    </select>
                    <div class="tkm-field__error" data-tkm-error="status_id"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-pearson-term">Term <span class="tkm-label__req">*</span></label>
                    <select class="tkm-select" id="tkm-pearson-term" name="term_declaration_id">
                        <option value="">Please select</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                    <div class="tkm-field__error" data-tkm-error="term_declaration_id"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-pearson-reason">Change reason</label>
                    <textarea class="tkm-textarea" id="tkm-pearson-reason" name="status_change_reason" style="height:88px"></textarea>
                </div>
                <div class="tkm-field">
                    <span class="tkm-label">Pearson registration excel</span>
                    <label class="tkm-drop" data-tkm-drop>
                        <div class="tkm-drop__ico">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16.5V6M7.5 10 12 5.5l4.5 4.5"></path><path d="M4.5 15.5v2.5a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-2.5"></path></svg>
                        </div>
                        <div class="tkm-drop__lead">Drop the .xlsx here or click to choose</div>
                        <div class="tkm-drop__note">One file · .xlsx only</div>
                        <input type="file" accept=".xlsx" name="document" data-tkm-files>
                    </label>
                    <div class="tkm-files" data-tkm-filelist></div>
                    <div class="tkm-field__error" data-tkm-error="document"></div>
                </div>
                <input type="hidden" name="task_list_id" value="{{ $task->id }}">
            </div>
            <div class="tkm-modal__foot tkm-modal__foot--split">
                <a href="{{ url('storage/BTECRT_Sample.xlsx') }}" class="tkm-btn">Download sample</a>
                <span style="display:flex;gap:10px">
                    <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                    <button type="submit" class="tkm-btn tkm-btn--green">Save<span class="tkm-btn__spin"></span></button>
                </span>
            </div>
        </form>
    </div>
@endif

@if($hasDocReq)
    {{-- document request decision --}}
    <div class="tkm-modal" data-tkm-modal="docreq" hidden>
        <form class="tkm-modal__box tkm-modal__box--lg" data-tkm-form="docreq">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Update document request</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <div class="tkm-note" data-tkm-request>
                    <div class="tkm-note__head">
                        <span class="tkm-note__title" data-tkm-request-name></span>
                        <span class="tkm-chip" style="margin-left:auto" data-tkm-request-status></span>
                    </div>
                    <div class="tkm-note__body" data-tkm-request-desc></div>
                    <div class="tkm-note__meta">
                        <span>Service requested: <strong data-tkm-request-service></strong></span>
                        <span style="margin-left:auto" data-tkm-request-when></span>
                    </div>
                </div>
                <div class="tkm-field">
                    <span class="tkm-label">Status <span class="tkm-label__req">*</span></span>
                    <div class="tkm-radios">
                        <label class="tkm-radio">
                            <input type="radio" name="status" value="Approved">
                            <span class="tkm-radio__dot"></span>
                            <span>Approved</span>
                        </label>
                        <label class="tkm-radio">
                            <input type="radio" name="status" value="Rejected">
                            <span class="tkm-radio__dot"></span>
                            <span>Rejected</span>
                        </label>
                    </div>
                    <div class="tkm-field__error" data-tkm-error="status"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label">Remarks / comments <span class="tkm-label__req">*</span></label>
                    <div class="tkm-editor">
                        <div class="tkm-editor__toolbar"></div>
                        <div class="tkm-editor__area" data-tkm-editor="docreq"></div>
                    </div>
                    <div class="tkm-field__error" data-tkm-error="description"></div>
                </div>
                <input type="hidden" name="student_task_id" value="">
            </div>
            <div class="tkm-modal__foot tkm-modal__foot--split">
                <label class="tkm-check">
                    <input type="checkbox" name="email_sent" value="1" checked>
                    <span class="tkm-check__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg></span>
                    Send confirmation email
                </label>
                <span style="display:flex;gap:10px">
                    <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                    <button type="submit" class="tkm-btn tkm-btn--green">Update status<span class="tkm-btn__spin"></span></button>
                </span>
            </div>
        </form>
    </div>

    {{-- generate + send the requested letter --}}
    <div class="tkm-modal" data-tkm-modal="letter" hidden>
        <form class="tkm-modal__box tkm-modal__box--xl" data-tkm-form="letter">
            <div class="tkm-modal__head">
                <h2 class="tkm-modal__title">Send letter</h2>
                <button type="button" class="tkm-icobtn tkm-icobtn--sm tkm-modal__x" data-tkm-act="close" aria-label="Close">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="tkm-modal__body">
                <div class="tkm-note" data-tkm-request>
                    <div class="tkm-note__head">
                        <span class="tkm-note__title" data-tkm-request-name></span>
                        <span class="tkm-chip" style="margin-left:auto" data-tkm-request-status></span>
                    </div>
                    <div class="tkm-note__body" data-tkm-request-desc></div>
                    <div class="tkm-note__meta">
                        <span>Service requested: <strong data-tkm-request-service></strong></span>
                        <span style="margin-left:auto" data-tkm-request-when></span>
                    </div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-issued">Issued date <span class="tkm-label__req">*</span></label>
                    <input class="tkm-input" type="date" id="tkm-issued" name="issued_date" value="{{ date('Y-m-d') }}">
                    <div class="tkm-field__error" data-tkm-error="issued_date"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-letterset">Letter <span class="tkm-label__req">*</span></label>
                    <select class="tkm-select" id="tkm-letterset" name="letter_set_id" data-tkm-act="letter-set">
                        <option value="">Please select</option>
                        @foreach($letterSet as $ls)
                            <option value="{{ $ls->id }}">{{ $ls->letter_type.' - '.$ls->letter_title }}</option>
                        @endforeach
                    </select>
                    <div class="tkm-field__error" data-tkm-error="letter_set_id"></div>
                </div>
                <div class="tkm-field" data-tkm-letterbody hidden>
                    <div style="display:flex;align-items:center;margin-bottom:7px">
                        <span class="tkm-label" style="margin:0">Letter body</span>
                        <div class="tkm-pop" data-tkm-pop style="margin-left:auto">
                            <button type="button" class="tkm-btn" data-tkm-act="pop-toggle" aria-expanded="false">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 12 4.8H5.2A1.2 1.2 0 0 0 4 6v6.8l8.6 8.6a1.7 1.7 0 0 0 2.4 0l5.6-5.6a1.7 1.7 0 0 0 0-2.4z"></path><circle cx="8.2" cy="8.2" r="1.2"></circle></svg>
                                Tags
                            </button>
                            <div class="tkm-pop__panel" style="width:320px;max-height:340px;overflow-y:auto">
                                @foreach($letterTags as $group => $tags)
                                    <div class="tkm-nav__menu-title" style="border:none;padding:8px 11px 6px">{{ $group }} tags</div>
                                    @foreach($tags as $tag)
                                        <button type="button" class="tkm-pop__item" style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px" data-tkm-act="copy-tag" data-tkm-tag="{{ $tag }}">{{ $tag }}</button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="tkm-editor">
                        <div class="tkm-editor__toolbar"></div>
                        <div class="tkm-editor__area" data-tkm-editor="letter"></div>
                    </div>
                    <div class="tkm-field__error" data-tkm-error="letter_body"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-label" for="tkm-signatory">Signatory</label>
                    <select class="tkm-select" id="tkm-signatory" name="signatory_id">
                        <option value="">Please select</option>
                        @foreach($signatory as $sg)
                            <option value="{{ $sg->id }}">{{ $sg->signatory_name }}</option>
                        @endforeach
                    </select>
                    <div class="tkm-field__error" data-tkm-error="signatory_id"></div>
                </div>
                <div class="tkm-field">
                    <label class="tkm-check">
                        <input type="checkbox" name="send_in_email" value="1" data-tkm-act="letter-email">
                        <span class="tkm-check__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg></span>
                        Send this letter by email
                    </label>
                </div>
                <div class="tkm-field" data-tkm-smtp hidden>
                    <label class="tkm-label" for="tkm-smtp">SMTP <span class="tkm-label__req">*</span></label>
                    <select class="tkm-select" id="tkm-smtp" name="comon_smtp_id">
                        <option value="">Please select</option>
                        @foreach($smtps as $sm)
                            <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                        @endforeach
                    </select>
                    <div class="tkm-field__error" data-tkm-error="comon_smtp_id"></div>
                </div>
                <input type="hidden" name="student_id" value="">
                <input type="hidden" name="student_task_id" value="">
            </div>
            <div class="tkm-modal__foot">
                <button type="button" class="tkm-btn" data-tkm-act="close">Cancel</button>
                <button type="submit" class="tkm-btn tkm-btn--green">Send letter<span class="tkm-btn__spin"></span></button>
            </div>
        </form>
    </div>
@endif

<script type="application/json" data-tkm-config>
{!! json_encode([
    'taskId' => $task->id,
    'taskName' => $task->name,
    'phase' => $phase,
    'flags' => [
        'upload' => $hasUpload,
        'outcome' => $hasOutcome,
        'interview' => $hasInterview,
        'idCard' => $hasIdCard,
        'orgEmail' => $hasOrgEmail,
        'pearson' => $hasPearson,
        'excuse' => $hasExcuse,
        'address' => $hasAddress,
        'docRequest' => $hasDocReq,
    ],
    'routes' => [
        'list' => route('task.manager.list'),
        'updateStatus' => route('task.manager.update.task.status'),
        'cancel' => route('task.manager.canceled.task'),
        'uploadDoc' => route('task.manager.upload.document'),
        'downloadDoc' => route('task.manage.document.download'),
        'outcomeStatuses' => route('task.manager.outcome.statuses'),
        'updateOutcome' => route('task.manager.update.outcome'),
        'exportList' => route('task.manager.students.list.excel'),
        'exportEmails' => route('task.manager.students.email.excel'),
        'completeEmails' => route('task.manager.comlete.students.email.id.task'),
        'exportPearson' => route('task.manager.pearson.registration.excel'),
        'uploadPearson' => route('student.process.upload.registration.confirmations'),
        'idCard' => route('task.manager.download.id.card'),
        'docRequestUpdate' => route('task.manager.document_request.update'),
        'letterStatus' => route('task.manager.document_request.letter.update'),
        'getLetterSet' => route('student.get.letter.set'),
        'sendLetter' => route('student.send.letter'),
        'viewExcuse' => route('student.process.task.view.excuse'),
        'updateExcuse' => route('student.process.update.task.and.excuse'),
        'viewAddress' => route('student.process.task.view.address.request'),
        'updateAddress' => route('student.process.update.address.request.task'),
        'unlockWithDob' => route('applicant.interview.unlock.only'),
        'unlockProfile' => route('applicant.interview.unlock'),
        'receipt' => route('order.print.pdf', ['__ID__']),
    ],
]) !!}
</script>
@endsection
