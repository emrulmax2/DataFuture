{{--
    Attendance Reports, "New version" tab (user's attendance-report design, 24 Nov 2026).
    Driven by resources/js/attendance-report-new.js; data from AttendanceReportNewController.
    The SMS / Email / Letter dialogs are the page's shared modals (index.blade.php), sent by
    the old script's handlers; this tab only fills in the ticked students.
--}}
@php
    $atrConfig = [
        'routes' => [
            'list' => 'report.attendance.reports.new.list',
            'export' => 'report.attendance.reports.new.export',
            'options' => 'student.get.all.student.type',
        ],
        'bands' => ['low' => \App\Services\Reports\StudentAttendanceReport::LOW, 'mid' => \App\Services\Reports\StudentAttendanceReport::MID, 'high' => \App\Services\Reports\StudentAttendanceReport::HIGH],
        'counts' => array_map(fn ($c) => ['key' => $c[0], 'label' => $c[2], 'name' => $c[3]], \App\Services\Reports\StudentAttendanceReport::COUNTS),
    ];
@endphp
<script type="application/json" data-atr-config>@json($atrConfig)</script>

<form class="atr-card atr-filters" action="#" method="get" novalidate data-atr-form>
    <div class="atr-field">
        <label for="atrIntake">Intake Semester <i>*</i></label>
        <select id="atrIntake" name="intake_semester[]" multiple data-atr-filter="intake_semester" data-placeholder="All intakes">
            @foreach($semesters as $sem)
                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="atr-field">
        <label for="atrTerm">Attendance Semester <i>*</i></label>
        <select id="atrTerm" name="attendance_semester[]" multiple data-atr-filter="attendance_semester" data-placeholder="All attendance semesters">
            @foreach($terms as $trm)
                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="atr-field">
        <label for="atrCourse">Course</label>
        <select id="atrCourse" name="course[]" multiple data-atr-filter="course" data-placeholder="All courses">
            @foreach($courses as $crs)
                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="atr-field">
        <label for="atrGroup">Master Group</label>
        {{-- Filled from the other filters' students, as in the old version. --}}
        <select id="atrGroup" name="group[]" multiple data-atr-filter="group" data-placeholder="All groups"></select>
    </div>
    <div class="atr-field">
        <label for="atrStatus">Student Status</label>
        <select id="atrStatus" name="group_student_status[]" multiple data-atr-filter="group_student_status" data-placeholder="All statuses">
            @foreach($allStatuses as $sts)
                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="atr-field">
        <label for="atrPercent">Percentage</label>
        <select id="atrPercent" name="attendance_percentage" class="atr-native" data-atr-percent>
            <option value="">Any percentage</option>
            <option value="0.00">0% only</option>
            @foreach([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $p)
                <option value="{{ $p }}.00">Below {{ $p }}%</option>
            @endforeach
        </select>
    </div>
    <div class="atr-filter-actions">
        <button class="atr-btn atr-btn--text" type="reset" data-atr-reset>Reset</button>
        <button class="atr-btn atr-btn--primary" type="submit" data-atr-search>
            <svg class="atr-btn__icon" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <span class="atr-spinner" aria-hidden="true"></span>
            Search
        </button>
    </div>
    <p class="atr-form-error" role="alert" data-atr-error hidden></p>
</form>

<section class="atr-card atr-results" aria-label="Students" data-atr-results>
    <div class="atr-start" data-atr-start>
        <span class="atr-start__icon" aria-hidden="true"><i data-lucide="calendar-check"></i></span>
        <p><b>No report yet.</b> Choose an intake or attendance semester above, then press Search.</p>
    </div>

    <div data-atr-report hidden>
        <div class="atr-toolbar">
            <div class="atr-chips" role="group" aria-label="Attendance band" data-atr-chips></div>
            <div class="atr-tools">
                {{-- Ticked students (design's selection pill): the old version's SMS / Email / Letter, plus Excel. --}}
                <div class="atr-bulk" data-atr-bulk hidden>
                    <span data-atr-bulk-count>0 selected</span>
                    <button type="button" class="atr-btn atr-btn--sm atr-btn--ghost" data-atr-act="sms"><i data-lucide="smartphone"></i> Send SMS</button>
                    <button type="button" class="atr-btn atr-btn--sm atr-btn--ghost" data-atr-act="email"><i data-lucide="mail"></i> Send Email</button>
                    <button type="button" class="atr-btn atr-btn--sm atr-btn--ghost" data-atr-act="letter"><i data-lucide="mailbox"></i> Generate Letter</button>
                    <button type="button" class="atr-btn atr-btn--sm atr-btn--primary" data-atr-act="export-selected">
                        <i data-lucide="file-spreadsheet"></i><span class="atr-spinner" aria-hidden="true"></span> Export selected
                    </button>
                    <button type="button" class="atr-btn atr-btn--sm atr-btn--text" data-atr-act="clear">Clear</button>
                </div>
                <div class="atr-quick">
                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <label class="atr-sr" for="atrQuick">Filter this page</label>
                    <input id="atrQuick" type="search" placeholder="Filter this page: name, ID, group…" autocomplete="off" data-atr-quick>
                </div>
            </div>
        </div>

        <div class="atr-key">
            <span class="atr-key__title">Key</span>
            @foreach(\App\Services\Reports\StudentAttendanceReport::COUNTS as [$key, $alias, $label, $name])
                <span><b>{{ $label }}</b> {{ $name }}</span>
            @endforeach
            <span><b>With excuse</b> Excused absences count as attended</span>
            <span><b>W/O excuse</b> Excused absences do not count</span>
        </div>

        <div class="atr-scroll" data-atr-scroll>
            <table class="atr-table">
                <thead>
                    <tr>
                        <th scope="col" class="atr-col-check"><input type="checkbox" aria-label="Select all on this page" data-atr-all></th>
                        <th scope="col" class="atr-col-student"><button type="button" data-sort="name">Student</button></th>
                        <th scope="col" class="atr-col-course"><button type="button" data-sort="group">Course · Group</button></th>
                        <th scope="col" class="atr-col-refs">References</th>
                        @foreach(\App\Services\Reports\StudentAttendanceReport::COUNTS as [$key, $alias, $label, $name])
                            <th scope="col" class="atr-num" title="{{ $name }}"><button type="button" data-sort="{{ $key }}">{{ $label }}</button></th>
                        @endforeach
                        <th scope="col" class="atr-att"><button type="button" data-sort="pct">Attendance</button></th>
                    </tr>
                </thead>
                <tbody data-atr-body></tbody>
            </table>
            <div class="atr-empty" data-atr-empty hidden></div>
            <div class="atr-loading" data-atr-loading hidden><span class="atr-spinner atr-spinner--lg" aria-hidden="true"></span><span>Loading students…</span></div>
        </div>

        <div class="atr-foot">
            <div>
                Showing <b data-atr-shown>0</b> of <b data-atr-total>0</b>
                <label>&nbsp;· Page size
                    <select data-atr-size>
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                </label>
            </div>
            <nav class="atr-pager" aria-label="Pagination" data-atr-pager></nav>
        </div>
    </div>
</section>
