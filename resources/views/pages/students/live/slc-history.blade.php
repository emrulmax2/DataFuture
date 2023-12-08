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
                <button data-tw-toggle="modal" data-tw-target="#addRegistrationModal" type="button" class="btn btn-primary shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Registration</button>
            </div>
        </div>
    </div>

    @if(!empty($slcRegistrations) && $slcRegistrations->count() > 0)
        @foreach($slcRegistrations as $regs)
            <div class="intro-y box p-5 mt-5">
                <div class="grid grid-cols-12 gap-0 items-center">
                    <div class="col-span-6">
                        <div class="font-medium text-base">Registration Information for <u class="text-success">Year {{ $regs->registration_year }}</u></div>
                    </div>
                    <div class="col-span-6 text-right relative">
                        <button data-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#editRegistrationModal" type="button" class="edit_registration_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                        <button data-reg-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#addAttendanceModal" type="button" class="add_attendance_btn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Attendance</button>
                    </div>
                </div>
                <div class="intro-y mt-5">
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Confirmation Date</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($regs->confirmation_date) ? date('jS M, Y', strtotime($regs->confirmation_date)) : '---') }}
                                    {!! (isset($regs->user->employee->full_name) && !empty($regs->user->employee->full_name) ? 'by '.$regs->user->employee->full_name : '') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-6 text-slate-500 font-medium">Registration Confirmation</div>
                                <div class="col-span-6 font-medium">
                                    {{ (!empty($regs->regStatus->name) ? $regs->regStatus->name : '---') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Academic Year</div>
                                <div class="col-span-8 font-medium">
                                    {{ (isset($regs->year->name) && !empty($regs->year->name) ? $regs->year->name : '---') }}
                                </div>
                            </div>
                        </div>
                        @if(!empty($regs->note))
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Note</div>
                                <div class="col-span-8 font-medium">
                                    {!! (!empty($regs->note) ? $regs->note : '---') !!}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="attendanceWrap mt-7">
                        @if(!empty($regs->attendances) && $regs->attendances->count() > 0)
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">ID</th>
                                        <th class="whitespace-nowrap">Confirmation Date</th>
                                        <th class="whitespace-nowrap">Attendance Semester</th>
                                        <th class="whitespace-nowrap">Session Term</th>
                                        <th class="whitespace-nowrap">Code</th>
                                        <th class="whitespace-nowrap">Note</th>
                                        <th class="whitespace-nowrap">COC ID</th>
                                        <th class="whitespace-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regs->attendances as $atn)
                                        <tr>
                                            <td>{{ $atn->id }}</td>
                                            <td>
                                                <span class="font-medium">
                                                    {{ (!empty($atn->confirmation_date) ? date('jS M, Y', strtotime($atn->confirmation_date)) : '') }}
                                                    {!! (isset($atn->user->employee->full_name) && !empty($atn->user->employee->full_name) ? 'by '.$atn->user->employee->full_name : '') !!}
                                                </span>
                                            </td>
                                            <td>
                                                ---
                                            </td>
                                            <td>{{ !empty($atn->session_term) ? 'Term '.$atn->session_term : '' }}</td>
                                            <td><span class="font-medium">{{ isset($atn->code->code) && !empty($atn->code->code) ? $atn->code->code : '' }}</span></td>
                                            <td>{{ !empty($atn->note) ? $atn->note : '' }}</td>
                                            <td>
                                                @if(isset($atn->coc->id) && $atn->coc->id > 0)
                                                    <a href="#" class="font-medium text-success underline">
                                                        {{ $atn->coc->id }}
                                                        @if(isset($atn->coc_type) && !empty($atn->coc_type))
                                                            <br/>{{ $atn->coc_type }}
                                                        @endif
                                                    </a>
                                                @elseif(isset($atn->code->id) && $atn->code->id > 0 && $atn->code->coc_required == 1)
                                                    <a href="#" class="font-medium text-success underline">Add COC</a>
                                                @endif
                                            </td>
                                            <td>
                                                <button data-id="{{ $atn->id }}" data-tw-toggle="modal" data-tw-target="#editAttendanceModal" type="button" class="edit_attendance_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else 
                            <div class="alert alert-danger-soft show flex items-center" role="alert">
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> SLC Attendance not found!
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <!-- BEGIN: Add Registration Modal -->
    <div id="addRegistrationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-xl-extended">
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
                                    <div class="col-span-4 text-slate-500 font-medium">Course Fees</div>
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
                            <div class="col-span-6 sm:col-span-3">
                                <label for="confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
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
                            <div class="col-span-6 sm:col-span-3">
                                <label for="registration_year" class="form-label">Registration Year <span class="text-danger">*</span></label>
                                <select id="registration_year" class="form-control w-full" name="registration_year">
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-registration_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
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
                            <div class="col-span-12 sm:col-span-3">
                                <label for="instance_fees" class="form-label">Instance Fees <span class="text-danger">*</span></label>
                                <input id="instance_fees" class="form-control w-full" name="instance_fees" type="number" step="any">
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="note" class="form-label">Self Funded?</label>
                                <div class="form-check form-switch">
                                    <input id="is_self_funded" name="is_self_funded" value="1" class="form-check-input" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
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
                                <textarea id="note" rows="2" class="form-control w-full" name="note"></textarea>
                            </div>
                            <div class="col-span-12">
                                <label for="note" class="form-label">Do you want to confirm Attendance Now?</label>
                                <div class="form-check form-switch">
                                    <input id="confirm_attendance" name="confirm_attendance" value="1" class="form-check-input" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-12 confirmAttendanceArea" style="display: none;">
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="attendance_term" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                        <select id="attendance_term" class="form-control w-full" name="attendance_term">
                                            <option value="0">Please Select</option>
                                        </select>
                                        <div class="acc__input-error error-attendance_term text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                        <select id="session_term" readonly class="form-control w-full" name="session_term">
                                            <option value="">Please Select</option>
                                        </select>
                                        <div class="acc__input-error error-session_term text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12"></div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="attendance_code_id" class="form-label">Attendance Code <span class="text-danger">*</span></label>
                                        <select id="attendance_code_id" class="form-control w-full" name="attendance_code_id">
                                            <option value="">Please Select</option>
                                            @if(!empty($attendanceCodes) && $attendanceCodes->count() > 0)
                                                @foreach($attendanceCodes as $ac)
                                                    <option data-coc-required="{{ $ac->coc_required }}" value="{{ $ac->id }}">{{ $ac->code }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-attendance_code_id text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3 installmentAmountWrap" style="display: none;">
                                        <label for="installment_amount" class="form-label">Installment Amount <span class="text-danger">*</span></label>
                                        <input id="installment_amount" class="form-control w-full" name="installment_amount" type="number" step="any">
                                        <div class="acc__input-error error-installment_amount text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="note" class="form-label">Attendance Note</label>
                                        <textarea id="attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
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
                        <input type="hidden" name="studen_ssn" value="{{ $student->ssn_no }}"/>
                        <input type="hidden" name="slc_course_code" value="{{ (isset($student->crel->creation->slc_code) ? $student->crel->creation->slc_code : '') }}"/>
                        <input type="hidden" name="student_course_relation_id" value="{{ $student->crel->id }}"/>
                        <input type="hidden" name="course_creation_id" value="{{ (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0) }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Registration Modal -->

    <!-- BEGIN: Edit Registration Modal -->
    <div id="editRegistrationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editRegistrationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Registration</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_ssn" class="form-label">SSN Number <span class="text-danger">*</span></label>
                                <input type="text" value="" readonly id="reg_ssn" class="form-control" name="ssn">
                                <div class="acc__input-error error-ssn text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="reg_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                <select id="reg_academic_year_id" class="form-control w-full" name="academic_year_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($ac_years) && $ac_years->count() > 0)
                                        @foreach($ac_years as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_registration_year" class="form-label">Registration Year <span class="text-danger">*</span></label>
                                <select id="reg_registration_year" class="form-control w-full" name="registration_year">
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-registration_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_course_creation_instance_id" class="form-label">Instance Year <span class="text-danger">*</span></label>
                                <select id="reg_course_creation_instance_id" class="form-control w-full" name="course_creation_instance_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($instances) && $instances->count())
                                        @foreach($instances as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-instance_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="reg_slc_registration_status_id" class="form-label">Registration Status <span class="text-danger">*</span></label>
                                <select id="reg_slc_registration_status_id" class="form-control w-full" name="slc_registration_status_id">
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
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateReg" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="slc_registration_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Registration Modal -->

    <!-- BEGIN: Add Attendance Modal -->
    <div id="addAttendanceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addAttendanceForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Attendance</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="add_atn_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="add_atn_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="add_attendance_year" class="form-label">Attendance Year <span class="text-danger">*</span></label>
                                <select id="add_attendance_year" class="form-control w-full" name="attendance_year">
                                    <option value="">Please Select</option>
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-attendance_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="add_atn_attendance_term" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                <select id="add_atn_attendance_term" class="form-control w-full" name="attendance_term">
                                    <option value="0">Please Select</option>
                                </select>
                                <div class="acc__input-error error-attendance_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="add_atn_session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                <select id="add_atn_session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="add_atn_attendance_code_id" class="form-label">Attendance Code <span class="text-danger">*</span></label>
                                <select id="add_atn_attendance_code_id" class="form-control w-full" name="attendance_code_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($attendanceCodes) && $attendanceCodes->count() > 0)
                                        @foreach($attendanceCodes as $ac)
                                            <option data-coc-required="{{ $ac->coc_required }}" value="{{ $ac->id }}">{{ $ac->code }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-attendance_code_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 addAttenInstallmentAmountWrap" style="display: none;">
                                <label for="add_atn_installment_amount" class="form-label">Installment Amount <span class="text-danger">*</span></label>
                                <input id="add_atn_installment_amount" class="form-control w-full" name="installment_amount" type="number" step="any">
                                <div class="acc__input-error error-installment_amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="add_atn_attendance_note" class="form-label">Attendance Note</label>
                                <textarea id="add_atn_attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addAtten" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="slc_registration_id" value="0"/>
                        <input type="hidden" name="instance_fees" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Attendance Modal -->

    <!-- BEGIN: Edit Attendance Modal -->
    <div id="editAttendanceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editAttendanceForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Attendance</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="atn_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="atn_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="attendance_year" class="form-label">Attendance Year <span class="text-danger">*</span></label>
                                <select id="attendance_year" class="form-control w-full" name="attendance_year">
                                    <option value="">Please Select</option>
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-attendance_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="atn_attendance_term" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                <select id="atn_attendance_term" class="form-control w-full" name="attendance_term">
                                    <option value="0">Please Select</option>
                                </select>
                                <div class="acc__input-error error-attendance_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="atn_session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                <select id="atn_session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="atn_attendance_code_id" class="form-label">Attendance Code <span class="text-danger">*</span></label>
                                <select id="atn_attendance_code_id" class="form-control w-full" name="attendance_code_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($attendanceCodes) && $attendanceCodes->count() > 0)
                                        @foreach($attendanceCodes as $ac)
                                            <option data-coc-required="{{ $ac->coc_required }}" value="{{ $ac->id }}">{{ $ac->code }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-attendance_code_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="atn_attendance_note" class="form-label">Attendance Note</label>
                                <textarea id="atn_attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAtten" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="slc_attendance_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Attendance Modal -->

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
    @vite('resources/js/student-slc-registration.js')
    @vite('resources/js/student-slc-attedance.js')
@endsection