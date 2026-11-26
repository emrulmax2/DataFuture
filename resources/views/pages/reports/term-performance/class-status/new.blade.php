{{--
    Term Performance › Class Status, "New version" tab (user's class-status-report design,
    25 Nov 2026). One attendance term; drawn by resources/js/term-class-status-new.js from
    ClassStatusReportController::generate. Download PDF and Export Excel show once a report is
    generated.
--}}
<div class="tar tcs" data-tcs
     data-generate="reports.term.performance.class.status.generate"
     data-pdf="reports.term.performance.class.status.pdf"
     data-excel="reports.term.performance.class.status.excel">
    <form class="tar-form" action="#" method="get" novalidate data-tcs-form>
        <div class="tar-field">
            <label for="tcsTerm" class="tar-label">Attendance term <span class="text-danger">*</span></label>
            <select id="tcsTerm" class="w-full" data-tcs-term>
                <option value="">Please select</option>
                @foreach($terms as $trm)
                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                @endforeach
            </select>
            <div class="tar-error" role="alert" data-tcs-error></div>
        </div>
        <div class="tar-actions">
            <button type="submit" class="tar-btn tar-btn--primary" data-tcs-generate>
                <i data-lucide="bar-chart-3" class="tar-btn__icon"></i>
                <span class="tar-spinner" aria-hidden="true"></span>
                Generate Report
            </button>
            <a href="javascript:void(0);" class="tar-btn tar-btn--blue" hidden data-tcs-pdf><i data-lucide="file-down" class="tar-btn__icon"></i> Download PDF</a>
            <a href="javascript:void(0);" class="tar-btn tar-btn--soft" hidden data-tcs-excel><i data-lucide="file-spreadsheet" class="tar-btn__icon"></i> Export Excel</a>
        </div>
    </form>

    <div aria-live="polite" data-tcs-result></div>
</div>
