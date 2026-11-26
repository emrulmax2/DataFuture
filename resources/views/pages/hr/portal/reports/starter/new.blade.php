{{-- Employee Starter Information, new version (user's employee-starter-information design, 26 Nov 2026). Go applies the
     filters (as in the design and the old form); figures, the year × month grid and the list are drawn by resources/js/hr-starter-report-new.js.
     The dates use the old report's rules. --}}
<form class="est-panel est-filters" role="search" aria-label="Starter filters" data-est-form>
    <div class="est-form">
        <label class="est-field"><span>Type</span>
            <select class="est-ctl" data-est-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="est-field"><span>Department</span>
            <select class="est-ctl" data-est-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="est-field"><span>Start date</span>
            <input class="est-ctl" type="text" data-hr-date data-est-f="from" title="Staff who started on or before this date">
            <small>Started on or before</small>
        </label>
        <label class="est-field"><span>End date</span>
            <input class="est-ctl" type="text" data-hr-date data-est-f="to" title="Staff whose employment ended on or after this date">
            <small>Left on or after</small>
        </label>
        <label class="est-field"><span>Ethnicity</span>
            <select class="est-ctl" data-est-f="ethnicity"><option value="">All ethnicities</option></select>
        </label>
        <label class="est-field"><span>Nationality</span>
            <select class="est-ctl" data-est-f="nationality"><option value="">All nationalities</option></select>
        </label>
        <label class="est-field"><span>Gender</span>
            <select class="est-ctl" data-est-f="gender">
                <option value="">All genders</option>
                @foreach($gender as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="est-field"><span>Status</span>
            <select class="est-ctl" data-est-f="status">
                @foreach(\App\Services\HR\StarterReport::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="est-form__end">
            <button type="button" class="est-btn" data-est-reset>Reset</button>
            <button type="submit" class="est-btn est-btn--primary">Go</button>
        </div>
    </div>
</form>
<div class="est-panel est-app" data-est-app aria-live="polite" style="display:flex;flex-direction:column;gap:28px">
    <div class="est-empty">Loading starters…</div>
</div>
