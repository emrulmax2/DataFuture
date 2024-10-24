@extends('../layout/' . $layout)

@section('head')
    <title>{{ $title }}</title>
@endsection

@section('content')
    <div class="dataCollectionFormWrap">
        <div class="container">
            <div class="grid grid-cols-12 gap-0">
                <div class="col-span-12">
                    <div class="form-wizard intro-y box py-10 sm:py-20 mt-5">
                        <div class="form-wizard-header">
                            <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                                <li class="intro-x lg:text-center flex items-center lg:block flex-1 z-10 form-wizard-step-item active">
                                    <button class="w-10 h-10 rounded-full btn btn-primary">1</button>
                                    <div class="lg:w-32 font-medium text-base lg:mt-3 ml-3 lg:mx-auto">Personal Details</div>
                                </li>
                                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Eligibility Info</div>
                                </li>
                                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">3</button>
                                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Employment Info</div>
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
                                <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="input-wizard-4" class="form-label inline-flex">Title <span class="text-danger"> *</span></label>
                                        <select id="data-4" name="title" class="tom-selects w-full lccToms">
                                            <option  value="">Please Select</option>   
                                            @foreach($titles as $title)
                                                <option  value="{{ $title->id }}">{{ $title->name }}</option>              
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-title text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-2" class="form-label inline-flex">First name(s) <span class="text-danger">*</span></label>
                                        <input id="vertical-form-2" type="text" class="form-control inputUppercase" name="first_name" aria-label="default input example">
                                        <div class="acc__input-error error-first_name text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-1" class="form-label inline-flex">Surname <span class="text-danger">*</span></label>
                                        <input id="vertical-form-1" type="text" class="form-control inputUppercase"  name="last_name" aria-label="default input example">
                                        <div class="acc__input-error error-sur_name text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="date_of_birth" class="form-label inline-flex">Date of Birth <span class="text-danger"> *</span></label>
                                        <input id="date_of_birth" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control datepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-7" class="form-label inline-flex">Sex <span class="text-danger"> *</span></label>
                                        <select id="vertical-form-7" name="sex" class="tom-selects w-full lccToms">
                                            <option  value="">Please Select</option>   
                                            @foreach($sexIdentifier as $sex)
                                                <option  value="{{ $sex->id }}">{{ $sex->name }}</option>              
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-sex text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-12" class="form-label inline-flex">Ethnic Origin <span class="text-danger"> *</span></label>
                                        <select id="vertical-form-12" name="ethnicity" class="tom-selects w-full lccToms">
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
                                        <input id="vertical-form-4" type="text" class="form-control form-control-lg" name="telephone" aria-label="default input example">
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-5" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                                        <input id="vertical-form-5" type="text" class="form-control form-control-lg" name="mobile" aria-label="default input example">
                                        <div class="acc__input-error error-mobile text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="vertical-form-6" class="form-label inline-flex">Email <span class="text-danger">*</span></label>
                                        <input id="vertical-form-6" type="text" name="email" class="form-control form-control-lg" aria-label="default input example">
                                        <div class="acc__input-error error-email text-danger mt-2"></div>
                                    </div>

                                    <div class="col-span-12 font-medium text-base pt-3 pb-3">Address</div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="address_line_1" class="form-label inline-flex">Number and street name <span class="text-danger">*</span></label>
                                        <input id="address_line_1" type="text" class="form-control form-control-lg" name="address_line_1">
                                        <div class="acc__input-error error-address_line_1 text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="address_line_2" class="form-label inline-flex">Address Line 2</label>
                                        <input id="address_line_2" type="text" class="form-control form-control-lg" name="address_line_2">
                                        <div class="acc__input-error error-address_line_2 text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="city" class="form-label inline-flex">Locality</label>
                                        <input id="city" type="text" class="form-control form-control-lg" name="city">
                                        <div class="acc__input-error error-city text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="state" class="form-label inline-flex">County</label>
                                        <input id="state" type="text" class="form-control form-control-lg" name="state">
                                        <div class="acc__input-error error-state text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-4">
                                        <label for="post_code" class="form-label inline-flex">Post Code <span class="text-danger">*</span></label>
                                        <input id="post_code" type="text" class="form-control form-control-lg" name="post_code">
                                        <div class="acc__input-error error-post_code text-danger mt-2"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end sm:justify-end mt-5">
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                                        Next
                                    </button>
                                </div>
                            </form>
                        </fieldset>
                        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                            <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
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
                                            <label for="workpermit_number" class="form-label inline-flex">Work Permit Number </label>
                                            <input id="workpermit_number" type="text" class="form-control w-full"  name="workpermit_number" aria-label="default input example">
                                            <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                                        </div>    
                                    </div>          
                                    <div class="col-span-12 sm:col-span-3">
                                        <div class="workPermitFields intro-y" style="display: none;">
                                            <label for="workpermit_expire" class="form-label inline-flex">Work Permit Expiry Date </label>
                                            <input id="workpermit_expire" type="text" placeholder="DD-MM-YYYY" class="form-control w-full datepicker" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">                   
                                            <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                                        </div> 
                                    </div>

                                    <div class="intro-y col-span-12 sm:col-span-3 py-1"> <!-- checkbox for yes/no -->
                                        <label for="document_type" class="form-label inline-flex">Document Type <span class="text-danger"> *</span></label>
                                        <select id="document_type" name="document_type" class="w-full lccToms tom-selects">
                                            <option value="">Please Select</option>
                                            @foreach($documentTypes as $documentType)
                                                <option  value="{{ $documentType->id }}">{{ $documentType->name }}</option>              
                                            @endforeach
                                        </select>
                                        <div class="acc__input-error error-document_type text-danger mt-2"></div>
                                    </div>

                                    <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                        <label for="doc_number" class="form-label inline-flex">Document Number <span class="text-danger"> *</span></label>
                                        <input id="doc_number" type="text" name="doc_number" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                        <label for="doc_expire" class="form-label inline-flex">Document Expiry Date <span class="text-danger"> *</span></label>
                                        <input id="doc_expire" type="text" placeholder="DD-MM-YYYY" id="doc_expire" class="form-control w-full datepicker" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3 py-1">
                                        <label for="doc_issue_country" class="form-label inline-flex">Document Issue Country <span class="text-danger"> *</span></label>
                                        <select id="doc_issue_country" name="doc_issue_country" class="tom-selects w-full lccToms">
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
                                        Back
                                    </button>
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn ml-auto">
                                        Next
                                    </button>
                                </div>
                            </form>
                        </fieldset>
                        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                            <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                                <div class="font-medium text-base">Employment Info</div>
                                <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="employee_work_type" class="form-label inline-flex">Are you a........................? <span class="text-danger">*</span></label>
                                        <select id="employee_work_type" name="employee_work_type" class="lcc-tom-select w-full">
                                            <option value="">Please Select</option>
                                            @foreach($workTypes as $type)
                                                <option  value="{{ $type->id }}">{{ $type->name }}</option>              
                                            @endforeach
                                        </select> 
                                        <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="national_insurance_num" class="form-label inline-flex">National Insurance number <span class="text-danger"> *</span></label>
                                        <input id="national_insurance_num" type="text" name="national_insurance_num" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-national_insurance_num text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <div class="intro-y taxRefNo" style="display: none;">
                                            <label for="tax_ref_no" class="form-label inline-flex">Unique Tax Ref No <span class="text-danger"> *</span></label>
                                            <input id="tax_ref_no" type="text" name="tax_ref_no" value="" class="w-full form-control" />
                                            <div class="acc__input-error error-tax_ref_no text-danger mt-2"></div>
                                        </div>
                                    </div>

                                    <div class="col-span-12 font-medium text-base pt-3 pb-3">Please provide your Bank Details where the payment will be made.</div>
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="bank_name" class="form-label inline-flex">Bank Name <span class="text-danger"> *</span></label>
                                        <input id="bank_name" type="text" name="bank_name" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-bank_name text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="beneficiary_name" class="form-label inline-flex">Beneficiary Name <span class="text-danger"> *</span></label>
                                        <input id="beneficiary_name" type="text" name="beneficiary_name" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-beneficiary_name text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="sort_code" class="form-label inline-flex">Sort Code <span class="text-danger"> *</span></label>
                                        <input id="sort_code" type="text" name="sort_code" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                                    </div>
                                    <div class="intro-y col-span-12 sm:col-span-3">
                                        <label for="account_number" class="form-label inline-flex">Account Number <span class="text-danger"> *</span></label>
                                        <input id="account_number" type="text" name="account_number" value="" class="w-full form-control" />
                                        <div class="acc__input-error error-account_number text-danger mt-2"></div>
                                    </div>

                                </div>
                                <div class="flex items-center justify-between sm:justify-end mt-5">
                                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-auto">
                                        Back
                                    </button>
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn ml-auto">
                                        Next
                                    </button>
                                </div>
                            </form>
                        </fieldset>
                        <fieldset class="wizard-fieldset wizard-last-step px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
                            <form method="post" action="#" id="appicantFormStep_4" class="wizard-step-form">
                                <div class="font-medium text-base">Employment Info</div>
                                <div class="grid grid-cols-12 gap-4 gap-y-3 mt-5">

                                </div>
                                <div class="flex items-center justify-between sm:justify-end mt-5">
                                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-auto">
                                        Back
                                    </button>
                                    <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/employee-data-collection-form.js')
@endsection
