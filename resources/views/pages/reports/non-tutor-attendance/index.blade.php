@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
<style>
    /* Report: Non-Tutor Attendance Updates. The details modal is moved to <body> when
       it opens, so its styles hang off .ntu-modal, not the page wrapper. */
    .ntu-note { font-size: 12px; color: #64748b; line-height: 1.5; }
    .ntu-count { font-weight: 600; }
    .ntu-staff { line-height: 1.25; }
    .ntu-staff__name { font-weight: 600; white-space: normal; }
    .ntu-staff__meta { font-size: 11px; color: #64748b; white-space: normal; }
    .ntu-num--alert { color: #b91c1c; font-weight: 600; }
    #ntuTable .tabulator-row { cursor: pointer; }
    /* Needs review: a red row, a red tag under the name and the reasons in short. */
    #ntuTable .tabulator-row.ntu-row--review,
    #ntuDetailTable .tabulator-row.ntu-row--review { background: #fef2f2 !important; box-shadow: inset 3px 0 0 #dc2626; }
    #ntuTable .tabulator-row.ntu-row--review:hover,
    #ntuDetailTable .tabulator-row.ntu-row--review:hover { background: #fee2e2 !important; }
    #ntuTable .tabulator-row.ntu-row--review .tabulator-cell,
    #ntuDetailTable .tabulator-row.ntu-row--review > .tabulator-cell { background: transparent !important; }
    .ntu-flag { display: inline-block; margin-top: 4px; font-size: 10px; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; line-height: 1; padding: 3px 7px; border-radius: 9999px; background: #dc2626; color: #fff; }
    .ntu-flag-why { margin-top: 3px; font-size: 11px; line-height: 1.3; color: #b91c1c; white-space: normal; }
    .ntu-legend { display: inline-block; width: 12px; height: 12px; margin-right: 4px; vertical-align: -2px; border-radius: 3px; background: #fef2f2; box-shadow: inset 3px 0 0 #dc2626, inset 0 0 0 1px #fecaca; }
    /* datafuture.css sets white-space: normal !important on every Tabulator table, which
       wraps a wide row onto a second line; keep these rows on one line and scroll sideways. */
    #ntuTable .tabulator-tableHolder .tabulator-table,
    #ntuDetailTable .tabulator-tableHolder .tabulator-table { white-space: nowrap !important; }
    /* Columns fit the box (no sideways scrolling): text wraps inside its cell and the
       headings wrap, with tighter padding than the theme's 20px. */
    #ntuTable .tabulator-cell,
    #ntuDetailTable .tabulator-cell { white-space: normal; overflow-wrap: anywhere; padding-left: 10px; padding-right: 10px; }
    #ntuTable .tabulator-col .tabulator-col-content,
    #ntuDetailTable .tabulator-col .tabulator-col-content { padding: 8px 8px; }
    #ntuTable .tabulator-col .tabulator-col-title,
    #ntuDetailTable .tabulator-col .tabulator-col-title { white-space: normal; text-overflow: clip; font-size: 12px; line-height: 1.25; }
    /* Let the heading row grow to its wrapped titles (Tabulator fixes each heading's height). */
    #ntuTable .tabulator-header .tabulator-col,
    #ntuDetailTable .tabulator-header .tabulator-col { height: auto !important; vertical-align: bottom; }
    #ntuTable .tabulator-col-group > .tabulator-col-content,
    #ntuDetailTable .tabulator-col-group > .tabulator-col-content { padding-bottom: 2px; text-align: center; }
    #ntuTable .tabulator-col-group > .tabulator-col-content .tabulator-col-title,
    #ntuDetailTable .tabulator-col-group > .tabulator-col-content .tabulator-col-title { color: #64748b; text-align: center; }
    /* The sort arrow only on the sorted column, so narrow number columns keep their title room. */
    #ntuTable .tabulator-col[aria-sort="none"] .tabulator-arrow,
    #ntuDetailTable .tabulator-col[aria-sort="none"] .tabulator-arrow { display: none; }
    #ntuTable .tabulator-col[aria-sort="asc"] .tabulator-col-title, #ntuTable .tabulator-col[aria-sort="desc"] .tabulator-col-title,
    #ntuDetailTable .tabulator-col[aria-sort="asc"] .tabulator-col-title, #ntuDetailTable .tabulator-col[aria-sort="desc"] .tabulator-col-title { padding-right: 12px !important; }
    @media (min-width: 1024px) {
        /* fitColumns can come out a pixel or two wider than the box; don't show a scrollbar for that. */
        #ntuTable .tabulator-tableHolder,
        #ntuDetailTable .tabulator-tableHolder { overflow-x: hidden !important; }
    }

    @media (min-width: 1024px) {
        .modal#ntuDetailModal .modal-dialog.modal-xl { width: 94%; max-width: 1760px; }
    }
    #ntuDetailTable.is-grouped .tabulator-row { cursor: pointer; }
    .ntu-modal .ntu-tabs { display: flex; gap: 4px; border-bottom: 1px solid #e2e8f0; }
    .ntu-modal .ntu-tab { padding: 8px 16px; margin-bottom: -1px; border: 1px solid transparent; border-bottom: 2px solid transparent; font-weight: 600; font-size: 13px; color: #64748b; background: none; }
    .ntu-modal .ntu-tab:hover { color: #0f172a; }
    .ntu-modal .ntu-tab.is-active { color: #164e63; border-bottom-color: #164e63; }
    .ntu-modal .ntu-tab--review { color: #b91c1c; }
    .ntu-modal .ntu-tab--review::before { content: ""; display: inline-block; width: 7px; height: 7px; margin-right: 6px; border-radius: 9999px; background: #dc2626; vertical-align: 1px; }
    .ntu-modal .ntu-tab--review:hover, .ntu-modal .ntu-tab--review.is-active { color: #991b1b; border-bottom-color: #dc2626; }
    /* Needs review: why the person is flagged, then each student's attendance in the classes involved. */
    .ntu-modal .ntu-review-box { border: 1px solid #fecaca; background: #fef2f2; border-radius: 8px; padding: 10px 14px; font-size: 12px; line-height: 1.5; color: #7f1d1d; }
    .ntu-modal .ntu-review-box strong { color: #991b1b; }
    .ntu-modal .ntu-review-box ul { margin: 4px 0 6px 18px; list-style: disc; }
    .ntu-modal .ntu-review-box p { color: #9f1239; opacity: .85; }
    .ntu-modal .ntu-tl { display: grid; gap: 12px; }
    .ntu-modal .ntu-tl__head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 8px 16px; margin-bottom: 6px; font-size: 12px; color: #64748b; line-height: 1.35; }
    .ntu-modal .ntu-tl__head > div:first-child { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .ntu-modal .ntu-tl__head strong { font-size: 13px; color: #0f172a; }
    .ntu-modal .ntu-tl__facts { display: flex; flex-wrap: wrap; gap: 6px; align-items: flex-start; justify-content: flex-end; }
    .ntu-modal .ntu-mini tr.is-flagged td { background: #fef2f2 !important; }
    .ntu-modal .ntu-mini tr.is-flagged td:first-child { box-shadow: inset 3px 0 0 #dc2626; }
    .ntu-modal .ntu-st--in { color: #15803d; font-weight: 600; }
    .ntu-modal .ntu-st--out { color: #b91c1c; font-weight: 600; }
    .ntu-modal .ntu-tags { display: flex; flex-wrap: wrap; gap: 4px; }
    .ntu-modal .ntu-tag { display: inline-block; font-size: 11px; font-weight: 600; line-height: 1.2; padding: 2px 7px; border-radius: 4px; background: #f1f5f9; color: #475569; }
    .ntu-modal .ntu-tag--red { background: #dc2626; color: #fff; }
    .ntu-modal .ntu-tag--red-soft { background: #fee2e2; color: #991b1b; }
    .ntu-modal .ntu-tag--amber { background: #fef3c7; color: #92400e; }
    .ntu-modal .ntu-tag--green { background: #dcfce7; color: #166534; }
    .ntu-modal .ntu-quote { margin-top: 3px; font-size: 11px; color: #475569; line-height: 1.35; }
    .ntu-modal .ntu-quote b { color: #334155; font-weight: 600; }
    .ntu-modal .ntu-toggle { display: inline-block; width: 14px; color: #64748b; font-size: 12px; transition: transform .15s; }
    .ntu-modal .ntu-toggle.is-open { transform: rotate(90deg); }
    .ntu-modal .ntu-count-line { font-size: 12px; color: #475569; font-weight: 600; }
    /* A student's own changes, opened under the student's line. */
    .ntu-modal .ntu-sub-wrap { display: block; white-space: normal; padding: 4px 16px 14px 40px; background: #fff; border-top: 1px dashed #e2e8f0; cursor: default; }
    .ntu-modal .ntu-sub-msg { padding: 10px 0; font-size: 12px; color: #64748b; }
    /* Opened rows (a student's changes, a class date's students): compact fixed-width tables. */
    .ntu-modal .ntu-mini { width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 0; font-size: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
    .ntu-modal .ntu-mini th { text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #64748b; background: #f8fafc; padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
    .ntu-modal .ntu-mini td { padding: 7px 10px; vertical-align: top; line-height: 1.35; border-bottom: 1px solid #f1f5f9; overflow-wrap: anywhere; }
    .ntu-modal .ntu-mini tbody tr:nth-child(even) td { background: #fcfdfe; }
    .ntu-modal .ntu-mini tbody tr:last-child td { border-bottom: 0; }
    /* By tutor: class date cards under a tutor, the students changed inside a card. */
    .ntu-modal .ntu-sub-wrap--classes { background: #f8fafc; padding: 10px 14px 14px 40px; }
    .ntu-modal .ntu-classes { display: grid; gap: 8px; }
    .ntu-modal .ntu-class { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
    .ntu-modal .ntu-class.is-open { border-color: #cbd5e1; box-shadow: 0 1px 3px rgba(15, 23, 42, .06); }
    .ntu-modal .ntu-class__head { display: grid; grid-template-columns: 16px minmax(96px, 120px) minmax(0, 2.2fr) minmax(0, 1.5fr) minmax(0, 1.6fr); gap: 14px; align-items: start; padding: 10px 14px; cursor: pointer; }
    .ntu-modal .ntu-class__head:hover { background: #f8fafc; }
    .ntu-modal .ntu-class.is-open .ntu-class__head { background: #f1f5f9; border-bottom: 1px solid #e2e8f0; }
    .ntu-modal .ntu-class__head .ntu-toggle { margin-top: 2px; }
    .ntu-modal .ntu-class__date, .ntu-modal .ntu-class__what, .ntu-modal .ntu-class__held { display: flex; flex-direction: column; gap: 2px; min-width: 0; font-size: 12px; color: #64748b; line-height: 1.35; }
    .ntu-modal .ntu-class__date strong, .ntu-modal .ntu-class__what strong { font-size: 13px; font-weight: 600; color: #0f172a; }
    .ntu-modal .ntu-class__held { align-items: flex-start; }
    .ntu-modal .ntu-class__facts { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
    .ntu-modal .ntu-class__body { padding: 10px 14px 12px 44px; }
    .ntu-modal .ntu-badge { display: inline-block; font-size: 11px; font-weight: 600; line-height: 1; padding: 4px 8px; border-radius: 9999px; background: #f1f5f9; color: #475569; }
    .ntu-modal .ntu-badge.is-done { background: #dcfce7; color: #166534; }
    .ntu-modal .ntu-badge.is-cancelled { background: #fee2e2; color: #991b1b; }
    .ntu-modal .ntu-badge.is-live { background: #dbeafe; color: #1e40af; }
    .ntu-modal .ntu-class__badges { display: flex; flex-wrap: wrap; gap: 6px; }
    .ntu-modal .ntu-badge--mode.is-online { background: #cffafe; color: #0e7490; }
    .ntu-modal .ntu-badge--mode.is-physical { background: #fff; color: #334155; box-shadow: inset 0 0 0 1px #cbd5e1; }
    .ntu-modal .ntu-warn { color: #b45309; font-weight: 600; }
    .ntu-modal .ntu-fact { font-size: 11px; line-height: 1.3; padding: 3px 8px; border-radius: 9999px; background: #f1f5f9; color: #334155; }
    .ntu-modal .ntu-fact--main { background: #e0f2fe; color: #075985; }
    .ntu-modal .ntu-fact--main strong { font-size: 12px; }
    .ntu-modal .ntu-fact--alert { background: #fee2e2; color: #991b1b; font-weight: 600; }
    .ntu-modal .ntu-fact--muted { background: none; color: #64748b; padding-left: 0; padding-right: 0; white-space: normal; text-align: right; }
    .ntu-modal .ntu-roles { margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px; }
    .ntu-modal .ntu-role { font-size: 10px; font-weight: 600; padding: 1px 6px; border-radius: 4px; background: #e0f2fe; color: #075985; white-space: nowrap; }
    .ntu-modal .ntu-role--pt { background: #ede9fe; color: #5b21b6; }
    .ntu-modal .ntu-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .ntu-modal .ntu-chip { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; background: #f8fafc; min-width: 110px; }
    .ntu-modal .ntu-chip__value { font-size: 16px; font-weight: 700; color: #0f172a; }
    .ntu-modal .ntu-chip__label { font-size: 11px; color: #64748b; }
    .ntu-modal .ntu-chip--alert .ntu-chip__value { color: #b91c1c; }
    .ntu-modal .ntu-cell { line-height: 1.3; white-space: normal; }
    .ntu-modal .ntu-cell__sub { font-size: 11px; color: #64748b; }
    .ntu-modal .ntu-cell__badge { margin-top: 4px; }
    .ntu-modal .ntu-cell__badge .ntu-badge { padding: 3px 7px; font-size: 10.5px; }
    .ntu-modal .ntu-tl__head strong .ntu-badge { vertical-align: 1px; margin-left: 4px; padding: 3px 7px; font-size: 10.5px; }
    /* Student status (students.status_id): green while studying, red once they have left. */
    .ntu-modal .ntu-sstatus { display: inline-block; max-width: 100%; font-size: 10.5px; font-weight: 600; line-height: 1.25; padding: 2px 7px; border-radius: 4px; background: #f1f5f9; color: #475569; white-space: normal; overflow-wrap: anywhere; }
    .ntu-modal .ntu-sstatus.is-on { background: #dcfce7; color: #166534; }
    .ntu-modal .ntu-sstatus.is-left { background: #fee2e2; color: #991b1b; }
    .ntu-modal .ntu-note { font-size: 12px; color: #64748b; line-height: 1.5; }

    /* Date range box: calendar icon on the left, clear button on the right. */
    #ntuReport .ntu-date { position: relative; }
    #ntuReport .ntu-date__icon { position: absolute; left: 0; top: 0; bottom: 0; width: 40px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px 0 0 6px; color: #64748b; pointer-events: none; }
    #ntuReport .ntu-date .form-control { padding-left: 52px; padding-right: 34px; background-color: #fff; cursor: pointer; }
    #ntuReport .ntu-date__clear { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #94a3b8; }
    #ntuReport .ntu-date__clear:hover { color: #475569; background: #f1f5f9; }
</style>
@endsection

@section('subcontent')
    <div id="ntuReport">
        <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Non-Tutor Attendance Updates</h2>
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('reports') }}" class="btn btn-primary shadow-md mr-2">Back to Reports</a>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <p class="ntu-note mb-4">
                Attendance that now says <strong>Present</strong> and was last changed by staff with no link to the class: not the class tutor, personal tutor, cover tutor or tutor of the parent theory class, and not the person who started the class.
                Each attendance row only remembers the <strong>last</strong> person who changed it, so if the tutor saved the row again afterwards, the earlier change is not shown. Treat every number as a minimum. Times are UK time.
            </p>
            <form action="#" id="ntuSearchForm" autocomplete="off">
                <div class="grid grid-cols-12 gap-0 gap-y-3 gap-x-4">
                    <div class="col-span-12 lg:col-span-5">
                        <label for="ntuTerms" class="form-label">Attendance Term</label>
                        <select id="ntuTerms" class="w-full" multiple name="terms[]">
                            @foreach($terms as $term)
                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-7 lg:col-span-3">
                        <label for="ntuDateRange" class="form-label">Date Range</label>
                        <div class="ntu-date">
                            <div class="ntu-date__icon"><i data-lucide="calendar" class="w-4 h-4"></i></div>
                            <input type="text" id="ntuDateRange" class="form-control" placeholder="Any date" readonly>
                            <button type="button" class="ntu-date__clear" id="ntuDateClear" title="Clear date range" aria-label="Clear date range" style="display: none;">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-5 lg:col-span-2">
                        <label for="ntuDateOn" class="form-label">Range On</label>
                        <select id="ntuDateOn" class="form-select w-full">
                            @foreach(\App\Services\Reports\NonTutorAttendanceUpdates::DATE_ON as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 lg:col-span-2 mt-auto text-right">
                        <button type="submit" id="ntuSearchBtn" class="btn btn-success text-white w-full lg:w-auto">
                            <i class="w-4 h-4 mr-2" data-lucide="search"></i> Search
                        </button>
                    </div>
                    <div class="col-span-12 ntu-note" id="ntuFormHint">
                        Choose a term, a date range, or both. <strong>Class date</strong>: classes held in the range. <strong>Date changed</strong>: attendance changed in the range (UK dates).
                    </div>
                    <div class="col-span-12 text-danger text-xs" id="ntuTermsError" style="display: none;">Please select an attendance term or a date range.</div>
                </div>
            </form>

            <div class="mt-4 text-danger" id="ntuError" style="display: none;"></div>

            <div class="pt-5" id="ntuResults" style="display: none;">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                    <div class="ntu-count mr-auto" id="ntuCount"></div>
                    <button type="button" id="ntuExportBtn" class="btn btn-facebook w-auto">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel
                    </button>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="ntuTable" class="table-report table-report--tabulator"></div>
                </div>
                <p class="ntu-note mt-3">
                    <span class="ntu-legend"></span><strong>Red rows need review</strong> (patterns that need an explanation, not proof of fraud): a student was marked Present after their own excuse for that class was rejected, or at least 10 (and 15% or more) of the absences this person changed to Present are a lone Present between two absences of the same class. When class tutors correct their own register that is about 4%.
                    Click a red row and open <strong>Needs review</strong> for the students and their attendance.<br>
                    Click a row to see that person's changes.
                    <strong>Single change</strong>: one student changed on the student's attendance page.
                    <strong>Register saved again</strong>: the whole class register was saved again, which marks every row as changed even if its status stayed the same.
                </p>
            </div>
        </div>
    </div>

    <!-- BEGIN: Details Modal -->
    <div id="ntuDetailModal" class="modal ntu-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="mr-auto">
                        <h2 class="font-medium text-base" id="ntuDetailName"></h2>
                        <div class="ntu-note" id="ntuDetailMeta"></div>
                    </div>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="ntu-chips mb-4" id="ntuDetailStats"></div>
                    <div class="ntu-tabs mb-3" id="ntuDetailTabs">
                        <button type="button" class="ntu-tab is-active" data-view="rows" aria-pressed="true">All changes</button>
                        <button type="button" class="ntu-tab" data-view="students" aria-pressed="false">By student</button>
                        <button type="button" class="ntu-tab" data-view="tutor" aria-pressed="false">By tutor / personal tutor</button>
                        <button type="button" class="ntu-tab ntu-tab--review" data-view="review" aria-pressed="false" id="ntuReviewTab" hidden>Needs review</button>
                    </div>
                    <div class="ntu-review-box mb-3" id="ntuReviewBox" hidden></div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                        <input type="text" id="ntuDetailSearch" class="form-control sm:w-72 mr-auto" placeholder="Search student, module or group">
                        <button type="button" id="ntuDetailExportBtn" class="btn btn-facebook w-auto">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel
                        </button>
                    </div>
                    <div class="ntu-count-line mb-2" id="ntuDetailCount"></div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="ntuDetailTable" class="table-report table-report--tabulator"></div>
                    </div>
                    <p class="ntu-note mt-3">
                        <strong>All changes</strong>: every row, newest change first. <strong>By student</strong>: one line per student with how many of their rows were changed, most first; click a student to see their changes.
                        <strong>By tutor / personal tutor</strong>: grouped by who teaches the class (its tutor, or its personal tutor when the class has no tutor); click a tutor for their class dates (whether the class was held and how many of its register's rows were changed), then a class date for the students whose attendance was changed.
                        <strong>Needs review</strong> (red people only): the students whose absence this person changed to Present (proven: the register recorded an absence notice); click a student for every class date of those classes, so a lone Present between absences and any rejected excuse show.
                        The search applies to every tab, and Export Excel exports the tab you are on. "After class" is the time between the end of the class and the change, in UK time.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Details Modal -->
@endsection

@section('script')
    @vite('resources/js/non-tutor-attendance-report.js')
@endsection
