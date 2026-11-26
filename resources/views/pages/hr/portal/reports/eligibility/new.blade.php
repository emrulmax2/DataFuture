{{-- Eligibility Expiry, new version (user's eligibility-expiry-report design, 26 Nov 2026). Go applies the
     filters; the document switch, tiles, 12-month runway and the list are drawn by
     resources/js/hr-eligibility-expiry-new.js. The old page has no filter lists, so they are read here. --}}
@php
    $eerTypes = \App\Models\EmployeeWorkType::orderBy('name')->get();
    $eerDepartments = \App\Models\Department::orderBy('name')->get();
    $eerGenders = \App\Models\SexIdentifier::all();
@endphp
<form class="eer-panel eer-filters" role="search" aria-label="Eligibility filters" data-eer-form>
    <div class="eer-form">
        <label class="eer-field"><span>Type</span>
            <select class="eer-ctl" data-eer-f="type">
                <option value="">All types</option>
                @foreach($eerTypes as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
            </select>
        </label>
        <label class="eer-field"><span>Department</span>
            <select class="eer-ctl" data-eer-f="department">
                <option value="">All departments</option>
                @foreach($eerDepartments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
            </select>
        </label>
        <label class="eer-field"><span>Expiry from</span>
            <input class="eer-ctl" type="text" data-hr-date data-eer-f="from">
        </label>
        <label class="eer-field"><span>Expiry to</span>
            <input class="eer-ctl" type="text" data-hr-date data-eer-f="to">
        </label>
        <label class="eer-field"><span>Ethnicity</span>
            <select class="eer-ctl" data-eer-f="ethnicity"><option value="">All ethnicities</option></select>
        </label>
        <label class="eer-field"><span>Nationality</span>
            <select class="eer-ctl" data-eer-f="nationality"><option value="">All nationalities</option></select>
        </label>
        <label class="eer-field"><span>Gender</span>
            <select class="eer-ctl" data-eer-f="gender">
                <option value="">All genders</option>
                @foreach($eerGenders as $g)<option value="{{ $g->id }}">{{ $g->name }}</option>@endforeach
            </select>
        </label>
        <label class="eer-field"><span>Status</span>
            <select class="eer-ctl" data-eer-f="status">
                @foreach(\App\Services\HR\EligibilityExpiry::STATUSES as $v => $label)<option value="{{ $v }}">{{ $label }}</option>@endforeach
            </select>
        </label>
        <div class="eer-form__end">
            <label class="eer-field"><span>Find a person</span>
                <input class="eer-ctl" type="search" data-eer-f="q" placeholder="First or last name" autocomplete="off">
            </label>
            <button type="button" class="eer-btn" data-eer-reset>Reset</button>
            <button type="submit" class="eer-btn eer-btn--primary">Go</button>
        </div>
    </div>
</form>
<div class="eer-panel eer-app" data-eer-app aria-live="polite" style="display:flex;flex-direction:column;gap:28px">
    <div class="eer-empty">Loading eligibility records…</div>
</div>
