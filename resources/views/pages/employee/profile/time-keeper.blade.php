@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->title->name.' '.$employee->full_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Time Keeping</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
            </div>
        </div>
        <div class="intro-y mt-5">
            @if(!empty($empAttendances))
                <div id="employeeTKYear" class="lcc_custom_accordion">
                    @foreach($empAttendances as $year_id => $year)
                        <div class="lcc_accordion_item mb-1">
                            <button class="lcc_accordion_button relative w-full text-lg font-semibold bg-slate-100 p-5 text-left" type="button" data-target="#employeeTKYear_{{ $year_id }}">
                                Year: {{ date('Y', strtotime($year['start_date'])).' - '.date('y', strtotime($year['end_date'])) }} 
                                <span class="accordionCollaps"></span>
                            </button>
                            <div id="employeeTKYear_{{ $year_id }}" class="lcc_accordion_body text-slate-600 dark:text-slate-500 leading-relaxed p-5" style="display: none;">
                                @if(!empty($year['month']))
                                    <div id="employeeMonthAttendances_{{ $year_id }}" class="employee_month_attendance_accordion">
                                        @foreach($year['month'] as $key => $month)
                                            <div class="lcc_month_accordion_item mb-1">
                                                <button class="lcc_month_accordion_button relative w-full text-lg font-semibold bg-slate-100 p-5 text-left" type="button" data-target="#employeeTKMonth_{{ $year_id }}_{{ $key }}">
                                                    {{ date('F Y', strtotime($month['start_date'])) }} 
                                                    <span class="accordionCollaps"></span>
                                                </button>
                                                <div id="employeeTKMonth_{{ $year_id }}_{{ $key }}" class="lcc_month_accordion_body text-slate-600 dark:text-slate-500 leading-relaxed p-5" style="display: none;">
                                                    @if(!empty($month['attendances']) && $month['attendances']->count() > 0)
                                                        <div class="grid grid-cols-12 gap-0">
                                                            <div class="col-span-12 text-right mb-5">
                                                                <a href="{{ route('employee.time.keeper.download.pdf', [$employee->id, $month['start_date']]) }}" class="btn btn-success text-white"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                                            </div>
                                                        </div>
                                                        <div class="overflow-x-auto">
                                                            <table class="table table-bordered table-sm">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="whitespace-nowrap">Date</th>
                                                                        <th class="whitespace-nowrap">Status</th>
                                                                        <th class="whitespace-nowrap">Note</th>
                                                                        <th class="whitespace-nowrap">Clock In - Out</th>
                                                                        <th class="whitespace-nowrap">Break</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($month['attendances'] as $attn)
                                                                        @php 
                                                                            $note = [];
                                                                            $clockin_punch = (isset($attn->clockin_punch) && !empty($attn->clockin_punch) && $attn->clockin_punch != '00:00' ? $attn->clockin_punch.':00' : '');
                                                                            $clockin_contract = (isset($attn->clockin_contract) && !empty($attn->clockin_contract) && $attn->clockin_contract != '00:00' ? $attn->clockin_contract.':00' : '');
                                                                            $clockin_system = (isset($attn->clockin_system) && !empty($attn->clockin_system) && $attn->clockin_system != '00:00' ? $attn->clockin_system.':00' : '');
                                                                            
                                                                            $clockout_punch = (isset($attn->clockout_punch) && !empty($attn->clockout_punch) && $attn->clockout_punch != '00:00' ? $attn->clockout_punch.':00' : '');
                                                                            $clockout_contract = (isset($attn->clockout_contract) && !empty($attn->clockout_contract) && $attn->clockout_contract != '00:00' ? $attn->clockout_contract.':00' : '');
                                                                            $clockout_system = (isset($attn->clockout_system) && !empty($attn->clockout_system) && $attn->clockout_system != '00:00' ? $attn->clockout_system.':00' : '');
                                                                            if($attn->total_work_hour > 0 && ($attn->leave_status == 0 || empty($attn->leave_status)) && $attn->overtime_status != 1):
                                                                                if(!empty($clockin_punch) && !empty($clockin_contract)):
                                                                                    $lastIn = date('H:i', strtotime('+'.$clockin.' minutes', strtotime($clockin_contract))).':00';
                                                                                    if($clockin_punch > $lastIn):
                                                                                        $note[] = 'Late - '.$clockin_punch.' - '.$lastIn;
                                                                                    endif;
                                                                                endif;
                                                                                if(!empty($clockout_punch) && !empty($clockout_contract)):
                                                                                    $earlyLeave = date('H:i', strtotime('-'.$clockout.' minutes', strtotime($clockout_contract))).':00';
                                                                                    if($clockout_punch < $earlyLeave):
                                                                                        $note[] = 'Leave Early';
                                                                                    endif;
                                                                                elseif(empty($clockout_punch) && !empty($clockout_contract)):
                                                                                    $note[] = 'Clock Out Not Found';
                                                                                endif;
                                                                                if(empty($attn->total_break) || $attn->total_break == 0):
                                                                                    $note[] = 'Break Not Found';
                                                                                endif;
                                                                            elseif($attn->total_work_hour > 0 && (!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && ($attn->leave_status == 1 && !empty($attn->leave_status)) && $attn->overtime_status != 1):
                                                                                if(!empty($clockin_punch) && !empty($clockin_contract)):
                                                                                    $lastIn = date('H:i', strtotime('+'.$clockin.' minutes', strtotime($clockin_contract))).':00';
                                                                                    if($clockin_punch > $lastIn):
                                                                                        $note[] = 'Late';
                                                                                    endif;
                                                                                endif;
                                                                                if(!empty($clockout_punch) && !empty($clockout_contract)):
                                                                                    $earlyLeave = date('H:i', strtotime('-'.$clockout.' minutes', strtotime($clockout_contract))).':00';
                                                                                    if($clockout_punch < $earlyLeave):
                                                                                        $note[] = 'Leave Early';
                                                                                    endif;
                                                                                elseif(empty($clockout_punch) && !empty($clockout_contract)):
                                                                                    $note[] = 'Clock Out Not Found';
                                                                                endif;
                                                                                if(empty($attn->total_break) || $attn->total_break == 0):
                                                                                    $note[] = 'Break Not Found';
                                                                                endif;
                                                                                if($attn->leave_status == 1):
                                                                                    $note[] = 'Holiday / Vacation'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                                endif;
                                                                            elseif($attn->leave_status == 1 && (empty($attn->clockin_punch) || $attn->clockin_punch == '00:00')):
                                                                                $note[] = 'Holiday / Vacation'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                            elseif($attn->leave_status == 2):
                                                                                $note[] = 'Unauthorised Absent'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                            elseif($attn->leave_status == 5):
                                                                                $note[] = 'Authorised Paid'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                            elseif($attn->leave_status == 4):
                                                                                $note[] = 'Authorised Unpaid'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                            elseif($attn->leave_status == 3):
                                                                                $note[] = 'Sick'.(isset($attn->leaveDay->leave->note) && !empty($attn->leaveDay->leave->note) ? ': '.$attn->leaveDay->leave->note : '');
                                                                            elseif($attn->overtime_status = 1):
                                                                                $note[] = 'Overtime';
                                                                            endif;
                                                                        @endphp
                                                                        <tr class="timeKeepingRow timeKeepingRow_{{ ($attn->leave_status > 0 ? $attn->leave_status : ($attn->overtime_status == 1 ? 'ov' : 0)) }}" data-id="{{ $attn->id }}">
                                                                            <td class="font-medium w-72">
                                                                                {{ date('jS F, Y, l', strtotime($attn->date)) }}<br/>
                                                                                {{ (isset($attn->clockin_contract) && !empty($attn->clockin_contract) ? $attn->clockin_contract : '').(isset($attn->clockout_contract) && !empty($attn->clockout_contract) ? ' - '.$attn->clockout_contract : '') }}
                                                                            </td>
                                                                            <td>
                                                                                @if($attn->total_work_hour > 0 && ($attn->leave_status == 0 || empty($attn->leave_status)))
                                                                                    Worked: {{ $attn->work_hour }}
                                                                                @elseif($attn->total_work_hour > 0 && (!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && ($attn->leave_status == 1) && !empty($attn->leave_status)))
                                                                                    Worked: {{ $attn->work_hour }}<br/>
                                                                                    Holiday: {{ $attn->leaves_hour }}
                                                                                @elseif($attn->leave_status == 1 && (empty($attn->clockin_punch) || $attn->clockin_punch == '00:00'))
                                                                                    Holiday: {{ $attn->leaves_hour }}
                                                                                @elseif($attn->leave_status == 5)
                                                                                    Authorised Paid: {{ $attn->leaves_hour }}
                                                                                @elseif($attn->leave_status == 4)
                                                                                    Authorise Unpaid
                                                                                @elseif($attn->leave_status == 3)
                                                                                    Sick
                                                                                @elseif($attn->leave_status == 2)
                                                                                    Unauthorised Absent
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                {{ implode(', ', $note) }}
                                                                            </td>
                                                                            <td>
                                                                                @if($attn->total_work_hour > 0 && ($attn->clockin_punch != '' && $attn->clockin_punch != '00:00'))
                                                                                    {{ 'A: '.$attn->clockin_punch.' - '.$attn->clockout_punch }}<br/>
                                                                                    {{ 'S: '.$attn->clockin_system.' - '.$attn->clockout_system }}
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if((!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && $attn->total_work_hour > 0)
                                                                                    {{ $attn->break_time }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>  
                                        @endforeach
                                    </div>
                                @else 

                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else 

            @endif
        </div>
    </div>

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/employee-time-keeping.js')
@endsection