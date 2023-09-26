@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
        
        <div class="ml-auto flex justify-end">
            <button type="button" class="btn btn-success text-white w-auto mr-1 mb-0">
                {{ $student->status->name }}
            </button>
            <!-- <a style="float: right;" href="{{ route('applicantprofile.print',$student->id) }}" data-id="{{ $student->id }}" class="btn btn-success text-white w-auto">Download Pdf</a> -->
            <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
        </div>
        
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Personal Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-8 font-medium">{{ $student->date_of_birth }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Gender</div>
                        <div class="col-span-8 font-medium">{{ $student->gender }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-8 font-medium">{{ $student->nation->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Country of Birth</div>
                        <div class="col-span-8 font-medium">{{ $student->country->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-8 font-medium">{{ isset($student->other->ethnicity->name) ? $student->other->ethnicity->name : '' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5  mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Student Other Personal Information</div>
                </div>
                
                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editOtherPersonalInfoModal" type="button" class="editOtherInfo btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Other Info
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Sexual Orientation</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->otherPerInfo->sexori->name) && !empty($student->otherPerInfo->sexori->name) ? $student->otherPerInfo->sexori->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Gender Identity</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->otherPerInfo->gender->name) && !empty($student->otherPerInfo->gender->name) ? $student->otherPerInfo->gender->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Religion or Belief</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->otherPerInfo->religion->name) && !empty($student->otherPerInfo->religion->name) ? $student->otherPerInfo->religion->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Disability Status</div>
                        <div class="col-span-8 font-medium">
                            {!! (isset($student->other->disability_status) && $student->other->disability_status == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                        </div>
                    </div>
                </div>
                @if(isset($student->other->disability_status) && $student->other->disability_status == 1)
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-500 font-medium">Disabilities</div>
                            <div class="col-span-12 font-medium">
                                @if(isset($student->disability) && !empty($student->disability))
                                    <ul class="m-0 p-0">
                                        @foreach($student->disability as $dis)
                                            <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->disabilities->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                            <div class="col-span-8 font-medium">
                                {!! (isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="intro-y box p-5  mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Student Other Identifications</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editOtherItentificationModal" type="button" class="editOtherIdentification btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Identification
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Application Ref. No</div>
                        <div class="col-span-8 font-medium">{{ $student->application_no }} {{ isset($student->submission_date) && !empty($student->submission_date) ? '('.$student->submission_date.')' : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">SSN</div>
                        <div class="col-span-8 font-medium">{{ isset($student->ssn_no) && !empty($student->ssn_no) ? $student->ssn_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">UHN Number</div>
                        <div class="col-span-8 font-medium">{{ isset($student->uhn_no) && !empty($student->uhn_no) ? $student->uhn_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">DF SID Number</div>
                        <div class="col-span-8 font-medium">{{ isset($student->registration_no) && !empty($student->registration_no) ? $student->registration_no : '---' }}</div>
                    </div>
                </div>
            </div>

            <div class="font-medium text-base mt-5 pt-5">Proof Of ID Checks</div>
            <div class="mt-2 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div id="tabulatorFilterForm-PIC" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-PIC" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-PIC" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-PIC" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-PIC" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </div>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-PIC" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto mr-2">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-json-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-html-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addProoOfIdCheckModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Proof Of ID
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="studentProofOfIdCheckTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Contact Details</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Contact Details
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-12 text-slate-500 font-medium mb-1">Term Time / Correspondence Address</div>
                        <div class="col-span-12 font-medium pl-5">
                            @if(isset($student->contact->address_line_1) && !empty($student->contact->address_line_1))
                                <span class="font-medium">{{ $student->contact->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($student->contact->address_line_2) && !empty($student->contact->address_line_2))
                                <span class="font-medium">{{ $student->contact->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($student->contact->city) && !empty($student->contact->city))
                                <span class="font-medium">{{ $student->contact->city }}</span>,
                            @endif
                            @if(isset($student->contact->state) && !empty($student->contact->state))
                                <span class="font-medium">{{ $student->contact->state }}</span>, <br/>
                            @endif
                            @if(isset($student->contact->post_code) && !empty($student->contact->post_code))
                                <span class="font-medium">{{ $student->contact->post_code }}</span>,
                            @endif
                            @if(isset($student->contact->country) && !empty($student->contact->country))
                                <span class="font-medium">{{ $student->contact->country }}</span><br/>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Term Time Accomodation Type</div>
                        <div class="col-span-8 font-medium">---</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Term Time Address Postcode</div>
                        <div class="col-span-8 font-medium">---</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-12 text-slate-500 font-medium">Permanent Address</div>
                        <div class="col-span-12 font-medium pl-5">
                            <span class="font-medium text-danger">Not set yet!</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Permanent Country codee</div>
                        <div class="col-span-8 font-medium">---</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Personal Email</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->users->email }}
                            @if ($student->users->email_verified_at == NULL)
                                <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                            @else
                                @if(isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && (isset($tempEmail->status) && $tempEmail->status == 'Pending'))
                                    <span class="btn inline-flex btn-warning px-2 ml-2 py-0 text-white rounded-0">Awaiting Verification</span><br/>
                                    <span>({{ $tempEmail->email }})</span>
                                @else
                                    <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Institutional Email</div>
                        <div class="col-span-8 font-medium">---</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Home Phone</div>
                        <div class="col-span-8 font-medium">{{ $student->contact->home }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->contact->mobile }}
                            @if($student->contact->mobile_verification == 1)
                                <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                            @else
                                <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Next of Kin</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Next of Kin
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->name) ? $student->kin->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->relation->name) ? $student->kin->relation->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->kin->email) && !empty($student->kin->email) ? $student->kin->email : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($student->kin->address_line_1) && !empty($student->kin->address_line_1))
                                <span class="font-medium">{{ $student->kin->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($student->kin->address_line_2) && !empty($student->kin->address_line_2))
                                <span class="font-medium">{{ $student->kin->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($student->kin->city) && !empty($student->kin->city))
                                <span class="font-medium">{{ $student->kin->city }}</span>,
                            @endif
                            @if(isset($student->kin->state) && !empty($student->kin->state))
                                <span class="font-medium">{{ $student->kin->state }}</span>, <br/>
                            @endif
                            @if(isset($student->kin->post_code) && !empty($student->kin->post_code))
                                <span class="font-medium">{{ $student->kin->post_code }}</span>,
                            @endif
                            @if(isset($student->kin->country) && !empty($student->kin->country))
                                <span class="font-medium">{{ $student->kin->country }}</span><br/>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5" id="applicantQualification">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Education Qualification</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentQualStatusModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Status
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12 mb-2">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-3 text-slate-500 font-medium">Student have any formal academic qualification?</div>
                        <div class="col-span-8 font-medium">{!! (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                <div class="col-span-12 educationQualificationTableWrap" style="display: {{ isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 ? 'block' : 'none' }};">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div id="tabulatorFilterForm-SEQ" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-SEQ" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-SEQ" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-SEQ" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-SEQ" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </div>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-SEQ" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto mr-2">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-json-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-html-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Qualification
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="studentEducationQualTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator {{ isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
            </div>
        </div>

        @php 
            if(!isset($student->other->employment_status) || ($student->other->employment_status == 'Unemployed' || $student->other->employment_status == 'Contractor' || $student->other->employment_status == 'Consultant' || $student->other->employment_status == 'Office Holder')):
                $emptStatus = false;
            else:
                $emptStatus = true;
            endif;
        @endphp
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Empoyment History</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentEmpStatusModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Status
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-12 mb-2">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-3 text-slate-500 font-medium">Student current employment status</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->other->employment_status) && $student->other->employment_status != '' ? $student->other->employment_status : $student->other->employment_status ) }}</div>
                    </div>
                </div>
                <div class="col-span-12 educationEmploymentTableWrap" style="display: {{ $emptStatus ? 'block' : 'none' }};">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div id="tabulatorFilterForm-SEH" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-SEH" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-SEH" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-SEH" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-SEH" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </div>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-SEH" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-json-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-html-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="btn btn-primary w-auto ml-2 mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Employement History
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="studentEmploymentHistoryTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator {{ $emptStatus ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Others</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentConsentModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Consent
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Student Consent</div>
                        <div class="col-span-8"> 
                            @if(!empty($stdConsentIds) && $consent->count() > 0)
                                <ul class="m-0 p-0 mb-2">
                                    @foreach($consent as $con)
                                        @if(in_array($con->id, $stdConsentIds))
                                        <li class="text-left font-normal mb-3 pl-6 relative">
                                            <i data-lucide="check-circle" class="w-4 h-4 text-success absolute" style="left: 0; top: 4px;"></i>
                                            <div class="font-medium text-base">{{ $con->name }}</div>
                                            <div class="pt-1">{{ $con->description }}</div>
                                        </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else 
                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Student consent not set yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if(isset($student->referral_code) && !empty($student->referral_code) && isset($student->is_referral_varified) && $student->is_referral_varified == 1)
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Referred By</div>
                        <div class="col-span-8 font-medium">
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Code</div>
                                <div class="font-medium">{{ $referral->code }}</div>
                            </div>
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Type</div>
                                <div class="font-medium">{{ $referral->type }}</div>
                            </div>
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Referrer</div>
                                <div class="font-medium">
                                    @if($referral->type == 'Student')
                                        <span>{{ $referral->student->frist_name }} {{ $referral->student->last_naem }}</span><br/>
                                        <span>{{ $referral->student->users->email }}</span><br/>
                                        <span>{{ $referral->student->contact->mobile }}</span>
                                    @else 
                                        <span>{{ $referral->user->name }}</span><br/>
                                        <span>{{ $referral->user->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('pages.students.live.show-modals')

@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-profile.js')
    @vite('resources/js/student-proof-id-check.js')
    @vite('resources/js/student-edication-qualification.js')
    @vite('resources/js/student-employment-history.js')
    @vite('resources/js/student-consent.js')
@endsection