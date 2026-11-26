{{-- Birthday List, new version (user's birthday-list-report design, 26 Nov 2026). Filters apply as
     they change; the calendar, side panel and list are drawn by resources/js/hr-birthday-report-new.js. --}}
<div class="bdl-panel bdl-filters" role="search" aria-label="Birthday list filters">
    <div class="bdl-form">
        <label class="bdl-field"><span>Birthday month</span>
            <select class="bdl-ctl" data-bdl-f="month">
                <option value="0">All months</option>
                @foreach(\App\Services\HR\BirthdayList::MONTHS as $i => $m)
                    <option value="{{ $i + 1 }}">{{ $m }}</option>
                @endforeach
            </select>
        </label>
        <label class="bdl-field"><span>Type</span>
            <select class="bdl-ctl" data-bdl-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="bdl-field"><span>Department</span>
            <select class="bdl-ctl" data-bdl-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="bdl-field"><span>Status</span>
            <select class="bdl-ctl" data-bdl-f="status">
                @foreach(\App\Services\HR\BirthdayList::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="bdl-field"><span>Find a person</span>
            <input class="bdl-ctl" type="search" data-bdl-f="q" placeholder="Name or works number" autocomplete="off">
        </label>
        <button type="button" class="bdl-btn" data-bdl-reset>Reset</button>
    </div>
</div>
<div class="bdl-panel bdl-app" data-bdl-app aria-live="polite">
    <div class="bdl-empty">Loading birthdays…</div>
</div>
