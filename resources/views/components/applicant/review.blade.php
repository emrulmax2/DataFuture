{{--
    Step 4: the application read back to the applicant.

    Rendered by ApplicationController@review and injected into
    `.reviewContent`, replacing the accordion that method used to build as a
    PHP string. Sections are native <details>, so no accordion script is
    needed. Every value is escaped; anything missing reads as an em dash.

    Pass `compact => true` (the agent's read-only view) to open only Personal
    details and Course & funding; the other sections start closed.
--}}
@php
    $dash = '—';
    $val = fn($v) => (isset($v) && trim((string) $v) !== '') ? $v : $dash;
    $yn = fn($v) => (isset($v) && (int) $v === 1) ? 'Yes' : 'No';
    $addr = function ($o) use ($dash) {
        if (!$o) return $dash;
        $parts = array_filter([$o->address_line_1 ?? null, $o->address_line_2 ?? null, $o->city ?? null, $o->state ?? null, $o->post_code ?? null, $o->country ?? null], fn($p) => isset($p) && trim((string) $p) !== '');
        return $parts ? implode(', ', $parts) : $dash;
    };
    $date = function ($v) use ($dash) {
        if (!isset($v) || trim((string) $v) === '') return $dash;
        foreach (['d-m-Y', 'Y-m-d'] as $f) { try { return \Carbon\Carbon::createFromFormat($f, $v)->format('d M Y'); } catch (\Throwable $e) {} }
        return $v;
    };

    $other = $applicant->other;
    $course = $applicant->course;
    $kin = $applicant->kin;
    $contact = $applicant->contact;
    $conv = $applicant->criminalConviction;

    $disabilities = $dash;
    if (isset($other->disability_status) && (int) $other->disability_status === 1) {
        $names = collect($applicant->disability ?? [])->map(fn($d) => optional($d->disabilities)->name)->filter()->implode(', ');
        $disabilities = ($names !== '' ? $names : 'Yes') . ((isset($other->disabilty_allowance) && (int) $other->disabilty_allowance === 1) ? ' · claims Disability Allowance' : '');
    } elseif (isset($other->disability_status)) {
        $disabilities = 'None declared';
    }

    $funding = $dash;
    if (!empty($course->student_loan)) {
        $funding = $course->student_loan === 'Others' ? 'Other: ' . $val($course->other_funding) : $course->student_loan;
    }

    $referral = 'None';
    if (!empty($applicant->referral_code) && (int) ($applicant->is_referral_varified ?? 0) === 1) {
        $owner = optional(\App\Models\ReferralCode::where('code', $applicant->referral_code)->first())->owner_name;
        $referral = $applicant->referral_code . ($owner ? ' — ' . $owner : '');
    }

    $hasQuals = isset($other->is_edication_qualification) && (int) $other->is_edication_qualification === 1;
    $quals = $hasQuals ? collect($applicant->quals ?? []) : collect();
    $jobs = collect($applicant->employment ?? []);

    $convictions = $dash;
    if (isset($conv->have_you_been_convicted)) {
        $convictions = (int) $conv->have_you_been_convicted === 1 ? 'Yes — ' . $val($conv->criminal_conviction_details) : 'None';
    }
@endphp

<div class="apd-review">
    <details class="apd-accordion" open>
        <summary>Personal details<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv">
                <dt>Name</dt><dd>{{ trim(implode(' ', array_filter([optional($applicant->title)->name, $applicant->first_name, $applicant->last_name]))) ?: $dash }}</dd>
                <dt>Date of birth</dt><dd>{{ $date($applicant->date_of_birth) }}</dd>
                <dt>Gender</dt><dd>{{ $val(optional($applicant->sexid)->name) }}</dd>
                <dt>Nationality</dt><dd>{{ $val(optional($applicant->nation)->name) }}</dd>
                <dt>Country of birth</dt><dd>{{ $val(optional($applicant->country)->name) }}</dd>
                <dt>Ethnicity</dt><dd>{{ $val(optional(optional($other)->ethnicity)->name) }}</dd>
                <dt>Disabilities</dt><dd>{{ $disabilities }}</dd>
            </dl>
        </div>
    </details>

    <details class="apd-accordion" {{ empty($compact) ? 'open' : '' }}>
        <summary>Contact details<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv">
                <dt>Email</dt><dd>{{ $val(optional($applicant->users)->email) }}</dd>
                <dt>Mobile</dt><dd>{{ $val(optional($contact)->mobile) }}</dd>
                <dt>Home phone</dt><dd>{{ $val(optional($contact)->home) }}</dd>
                <dt>Address</dt><dd>{{ $addr($contact) }}</dd>
            </dl>
        </div>
    </details>

    <details class="apd-accordion" {{ empty($compact) ? 'open' : '' }}>
        <summary>Next of kin<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv">
                <dt>Name</dt><dd>{{ $val(optional($kin)->name) }}@if(optional(optional($kin)->relation)->name) ({{ $kin->relation->name }})@endif</dd>
                <dt>Mobile</dt><dd>{{ $val(optional($kin)->mobile) }}</dd>
                <dt>Email</dt><dd>{{ $val(optional($kin)->email) }}</dd>
                <dt>Address</dt><dd>{{ $addr($kin) }}</dd>
            </dl>
        </div>
    </details>

    <details class="apd-accordion" open>
        <summary>Course &amp; funding<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv">
                <dt>Course</dt><dd>{{ optional(optional(optional($course)->creation)->course)->name ? $course->creation->course->name . ' — ' . $val(optional($course->semester)->name) : $dash }}</dd>
                <dt>Venue</dt><dd>{{ $val(optional(optional($course)->venue)->name) }}</dd>
                <dt>Mode</dt><dd>{{ isset($course->full_time) && (int) $course->full_time === 1 ? 'Evening & weekend (Full Time)' : 'Weekday timetable' }}</dd>
                <dt>Funding</dt>
                <dd>
                    {{ $funding }}
                    @if(($course->student_loan ?? '') === 'Student Loan')
                        <span class="apd-kv__sub">Applied to SFE: {{ $yn($course->student_finance_england) }}@if((int) ($course->student_finance_england ?? 0) === 1) · Funds received: {{ $yn($course->fund_receipt) }}@endif · Previous SLC/government loan: {{ $yn($course->applied_received_fund) }}</span>
                    @endif
                </dd>
                <dt>Referral</dt><dd>{{ $referral }}</dd>
            </dl>
        </div>
    </details>

    <details class="apd-accordion" {{ empty($compact) ? 'open' : '' }}>
        <summary>Qualifications<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            @if($hasQuals && $quals->count())
                <div class="apd-tablewrap">
                    <table class="apd-table">
                        <thead><tr><th>Qualification</th><th>Awarding body</th><th>Subjects</th><th>Result</th><th>Awarded</th></tr></thead>
                        <tbody>
                            @foreach($quals as $q)
                                <tr><td>{{ $val($q->highest_academic) }}</td><td>{{ $val($q->awarding_body) }}</td><td>{{ $val($q->subjects) }}</td><td>{{ $val($q->result) }}</td><td>{{ $val($q->degree_award_date) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <dl class="apd-kv"><dt>Qualifications</dt><dd>{{ isset($other->is_edication_qualification) ? ($hasQuals ? 'None listed' : 'No formal qualification (mature entry)') : $dash }}</dd></dl>
            @endif
        </div>
    </details>

    <details class="apd-accordion" {{ empty($compact) ? 'open' : '' }}>
        <summary>Employment<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv"><dt>Status</dt><dd>{{ $val(optional($other)->employment_status) }}</dd></dl>
            @if($jobs->count())
                <div class="apd-tablewrap apd-review__table">
                    <table class="apd-table">
                        <thead><tr><th>Organisation</th><th>Position</th><th>From</th><th>To</th></tr></thead>
                        <tbody>
                            @foreach($jobs as $j)
                                <tr><td>{{ $val($j->company_name) }}</td><td>{{ $val($j->position) }}</td><td>{{ $val($j->start_date) }}</td><td>{{ (int) ($j->continuing ?? 0) === 1 ? 'Continuing' : $val($j->end_date) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </details>

    <details class="apd-accordion" {{ empty($compact) ? 'open' : '' }}>
        <summary>Residency &amp; convictions<span class="apd-accordion__caret">&#9662;</span></summary>
        <div class="body">
            <dl class="apd-kv">
                <dt>Residency</dt><dd>{{ $val(optional(optional($applicant->residency)->residencyStatus)->name) }}</dd>
                <dt>Convictions</dt><dd>{{ $convictions }}</dd>
            </dl>
        </div>
    </details>
</div>
