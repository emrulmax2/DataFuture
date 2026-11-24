{{--
    Term Performance › Attendance Rates, "New version" tab (user's attendance-term-report design,
    24 Nov 2026). One attendance semester; the result is drawn by
    resources/js/term-attendance-report-new.js from TermAttendanceRateController::generate (the
    whole term as a tree, so the course / group / module drill-down asks for nothing more).
--}}
<div class="tar" data-tar
     data-generate="reports.term.performance.attendance.rate.generate"
     data-pdf="reports.term.performance.attendance.rate.pdf">
    <form class="tar-form" action="#" method="get" novalidate data-tar-form>
        <div class="tar-field">
            <label for="tarTerm" class="tar-label">Attendance semester <span class="text-danger">*</span></label>
            <select id="tarTerm" class="w-full" data-tar-term>
                <option value="">Please select</option>
                @foreach($terms as $trm)
                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                @endforeach
            </select>
            <div class="tar-error" role="alert" data-tar-error></div>
        </div>
        <div class="tar-actions">
            <button type="submit" class="tar-btn tar-btn--primary" data-tar-generate>
                <i data-lucide="bar-chart-3" class="tar-btn__icon"></i>
                <span class="tar-spinner" aria-hidden="true"></span>
                Generate Report
            </button>
            {{-- Shown once a report is generated; downloads the level on screen. --}}
            <a href="javascript:void(0);" class="tar-btn tar-btn--blue" hidden data-tar-pdf><i data-lucide="file-down" class="tar-btn__icon"></i> Download PDF</a>
        </div>
    </form>

    <div aria-live="polite" data-tar-result></div>
</div>
