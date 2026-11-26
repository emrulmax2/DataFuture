{{-- Employee Service Lengths, new version (user's employee-service-lengths design, 26 Nov 2026). Filters
     apply as they change; figures, charts and the service bars are drawn by
     resources/js/hr-service-lengths-new.js. The dates use the old report's rules. --}}
<div class="esl-panel esl-filters" role="search" aria-label="Service length filters">
    <div class="esl-form">
        <label class="esl-field"><span>Type</span>
            <select class="esl-ctl" data-esl-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="esl-field"><span>Department</span>
            <select class="esl-ctl" data-esl-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="esl-field"><span>Start date</span>
            <input class="esl-ctl" type="text" data-hr-date data-esl-f="from" title="Staff who started on or before this date">
            <small>Started on or before</small>
        </label>
        <label class="esl-field"><span>End date</span>
            <input class="esl-ctl" type="text" data-hr-date data-esl-f="to" title="Staff whose employment ended on or after this date">
            <small>Left on or after</small>
        </label>
        <label class="esl-field"><span>Ethnicity</span>
            <select class="esl-ctl" data-esl-f="ethnicity"><option value="">All ethnicities</option></select>
        </label>
        <label class="esl-field"><span>Nationality</span>
            <select class="esl-ctl" data-esl-f="nationality"><option value="">All nationalities</option></select>
        </label>
        <label class="esl-field"><span>Gender</span>
            <select class="esl-ctl" data-esl-f="gender">
                <option value="">All genders</option>
                @foreach($gender as $g)
                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="esl-field"><span>Status</span>
            <select class="esl-ctl" data-esl-f="status">
                @foreach(\App\Services\HR\ServiceLengthsReport::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="esl-form__end"><button type="button" class="esl-btn" data-esl-reset>Reset</button></div>
    </div>
</div>
<div class="esl-panel esl-app" data-esl-app aria-live="polite">
    <div class="esl-empty">Loading service lengths…</div>
</div>
