{{--
    Term Performance › Submission Performance, "New version" tab (user's
    submission-performance-report design, 25 Nov 2026). One attendance term; drawn by
    resources/js/term-submission-new.js from SubmissionPerformanceController::generate.
    Download PDF and Export Excel show once a report is generated.
--}}
<div class="tar tsp" data-tsp
     data-generate="reports.term.performance.submission.new.generate"
     data-pdf="reports.term.performance.submission.new.pdf"
     data-excel="reports.term.performance.submission.new.excel"
     data-students="reports.term.performance.submission.new.students">
    <form class="tar-form" action="#" method="get" novalidate data-tsp-form>
        <div class="tar-field">
            <label for="tspTerm" class="tar-label">Attendance term <span class="text-danger">*</span></label>
            <select id="tspTerm" class="w-full" data-tsp-term>
                <option value="">Please select</option>
                @foreach($terms as $trm)
                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                @endforeach
            </select>
            <div class="tar-error" role="alert" data-tsp-error></div>
        </div>
        <div class="tar-actions">
            <button type="submit" class="tar-btn tar-btn--primary" data-tsp-generate>
                <i data-lucide="bar-chart-3" class="tar-btn__icon"></i>
                <span class="tar-spinner" aria-hidden="true"></span>
                Generate Report
            </button>
            <a href="javascript:void(0);" class="tar-btn tar-btn--blue" hidden data-tsp-pdf><i data-lucide="file-down" class="tar-btn__icon"></i> Download PDF</a>
            <a href="javascript:void(0);" class="tar-btn tar-btn--soft" hidden data-tsp-excel><i data-lucide="file-spreadsheet" class="tar-btn__icon"></i> Export Excel</a>
        </div>
    </form>

    <div aria-live="polite" data-tsp-result></div>

    {{--
        The students behind a count of the Full figures table (user, 25 Nov 2026; the old version
        has the same list in #submissionPerformanceSTDListModal). Styles are scoped to
        .modal.tsp-modal because the theme moves an open modal to <body>.
    --}}
    <div id="tspStudentsModal" class="modal tsp-modal" tabindex="-1" aria-hidden="true" aria-labelledby="tspStudentsTitle">
        <div class="modal-dialog tsp-modal__dialog">
            <div class="modal-content">
                <div class="modal-header tsp-modal__head">
                    <span class="tsp-modal__icon" aria-hidden="true"><i data-lucide="users"></i></span>
                    <div class="tsp-modal__titles">
                        <h2 class="tsp-modal__title" id="tspStudentsTitle" data-tsp-m-title>Students</h2>
                        <p class="tsp-modal__sub" data-tsp-m-sub></p>
                    </div>
                    <a data-tw-dismiss="modal" href="javascript:;" class="tsp-modal__close" aria-label="Close"><i data-lucide="x"></i></a>
                </div>
                <div class="modal-body tsp-modal__body" aria-live="polite" data-tsp-m-body></div>
                <div class="modal-footer tsp-modal__foot">
                    <span class="tsp-modal__range" data-tsp-m-range></span>
                    <div class="tsp-modal__pager" data-tsp-m-pager hidden>
                        <button type="button" class="tsp-mbtn tsp-mbtn--icon" data-tsp-m-prev aria-label="Previous page"><i data-lucide="chevron-left"></i></button>
                        <span data-tsp-m-page></span>
                        <button type="button" class="tsp-mbtn tsp-mbtn--icon" data-tsp-m-next aria-label="Next page"><i data-lucide="chevron-right"></i></button>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="tsp-mbtn">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
