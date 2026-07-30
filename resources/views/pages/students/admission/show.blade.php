@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.admission.show-info')
    
    <!-- END: Profile Info -->
    <div class="adm-sections">

        {{-- ============================ Personal Details ============================ --}}
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Personal Details</div>
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails adm-btn adm-btn--edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"></path></svg>
                    Edit
                </button>
            </div>

            <div class="adm-grid">
                <div>
                    <div class="adm-item__label">Name</div>
                    <div class="adm-item__value">{{ trim(($applicant->title->name ?? '').' '.$applicant->first_name.' '.$applicant->last_name) }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Date of Birth</div>
                    <div class="adm-item__value">{{ $applicant->date_of_birth }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Gender</div>
                    <div class="adm-item__value {{ empty($applicant->sexid->name) ? 'adm-item__value--empty' : '' }}">{{ (isset($applicant->sexid->name) && !empty($applicant->sexid->name) ? $applicant->sexid->name : '—') }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Nationality</div>
                    <div class="adm-item__value {{ empty($applicant->nation->name) ? 'adm-item__value--empty' : '' }}">{{ $applicant->nation->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Country of Birth</div>
                    <div class="adm-item__value {{ empty($applicant->country->name) ? 'adm-item__value--empty' : '' }}">{{ $applicant->country->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Ethnicity</div>
                    <div class="adm-item__value {{ empty($applicant->other->ethnicity->name) ? 'adm-item__value--empty' : '' }}">{{ $applicant->other->ethnicity->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Care Leaver</div>
                    <div class="adm-item__value {{ empty(optional($applicant->other->leaver)->name) ? 'adm-item__value--empty' : '' }}">{{ optional($applicant->other->leaver)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Disability Status</div>
                    @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                        <span class="adm-chip adm-chip--yes">Yes</span>
                    @else
                        <span class="adm-chip adm-chip--no">No</span>
                    @endif
                </div>

                @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                    <div>
                        <div class="adm-item__label">Allowance Claimed?</div>
                        @if(isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1)
                            <span class="adm-chip adm-chip--yes">Yes</span>
                        @else
                            <span class="adm-chip adm-chip--no">No</span>
                        @endif
                    </div>
                    <div style="grid-column: span 3;">
                        <div class="adm-item__label">Disabilities</div>
                        @if(isset($applicant->disability) && !empty($applicant->disability))
                            <ul class="adm-disability-list">
                                @foreach($applicant->disability as $dis)
                                    <li>
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M9 12l2 2 4-4"></path></svg>
                                        {{ $dis->disabilities->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <div>
                    <div class="adm-item__label">Proof of Id Type</div>
                    <div class="adm-item__value {{ empty($applicant->proof->proof_type) ? 'adm-item__value--empty' : '' }}">{{ isset($applicant->proof->proof_type) && !empty($applicant->proof->proof_type) ? ucfirst($applicant->proof->proof_type) : '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">ID No</div>
                    <div class="adm-item__value {{ empty($applicant->proof->proof_id) ? 'adm-item__value--empty' : '' }}">{{ isset($applicant->proof->proof_id) && !empty($applicant->proof->proof_id) ? $applicant->proof->proof_id : '—' }}</div>
                </div>
                <div>
                    <div class="adm-item__label">Expiry date</div>
                    <div class="adm-item__value {{ empty($applicant->proof->proof_expiredate) ? 'adm-item__value--empty' : '' }}">{{ isset($applicant->proof->proof_expiredate) && !empty($applicant->proof->proof_expiredate) ? $applicant->proof->proof_expiredate : '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ============================ Contact Details ============================ --}}
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Contact Details</div>
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="adm-btn adm-btn--edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"></path></svg>
                    Edit
                </button>
            </div>

            <div class="adm-grid adm-grid--2">
                <div>
                    <div class="adm-item__label">Address</div>
                    <div class="adm-item__value adm-item__value--multiline">
                        @if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1))
                            {{ $applicant->contact->address_line_1 }}<br/>
                        @endif
                        @if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2))
                            {{ $applicant->contact->address_line_2 }}<br/>
                        @endif
                        @if(isset($applicant->contact->city) && !empty($applicant->contact->city))
                            {{ $applicant->contact->city }},
                        @endif
                        @if(isset($applicant->contact->state) && !empty($applicant->contact->state))
                            {{ $applicant->contact->state }},<br/>
                        @endif
                        @if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code))
                            {{ $applicant->contact->post_code }},
                        @endif
                        @if(isset($applicant->contact->country) && !empty($applicant->contact->country))
                            {{ $applicant->contact->country }}
                        @endif
                    </div>
                </div>

                <div class="adm-rows">
                    <div class="adm-row">
                        <span class="adm-row__label">Email</span>
                        <b class="adm-row__value">{{ $applicant->users->email }}</b>
                        @if ($applicant->users->email_verified_at == NULL)
                            <span class="adm-tag adm-tag--no">Unverified</span>
                        @else
                            @if(isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && (isset($tempEmail->status) && $tempEmail->status == 'Pending'))
                                <span class="adm-tag adm-tag--warn">Awaiting Verification</span>
                                <span class="adm-item__label" style="margin:0;">({{ $tempEmail->email }})</span>
                            @else
                                <span class="adm-tag adm-tag--ok">Verified</span>
                            @endif
                        @endif
                    </div>
                    <div class="adm-row">
                        <span class="adm-row__label">Home Phone</span>
                        @if(!empty($applicant->contact->home))
                            <b class="adm-row__value">{{ $applicant->contact->home }}</b>
                        @else
                            <span class="adm-item__value adm-item__value--empty">—</span>
                        @endif
                    </div>
                    <div class="adm-row">
                        <span class="adm-row__label">Mobile</span>
                        @if(!empty($applicant->contact->mobile))
                            <b class="adm-row__value">{{ $applicant->contact->mobile }}</b>
                        @else
                            <span class="adm-item__value adm-item__value--empty">—</span>
                        @endif
                        @if($applicant->contact->mobile_verification == 1)
                            <span class="adm-tag adm-tag--ok">Verified</span>
                        @else
                            <span class="adm-tag adm-tag--no">Unverified</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================ Next of Kin ============================ --}}
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Next of Kin</div>
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="adm-btn adm-btn--edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"></path></svg>
                    Edit
                </button>
            </div>

            <div class="adm-grid adm-grid--2">
                <div class="adm-rows adm-rows--compact">
                    <div class="adm-row">
                        <span class="adm-row__label">Name</span>
                        <b class="adm-row__value">{{ $applicant->kin->name ?? '—' }}</b>
                    </div>
                    <div class="adm-row">
                        <span class="adm-row__label">Relation</span>
                        <b class="adm-row__value">{{ (isset($applicant->kin->relation->name) ? $applicant->kin->relation->name : '—') }}</b>
                    </div>
                    <div class="adm-row">
                        <span class="adm-row__label">Mobile</span>
                        <b class="adm-row__value">{{ $applicant->kin->mobile ?? '—' }}</b>
                    </div>
                    <div class="adm-row">
                        <span class="adm-row__label">Email</span>
                        <b class="adm-row__value">{{ (isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '—') }}</b>
                    </div>
                </div>

                <div>
                    <div class="adm-item__label">Address</div>
                    <div class="adm-item__value adm-item__value--multiline">
                        @if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1))
                            {{ $applicant->kin->address_line_1 }}<br/>
                        @endif
                        @if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2))
                            {{ $applicant->kin->address_line_2 }}<br/>
                        @endif
                        @if(isset($applicant->kin->city) && !empty($applicant->kin->city))
                            {{ $applicant->kin->city }},
                        @endif
                        @if(isset($applicant->kin->state) && !empty($applicant->kin->state))
                            {{ $applicant->kin->state }},<br/>
                        @endif
                        @if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code))
                            {{ $applicant->kin->post_code }},
                        @endif
                        @if(isset($applicant->kin->country) && !empty($applicant->kin->country))
                            {{ $applicant->kin->country }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================ Proposed Course ============================ --}}
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Proposed Course</div>
                <button data-tw-toggle="modal" data-tw-target="#editAdmissionCourseDetailsModal" type="button" class="adm-btn adm-btn--edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"></path></svg>
                    Edit
                </button>
            </div>

            <div class="adm-grid adm-grid--1 adm-qa-list">
                <div class="adm-qa">
                    <div class="adm-item__label">Course &amp; Semester</div>
                    <div class="adm-item__value">{{ ($applicant->course->creation->course->name ?? '').(isset($applicant->course->semester->name) ? ' - '.$applicant->course->semester->name : '') }}</div>
                </div>

                @if(isset($applicant->course->venue) && !empty($applicant->course->venue))
                    <div class="adm-qa">
                        <div class="adm-item__label">Venue</div>
                        <div class="adm-item__value">{{ $applicant->course->venue->name }}</div>
                    </div>
                @endif

                <div class="adm-qa">
                    <div class="adm-item__label">How are you funding your education at London Churchill College?</div>
                    <div class="adm-item__value">{{ $applicant->course->student_loan }}</div>
                </div>

                @if($applicant->course->student_loan == 'Student Loan')
                    <div class="adm-qa">
                        <div class="adm-item__label">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>
                        @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                            <span class="adm-chip adm-chip--yes">Yes</span>
                        @else
                            <span class="adm-chip adm-chip--no">No</span>
                        @endif
                    </div>

                    @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                        <div class="adm-qa">
                            <div class="adm-item__label">Are you already in receipt of funds?</div>
                            @if(isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1)
                                <span class="adm-chip adm-chip--yes">Yes</span>
                            @else
                                <span class="adm-chip adm-chip--no">No</span>
                            @endif
                        </div>
                    @endif

                    <div class="adm-qa">
                        <div class="adm-item__label">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                        @if(isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1)
                            <span class="adm-chip adm-chip--yes">Yes</span>
                        @else
                            <span class="adm-chip adm-chip--no">No</span>
                        @endif
                    </div>
                @elseif($applicant->course->student_loan == 'Others')
                    <div class="adm-qa">
                        <div class="adm-item__label">Other Funding</div>
                        <div class="adm-item__value">{{ (isset($applicant->course->other_funding) && $applicant->course->other_funding != '' ? $applicant->course->other_funding : '—') }}</div>
                    </div>
                @endif

                @if($applicant->creation_venue_status)
                    <div class="adm-qa">
                        <div class="adm-item__label">Are you applying for evening and weekend classes (Full Time)</div>
                        @if(isset($applicant->course->full_time) && $applicant->course->full_time == 1)
                            <span class="adm-chip adm-chip--yes">Yes</span>
                        @else
                            <span class="adm-chip adm-chip--no">No</span>
                        @endif
                    </div>
                @endif

                <div class="adm-qa">
                    <div class="adm-item__label">Fee Eligibility</div>
                    <div class="adm-item__value {{ !isset($applicant->feeeligibility->elegibility->name) ? 'adm-item__value--empty' : '' }}">{{ (isset($applicant->feeeligibility->elegibility->name) && isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id > 0 ? $applicant->feeeligibility->elegibility->name : '—') }}</div>
                </div>
            </div>
        </div>

        {{-- ===================== Educational Qualification ===================== --}}
        <div class="adm-section" id="applicantQualification">
            <div class="adm-section__head">
                <div class="adm-section__title">Educational Qualification</div>
                <div class="adm-qual-toggle form-check">
                    <input data-applicant="{{ $applicant->id }}" {{ (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'checked' : '') }} id="is_edication_qualification" value="1" name="is_edication_qualification" class="form-check-input" type="checkbox">
                    <label class="adm-qual-toggle__label form-check-label" for="is_edication_qualification">
                        <span class="adm-qual-toggle__title">Do you have any formal academic qualification?</span>
                        <span class="adm-qual-toggle__meta">{{ (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'Yes - qualifications recorded below' : 'Not declared') }}</span>
                    </label>
                </div>
            </div>

            <div class="educationQualificationTableWrap" style="display: {{ isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'block' : 'none' }};">
                <div class="adm-tabletools">
                    <div id="tabulatorFilterForm-EQ" class="adm-tabletools__filters">
                        <div class="adm-field">
                            <label class="adm-label" for="query-EQ">Query</label>
                            <input id="query-EQ" name="query" type="text" class="adm-input" placeholder="Search...">
                        </div>
                        <div class="adm-field adm-field--narrow">
                            <label class="adm-label" for="status-EQ">Status</label>
                            <select id="status-EQ" name="status" class="adm-select">
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                            <svg class="adm-field__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                        </div>
                        <button id="tabulator-html-filter-go-EQ" type="button" class="adm-btn adm-btn--primary">Go</button>
                        <button id="tabulator-html-filter-reset-EQ" type="button" class="adm-btn adm-btn--soft">Reset</button>
                    </div>

                    <div class="adm-tabletools__actions">
                        <button type="button" id="tabulator-print-EQ" class="adm-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>
                            Print
                        </button>
                        <div class="dropdown adm-dropdown">
                            <button class="dropdown-toggle adm-btn" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>
                                Export
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="dropdown-menu">
                                <ul class="dropdown-content">
                                    <li><a id="tabulator-export-csv-EQ" href="javascript:;" class="dropdown-item">Export CSV</a></li>
                                    <li><a id="tabulator-export-xlsx-EQ" href="javascript:;" class="dropdown-item">Export XLSX</a></li>
                                </ul>
                            </div>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="adm-btn adm-btn--primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                            Add Qualification
                        </button>
                    </div>
                </div>

                <div class="adm-table-wrap">
                    <div id="educationQualTable" data-applicant="{{ $applicant->id }}" class="table-report table-report--tabulator {{ isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'activeTable' : '' }}"></div>
                </div>
            </div>

            <div class="educationQualificationTableNoWrap" style="display: {{ !isset($applicant->other->is_edication_qualification) || $applicant->other->is_edication_qualification != 1 ? 'block' : 'none' }};">
                <div class="adm-alert adm-alert--warning" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg>
                    Educational Qualification status are disabled.
                </div>
            </div>
        </div>

        {{-- ======================== Employment History ======================== --}}
        @php
            if(!isset($applicant->other->employment_status) || ($applicant->other->employment_status == 'Unemployed' || $applicant->other->employment_status == 'Contractor' || $applicant->other->employment_status == 'Consultant' || $applicant->other->employment_status == 'Office Holder')):
                $emptStatus = false;
            else:
                $emptStatus = true;
            endif;
        @endphp
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Employment History</div>
                <div class="adm-switch">
                    <label class="adm-switch__label" for="employment_status">What is your current employment status?</label>
                    <div class="adm-field adm-field--narrow" style="width:210px;">
                        <select id="employment_status" data-applicant="{{ $applicant->id }}" class="lcc-tom-select adm-select" name="employment_status">
                            <option value="">Please Select</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Part Time' ? 'Selected' : '' }} value="Part Time">Part Time</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Fixed Term' ? 'Selected' : '' }} value="Fixed Term">Fixed Term</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Contractor' ? 'Selected' : '' }} value="Contractor">Contractor</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Zero Hour' ? 'Selected' : '' }} value="Zero Hour">Zero Hour</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Seasonal' ? 'Selected' : '' }} value="Seasonal">Seasonal</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Agency or Temp' ? 'Selected' : '' }} value="Agency or Temp">Agency or Temp</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Consultant' ? 'Selected' : '' }} value="Consultant">Consultant</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Office Holder' ? 'Selected' : '' }} value="Office Holder">Office Holder</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Volunteer' ? 'Selected' : '' }} value="Volunteer">Volunteer</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Unemployed' ? 'Selected' : '' }} value="Unemployed">Unemployed</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Full Time' ? 'Selected' : '' }} value="Full Time">Full Time</option>
                        </select>
                        <svg class="adm-field__caret" style="bottom:15px;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                    </div>
                </div>
            </div>

            <div class="educationEmploymentTableWrap" style="display: {{ $emptStatus ? 'block' : 'none' }};">
                <div class="adm-tabletools">
                    <div id="tabulatorFilterForm-EH" class="adm-tabletools__filters">
                        <div class="adm-field">
                            <label class="adm-label" for="query-EH">Query</label>
                            <input id="query-EH" name="query" type="text" class="adm-input" placeholder="Search...">
                        </div>
                        <div class="adm-field adm-field--narrow">
                            <label class="adm-label" for="status-EH">Status</label>
                            <select id="status-EH" name="status" class="adm-select">
                                <option value="1">Active</option>
                                <option value="2">Archived</option>
                            </select>
                            <svg class="adm-field__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                        </div>
                        <button id="tabulator-html-filter-go-EH" type="button" class="adm-btn adm-btn--primary">Go</button>
                        <button id="tabulator-html-filter-reset-EH" type="button" class="adm-btn adm-btn--soft">Reset</button>
                    </div>

                    <div class="adm-tabletools__actions">
                        <button type="button" id="tabulator-print-EH" class="adm-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>
                            Print
                        </button>
                        <div class="dropdown adm-dropdown">
                            <button class="dropdown-toggle adm-btn" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>
                                Export
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="dropdown-menu">
                                <ul class="dropdown-content">
                                    <li><a id="tabulator-export-csv-EH" href="javascript:;" class="dropdown-item">Export CSV</a></li>
                                    <li><a id="tabulator-export-xlsx-EH" href="javascript:;" class="dropdown-item">Export XLSX</a></li>
                                </ul>
                            </div>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="adm-btn adm-btn--primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                            Add Employement History
                        </button>
                    </div>
                </div>

                <div class="adm-table-wrap">
                    <div id="employmentHistoryTable" data-applicant="{{ $applicant->id }}" class="table-report table-report--tabulator {{ $emptStatus ? 'activeTable' : '' }}"></div>
                </div>
            </div>

            <div class="educationEmploymentTableWrap" style="display: {{ !$emptStatus ? 'block' : 'none' }};">
                <div class="adm-alert adm-alert--warning" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg>
                    Based on selected employment status there are no employment history found!
                </div>
            </div>
        </div>

        {{-- ============ Residency Status and Criminal Convictions ============ --}}
        <div id="residency-status" class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Residency Status and Criminal Convictions</div>
                <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionResidencyCriminalModal" type="button" class="adm-btn adm-btn--edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"></path></svg>
                    Edit
                </button>
            </div>

            <div class="adm-grid adm-grid--2">
                <div>
                    <div class="adm-item__label">Residency Status</div>
                    <div class="adm-item__value {{ empty(optional(optional($applicant->residency)->residencyStatus)->name) ? 'adm-item__value--empty' : '' }}">{{ optional(optional($applicant->residency)->residencyStatus)->name ?? '—' }}</div>
                </div>

                <div>
                    <div class="adm-item__label">Criminal Conviction</div>
                    @if(isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1)
                        <span class="adm-chip adm-chip--yes">Yes</span>
                    @elseif(isset($applicant->criminalConviction->have_you_been_convicted))
                        <span class="adm-chip adm-chip--no">No</span>
                    @else
                        <div class="adm-item__value adm-item__value--empty">—</div>
                    @endif

                    @if(isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1)
                        <div class="adm-item__label" style="margin-top:14px;">Conviction Details</div>
                        <div class="adm-item__value adm-item__value--multiline">{{ isset($applicant->criminalConviction->criminal_conviction_details) && $applicant->criminalConviction->criminal_conviction_details != '' ? $applicant->criminalConviction->criminal_conviction_details : '—' }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================== Others ============================== --}}
        <div class="adm-section">
            <div class="adm-section__head">
                <div class="adm-section__title">Others</div>
            </div>

            <div class="adm-grid adm-grid--1 adm-qa-list">
                <div class="adm-qa">
                    <div class="adm-item__label">If you referred by Somone/ Agent, Please enter the Referral Code.</div>
                    @if($applicant->referral_code != '')
                        <div class="adm-item__value">{{ $applicant->referral_code }}</div>
                    @else
                        <span class="adm-chip adm-chip--no">No</span>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @include('pages.students.admission.show-modals')

@endsection

@section('script')
    @vite('resources/js/admission.js')
    @vite('resources/js/admission-vue.js')
@endsection
