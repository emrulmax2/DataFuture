@extends('../layout/noauth')

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('content')
    <div class="dataCollectionFormWrap">
        <div class="container">
            <div class="grid grid-cols-12 gap-0">
                @if(isset($employee->id) && $employee->id > 0 && $employee->status == 2)
                    <div class="col-span-12">
                        <form method="post" action="#" id="theEmployeeDataCollectionForm">
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                            <div class="form-wizard intro-y box py-20 pt-15 mt-5">
                                @if(Storage::disk('local')->exists('public/company_logo_red.png'))
                                <div class="logoBar flex justify-center pb-10">
                                    <img alt="London Churchill College" class="w-auto h-20" src="{{ (Storage::disk('local')->exists('public/company_logo_red.png') ? Storage::disk('local')->url('public/company_logo_red.png') : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                </div>
                                @endif
                                <div class="form-wizard-header">
                                    <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                                        <li data-id="step_1" class="intro-x lg:text-center flex items-center lg:block flex-1 z-10 form-wizard-step-item active">
                                            <button class="w-10 h-10 rounded-full btn btn-primary">1</button>
                                            <div class="lg:w-32 font-medium text-base lg:mt-3 ml-3 lg:mx-auto">Personal Details</div>
                                        </li>
                                        <li data-id="step_2" class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                                            <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                                            <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Eligibility Info</div>
                                        </li>
                                        <li data-id="step_3" class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                                            <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">3</button>
                                            <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Employment Info</div>
                                        </li>
                                        <li data-id="step_4" class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                                            <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">4</button>
                                            <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Emergency Contact</div>
                                        </li>
                                    </ul>
                                </div>
                                <fieldset id="step_1" class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400 show"> 
                                    <div class="font-medium text-base">Personal Details</div>
                                    <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="input-wizard-4" class="form-label inline-flex">Title <span class="text-danger"> *</span></label>
                                            <select id="data-4" name="title" class="tom-selects w-full lccToms tomRequire">
                                                <option  value="">Please Select</option>   
                                                @foreach($titles as $title)
                                                    <option  value="{{ $title->id }}">{{ $title->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-title text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-2" class="form-label inline-flex">First name(s) <span class="text-danger">*</span></label>
                                            <input id="vertical-form-2" type="text" class="form-control inputUppercase require" name="first_name" aria-label="default input example">
                                            <div class="acc__input-error error-first_name text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-1" class="form-label inline-flex">Surname <span class="text-danger">*</span></label>
                                            <input id="vertical-form-1" type="text" class="form-control inputUppercase require"  name="last_name" aria-label="default input example">
                                            <div class="acc__input-error error-sur_name text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="date_of_birth" class="form-label inline-flex">Date of Birth <span class="text-danger"> *</span></label>
                                            <input id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control datepicker require" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-7" class="form-label inline-flex">Sex <span class="text-danger"> *</span></label>
                                            <select id="vertical-form-7" name="sex" class="tom-selects w-full lccToms tomRequire">
                                                <option  value="">Please Select</option>   
                                                @foreach($sexIdentifier as $sex)
                                                    <option  value="{{ $sex->id }}">{{ $sex->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-sex text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="nationality_id" class="form-label inline-flex">Nationality <span class="text-danger"> *</span></label>
                                            <select id="nationality_id" name="nationality_id" class="tom-selects w-full lccToms tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($country as $ctry)
                                                    <option  value="{{ $ctry->id }}">{{ $ctry->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-nationality_id text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-12" class="form-label inline-flex">Ethnic Origin <span class="text-danger"> *</span></label>
                                            <select id="vertical-form-12" name="ethnicity" class="tom-selects w-full lccToms tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($ethnicity as $ethnicities)
                                                    <option  value="{{ $ethnicities->id }}">{{ $ethnicities->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-ethnicity text-danger mt-2"></div>
                                        </div>

                                        
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="disability_status" class="form-label">Do you have any disabilities?</label>
                                            <div class="form-check form-switch">
                                                <input id="disability_status" class="form-check-input" name="disability_status" value="1" type="checkbox">
                                                <label class="form-check-label" for="disability_status">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-8">
                                            <div id="disabilityItems" class="disabilityItems hidden">
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
                                        </div>
                                        
                                        <div class="col-span-12 font-medium text-base pt-3 pb-3">Contact Details</div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-4" class="form-label inline-flex">Home Phone</label>
                                            <input id="vertical-form-4" type="text" class="form-control" name="telephone" aria-label="default input example">
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                                            <input id="vertical-form-5" type="text" class="form-control require" name="mobile" aria-label="default input example">
                                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-6" class="form-label inline-flex">Email <span class="text-danger">*</span></label>
                                            <input id="vertical-form-6" readonly value="{{ $employee->email }}" type="text" name="email" class="form-control" aria-label="default input example">
                                            <div class="acc__input-error error-email text-danger mt-2"></div>
                                        </div>

                                        <div class="col-span-12 font-medium text-base pt-3 pb-3">Address</div>
                                        <div class="col-span-6 addressWrap" id="empAddressWrap">
                                            <div class="addresses mb-2" style="display: none;"></div>
                                            <div>
                                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                                </button>
                                                <input type="hidden" name="address_prfix" class="address_prfix_field" value="emp_"/>
                                            </div>
                                            <div class="acc__input-error error-emp_address_line_1 text-danger mt-2"></div>
                                        </div>


                                        <div class="col-span-12 font-medium text-base pt-3 pb-3">Educational Qualification</div>
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="highest_qualification_on_entry_id" class="form-label inline-flex">Highest Educational Qualification <span class="text-danger"> *</span></label>
                                            <select id="highest_qualification_on_entry_id" name="highest_qualification_on_entry_id" class="tom-selects w-full lccToms tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($qualEntries as $entry)
                                                    <option  value="{{ $entry->id }}">{{ $entry->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                                            <label for="qualification_name" class="form-label inline-flex">Qualification Name <span class="text-danger">*</span></label>
                                            <input id="qualification_name" type="text" class="form-control require" name="qualification_name">
                                            <div class="acc__input-error error-qualification_name text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                                            <label for="award_body" class="form-label inline-flex">Award Body <span class="text-danger">*</span></label>
                                            <input id="award_body" type="text" class="form-control require" name="award_body">
                                            <div class="acc__input-error error-award_body text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3 eduQuals">
                                            <label for="award_date" class="form-label inline-flex">Award Date <span class="text-danger"> *</span></label>
                                            <input id="award_date" type="text" placeholder="MM-YYYY" autocomplete="off" class="form-control datepicker monthYearMask require" name="award_date" data-format="MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-award_date text-danger mt-2"></div>
                                        </div>

                                    </div>
                                    <div class="flex items-center justify-end sm:justify-end mt-5">
                                        <button type="button" class="btn btn-primary w-auto form-wizard-next-btn ml-auto">
                                            Next <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                                        </button>
                                    </div>
                                </fieldset>
                                <fieldset id="step_2" class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                                    <div class="font-medium text-base">Eligibility Info</div>
                                    <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                        <div class="col-span-12 sm:col-span-3">
                                            <label for="eligible_to_work_status" class="form-label">Are you eligible to work in UK?</label>
                                            <div class="form-check form-switch">
                                                <input  id="eligible_to_work_status" class="form-check-input" name="eligible_to_work_status" value="Yes" type="checkbox">
                                                <label class="form-check-label" for="eligible_to_work">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="workPermitTypeFields intro-y" style="display: none;">
                                                <label for="workpermit_type" class="form-label inline-flex">Your Status In UK <span class="text-danger">*</span></label>
                                                <select id="workpermit_type" name="workpermit_type" class="w-full tom-selects">
                                                    <option value="">Please Select</option>
                                                    @foreach($workPermitTypes as $workPermitType)
                                                        <option  value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>              
                                                    @endforeach
                                                </select> 
                                                <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="workPermitFields intro-y" style="display: none;">
                                                <label for="workpermit_number" class="form-label inline-flex">Work Permit Number <span class="text-danger">*</span></label>
                                                <input id="workpermit_number" type="text" class="form-control w-full"  name="workpermit_number" aria-label="default input example">
                                                <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                                            </div>    
                                        </div>          
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="workPermitFields intro-y" style="display: none;">
                                                <label for="workpermit_expire" class="form-label inline-flex">Work Permit Expiry Date <span class="text-danger">*</span></label>
                                                <input id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="form-control w-full datepicker" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">                   
                                                <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                                            </div> 
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-3 py-1"> <!-- checkbox for yes/no -->
                                            <label for="document_type" class="form-label inline-flex">Document Type <span class="text-danger"> *</span></label>
                                            <select id="document_type" name="document_type" class="w-full lccToms tom-selects tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($documentTypes as $documentType)
                                                    <option  value="{{ $documentType->id }}">{{ $documentType->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-document_type text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                            <label for="doc_number" class="form-label inline-flex">Document Number <span class="text-danger"> *</span></label>
                                            <input id="doc_number" type="text" name="doc_number" value="" class="w-full form-control require" />
                                            <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                            <label for="doc_expire" class="form-label inline-flex">Document Expiry Date <span class="text-danger"> *</span></label>
                                            <input id="doc_expire" type="text" placeholder="DD-MM-YYYY" id="doc_expire" class="form-control w-full datepicker require" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                            <label for="doc_issue_country" class="form-label inline-flex">Document Issue Country <span class="text-danger"> *</span></label>
                                            <select id="doc_issue_country" name="doc_issue_country" class="tom-selects w-full lccToms tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($country as $countries)
                                                    <option  value="{{ $countries->id }}">{{ $countries->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-doc_issue_country text-danger mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end mt-5">
                                        <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-auto">
                                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-primary w-auto form-wizard-next-btn ml-auto">
                                            Next <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                                        </button>
                                    </div>
                                </fieldset>
                                <fieldset id="step_3" class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                                    <div class="font-medium text-base">Employment Info</div>
                                    <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="employee_work_type" class="form-label inline-flex">Are you a........................? <span class="text-danger">*</span></label>
                                            <select id="employee_work_type" name="employee_work_type" class="lcc-tom-select w-full tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($workTypes as $type)
                                                    <option  value="{{ $type->id }}">{{ $type->name }}</option>              
                                                @endforeach
                                            </select> 
                                            <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="national_insurance_num" class="form-label inline-flex">National Insurance number <span class="text-danger"> *</span></label>
                                            <input id="national_insurance_num" type="text" name="national_insurance_num" value="" class="w-full form-control ni-number" />
                                            <div class="acc__input-error error-national_insurance_num text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="intro-y taxRefNo" style="display: none;">
                                                <label for="utr_number" class="form-label inline-flex">Unique Tax Ref No <span class="text-danger"> *</span></label>
                                                <input id="utr_number" type="text" name="utr_number" value="" class="w-full form-control" />
                                                <div class="acc__input-error error-utr_number text-danger mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="col-span-12 font-medium text-base pt-3 pb-3">Please provide your Bank Details where the payment will be made.</div>
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="beneficiary_name" class="form-label inline-flex">Beneficiary Name <span class="text-danger"> *</span></label>
                                            <input id="beneficiary_name" type="text" name="beneficiary_name" value="" class="w-full form-control require" />
                                            <div class="acc__input-error error-beneficiary_name text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="sort_code" class="form-label inline-flex">Sort Code <span class="text-danger"> *</span></label>
                                            <input id="sort_code" type="text" name="sort_code" value="" class="w-full form-control require sortCode" />
                                            <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-3">
                                            <label for="account_number" class="form-label inline-flex">Account Number <span class="text-danger"> *</span></label>
                                            <input id="account_number" maxlength="8" minlength="8" type="text" name="account_number" value="" class="w-full form-control account_number require" />
                                            <div class="acc__input-error error-account_number text-danger mt-2"></div>
                                        </div>

                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end mt-5">
                                        <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-auto">
                                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Previous
                                        </button>
                                        <button type="button" class="btn btn-primary w-auto form-wizard-next-btn ml-auto">
                                            Next <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                                        </button>
                                    </div>
                                </fieldset>
                                <fieldset id="step_4" class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                                    <div class="font-medium text-base">Next of Kin Details</div>
                                    <div class="text-slate-400 text-sm">In the event of an emergency, we require your next of kin's contact information. Please provide these details.</div>
                                    <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                        <div class="intro-y col-span-12 sm:col-span-6">
                                            <label for="emergency_contact_name" class="form-label inline-flex">Name <span class="text-danger">*</span></label>
                                            <input id="emergency_contact_name" type="text" class="form-control inputUppercase require"  name="emergency_contact_name" aria-label="default input example">
                                            <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                                        </div>              
                                        <div class="intro-y col-span-12 sm:col-span-6">
                                            <label for="relationship" class="form-label inline-flex">Relationship <span class="text-danger">*</span></label>
                                            <select id="relationship" name="relationship" class="form-control lccToms lcc-tom-select tomRequire">
                                                <option value="">Please Select</option>
                                                @foreach($relation as $kins)
                                                    <option  value="{{ $kins->id }}">{{ $kins->name }}</option>              
                                                @endforeach
                                            </select>
                                            <div class="acc__input-error error-relationship text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-4" class="form-label inline-flex">Home Phone </label>
                                            <input id="vertical-form-4" type="text" class="form-control" name="emergency_contact_telephone" aria-label="default input example">
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                                            <input id="vertical-form-5" type="text" class="form-control require" name="emergency_contact_mobile" aria-label="default input example">
                                            <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                                        </div>
                                        <div class="intro-y col-span-12 sm:col-span-4">
                                            <label for="vertical-form-6" class="form-label inline-flex">Email </label>
                                            <input id="vertical-form-6" type="text" name="emergency_contact_email" class="form-control" aria-label="default input example">
                                        </div>

                                        <div class="col-span-12 font-medium text-base pt-3 pb-3">Address</div>
                                        <div class="col-span-6 addressWrap" id="emcAddressWrap">
                                            <div class="addresses mb-2" style="display: none;"></div>
                                            <div>
                                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                                </button>
                                                <input type="hidden" name="address_prfix" class="address_prfix_field" value="emc_"/>
                                            </div>
                                            <div class="acc__input-error error-emc_address_line_1 text-danger mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end mt-5">
                                        <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-auto">
                                            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Previous
                                        </button>
                                        <button type="submit" id="saveEmpData" class="btn btn-success text-white w-auto">
                                            Submit
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
                                    </div>
                                </fieldset>
                            </div>
                        </form>
                    </div>
                @elseif(isset($employee->id) && $employee->id > 0 && $employee->status == 4)
                    <div class="col-span-3"></div>
                    <div class="col-span-6">
                        <div class="intro-y box p-10 text-center">
                            <div class="p-5 text-center">
                                <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                                <div class="text-3xl mt-5 warningModalTitle">Congratulation</div>
                                <div class="text-slate-500 mt-2 warningModalDesc">Your data are waiting for reviews.</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-span-3"></div>
                    <div class="col-span-6">
                        <div class="intro-y box p-10 text-center">
                            <div class="p-5 text-center">
                                <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                                <div class="text-3xl mt-5 warningModalTitle">Thank You!</div>
                                <div class="text-slate-500 mt-2 warningModalDesc">Your submitted data successfully reviewed and submitted.</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- BEGIN: Success Modal Content -->
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
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
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
    <!-- END: Warning Modal Content -->

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
                            <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key={{ $postcode_api }}" />
                            <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key={{ $postcode_api }}"></script>
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
    @vite('resources/js/employee-data-collection-form.js')
@endsection
