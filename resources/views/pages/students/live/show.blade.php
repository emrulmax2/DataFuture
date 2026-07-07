@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="student-profile-show-page intro-y mt-5">
        <div class="intro-y box p-4 sm:p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-5 md:col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>

                <div class="col-span-7 md:col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
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
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Care Leaver</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->other->leaver->name) ? $student->other->leaver->name : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($can_view_other_personal_info) && $can_view_other_personal_info == true)
        <div class="intro-y box p-5  mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Other Personal Information</div>
                </div>

                @if(isset($can_edit_other_personal_info) && $can_edit_other_personal_info == true)
                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editOtherPersonalInfoModal" type="button" class="editOtherInfo btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
                    </button>
                </div>
                @endif
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
        @endif

        @if(isset($can_view_residency_status) && $can_view_residency_status == true)
        <div id="residency-status" class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Residency Status and Criminal Convictions</div>
                </div>
                @if(isset($can_edit_residency_status) && $can_edit_residency_status == true)
                <div class="col-span-6 text-right">
                    <button data-student="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editStudentResidencyCriminalModal" type="button" class="btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
                    </button>
                </div>
                @endif
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-5 text-slate-500 font-medium">Residency Status and Criminal Conviction</div>
                            <div class="col-span-7 font-medium">{{ optional(optional($student->residency)->residencyStatus)->name ?? '---' }}</div>
                        </div>
                    </div>
                    {{-- <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-5 text-slate-500 font-medium">Declaration Accepted</div>
                            <div class="col-span-7 font-medium">
                                {!! (isset($student->criminalConviction->criminal_declaration) && (int) $student->criminalConviction->criminal_declaration === 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                            </div>
                        </div>
                    </div> --}}
                </div>
                <div class="col-span-6">
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-5 text-slate-500 font-medium">Criminal Conviction</div>
                            <div class="col-span-7 font-medium">
                                {!! (isset($student->criminalConviction->have_you_been_convicted) && (int) $student->criminalConviction->have_you_been_convicted === 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : (isset($student->criminalConviction->have_you_been_convicted) ? '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>' : '---')) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-5 text-slate-500 font-medium">Conviction Details</div>
                            <div class="col-span-7 font-medium">{{ isset($student->criminalConviction->criminal_conviction_details) && $student->criminalConviction->criminal_conviction_details != '' ? $student->criminalConviction->criminal_conviction_details : '---' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <div class="intro-y box p-5  mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-5 sm:col-span-6">
                    <div class="font-medium text-base">Identifications</div>
                </div>

                <div class="col-span-7 sm:col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editOtherItentificationModal" type="button" class="editOtherIdentification btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
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
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">LCC Reg. Number</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->registration_no) && !empty($student->registration_no) ? $student->registration_no : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">DF SID Number</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->df_sid_number) && !empty($student->df_sid_number) ? $student->df_sid_number : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Study Modes</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->other->mode->name) && !empty($student->other->mode->name) ? $student->other->mode->name : '---' }}</div>
                    </div>
                </div>
            </div>

            <div class="student-profile-proofid">
                <div class="student-profile-proofid-head">
                    <div class="font-medium text-base student-profile-proofid-title">Proof Of ID Checks</div>
                    <div id="tabulatorFilterForm-PIC" class="student-profile-proofid-filter">
                        <input id="query-PIC" name="query" type="text" class="form-control student-profile-proofid-search" placeholder="Search...">
                        <select id="status-PIC" name="status" class="form-select student-profile-proofid-status">
                            <option value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                        <button id="tabulator-html-filter-go-PIC" type="button" class="btn btn-primary student-profile-theme-btn student-profile-proofid-go">Go</button>
                        <button id="tabulator-html-filter-reset-PIC" type="button" class="btn btn-outline-secondary student-profile-proofid-reset">Reset</button>
                    </div>
                    <div class="student-profile-proofid-actions">
                        <button id="tabulator-print-PIC" class="btn btn-outline-secondary hidden md:inline-flex">Print</button>
                        <div class="dropdown hidden md:inline-flex">
                            <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                                Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv-PIC" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx-PIC" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addProoOfIdCheckModal" type="button" class="btn btn-primary student-profile-theme-btn">
                            <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Proof Of ID
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="studentProofOfIdCheckTable" data-student="{{ $student->id }}" class="mt-4 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-center student-profile-contact-head">
                <div class="xl:flex sm:mr-auto">
                    <div class="font-medium text-base">Contact Details</div>
                </div>
                <div class="student-profile-contact-head-actions flex justify-between mt-5 sm:mt-0">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="btn btn-primary student-profile-theme-outline-btn mr-2">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
                    </button>
                    <div class="dropdown student-profile-contact-more">
                        <button class="dropdown-toggle btn btn-outline-secondary student-profile-contact-more-btn" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="grip" class="w-4 h-4"></i>
                        </button>
                        <div class="dropdown-menu w-56 student-profile-contact-menu">
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
            <div class="grid grid-cols-12 gap-4 student-profile-contact">
                <div class="col-span-12 sm:col-span-4 student-profile-contact-col">
                    <div class="student-profile-contact-colhead">Term Time / Correspondence</div>
                    <div class="student-profile-contact-address">
                        @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                            @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                {{ $student->contact->termaddress->address_line_1 }}<br/>
                            @endif
                            @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                {{ $student->contact->termaddress->address_line_2 }}<br/>
                            @endif
                            @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                {{ $student->contact->termaddress->city }},
                            @endif
                            @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                {{ $student->contact->termaddress->state }},<br/>
                            @endif
                            @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                {{ $student->contact->termaddress->post_code }},
                            @endif
                            @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                {{ $student->contact->termaddress->country }}
                            @endif
                        @else
                            <span class="text-warning">Not Set Yet!</span>
                        @endif
                    </div>
                    <div class="student-profile-contact-rows">
                        <span class="student-profile-contact-rlabel">Polar4 quantile</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->termaddress->polar_4_quantile) && !empty($student->contact->termaddress->polar_4_quantile) ? $student->contact->termaddress->polar_4_quantile : '---') }}</span>
                        <span class="student-profile-contact-rlabel">Accommodation</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->ttacom->name) && !empty($student->contact->ttacom->name) ? $student->contact->ttacom->name : '---') }}</span>
                        <span class="student-profile-contact-rlabel">Postcode</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->term_time_post_code) && !empty($student->contact->term_time_post_code) ? $student->contact->term_time_post_code : '---') }}</span>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4 student-profile-contact-col">
                    <div class="student-profile-contact-colhead">Permanent</div>
                    <div class="student-profile-contact-address">
                        @if(isset($student->contact->permanent_address_id) && $student->contact->permanent_address_id > 0)
                            @if(isset($student->contact->permaddress->address_line_1) && !empty($student->contact->permaddress->address_line_1))
                                {{ $student->contact->permaddress->address_line_1 }}<br/>
                            @endif
                            @if(isset($student->contact->permaddress->address_line_2) && !empty($student->contact->permaddress->address_line_2))
                                {{ $student->contact->permaddress->address_line_2 }}<br/>
                            @endif
                            @if(isset($student->contact->permaddress->city) && !empty($student->contact->permaddress->city))
                                {{ $student->contact->permaddress->city }},
                            @endif
                            @if(isset($student->contact->permaddress->state) && !empty($student->contact->permaddress->state))
                                {{ $student->contact->permaddress->state }},<br/>
                            @endif
                            @if(isset($student->contact->permaddress->post_code) && !empty($student->contact->permaddress->post_code))
                                {{ $student->contact->permaddress->post_code }},
                            @endif
                            @if(isset($student->contact->permaddress->country) && !empty($student->contact->permaddress->country))
                                {{ $student->contact->permaddress->country }}
                            @endif
                        @else
                            <span class="text-warning">Not Set Yet!</span>
                        @endif
                    </div>
                    <div class="student-profile-contact-rows">
                        <span class="student-profile-contact-rlabel">Polar4 quantile</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->permaddress->polar_4_quantile) && !empty($student->contact->permaddress->polar_4_quantile) ? $student->contact->permaddress->polar_4_quantile : '---') }}</span>
                        <span class="student-profile-contact-rlabel">Country code</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->pcountry->name) && !empty($student->contact->pcountry->name) ? $student->contact->pcountry->name : '---') }}</span>
                        <span class="student-profile-contact-rlabel">Postcode</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($student->contact->permanent_post_code) && !empty($student->contact->permanent_post_code) ? $student->contact->permanent_post_code : '---') }}</span>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4 student-profile-contact-col">
                    <div class="student-profile-contact-colhead">Email &amp; Phone</div>
                    <div class="student-profile-contact-rows">
                        <span class="student-profile-contact-rlabel">Login email</span>
                        <span class="student-profile-contact-rvalue break-words">{{ $student->users->email }}</span>
                        <span class="student-profile-contact-rlabel">Personal email</span>
                        <span class="student-profile-contact-rvalue break-words">
                            {{ $student->contact->personal_email }}
                            @if ($student->contact->personal_email_verification == 0)
                                <span class="btn inline-flex btn-danger px-2 py-0 text-white rounded-0">Unverified</span>
                            @else
                                <span class="btn inline-flex btn-success px-2 py-0 text-white rounded-0">&#10003; Verified</span>
                            @endif
                        </span>
                        <span class="student-profile-contact-rlabel">Institutional</span>
                        <span class="student-profile-contact-rvalue break-words">{{ $student->contact->institutional_email }}</span>
                        <span class="student-profile-contact-rlabel">Home phone</span>
                        <span class="student-profile-contact-rvalue">{{ !empty($student->contact->home) ? $student->contact->home : '—' }}</span>
                        <span class="student-profile-contact-rlabel">Mobile</span>
                        <span class="student-profile-contact-rvalue">
                            {{ $student->contact->mobile }}
                            @if($student->contact->mobile_verification == 1)
                                <span class="btn inline-flex btn-success px-2 py-0 text-white rounded-0">&#10003; Verified</span>
                            @else
                                <span class="btn inline-flex btn-danger px-2 py-0 text-white rounded-0">Unverified</span>
                            @endif
                        </span>
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
                    <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
                    </button>

                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->kin->name) ? $student->kin->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Relation</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->kin->relation->name) ? $student->kin->relation->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-6 md:col-span-8 font-medium">{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3 sm:row-span-2">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
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
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-6 md:col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-6 md:col-span-8 font-medium break-words">{{ (isset($student->kin->email) && !empty($student->kin->email) ? $student->kin->email : '---') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php $hasFormalQual = isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1; @endphp
        <div class="intro-y box p-5 mt-5" id="applicantQualification">
            <div class="student-profile-secthead">
                <div class="student-profile-secthead-title">
                    <span class="font-medium text-base">Educational Qualification</span>
                    @if($hasFormalQual)
                        <span class="student-profile-secthead-pill">Has formal qualification</span>
                    @else
                        <span class="student-profile-secthead-pill is-no">No formal qualification</span>
                    @endif
                    <button data-tw-toggle="modal" data-tw-target="#editStudentQualStatusModal" type="button" class="student-profile-secthead-editbtn tooltip" title="Edit">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="student-profile-secthead-actions" style="display: {{ $hasFormalQual ? 'flex' : 'none' }};">
                    <button id="tabulator-print-SEQ" class="btn btn-outline-secondary hidden md:inline-flex">Print</button>
                    <div class="dropdown hidden md:inline-flex">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-SEQ" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-SEQ" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="btn student-profile-addbtn">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Qualification
                    </button>
                </div>
            </div>
            <div class="educationQualificationTableWrap" style="display: {{ $hasFormalQual ? 'block' : 'none' }};">
                <div class="student-profile-tablefilter" id="tabulatorFilterForm-SEQ">
                    <input id="query-SEQ" name="query" type="text" class="form-control student-profile-tablefilter-search" placeholder="Search...">
                    <select id="status-SEQ" name="status" class="form-select student-profile-tablefilter-status">
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                    <button id="tabulator-html-filter-go-SEQ" type="button" class="btn btn-primary student-profile-theme-btn">Go</button>
                    <button id="tabulator-html-filter-reset-SEQ" type="button" class="btn btn-outline-secondary student-profile-tablefilter-reset">Reset</button>
                </div>
                <div class="student-profile-tablebody">
                    <div id="studentEducationQualTable" data-student="{{ $student->id }}" class="table-report table-report--tabulator {{ $hasFormalQual ? 'activeTable' : '' }}"></div>
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
        @php $empStatusLabel = (isset($student->other->employment_status) && $student->other->employment_status != '' ? $student->other->employment_status : ''); @endphp
        <div class="intro-y box p-5 mt-5">
            <div class="student-profile-secthead">
                <div class="student-profile-secthead-title">
                    <span class="font-medium text-base">Employment History</span>
                    @if($empStatusLabel != '')
                        <span class="student-profile-secthead-pill is-neutral">Currently: {{ $empStatusLabel }}</span>
                    @endif
                    <button data-tw-toggle="modal" data-tw-target="#editStudentEmpStatusModal" type="button" class="student-profile-secthead-editbtn tooltip" title="Edit">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                </div>
                <div class="student-profile-secthead-actions" style="display: {{ $emptStatus ? 'flex' : 'none' }};">
                    <button id="tabulator-print-SEH" class="btn btn-outline-secondary hidden md:inline-flex">Print</button>
                    <div class="dropdown hidden md:inline-flex">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-SEH" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-SEH" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="btn student-profile-addbtn">
                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Employment
                    </button>
                </div>
            </div>
            <div class="educationEmploymentTableWrap" style="display: {{ $emptStatus ? 'block' : 'none' }};">
                <div class="student-profile-tablefilter" id="tabulatorFilterForm-SEH">
                    <input id="query-SEH" name="query" type="text" class="form-control student-profile-tablefilter-search" placeholder="Search...">
                    <select id="status-SEH" name="status" class="form-select student-profile-tablefilter-status">
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                    <button id="tabulator-html-filter-go-SEH" type="button" class="btn btn-primary student-profile-theme-btn">Go</button>
                    <button id="tabulator-html-filter-reset-SEH" type="button" class="btn btn-outline-secondary student-profile-tablefilter-reset">Reset</button>
                </div>
                <div class="student-profile-tablebody">
                    <div id="studentEmploymentHistoryTable" data-student="{{ $student->id }}" class="table-report table-report--tabulator {{ $emptStatus ? 'activeTable' : '' }}"></div>
                </div>
            </div>
        </div>
        
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Consents &amp; Referral</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#editStudentConsentModal" type="button" class="btn btn-primary student-profile-theme-outline-btn w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            @php $hasReferral = isset($student->referral_code) && !empty($student->referral_code) && isset($student->is_referral_varified) && $student->is_referral_varified == 1; @endphp
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 {{ $hasReferral ? 'sm:col-span-8' : '' }}">
                    @if(!empty($stdConsentIds) && $consent->count() > 0)
                        <ul class="student-profile-consents">
                            @foreach($consent as $con)
                                @if(in_array($con->id, $stdConsentIds))
                                <li class="student-profile-consent">
                                    <span class="student-profile-consent-check"><i data-lucide="check" class="w-3 h-3"></i></span>
                                    <div class="student-profile-consent-copy">
                                        <div class="student-profile-consent-title">{{ $con->name }}</div>
                                        <div class="student-profile-consent-desc">{{ $con->description }}</div>
                                    </div>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-danger-soft show flex items-center" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Student consent not set yet.
                        </div>
                    @endif
                </div>
                @if($hasReferral)
                <div class="col-span-12 sm:col-span-4 student-profile-referral">
                    <div class="student-profile-referral-head">Referred By</div>
                    <div class="student-profile-contact-rows">
                        <span class="student-profile-contact-rlabel">Code</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($referral->code) && !empty($referral->code) ? $referral->code : '—') }}</span>
                        <span class="student-profile-contact-rlabel">Type</span>
                        <span class="student-profile-contact-rvalue">{{ (isset($referral->type) ? $referral->type : '—') }}</span>
                        <span class="student-profile-contact-rlabel">Referrer</span>
                        <span class="student-profile-contact-rvalue is-block">
                            @if(isset($referral->type) && $referral->type == 'Student')
                                {{ $referral->student->first_name }} {{ $referral->student->last_name }}<br/>
                                {{ $referral->student->users->email }}<br/>
                                {{ $referral->student->contact->mobile }}
                            @elseif(isset($referral->type) && $referral->type == 'Agent')
                                N/A
                            @else
                                {{ (isset($referral->user->name) ? $referral->user->name : '') }}<br/>
                                {{ (isset($referral->user->email) ? $referral->user->email : '') }}
                            @endif
                        </span>
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
    @vite('resources/js/student-residency-criminal.js')
    @vite('resources/js/student-proof-id-check.js')
    @vite('resources/js/student-edication-qualification.js')
    @vite('resources/js/student-employment-history.js')
    @vite('resources/js/student-consent.js')
    <!-- @vite('resources/js/address.js') -->
@endsection
