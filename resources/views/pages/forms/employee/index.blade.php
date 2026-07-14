@extends('../layout/noauth')

@section('body_class', 'employee-public-form-body')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('content')
    <div class="dataCollectionFormWrap employee-public-form-page">
        @if(isset($employee->id) && $employee->id > 0 && $employee->status == 2)
            <main class="employee-create-page employee-public-form-shell">
                <form method="post" action="#" id="theEmployeeDataCollectionForm">
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>

                    <div class="form-wizard employee-create-wizard employee-public-wizard">
                        <div class="form-wizard-header employee-create-rail">
                            <div class="employee-create-rail__content">
                                <div class="employee-create-rail__eyebrow">Data Collection</div>
                                <h1>Employee or Contractor Form</h1>
                                <p>Complete all four steps so HR can review your details.</p>

                                <div class="employee-create-progress">
                                    <span id="employeePublicProgressFill" style="width: 0%;"></span>
                                </div>
                                <div id="employeePublicProgressText" class="employee-create-progress__label">1 of 4 steps</div>

                                <ul class="form-wizard-steps employee-create-steps">
                                    <li data-id="step_1" class="form-wizard-step-item active">
                                        <button type="button">1</button>
                                        <div>
                                            <span>Personal Details</span>
                                            <small>Name, contact &amp; background</small>
                                        </div>
                                    </li>
                                    <li data-id="step_2" class="form-wizard-step-item">
                                        <button type="button">2</button>
                                        <div>
                                            <span>Eligibility Info</span>
                                            <small>Right to work &amp; documents</small>
                                        </div>
                                    </li>
                                    <li data-id="step_3" class="form-wizard-step-item">
                                        <button type="button">3</button>
                                        <div>
                                            <span>Employment Info</span>
                                            <small>Work type &amp; payment</small>
                                        </div>
                                    </li>
                                    <li data-id="step_4" class="form-wizard-step-item">
                                        <button type="button">4</button>
                                        <div>
                                            <span>Emergency Contact</span>
                                            <small>Next of kin details</small>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <fieldset id="step_1" class="wizard-fieldset employee-create-panel show">
                            <div class="employee-create-panel__head">
                                <span class="employee-create-panel__icon"><i data-lucide="clipboard-list"></i></span>
                                <span>
                                    <small>Step 1 of 4</small>
                                    <strong>Personal Details</strong>
                                </span>
                            </div>

                            <div class="employee-create-panel__body">
                                <div class="employee-create-section-title">Personal Details</div>

                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <label for="data-4" class="form-label">Title <span class="text-danger">*</span></label>
                                        <select id="data-4" name="title" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($titles as $title)
                                                <option value="{{ $title->id }}">{{ $title->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-title text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_first_name" class="form-label">First name(s) <span class="text-danger">*</span></label>
                                        <input id="employee_public_first_name" type="text" class="form-control inputUppercase require" name="first_name">
                                        <div class="acc__input-error error-first_name text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_last_name" class="form-label">Surname <span class="text-danger">*</span></label>
                                        <input id="employee_public_last_name" type="text" class="form-control inputUppercase require" name="last_name">
                                        <div class="acc__input-error error-last_name error-sur_name text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control datepicker require" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_sex" class="form-label">Sex <span class="text-danger">*</span></label>
                                        <select id="employee_public_sex" name="sex" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($sexIdentifier as $sex)
                                                <option value="{{ $sex->id }}">{{ $sex->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-sex text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="nationality_id" class="form-label">Nationality <span class="text-danger">*</span></label>
                                        <select id="nationality_id" name="nationality_id" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($country as $ctry)
                                                <option value="{{ $ctry->id }}">{{ $ctry->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-nationality_id text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_ethnicity" class="form-label">Ethnic Origin <span class="text-danger">*</span></label>
                                        <select id="employee_public_ethnicity" name="ethnicity" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($ethnicity as $ethnicities)
                                                @if($ethnicities->active == 1)
                                                    <option value="{{ $ethnicities->id }}">{{ $ethnicities->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-ethnicity text-danger mt-2"></div>
                                    </div>
                                </div>

                                <div class="employee-create-switch-card">
                                    <span>
                                        <strong>Do you have any disabilities?</strong>
                                        <small>Select yes to provide details.</small>
                                    </span>
                                    <label class="employee-create-switch" for="disability_status">
                                        <input id="disability_status" name="disability_status" value="1" type="checkbox">
                                        <span></span>
                                    </label>
                                </div>

                                <div id="disabilityItems" class="employee-create-check-list disabilityItems" style="display: none;">
                                    <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                                    @if(!empty($disability))
                                        @foreach($disability as $d)
                                            <div class="form-check {{ !$loop->first ? 'mt-2' : '' }} items-start">
                                                <input id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="form-check-input disability_ids" type="checkbox" value="{{ $d->id }}">
                                                <label class="form-check-label" for="disabilty_id_{{ $d->id }}">{{ $d->name }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                    <div class="acc__input-error error-disability_id text-danger mt-2"></div>
                                </div>

                                <div class="employee-create-section-title employee-create-section-title--spaced">Contact Details</div>
                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <label for="employee_public_telephone" class="form-label">Home Phone</label>
                                        <input id="employee_public_telephone" type="text" class="form-control" name="telephone">
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                        <input id="employee_public_mobile" type="text" class="form-control require" name="mobile">
                                        <div class="acc__input-error error-mobile text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="employee_public_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input id="employee_public_email" readonly value="{{ $employee->email }}" type="text" name="email" class="form-control">
                                        <div class="acc__input-error error-email text-danger mt-2"></div>
                                    </div>
                                </div>

                                <div class="employee-create-subtitle">
                                    <span>Address</span>
                                    <i data-lucide="help-circle" class="tooltip"></i>
                                </div>
                                <div class="employee-create-address-row addressWrap" id="empAddressWrap">
                                    <div class="addresses" style="display: none;"></div>
                                    <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                        <i data-lucide="plus-circle"></i>
                                        <span>Add Address</span>
                                    </button>
                                    <input type="hidden" name="address_prfix" class="address_prfix_field" value="emp_"/>
                                    <div class="acc__input-error error-emp_address_line_1 text-danger mt-2"></div>
                                </div>

                                <div class="employee-create-section-title employee-create-section-title--spaced">Educational Qualification</div>
                                <div class="employee-create-grid employee-create-grid--4">
                                    <div class="employee-create-field">
                                        <label for="highest_qualification_on_entry_id" class="form-label">Highest Educational Qualification <span class="text-danger">*</span></label>
                                        <select id="highest_qualification_on_entry_id" name="highest_qualification_on_entry_id" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($qualEntries as $entry)
                                                <option value="{{ $entry->id }}">{{ $entry->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field eduQuals">
                                        <label for="qualification_name" class="form-label">Qualification Name <span class="text-danger">*</span></label>
                                        <input id="qualification_name" type="text" class="form-control require" name="qualification_name">
                                        <div class="acc__input-error error-qualification_name text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field eduQuals">
                                        <label for="award_body" class="form-label">Award Body <span class="text-danger">*</span></label>
                                        <input id="award_body" type="text" class="form-control require" name="award_body">
                                        <div class="acc__input-error error-award_body text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field eduQuals">
                                        <label for="award_date" class="form-label">Award Date <span class="text-danger">*</span></label>
                                        <input id="award_date" type="text" placeholder="MM-YYYY" autocomplete="off" class="form-control datepicker monthYearMask require" name="award_date" data-format="MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-award_date text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="employee-create-panel__footer">
                                <span>All fields marked <strong>*</strong> are required</span>
                                <div class="employee-create-actions">
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">Next</button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="step_2" class="wizard-fieldset employee-create-panel">
                            <div class="employee-create-panel__head">
                                <span class="employee-create-panel__icon"><i data-lucide="shield-check"></i></span>
                                <span>
                                    <small>Step 2 of 4</small>
                                    <strong>Eligibility Info</strong>
                                </span>
                            </div>

                            <div class="employee-create-panel__body">
                                <div class="employee-create-section-title">Right To Work</div>

                                <div class="employee-create-switch-card">
                                    <span>
                                        <strong>Are you eligible to work in the UK?</strong>
                                        <small>Select yes if you can provide right-to-work details.</small>
                                    </span>
                                    <label class="employee-create-switch" for="eligible_to_work_status">
                                        <input id="eligible_to_work_status" name="eligible_to_work_status" value="Yes" type="checkbox">
                                        <span></span>
                                    </label>
                                </div>

                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <div class="workPermitTypeFields" style="display: none;">
                                            <label for="workpermit_type" class="form-label">Your Status In UK <span class="text-danger">*</span></label>
                                            <select id="workpermit_type" name="workpermit_type" class="w-full tom-selects">
                                                <option value="">Please Select</option>
                                                @foreach($workPermitTypes as $workPermitType)
                                                    <option value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                                        </div>
                                    </div>

                                    <div class="employee-create-field">
                                        <div class="workPermitFields" style="display: none;">
                                            <label for="workpermit_number" class="form-label">Work Permit Number <span class="text-danger">*</span></label>
                                            <input id="workpermit_number" type="text" class="form-control w-full" name="workpermit_number">
                                            <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                                        </div>
                                    </div>

                                    <div class="employee-create-field">
                                        <div class="workPermitFields" style="display: none;">
                                            <label for="workpermit_expire" class="form-label">Work Permit Expiry Date <span class="text-danger">*</span></label>
                                            <input id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="form-control w-full datepicker" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="employee-create-section-title employee-create-section-title--spaced">Identity Document</div>
                                <div class="employee-create-grid employee-create-grid--4">
                                    <div class="employee-create-field">
                                        <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                        <select id="document_type" name="document_type" class="w-full lccToms tom-selects tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($documentTypes as $documentType)
                                                <option value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-document_type text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="doc_number" class="form-label">Document Number <span class="text-danger">*</span></label>
                                        <input id="doc_number" type="text" name="doc_number" value="" class="w-full form-control require">
                                        <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="doc_expire" class="form-label">Document Expiry Date <span class="text-danger">*</span></label>
                                        <input id="doc_expire" type="text" placeholder="DD-MM-YYYY" class="form-control w-full datepicker require" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="doc_issue_country" class="form-label">Document Issue Country <span class="text-danger">*</span></label>
                                        <select id="doc_issue_country" name="doc_issue_country" class="tom-selects w-full lccToms tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($country as $countries)
                                                <option value="{{ $countries->id }}">{{ $countries->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-doc_issue_country text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="employee-create-panel__footer">
                                <span>All fields marked <strong>*</strong> are required</span>
                                <div class="employee-create-actions">
                                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Previous</button>
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">Next</button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="step_3" class="wizard-fieldset employee-create-panel">
                            <div class="employee-create-panel__head">
                                <span class="employee-create-panel__icon"><i data-lucide="briefcase"></i></span>
                                <span>
                                    <small>Step 3 of 4</small>
                                    <strong>Employment Info</strong>
                                </span>
                            </div>

                            <div class="employee-create-panel__body">
                                <div class="employee-create-section-title">Employment Info</div>
                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <label for="employee_work_type" class="form-label">Are you a...? <span class="text-danger">*</span></label>
                                        <select id="employee_work_type" name="employee_work_type" class="lcc-tom-select w-full tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($workTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="national_insurance_num" class="form-label">National Insurance number <span class="text-danger">*</span></label>
                                        <input id="national_insurance_num" type="text" name="national_insurance_num" value="" class="w-full form-control ni-number">
                                        <div class="acc__input-error error-national_insurance_num text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <div class="taxRefNo" style="display: none;">
                                            <label for="utr_number" class="form-label">Unique Tax Ref No <span class="text-danger">*</span></label>
                                            <input id="utr_number" type="text" name="utr_number" value="" class="w-full form-control">
                                            <div class="acc__input-error error-utr_number text-danger mt-2"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="employee-create-section-title employee-create-section-title--spaced">Payment Details</div>
                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <label for="beneficiary_name" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                                        <input id="beneficiary_name" type="text" name="beneficiary_name" value="" class="w-full form-control require">
                                        <div class="acc__input-error error-beneficiary_name text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                                        <input id="sort_code" type="text" name="sort_code" value="" class="w-full form-control require sortCode">
                                        <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                                        <input id="account_number" maxlength="8" minlength="8" type="text" name="account_number" value="" class="w-full form-control account_number require">
                                        <div class="acc__input-error error-account_number text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="employee-create-panel__footer">
                                <span>All fields marked <strong>*</strong> are required</span>
                                <div class="employee-create-actions">
                                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Previous</button>
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">Next</button>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="step_4" class="wizard-fieldset employee-create-panel">
                            <div class="employee-create-panel__head">
                                <span class="employee-create-panel__icon"><i data-lucide="clock-3"></i></span>
                                <span>
                                    <small>Step 4 of 4</small>
                                    <strong>Emergency Contact</strong>
                                </span>
                            </div>

                            <div class="employee-create-panel__body">
                                <div class="employee-create-section-title">Next of Kin Details</div>
                                <p class="employee-create-note">In the event of an emergency, we require your next of kin's contact information. Please provide these details.</p>

                                <div class="employee-create-grid employee-create-grid--3">
                                    <div class="employee-create-field">
                                        <label for="emergency_contact_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input id="emergency_contact_name" type="text" class="form-control inputUppercase require" name="emergency_contact_name">
                                        <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                                        <select id="relationship" name="relationship" class="form-control lccToms lcc-tom-select tomRequire">
                                            <option value="">Please Select</option>
                                            @foreach($relation as $kins)
                                                <option value="{{ $kins->id }}">{{ $kins->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-relationship text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="emergency_contact_telephone" class="form-label">Home Phone</label>
                                        <input id="emergency_contact_telephone" type="text" class="form-control" name="emergency_contact_telephone">
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="emergency_contact_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                        <input id="emergency_contact_mobile" type="text" class="form-control require" name="emergency_contact_mobile">
                                        <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                                    </div>

                                    <div class="employee-create-field">
                                        <label for="emergency_contact_email" class="form-label">Email</label>
                                        <input id="emergency_contact_email" type="text" name="emergency_contact_email" class="form-control">
                                    </div>
                                </div>

                                <div class="employee-create-subtitle">
                                    <span>Address</span>
                                    <i data-lucide="help-circle" class="tooltip"></i>
                                </div>
                                <div class="employee-create-address-row addressWrap" id="emcAddressWrap">
                                    <div class="addresses" style="display: none;"></div>
                                    <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                        <i data-lucide="plus-circle"></i>
                                        <span>Add Address</span>
                                    </button>
                                    <input type="hidden" name="address_prfix" class="address_prfix_field" value="emc_"/>
                                    <div class="acc__input-error error-emc_address_line_1 text-danger mt-2"></div>
                                </div>
                            </div>

                            <div class="employee-create-panel__footer">
                                <span>All fields marked <strong>*</strong> are required</span>
                                <div class="employee-create-actions">
                                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Previous</button>
                                    <button type="submit" id="saveEmpData" class="btn btn-primary w-auto employee-public-submit">
                                        Submit
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                                            <g fill="none" fill-rule="evenodd">
                                                <g transform="translate(1 1)" stroke-width="4">
                                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </main>
        @elseif(isset($employee->id) && $employee->id > 0 && $employee->status == 4)
            <main class="employee-public-status-shell">
                <section class="employee-public-status-card">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto"></i>
                    <h1>Congratulation</h1>
                    <p>Your data are waiting for reviews.</p>
                </section>
            </main>
        @else
            <main class="employee-public-status-shell">
                <section class="employee-public-status-card">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto"></i>
                    <h1>Thank You!</h1>
                    <p>Your submitted data successfully reviewed and submitted.</p>
                </section>
            </main>
        @endif
    </div>

    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="octagon-alert" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="btn btn-primary warningCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addressModal" class="modal employee-create-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addressForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Address</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap">
                            <div class="col-span-12">
                                <label for="address_lookup" class="form-label">Address Lookup</label>
                                <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full theAddressLookup" name="address_lookup">
                            </div>
                            <div class="col-span-12">
                                <label for="address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Address Line 1" id="address_address_line_1" class="address_line_1 form-control w-full" name="address_line_1">
                                <div class="acc__input-error error-address_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                                <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="address_line_2 form-control w-full" name="address_line_2">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_city" class="form-label">Town/City <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_city" class="city form-control w-full" name="city">
                                <div class="acc__input-error error-city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_postal_zip_code" class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Postcode" id="student_address_postal_zip_code" class="postal_code form-control w-full" name="post_code">
                                <div class="acc__input-error error-post_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Country" id="student_address_country" class="country form-control w-full" name="country">
                                <div class="acc__input-error error-country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="insertAddress" class="btn btn-primary w-auto">
                            Add Address
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="place" value=""/>
                        <input type="hidden" name="prfix" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/employee-data-collection-form.js')
@endsection
