{{--
    SLC Reports › SLC Registration History, "New version" tab (user's slc-registration-history
    design, 25 Nov 2026). As SLC Attendance History: nothing shows until Search; then
    resources/js/slc-registration-history.js loads every registration confirmation of the date range
    (SlcRegistrationHistoryController::data), filters it in the browser and copies the search into
    the Old version's form. Export Excel (beside Search, once searched) downloads the selection.
    Styles: the `.sah-*` rules in slc-reports.css.
--}}
<div class="sah srh" data-srh
     data-data="reports.slc.registration.history.data"
     data-excel="reports.slc.registration.history.excel"
     data-statuses='@json($srhStatuses ?? [])'
     data-years='@json($srhYears ?? [])'
     data-courses='@json($srhCourses ?? [])'
     data-from="{{ now()->subYear()->addDay()->toDateString() }}"
     data-to="{{ now()->toDateString() }}">
    <section class="sah-card" aria-label="Filters"><div class="sah-filters" data-srh-filters></div></section>

    <div class="sah-state" aria-live="polite" data-srh-state>Choose the dates and any filters, then select Search.</div>

    <div class="sah-body" data-srh-body hidden>
        <div class="sah-kpis" data-srh-kpis></div>

        <section class="sah-card">
            <div class="sah-head"><h2>When registrations were confirmed</h2><span class="sah-hint">One square per day. Select a day to see only that day.</span></div>
            <div class="sah-calwrap"><div class="sah-cal" data-srh-cal></div></div>
            <div class="sah-calfoot" data-srh-calfoot></div>
        </section>

        <div class="sah-grid2">
            <section class="sah-card"><div class="sah-head"><h2>By registration status</h2><span class="sah-hint">Registrations · share</span></div><div data-srh-bystatus></div></section>
            <section class="sah-card"><div class="sah-head"><h2>By academic year and registration year</h2><span class="sah-hint">Select a cell to filter</span></div><div class="sah-matrix" data-srh-matrix></div></section>
            <section class="sah-card"><div class="sah-head"><h2>By course</h2><span class="sah-hint">Registrations · confirmed as registered</span></div><div data-srh-bycourse></div></section>
            <section class="sah-card"><div class="sah-head"><h2>Worth checking</h2><span class="sah-hint">In the current selection</span></div><ul class="sah-checks" data-srh-checks></ul></section>
        </div>

        <section class="sah-card">
            <div class="sah-tbar">
                <h2>Registrations</h2>
                <label class="sah-f">
                    <span>Find a student</span>
                    <input type="search" placeholder="Registration number or name" autocomplete="off" data-srh-q>
                </label>
            </div>
            <div class="sah-scroll"><table class="sah-table" data-srh-table></table></div>
            <div class="sah-pfoot">
                <span data-srh-tcount></span>
                <span class="sah-pager">
                    <button type="button" class="sah-btn" data-srh-page="-1">Previous</button>
                    <span data-srh-tpage></span>
                    <button type="button" class="sah-btn" data-srh-page="1">Next</button>
                </span>
            </div>
        </section>
    </div>
    <div class="sah-tip" data-srh-tip hidden></div>
</div>
