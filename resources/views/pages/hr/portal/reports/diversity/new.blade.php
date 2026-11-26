{{-- Diversity Information, new version (user's diversity-information-report design, 26 Nov 2026).
     Filters apply as they change; ethnicity and nationality lists (with counts), the charts and the staff
     list are drawn by resources/js/hr-diversity-report-new.js. Dates use the old report's rules. --}}
<div class="dvr-panel dvr-filters" role="search" aria-label="Diversity filters">
    <div class="dvr-form">
        <label class="dvr-field"><span>Type</span>
            <select class="dvr-ctl" data-dvr-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="dvr-field"><span>Department</span>
            <select class="dvr-ctl" data-dvr-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="dvr-field"><span>Start date</span>
            <input class="dvr-ctl" type="text" data-hr-date data-dvr-f="from" title="Staff who started on or before this date">
            <small>Started on or before</small>
        </label>
        <label class="dvr-field"><span>End date</span>
            <input class="dvr-ctl" type="text" data-hr-date data-dvr-f="to" title="Staff whose employment ended on or after this date">
            <small>Left on or after</small>
        </label>
        <label class="dvr-field"><span>Ethnicity</span>
            <select class="dvr-ctl" data-dvr-f="ethnicity"><option value="">All ethnicities</option></select>
        </label>
        <label class="dvr-field"><span>Nationality</span>
            <select class="dvr-ctl" data-dvr-f="nationality"><option value="">All nationalities</option></select>
        </label>
        <label class="dvr-field"><span>Gender</span>
            <select class="dvr-ctl" data-dvr-f="gender">
                <option value="">All genders</option>
                @foreach($gender as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="dvr-field"><span>Status</span>
            <select class="dvr-ctl" data-dvr-f="status">
                @foreach(\App\Services\HR\DiversityReport::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="dvr-form__end"><button type="button" class="dvr-btn" data-dvr-reset>Reset</button></div>
    </div>
</div>
<div class="dvr-panel dvr-app" data-dvr-app aria-live="polite">
    <div class="dvr-empty">Loading staff…</div>
</div>
