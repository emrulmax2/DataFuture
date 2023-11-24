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
                    <select id="data-4" name="title" class=" lcc-tom-select w-full  lccTom  ">
                        <option  value="">Please Select</option>   
                        @foreach($titles as $title)
                            <option  value="{{ $title->id }}">{{ $title->name }}</option>              
                        @endforeach
                    </select>
                    <div class="acc__input-error error-title text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-2" class="form-label inline-flex">First name(s) <span class="text-danger">*</span></label>
                    <input id="vertical-form-2" type="text" class="form-control rounded-none form-control-lg" name="first_name" aria-label="default input example">
                    <div class="acc__input-error error-first_name text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-1" class="form-label inline-flex">Surname <span class="text-danger">*</span></label>
                    <input id="vertical-form-1" type="text" class="form-control rounded-none form-control-lg"  name="sur_name" aria-label="default input example">
                    <div class="acc__input-error error-sur_name text-danger mt-2"></div>
                </div>
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-4" class="form-label inline-flex">Telephone</label>
                    <input id="vertical-form-4" type="text" class="form-control rounded-none form-control-lg" name="telephone" aria-label="default input example">
    
                </div>
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                    <input id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg" name="mobile" aria-label="default input example">
                    <div class="acc__input-error error-mobile text-danger mt-2"></div>
                </div>

                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-6" class="form-label inline-flex">Email <span class="text-danger">*</span></label>
                    <input id="vertical-form-6" type="text" name="email" class="form-control rounded-none form-control-lg" aria-label="default input example">
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
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="vertical-form-13" class="form-label inline-flex">Address Line 1 <span class="text-danger">*</span></label>
                            <input id="vertical-form-13" type="text" name="address_line_1" class="form-control rounded-none form-control-lg"  aria-label="default input example">
                            <div class="acc__input-error error-address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="vertical-form-14" class="form-label inline-flex">Address Line 2</label>
                            <input id="vertical-form-14" type="text" name="address_line_2" class="form-control rounded-none form-control-lg"  aria-label="default input example">
                            
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="post_code" class="form-label inline-flex">Post Code <span class="text-danger">*</span></label>
                            <input  id="post_code" type="text" name="post_code" value="" class="w-full text-sm"  />
                            <div class="acc__input-error error-post_code text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-13" class="form-label inline-flex">City <span class="text-danger">*</span></label>
                            <input  id="vertical-form-13" type="text" name="city" value="" class="w-full text-sm"  />
                            <div class="acc__input-error error-city text-danger mt-2"></div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-14" class="form-label inline-flex">State <span class="text-danger">*</span></label>
                            <input id="vertical-form-14" type="text" name="state" value="" class="w-full text-sm" />
                            <div class="acc__input-error error-state text-danger mt-2"></div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-15" class="form-label inline-flex">Country <span class="text-danger">*</span></label>
                            <input id="vertical-form-15" type="text" name="country" value="" class="w-full text-sm" />
                            <div class="acc__input-error error-country text-danger mt-2"></div>
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
                    <select id="vertical-form-7" name="sex" class="lcc-tom-select w-full  lccTom ">
                        <option  value="">Please Select</option>   
                        @foreach($sexIdentifier as $sex)
                            <option  value="{{ $sex->id }}">{{ $sex->name }}</option>              
                        @endforeach
                    </select>
                    <div class="acc__input-error error-sex text-danger mt-2"></div>
                </div>
                
                
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="date_of_birth" class="form-label inline-flex">Date of Birth <span class="text-danger"> *</span></label>
                    <input id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control form-control-lg datepicker rounded-none" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                    <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12 sm:col-span-4">
                    <label for="vertical-form-9" class="form-label inline-flex">NI Number</label>
                    <input id="vertical-form-9" type="text" name="ni_number" class="form-control rounded-none form-control-lg ni-number"  aria-label="default input example">
                    <div class="acc__input-error error-ni_number text-danger mt-2"></div>
                </div>
                <div class="intro-y col-span-12">
                    <div class="grid grid-cols-12 gap-x-4">
                        <div class="col-span-12 sm:col-span-12">
                            <label for="disability_status" class="form-label">Do you have any disabilities?</label>
                            <div class="form-check form-switch">
                                <input id="disability_status" class="form-check-input" name="disability_status" value="1" type="checkbox">
                                <label class="form-check-label" for="disability_status">&nbsp;</label>
                            </div>
                        </div>
                        <div id="disabilityItems" class="col-span-12 sm:col-span-12 disabilityItems hidden">
                            <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                            @php 
                                $ids = [];
                                // if(!empty($apply->disability)):
                                //     foreach($apply->disability as $dis): $ids[] = $dis->disabilitiy_id; endforeach;
                                // endif;
                            @endphp
                            @if(!empty($disability))
                                @foreach($disability as $d)
                                    <div class="form-check {{ !$loop->first ? 'mt-2' : '' }} items-start">
                                        <input {{ (in_array($d->id, $ids) ? 'checked' : '' ) }} id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="form-check-input disability_ids" type="checkbox" value="{{ $d->id }}">
                                        <label class="form-check-label" for="disabilty_id_{{ $d->id }}">{{ $d->name }}</label>
                                    </div>
                                @endforeach 
                            @endif 
                            <div class="acc__input-error error-disability_id text-danger mt-2"></div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-6 py-1">
                            <label for="vertical-form-11" class="form-label inline-flex">Nationality <span class="text-danger"> *</span></label>
                            <select id="vertical-form-11" name="nationality" class="form-control w-full">
                                <option value="">Please Select</option>
                                @foreach($country as $countries)
                                    <option  value="{{ $countries->id }}">{{ $countries->name }}</option>              
                                @endforeach
                            </select>
                            <div class="acc__input-error error-nationality text-danger mt-2"></div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-6 py-1">
                            <label for="vertical-form-12" class="form-label inline-flex">Ethnic Origin <span class="text-danger"> *</span></label>
                            <select id="vertical-form-12" name="ethnicity" class="form-control w-full">
                                <option value="">Please Select</option>
                                @foreach($ethnicity as $ethnicities)
                                    <option  value="{{ $ethnicities->id }}">{{ $ethnicities->name }}</option>              
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
                            <input id="vertical-form-13" type="text" name="car_reg_number" class="form-control rounded-none form-control-lg"  aria-label="default input example">
        
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="vertical-form-14" class="form-label inline-flex">Driving Licence Number</label>
                            <input id="vertical-form-14" type="text" name="drive_license_number" class="form-control rounded-none form-control-lg"  aria-label="default input example">
        
                        </div>
                        {{-- <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-13" class="form-label inline-flex">City <span class="text-danger">*</span></label>
                            <input  id="vertical-form-13" type="text" name="city" value="" class="w-full text-sm"  />
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-14" class="form-label inline-flex">State <span class="text-danger">*</span></label>
                            <input id="vertical-form-14" type="text" name="state" value="" class="w-full text-sm" />
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                            <label for="vertical-form-15" class="form-label inline-flex">Country <span class="text-danger">*</span></label>
                            <input id="vertical-form-15" type="text" name="country" value="" class="w-full text-sm" />
                        </div> --}}
                    </div>
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
            
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                <div class="font-medium text-base">Employment Details</div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-1" class="form-label inline-flex">Started On <span class="text-danger"> *</span></label>
                        <input id="vertical-form-1" type="text" placeholder="DD-MM-YYYY" id="started_on" class="form-control form-control-lg datepicker rounded-none" name="started_on" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-started_on text-danger mt-2"></div>
                    </div>            
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-3" class="form-label inline-flex">Site Location <span class="text-danger">*</span></label>
                        <select id="vertical-form-11" name="site_location[]" class=" w-full lccTom lcc-tom-select" multiple>
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option  value="{{ $venue->id }}">{{ $venue->name }}</option>              
                            @endforeach
                        </select>         
                        <div class="acc__input-error error-site_location text-danger mt-2"></div>
                    </div>    
                    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-2" class="form-label inline-flex">Punch Number <span class="text-danger">*</span></label>
                        <input id="vertical-form-2" type="text" class="form-control rounded-none form-control-lg"  name="punch_number" aria-label="default input example">
                        <div class="acc__input-error error-punch_number text-danger mt-2"></div>
                    </div>  
   
                    <div class="intro-y col-span-12 sm:col-span-6"> <!-- Type selection based with work number available('employee') -->
                        <label for="employee_work_type" class="form-label inline-flex">Type <span class="text-danger">*</span></label>
                        <select id="employee_work_type" name="employee_work_type" class=" w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($workTypes as $type)
                                <option  value="{{ $type->id }}">{{ $type->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                    </div>
                    
                    <div class="intro-y col-span-12 sm:col-span-6 invisible">
                        <label for="vertical-form-5" class="form-label inline-flex">Works Number <span class="text-danger">*</span></label>
                        <input id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg"  name="works_number" aria-label="default input example">
                        <div class="acc__input-error error-works_number text-danger mt-2"></div>  
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="job_title" class="form-label inline-flex">Job Title <span class="text-danger">*</span></label>
                        <select id="job_title" name="job_title" class=" w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($jobTitles as $jobTitle)
                                <option  value="{{ $jobTitle->id }}">{{ $jobTitle->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-job_title text-danger mt-2"></div>
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="department" class="form-label inline-flex">Department <span class="text-danger">*</span></label>
                        <select id="department" name="department" class=" w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($departments as $department)
                                <option  value="{{ $department->id }}">{{ $department->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-department text-danger mt-2"></div>
                    </div>                      
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-8" class="form-label inline-flex">Office Telephone / Ext. No</label>
                        <input id="vertical-form-8" type="text" class="form-control rounded-none form-control-lg" name="office_telephone" aria-label="default input example">                   
                        
                    </div>    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-10" class="form-label inline-flex">Mobile </label>
                        <input id="vertical-form-10" type="text" class="form-control rounded-none form-control-lg"  name="mobile" aria-label="default input example">
                        
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="email" class="form-label inline-flex">Email (username) <span class="text-danger">*</span> </label>
                        <input id="email" type="text" class="form-control rounded-none form-control-lg"  name="email" aria-label="default input example">   
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
                                <label for="notice-period" class="form-label inline-flex">Notice Period  <span class="text-danger"> *</span></label>
                                <select id="notice-period" name="notice_period" class="form-control lccTom lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($noticePeriods as $noticePeriod)
                                        <option  value="{{ $noticePeriod->id }}">{{ $noticePeriod->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-notice_period text-danger mt-2"></div>
            
                            </div> 

                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="employment-period" class="form-label inline-flex employment-period">Period of Employment  <span class="text-danger"> *</span></label>
                                <select id="employment-period" name="employment_period" class="form-control lccTom lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($employmentPeriods as $employmentPeriod)
                                        <option  value="{{ $employmentPeriod->id }}">{{ $employmentPeriod->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-employment_period text-danger mt-2"></div>
        
                            </div>

                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="ssp-term" class="form-label inline-flex employment-period">SSP Terms & Conditions   <span class="text-danger"> *</span></label>
                                <select id="ssp-term" name="ssp_term" class="form-control lccTom lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($sspTerms as $sspterm)
                                        <option  value="{{ $sspterm->id }}">{{ $sspterm->name }}</option>              
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
                            <input  id="eligible_to_work_status" class="form-check-input" name="eligible_to_work_status" value="Yes" type="checkbox">
                            <label class="form-check-label" for="eligible_to_work">&nbsp;</label>
                        </div>
                    </div>

                    <div id="workpermit_type" class="intro-y col-span-12 sm:col-span-3 invisible">
                        <label for="workpermit_type" class="form-label inline-flex">Type <span class="text-danger">*</span></label>
                        <select id="workpermit_type" name="workpermit_type" class=" w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($workPermitTypes as $workPermitType)
                                <option  value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>              
                            @endforeach
                        </select> 
                        <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                    </div>
                    <div id="workpermit-number" class="intro-y col-span-12 sm:col-span-3 invisible">
                        <label for="workpermit_number" class="form-label inline-flex">Work Permit Number </label>
                        <input id="workpermit_number" type="text" class="form-control rounded-none form-control-lg"  name="workpermit_number" aria-label="default input example">
                        <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                    </div>              
                    <div id="workpermit-expire" class="intro-y col-span-12 sm:col-span-3 invisible">
                        <label for="workpermit_expire" class="form-label inline-flex">Work Permit Expiry Date </label>
                        <input id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="form-control form-control-lg datepicker rounded-none" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">                   
                        <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                    </div>   
                    
                    <div class="intro-y col-span-12">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="intro-y col-span-12 sm:col-span-6 py-1"> <!-- checkbox for yes/no -->
                                <label for="document_type" class="form-label inline-flex">Document Type <span class="text-danger"> *</span></label>
                                <select id="document_type" name="document_type" class="form-control lccTom lcc-tom-select">
                                    <option value="">Please Select</option>
                                    @foreach($documentTypes as $documentType)
                                        <option  value="{{ $documentType->id }}">{{ $documentType->name }}</option>              
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-document_type text-danger mt-2"></div>
                            </div>

                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-4" class="form-label inline-flex">Document Number <span class="text-danger"> *</span></label>
                                <input id="vertical-form-4" type="text" name="doc_number" value="" class="w-full text-sm" />
                                <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-5" class="form-label inline-flex">Document Expiry Date <span class="text-danger"> *</span></label>
                                <input id="vertical-form-5" type="text" placeholder="DD-MM-YYYY" id="doc_expire" class="form-control  datepicker rounded-none" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                <label for="vertical-form-6" class="form-label inline-flex">Document Issue Country <span class="text-danger"> *</span></label>
                                <select id="vertical-form-6" name="doc_issue_country" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @foreach($country as $countries)
                                        <option  value="{{ $countries->id }}">{{ $countries->name }}</option>              
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
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_4" class="wizard-step-form">
                <div class="font-medium text-base">Emergency Contact</div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="emergency_contact_name" class="form-label inline-flex">Name <span class="text-danger">*</span></label>
                        <input id="emergency_contact_name" type="text" class="form-control rounded-none form-control-lg"  name="emergency_contact_name" aria-label="default input example">
                        <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                    </div>              
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="relationship" class="form-label inline-flex">Relationship <span class="text-danger">*</span></label>
                        <select id="relationship" name="relationship" class="form-control lccTom lcc-tom-select">
                            @foreach($relation as $kins)
                                <option  value="{{ $kins->id }}">{{ $kins->name }}</option>              
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
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="vertical-form-13" class="form-label inline-flex">Address Line 1</label>
                                <input id="vertical-form-13" type="text" name="emergency_contact_address_line_1" class="form-control rounded-none form-control-lg"  aria-label="default input example">
                                
                                <div class="acc__input-error error-emergency_contact_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="vertical-form-14" class="form-label inline-flex">Address Line 2</label>
                                <input id="vertical-form-14" type="text" name="emergency_contact_address_line_2" class="form-control rounded-none form-control-lg"  aria-label="default input example">
                                
                            </div>
                            
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="vertical-form-14" class="form-label inline-flex">Post Code</label>
                                <input id="vertical-form-14" type="text" name="emergency_contact_post_code" class="form-control rounded-none form-control-lg"  aria-label="default input example">
                                <div class="acc__input-error error-emergency_contact_post_code text-danger mt-2"></div>
                            </div>
                             <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                <label for="vertical-form-13" class="form-label inline-flex">City <span class="text-danger">*</span></label>
                                <input  id="vertical-form-13" type="text" name="emergency_contact_city" value="" class="w-full text-sm"  />
                                <div class="acc__input-error error-emergency_contact_city text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                <label for="vertical-form-14" class="form-label inline-flex">State <span class="text-danger">*</span></label>
                                <input id="vertical-form-14" type="text" name="emergency_contact_state" value="" class="w-full text-sm" />
                                <div class="acc__input-error error-emergency_contact_state text-danger mt-2"></div>
                            </div>
    
                            <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                <label for="vertical-form-15" class="form-label inline-flex">Country <span class="text-danger">*</span></label>
                                <input id="vertical-form-15" type="text" name="emergency_contact_country" value="" class="w-full text-sm" />
                                <div class="acc__input-error error-emergency_contact_country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>            
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-4" class="form-label inline-flex">Telephone </label>
                        <input id="vertical-form-4" type="text" class="form-control rounded-none form-control-lg" name="emergency_contact_telephone" aria-label="default input example">
                                        
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                        <input id="vertical-form-5" type="text" class="form-control rounded-none form-control-lg" name="emergency_contact_mobile" aria-label="default input example">
                        <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                    </div>
    
                    <div class="intro-y col-span-12 sm:col-span-4">
                        <label for="vertical-form-6" class="form-label inline-flex">Email </label>
                        <input id="vertical-form-6" type="text" name="emergency_contact_email" class="form-control rounded-none form-control-lg" aria-label="default input example">
                        
                    </div>
                </div>
                <input type="hidden" name="url" value=""/> 
                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                        Back
                    </button>
                    <button id="form4SaveButton" type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Finished and Create 
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

                
            </form>
        </fieldset>
    </div>
    <!-- END: Wizard Layout -->
@endsection
@section('script')
    @vite('resources/js/employee-new.js')
@endsection