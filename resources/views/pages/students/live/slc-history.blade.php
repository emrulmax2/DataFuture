@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">SLC History</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#addRegistrationModal" type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Registration</button>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-AN" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-AN" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-AN" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-AN" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-AN" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
                <div class="flex mt-5 sm:mt-0">
                    <button id="tabulator-print-AN" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown w-1/2 sm:w-auto">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentSlcRetistrationTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="addRegistrationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addRegistrationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Registration</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Name</div>
                                    <div class="col-span-8 font-medium">{{ $student->full_name }}</div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                                    <div class="col-span-8 font-medium">{{ !empty($student->date_of_birth) ? date('jS M, Y', strtotime($student->date_of_birth)) : '' }}</div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Address</div>
                                    <div class="col-span-8 font-medium">
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
                                                <span class="font-medium">{{ $student->contact->termaddress->country }}</span>
                                            @endif
                                        @else 
                                            <span class="font-medium text-warning">Not Set Yet!</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">SSN</div>
                                    <div class="col-span-8 font-medium">{{ $student->ssn_no }}</div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Course</div>
                                    <div class="col-span-8 font-medium">
                                        {{ $student->crel->creation->course->name }}
                                        {{ (isset($student->crel->creation->slc_code) ? ' ('.$student->crel->creation->slc_code.')' : '')}}
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Campus</div>
                                    <div class="col-span-8 font-medium">
                                        {{ (isset($student->crel->creation->venue->name) ? $student->crel->creation->venue->name : '') }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Fees</div>
                                    <div class="col-span-8 font-medium">
                                        {{ (isset($student->crel->creation->fees) && $student->crel->creation->fees > 0 ? '£'.number_format($student->crel->creation->fees, 2) : '') }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Awarding Body Ref:</div>
                                    <div class="col-span-8 font-medium">
                                        {{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-0 mb-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6">
                                <label for="confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="confirmation_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select id="academic_year_id" class="form-control w-full" name="academic_year_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($ac_years) && $ac_years->count() > 0)
                                        @foreach($ac_years as $year)
                                            <option {{ ($active_ac_year == $year->id ? 'Selected' : '') }} value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="registration_year" class="form-label">Registration Year <span class="text-danger">*</span></label>
                                <select id="registration_year" class="form-control w-full" name="registration_year">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                                <div class="acc__input-error error-registration_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="course_creation_instance_id" class="form-label">Instance Year <span class="text-danger">*</span></label>
                                <select id="course_creation_instance_id" class="form-control w-full" name="course_creation_instance_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($instances) && $instances->count())
                                        @foreach($instances as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-instance_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="slc_registration_status_id" class="form-label">Registration Status <span class="text-danger">*</span></label>
                                <select id="status" class="form-control w-full" name="slc_registration_status_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($reg_status) && $reg_status->count() > 0)
                                        @foreach($reg_status as $rst)
                                            <option value="{{ $rst->id }}">{{ $rst->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-slc_registration_status_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="note" class="form-label">Note</label>
                                <textarea id="note" rows="3" class="form-control w-full" name="note"></textarea>
                            </div>
                            <div class="col-span-12">
                                <label for="note" class="form-label">Do you want to confirm Attendance Now?</label>
                                <div class="form-check form-switch">
                                    <input id="confirm_attendance" name="confirm_attendance" value="1" class="form-check-input" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-12 confirmAttendanceArea" style="display: none;">
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="note" class="form-label">Self Funded?</label>
                                        <div class="form-check form-switch">
                                            <input id="is_self_funded" name="is_self_funded" value="1" class="form-check-input" type="checkbox">
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="self_funded_year" class="form-label">Year</label>
                                        <select id="self_funded_year" class="form-control w-full" name="self_funded_year">
                                            <option value="">Please Select</option>
                                            @if(!empty($ac_years) && $ac_years->count() > 0)
                                                @foreach($ac_years as $year)
                                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="instance_fees" class="form-label">Instance Fees</label>
                                        <input id="instance_fees" readonly class="form-control w-full" name="instance_fees" type="number" step="any">
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="attendance_term" class="form-label">Selected Attendance Terms</label>
                                        <select id="attendance_term" class="form-control w-full" name="attendance_term">
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="session_term" class="form-label">Attendance Session Term</label>
                                        <select id="session_term" readonly class="form-control w-full" name="session_term">
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12"></div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="attendance_code_id" class="form-label">Attendance Code</label>
                                        <select id="attendance_code_id" readonly class="form-control w-full" name="attendance_code_id">
                                            <option value="">Please Select</option>
                                            @if(!empty($attendanceCodes) && $attendanceCodes->count() > 0)
                                                @foreach($attendanceCodes as $ac)
                                                    <option data-coc-required="{{ $ac->coc_required }}" value="{{ $ac->id }}">{{ $ac->code }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveReg" class="btn btn-primary w-auto">     
                            Save                      
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
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
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-slc-history.js')
@endsection