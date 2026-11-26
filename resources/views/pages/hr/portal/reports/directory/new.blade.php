{{-- Employee Telephone Directory, new version (user's employee-telephone-directory design, 26 Nov 2026).
     Search first; the eight HR filters sit under More filters and apply with Go (as the old form). The
     A–Z rail, quick filters and the directory are drawn by resources/js/hr-telephone-directory-new.js. --}}
<form class="etd-panel etd-find" role="search" aria-label="Directory search" data-etd-form>
    <label class="etd-big"><span class="etd-sr" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Search the directory</span>
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="9" r="6"/><path d="M14 14l4 4"/></svg>
        <input type="search" data-etd-q placeholder="Search a name, number or email" autocomplete="off">
    </label>
    <details data-etd-more><summary>More filters <span data-etd-more-note></span></summary>
    <div class="etd-form">
            <label class="etd-field"><span>Type</span>
                <select class="etd-ctl" data-etd-f="type">
                    <option value="">All types</option>
                    @foreach($employeeWorkType->sortBy('name') as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="etd-field"><span>Department</span>
                <select class="etd-ctl" data-etd-f="department">
                    <option value="">All departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="etd-field"><span>Start date</span>
                <input class="etd-ctl" type="text" data-hr-date data-etd-f="from" title="Staff who started on or before this date">
                <small>Started on or before</small>
            </label>
            <label class="etd-field"><span>End date</span>
                <input class="etd-ctl" type="text" data-hr-date data-etd-f="to" title="Staff whose employment ended on or after this date">
                <small>Left on or after</small>
            </label>
            <label class="etd-field"><span>Ethnicity</span>
                <select class="etd-ctl" data-etd-f="ethnicity"><option value="">All ethnicities</option></select>
            </label>
            <label class="etd-field"><span>Nationality</span>
                <select class="etd-ctl" data-etd-f="nationality"><option value="">All nationalities</option></select>
            </label>
            <label class="etd-field"><span>Gender</span>
                <select class="etd-ctl" data-etd-f="gender">
                    <option value="">All genders</option>
                    @foreach($gender as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="etd-field"><span>Status</span>
                <select class="etd-ctl" data-etd-f="status">
                    @foreach(\App\Services\HR\TelephoneDirectory::STATUSES as $v => $label)
                        <option value="{{ $v }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="etd-form__end">
                <button type="button" class="etd-btn" data-etd-reset>Reset</button>
                <button type="submit" class="etd-btn etd-btn--primary">Go</button>
            </div>
        </div>
    </details>
    <div class="etd-stale" data-etd-stale hidden>The filters have changed. Press Go to apply them.</div>
</form>
<div class="etd-rail" data-etd-rail></div>
<div class="etd-panel etd-app" data-etd-app aria-live="polite" style="display:flex;flex-direction:column;gap:18px">
    <div class="etd-empty">Loading the directory…</div>
</div>
