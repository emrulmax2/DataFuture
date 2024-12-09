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
                        <th class="border-none whitespace-no-wrap"><input id="checkbox-switch-all" data-tw-merge type="checkbox" class="checkbox-switch-all transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  value="" />
                            <label data-tw-merge for="checkbox-switch-all" class="cursor-pointer ml-2">S.N.</label></th>
                        <th class="border-none whitespace-no-wrap">Payslip Name</th>
                        <th class="border-none whitespace-no-wrap">Employee</th>
                        <th class="border-none whitespace-no-wrap">Action</th>
                    </tr>
                </thead>
                
                <tbody>
                    @php $i = 0; $serial=1; @endphp
                    @foreach ($paySlipUploadSync as $paySlip)
                    @php
                        $warningCheck = "transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-warning focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-warning [&[type='radio']]:checked:border-warning [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-warning [&[type='checkbox']]:checked:border-warning [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                        $primaryCheck ="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                        $checkboxCssClass = (isset($data['id'])) ? $warningCheck : $primaryCheck ; 
                    @endphp
                        <tr id="tr_id_{{ $paySlip->id }}" class="{{ isset($paySlip->employee) ?  $success : $danger }}" >
                            <td class="px-5 py-3 {{ isset($paySlip->employee) ? 'text-green-800' : 'text-danger' }} dark:border-darkmode-300  border-r border-b">
                                <input data-tw-merge type="checkbox" name="id[{{ $serial }}]" 
                                class="fill-box {{ $checkboxCssClass }}" id="checkbox-switch-{{ $serial }}" value="{{ $paySlip->id }}" />
                                <label data-tw-merge for="checkbox-switch-{{ $serial }}" class="cursor-pointer ml-2">{{ $serial }}</label>
                               @php $serial++; @endphp
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
            <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#confirmDeleteModal" id="deleteBtnAll" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-danger focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-danger text-danger dark:border-danger [&:hover:not(:disabled)]:bg-danger/10 mb-2 mr-1 inline-block w-48">Delete All</button>
                   
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

        <!-- BEGIN: Plan Task  Confirm Modal Content -->
        <div id="confirmDeleteModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <i data-lucide="info" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 confModTitle">Do you want to Delete?</div>
                            <div class="text-slate-500 mt-2 confModDesc">Please make sure before deletion. it is parmanent.</div>
                        </div>
                        <form id="resultDeleteAllForm" method="post">
                            @csrf
                            <div class="append-input">
                                <input type="hidden" name="ids[]" value=""/>
                            </div>
                            <div class="px-5 pb-8 text-center">
                                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                                <button type="submit" data-id="0" data-action="none" class="update btn btn-danger w-auto">Yes, I agree <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Plan Task Confirm Modal Content -->

    <!-- END: Success Modal Content -->
@endsection

@section('script')
@vite('resources/js/hr-payslipsync-show.js')
@endsection