@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    @php
        $allCocs = collect();
        $studentDobFormatted = !empty($student->date_of_birth) ? date('jS M, Y', strtotime($student->date_of_birth)) : 'Not set';
        $studentCourseName = isset($student->crel->creation->course->name) && !empty($student->crel->creation->course->name) ? $student->crel->creation->course->name : 'Not set';
        $studentSlcCode = isset($student->crel->propose->slc_code) && !empty($student->crel->propose->slc_code) ? $student->crel->propose->slc_code : null;
        $studentCampusName = isset($student->crel->propose->venue->name) && !empty($student->crel->propose->venue->name) ? $student->crel->propose->venue->name : 'Not set';
        $studentCourseFees = isset($student->crel->creation->fees) && $student->crel->creation->fees > 0 ? '£'.number_format($student->crel->creation->fees, 2) : 'Not set';
        $studentAwardRef = isset($student->crel->abody->reference) && !empty($student->crel->abody->reference) ? $student->crel->abody->reference : 'Not set';
        $studentAddressHtml = 'Not set yet';
        if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0) {
            $addressParts = array_filter([
                $student->contact->termaddress->address_line_1 ?? null,
                $student->contact->termaddress->address_line_2 ?? null,
                trim(implode(', ', array_filter([
                    $student->contact->termaddress->city ?? null,
                    $student->contact->termaddress->state ?? null,
                ]))),
                trim(implode(', ', array_filter([
                    $student->contact->termaddress->post_code ?? null,
                    $student->contact->termaddress->country ?? null,
                ]))),
            ]);
            if(!empty($addressParts)) {
                $studentAddressHtml = implode(', ', $addressParts);
            }
        }
        if(!empty($slcRegistrations) && $slcRegistrations->count() > 0) {
            foreach($slcRegistrations as $registration) {
                if(isset($registration->cocs) && $registration->cocs->count() > 0) {
                    foreach($registration->cocs as $coc) {
                        $allCocs->push(['coc' => $coc, 'showMove' => false]);
                    }
                }
            }
        }
        if(isset($undefinedSlcCocs) && $undefinedSlcCocs->count() > 0) {
            foreach($undefinedSlcCocs as $coc) {
                $allCocs->push(['coc' => $coc, 'showMove' => true]);
            }
        }
        $allCocs = $allCocs
            ->sortByDesc(function ($entry) {
                $coc = $entry['coc'];
                return !empty($coc->confirmation_date) ? strtotime($coc->confirmation_date) : 0;
            })
            ->values();
    @endphp

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="slc-wrap intro-y mt-5">

        <!-- ===== SLC History header card ===== -->
        <div class="slc-card slc-headcard">
            <div>
                <div class="slc-headcard-title">SLC History</div>
                <div class="slc-ssn">
                    <span class="slc-ssn-label">SSN</span>
                    <a class="slc-ssn-value" target="_blank" href="https://auth.uim.slcsvc.co.uk/login?response_type=code&scope=openid&client_id=3lppkvq4jfbfk4r2d8hta5scon&state=ploPoWBGdpKE5QuRA6KJ-7ZcFGc&redirect_uri=https://secure.heservices.slc.co.uk/redirect_uri&nonce=bWjY_a6eji9c1wC_JynAcxuvt_jPB4P53o7KMKmclQM">{{ isset($student->ssn_no) && !empty($student->ssn_no) ? $student->ssn_no : '---' }}</a>
                </div>
            </div>
            <div class="slc-headcard-actions">
                @if($can_add) <button data-tw-toggle="modal" data-tw-target="#addRegistrationModal" type="button" class="slc-btn slc-btn-teal"><i data-lucide="plus-circle"></i>Add Registration</button> @endif
            </div>
        </div>

        <!-- ===== Registration year cards ===== -->
        @if(!empty($slcRegistrations) && $slcRegistrations->count() > 0)
            @foreach($slcRegistrations as $regs)
                <div class="slc-card">
                    <div class="slc-reg-head">
                        <span class="slc-reg-badge">Y{{ $regs->registration_year }}</span>
                        <div>
                            <div class="slc-reg-title">Registration &mdash; <u class="text-success">Year {{ $regs->registration_year }}</u></div>
                            <div class="slc-reg-sub">
                                {{ (isset($regs->year->name) && !empty($regs->year->name) ? 'Academic year '.$regs->year->name.' · ' : '') }}<span class="mono">#{{ $regs->id }}</span>
                            </div>
                        </div>
                        @if(isset($regs->regStatus->name) && !empty($regs->regStatus->name))
                            <span class="slc-reg-status"><span class="slc-reg-status-dot"></span>{{ $regs->regStatus->name }}</span>
                        @endif
                        <div class="slc-reg-meta">
                            <span class="slc-reg-meta-text">
                                @if(!empty($regs->confirmation_date))Confirmed {{ date('jS M, Y', strtotime($regs->confirmation_date)) }}@endif
                                {!! (isset($regs->user->employee->full_name) && !empty($regs->user->employee->full_name) ? ' · by '.$regs->user->employee->full_name : '') !!}
                            </span>
                            <div class="slc-iconbtn-group">
                                @if($can_edit) <button data-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#editRegistrationModal" type="button" class="edit_registration_btn slc-iconbtn" title="Edit"><i data-lucide="pencil"></i></button> @endif
                                @if($can_delete) <button data-id="{{ $regs->id }}" type="button" class="delete_reg_btn slc-iconbtn is-danger" title="Delete"><i data-lucide="trash-2"></i></button> @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($regs->note))
                        <div class="slc-card-body" style="padding-bottom:0">
                            <div class="slc-reg-sub"><strong style="color:#43585D">Note:</strong> {!! $regs->note !!}</div>
                        </div>
                    @endif

                    <!-- Attendances -->
                    <div class="slc-subhead">
                        <div class="slc-subhead-title">Attendance Confirmations</div>
                        @if($can_add) <button data-reg-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#addAttendanceModal" type="button" class="add_attendance_btn slc-btn slc-btn-outline slc-btn-sm"><i data-lucide="plus-circle"></i>Add Attendance</button> @endif
                    </div>
                    <div class="slc-card-body">
                        @if(!empty($regs->attendances) && $regs->attendances->count() > 0)
                            <div class="slc-tablewrap">
                                <div class="slc-tablescroll">
                                    <table class="slc-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Confirmation</th>
                                                <th>Semester</th>
                                                <th>Session Term</th>
                                                <th>Code</th>
                                                <th>COC / Note</th>
                                                <th class="slc-td-actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($regs->attendances as $atn)
                                                <tr>
                                                    <td class="slc-td-mono">{{ $atn->id }}</td>
                                                    <td>
                                                        <span class="slc-td-strong">{{ (!empty($atn->confirmation_date) ? date('jS M, Y', strtotime($atn->confirmation_date)) : '') }}</span>
                                                        {!! (isset($atn->user->employee->full_name) && !empty($atn->user->employee->full_name) ? '<br><span class="slc-td-muted">by '.$atn->user->employee->full_name.'</span>' : '') !!}
                                                    </td>
                                                    <td>
                                                        <span class="slc-td-strong">{{ isset($atn->term->name) && !empty($atn->term->name) ? $atn->term->name : '' }}</span>
                                                        {!! (isset($atn->term->termType->name) && !empty($atn->term->termType->name) ? '<br><span class="slc-td-muted">'.$atn->term->termType->name.'</span>' : '') !!}
                                                    </td>
                                                    <td>@if(!empty($atn->session_term))<span class="slc-term-chip">Term {{ $atn->session_term }}</span>@endif</td>
                                                    <td>@if(isset($atn->code->code) && !empty($atn->code->code))<span class="slc-code">{{ $atn->code->code }}</span>@endif</td>
                                                    <td>
                                                        @if(isset($atn->code->id) && $atn->code->id > 0 && $atn->code->coc_required == 1 && isset($atn->coc) && $atn->coc->count() > 0)
                                                            <a class="slc-doclink" href="javascript:void(0);">COC ( @foreach($atn->coc as $coc){{ $coc->id.(!$loop->last ? ', ' : '') }}@endforeach )</a>
                                                        @elseif(!empty($atn->note))
                                                            <span class="slc-td-muted">{{ $atn->note }}</span>
                                                        @else
                                                            <span class="slc-dash">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="slc-td-actions">
                                                        <div class="slc-iconbtn-group">
                                                            @if($can_edit) <button data-id="{{ $atn->id }}" data-tw-toggle="modal" data-tw-target="#editAttendanceModal" type="button" class="edit_attendance_btn slc-iconbtn" title="Edit"><i data-lucide="pencil"></i></button> @endif
                                                            @if($can_delete) <button data-id="{{ $atn->id }}" type="button" class="delete_attendance_btn slc-iconbtn is-danger" title="Delete"><i data-lucide="trash-2"></i></button> @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="slc-tablewrap">
                                <div class="slc-empty">
                                    <span class="slc-empty-icon"><i data-lucide="calendar-x" class="w-4 h-4"></i></span>
                                    <div class="slc-empty-title">Attendance record not found</div>
                                    <div class="slc-empty-sub">Add an attendance confirmation for this registration year.</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

        <!-- ===== Unspecified attendance ===== -->
        <div class="slc-card">
            <div class="slc-sechead">
                <div class="slc-sechead-title">Attendance at SLC — Unspecified</div>
                <span class="slc-tag is-warning">No registration year</span>
                @if($can_add) <button data-reg-id="0" data-tw-toggle="modal" data-tw-target="#addAttendanceModal" type="button" class="add_attendance_btn slc-btn slc-btn-outline slc-btn-sm" style="margin-left:auto"><i data-lucide="plus-circle"></i>Add Attendance</button> @endif
            </div>
            <div class="slc-sec-body">
                @if(!empty($undefinedSlcAttendances) && $undefinedSlcAttendances->count() > 0)
                    <div class="slc-tablewrap">
                        <div class="slc-tablescroll">
                            <table class="slc-table" id="undefinedAttendanceTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Confirmation Date</th>
                                        <th>Semester</th>
                                        <th>Session Term</th>
                                        <th>Code</th>
                                        <th>Note</th>
                                        <th class="slc-td-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($undefinedSlcAttendances as $atn)
                                        <tr>
                                            <td class="slc-td-mono">{{ $atn->id }}</td>
                                            <td>
                                                <span class="slc-td-strong">{{ (!empty($atn->confirmation_date) ? date('jS M, Y', strtotime($atn->confirmation_date)) : '') }}</span>
                                                {!! (isset($atn->user->employee->full_name) && !empty($atn->user->employee->full_name) ? '<br><span class="slc-td-muted">by '.$atn->user->employee->full_name.'</span>' : '') !!}
                                            </td>
                                            <td>
                                                <span class="slc-td-strong">{{ isset($atn->term->name) && !empty($atn->term->name) ? $atn->term->name : '' }}</span>
                                                {!! (isset($atn->term->termType->name) && !empty($atn->term->termType->name) ? '<br><span class="slc-td-muted">'.$atn->term->termType->name.'</span>' : '') !!}
                                            </td>
                                            <td>@if(!empty($atn->session_term))<span class="slc-term-chip">Term {{ $atn->session_term }}</span>@endif</td>
                                            <td>@if(isset($atn->code->code) && !empty($atn->code->code))<span class="slc-code">{{ $atn->code->code }}</span>@endif</td>
                                            <td>@if(!empty($atn->note))<span class="slc-td-muted">{{ $atn->note }}</span>@else<span class="slc-dash">—</span>@endif</td>
                                            <td class="slc-td-actions">
                                                @if(!empty($slcRegistrations) && $slcRegistrations->count() > 0 && $can_add)
                                                    <div class="dropdown inline-block" data-tw-placement="bottom-end">
                                                        <button class="dropdown-toggle slc-iconbtn" aria-expanded="false" data-tw-toggle="dropdown" title="Move to registration"><i data-lucide="arrow-right-left"></i></button>
                                                        <div class="dropdown-menu w-64">
                                                            <ul class="dropdown-content">
                                                                @foreach($slcRegistrations as $regs)
                                                                    <li><a href="javascript:void(0);" data-reg="{{ $regs->id }}" data-atn="{{ $atn->id }}" class="dropdown-item assignAttendanceToReg text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>ID: {{ $regs->id }} - Year {{ $regs->registration_year }} {{ (isset($regs->year->name) && !empty($regs->year->name) ? ' - '.$regs->year->name : '') }}</a></li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="slc-tablewrap">
                        <div class="slc-empty">
                            <span class="slc-empty-icon"><i data-lucide="calendar" class="w-4 h-4"></i></span>
                            <div class="slc-empty-title">No unspecified attendance records</div>
                            <div class="slc-empty-sub">Confirmations without a registration year will appear here.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ===== Combined COC history ===== -->
        <div class="slc-card">
            <div class="slc-sechead">
                <div class="slc-sechead-title">Change of Circumstances (COC)</div>
                <span class="slc-tag is-muted">{{ $allCocs->count() }} records</span>
                @if($can_add) <button data-regid="0" data-atnid="0" data-tw-toggle="modal" data-tw-target="#addCOCModal" type="button" class="addCOCBtn slc-btn slc-btn-teal slc-btn-sm" style="margin-left:auto"><i data-lucide="plus-circle"></i>Add COC</button> @endif
            </div>
            <div class="slc-sec-body">
                @if($allCocs->count() > 0)
                    <div class="slc-tablewrap">
                        <div class="slc-tablescroll">
                            <table class="slc-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Confirmation Date</th>
                                        <th>Type</th>
                                        <th>Reason</th>
                                        <th>Actioned</th>
                                        <th>Submitted By</th>
                                        <th>Documents</th>
                                        <th class="slc-td-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allCocs as $entry)
                                        @include('pages.students.live.slc-coc-row', [
                                            'coc' => $entry['coc'],
                                            'showMove' => $entry['showMove'],
                                            'studentAttendanceIds' => $studentAttendanceIds
                                        ])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="slc-cardfoot">
                        <span>Showing <strong>{{ $allCocs->count() }}</strong> of <strong>{{ $allCocs->count() }}</strong> COC records</span>
                    </div>
                @else
                    <div class="slc-tablewrap">
                        <div class="slc-empty">
                            <span class="slc-empty-icon"><i data-lucide="file-x" class="w-4 h-4"></i></span>
                            <div class="slc-empty-title">No COC records found</div>
                            <div class="slc-empty-sub">Change of Circumstances history will appear here once records are added.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>


    <!-- BEGIN: Add Registration Modal -->
    <div id="addRegistrationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-xl-extended">
            <form method="POST" action="#" id="addRegistrationForm" enctype="multipart/form-data">
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Add Registration</h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-summary">
                            <div class="slm-summary-head">
                                <div>
                                    <div class="slm-summary-title">Student registration overview</div>
                                    <div class="slm-summary-sub">Confirm the core student and course details before creating the registration.</div>
                                </div>
                                <span class="slm-ssn-chip">SSN {{ !empty($student->ssn_no) ? $student->ssn_no : '---' }}</span>
                            </div>
                            <div class="slm-facts">
                                <div class="slm-fact slm-fact-span">
                                    <span class="slm-fact-label">Name</span>
                                    <span class="slm-fact-value">{{ $student->full_name }}</span>
                                </div>
                                <div class="slm-fact">
                                    <span class="slm-fact-label">Date of Birth</span>
                                    <span class="slm-fact-value">{{ $studentDobFormatted }}</span>
                                </div>
                                <div class="slm-fact slm-fact-span">
                                    <span class="slm-fact-label">Address</span>
                                    <span class="slm-fact-value">{!! $studentAddressHtml !!}</span>
                                </div>
                                <div class="slm-fact slm-fact-span">
                                    <span class="slm-fact-label">Course</span>
                                    <span class="slm-fact-value">{{ $studentCourseName }}{{ $studentSlcCode ? ' ('.$studentSlcCode.')' : '' }}</span>
                                </div>
                                <div class="slm-fact">
                                    <span class="slm-fact-label">Campus</span>
                                    <span class="slm-fact-value">{{ $studentCampusName }}</span>
                                </div>
                                <div class="slm-fact">
                                    <span class="slm-fact-label">Course Fees</span>
                                    <span class="slm-fact-value regCourseFee" data-fee="{{ (isset($student->crel->creation->fees) && $student->crel->creation->fees > 0 ? $student->crel->creation->fees : 0) }}">
                                        <span class="regularCourseFee">
                                            {{ $studentCourseFees }}
                                        </span>
                                        <span class="instanceCourseFee text-success ml-2 hidden"></span>
                                    </span>
                                </div>
                                <div class="slm-fact">
                                    <span class="slm-fact-label">Awarding Body Ref</span>
                                    <span class="slm-fact-value">{{ $studentAwardRef }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="slm-section">
                            <div class="slm-section-title">Registration details</div>
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
                                            <option {{ (isset($student->crel->propose->academic_year_id) && $student->crel->propose->academic_year_id == $year->id ? 'Selected' : '') }} value="{{ $year->id }}">{{ $year->name }}</option>
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
                                <label for="course_creation_instance_id" class="form-label">Instance Year </label><!-- <span class="text-danger">*</span> -->
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
                                            <option {{ (!isset($student->crel->abody->reference) || empty($student->crel->abody->reference) ? ($rst->id == 2 ? '' : 'disabled') : '' ) }} value="{{ $rst->id }}">{{ $rst->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-slc_registration_status_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 linkedRegistrationWrap bg-warning-soft rounded pb-2" style="display: none;">
                                <div class="alert alert-warning-soft show flex items-center mb-2 text-dark" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2 text-warning"></i>
                                    An agreement already exists for the selected year. Would you like to link it to this registration?
                                </div>
                                <div class="flex flex-col sm:flex-row mt-2 px-5">
                                    <div class="form-check mr-4">
                                        <input id="linked_agreement_y" class="form-check-input" type="radio" name="linked_agreement" value="1">
                                        <label class="form-check-label" for="linked_agreement_y">Yes</label>
                                    </div>
                                    <div class="form-check mr-4">
                                        <input id="linked_agreement_n" class="form-check-input" type="radio" name="linked_agreement" value="0">
                                        <label class="form-check-label" for="linked_agreement_n">No</label>
                                    </div>
                                </div>
                                <div class="acc__input-error error-linked_agreement text-danger mt-2 px-5"></div>
                                <input type="hidden" name="linked_agreement_id" value="0"/>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="note" class="form-label">Note</label>
                                <textarea id="note" rows="2" class="form-control w-full" name="note"></textarea>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="slm-togglecard">
                                <label for="note" class="form-label">Do you want to confirm Attendance Now?</label>
                                <div class="form-check form-switch">
                                    <input id="confirm_attendance" name="confirm_attendance" value="1" class="form-check-input" type="checkbox">
                                </div>
                                <div class="slm-togglehint">Enable this if you want to create the first attendance confirmation immediately after registration.</div>
                                </div>
                            </div>
                            <div class="col-span-12 confirmAttendanceArea" style="display: none;">
                                <div class="slm-subsection">
                                    <div class="slm-section-title">Initial attendance confirmation</div>
                                <div class="grid grid-cols-12 gap-3">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="term_declaration_id" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                        <select id="term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                            <option value="0">Please Select</option>
                                            @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                                @foreach($term_declarations as $td)
                                                    <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                        <select id="session_term" class="form-control w-full" name="session_term">
                                            <option value="">Please Select</option>
                                            <option value="1">Term 01</option>
                                            <option value="2">Term 02</option>
                                            <option value="3">Term 03</option>
                                            <option value="4">Term 04</option>
                                            <option value="5">N/A</option>
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
                                    <div class="col-span-12 sm:col-span-3 cocReqWrap" style="display: none;">
                                        <div class="alert alert-pending-soft show flex items-center px-2 py-1 mt-5" role="alert">
                                            COC required. please raise a COC and record it on the system
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="note" class="form-label">Attendance Note</label>
                                        <textarea id="attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer slm-footer">
                        @php 
                            $disable = '';
                            if(empty($student->ssn_no) || (!isset($student->crel->propose->slc_code) || empty($student->crel->propose->slc_code)) || (!isset($student->crel->id) || empty($student->crel->id))):
                                $disable = ' disabled ';
                            endif;
                        @endphp
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1 ml-auto">Cancel</button>
                        <button {{ $disable }} type="submit" id="saveReg" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="slc_course_code" value="{{ (isset($student->crel->propose->slc_code) && !empty($student->crel->propose->slc_code) ? $student->crel->propose->slc_code : '')}}"/>
                        <input type="hidden" name="student_course_relation_id" value="{{ $student->crel->id }}"/>
                        <input type="hidden" name="course_creation_id" value="{{ (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0) }}"/>
                        <input type="hidden" name="awarding_body_ref" value="{{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}"/>
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
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Edit Registration</h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-section">
                            <div class="slm-section-title">Registration details</div>
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
                                <label for="reg_course_creation_instance_id" class="form-label">Instance Year</label><!-- <span class="text-danger">*</span> -->
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
                                            <option {{ (!isset($student->crel->abody->reference) || empty($student->crel->abody->reference) ? ($rst->id == 2 ? '' : 'disabled') : '' ) }} value="{{ $rst->id }}">{{ $rst->name }}</option>
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
                    </div>
                    <div class="modal-footer slm-footer">
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
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Add Attendance <span class="font-medium attendanceYear text-success underline"></span></h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-section-title">Attendance confirmation details</div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="add_atn_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="add_atn_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="add_atn_term_declaration_id" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                <select id="add_atn_term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="0">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="add_atn_session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                <select id="add_atn_session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
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
                            <div class="col-span-12 sm:col-span-4 addAttenInstallmentAmountNotice" style="display: none;">
                                <div class="alert alert-warning-soft show flex items-center px-2 py-1 mt-5" role="alert">
                                    Opps! Installment already exist under this selected attendance year and term.
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 cocReqWrap" style="display: none;">
                                <div class="alert alert-pending-soft show flex items-center px-2 py-1 mt-5" role="alert">
                                    COC required. please raise a COC and record it on the system
                                </div>
                            </div>
                            <div class="col-span-12">
                                <label for="add_atn_attendance_note" class="form-label">Attendance Note</label>
                                <textarea id="add_atn_attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer slm-footer">
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
                        <input type="hidden" name="attendance_year" value="0"/>
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
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Edit Attendance <span class="font-medium attendanceYear text-success underline"></span></h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-section-title">Attendance confirmation details</div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="atn_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="atn_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="atn_term_declaration_id" class="form-label">Selected Attendance Terms <span class="text-danger">*</span></label>
                                <select id="atn_term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="0">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="atn_session_term" class="form-label">Attendance Session Term <span class="text-danger">*</span></label>
                                <select id="atn_session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
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
                            <div class="col-span-12 sm:col-span-4 cocReqWrap" style="display: none;">
                                <div class="alert alert-pending-soft show flex items-center px-2 py-1 mt-5" role="alert">
                                    COC required. please raise a COC and record it on the system
                                </div>
                            </div>
                            <div class="col-span-12">
                                <label for="atn_attendance_note" class="form-label">Attendance Note</label>
                                <textarea id="atn_attendance_note" rows="2" class="form-control w-full" name="attendance_note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer slm-footer">
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

    <!-- BEGIN: Add COC Modal -->
    <div id="addCOCModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addtCOCForm" enctype="multipart/form-data">
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Add COC</h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-section-title">COC details</div>
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-6 sm:col-span-6">
                                <label for="coc_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="<?php echo date('d-m-Y') ?>" placeholder="DD-MM-YYYY" id="coc_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="coc_type" class="form-label">Type of COC <span class="text-danger">*</span></label>
                                <select id="coc_type" class="form-control w-full" name="coc_type">
                                    <option value="">Please Select</option>
                                    <option value="Fee">Fee</option>
                                    <option value="Outstanding">Outstanding</option>
                                    <option value="Repetition">Repetition</option>
                                    <option value="Resumption">Resumption</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Withdrawal">Withdrawal</option>
                                </select>
                                <div class="acc__input-error error-coc_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="coc_actioned" class="form-label">Actioned <span class="text-danger">*</span></label>
                                <select id="coc_actioned" class="form-control w-full" name="actioned">
                                    <option value="">Please Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                                <div class="acc__input-error error-actioned text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="coc_reason" class="form-label">Reason</label>
                                <textarea id="coc_reason" rows="2" class="form-control w-full" name="reason"></textarea>
                            </div>
                            <div class="col-span-12">
                                <div class="slm-upload">
                                    <label for="addCOCDocument" class="inline-flex items-center justify-center btn btn-primary cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="addCOCDocument"/>
                                    <span id="addCOCDocumentName" class="documentCOCName slm-upload-name"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer slm-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addCOC" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="slc_attendance_id" value="0"/>
                        <input type="hidden" name="slc_registration_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add COC Modal -->

    <!-- BEGIN: Edit COC Modal -->
    <div id="editCOCModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editCOCForm" enctype="multipart/form-data">
                <div class="modal-content slm-card">
                    <div class="modal-header slm-header">
                        <div class="slm-titleblock">
                            <span class="slm-kicker">SLC History</span>
                            <h2 class="font-medium text-base mr-auto">Edit COC</h2>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body slm-body">
                        <div class="slm-section-title">COC details</div>
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-6 sm:col-span-6">
                                <label for="ecoc_confirmation_date" class="form-label">Date of Confirmation <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="ecoc_confirmation_date" class="form-control datepicker" name="confirmation_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-confirmation_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="ecoc_type" class="form-label">Type of COC <span class="text-danger">*</span></label>
                                <select id="ecoc_type" class="form-control w-full" name="coc_type">
                                    <option value="">Please Select</option>
                                    <option value="Fee">Fee</option>
                                    <option value="Outstanding">Outstanding</option>
                                    <option value="Repetition">Repetition</option>
                                    <option value="Resumption">Resumption</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Withdrawal">Withdrawal</option>
                                </select>
                                <div class="acc__input-error error-coc_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="ecoc_actioned" class="form-label">Actioned <span class="text-danger">*</span></label>
                                <select id="ecoc_actioned" class="form-control w-full" name="actioned">
                                    <option value="">Please Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                                <div class="acc__input-error error-actioned text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="ecoc_reason" class="form-label">Reason</label>
                                <textarea id="ecoc_reason" rows="2" class="form-control w-full" name="reason"></textarea>
                            </div>
                            <div class="col-span-12">
                                <div class="slm-upload">
                                    <label for="editCOCDocument" class="inline-flex items-center justify-center btn btn-primary cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="editCOCDocument"/>
                                    <span id="editCOCDocumentName" class="documentCOCName slm-upload-name"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer slm-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateCOC" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="slc_coc_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit COC Modal -->

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
    @vite('resources/js/student-slc-coc.js')
    @vite('resources/js/student-slc-history-merge.js')
@endsection
