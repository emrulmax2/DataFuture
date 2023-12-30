@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Personal Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium uppercase">{{ $employee->title->name.' '.$employee->full_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->date_of_birth) && !empty($employee->date_of_birth) ? date('jS M, Y', strtotime($employee->date_of_birth)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Age</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->age) ? $employee->age: '') }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Sex Identifier/Gender</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->sex->name) ? $employee->sex->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->nationality->name) ? $employee->nationality->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->ethnicity->name) ? $employee->ethnicity->name : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">NI Number</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->ni_number) ? $employee->ni_number : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Is this employee has disabilites?</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->disability_status) ? $employee->disability_status : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Car Reg Number</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->car_reg_number	) ? $employee->car_reg_number	 : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Driving License</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->drive_license_number) ? $employee->drive_license_number : '' }}</div>
                    </div>
                </div>
              
                @if(isset($employee->disability_status) && $employee->disability_status == "Yes")
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-500 font-medium mb-2">Disabilities :</div>
                            <div class="col-span-12 font-medium">
                                @if(isset($employee->disability) && !empty($employee->disability))
                                    <ul class="m-0 p-0"> 
                                        @foreach($employee->disability as $dis)
                                            <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employment</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editEmploymentDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Employment Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 

                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Started on</div>
                        <div class="col-span-8 font-medium">{{ (isset($employment->started_on) && !empty($employment->started_on) ? date('jS M, Y', strtotime($employment->started_on)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Employee type</div>
                        <div class="col-span-8 font-medium">{{ (isset($employment->employeeWorkType->name) ? $employment->employeeWorkType->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Punch number</div>
                        <div class="col-span-8 font-medium">{{ isset($employment->punch_number) ? $employment->punch_number : '' }}</div>
                    </div>
                </div>
                @if($employment->employeeWorkType->name == "Employee")
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Works number</div>
                        <div class="col-span-8 font-medium">{{ (isset($employment->works_number) && !empty($employment->works_number) ? $employment->works_number : '') }}</div>
                    </div>
                </div>
                @else
                    <div class="col-span-12 sm:col-span-3"></div>
                @endif
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Job Title </div>
                        <div class="col-span-8 font-medium">{{ isset($employment->employeeJobTitle->name) ? $employment->employeeJobTitle->name : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Department </div>
                        <div class="col-span-8 font-medium">{{ isset($employment->department->name) ? $employment->department->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Site locations </div>
                        <div class="col-span-8 font-medium">
                            <ul class="m-0 p-0"> 
                                @foreach($employee->venues as $dis)
                                    <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Office telephone</div>
                        <div class="col-span-8 font-medium">{{ isset($employment->office_telephone	) ? $employment->office_telephone	 : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">{{ isset($employment->mobile) ? $employment->mobile : '' }}</div>
                    </div>
                </div>
                
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email (username)</div>
                        <div class="col-span-8 font-medium">{{ isset($employee->user->email) ? $employee->user->email : '' }}</div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Eligibilites</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editEligibilitesDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Eligibility Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-6">
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Eligible To Work</div>
                        <div class="col-span-8 font-medium">{{ isset($employeeEligibilites->eligible_to_work) ? $employeeEligibilites->eligible_to_work : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Employee Work Permit: </div>
                        <div class="col-span-8 font-medium">{{ (isset($employeeEligibilites->employeeWorkPermitType->name) && !empty($employeeEligibilites->employeeWorkPermitType->name) ? $employeeEligibilites->employeeWorkPermitType->name : '') }}</div>
                    </div>
                    @if(isset($employeeEligibilites->employeeWorkPermitType->name) && $employeeEligibilites->employeeWorkPermitType->name != "British Citizen")
                        <div class="grid grid-cols-12 gap-0 mb-4">
                            <div class="col-span-4 text-slate-500 font-medium">Workpermit Number</div>
                            <div class="col-span-8 font-medium">{{ (isset($employeeEligibilites->workpermit_number) ? $employeeEligibilites->workpermit_number : '') }}</div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 mb-4">
                            <div class="col-span-4 text-slate-500 font-medium">Workpermit Expire</div>
                            <div class="col-span-8 font-medium">{{ (isset($employeeEligibilites->workpermit_expire) && !empty($employeeEligibilites->workpermit_expire) ? date('jS M, Y', strtotime($employeeEligibilites->workpermit_expire)) : '') }}</div>
                        </div>
                    @endif
                </div>
                <div class="col-span-6">
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Proof of ID Type</div>
                        <div class="col-span-8 font-medium">{{ isset($employeeEligibilites->employeeDocType->name) ? $employeeEligibilites->employeeDocType->name	 : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">ID Number</div>
                        <div class="col-span-8 font-medium">{{ isset($employeeEligibilites->doc_number	) ? $employeeEligibilites->doc_number	 : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Expiry Date</div>
                        <div class="col-span-8 font-medium">{{ isset($employeeEligibilites->doc_expire) && !empty($employeeEligibilites->doc_expire) ? date('jS M, Y', strtotime($employeeEligibilites->doc_expire)) : '' }}</div>
                    </div>
                    
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Issuing Country</div>
                        <div class="col-span-8 font-medium">{{ isset($employeeEligibilites->docIssueCountry->name) ? $employeeEligibilites->docIssueCountry->name : '' }}</div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Emergency Contacts</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editEmergencyContactDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Emergency Contacts Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-6">
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ (isset($emergencyContacts->emergency_contact_name) ? $emergencyContacts->emergency_contact_name : '') }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                        <div class="col-span-8 font-medium">{{ (isset($emergencyContacts->kin->name) && !empty($emergencyContacts->kin->name) ? $emergencyContacts->kin->name : '') }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Telephone</div>
                        <div class="col-span-8 font-medium">{{ (isset($emergencyContacts->emergency_contact_telephone) ? $emergencyContacts->emergency_contact_telephone : '') }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_mobile) ? $emergencyContacts->emergency_contact_mobile : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Email </div>
                        <div class="col-span-8 font-medium">{{ isset($emergencyContacts->emergency_contact_email) ? $emergencyContacts->emergency_contact_email : '' }}</div>
                    </div>
                </div>
                <div class="col-span-6">
                    <div class="col-span-12">
                            <div class="flex flex-col justify-center items-center lg:items-start">
                                <div class="truncate sm:whitespace-normal flex items-start">
                                    <i data-lucide="map-pin" class="w-4 h-4 mr-2" style="padding-top: 3px;"></i> 
                                    <span class="uppercase">
                                        @if(isset($emergencyContacts->address->address_line_1) && $emergencyContacts->address->address_line_1 > 0)
                                            @if(isset($emergencyContacts->address->address_line_1) && !empty($emergencyContacts->address->address_line_1))
                                                <span class="font-medium">{{ $emergencyContacts->address->address_line_1 }}</span><br/>
                                            @endif
                                            @if(isset($emergencyContacts->address->address_line_2) && !empty($emergencyContacts->address->address_line_2))
                                                <span class="font-medium">{{ $emergencyContacts->address->address_line_2 }}</span><br/>
                                            @endif
                                            @if(isset($emergencyContacts->address->city) && !empty($emergencyContacts->address->city))
                                                <span class="font-medium">{{ $emergencyContacts->address->city }}</span>,
                                            @endif
                                            @if(isset($emergencyContacts->address->state) && !empty($emergencyContacts->address->state))
                                                <span class="font-medium">{{ $emergencyContacts->address->state }}</span>,
                                            @endif
                                            @if(isset($emergencyContacts->address->post_code) && !empty($emergencyContacts->address->post_code))
                                                <span class="font-medium">{{ $emergencyContacts->address->post_code }}</span>,<br/>
                                            @endif
                                            @if(isset($employee->address->country) && !empty($emergencyContacts->address->country))
                                                <span class="font-medium">{{ $emergencyContacts->address->country }}</span><br/>
                                            @endif
                                        @else 
                                            <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employment Terms</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editTermDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Terms Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Employement Notice Period: </div>
                        <div class="col-span-8 font-medium">{{ (isset($employeeTerms->notice->name) ? 'Employee must give '.$employeeTerms->notice->name.' notice' : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Employement Period: </div>
                        <div class="col-span-8 font-medium">{{ (isset($employeeTerms->period->name) && !empty($employeeTerms->period->name) ? 'This employment is '.$employeeTerms->period->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Employement SSP terms</div>
                        <div class="col-span-8 font-medium">Employee receives {{ (isset($employeeTerms->SSP->name) ? $employeeTerms->SSP->name : '') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.employee.profile.show-modals')

@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/employee-profile.js')
@endsection