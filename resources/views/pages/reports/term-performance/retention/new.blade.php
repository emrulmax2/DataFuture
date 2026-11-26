{{--
    Term Performance › Retention, "New version" tab (user's term-retention-report design,
    25 Nov 2026). One or more attendance terms; drawn by resources/js/term-retention-new.js from
    RetentionByIntakeController::generate. Download PDF and Export Excel show once a report is
    generated. A count opens its students in #trtStudentsModal.
--}}
<div class="tar trt" data-trt
     data-generate="reports.term.performance.retention.new.generate"
     data-pdf="reports.term.performance.retention.new.pdf"
     data-excel="reports.term.performance.retention.new.excel"
     data-students="reports.term.performance.retention.new.students">
    <form class="tar-form" action="#" method="get" novalidate data-trt-form>
        <div class="tar-field trt-field">
            <label for="trtTerms" class="tar-label">Attendance terms <span class="text-danger">*</span></label>
            <select id="trtTerms" class="w-full" multiple data-trt-terms>
                @foreach($terms as $trm)
                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                @endforeach
            </select>
            <div class="tar-error" role="alert" data-trt-error></div>
        </div>
        <div class="tar-actions">
            <button type="submit" class="tar-btn tar-btn--primary" data-trt-generate>
                <i data-lucide="bar-chart-3" class="tar-btn__icon"></i>
                <span class="tar-spinner" aria-hidden="true"></span>
                Generate Report
            </button>
            <a href="javascript:void(0);" class="tar-btn tar-btn--blue" hidden data-trt-pdf><i data-lucide="file-down" class="tar-btn__icon"></i> Download PDF</a>
            <a href="javascript:void(0);" class="tar-btn tar-btn--soft" hidden data-trt-excel><i data-lucide="file-spreadsheet" class="tar-btn__icon"></i> Export Excel</a>
        </div>
    </form>

    <div aria-live="polite" data-trt-result></div>

    {{-- The students behind a count. Scoped to .modal.trt-modal: the theme moves an open modal to <body>. --}}
    <div id="trtStudentsModal" class="modal trt-modal" tabindex="-1" aria-hidden="true" aria-labelledby="trtStudentsTitle">
        <div class="modal-dialog trt-modal__dialog">
            <div class="modal-content">
                <div class="trt-modal__head">
                    <div>
                        <div class="trt-modal__kicker" data-trt-m-kicker></div>
                        <h2 class="trt-modal__title" id="trtStudentsTitle" data-trt-m-title>Students</h2>
                    </div>
                    <button type="button" class="trt-modal__x" data-tw-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="trt-modal__bar">
                    <label class="trt-modal__search">
                        <span>Search</span>
                        <input type="search" placeholder="Student ID or name" autocomplete="off" data-trt-m-q>
                    </label>
                    <button type="button" class="trt-mbtn" data-trt-m-excel disabled>Export Excel</button>
                </div>
                <div class="trt-modal__scroll" aria-live="polite" data-trt-m-body></div>
                <div class="trt-modal__foot">
                    <span data-trt-m-count></span>
                    <span class="trt-modal__pager">
                        <button type="button" class="trt-mbtn" data-trt-m-page="-1">Previous</button>
                        <span data-trt-m-page-label></span>
                        <button type="button" class="trt-mbtn" data-trt-m-page="1">Next</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
