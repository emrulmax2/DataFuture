@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Create New Employee</h2>
    </div>
 <input type="hidden" id="studentId" name="student_id" value="" />
    <!-- BEGIN: Wizard Layout -->
    <div class="form-wizard intro-y box py-10 sm:py-20 mt-5">
        <div class="form-wizard-header">
            <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                <li class="intro-x lg:text-center flex items-center lg:block flex-1 z-10 form-wizard-step-item active">
                    <button class="w-10 h-10 rounded-full btn btn-primary">1</button>
                    <div class="lg:w-32 font-medium text-base lg:mt-3 ml-3 lg:mx-auto">Personal Details</div>
                </li>
                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Employment</div>
                </li>
                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">3</button>
                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Eligibility Info</div>
                </li>
                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">4</button>
                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Emergency Contact</div>
                </li>

            </ul>
        </div>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400 show"> 
            <form method="post" action="#" id="appicantFormStep_1" class="wizard-step-form">
            <div class="font-medium text-base">Personal Details</div>
            <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="input-wizard-4" class="form-label inline-flex">Title <span class="text-danger"> *</span></label>
                    <select id="data-4" name="title" class=" lcc-tom-select w-full lccToms  ">
                        <option  value="">Please Select</option>   
                        @foreach($titles as $title)
                            <option {{ (isset($employee->title_id) && $employee->title_id == $title->id ? 'Selected' : '') }} value="{{ $title->id }}">{{ $title->name }}</option>              
                        @endforeach
                    </select>
                    <div class="acc__input-error error-title text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-2" class="form-label inline-flex">First name(s) <span class="text-danger">*</span></label>
                    <input value="{{ (isset($employee->first_name) ? $employee->first_name : '') }}" id="vertical-form-2" type="text" class="form-control rounded-none form-control-lg inputUppercase" name="first_name" aria-label="default input example">
                    <div class="acc__input-error error-first_name text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-1" class="form-label inline-flex">Surname <span class="text-danger">*</span></label>
                    <input value="{{ (isset($employee->last_name) ? $employee->last_name : '') }}" id="vertical-form-1" type="text" class="form-control rounded-none form-control-lg inputUppercase"  name="last_name" aria-label="default input example">
                    <div class="acc__input-error error-sur_name text-danger mt-2"></div>
                </div>
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-4" class="form-label inline-flex">Telephone</label>
                    <input value="{{ (isset($employee->telephone) ? $employee->telephone : '') }}" id="vertical-form-4" type="text" class="form-control rounded-none form-control-lg" name="telephone" aria-label="default input example">
    
                </div>
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                    <input value="{{ (isset($employee->mobile) ? $employee->mobile : '') }}" id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg" name="mobile" aria-label="default input example">
                    <div class="acc__input-error error-mobile text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-6" class="form-label inline-flex">Email <span class="text-danger">*</span></label>
                    <input value="{{ (isset($employee->email) ? $employee->email : '') }}" id="vertical-form-6" type="text" name="email" class="form-control rounded-none form-control-lg" aria-label="default input example">
                    <div class="acc__input-error error-email text-danger mt-2"></div>
                </div>
                
                <div class="font-medium text-base">
                    <label for="input-wizard-4" class="form-label inline-flex">Address <i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
    
                    <!-- BEGIN: Custom Tooltip Content -->
                    <div class="tooltip-content">
                        <div id="address-tooltip" class="relative flex items-center py-1">
                            <div class="text-slate-500 dark:text-slate-400">Please your term time address the same as your permanent address?</div>
                        </div>
                    </div>
                    <!-- END: Custom Tooltip Content -->
                </div> 
                <div class="intro-y col-span-12">
                    <div class="grid grid-cols-12 gap-x-4">
                        <div class="col-span-6 addressWrap" id="empAddressWrap">
                            <div class="addresses mb-2 {{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'active' : '') }}" style="display: {{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'block' : 'none') }};">
                                @if(isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input))
                                    {!! $employee->address->full_address_input !!}
                                @endif
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ (isset($employee->address_id) && $employee->address_id > 0 && isset($employee->address->full_address_input) && !empty($employee->address->full_address_input) ? 'Update' : 'Add') }} Address</span>
                                </button>
                                <input type="hidden" name="address_prfix" class="address_prfix_field" value="emp_"/>
                            </div>
                            <div class="acc__input-error error-emp_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">

                        </div>
                    </div>
                </div>
                <div class="intro-y col-span-12">
                    <div class="font-medium text-base">
                        <label for="input-wizard-4" class="form-label inline-flex">Other Details <i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
        
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please Set other details</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div> 
                </div>
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-7" class="form-label inline-flex">Sex <span class="text-danger"> *</span></label>
                    <select id="vertical-form-7" name="sex" class="lcc-tom-select w-full lccToms ">
                        <option  value="">Please Select</option>   
                        @foreach($sexIdentifier as $sex)
                            <option {{ (isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $sex->id ? 'Selected' : '') }} value="{{ $sex->id }}">{{ $sex->name }}</option>              
                        @endforeach
                    </select>
                    <div class="acc__input-error error-sex text-danger mt-2"></div>
                </div>
                
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="date_of_birth" class="form-label inline-flex">Date of Birth <span class="text-danger"> *</span></label>
                    <input value="{{ (isset($employee->date_of_birth) ? $employee->date_of_birth : '') }}" id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control form-control-lg datepicker rounded-none" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                    <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-9" class="form-label inline-flex">NI Number</label>
                    <input value="{{ (isset($employee->ni_number) ? $employee->ni_number : '') }}" id="vertical-form-9" type="text" name="ni_number" class="form-control rounded-none form-control-lg ni-number inputUppercase"  aria-label="default input example">
                    <div class="acc__input-error error-ni_number text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12">
                    <div class="grid grid-cols-12 gap-x-4">
                        <div class="col-span-12 sm:col-span-12">
                            <label for="disability_status" class="form-label">Do you have any disabilities?</label>
                            <div class="form-check form-switch">
                                <input {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? 'Checked' : '') }} id="disability_status" class="form-check-input" name="disability_status" value="1" type="checkbox">
                                <label class="form-check-label" for="disability_status">&nbsp;</label>
                            </div>
                        </div>
                        <div id="disabilityItems" class="col-span-12 sm:col-span-12 pt-4 disabilityItems {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? '' : 'hidden') }}">
                            <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                            @php 
                                $ids = [];
                            @endphp
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

                        <div class="intro-y col-span-12 sm:col-span-6 pt-4">
                            <label for="vertical-form-11" class="form-label inline-flex">Nationality <span class="text-danger"> *</span></label>
                            <select id="vertical-form-11" name="nationality" class="lcc-tom-select w-full lccToms">
                                <option value="">Please Select</option>
                                @foreach($country as $countries)
                                    <option {{ (isset($employee->nationality_id) && $employee->nationality_id == $countries->id ? 'Selected' : '') }} value="{{ $countries->id }}">{{ $countries->name }}</option>              
                                @endforeach
                            </select>
                            <div class="acc__input-error error-nationality text-danger mt-2"></div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-6 pt-4">
                            <label for="vertical-form-12" class="form-label inline-flex">Ethnic Origin <span class="text-danger"> *</span></label>
                            <select id="vertical-form-12" name="ethnicity" class="lcc-tom-select w-full lccToms">
                                <option value="">Please Select</option>
                                @foreach($ethnicity as $ethnicities)
                                    <option  {{ (isset($employee->ethnicity_id) && $employee->ethnicity_id == $ethnicities->id ? 'Selected' : '') }} value="{{ $ethnicities->id }}">{{ $ethnicities->name }}</option>              
                                @endforeach
                            </select>
                            <div class="acc__input-error error-ethnicity text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="intro-y col-span-12">
                    <div class="grid grid-cols-12 gap-x-4">
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="vertical-form-13" class="form-label inline-flex">Car Reg. Number</label>
                            <input value="{{ (isset($employee->car_reg_number) ? $employee->car_reg_number : '') }}" id="vertical-form-13" type="text" name="car_reg_number" class="form-control rounded-none form-control-lg"  aria-label="default input example">
        
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="vertical-form-14" class="form-label inline-flex">Driving Licence Number</label>
                            <input value="{{ (isset($employee->drive_license_number) ? $employee->drive_license_number : '') }}" id="vertical-form-14" type="text" name="drive_license_number" class="form-control rounded-none form-control-lg"  aria-label="default input example">
        
                        </div>
                    </div>
                </div>

                
                <div class="intro-y col-span-12">
                    <div class="font-medium text-base">
                        <label for="input-wizard-4" class="form-label inline-flex">Educational Qualification <i data-theme="light" data-tooltip-content="#edu-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
        
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="edu-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please Set Educational Qualification</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div> 
                </div>
                <div class="intro-y col-span-12 sm:col-span-3">
                    <label for="highest_qualification_on_entry_id" class="form-label inline-flex">Highest Educational Qualification <span class="text-danger"> *</span></label>
                    <select id="highest_qualification_on_entry_id" name="highest_qualification_on_entry_id" class="tom-selects w-full lccToms">
                        <option value="">Please Select</option>
                        @foreach($qualEntries as $entry)
                            <option {{ (isset($employee->education->highest_qualification_on_entry_id) && $employee->education->highest_qualification_on_entry_id == $entry->id ? 'Selected' : '') }} value="{{ $entry->id }}">{{ $entry->name }}</option>              
                        @endforeach
                    </select>
                    <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                    <label for="qualification_name" class="form-label inline-flex">Qualification Name <span class="text-danger">*</span></label>
                    <input value="{{ (isset($employee->education->qualification_name) ? $employee->education->qualification_name : '') }}" id="qualification_name" type="text" class="form-control" name="qualification_name">
                    <div class="acc__input-error error-qualification_name text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                    <label for="award_body" class="form-label inline-flex">Award Body <span class="text-danger">*</span></label>
                    <input value="{{ (isset($employee->education->award_body) ? $employee->education->award_body : '') }}" id="award_body" type="text" class="form-control" name="award_body">
                    <div class="acc__input-error error-award_body text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                    <label for="award_date" class="form-label inline-flex">Award Date <span class="text-danger"> *</span></label>
                    <input value="{{ (isset($employee->education->award_date) && !empty($employee->education->award_date) ? date('m-Y', strtotime($employee->education->award_date)) : '') }}" id="award_date" type="text" placeholder="MM-YYYY" autocomplete="off" class="form-control datepicker monthYearMask" name="award_date" data-format="MM-YYYY" data-single-mode="true">
                    <div class="acc__input-error error-award_date text-danger mt-2"></div>
                </div>


                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                        Save &amp; Continue
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 svg_2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
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
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                <div class="font-medium text-base">Employment Details</div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-1" class="form-label inline-flex">Started On <span class="text-danger"> *</span></label>
                        <input value="{{ (isset($employee->employment->started_on) && !empty($employee->employment->started_on) ? date('Y-m-d', strtotime($employee->employment->started_on)) : '') }}" id="vertical-form-1" type="text" placeholder="DD-MM-YYYY" id="started_on" class="form-control form-control-lg datepicker rounded-none" name="started_on" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-started_on text-danger mt-2"></div>
                    </div>            
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-3" class="form-label inline-flex">Site Location <span class="text-danger">*</span></label>
                        <select id="vertical-form-11" name="site_location[]" class=" w-full lccToms lcc-tom-select" multiple>
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option {{ (!empty($emp_venue) && in_array($venue->id, $emp_venue) ? 'Selected' : '') }} value="{{ $venue->id }}">{{ $venue->name }}</option>              
                            @endforeach
                        </select>         
                        <div class="acc__input-error error-site_location text-danger mt-2"></div>
                    </div>    
                    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-2" class="form-label inline-flex">Punch Number <span class="text-danger">*</span></label>
                        <input value="{{ (isset($employee->employment->punch_number) ? $employee->employment->punch_number : '') }}" id="vertical-form-2" type="text" class="form-control rounded-none form-control-lg"  name="punch_number" aria-label="default input example">
                        <div class="acc__input-error error-punch_number text-danger mt-2"></div>
                    </div>  
   
                    <div class="intro-y col-span-12 sm:col-span-6"> <!-- Type selection based with work number available('employee') -->
                        <label for="employee_work_type" class="form-label inline-flex">Type <span class="text-danger">*</span></label>
                        <select id="employee_work_type" name="employee_work_type" class="lcc-tom-select w-full">
                            <option value="">Please Select</option>
                            @foreach($workTypes as $type)
                                <option {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == $type->id ? 'Selected' : '') }} value="{{ $type->id }}">{{ $type->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                    </div>
                    
                    <div class="employeeWorkTypeFields intro-y col-span-12 sm:col-span-6" style="display: {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == 3 ? 'block' : 'none') }};">
                        <label for="vertical-form-5" class="form-label inline-flex">Works Number <span class="text-danger">*</span></label>
                        <input value="{{ (isset($employee->employment->works_number) ? $employee->employment->works_number : '') }}" id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg"  name="works_number" aria-label="default input example">
                        <div class="acc__input-error error-works_number text-danger mt-2"></div>  
                    </div>
                    <div class="intro-y  col-span-12 sm:col-span-6 taxRefNo" style="display: {{ (isset($employee->employment->employee_work_type_id) && $employee->employment->employee_work_type_id == 2 ? 'block' : 'none') }};">
                        <label for="utr_number" class="form-label inline-flex">Unique Tax Ref No <span class="text-danger"> *</span></label>
                        <input value="{{ (isset($employee->employment->utr_number) ? $employee->employment->utr_number : '') }}" id="utr_number" type="text" name="utr_number" class="w-full form-control" />
                        <div class="acc__input-error error-utr_number text-danger mt-2"></div>
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="job_title" class="form-label inline-flex">Job Title <span class="text-danger">*</span></label>
                        <select id="job_title" name="job_title" class=" w-full lccToms lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($jobTitles as $jobTitle)
                                <option {{ (isset($employee->employment->employee_job_title_id) && $employee->employment->employee_job_title_id == $jobTitle->id ? 'Selected' : '') }} value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-job_title text-danger mt-2"></div>
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="department" class="form-label inline-flex">Department <span class="text-danger">*</span></label>
                        <select id="department" name="department" class=" w-full lccToms lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($departments as $department)
                                <option {{ (isset($employee->employment->department_id) && $employee->employment->department_id == $department->id ? 'Selected' : '') }} value="{{ $department->id }}">{{ $department->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-department text-danger mt-2"></div>
                    </div>                      
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-8" class="form-label inline-flex">Office Telephone / Ext. No</label>
                        <input value="{{ (isset($employee->employment->office_telephone) ? $employee->employment->office_telephone : '') }}" id="vertical-form-8" type="text" class="form-control rounded-none form-control-lg" name="office_telephone" aria-label="default input example">                   
                        
                    </div>    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-10" class="form-label inline-flex">Mobile </label>
                        <input value="{{ (isset($employee->employment->mobile) ? $employee->employment->mobile : '') }}" id="vertical-form-10" type="text" class="form-control rounded-none form-control-lg"  name="mobile" aria-label="default input example">
                        
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="email" class="form-label inline-flex">Email (username) <span class="text-danger">*</span> </label>
                        <input value="{{ (isset($employee->employment->email) ? $employee->employment->email : '') }}" id="email" type="text" class="form-control rounded-none form-control-lg"  name="email" aria-label="default input example">   
                        <div class="acc__input-error error-email text-danger mt-2"></div>                        
                    </div>
                

                    <div class="font-medium text-base intro-y col-span-12 mt-5">
                        <label for="input-wizard-4" class="form-label inline-flex">Terms <i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
        
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please check terms</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div> 
                    <div class="intro-y col-span-12">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="notice-period" class="form-label inline-flex">Notice Period  <span class="text-danger"> *</span> <span class="form-help m-0 ml-2">Employee must give</span></label>
                                <select id="notice-period" name="notice_period" class="form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($noticePeriods as $noticePeriod)
                                        <option {{ (isset($employee->terms->employee_notice_period_id) && $employee->terms->employee_notice_period_id == $noticePeriod->id ? 'Selected' : '') }} value="{{ $noticePeriod->id }}">{{ $noticePeriod->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-notice_period text-danger mt-2"></div>
            
                            </div> 

                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="employment-period" class="form-label inline-flex employment-period">Period of Employment  <span class="text-danger"> *</span> <span class="form-help m-0 ml-2">This employment is</span></label>
                                <select id="employment-period" name="employment_period" class="form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($employmentPeriods as $employmentPeriod)
                                        <option  {{ (isset($employee->terms->employment_period_id) && $employee->terms->employment_period_id == $employmentPeriod->id ? 'Selected' : '') }} value="{{ $employmentPeriod->id }}">{{ $employmentPeriod->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-employment_period text-danger mt-2"></div>
        
                            </div>

                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="ssp-term" class="form-label inline-flex employment-period">SSP Terms & Conditions   <span class="text-danger"> *</span> <span class="form-help m-0 ml-2" >Employee receives</span></label>
                                <select id="ssp-term" name="ssp_term" class="form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($sspTerms as $sspterm)
                                        <option {{ (isset($employee->terms->employment_ssp_term_id) && $employee->terms->employment_ssp_term_id == $sspterm->id ? 'Selected' : '') }} value="{{ $sspterm->id }}">{{ $sspterm->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-ssp_term text-danger mt-2"></div>
        
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                        Back
                    </button>
                    <button id="form2SaveButton" type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Save & Continue 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 svg_2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
                <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
               
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                
                {{-- <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div> --}}
                <div class="font-medium text-base">Eligibility Info </div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                    
                
                    <div class="col-span-12 sm:col-span-3">
                        <label for="eligible_to_work_status" class="form-label">Do this person is eligible to work in UK?</label>
                        <div class="form-check form-switch">
                            <input {{ (isset($employee->eligibilities->eligible_to_work) && $employee->eligibilities->eligible_to_work == 'Yes' ? 'Checked' : '') }} id="eligible_to_work_status" class="form-check-input" name="eligible_to_work_status" value="Yes" type="checkbox">
                            <label class="form-check-label" for="eligible_to_work">&nbsp;</label>
                        </div>
                    </div>

                    <div class="workPermitTypeFields intro-y col-span-12 sm:col-span-3" style="display: {{ (isset($employee->eligibilities->eligible_to_work) && $employee->eligibilities->eligible_to_work == 'Yes' ? 'block' : 'none') }};">
                        <label for="workpermit_type" class="form-label inline-flex">Type <span class="text-danger">*</span></label>
                        <select id="workpermit_type" name="workpermit_type" class=" w-full lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($workPermitTypes as $workPermitType)
                                <option {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == $workPermitType->id ? 'Selected' : '') }} value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                    </div>
                    <div class="workPermitFields intro-y col-span-12 sm:col-span-3" style="display: {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == 3 ? 'block' : 'none') }};">
                        <label for="workpermit_number" class="form-label inline-flex">Work Permit Number </label>
                        <input value="{{ (isset($employee->eligibilities->workpermit_number) ? $employee->eligibilities->workpermit_number : '') }}" id="workpermit_number" type="text" class="form-control rounded-none form-control-lg"  name="workpermit_number" aria-label="default input example">
                        <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                    </div>              
                    <div class="workPermitFields intro-y col-span-12 sm:col-span-3" style="display: {{ (isset($employee->eligibilities->employee_work_permit_type_id) && $employee->eligibilities->employee_work_permit_type_id == 3 ? 'block' : 'none') }};">
                        <label for="workpermit_expire" class="form-label inline-flex">Work Permit Expiry Date </label>
                        <input value="{{ (isset($employee->eligibilities->workpermit_expire) && !empty($employee->eligibilities->workpermit_expire) ? date('d-m-Y', strtotime($employee->eligibilities->workpermit_expire)) : '') }}" id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="form-control form-control-lg datepicker rounded-none" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">                   
                        <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                    </div>   
                    
                    <div class="intro-y col-span-12">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="intro-y col-span-12 sm:col-span-6 py-1"> <!-- checkbox for yes/no -->
                                <label for="document_type" class="form-label inline-flex">Document Type <span class="text-danger"> *</span></label>
                                <select id="document_type" name="document_type" class="form-control lccToms lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($documentTypes as $documentType)
                                        <option {{ (isset($employee->eligibilities->document_type) && $employee->eligibilities->document_type == $documentType->id ? 'Selected' : '') }} value="{{ $documentType->id }}">{{ $documentType->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-document_type text-danger mt-2"></div>
                            </div>

                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-4" class="form-label inline-flex">Document Number <span class="text-danger"> *</span></label>
                                <input value="{{ (isset($employee->eligibilities->doc_number) ? $employee->eligibilities->doc_number : '') }}" id="vertical-form-4" type="text" name="doc_number" value="" class="w-full text-sm" />
                                <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-5" class="form-label inline-flex">Document Expiry Date <span class="text-danger"> *</span></label>
                                <input value="{{ (isset($employee->eligibilities->doc_expire) && !empty($employee->eligibilities->doc_expire) ? date('d-m-Y', strtotime($employee->eligibilities->doc_expire)) : '') }}" id="vertical-form-5" type="text" placeholder="DD-MM-YYYY" id="doc_expire" class="form-control  datepicker rounded-none" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-6" class="form-label inline-flex">Document Issue Country <span class="text-danger"> *</span></label>
                                <select id="vertical-form-6" name="doc_issue_country" class="lcc-tom-select w-full lccToms">
                                    <option value="">Please Select</option>
                                    @foreach($country as $countries)
                                        <option {{ (isset($employee->eligibilities->doc_issue_country) && $employee->eligibilities->doc_issue_country == $countries->id ? 'Selected' : '') }} value="{{ $countries->id }}">{{ $countries->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-doc_issue_country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                        Back
                    </button>
                    <button id="form3SaveButton" type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Save & Continue 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 svg_2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
                <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_4" class="wizard-step-form">
                <div class="font-medium text-base">Emergency Contact</div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="emergency_contact_name" class="form-label inline-flex">Name <span class="text-danger">*</span></label>
                        <input value="{{ (isset($employee->emergencyContact->emergency_contact_name) ? $employee->emergencyContact->emergency_contact_name : '') }}" id="emergency_contact_name" type="text" class="form-control rounded-none form-control-lg inputUppercase"  name="emergency_contact_name" aria-label="default input example">
                        <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                    </div>              
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="relationship" class="form-label inline-flex">Relationship <span class="text-danger">*</span></label>
                        <select id="relationship" name="relationship" class="form-control lccToms lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($relation as $kins)
                                <option {{ (isset($employee->emergencyContact->kins_relation_id) && $employee->emergencyContact->kins_relation_id == $kins->id ? 'Selected' : '') }} value="{{ $kins->id }}">{{ $kins->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-relationship text-danger mt-2"></div>
                    </div>
                    <div class="font-medium text-base intro-y col-span-12">
                        <label for="input-wizard-4" class="form-label inline-flex">Address <i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
        
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please add the emergency contact?</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div> 
                    <div class="intro-y col-span-12">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="col-span-6 addressWrap" id="emcAddressWrap">
                                <div class="addresses mb-2" style="display: {{ (isset($employee->emergencyContact->address_id) && $employee->emergencyContact->address_id > 0 ? 'block' : 'none') }};">
                                    {!! (isset($employee->emergencyContact->address_input) && !empty($employee->emergencyContact->address_input) ? $employee->emergencyContact->address_input : '') !!}
                                </div>
                                <div>
                                    <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ (isset($employee->emergencyContact->address_id) && $employee->emergencyContact->address_id > 0 ? 'Update' : 'Add') }} Address</span>
                                    </button>
                                    <input type="hidden" name="address_prfix" class="address_prfix_field" value="emc_"/>
                                </div>
                                <div class="acc__input-error error-emc_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">

                            </div>
                        </div>
                    </div>            
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-4" class="form-label inline-flex">Telephone </label>
                        <input value="{{ (isset($employee->emergencyContact->emergency_contact_telephone) ? $employee->emergencyContact->emergency_contact_telephone : '') }}" id="vertical-form-4" type="text" class="form-control rounded-none form-control-lg" name="emergency_contact_telephone" aria-label="default input example">
                                        
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                        <input value="{{ (isset($employee->emergencyContact->emergency_contact_mobile) ? $employee->emergencyContact->emergency_contact_mobile : '') }}" id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg" name="emergency_contact_mobile" aria-label="default input example">
                        <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                    </div>
    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-6" class="form-label inline-flex">Email </label>
                        <input value="{{ (isset($employee->emergencyContact->emergency_contact_email) ? $employee->emergencyContact->emergency_contact_email : '') }}" id="vertical-form-6" type="text" name="emergency_contact_email" class="form-control rounded-none form-control-lg" aria-label="default input example">
                        
                    </div>
                </div>
                <input type="hidden" name="url" value=""/> 
                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                        Back
                    </button>
                    <button id="form4SaveButton" type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Create 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 svg_2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>

                <input type="hidden" name="employee_id" value="{{ (isset($employee->id) && $employee->id > 0 ? $employee->id : 0) }}"/>
            </form>
        </fieldset>
    </div>
    <!-- END: Wizard Layout -->

    <!-- BEGIN: Address Modal -->
    <div id="addressModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
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
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full" name="address_line_1">
                                <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                                <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full" name="address_line_2">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_city" class="form-label">Town/City <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full" name="city">
                                <div class="acc__input-error error-city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_postal_zip_code" class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="form-control w-full" name="post_code">
                                <div class="acc__input-error error-post_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full" name="country">
                                <div class="acc__input-error error-country text-danger mt-2"></div>
                            </div>
                            <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key={{$postcode_api}}" />
                            <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key={{$postcode_api}}"></script>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="insertAddress" class="btn btn-primary w-auto">     
                            Add Address                      
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
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
@endsection
@section('script')
    @vite('resources/js/employee-new.js')
@endsection