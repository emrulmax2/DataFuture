@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-4 sm:p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-5 md:col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>

                <div class="col-span-7 md:col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Personal Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-6 md:col-span-4 font-medium">{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->date_of_birth) && !empty($student->date_of_birth) ? date('jS M, Y', strtotime($student->date_of_birth)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Sex Identifier/Gender</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->sexid->name) ? $student->sexid->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ $student->nation->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Country of Birth</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->country) ? $student->country->name : "" }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->other->ethnicity->name) ? $student->other->ethnicity->name : '' }}</div>
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
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Other Info
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Sexual Orientation</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->other->sexori->name) && !empty($student->other->sexori->name) ? $student->other->sexori->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Gender Identity</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->other->gender->name) && !empty($student->other->gender->name) ? $student->other->gender->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Religion or Belief</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->other->religion->name) && !empty($student->other->religion->name) ? $student->other->religion->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Disability Status</div>
                        <div class="col-span-6 md:col-span-8 font-medium">
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
                            <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                            <div class="col-span-6 md:col-span-8 font-medium">
                                {!! (isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="intro-y box p-5  mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-5 sm:col-span-6">
                    <div class="font-medium text-base">Student Other Identifications</div>
                </div>

                <div class="col-span-7 sm:col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editOtherItentificationModal" type="button" class="editOtherIdentification btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Identification
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Application Ref. No</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ $student->application_no }} {{ isset($student->submission_date) && !empty($student->submission_date) ? '('.$student->submission_date.')' : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">SSN</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->ssn_no) && !empty($student->ssn_no) ? $student->ssn_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">UHN Number</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->uhn_no) && !empty($student->uhn_no) ? $student->uhn_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">DF SID Number</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->registration_no) && !empty($student->registration_no) ? $student->registration_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Study Modes</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->other->mode->name) && !empty($student->other->mode->name) ? $student->other->mode->name : '---' }}</div>
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
                        <div class="flex justify-end mt-5 sm:mt-0">
                            <button id="tabulator-print-PIC" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2 hidden md:inline-flex">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto hidden md:inline-flex mr-2">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto hidden md:inline-flex" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-PIC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
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
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-center">
                <div class="xl:flex sm:mr-auto">
                    <div class="font-medium text-base">Contact Details</div>
                </div>
                <div class="flex justify-between mt-5 sm:mt-0">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="btn btn-primary mr-2">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Contact Details
                    </button>
                    <div class="dropdown w-10">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="grip" class="w-4 h-4"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv" data-tw-toggle="modal" data-tw-target="#confirmPersonalEmailUpdateModal" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="mail-question" class="w-4 h-4 mr-2"></i> Change Email
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx" href="javascript:;"  data-tw-toggle="modal" data-tw-target="#confirmPersonalMobileUpdateModal" class="dropdown-item">
                                        <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> Change Mobile
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-12 text-slate-500 font-medium mb-2">Term Time / Correspondence Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                    <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span><br/>
                                @endif
                                @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                    <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span><br/>
                                @endif
                                @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                    <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                    <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                                @endif
                                @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                    <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                    <span class="font-medium">{{ $student->contact->termaddress->country }}</span><br/>
                                @endif
                            @else 
                                <span class="font-medium text-warning">Not Set Yet!</span><br/>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Term Time Acco. Type</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->contact->ttacom->name) && !empty($student->contact->ttacom->name) ? $student->contact->ttacom->name : '---') }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Term Time Address Postcode</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->contact->term_time_post_code) && !empty($student->contact->term_time_post_code) ? $student->contact->term_time_post_code : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-12 text-slate-500 font-medium mb-2">Permanent Address</div>
                        <div class="col-span-6 md:col-span-12 font-medium">
                            @if(isset($student->contact->permanent_address_id) && $student->contact->permanent_address_id > 0)
                                @if(isset($student->contact->permaddress->address_line_1) && !empty($student->contact->permaddress->address_line_1))
                                    <span class="font-medium">{{ $student->contact->permaddress->address_line_1 }}</span><br/>
                                @endif
                                @if(isset($student->contact->permaddress->address_line_2) && !empty($student->contact->permaddress->address_line_2))
                                    <span class="font-medium">{{ $student->contact->permaddress->address_line_2 }}</span><br/>
                                @endif
                                @if(isset($student->contact->permaddress->city) && !empty($student->contact->permaddress->city))
                                    <span class="font-medium">{{ $student->contact->permaddress->city }}</span>,
                                @endif
                                @if(isset($student->contact->permaddress->state) && !empty($student->contact->permaddress->state))
                                    <span class="font-medium">{{ $student->contact->permaddress->state }}</span>, <br/>
                                @endif
                                @if(isset($student->contact->permaddress->post_code) && !empty($student->contact->permaddress->post_code))
                                    <span class="font-medium">{{ $student->contact->permaddress->post_code }}</span>,
                                @endif
                                @if(isset($student->contact->permaddress->country) && !empty($student->contact->permaddress->country))
                                    <span class="font-medium">{{ $student->contact->permaddress->country }}</span><br/>
                                @endif
                            @else 
                                <span class="font-medium text-warning">Not Set Yet!</span><br/>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Permanent Country code</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->contact->pcountry->name) && !empty($student->contact->pcountry->name) ? $student->contact->pcountry->name : '---') }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Permanent Post code</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ (isset($student->contact->permanent_post_code) && !empty($student->contact->permanent_post_code) ? $student->contact->permanent_post_code : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Login Email</div>
                        <div class="col-span-6 md:col-span-8 font-medium">
                            {{ $student->users->email }} 
                            {{--@if ($student->users->email_verified_at == NULL)
                                <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                            @else
                                <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                            @endif--}}
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Personal Email</div>
                        <div class="col-span-6 md:col-span-8 font-medium break-words">
                            {{ $student->contact->personal_email }}
                            @if ($student->contact->personal_email_verification == 0)
                                <span class="btn inline-flex btn-danger px-2 py-0 md:ml-2 text-white rounded-0">Unverified</span>
                            @else
                                <span class="btn inline-flex btn-success px-2 md:ml-2 py-0 text-white rounded-0">Verified</span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Institutional Email</div>
                        <div class="col-span-6 md:col-span-8 font-medium">
                            {{ $student->contact->institutional_email }}
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Home Phone</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ $student->contact->home }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-6 md:col-span-8 font-medium">
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
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Next of Kin
                    </button>

                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->name) ? $student->kin->name : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->relation->name) ? $student->kin->relation->name : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0 mb-3">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium break-words">{{ (isset($student->kin->email) && !empty($student->kin->email) ? $student->kin->email : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 md:col-span-12 text-slate-500 font-medium mb-2">Address</div>
                        <div class="col-span-8 md:col-span-12 font-medium">
                            @if(isset($student->kin->address_id) && $student->kin->address_id > 0)
                                @if(isset($student->kin->address->address_line_1) && !empty($student->kin->address->address_line_1))
                                    <span class="font-medium">{{ $student->kin->address->address_line_1 }}</span><br/>
                                @endif
                                @if(isset($student->kin->address->address_line_2) && !empty($student->kin->address->address_line_2))
                                    <span class="font-medium">{{ $student->kin->address->address_line_2 }}</span><br/>
                                @endif
                                @if(isset($student->kin->address->city) && !empty($student->kin->address->city))
                                    <span class="font-medium">{{ $student->kin->address->city }}</span>,
                                @endif
                                @if(isset($student->kin->address->state) && !empty($student->kin->address->state))
                                    <span class="font-medium">{{ $student->kin->address->state }}</span>, <br/>
                                @endif
                                @if(isset($student->kin->address->post_code) && !empty($student->kin->address->post_code))
                                    <span class="font-medium">{{ $student->kin->address->post_code }}</span>,
                                @endif
                                @if(isset($student->kin->address->country) && !empty($student->kin->address->country))
                                    <br/><span class="font-medium">{{ $student->kin->address->country }}</span>
                                @endif
                            @else 
                                <span class="font-medium text-warning">Not Set Yet!</span><br/>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5" id="applicantQualification">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-7 md:col-span-6">
                    <div class="font-medium text-base">Educational Qualification</div>
                </div>
                <div class="col-span-5 md:col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentQualStatusModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Status
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12 mb-2">
                    <div class="flex md:grid grid-cols-12 gap-0">
                        <div class="col-span-9 md:col-span-3 text-slate-500 font-medium">Student have any formal academic qualification?</div>
                        <div class="col-span-3 md:col-span-8 font-medium">{!! (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
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
                            <button id="tabulator-print-SEQ" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2 hidden md:inline-flex">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto hidden md:inline-flex mr-2">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto hidden md:inline-flex" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-SEQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="btn btn-primary w-auto mr-0 mb-0 ml-auto">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Qualification
                            </button>
                        </div>
                    </div>
                    <div class="">
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
                    <div class="font-medium text-base">Employment History</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentEmpStatusModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Status
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-12 mb-2">
                    <div class="flex md:grid justify-between grid-cols-12 gap-0">
                        <div class="col-span-9 md:col-span-3 text-slate-500 font-medium">Student current employment status</div>
                        <div class="col-span-3 md:col-span-8 font-medium">{{ (isset($student->other->employment_status) && $student->other->employment_status != '' ? $student->other->employment_status : $student->other->employment_status ) }}</div>
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
                        <div class="flex justify-end mt-5 sm:mt-0">
                            <button id="tabulator-print-SEH" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2 hidden md:inline-flex">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto hidden md:block">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto hidden md:inline-flex" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-SEH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
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
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Consent
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 md:col-span-4 text-slate-500 font-medium">Communication Consent</div>
                        <div class="col-span-12 md:col-span-8"> 
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
                    <div class="grid grid-cols-12 gap-4 md:gap-0">
                        <div class="col-span-12 md:col-span-4 text-slate-500 font-medium">Referred By</div>
                        <div class="col-span-12 md:col-span-8 font-medium">
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Code</div>
                                <div class="font-medium">{{ (isset($referral->code) && !empty($referral->code) ? $referral->code : '') }}</div>
                            </div>
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Type</div>
                                <div class="font-medium">{{ (isset($referral->type) ? $referral->type : '') }}</div>
                            </div>
                            <div class="flex justify-start items-start mb-2">
                                <div class="text-slate-500 font-medium mr-3 mw-120">Referrer</div>
                                <div class="font-medium">
                                    @if(isset($referral->type) && $referral->type == 'Student')
                                        <span>{{ $referral->student->first_name }} {{ $referral->student->last_name }}</span><br/>
                                        <span>{{ $referral->student->users->email }}</span><br/>
                                        <span>{{ $referral->student->contact->mobile }}</span>
                                    @elseif(isset($referral->type) && $referral->type == 'Agent')
                                        <span>N/A</span>
                                    @else 
                                        <span>{{ (isset($referral->user->name) ? $referral->user->name : '') }}</span><br/>
                                        <span>{{ (isset($referral->user->email) ? $referral->user->email : '') }}</span>
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
    <!-- @vite('resources/js/address.js') -->
@endsection