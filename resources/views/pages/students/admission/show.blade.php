@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Applicant details on the admission portal shell.

        The data behaviour is unchanged and still lives in admission.js /
        admission-global.js / admission-vue.js, so every id, name and hook
        class those bundles bind is kept: the edit modals and their forms
        (partials/portal-modals), #educationQualTable / #employmentHistoryTable
        and their filter ids, #is_edication_qualification, #employment_status,
        .changeApplicantStatus / .rejectApplicationBtn (partials/portal-record),
        #addApplicantPhotoModal, #sendOfferAcceptanceModal, #progressBarModal.
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        // Shared by the record header (hidden status anchors) and the modals
        // (the Change status dialog): which statuses this one can move to.
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
    @endphp

    @include('pages.students.admission.partials.portal-record')

    @php
        $adpAddr = function ($o) {
            if (!$o) return '';
            return implode(', ', array_filter([$o->address_line_1 ?? null, $o->address_line_2 ?? null, $o->city ?? null, $o->state ?? null, $o->post_code ?? null, $o->country ?? null], fn ($v) => !empty($v)));
        };
        $adpDash = fn ($v) => (isset($v) && trim((string) $v) !== '') ? $v : '—';
        $adpYn = fn ($v) => (isset($v) && (int) $v === 1) ? 'Yes' : 'No';

        $adpDisability = 'No';
        if (isset($applicant->other->disability_status) && $applicant->other->disability_status == 1) {
            $adpDisList = [];
            if (isset($applicant->disability) && !empty($applicant->disability)) {
                foreach ($applicant->disability as $adpDis) { if (isset($adpDis->disabilities->name)) $adpDisList[] = $adpDis->disabilities->name; }
            }
            $adpDisability = (count($adpDisList) ? implode(', ', $adpDisList) : 'Yes');
            if (isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1) $adpDisability .= ' · claims Disability Allowance';
        }

        $adpEmailFlag = '';
        if (isset($applicant->users)) {
            if ($applicant->users->email_verified_at == null) {
                $adpEmailFlag = '<span class="adp-flag adp-flag--no">unverified</span>';
            } elseif (isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && isset($tempEmail->status) && $tempEmail->status == 'Pending') {
                $adpEmailFlag = '<span class="adp-flag adp-flag--warn">awaiting verification</span> <span class="adp-note">('.e($tempEmail->email).')</span>';
            } else {
                $adpEmailFlag = '<span class="adp-flag adp-flag--ok">verified</span>';
            }
        }
        $adpMobileFlag = (isset($applicant->contact) && $applicant->contact->mobile_verification == 1)
            ? '<span class="adp-flag adp-flag--ok">verified</span>'
            : '<span class="adp-flag adp-flag--no">unverified</span>';

        $adpEqOn = isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1;
        $adpEmpStatus = $applicant->other->employment_status ?? '';
        $adpEmpOn = !(empty($adpEmpStatus) || in_array($adpEmpStatus, ['Unemployed', 'Contractor', 'Consultant', 'Office Holder']));
        $adpEmpOptions = ['Part Time', 'Fixed Term', 'Contractor', 'Zero Hour', 'Seasonal', 'Agency or Temp', 'Consultant', 'Office Holder', 'Volunteer', 'Unemployed', 'Full Time'];

        $adpPencil = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>';
        $adpPlus = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';
        $adpCaret = '<span class="adp-acc-caret" aria-hidden="true">▾</span>';

        // Other submitted applications at the same address / with the same email + date of birth.
        $adpMatches = \App\Support\ApplicantMatches::for($applicant);
        $adpMatchPill = [2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'];
        $adpMatchShow = 8;
    @endphp

    @foreach([
        ['key' => 'address', 'title' => fn ($n) => $n.' other '.($n === 1 ? 'application has' : 'applications have').' the same address', 'detail' => $adpMatches['address_text']],
        ['key' => 'identity', 'title' => fn ($n) => $n.' other '.($n === 1 ? 'application has' : 'applications have').' the same email and date of birth', 'detail' => trim((string) optional($applicant->users)->email.(!empty($applicant->date_of_birth) ? ' · born '.$applicant->date_of_birth : ''), ' ·')],
    ] as $adpCheck)
        @php $adpList = $adpMatches[$adpCheck['key']]; @endphp
        @if($adpList->count() > 0)
            <div class="adp-notice adp-notice--warn adp-match" data-adp-match="{{ $adpCheck['key'] }}" data-count="{{ $adpList->count() }}">
                <b>{{ ($adpCheck['title'])($adpList->count()) }}</b>
                @if($adpCheck['detail'] !== '')<div class="adp-match__detail">{{ $adpCheck['detail'] }}</div>@endif
                <ul class="adp-match__list">
                    @foreach($adpList->take($adpMatchShow) as $adpM)
                        @php $adpMStatus = $adpM->status->name ?? '—'; @endphp
                        <li>
                            <a href="{{ route('admission.show', $adpM->id) }}"><span class="adp-mono">{{ $adpM->application_no ?: '#'.$adpM->id }}</span> {{ trim($adpM->first_name.' '.$adpM->last_name) }}</a>
                            <span class="adp-pill adp-pill--tight adp-pill--{{ $adpMatchPill[(int) $adpM->status_id] ?? 'muted' }}"><i></i>{{ $adpMStatus === 'Awating Decision' ? 'Awaiting Decision' : $adpMStatus }}</span>
                            @if(!empty($adpM->submission_date))<small>submitted {{ \Carbon\Carbon::parse($adpM->submission_date)->format('d M Y') }}</small>@endif
                        </li>
                    @endforeach
                </ul>
                @if($adpList->count() > $adpMatchShow)<div class="adp-match__more">and {{ $adpList->count() - $adpMatchShow }} more</div>@endif
            </div>
        @endif
    @endforeach

    <div class="adp-acc-list">

        {{-- ============================ Personal details ============================ --}}
        <details class="adp-accordion" open>
            <summary>Personal details{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Name</dt><dd>{{ trim(($applicant->title->name ?? '').' '.$applicant->first_name.' '.$applicant->last_name) }}</dd>
                    <dt>Date of birth</dt><dd>{{ $adpDash($applicant->date_of_birth) }}</dd>
                    <dt>Gender</dt><dd>{{ $adpDash($applicant->sexid->name ?? '') }}</dd>
                    <dt>Nationality</dt><dd>{{ $adpDash($applicant->nation->name ?? '') }}</dd>
                    <dt>Country of birth</dt><dd>{{ $adpDash($applicant->country->name ?? '') }}</dd>
                    <dt>Ethnicity</dt><dd>{{ $adpDash($applicant->other->ethnicity->name ?? '') }}</dd>
                    <dt>Disability</dt><dd>{{ $adpDisability }}</dd>
                    <dt>Care leaver</dt><dd>{{ $adpDash(optional(optional($applicant->other)->leaver)->name) }}</dd>
                </dl>
            </div>
        </details>

        {{-- ============================ Proof of ID (admissions) ============================ --}}
        @php
            $adpProofTypes = ['passport' => 'Passport', 'birth' => 'Birth Certificate', 'driving' => 'Driving Licence', 'nid' => 'National ID Card', 'respermit' => 'Residence Permit No'];
            $adpProofType = $applicant->proof->proof_type ?? '';
            $adpMiss = '—';
        @endphp
        {{-- open from Awaiting Decision: it is needed to accept (id = the dialog's "Add" link target) --}}
        <details class="adp-accordion" id="adp-proof" {{ $adpStatusId >= 4 ? 'open' : '' }}>
            <summary>Proof of ID &amp; fee eligibility (admissions){!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionProofModal" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Proof type</dt><dd>{!! $adpProofType !== '' ? e($adpProofTypes[$adpProofType] ?? ucfirst($adpProofType)) : $adpMiss !!}</dd>
                    <dt>Document no.</dt><dd>{!! !empty($applicant->proof->proof_id) ? e($applicant->proof->proof_id) : $adpMiss !!}</dd>
                    <dt>Expiry</dt><dd>{!! !empty($applicant->proof->proof_expiredate) ? e($applicant->proof->proof_expiredate) : $adpMiss !!}</dd>
                    <dt>Fee eligibility</dt><dd>{!! !empty($applicant->feeeligibility->fee_eligibility_id) ? e(optional(\App\Models\FeeEligibility::find($applicant->feeeligibility->fee_eligibility_id))->name ?? '#'.$applicant->feeeligibility->fee_eligibility_id) : $adpMiss !!}</dd>
                </dl>
                <p class="adp-acc-hint">Required before the application can be accepted: accepting places the offer and sends the offer acceptance form.</p>
            </div>
        </details>

        {{-- ============================ Contact details ============================ --}}
        <details class="adp-accordion" open>
            <summary>Contact details{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Email</dt><dd>{{ $adpDash($applicant->users->email ?? '') }} {!! $adpEmailFlag !!}</dd>
                    <dt>Mobile</dt><dd>{{ $adpDash($applicant->contact->mobile ?? '') }} {!! $adpMobileFlag !!}</dd>
                    <dt>Home phone</dt><dd>{{ $adpDash($applicant->contact->home ?? '') }}</dd>
                    <dt>Address</dt><dd>{{ $adpDash($adpAddr($applicant->contact ?? null)) }}</dd>
                </dl>
            </div>
        </details>

        {{-- ============================ Next of kin ============================ --}}
        <details class="adp-accordion">
            <summary>Next of kin{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Name</dt><dd>{{ $adpDash($applicant->kin->name ?? '') }}{{ isset($applicant->kin->relation->name) ? ' ('.$applicant->kin->relation->name.')' : '' }}</dd>
                    <dt>Mobile</dt><dd>{{ $adpDash($applicant->kin->mobile ?? '') }}</dd>
                    <dt>Email</dt><dd>{{ $adpDash($applicant->kin->email ?? '') }}</dd>
                    <dt>Address</dt><dd>{{ $adpDash($adpAddr($applicant->kin ?? null)) }}</dd>
                </dl>
            </div>
        </details>

        {{-- ============================ Proposed course & funding ============================ --}}
        <details class="adp-accordion" open>
            <summary>Proposed course &amp; funding{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionCourseDetailsModal" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Course</dt><dd>{{ $adpDash(trim(($applicant->course->creation->course->name ?? '').(isset($applicant->course->semester->name) ? ' — '.$applicant->course->semester->name : ''))) }}</dd>
                    <dt>Venue</dt><dd>{{ $adpDash($applicant->course->venue->name ?? '') }}</dd>
                    @if($applicant->creation_venue_status)
                        <dt>Mode</dt><dd>{{ (isset($applicant->course->full_time) && $applicant->course->full_time == 1) ? 'Evening & weekend (Full time)' : 'Weekday timetable' }}</dd>
                    @endif
                    <dt>Funding</dt>
                    <dd>
                        {{ $adpDash($applicant->course->student_loan ?? '') }}
                        @if(($applicant->course->student_loan ?? '') == 'Others' && !empty($applicant->course->other_funding))
                            — {{ $applicant->course->other_funding }}
                        @endif
                    </dd>
                    @if(($applicant->course->student_loan ?? '') == 'Student Loan')
                        <dt>Applied to SFE</dt><dd>{{ $adpYn($applicant->course->student_finance_england ?? 0) }}</dd>
                        @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                            <dt>Funds received</dt><dd>{{ $adpYn($applicant->course->fund_receipt ?? 0) }}</dd>
                        @endif
                        <dt>Previous SLC / government loan</dt><dd>{{ $adpYn($applicant->course->applied_received_fund ?? 0) }}</dd>
                    @endif
                    <dt>Fee eligibility</dt><dd>{{ (isset($applicant->feeeligibility->elegibility->name) && isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id > 0) ? $applicant->feeeligibility->elegibility->name : '—' }}</dd>
                </dl>
            </div>
        </details>

        {{-- ============================ Educational qualifications ============================ --}}
        <details class="adp-accordion" id="applicantQualification">
            <summary>Educational qualifications{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <span class="adp-note">Formal qualification?</span>
                <label class="adp-sw" for="is_edication_qualification">
                    <input data-applicant="{{ $applicant->id }}" {{ $adpEqOn ? 'checked' : '' }} id="is_edication_qualification" value="1" name="is_edication_qualification" class="adp-sw__input" type="checkbox">
                    <span class="adp-sw__dot">
                        <svg class="adp-sw__on" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                        <svg class="adp-sw__off" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </span>
                    <span class="adp-sw__yes">Yes</span><span class="adp-sw__no">No</span>
                </label>
            </div>
            <div class="adp-acc-body">
                <div class="educationQualificationTableWrap" style="display: {{ $adpEqOn ? 'block' : 'none' }};">
                    <div class="adp-tabletools">
                        <div id="tabulatorFilterForm-EQ" class="adp-tabletools__filters">
                            <input id="query-EQ" name="query" type="text" class="adp-ctl" placeholder="Search…" autocomplete="off">
                            <select id="status-EQ" name="status" class="adp-ctl adp-select" aria-label="Status">
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                            <button id="tabulator-html-filter-go-EQ" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Go</button>
                            <button id="tabulator-html-filter-reset-EQ" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Reset</button>
                        </div>
                        <div class="adp-tabletools__actions">
                            <button type="button" id="tabulator-print-EQ" class="adp-btn adp-btn--ghost adp-btn--sm">Print</button>
                            <button type="button" id="tabulator-export-csv-EQ" class="adp-btn adp-btn--ghost adp-btn--sm">Export CSV</button>
                            <button type="button" id="tabulator-export-xlsx-EQ" class="adp-btn adp-btn--ghost adp-btn--sm">Export XLSX</button>
                            <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpPlus !!}Add qualification</button>
                        </div>
                    </div>
                    <div class="adp-dtable">
                        <div id="educationQualTable" data-applicant="{{ $applicant->id }}" class="adp-table {{ $adpEqOn ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
                <div class="educationQualificationTableNoWrap" style="display: {{ !$adpEqOn ? 'block' : 'none' }};">
                    <div class="adp-empty adp-empty--small"><span>No formal qualification declared (mature entry route). Turn the switch on to record qualifications.</span></div>
                </div>
            </div>
        </details>

        {{-- ============================ Employment history ============================ --}}
        <details class="adp-accordion">
            <summary>Employment history{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <label class="adp-note" for="employment_status">Employment status</label>
                <div class="adp-acc-select">
                    <select id="employment_status" data-applicant="{{ $applicant->id }}" class="lcc-tom-select" name="employment_status">
                        <option value="">Please select</option>
                        @foreach($adpEmpOptions as $adpOpt)
                            <option {{ $adpEmpStatus == $adpOpt ? 'selected' : '' }} value="{{ $adpOpt }}">{{ $adpOpt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="adp-acc-body">
                <div class="educationEmploymentTableWrap" style="display: {{ $adpEmpOn ? 'block' : 'none' }};">
                    <div class="adp-tabletools">
                        <div id="tabulatorFilterForm-EH" class="adp-tabletools__filters">
                            <input id="query-EH" name="query" type="text" class="adp-ctl" placeholder="Search…" autocomplete="off">
                            <select id="status-EH" name="status" class="adp-ctl adp-select" aria-label="Status">
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                            <button id="tabulator-html-filter-go-EH" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Go</button>
                            <button id="tabulator-html-filter-reset-EH" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Reset</button>
                        </div>
                        <div class="adp-tabletools__actions">
                            <button type="button" id="tabulator-print-EH" class="adp-btn adp-btn--ghost adp-btn--sm">Print</button>
                            <button type="button" id="tabulator-export-csv-EH" class="adp-btn adp-btn--ghost adp-btn--sm">Export CSV</button>
                            <button type="button" id="tabulator-export-xlsx-EH" class="adp-btn adp-btn--ghost adp-btn--sm">Export XLSX</button>
                            <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpPlus !!}Add employment</button>
                        </div>
                    </div>
                    <div class="adp-dtable">
                        <div id="employmentHistoryTable" data-applicant="{{ $applicant->id }}" class="adp-table {{ $adpEmpOn ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
                <div class="educationEmploymentTableWrap" style="display: {{ !$adpEmpOn ? 'block' : 'none' }};">
                    <div class="adp-empty adp-empty--small"><span>{{ empty($adpEmpStatus) ? 'No employment status recorded.' : 'No employment history applies to the "'.$adpEmpStatus.'" status.' }}</span></div>
                </div>
            </div>
        </details>

        {{-- ============================ Residency & convictions ============================ --}}
        <details class="adp-accordion" id="residency-status">
            <summary>Residency status &amp; criminal convictions{!! $adpCaret !!}</summary>
            <div class="adp-acc-tools">
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionResidencyCriminalModal" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpPencil !!}Edit</button>
            </div>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Residency</dt><dd>{{ $adpDash(optional(optional($applicant->residency)->residencyStatus)->name) }}</dd>
                    <dt>Convictions</dt>
                    <dd>
                        @if(isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1)
                            Yes — {{ $adpDash($applicant->criminalConviction->criminal_conviction_details ?? '') }}
                        @elseif(isset($applicant->criminalConviction->have_you_been_convicted))
                            None declared
                        @else
                            —
                        @endif
                    </dd>
                    <dt>Declaration</dt><dd>{{ (isset($applicant->criminalConviction->criminal_declaration) && (int) $applicant->criminalConviction->criminal_declaration === 1) ? 'Signed' : '—' }}</dd>
                </dl>
            </div>
        </details>

        {{-- ============================ Others ============================ --}}
        <details class="adp-accordion">
            <summary>Others{!! $adpCaret !!}</summary>
            <div class="adp-acc-body">
                <dl class="adp-kv">
                    <dt>Referral code</dt><dd>{{ $applicant->referral_code != '' ? $applicant->referral_code : 'None' }}</dd>
                </dl>
            </div>
        </details>
    </div>

    @include('pages.students.admission.partials.portal-modals')
@endsection

@section('script')
    @vite('resources/js/admission.js')
    @vite('resources/js/admission-vue.js')
    @vite('resources/js/admission-show.js')
@endsection
