{{-- Employee Contact Details, new version (user's employee-contact-details design, 26 Nov 2026).
     Filters apply as they change; the completeness ring, the staff cards and the pager are drawn by
     resources/js/hr-contact-details-new.js. --}}
<div class="ecd-panel ecd-filters" role="search" aria-label="Contact details filters">
    <div class="ecd-form">
        <label class="ecd-field"><span>Find a person</span>
            <input class="ecd-ctl" type="search" data-ecd-f="q" placeholder="Name, works number, phone or email" autocomplete="off">
        </label>
        <label class="ecd-field"><span>Type</span>
            <select class="ecd-ctl" data-ecd-f="type">
                <option value="">All types</option>
                @foreach($employeeWorkType->sortBy('name') as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="ecd-field"><span>Department</span>
            <select class="ecd-ctl" data-ecd-f="department">
                <option value="">All departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="ecd-field"><span>Status</span>
            <select class="ecd-ctl" data-ecd-f="status">
                @foreach(\App\Services\HR\ContactDetailsReport::STATUSES as $v => $label)
                    <option value="{{ $v }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="button" class="ecd-btn" data-ecd-reset>Reset</button>
    </div>
    <div class="ecd-extra" data-ecd-extra hidden></div>
</div>
<div class="ecd-panel ecd-app" data-ecd-app aria-live="polite">
    <div class="ecd-empty">Loading contact details…</div>
</div>
