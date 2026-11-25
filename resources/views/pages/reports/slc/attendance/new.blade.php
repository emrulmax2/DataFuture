{{--
    SLC Reports › SLC Attendance History, "New version" tab (user's slc-attendance-history design,
    25 Nov 2026). Nothing shows until Search (user): resources/js/slc-attendance-history.js then
    loads every confirmation of the date range (SlcAttendanceHistoryController::data), filters it
    in the browser and copies the search into the Old version's form. Export Excel (beside Search,
    shown once searched) downloads the current selection with the old export's columns.
--}}
<div class="sah" data-sah
     data-data="reports.slc.attendance.history.data"
     data-excel="reports.slc.attendance.history.excel"
     data-codes='@json($sahCodes ?? [])'
     data-courses='@json($sahCourses ?? [])'
     data-from="{{ now()->subYear()->addDay()->toDateString() }}"
     data-to="{{ now()->toDateString() }}">
    <section class="sah-card" aria-label="Filters"><div class="sah-filters" data-sah-filters></div></section>

    <div class="sah-state" aria-live="polite" data-sah-state>Choose the dates and any filters, then select Search.</div>

    <div class="sah-body" data-sah-body hidden>
        <div class="sah-kpis" data-sah-kpis></div>

        <section class="sah-card">
            <div class="sah-head"><h2>When confirmations were sent</h2><span class="sah-hint">One square per day. Select a day to see only that day.</span></div>
            <div class="sah-calwrap"><div class="sah-cal" data-sah-cal></div></div>
            <div class="sah-calfoot" data-sah-calfoot></div>
        </section>

        <div class="sah-grid2">
            <section class="sah-card"><div class="sah-head"><h2>By attendance code</h2><span class="sah-hint">Confirmations · claim amount</span></div><div data-sah-bycode></div></section>
            <section class="sah-card"><div class="sah-head"><h2>By year and term</h2><span class="sah-hint">Select a cell to filter</span></div><div class="sah-matrix" data-sah-matrix></div></section>
            <section class="sah-card"><div class="sah-head"><h2>By course</h2><span class="sah-hint">Confirmations · claim amount</span></div><div data-sah-bycourse></div></section>
            <section class="sah-card"><div class="sah-head"><h2>Worth checking</h2><span class="sah-hint">In the current selection</span></div><ul class="sah-checks" data-sah-checks></ul></section>
        </div>

        <section class="sah-card">
            <div class="sah-tbar">
                <h2>Confirmations</h2>
                <label class="sah-f">
                    <span>Find a student</span>
                    <input type="search" placeholder="Registration number or name" autocomplete="off" data-sah-q>
                </label>
            </div>
            <div class="sah-scroll"><table class="sah-table" data-sah-table></table></div>
            <div class="sah-pfoot">
                <span data-sah-tcount></span>
                <span class="sah-pager">
                    <button type="button" class="sah-btn" data-sah-page="-1">Previous</button>
                    <span data-sah-tpage></span>
                    <button type="button" class="sah-btn" data-sah-page="1">Next</button>
                </span>
            </div>
        </section>
    </div>
    <div class="sah-tip" data-sah-tip hidden></div>
</div>
