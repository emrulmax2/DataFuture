{{--
    SLC Reports › SLC Record Report, "New version" tab (user's slc-record-report web design,
    25 Nov 2026). Nothing shows until Generate report; resources/js/slc-record.js then loads the
    chosen intakes (SlcRecordController::data, both record sets at once), draws the journey, the
    exits and the intake/course table, and runs the Old version with the same intakes. A count opens
    its students in #srrStudentsModal. Styles: `.srr*` and `.sah*` in slc-reports.css.
--}}
<div class="sah srr" data-srr
     data-data="reports.slc.record.new.data"
     data-students="reports.slc.record.new.students"
     data-excel="reports.slc.record.new.excel"
     data-pdf="reports.slc.record.new.pdf"
     data-details="reports.slc.record.new.details"
     data-semesters='@json($srrSemesters ?? [])'>
    <div class="srr-bar" data-srr-bar></div>
    <div class="srr-dirty" role="status" data-srr-dirty hidden></div>

    <div class="sah-state" aria-live="polite" data-srr-state>Select one or more intake semesters, then select Generate report.</div>
    <div class="srr-report" aria-live="polite" data-srr-report hidden></div>

    {{-- The students behind a count. Scoped to .modal.srr-modal: the theme moves an open modal to <body>. --}}
    <div id="srrStudentsModal" class="modal srr-modal" tabindex="-1" aria-hidden="true" aria-labelledby="srrStudentsTitle">
        <div class="modal-dialog srr-modal__dialog">
            <div class="modal-content">
                <div class="srr-modal__head">
                    <div>
                        <div class="srr-modal__kicker" data-srr-m-kicker></div>
                        <h2 class="srr-modal__title" id="srrStudentsTitle" data-srr-m-title>Students</h2>
                    </div>
                    <button type="button" class="srr-modal__x" data-tw-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="srr-modal__bar">
                    <label class="srr-modal__search">
                        <span>Search</span>
                        <input type="search" placeholder="Student ID or name" autocomplete="off" data-srr-m-q>
                    </label>
                    <button type="button" class="srr-mbtn" data-srr-m-excel disabled>Export Excel</button>
                </div>
                <div class="srr-modal__scroll" aria-live="polite" data-srr-m-body></div>
                <div class="srr-modal__foot">
                    <span data-srr-m-count></span>
                    <span class="srr-modal__pager">
                        <button type="button" class="srr-mbtn" data-srr-m-page="-1">Previous</button>
                        <span data-srr-m-page-label></span>
                        <button type="button" class="srr-mbtn" data-srr-m-page="1">Next</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
