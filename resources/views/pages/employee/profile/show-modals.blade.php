<!-- BEGIN: Edit Personal Details Modal -->
<div id="editAdmissionPersonalDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editAdmissionPersonalDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Personal Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-4">
                            <label for="title_id" class="form-label">Title <span class="text-danger">*</span></label>
                            <select id="title_id" class="lccTom lcc-tom-select w-full" name="title_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($titles))
                                    @foreach($titles as $t)
                                        <option {{ isset($employee->title_id) && $employee->title_id == $t->id ? 'Selected' : '' }} value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-title_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="first_name" class="form-label">First Name(s) <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employee->first_name) ? $employee->first_name : '' }}" placeholder="First Name" id="first_name" class="form-control inputUppercase" name="first_name">
                            <div class="acc__input-error error-first_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employee->last_name) ? $employee->last_name : '' }}" placeholder="Last Name" id="last_name" class="form-control inputUppercase" name="last_name">
                            <div class="acc__input-error error-last_name text-danger mt-2"></div>
                        </div>
                                 
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="employee_telephone" class="form-label inline-flex">Telephone </label>
                            <input id="employee_telephone" type="text" value="{{ isset($employee->telephone) ? $employee->telephone : '' }}" class="form-control rounded-none form-control-lg" name="telephone" aria-label="default input example">
                                            
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="employee_mobile" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                            <input id="employee_mobile" type="text" value="{{ isset($employee->mobile) ? $employee->mobile : '' }}" class="form-control rounded-none form-control-lg" name="mobile" aria-label="default input example">
                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                        </div>
        
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="employee_email" class="form-label inline-flex">Email </label>
                            <input id="employee_email" type="text" value="{{ isset($employee->email) ? $employee->email : '' }}" name="email" class="form-control rounded-none form-control-lg" aria-label="default input example">
                            
                        </div>   
                        <div class="col-span-12 sm:col-span-4">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employee->date_of_birth) ? $employee->date_of_birth : '' }}" placeholder="DD-MM-YYYY" id="date_of_birth" class="form-control datepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                        </div>
                        
                        <div class="col-span-12 sm:col-span-4">
                            <label for="sex_identifier_id" class="form-label">Sex Identifier/Gender <span class="text-danger">*</span></label>
                            <select id="sex_identifier_id" class="lccTom lcc-tom-select w-full" name="sex_identifier_id">
                                <option value="" selected>Please Select</option>
                                @if($sexids->count() > 0)
                                    @foreach($sexids as $si)
                                        <option {{ isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-sex_identifier_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="nationality_id" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <select id="nationality_id" class="lccTom lcc-tom-select w-full" name="nationality_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($employee->nationality_id) && $employee->nationality_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-nationality_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label for="ethnicity_id" class="form-label">Ethnicity <span class="text-danger">*</span></label>
                            <select id="ethnicity_id" class="lccTom lcc-tom-select w-full" name="ethnicity_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($ethnicity))
                                    @foreach($ethnicity as $n)
                                        <option {{ isset($employee->ethnicity_id) && $employee->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-ethnicity_id text-danger mt-2"></div>
                        </div>
                        
                        <div class="col-span-12 sm:col-span-3">
                            <label for="ni_number" class="form-label">NI Number <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employee->ni_number) ? $employee->ni_number : '' }}" id="ni_number" class="form-control inputUppercase ni-number" name="ni_number"  >
                            <div class="acc__input-error error-ni_number text-danger mt-2"></div>
                        </div>
                        
                        <div class="col-span-12 sm:col-span-3">
                            <label for="car_reg_number" class="form-label">Car Reg. Number </label>
                            <input type="text" value="{{ isset($employee->car_reg_number) ? $employee->car_reg_number : '' }}" id="car_reg_number" class="form-control " name="car_reg_number"  >
                            
                        </div>
                        
                        <div class="col-span-12 sm:col-span-3">
                            <label for="drive_license_number" class="form-label">Driving License </label>
                            <input type="text" value="{{ isset($employee->drive_license_number) ? $employee->drive_license_number : '' }}" id="drive_license_number" class="form-control" name="drive_license_number"  >
                            
                        </div>

                        <div class="col-span-12 sm:col-span-12">
                            <label for="disability_status" class="form-label">Do you have any disabilities?</label>
                            <div class="form-check form-switch">
                                <input {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? 'checked' : '') }} id="disability_status" class="form-check-input" name="disability_status" value="1" type="checkbox">
                                <label class="form-check-label" for="disability_status">&nbsp;</label>
                            </div>
                        </div>
                        <div id="disabilityItems" class="col-span-12 sm:col-span-12 disabilityItems {{ (isset($employee->disability_status) && $employee->disability_status == 'Yes' ? '' : 'hidden') }}">
                            <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                            @php 
                                $ids = (isset($empDisIds) && !empty($empDisIds) ? $empDisIds : []);
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
                        
                        <div class="col-span-12 sm:col-span-3">
                            <label for="active" class="form-label">Employee Status </label>
                            <div class="form-check form-switch">
                                <input id="status" {{ $employee->status == 1 ? 'checked' : '' }} class="form-check-input m-0" name="status" value="1" type="checkbox">
                                <label class="form-check-label ml-4 font-medium statusDisplay {{ $employee->status == 1 ? 'text-success' : 'text-danger' }}" for="active">{{ $employee->status == 1 ? 'Active' : 'Inactive' }}</label>
                            </div>
                        </div> 
                        {{--<div class="col-span-12 sm:col-span-3 employmentEndedOn" style="display: {{ $employee->status != 1 ? 'block' : 'none' }};"> --}}
                        <div class="col-span-12 sm:col-span-3">
                            <label for="ended_on" class="form-label">Employment End Date</label>
                            <input type="text" value="{{ isset($employee->employment->ended_on) && !empty($employee->employment->ended_on) ? date('d-m-Y', strtotime($employee->employment->ended_on)) : '' }}" placeholder="DD-MM-YYYY" id="ended_on" class="form-control datepicker" name="ended_on" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-ended_on text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Update                      
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
                    <input type="hidden" value="{{ $employee->id }}" name="id"/>
                    
                    <input type="hidden" name="url" value="{{ route('employee.update', $employee->id) }}" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Personal Details Modal -->

<!-- BEGIN: Edit Employment  Details Modal -->
<div id="editEmploymentDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editEmploymentDetailsForm" enctype="multipart/form-data">
            
            <input type="hidden" name="url" value="{{ route('employee.employment.update', $employment->id) }}" />
            <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Employement Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-4">
                            <label for="started_on" class="form-label">Started on <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employment->started_on) ? $employment->started_on : '' }}" placeholder="DD-MM-YYYY" id="started_on" class="form-control datepicker" name="started_on" data-format="DD-MM-YYYY" data-single-mode="true">
                            
                            <div class="acc__input-error error-title_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="employee_work_type_id" class="form-label">Employee type <span class="text-danger">*</span></label>
                            <select id="employee_work_type_id" class="lcc-tom-select w-full" name="employee_work_type_id">
                                <option value="" selected>Please Select</option>
                                @if($employeeWorkTypes->count() > 0)
                                    @foreach($employeeWorkTypes as $si)
                                        <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-first_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 employeeWorkTypeFields" style="{{ (isset($employment->employee_work_type_id) && $employment->employee_work_type_id == 3 ? '' : 'display: none;') }}">
                            <label for="works_number" class="form-label">Works Number<span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employment->works_number) ? $employment->works_number : '' }}" placeholder="" id="works_number" class="form-control" name="works_number">
                            <div class="acc__input-error error-works_number text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="punch_number" class="form-label">Punch Number<span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($employment->punch_number) ? $employment->punch_number : '' }}" placeholder="" id="punch_number" class="form-control" name="punch_number">
                            <div class="acc__input-error error-punch_number text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="employee_job_title_id" class="form-label">Job Title <span class="text-danger">*</span></label>
                            <select id="employee_job_title_id" class="lccTom lcc-tom-select w-full" name="employee_job_title_id">
                                <option value="" selected>Please Select</option>
                                @if($employeeJobTitles->count() > 0)
                                    @foreach($employeeJobTitles as $si)
                                        <option {{ isset($employment->employee_job_title_id) && $employment->employee_job_title_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_job_title_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select id="department_id" class="lccTom lcc-tom-select w-full" name="department_id">
                                <option value="" selected>Please Select</option>
                                @if($departments->count() > 0)
                                    @foreach($departments as $si)
                                        <option {{ isset($employment->department_id) && $employment->department_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-department_id text-danger mt-2"></div>
                        </div>
                        
                        <div class="col-span-12 sm:col-span-4">
                            <label for="office_telephone" class="form-label">Office Telephone</label>
                            <input type="text" value="{{ isset($employment->office_telephone) ? $employment->office_telephone : '' }}" id="office_telephone" class="form-control" name="office_telephone"  >
                            <div class="acc__input-error error-office_telephone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" value="{{ isset($employment->mobile) ? $employee->mobile : '' }}" id="mobile" class="form-control" name="mobile"  >
                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 disabled" >
                            <label for="email" class="form-label">Email (username) <span class="text-danger">*</span></label>
                            <input disabled type="text" value="{{ isset($employee->user->email) ? $employee->user->email : '' }}" id="email" class="form-control"  >
                            <input type="hidden" value="{{ isset($employee->user->email) ? $employee->user->email : '' }}" name="email"/>
                        </div>

                        <div class="col-span-12 sm:col-span-4">
                            <label for="site_location[]" class="form-label">Site locations: <span class="text-danger">*</span></label>
                            <select id="site_location[]" class="lccTom lcc-tom-select w-full" name="site_location[]" multiple>
                                @if($venues->count() > 0)
                                    @foreach($venues as $si)
                                        <option {{ in_array($si->id,$employmentVenue) ? "selected" : "" }}  value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-site_location text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Update                      
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
                    <input type="hidden" value="{{ isset($employment->id) ? $employment->id : 0 }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Employment Details Modal -->

<!-- BEGIN: Edit Eligibilites  Details Modal -->
<div id="editEligibilitesDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editEligibilitesDetailsForm" enctype="multipart/form-data">
            <input type="hidden" name="url" value="{{ route('employeeeligibility.update',$employeeEligibilites->id) }}" />
            <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Eligibilites Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-3">
                            <label for="eligible_to_work_status" class="form-label">Do this person is eligible to work in UK?</label>
                            <div class="form-check form-switch">
                                <input {{ (isset($employeeEligibilites->eligible_to_work) && $employeeEligibilites->eligible_to_work == "Yes") ? "checked" : '' }} id="eligible_to_work_status" class="form-check-input" name="eligible_to_work_status" value="Yes" type="checkbox">
                                <label class="form-check-label" for="eligible_to_work">&nbsp;</label>
                            </div>
                        </div>
    
                        <div class="workPermitTypeFields intro-y col-span-12 sm:col-span-3" style="{{ ($employeeEligibilites->eligible_to_work == 'Yes') ? '' : 'display: none;' }}">
                            <label for="workpermit_type" class="form-label inline-flex">Type <span class="text-danger">*</span></label>
                            <select id="workpermit_type" name="workpermit_type" class=" w-full lcc-tom-select">
                                <option value="" selected>Please Select</option>
                                @foreach($workPermitTypes as $workPermitType)
                                    <option {{ (isset($employeeEligibilites->employee_work_permit_type_id) && $employeeEligibilites->employee_work_permit_type_id == $workPermitType->id) ? "selected" : "" }} value="{{ $workPermitType->id }}">{{ $workPermitType->name }}</option>       
                                @endforeach
                            </select> 
                            <div class="acc__input-error error-workpermit_type text-danger mt-2"></div>
                        </div>
                        <div class="workPermitFields intro-y col-span-12 sm:col-span-3" style="{{ (isset($employeeEligibilites->employee_work_permit_type_id) && $employeeEligibilites->employee_work_permit_type_id == 3) ? '' : 'display: none;' }}">
                            <label for="workpermit_number" class="form-label inline-flex">Work Permit Number </label>
                            <input id="workpermit_number" type="text" value="{{ isset($employeeEligibilites->workpermit_number) ? $employeeEligibilites->workpermit_number : '' }}" class="form-control rounded-none form-control-lg"  name="workpermit_number" aria-label="default input example">
                            <div class="acc__input-error error-workpermit_number text-danger mt-2"></div>
                        </div>              
                        <div class="workPermitFields intro-y col-span-12 sm:col-span-3" style="{{ (isset($employeeEligibilites->employee_work_permit_type_id) && $employeeEligibilites->employee_work_permit_type_id == 3) ? '' : 'display: none;' }}">
                            <label for="workpermit_expire" class="form-label inline-flex">Work Permit Expiry Date </label>
                            <input id="workpermit_expire" type="text" value="{{ isset($employeeEligibilites->workpermit_expire) ? $employeeEligibilites->workpermit_expire : '' }}" placeholder="DD-MM-YYYY" class="form-control form-control-lg datepicker rounded-none" name="workpermit_expire" data-format="DD-MM-YYYY" data-single-mode="true">                   
                            <div class="acc__input-error error-workpermit_expire text-danger mt-2"></div>
                        </div>   
                        
                        <div class="intro-y col-span-12">
                            <div class="grid grid-cols-12 gap-x-4">
                                <div class="intro-y col-span-12 sm:col-span-6 py-1"> <!-- checkbox for yes/no -->
                                    <label for="document_type" class="form-label inline-flex">Document Type <span class="text-danger"> *</span></label>
                                    <select id="document_type" name="document_type" class="form-control lccTom lcc-tom-select">
                                        <option value="" selected>Please Select</option>
                                        @if($documentTypes->count() > 0)
                                            @foreach($documentTypes as $documentType)
                                            <option {{ isset($employeeEligibilites->employeeDocType->id) && $employeeEligibilites->employeeDocType->id == $documentType->id ? 'Selected' : '' }} value="{{ $documentType->id }}">{{ $documentType->name }}</option>

                                            {{-- <option  value="{{ $documentType->id }}">{{ $documentType->name }}</option>               --}}
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-document_type text-danger mt-2"></div>
                                </div>
    
                                <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                    <label for="vertical-form-4" class="form-label inline-flex">Document Number <span class="text-danger"> *</span></label>
                                    <input id="vertical-form-4" type="text" name="doc_number" value="{{ isset($employeeEligibilites->doc_number	) ? $employeeEligibilites->doc_number	 : '' }}" class="w-full text-sm" />
                                    <div class="acc__input-error error-doc_number text-danger mt-2"></div>
                                </div>
        
                                <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                    <label for="vertical-form-5" class="form-label inline-flex">Document Expiry Date <span class="text-danger"> *</span></label>
                                    <input id="vertical-form-5" type="text" placeholder="DD-MM-YYYY" id="doc_expire" value="{{ isset($employeeEligibilites->doc_expire) ? $employeeEligibilites->doc_expire : '' }}" class="form-control  datepicker rounded-none" name="doc_expire" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-doc_expire text-danger mt-2"></div>
                                </div>
        
                                <div class="intro-y col-span-12 sm:col-span-6 py-1">
                                    <label for="vertical-form-6" class="form-label inline-flex">Document Issue Country <span class="text-danger"> *</span></label>
                                    <select id="vertical-form-6" name="doc_issue_country" class="form-control w-full">
                                        <option value="" selected>Please Select</option>
                                        @if($country->count() > 0)
                                            @foreach($country as $countries)
                                                <option {{ isset($employeeEligibilites->docIssueCountry->id) && $employeeEligibilites->docIssueCountry->id == $countries->id ? 'Selected' : '' }} value="{{ $countries->id }}">{{ $countries->name }}</option>
                                                {{-- <option  value="{{ $countries->id }}">{{ $countries->name }}</option>               --}}
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-doc_issue_country text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Update                      
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
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Eligibilites Details Modal -->

<!-- BEGIN: Edit Emergency Contact Details Modal -->
<div id="editEmergencyContactDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editEmergencyContactDetailsForm" enctype="multipart/form-data">
            
            <input type="hidden" name="url" value="{{ route('employee.emergency.update',$emergencyContacts->id) }}" />
            <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Emergency Contact Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="emergency_contact_name" class="form-label inline-flex">Name <span class="text-danger">*</span></label>
                            <input id="emergency_contact_name" type="text" value="{{ isset($emergencyContacts->emergency_contact_name) ? $emergencyContacts->emergency_contact_name : '' }}" class="form-control rounded-none form-control-lg inputUppercase"  name="emergency_contact_name" aria-label="default input example">
                            <div class="acc__input-error error-emergency_contact_name text-danger mt-2"></div>
                        </div>              
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="kins_relation_id" class="form-label inline-flex">Relationship <span class="text-danger">*</span></label>
                            <select id="kins_relation_id" name="kins_relation_id" class="form-control lccTom lcc-tom-select">
                                <option value="">Please Select</option>
                                @foreach($relation as $kins)
                                    <option {{ (isset($emergencyContacts->kins_relation_id) && $emergencyContacts->kins_relation_id == $kins->id ? 'selected' : '') }} value="{{ $kins->id }}">{{ $kins->name }}</option>              
                                @endforeach
                            </select>
                            <div class="acc__input-error error-kins_relation_id text-danger mt-2"></div>
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

                        @php
                            $emc_address_id = (isset($emergencyContacts->address_id) && $emergencyContacts->address_id > 0 ? $emergencyContacts->address_id : 0);
                            $address3 = '';
                            if(isset($emergencyContacts->address->address_line_1) && !empty($emergencyContacts->address->address_line_1)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$emergencyContacts->address->address_line_1.'</span><br/>';
                            endif;
                            if(isset($emergencyContacts->address->address_line_2) && !empty($emergencyContacts->address->address_line_2)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$emergencyContacts->address->address_line_2.'</span><br/>';
                            endif;
                            if(isset($emergencyContacts->address->city) && !empty($emergencyContacts->address->city)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$emergencyContacts->address->city.'</span>, ';
                            endif;
                            if(isset($emergencyContacts->address->post_code) && !empty($emergencyContacts->address->post_code)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$emergencyContacts->address->post_code.'</span>,<br/>';
                            endif;
                            if(isset($emergencyContacts->address->country) && !empty($emergencyContacts->address->country)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.strtoupper($emergencyContacts->address->country).'</span><br/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="emcAddressWrap">
                            <div class="addresses mb-2">
                                @if($emc_address_id > 0)
                                    {!! $address3 !!}
                                @else 
                                    <span class="text-warning font-medium">Not set yet!</span>
                                @endif
                            </div>
                            <div>
                                <button  data-id="{{ $emc_address_id }}" data-type="emc" type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ $emc_address_id > 0 ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6"></div>

                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="emergency_contact_telephone" class="form-label inline-flex">Telephone </label>
                            <input id="emergency_contact_telephone" type="text" value="{{ isset($emergencyContacts->emergency_contact_telephone) ? $emergencyContacts->emergency_contact_telephone : '' }}" class="form-control rounded-none form-control-lg" name="emergency_contact_telephone" aria-label="default input example">
                                            
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="emergency_contact_mobile" class="form-label inline-flex">Mobile <span class="text-danger"> *</span></label>
                            <input id="emergency_contact_mobile" type="text" value="{{ isset($emergencyContacts->emergency_contact_mobile) ? $emergencyContacts->emergency_contact_mobile : '' }}" class="form-control rounded-none form-control-lg" name="emergency_contact_mobile" aria-label="default input example">
                            <div class="acc__input-error error-emergency_contact_mobile text-danger mt-2"></div>
                        </div>
        
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="emergency_contact_email" class="form-label inline-flex">Email </label>
                            <input id="emergency_contact_email" type="text" value="{{ isset($emergencyContacts->emergency_contact_email) ? $emergencyContacts->emergency_contact_email : '' }}" name="emergency_contact_email" class="form-control rounded-none form-control-lg" aria-label="default input example">
                            
                        </div>        
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Update                      
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
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Emergency Contact Details Modal -->

<!-- BEGIN: Edit Terms Details Modal -->
<div id="editTermDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editTermDetailsForm" enctype="multipart/form-data">
            
            <input type="hidden" name="url" value="{{ route('employee.term.update', $employeeTerms->id) }}" />
            <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Term Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="notice-period" class="form-label inline-flex">Notice Period  <span class="text-danger"> *</span></label>
                            <select id="notice-period" name="employee_notice_period_id" class="form-control lccTom lcc-tom-select">
                                <option value="" selected>Please Select</option>
                                @if($noticePeriods->count() > 0)
                                    @foreach($noticePeriods as $noticePeriod)
                                        <option {{ isset($employeeTerms->employee_notice_period_id) && $employeeTerms->employee_notice_period_id == $noticePeriod->id ? 'Selected' : '' }} value="{{ $noticePeriod->id }}">{{ $noticePeriod->name }}</option>
                                        {{-- <option  value="{{ $noticePeriod->id }}">{{ $noticePeriod->name }}</option>               --}}
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_notice_period_id text-danger mt-2"></div>
        
                        </div> 

                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="employment-period" class="form-label inline-flex employment-period">Period of Employment  <span class="text-danger"> *</span></label>
                            <select id="employment-period" name="employment_period_id" class="form-control lccTom lcc-tom-select">
                                <option value="" selected>Please Select</option>
                                @if($employmentPeriods->count() > 0)
                                    @foreach($employmentPeriods as $employmentPeriod)
                                        <option {{ isset($employeeTerms->employment_period_id) && $employeeTerms->employment_period_id == $employmentPeriod->id ? 'Selected' : '' }} value="{{ $employmentPeriod->id }}">{{ $employmentPeriod->name }}</option>
                                        {{-- <option  value="{{ $employmentPeriod->id }}">{{ $employmentPeriod->name }}</option>               --}}
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employment_period_id text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="ssp-term" class="form-label inline-flex employment-period">SSP Terms & Conditions   <span class="text-danger"> *</span></label>
                            <select id="ssp-term" name="employment_ssp_term_id" class="form-control lccTom lcc-tom-select">
                                <option value="" selected>Please Select</option>
                                @if($sspTerms->count() > 0)
                                    @foreach($sspTerms as $sspterm)
                                        <option {{ isset($employeeTerms->employment_ssp_term_id) && $employeeTerms->employment_ssp_term_id == $sspterm->id ? 'Selected' : '' }} value="{{ $sspterm->id }}">{{ $sspterm->name }}</option>
                                        
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employment_ssp_term_id text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Update                      
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
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Terms Details Modal -->

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
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full uppercase inputUppercase" name="address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full uppercase inputUppercase" name="address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full uppercase inputUppercase" name="city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="form-control w-full uppercase inputUppercase" name="post_code">
                            <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full uppercase inputUppercase" name="country">
                            <div class="acc__input-error error-student_address_country text-danger mt-2"></div>
                        </div>
                        <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key={{ $postcodeApi }}" />
                        <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key={{ $postcodeApi }}"></script>
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
                    <input type="hidden" name="address_id" value="0"/>
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="type" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->

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
                </div>
            </div>
        </div>
    </div>
<!-- END: Success Modal Content -->