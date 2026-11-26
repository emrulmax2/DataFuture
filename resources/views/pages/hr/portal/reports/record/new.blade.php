{{-- Employee Record Card, new version (user's employee-record-card design, 26 Nov 2026). Go applies the
     filters (as in the design and the old form); the cards are drawn by resources/js/hr-record-card-new.js.
     The dates use the old report's rules. --}}
<form class="erc-panel erc-filters" role="search" aria-label="Record card filters" data-erc-form>
    <div class="erc-form">
        <label class="erc-field"><span>Type</span>
            <select class="erc-ctl" data-erc-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="erc-field"><span>Department</span>
            <select class="erc-ctl" data-erc-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="erc-field"><span>Start date</span>
            <input class="erc-ctl" type="text" data-hr-date data-erc-f="from" title="Staff who started on or before this date">
            <small>Started on or before</small>
        </label>
        <label class="erc-field"><span>End date</span>
            <input class="erc-ctl" type="text" data-hr-date data-erc-f="to" title="Staff whose employment ended on or after this date">
            <small>Left on or after</small>
        </label>
        <label class="erc-field"><span>Ethnicity</span>
            <select class="erc-ctl" data-erc-f="ethnicity"><option value="">All ethnicities</option></select>
        </label>
        <label class="erc-field"><span>Nationality</span>
            <select class="erc-ctl" data-erc-f="nationality"><option value="">All nationalities</option></select>
        </label>
        <label class="erc-field"><span>Gender</span>
            <select class="erc-ctl" data-erc-f="gender">
                <option value="">All genders</option>
                @foreach($gender as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="erc-field"><span>Status</span>
            <select class="erc-ctl" data-erc-f="status">
                @foreach(\App\Services\HR\RecordCardReport::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="erc-form__end">
            <label class="erc-field"><span>Find a person</span>
                <input class="erc-ctl" type="search" data-erc-f="q" placeholder="Name or works number" autocomplete="off">
            </label>
            <button type="button" class="erc-btn" data-erc-reset>Reset</button>
            <button type="submit" class="erc-btn erc-btn--primary">Go</button>
        </div>
    </div>
</form>
<div class="erc-app" data-erc-app aria-live="polite" style="display:flex;flex-direction:column;gap:12px">
    <div class="erc-empty">Loading record cards…</div>
</div>
