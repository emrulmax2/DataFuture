@extends('../layout/' . $layout)

@section('body_class', 'hr-dashboard-v2-body employee-create-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="hrd-page employee-create-hrd-page">
        @include('layout.components.hr-dashboard-topbar', [
            'active' => 'dashboard',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Add Employee'],
            ],
        ])

        <main class="employee-create-page">
        <input type="hidden" id="studentId" name="student_id" value="" />

        <!-- BEGIN: Wizard Layout -->
        <div class="form-wizard employee-create-wizard">
            <div class="form-wizard-header employee-create-rail">
                <div class="employee-create-rail__content">
                    <div class="employee-create-rail__eyebrow">New Record</div>
                    <h1>Create New Employee</h1>
                    <p>Complete all four steps to add a member to the workforce.</p>

                    <div class="employee-create-progress">
                        <span id="employeeCreateProgressFill" style="width: 0%;"></span>
                    </div>
                    <div id="employeeCreateProgressText" class="employee-create-progress__label">1 of 4 steps</div>

                    <ul class="form-wizard-steps employee-create-steps">
                        <li class="form-wizard-step-item active">
                            <button type="button">1</button>
                            <div>
                                <span>Personal Details</span>
                                <small>Name, contact &amp; background</small>
                            </div>
                        </li>
                        <li class="form-wizard-step-item">
                            <button type="button">2</button>
                            <div>
                                <span>Employment</span>
                                <small>Role, department &amp; terms</small>
                            </div>
                        </li>
                        <li class="form-wizard-step-item">
                            <button type="button">3</button>
                            <div>
                                <span>Eligibility Info</span>
                                <small>Right to work &amp; documents</small>
                            </div>
                        </li>
                        <li class="form-wizard-step-item">
                            <button type="button">4</button>
                            <div>
                                <span>Emergency Contact</span>
                                <small>Next of kin details</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <fieldset class="wizard-fieldset employee-create-panel show">
                <div class="employee-create-panel__head">
                    <span class="employee-create-panel__icon"><i data-lucide="clipboard-list"></i></span>
                    <span>
                        <small>Step 1 of 4</small>
                        <strong>Personal Details</strong>
                    </span>
                </div>

                <form method="post" action="#" id="appicantFormStep_1" class="wizard-step-form">
                    <div class="employee-create-panel__body">
                        <div class="employee-create-section-title">Personal Details</div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field">
                                <label for="data-4" class="form-label">Title <span class="text-danger">*</span></label>
                                <select id="data-4" name="title" class="title lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($titles as $title)
                                        <option {{ (isset($employee->title_id) && $employee->title_id == $title->id ? 'Selected' : '') }} value="{{ $title->id }}">{{ $title->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-title text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_first_name" class="form-label">First name(s) <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->first_name) ? $employee->first_name : '') }}" id="employee_first_name" type="text" class="first_name form-control inputUppercase" name="first_name">
                                <div class="acc__input-error error-first_name text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_last_name" class="form-label">Surname <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->last_name) ? $employee->last_name : '') }}" id="employee_last_name" type="text" class="last_name form-control inputUppercase" name="last_name">
                                <div class="acc__input-error error-last_name text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_telephone" class="form-label">Telephone</label>
                                <input value="{{ (isset($employee->telephone) ? $employee->telephone : '') }}" id="employee_telephone" type="text" class="telephone form-control" name="telephone">
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->mobile) ? $employee->mobile : '') }}" id="employee_mobile" type="text" class="mobile form-control" name="mobile">
                                <div class="acc__input-error error-mobile text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->email) ? $employee->email : '') }}" id="employee_email" type="text" name="email" class="email form-control">
                                <div class="acc__input-error error-email text-danger mt-2"></div>
                            </div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Address</span>
                            <i data-theme="light" data-tooltip-content="#employee-address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="employee-address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please add the employee address.</div>
                            </div>
                        </div>

                        <div class="employee-create-address-row addressWrap" id="empAddressWrap">
                            <div class="addresses {{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'active' : '') }}" style="display: {{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'block' : 'none') }};">
                                @if(isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input))
                                    {!! $employee->address->full_address_input !!}
                                @endif
                            </div>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                <i data-lucide="plus-circle"></i>
                                <span>{{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'Update' : 'Add') }} Address</span>
                            </button>
                            <input type="hidden" name="address_prfix" class="address_prfix_field" value="emp_"/>
                            <div class="acc__input-error error-emp_address_line_1 text-danger mt-2"></div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Other Details</span>
                            <i data-theme="light" data-tooltip-content="#employee-other-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="employee-other-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please set other details.</div>
                            </div>
                        </div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field">
                                <label for="employee_sex" class="form-label">Sex <span class="text-danger">*</span></label>
                                <select id="employee_sex" name="sex" class="sex lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($sexIdentifier as $sex)
                                        <option {{ (isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $sex->id ? 'Selected' : '') }} value="{{ $sex->id }}">{{ $sex->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-sex text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->date_of_birth) ? $employee->date_of_birth : '') }}" id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="date_of_birth form-control datepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_ni_number" class="form-label">NI Number</label>
                                <input value="{{ (isset($employee->ni_number) ? $employee->ni_number : '') }}" id="employee_ni_number" type="text" name="ni_number" class="ni_number form-control ni-number inputUppercase">
                                <div class="acc__input-error error-ni_number text-danger mt-2"></div>
                            </div>
                        </div>

                        <div class="employee-create-switch-card">
                            <span>
                                <strong>Do you have any disabilities?</strong>
                                <small>Toggle on to record accessibility requirements</small>
                            </span>
                            <label class="employee-create-switch" for="disability_status">
                                <input {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? 'Checked' : '') }} id="disability_status" name="disability_status" value="1" type="checkbox">
                                <span></span>
                            </label>
                        </div>

                        <div id="disabilityItems" class="disabilityItems employee-create-check-list {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? '' : 'hidden') }}">
                            <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                            @if(!empty($disability))
                                @foreach($disability as $d)
                                    <div class="form-check {{ !$loop->first ? 'mt-2' : '' }} items-start">
                                        <input {{ (in_array($d->id, $emp_dis) ? 'checked' : '' ) }} id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="form-check-input disability_ids" type="checkbox" value="{{ $d->id }}">
                                        <label class="form-check-label" for="disabilty_id_{{ $d->id }}">{{ $d->name }}</label>
                                    </div>
                                @endforeach
                            @endif
                            <div class="acc__input-error error-disability_id text-danger mt-2"></div>
                        </div>

                        <div class="employee-create-grid employee-create-grid--2">
                            <div class="employee-create-field">
                                <label for="employee_nationality" class="form-label">Nationality <span class="text-danger">*</span></label>
                                <select id="employee_nationality" name="nationality" class="nationality lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($country as $countries)
                                        <option {{ (isset($employee->nationality_id) && $employee->nationality_id == $countries->id ? 'Selected' : '') }} value="{{ $countries->id }}">{{ $countries->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-nationality text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_ethnicity" class="form-label">Ethnic Origin <span class="text-danger">*</span></label>
                                <select id="employee_ethnicity" name="ethnicity" class="ethnicity lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($ethnicity as $ethnicities)
                                        @if($ethnicities->active == 1)
                                            <option {{ (isset($employee->ethnicity_id) && $employee->ethnicity_id == $ethnicities->id ? 'Selected' : '') }} value="{{ $ethnicities->id }}">{{ $ethnicities->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-ethnicity text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_car_reg_number" class="form-label">Car Reg. Number</label>
                                <input value="{{ (isset($employee->car_reg_number) ? $employee->car_reg_number : '') }}" id="employee_car_reg_number" type="text" name="car_reg_number" class="car_reg_number form-control">
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_drive_license_number" class="form-label">Driving Licence Number</label>
                                <input value="{{ (isset($employee->drive_license_number) ? $employee->drive_license_number : '') }}" id="employee_drive_license_number" type="text" name="drive_license_number" class="drive_license_number form-control">
                            </div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Educational Qualification</span>
                            <i data-theme="light" data-tooltip-content="#edu-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="edu-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please set educational qualification.</div>
                            </div>
                        </div>

                        <div class="employee-create-grid employee-create-grid--4">
                            <div class="employee-create-field">
                                <label for="highest_qualification_on_entry_id" class="form-label">Highest Qualification <span class="text-danger">*</span></label>
                                <select id="highest_qualification_on_entry_id" name="highest_qualification_on_entry_id" class="highest_qualification_on_entry_id tom-selects w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($qualEntries as $entry)
                                        <option {{ (isset($employee->education->highest_qualification_on_entry_id) && $employee->education->highest_qualification_on_entry_id == $entry->id ? 'Selected' : '') }} value="{{ $entry->id }}">{{ $entry->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field eduQuals">
                                <label for="qualification_name" class="form-label">Qualification Name <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->education->qualification_name) ? $employee->education->qualification_name : '') }}" id="qualification_name" type="text" class="qualification_name form-control" name="qualification_name">
                                <div class="acc__input-error error-qualification_name text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field eduQuals">
                                <label for="award_body" class="form-label">Award Body <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->education->award_body) ? $employee->education->award_body : '') }}" id="award_body" type="text" class="award_body form-control" name="award_body">
                                <div class="acc__input-error error-award_body text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field eduQuals">
                                <label for="award_date" class="form-label">Award Date <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->education->award_date) && !empty($employee->education->award_date) ? date('m-Y', strtotime($employee->education->award_date)) : '') }}" id="award_date" type="text" placeholder="MM-YYYY" autocomplete="off" class="award_date form-control datepicker monthYearMask" name="award_date" data-format="MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-award_date text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="employee-create-panel__footer">
                        <span>All fields marked <strong>*</strong> are required</span>
                        <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                            Save &amp; Continue
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                    <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
                </form>
            </fieldset>

            <fieldset class="wizard-fieldset employee-create-panel">
                <div class="employee-create-panel__head">
                    <span class="employee-create-panel__icon"><i data-lucide="briefcase"></i></span>
                    <span>
                        <small>Step 2 of 4</small>
                        <strong>Employment</strong>
                    </span>
                </div>

                <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                    <div class="employee-create-panel__body">
                        <div class="employee-create-section-title">Employment Details</div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field">
                                <label for="started_on" class="form-label">Started On <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->employment->started_on) && !empty($employee->employment->started_on) ? date('d-m-Y', strtotime($employee->employment->started_on)) : '') }}" id="started_on" type="text" placeholder="DD-MM-YYYY" class="started_on form-control datepicker" name="started_on" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-started_on text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="site_location" class="form-label">Site Location <span class="text-danger">*</span></label>
                                <select id="site_location" name="site_location[]" class="site_location w-full lccToms lcc-tom-select" multiple>
                                    <option value="">Please Select</option>
                                    @foreach($venues as $venue)
                                        <option {{ (!empty($emp_venue) && in_array($venue->id, $emp_venue) ? 'Selected' : '') }} value="{{ $venue->id }}">{{ $venue->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-site_location text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="punch_number" class="form-label">Punch Number <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->employment->punch_number) ? $employee->employment->punch_number : '') }}" id="punch_number" type="text" class="punch_number form-control" name="punch_number">
                                <div class="acc__input-error error-punch_number text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employee_work_type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select id="employee_work_type" name="employee_work_type" class="employee_work_type lcc-tom-select w-full">
                                    <option value="">Please Select</option>
                                    @foreach($workTypes as $type)
                                        <option {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == $type->id ? 'Selected' : '') }} value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field employeeWorkTypeFields" style="display: {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == 3 ? 'block' : 'none') }};">
                                <label for="works_number" class="form-label">Works Number <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->employment->works_number) ? $employee->employment->works_number : '') }}" id="works_number" type="text" class="works_number form-control" name="works_number">
                                <div class="acc__input-error error-works_number text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field taxRefNo" style="display: {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == 2 ? 'block' : 'none') }};">
                                <label for="utr_number" class="form-label">Unique Tax Ref No <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->employment->utr_number) ? $employee->employment->utr_number : '') }}" id="utr_number" type="text" name="utr_number" class="utr_number form-control" />
                                <div class="acc__input-error error-utr_number text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <select id="job_title" name="job_title" class="job_title w-full lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($jobTitles as $jobTitle)
                                        <option {{ (isset($employee->employment->employee_job_title_id) && $employee->employment->employee_job_title_id == $jobTitle->id ? 'Selected' : '') }} value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-job_title text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select id="department" name="department" class="department w-full lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($departments as $department)
                                        <option {{ (isset($employee->employment->department_id) && $employee->employment->department_id == $department->id ? 'Selected' : '') }} value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-department text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="office_telephone" class="form-label">Office Telephone / Ext. No</label>
                                <input value="{{ (isset($employee->employment->office_telephone) ? $employee->employment->office_telephone : '') }}" id="office_telephone" type="text" class="office_telephone form-control" name="office_telephone">
                            </div>

                            <div class="employee-create-field">
                                <label for="employment_mobile" class="form-label">Mobile</label>
                                <input value="{{ (isset($employee->employment->mobile) ? $employee->employment->mobile : '') }}" id="employment_mobile" type="text" class="mobile form-control" name="mobile">
                            </div>

                            <div class="employee-create-field">
                                <label for="employment_email" class="form-label">Email (username) <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->employment->email) ? $employee->employment->email : '') }}" id="employment_email" type="text" class="email form-control" name="email">
                                <div class="acc__input-error error-email text-danger mt-2"></div>
                            </div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Terms</span>
                            <i data-theme="light" data-tooltip-content="#terms-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="terms-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please check terms.</div>
                            </div>
                        </div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field">
                                <label for="notice-period" class="form-label">Notice Period <span class="text-danger">*</span></label>
                                <select id="notice-period" name="notice_period" class="notice_period form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($noticePeriods as $noticePeriod)
                                        <option {{ (isset($employee->terms->employee_notice_period_id) && $employee->terms->employee_notice_period_id == $noticePeriod->id ? 'Selected' : '') }} value="{{ $noticePeriod->id }}">{{ $noticePeriod->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-notice_period text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="employment-period" class="form-label">Period of Employment <span class="text-danger">*</span></label>
                                <select id="employment-period" name="employment_period" class="employment_period form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($employmentPeriods as $employmentPeriod)
                                        <option {{ (isset($employee->terms->employment_period_id) && $employee->terms->employment_period_id == $employmentPeriod->id ? 'Selected' : '') }} value="{{ $employmentPeriod->id }}">{{ $employmentPeriod->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-employment_period text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="ssp-term" class="form-label">SSP Terms &amp; Conditions <span class="text-danger">*</span></label>
                                <select id="ssp-term" name="ssp_term" class="ssp_term form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($sspTerms as $sspterm)
                                        <option {{ (isset($employee->terms->employment_ssp_term_id) && $employee->terms->employment_ssp_term_id == $sspterm->id ? 'Selected' : '') }} value="{{ $sspterm->id }}">{{ $sspterm->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-ssp_term text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="employee-create-panel__footer">
                        <span>All fields marked <strong>*</strong> are required</span>
                        <div class="employee-create-actions">
                            <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Back</button>
                            <button id="form2SaveButton" type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                                Save &amp; Continue
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                    <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
                </form>
            </fieldset>

            <fieldset class="wizard-fieldset employee-create-panel">
                <div class="employee-create-panel__head">
                    <span class="employee-create-panel__icon"><i data-lucide="shield-check"></i></span>
                    <span>
                        <small>Step 3 of 4</small>
                        <strong>Eligibility Info</strong>
                    </span>
                </div>

                <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                    <div class="employee-create-panel__body">
                        <div class="employee-create-section-title">Eligibility Info</div>

                        <div class="employee-create-switch-card">
                            <span>
                                <strong>Is this person eligible to work in the UK?</strong>
                                <small>Right-to-work status</small>
                            </span>
                            <label class="employee-create-switch" for="eligible_to_work_status">
                                <input {{ (isset($employee->eligibilities->eligible_to_work) && $employee->eligibilities->eligible_to_work == 'Yes' ? 'Checked' : '') }} id="eligible_to_work_status" name="eligible_to_work_status" value="Yes" type="checkbox">
                                <span></span>
                            </label>
                        </div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field workPermitTypeFields" style="display: {{ (isset($employee->eligibilities->eligible_to_work) && $employee->eligibilities->eligible_to_work == 'Yes' ? 'block' : 'none') }};">
                                <label for="workpermit_type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select id="workpermit_type" name="workpermit_type" class="workpermit_type w-full lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($workPermitTypes as $workPermitType)
                                        <option {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == $workPermitType->id ? 'Selected' : '') }} value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field workPermitFields" style="display: {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == 3 ? 'block' : 'none') }};">
                                <label for="workpermit_number" class="form-label">Work Permit Number</label>
                                <input value="{{ (isset($employee->eligibilities->workpermit_number) ? $employee->eligibilities->workpermit_number : '') }}" id="workpermit_number" type="text" class="workpermit_number form-control" name="workpermit_number">
                                <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field workPermitFields" style="display: {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == 3 ? 'block' : 'none') }};">
                                <label for="workpermit_expire" class="form-label">Work Permit Expiry Date</label>
                                <input value="{{ (isset($employee->eligibilities->workpermit_expire) && !empty($employee->eligibilities->workpermit_expire) ? date('d-m-Y', strtotime($employee->eligibilities->workpermit_expire)) : '') }}" id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="workpermit_expire form-control datepicker" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                            </div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Right-to-Work Document</span>
                            <i data-theme="light" data-tooltip-content="#document-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="document-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Identity document evidence.</div>
                            </div>
                        </div>

                        <div class="employee-create-grid employee-create-grid--2">
                            <div class="employee-create-field">
                                <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                <select id="document_type" name="document_type" class="document_type form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($documentTypes as $documentType)
                                        <option {{ (isset($employee->eligibilities->document_type) && $employee->eligibilities->document_type == $documentType->id ? 'Selected' : '') }} value="{{ $documentType->id }}">{{ $documentType->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-document_type text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="doc_number" class="form-label">Document Number <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->eligibilities->doc_number) ? $employee->eligibilities->doc_number : '') }}" id="doc_number" type="text" name="doc_number" class="doc_number form-control" />
                                <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="doc_expire" class="form-label">Document Expiry Date <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->eligibilities->doc_expire) && !empty($employee->eligibilities->doc_expire) ? date('d-m-Y', strtotime($employee->eligibilities->doc_expire)) : '') }}" id="doc_expire" type="text" placeholder="DD-MM-YYYY" class="doc_expire form-control datepicker" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="doc_issue_country" class="form-label">Document Issue Country <span class="text-danger">*</span></label>
                                <select id="doc_issue_country" name="doc_issue_country" class="doc_issue_country lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($country as $countries)
                                        <option {{ (isset($employee->eligibilities->doc_issue_country) && $employee->eligibilities->doc_issue_country == $countries->id ? 'Selected' : '') }} value="{{ $countries->id }}">{{ $countries->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-doc_issue_country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="employee-create-panel__footer">
                        <span>All fields marked <strong>*</strong> are required</span>
                        <div class="employee-create-actions">
                            <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Back</button>
                            <button id="form3SaveButton" type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                                Save &amp; Continue
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                    <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
                </form>
            </fieldset>

            <fieldset class="wizard-fieldset employee-create-panel">
                <div class="employee-create-panel__head">
                    <span class="employee-create-panel__icon"><i data-lucide="clock-3"></i></span>
                    <span>
                        <small>Step 4 of 4</small>
                        <strong>Emergency Contact</strong>
                    </span>
                </div>

                <form method="post" action="#" id="appicantFormStep_4" class="wizard-step-form">
                    <div class="employee-create-panel__body">
                        <div class="employee-create-section-title">Emergency Contact</div>

                        <div class="employee-create-grid employee-create-grid--3">
                            <div class="employee-create-field">
                                <label for="emergency_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->emergencyContact->emergency_contact_name) ? $employee->emergencyContact->emergency_contact_name : '') }}" id="emergency_contact_name" type="text" class="emergency_contact_name form-control inputUppercase" name="emergency_contact_name">
                                <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                                <select id="relationship" name="relationship" class="relationship form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($relation as $kins)
                                        <option {{ (isset($employee->emergencyContact->kins_relation_id) && $employee->emergencyContact->kins_relation_id == $kins->id ? 'Selected' : '') }} value="{{ $kins->id }}">{{ $kins->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-relationship text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="emergency_contact_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                                <input value="{{ (isset($employee->emergencyContact->emergency_contact_mobile) ? $employee->emergencyContact->emergency_contact_mobile : '') }}" id="emergency_contact_mobile" type="text" class="emergency_contact_mobile form-control" name="emergency_contact_mobile">
                                <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                            </div>

                            <div class="employee-create-field">
                                <label for="emergency_contact_telephone" class="form-label">Telephone</label>
                                <input value="{{ (isset($employee->emergencyContact->emergency_contact_telephone) ? $employee->emergencyContact->emergency_contact_telephone : '') }}" id="emergency_contact_telephone" type="text" class="emergency_contact_telephone form-control" name="emergency_contact_telephone">
                            </div>

                            <div class="employee-create-field">
                                <label for="emergency_contact_email" class="form-label">Email</label>
                                <input value="{{ (isset($employee->emergencyContact->emergency_contact_email) ? $employee->emergencyContact->emergency_contact_email : '') }}" id="emergency_contact_email" type="text" name="emergency_contact_email" class="emergency_contact_email form-control">
                            </div>
                        </div>

                        <div class="employee-create-subtitle">
                            <span>Contact Address</span>
                            <i data-theme="light" data-tooltip-content="#emergency-address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip"></i>
                        </div>
                        <div class="tooltip-content">
                            <div id="emergency-address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please add the emergency contact address.</div>
                            </div>
                        </div>

                        <div class="employee-create-address-row addressWrap" id="emcAddressWrap">
                            <div class="addresses" style="display: {{ (isset($employee->emergencyContact->address_id) && $employee->emergencyContact->address_id > 0 ? 'block' : 'none') }};">
                                {!! (isset($employee->emergencyContact->address_input) && !empty($employee->emergencyContact->address_input) ? $employee->emergencyContact->address_input : '') !!}
                            </div>
                            <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                <i data-lucide="plus-circle"></i>
                                <span>{{ (isset($employee->emergencyContact->address_id) && $employee->emergencyContact->address_id > 0 ? 'Update' : 'Add') }} Address</span>
                            </button>
                            <input type="hidden" name="address_prfix" class="address_prfix_field" value="emc_"/>
                            <div class="acc__input-error error-emc_address_line_1 text-danger mt-2"></div>
                        </div>
                    </div>

                    <input type="hidden" name="url" value=""/>
                    <div class="employee-create-panel__footer">
                        <span>All fields marked <strong>*</strong> are required</span>
                        <div class="employee-create-actions">
                            <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">Back</button>
                            <button id="form4SaveButton" type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                                Save Employee
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                    <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
                </form>
            </fieldset>
        </div>
        <!-- END: Wizard Layout -->
        </main>

    <!-- BEGIN: Address Modal -->
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
                                <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full address_line_1" name="address_line_1">
                                <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                                <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full address_line_2" name="address_line_2">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_city" class="form-label">Town/City <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full city" name="city">
                                <div class="acc__input-error error-city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_postal_zip_code" class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Postcode" id="student_address_postal_zip_code" class="form-control w-full postal_code" name="post_code">
                                <div class="acc__input-error error-post_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full country" name="country">
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
    <!-- END: Address Modal -->
    </div>
@endsection

@section('script')
    @vite('resources/js/employee-new.js')
@endsection
