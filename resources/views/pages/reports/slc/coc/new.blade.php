{{--
    SLC Reports › SLC COC History, "New version" tab (user's slc-coc-history design, 25 Nov 2026).
    As the other SLC reports: nothing shows until Search; then resources/js/slc-coc-history.js loads
    every change of circumstances (SlcCocHistoryController::data; few rows, the design's "All dates"
    default), filters it in the browser and copies the search into the Old version's form. Export
    Excel (beside Search, once searched) downloads the selection. Styles: `.sah-*` in slc-reports.css.
--}}
<div class="sah sch" data-sch
     data-data="reports.slc.coc.history.data"
     data-excel="reports.slc.coc.history.excel"
     data-types='@json($schTypes ?? [])'
     data-courses='@json($schCourses ?? [])'
     data-today="{{ now()->toDateString() }}">
    <section class="sah-card" aria-label="Filters"><div class="sah-filters" data-sch-filters></div></section>

    <div class="sah-state" aria-live="polite" data-sch-state>Choose the dates and any filters, then select Search.</div>

    <div class="sah-body" data-sch-body hidden>
        <div class="sah-kpis" data-sch-kpis></div>

        <section class="sah-card">
            <div class="sah-head">
                <h2>By type of change</h2>
                <span class="sah-hint"><i class="sah-sw" style="background:var(--sah-orange)"></i>Not actioned <i class="sah-sw" style="background:var(--sah-teal);margin-left:12px"></i>Actioned · select a row to filter</span>
            </div>
            <div data-sch-bytype></div>
        </section>

        <section class="sah-card">
            <div class="sah-tbar">
                <div><h2>Changes of circumstances</h2><div class="sah-hint">Not actioned first, longest waiting at the top.</div></div>
                <label class="sah-f">
                    <span>Find a student</span>
                    <input type="search" placeholder="Registration number or name" autocomplete="off" data-sch-q>
                </label>
            </div>
            <div class="sah-scroll"><table class="sah-table" data-sch-table></table></div>
            <div class="sah-pfoot">
                <span data-sch-tcount></span>
                <span class="sah-pager">
                    <button type="button" class="sah-btn" data-sch-page="-1">Previous</button>
                    <span data-sch-tpage></span>
                    <button type="button" class="sah-btn" data-sch-page="1">Next</button>
                </span>
            </div>
        </section>
    </div>
</div>
