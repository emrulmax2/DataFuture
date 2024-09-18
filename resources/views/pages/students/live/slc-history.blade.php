@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">SLC History</div>
                <div class="col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium"><a target="_blank" href="https://auth.uim.slcsvc.co.uk/login?response_type=code&scope=openid&client_id=3lppkvq4jfbfk4r2d8hta5scon&state=ploPoWBGdpKE5QuRA6KJ-7ZcFGc&redirect_uri=https://secure.heservices.slc.co.uk/redirect_uri&nonce=bWjY_a6eji9c1wC_JynAcxuvt_jPB4P53o7KMKmclQM">{{ isset($student->ssn_no) && !empty($student->ssn_no) ? $student->ssn_no : '---' }}</a></div>
                    </div>
                </div>
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
                        <button data-id="{{ $regs->id }}" type="button" class="delete_reg_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </div>
                <div class="intro-y mt-5">
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">#ID</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($regs->id) ? $regs->id : '---') }}
                                </div>
                            </div>
                        </div>
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
                    @if(!empty($regs->attendances) && $regs->attendances->count() > 0)
                        <div class="attendanceWrap mt-7  bg-success-soft-1 p-3 rounded">
                            <div class="grid grid-cols-12 gap-0 items-center">
                                <div class="col-span-6">
                                    <h3 class="font-medium text-base">Attendances</h3>
                                </div>
                                <div class="col-span-6 text-right">
                                    <button data-reg-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#addAttendanceModal" type="button" class="add_attendance_btn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Attendance</button>
                                </div>
                            </div>
                            <table class="table table-bordered table-sm mt-3 bg-white">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">ID</th>
                                        <th class="whitespace-nowrap">Confirmation Date</th>
                                        <th class="whitespace-nowrap">Attendance Semester</th>
                                        <th class="whitespace-nowrap">Session Term</th>
                                        <th class="whitespace-nowrap">Code</th>
                                        <th class="whitespace-nowrap">Note</th>
                                        <th class="whitespace-nowrap">COC</th>
                                        <th class="whitespace-nowrap">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regs->attendances as $atn)
                                        <tr>
                                            <td>{{ $atn->id }}</td>
                                            <td>
                                                <span>
                                                    {{ (!empty($atn->confirmation_date) ? date('jS M, Y', strtotime($atn->confirmation_date)) : '') }}
                                                    {!! (isset($atn->user->employee->full_name) && !empty($atn->user->employee->full_name) ? 'by '.$atn->user->employee->full_name : '') !!}
                                                </span>
                                            </td>
                                            <td>
                                                {{ isset($atn->term->name) && !empty($atn->term->name) ? $atn->term->name : '' }}
                                                {{ isset($atn->term->termType->name) && !empty($atn->term->termType->name) ? ' - '.$atn->term->termType->name : '' }}
                                            </td>
                                            <td>{{ !empty($atn->session_term) ? 'Term '.$atn->session_term : '' }}</td>
                                            <td><span class="font-medium">{{ isset($atn->code->code) && !empty($atn->code->code) ? $atn->code->code : '' }}</span></td>
                                            <td>{{ !empty($atn->note) ? $atn->note : '' }}</td>
                                            <td>
                                                @if(isset($atn->code->id) && $atn->code->id > 0 && $atn->code->coc_required == 1)
                                                    <div class="flex items-center">
                                                        @if(isset($atn->coc) && $atn->coc->count() > 0)
                                                            <a class="inline-flex items-center text-primary font-medium mr-3" href="javascript:void(0);">
                                                                ( @foreach($atn->coc as $coc)
                                                                    {{ $coc->id.(!$loop->last ? ', ' : '') }}
                                                                @endforeach )
                                                            </a>
                                                        @endif
                                                        <a href="javascript:void(0);" data-regid="{{ $regs->id }}" data-atnid="{{ $atn->id }}" data-tw-toggle="modal" data-tw-target="#addCOCModal" class="addCOCBtn inline-flex items-center font-medium text-success"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add COC</a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <button data-id="{{ $atn->id }}" data-tw-toggle="modal" data-tw-target="#editAttendanceModal" type="button" class="edit_attendance_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                                                <button data-id="{{ $atn->id }}" type="button" class="delete_attendance_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else 
                        <div class="alert alert-pending-soft show flex items-center mt-7" role="alert">
                            <span class="inline-flex items-center">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Attendance recored not found!
                            </span>
                            <button data-reg-id="{{ $regs->id }}" data-tw-toggle="modal" data-tw-target="#addAttendanceModal" type="button" class="add_attendance_btn btn btn-linkedin shadow-md ml-auto"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Attendance</button>
                        </div>
                    @endif

                    @if(isset($regs->cocs) && $regs->cocs->count() > 0)
                    <div class="cocWraps mt-7 bg-danger-soft p-3 rounded">
                        <div class="grid grid-cols-12 gap-0 items-center">
                            <div class="col-span-6">
                                <h3 class="font-medium text-base">Coc Histories</h3>
                            </div>
                            <div class="col-span-6 text-right">
                                <button  data-regid="{{ $regs->id }}" data-atnid="0" data-tw-toggle="modal" data-tw-target="#addCOCModal" type="button" class="addCOCBtn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add COC</button>
                            </div>
                        </div>
                        <table class="table table-bordered table-sm mt-3 bg-white">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">ID</th>
                                    <th class="whitespace-nowrap">Confirmation Date</th>
                                    <th class="whitespace-nowrap">Type</th>
                                    <th class="whitespace-nowrap">Reason</th>
                                    <th class="whitespace-nowrap">Actioned</th>
                                    <th class="whitespace-nowrap">Submitted By</th>
                                    <th class="whitespace-nowrap">Documents</th>
                                    <th class="whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($regs->cocs as $coc)
                                    <tr>
                                        <td>
                                            {{ $coc->id.(isset($coc->slc_attendance_id) && $coc->slc_attendance_id > 0 ? ' - '.$coc->slc_attendance_id : '') }}
                                        </td>
                                        <td>
                                            {{ (!empty($coc->confirmation_date) ? date('jS F, Y', strtotime($coc->confirmation_date)) : '') }}
                                        </td>
                                        <td>{{ $coc->coc_type }}</td>
                                        <td>{{ $coc->reason }}</td>
                                        <td>{{ ucfirst($coc->actioned) }}</td>
                                        <td>{{ (isset($coc->user->employee->full_name) ? $coc->user->employee->full_name : '') }}</td>
                                        <td>
                                            @if($coc->documents->count() > 0)
                                                <div class="dropdown">
                                                    <button class="dropdown-toggle inline-flex justify-start items-center font-medium text-success" aria-expanded="false" data-tw-toggle="dropdown">
                                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                                        Available Documents
                                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                                    </button>
                                                    <div class="dropdown-menu w-80">
                                                        <ul class="dropdown-content">
                                                            @foreach($coc->documents as $doc)
                                                            <li>
                                                                <span class="dropdown-item">
                                                                    <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> {{ $doc->display_file_name }}
                                                                    <span class="ml-auto inline-flex justify-end items-center">
                                                                        @if(isset($doc->current_file_name) && !empty($doc->current_file_name) && Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$doc->current_file_name))
                                                                            <a href="{{ Storage::disk('s3')->temporaryUrl('public/students/'.$doc->student_id.'/'.$doc->current_file_name, now()->addMinutes(60)) }}" target="_blank" class="text-success mr-2"><i data-lucide="download-cloud" class="w-4 h-4"></i></a>
                                                                        @endif
                                                                        <a data-cocid="{{ $coc->id }}" data-docid="{{ $doc->id }}" href="javascript:void(0);" target="_blank" class="deleteCOCDoc text-danger"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                                                    </span>
                                                                </span>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <button data-id="{{ $coc->id }}" data-tw-toggle="modal" data-tw-target="#editCOCModal" type="button" class="edit_coc_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                                            <button data-id="{{ $coc->id }}" type="button" class="delete_coc_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else 
                        <div class="alert alert-pending-soft show flex items-center mt-7" role="alert">
                            <span class="inline-flex items-center">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> COC recored not found!
                            </span>
                            <button  data-regid="{{ $regs->id }}" data-atnid="0" data-tw-toggle="modal" data-tw-target="#addCOCModal" type="button" class="addCOCBtn btn btn-linkedin shadow-md ml-auto"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add COC</button>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    @if($undefinedSlcAttendances->count() > 0)
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Attendance at SLC is unspecified</div>
                </div>
                <div class="col-span-6 text-right relative"></div>
            </div>
            <div class="intro-y mt-5">
                <table class="table table-bordered table-sm mt-3" id="undefinedAttendanceTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">ID</th>
                            <th class="whitespace-nowrap">Confirmation Date</th>
                            <th class="whitespace-nowrap">Attendance Semester</th>
                            <th class="whitespace-nowrap">Session Term</th>
                            <th class="whitespace-nowrap">Code</th>
                            <th class="whitespace-nowrap">Note</th>
                            <th class="whitespace-nowrap text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($undefinedSlcAttendances as $atn)
                            <tr>
                                <td>{{ $atn->id }}</td>
                                <td>
                                    <span>
                                        {{ (!empty($atn->confirmation_date) ? date('jS M, Y', strtotime($atn->confirmation_date)) : '') }}
                                        {!! (isset($atn->user->employee->full_name) && !empty($atn->user->employee->full_name) ? 'by '.$atn->user->employee->full_name : '') !!}
                                    </span>
                                </td>
                                <td>
                                    {{ isset($atn->term->name) && !empty($atn->term->name) ? $atn->term->name : '' }}
                                    {{ isset($atn->term->termType->name) && !empty($atn->term->termType->name) ? ' - '.$atn->term->termType->name : '' }}
                                </td>
                                <td>{{ !empty($atn->session_term) ? 'Term '.$atn->session_term : '' }}</td>
                                <td><span class="font-medium">{{ isset($atn->code->code) && !empty($atn->code->code) ? $atn->code->code : '' }}</span></td>
                                <td>{{ !empty($atn->note) ? $atn->note : '' }}</td>
                                <td class="text-right">
                                    @if(!empty($slcRegistrations) && $slcRegistrations->count() > 0)
                                        <div class="dropdown inline-block" data-tw-placement="bottom-end">
                                            <button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="arrow-right-left" class="w-4 h-4"></i></button>
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
    @endif

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">COC at SLC is unspecified</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button  data-regid="0" data-atnid="0" data-tw-toggle="modal" data-tw-target="#addCOCModal" type="button" class="addCOCBtn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add COC</button>
            </div>
        </div>
        <div class="intro-y mt-5">
            <table class="table table-bordered table-sm mt-3">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ID</th>
                        <th class="whitespace-nowrap">Confirmation Date</th>
                        <th class="whitespace-nowrap">Type</th>
                        <th class="whitespace-nowrap">Reason</th>
                        <th class="whitespace-nowrap">Actioned</th>
                        <th class="whitespace-nowrap">Submitted By</th>
                        <th class="whitespace-nowrap">Documents</th>
                        <th class="whitespace-nowrap text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($undefinedSlcCocs) && $undefinedSlcCocs->count() > 0)
                        @foreach($undefinedSlcCocs as $coc)
                            <tr>
                                <td>
                                    {{ $coc->id.(isset($coc->slc_attendance_id) && $coc->slc_attendance_id > 0 ? ' - '.$coc->slc_attendance_id : '') }}
                                </td>
                                <td>
                                    {{ (!empty($coc->confirmation_date) ? date('jS F, Y', strtotime($coc->confirmation_date)) : '') }}
                                </td>
                                <td>{{ $coc->coc_type }}</td>
                                <td>{{ $coc->reason }}</td>
                                <td>{{ ucfirst($coc->actioned) }}</td>
                                <td>{{ (isset($coc->user->employee->full_name) ? $coc->user->employee->full_name : '') }}</td>
                                <td>
                                    @if($coc->documents->count() > 0)
                                        <div class="dropdown">
                                            <button class="dropdown-toggle inline-flex justify-start items-center font-medium text-success" aria-expanded="false" data-tw-toggle="dropdown">
                                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                                                Available Documents
                                                <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                            </button>
                                            <div class="dropdown-menu w-80">
                                                <ul class="dropdown-content">
                                                    @foreach($coc->documents as $doc)
                                                    <li>
                                                        <span class="dropdown-item">
                                                            <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> {{ $doc->display_file_name }}
                                                            <span class="ml-auto inline-flex justify-end items-center">
                                                                @if(isset($doc->current_file_name) && !empty($doc->current_file_name) && Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$doc->current_file_name))
                                                                    <a href="{{ Storage::disk('s3')->temporaryUrl('public/students/'.$doc->student_id.'/'.$doc->current_file_name, now()->addMinutes(60)) }}" target="_blank" class="text-success mr-2"><i data-lucide="download-cloud" class="w-4 h-4"></i></a>
                                                                @endif
                                                                <a data-cocid="{{ $coc->id }}" data-docid="{{ $doc->id }}" href="javascript:void(0);" target="_blank" class="deleteCOCDoc text-danger"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                                            </span>
                                                        </span>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button data-id="{{ $coc->id }}" data-tw-toggle="modal" data-tw-target="#editCOCModal" type="button" class="edit_coc_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                                    <button data-id="{{ $coc->id }}" type="button" class="delete_coc_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    @if(!empty($studentAttendanceIds) && !empty($studentAttendanceIds))
                                        <div class="dropdown inline-block ml-1" data-tw-placement="bottom-end">
                                            <button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="arrow-right-left" class="w-4 h-4"></i></button>
                                            <div class="dropdown-menu w-64">
                                                <ul class="dropdown-content">
                                                    @foreach($studentAttendanceIds as $atnId)
                                                        <li><a href="javascript:void(0);" data-atn="{{ $atnId }}" data-coc="{{ $coc->id }}" class="dropdown-item assignCocToAttendance text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Move to Attendance &nbsp;<strong>#{{ $atnId }}</strong></a></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else 
                        <tr>
                            <td colspan="8" class="text-center">Unspecified COC history not found!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

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
                                        {{ (isset($student->crel->propose->slc_code) && !empty($student->crel->propose->slc_code) ? ' ('.$student->crel->propose->slc_code.')' : '')}}
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Campus</div>
                                    <div class="col-span-8 font-medium">
                                        {{ (isset($student->crel->propose->venue->name) ? $student->crel->propose->venue->name : '') }}
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
                    <div class="modal-footer">
                        @php 
                            $disable = '';
                            if(empty($student->ssn_no) || (!isset($student->crel->creation->slc_code) || empty($student->crel->creation->slc_code)) || (!isset($student->crel->id) || empty($student->crel->id)) || !isset($student->crel->abody->reference) || empty($student->crel->abody->reference)):
                                $disable = ' disabled ';
                            endif;
                        @endphp
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
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
                        <input type="hidden" name="slc_course_code" value="{{ (isset($student->crel->creation->slc_code) ? $student->crel->creation->slc_code : '') }}"/>
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
                        <h2 class="font-medium text-base mr-auto">Add Attendance <span class="font-medium attendanceYear text-success underline"></span></h2>
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
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Attendance <span class="font-medium attendanceYear text-success underline"></span></h2>
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

    <!-- BEGIN: Add COC Modal -->
    <div id="addCOCModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addtCOCForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add COC</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
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
                                    <option value="Resumption">Resumption</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Withdrawal">Withdrawal</option>
                                    <option value="Outstanding">Outstanding</option>
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
                                <div class="flex justify-start items-start relative">
                                    <label for="addCOCDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="addCOCDocument"/>
                                    <span id="addCOCDocumentName" class="documentCOCName ml-5"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
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
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit COC</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
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
                                    <option value="Resumption">Resumption</option>
                                    <option value="Suspension">Suspension</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Withdrawal">Withdrawal</option>
                                    <option value="Outstanding">Outstanding</option>
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
                                <div class="flex justify-start items-start relative">
                                    <label for="editCOCDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="editCOCDocument"/>
                                    <span id="editCOCDocumentName" class="documentCOCName ml-5"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
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