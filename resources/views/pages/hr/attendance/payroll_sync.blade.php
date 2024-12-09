@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
@php
    use App\Models\HrHolidayYear;
    // Check if the $month_year contains 'P45' or 'P60'
    $containsP45 = strpos($month_year, 'P45') !== false;
    $containsP60 = strpos($month_year, 'P60') !== false;
    if ($containsP45 || $containsP60) {
        $content = explode('_', $month_year);
        $formattedDate = $month_year;
        $holidayYear = HrHolidayYear::find($content[1]);
    } else {
        $date = DateTime::createFromFormat('Y-m', $month_year);
        $formattedDate = $date->format('F Y'); // 'F' for full month name, 'Y' for full year
    }
    
    

    
@endphp
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        @if ($containsP45 || $containsP60) 
        
        <h2 class="text-lg font-medium mr-auto">{{ $content[0] }} for <u>{{ date('Y', strtotime($holidayYear->start_date)).' - '.date('Y', strtotime($holidayYear->end_date)) }}</u></h2>
        @else
        
        <h2 class="text-lg font-medium mr-auto">Payslips for <u>{{ $formattedDate }}</u></h2>
        @endif
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.attendance') }}" class="btn btn-primary shadow-md mr-2"><i data-lucide="arrow-left" class="w-4 h-4 mx-2"></i> Back to Attendance</a>
            
            
        </div>
    </div>
    @php
       $danger ="relative border-none rounded-md bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger dark:border-danger dark:border-opacity-20 ";
       $success ="relative border-none bg-success border-success bg-opacity-20 border-opacity-5 text-success dark:border-success dark:border-opacity-20"
       
    @endphp
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        {{-- <div class="overflow-x-auto scrollbar-hidden">
            <div id="hrPayslipListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div> --}}
        <form action="{{ route('payslip-upload.store') }}" method="post" id="hrPayslipSyncForm">
            <table id="hrPayslipSyncTable" class="table table-report table-report--tabulator">
                <thead>
                    <tr>
                        <th class="border-none whitespace-no-wrap">ID</th>
                        <th class="border-none whitespace-no-wrap">Payslip Name</th>
                        <th class="border-none whitespace-no-wrap">Employee</th>
                        <th class="border-none whitespace-no-wrap">Action</th>
                    </tr>
                </thead>
                
                <tbody>
                    @php $i = 0; $serial=1; @endphp
                    @foreach ($paySlipUploadSync as $paySlip)
                    @php
                        $employeeFound = 0;
                        $employeeList = \App\Models\Employee::all();
                        foreach($employeeList as $employee) {
                            
                            if($paySlip->file_name == $fileName && $paySlip->employe_id != null) {
        
                                $employeeFound = $pay->employe_id;
                                break;
                            }
                            
                        }
                    @endphp
                        <tr id="tr_id_{{ $paySlip->id }}" class="{{ isset($paySlip->employee) ?  $success : $danger }}" >
                            <td class="px-5 py-3 {{ isset($paySlip->employee) ? 'text-green-800' : 'text-danger' }} dark:border-darkmode-300  border-r border-b">
                                <div class="font-medium whitespace-no-wrap">{{ $serial++ }}</div>
                                <input type="hidden" name="id[]" value="{{ $paySlip->id }}">
                            </td>
                            <td class="px-5 py-3 {{ isset($paySlip->employee) ? 'text-green-800' : 'text-danger' }} dark:border-darkmode-300  border-r border-b">
                                <div class="font-medium whitespace-no-wrap">{{ $paySlip->file_name }}</div>
                            </td>
                            <td class="px-5 py-3 {{ isset($paySlip->employee) ? 'text-green-800' : 'text-danger' }} dark:border-darkmode-300  border-r border-b">

                                        <select id="employee_id_{{ $paySlip->id }}" class="lccTom lcc-tom-select w-full " name="employee_id[]">
                                            <option value="">Please Select</option>
                                                @foreach($employees as $data)
                                                @php
                                                // $html = '<div class="flex justify-start items-center">';
                                                //     $html .= '<div class="w-10 h-10 intro-x image-fit mr-5">';
                                                //         $html .= '<img alt="#" class="rounded-full shadow" src="'.$data->photo_url.'">';
                                                //     $html .= '</div>';
                                                //     $html .= '<div>';
                                                //         $html .= '<div class="font-medium whitespace-nowrap">'.$data->full_name.'</div>';
                                                //         $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.($data->status!=1 ? " - InActive" : " - Active" ). ' - ' .($data->id).'</div>';
                                                //     $html .= '</div>';
                                                // $html .= '</div>';
                                                @endphp
                                                    <option {{ isset($paySlip->employee) && ($paySlip->employee->id ==$data->id) ? "selected" : ""  }} value="{{ $data->id }}"
                                                        data-photo-url="{{ $data->photo_url }}" 
                                                        data-status="{{ $data->status }}" 
                                                        data-id="{{ $data->id }}" 
                                                        {{ isset($paySlip->employee) && ($paySlip->employee->id == $data->id) ? "selected" : "" }} 
                                                        value="{{ $data->id }}"
                                                    >
                                                        {{ $data->full_name }}
                                                    </option>

                                                    </option>
                                                @endforeach
                                        </select>
                                        <div class="acc__input-error error-employee_id text-danger mt-2"></div>
                            </td>
                            <td class="px-5 py-3 {{ isset($paySlip->employee) ? 'text-green-800' : 'text-danger' }} dark:border-darkmode-300  border-r border-b">
                                <span data-tw-target="#confirmModal" data-tw-toggle="modal" data-id={{ $paySlip->id}} class="delete_btn inline-flex cursor-pointer"><i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>Delete</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
    </div>
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto"></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.attendance') }}" class="btn btn-primary shadow-md mr-2"><i data-lucide="arrow-left" class="w-4 h-4 mx-2"></i> Back to Attendance</a>
            
            <button id="hrPaySlipBtn"  class="btn btn-outline-success shadow-md mr-2 w-36"><i data-lucide="check-circle" class="w-4 h-4 mx-2"></i> Confirm All <i data-loading-icon="oval" class="loading w-4 h-4 ml-2 hidden"></i></a>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    <!-- END: Success Modal Content -->
@endsection

@section('script')
@vite('resources/js/hr-payslipsync-show.js')
@endsection