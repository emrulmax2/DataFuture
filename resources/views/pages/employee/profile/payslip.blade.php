@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    
    @include('pages.employee.profile.title-info')
    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-12">
                <div class="intro-y box p-5 pb-7">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Employee Payslip List</div>
                        </div>
                        <div class="col-span-6 text-right">
                            <button id="uploadSync" data-tw-toggle="modal" data-tw-target="#synPaySlipModal" type="button" class="w-auto px-5 py-2 btn btn-primary text-white mr-auto"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Upload P45</button>
                                                
                            <div class="dropdown hidden" id="uploadsDropdown">
                                <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>  Upload P45 <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-72">
                                    <ul class="dropdown-content">
                                        <li><h6 class="dropdown-header">Pending P45</h6></li>
                                        <li><hr class="dropdown-divider mt-0"></li>
                                        @if(isset($RemainpaySlips) && !empty($RemainpaySlips) && count($RemainpaySlips) > 0)
                                            @foreach($RemainpaySlips as $month_year)
                                                <li>
                                                    <div class="form-check dropdown-item">
                                                        <a href="{{ route('hr.attendance.payroll.sync',$month_year) }}" class="inline-flex items-center cursor-pointer" for="employee_doc_{{ $month_year }}"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> {{ $month_year }}</a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else 
                                            <li>
                                                <div class="alert alert-pending-soft show flex items-top mb-1 mt-1" role="alert">
                                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are no pending p45 found!
                                                </div>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <div class="flex p-1">
                                                <button id="uploadSync" data-tw-toggle="modal" data-tw-target="#synPaySlipModal" type="button" class="w-auto btn-sm px-1 py-2 btn btn-primary text-white mr-auto"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Upload P45</button>
                                                <button type="button" id="closeUploadsDropdown" class="btn btn-secondary py-1 px-2 ml-auto">Close</button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12">
                            @if(!empty($holidayYearIds) && count($holidayYearIds) > 0)
                            <div id="employeeHolidayAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                @foreach($holidayYearIds  as $holidayYearId)
                                    @php
                                        $holidayYearData = App\Models\HrHolidayYear::find($holidayYearId);
                                        
                                    @endphp
                                    @if($holidayYearData)
                                    <div class="accordion-item bg-slate-100">
                                        <div id="employeeHolidayHeading-{{ $holidayYearData->id }}" class="accordion-header">
                                            <button class="accordion-button {{ $holidayYearData->active == 1 ? '' : 'collapsed' }} relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayCollapse-{{ $holidayYearData->id }}" aria-expanded="{{ $holidayYearData->active == 1 ? 'true' : 'false' }}" aria-controls="employeeHolidayCollapse-{{ $holidayYearData->id }}">
                                                <span class="font-normal">Tax Year:</span> {{ $holidayYearData->holiday_year }}
                                                <span class="accordionCollaps"></span>
                                            </button>
                                        </div>
                                        <div id="employeeHolidayCollapse-{{ $holidayYearData->id }}" class="accordion-collapse collapse {{ $holidayYearData->active == 1 ? 'show' : '' }}" aria-labelledby="employeeHolidayHeading-{{ $holidayYearData->id }}" data-tw-parent="#employeeHolidayAccordion">
                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                <div id="employeePatternAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                                    <div class="accordion-item bg-white">
                                                        @php
                                                            $uploadRecords = $paySlipUploadSync->where('type', 'Payslips')->where('holiday_year_id', $holidayYearData->id);
                                                        @endphp
                                                        <div id="employeePatternAccordion-payslips" class="accordion-header">
                                                            <button class="accordion-button relative w-full text-lg font-semibold flex" type="button" data-tw-toggle="collapse" data-tw-target="#employeePatternAccordion-collapse-payslips" aria-expanded="false" aria-controls="employeePatternAccordion-collapse-payslips">
                                                                <span class="font-normal">Payslips</span> 
                                                                <span class="accordionCollaps"></span>
                                                            </button>
                                                        </div>
                                                        <div id="employeePatternAccordion-collapse-payslips" class="accordion-collapse collapse show" aria-labelledby="employeePatternAccordion-payslips" data-tw-parent="#employeePatternAccordion-payslips">
                                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                @if($uploadRecords && count($uploadRecords) > 0)
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="whitespace-nowrap uppercase">Month</th>
                                                                                <th class="whitespace-nowrap uppercase">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @foreach($uploadRecords as $key => $record)
                                                                        <tr>
                                                                            {{-- <td class="whitespace-nowrap">{{ $key + 1 }}</td> --}}
                                                                            
                                                                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::createFromFormat('Y-m', $record->month_year)->format('F, Y') }}</td>
                                                                            {{-- <td class="whitespace-nowrap">
                                                                                @if($record->uploadedBy)
                                                                                    {{ $record->uploadedBy->name }}
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </td>
                                                                            <td class="whitespace-nowrap">{{ date('d M, Y', strtotime($record->file_transffered_at)) }}</td> --}}
                                                                            {{-- <td class="whitespace-nowrap">{{ $record->file_name }}</td> --}}
                                                                            <td class="whitespace-nowrap">
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.(in_array(strtolower($record->type ?? ''), ['p45','p60']) ? '/'.strtolower($record->type) : '').'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i>Download</a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                @else
                                                                    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Valid holiday data not found!
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $uploadRecords = $paySlipUploadSync->where('type', 'P45')->where('holiday_year_id', $holidayYearData->id);
                                        @endphp
                                        @if($uploadRecords && count($uploadRecords) > 0)
                                        <div id="employeeP45Collapse-{{ $holidayYearData->id }}" class="accordion-collapse collapse {{ $holidayYearData->active == 1 ? 'show' : '' }}" aria-labelledby="employeeHolidayHeading-{{ $holidayYearData->id }}" data-tw-parent="#employeeHolidayAccordion">
                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                <div id="employeePatternAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                                    <div class="accordion-item bg-white">
                                                        {{-- Next Item --}}
                                                        
                                                        <div id="employeePatternAccordion-P45" class="accordion-header">
                                                            <button class="accordion-button relative w-full text-lg font-semibold flex" type="button" data-tw-toggle="collapse" data-tw-target="#employeePatternAccordion-collapse-P45" aria-expanded="false" aria-controls="employeePatternAccordion-collapse-P45">
                                                                <span class="font-normal">P45</span> 
                                                                <span class="accordionCollaps"></span>
                                                            </button>
                                                        </div>
                                                        <div id="employeePatternAccordion-collapse-P45" class="accordion-collapse collapse show" aria-labelledby="employeePatternAccordion-P45" data-tw-parent="#employeePatternAccordion-P45">
                                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                @if($uploadRecords && count($uploadRecords) > 0)
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="whitespace-nowrap uppercase">Month</th>
                                                                                <th class="whitespace-nowrap uppercase">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @foreach($uploadRecords as $key => $record)
                                                                        <tr>
                                                                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::createFromFormat('Y-m', $record->month_year)->format('F, Y') }}</td>
                                                                            {{-- <td class="whitespace-nowrap">
                                                                                @if($record->uploadedBy)
                                                                                    {{ $record->uploadedBy->name }}
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </td>
                                                                            <td class="whitespace-nowrap">{{ date('d M, Y', strtotime($record->file_transffered_at)) }}</td>
                                                                            <td class="whitespace-nowrap">{{ $record->file_name }}</td> --}}
                                                                            <td class="whitespace-nowrap">
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.(in_array(strtolower($record->type ?? ''), ['p45','p60']) ? '/'.strtolower($record->type) : '').'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i> Download</a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                @else
                                                                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                                                                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> P45 data not found!
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        {{-- End of Next item --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        @php
                                            $uploadRecords = $paySlipUploadSync->where('type', 'P60');
                                        @endphp
                                        @if($uploadRecords && count($uploadRecords) > 0)
                                        <div id="employeeP60Collapse-{{ $holidayYearData->id }}" class="accordion-collapse collapse {{ $holidayYearData->active == 1 ? 'show' : '' }}" aria-labelledby="employeeHolidayHeading-{{ $holidayYearData->id }}" data-tw-parent="#employeeHolidayAccordion">
                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                <div id="employeePatternAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                                    <div class="accordion-item bg-white">
                                                        {{-- Next Item --}}
                                                        
                                                        <div id="employeePatternAccordion-P60" class="accordion-header">
                                                            <button class="accordion-button relative w-full text-lg font-semibold flex" type="button" data-tw-toggle="collapse" data-tw-target="#employeePatternAccordion-collapse-P60" aria-expanded="false" aria-controls="employeePatternAccordion-collapse-P60">
                                                                <span class="font-normal">P60</span> 
                                                                <span class="accordionCollaps"></span>
                                                            </button>
                                                        </div>
                                                        <div id="employeePatternAccordion-collapse-P60" class="accordion-collapse collapse show" aria-labelledby="employeePatternAccordion-P60" data-tw-parent="#employeePatternAccordion-P60">
                                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                @if($uploadRecords && count($uploadRecords) > 0)
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                {{-- <th class="whitespace-nowrap uppercase">S/N</th> --}}
                                                                                <th class="whitespace-nowrap uppercase">Month</th>
                                                                                {{-- <th class="whitespace-nowrap uppercase">Uploaded By</th>
                                                                                <th class="whitespace-nowrap uppercase">Upload Date</th>
                                                                                <th class="whitespace-nowrap uppercase">File Name</th> --}}
                                                                                <th class="whitespace-nowrap uppercase">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @foreach($uploadRecords as $key => $record)
                                                                        <tr>
                                                                            {{-- <td class="whitespace-nowrap">{{ $key + 1 }}</td> --}}
                                                                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::createFromFormat('Y-m', $record->month_year)->format('F, Y') }}</td>
                                                                            {{--
                                                                            <td class="whitespace-nowrap">
                                                                                @if($record->uploadedBy)
                                                                                    {{ $record->uploadedBy->name }}
                                                                                @else
                                                                                    N/A
                                                                                @endif
                                                                            </td>
                                                                            <td class="whitespace-nowrap">{{ date('d M, Y', strtotime($record->file_transffered_at)) }}</td>
                                                                            <td class="whitespace-nowrap">{{ $record->file_name }}</td> --}}
                                                                            <td class="whitespace-nowrap">
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.(in_array(strtolower($record->type ?? ''), ['p45','p60']) ? '/'.strtolower($record->type) : '').'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i>Download</a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                @else
                                                                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                                                                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> P60 data not found!
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        {{-- End of Next item --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @else
                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No payslip upload records found!
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
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
    <!-- BEGIN: Add synPaySlipModal Modal -->
        <div id="synPaySlipModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ">
                
                    <div class="modal-content">
                        <div class="modal-body p-0"><a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                            <i data-tw-merge data-lucide="x" class="stroke-1.5  h-8 w-8 text-slate-400"></i>
                        </a>
                            <div class="p-5 text-center">
                                <i data-lucide="badge-pound-sterling" class="w-16 h-16 text-success mx-auto mt-3"></i>
                                <div class="text-3xl mt-5 ">Upload P45</div>
                                <div class="text-slate-500 mt-2 ">Please Upload P45 from below</div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <form method="post"  action="{{ route('hr.attendance.payslip.upload.eid') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                                        @csrf    
                                        <div class="fallback">
                                            <input name="documents[]"  type="file" />
                                        </div>
                                        <div class="dz-message" data-dz-message>
                                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                                            <div class="text-slate-500">
                                                Upload a pdf file
                                            </div>
                                        </div>
                                        <input type="hidden" name="dir_name" value=""/>
                                        <input type="hidden" name="type" value="P45"/>
                                        <input type="hidden" name="holiday_year_info" value=""/>
                                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                                        <input type="hidden" name="user_id" value="{{ $employee->user_id }}"/>
                                    </form>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3 hidden">
                                    <select id="type" name="typePaySlip" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Type</option>
                                        
                                        <option selected value="P45">P45</option>
                                    </select> 
                                    <div class="acc__input-error error-type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_year" name="holiday_year_id" class="lccTom lcc-tom-select w-full text-left">
                                        <option value="">Please Select Year</option>
                                        @foreach($holiday_years as $list)
                                            <option value="{{ $list->id }}">{{ date('Y', strtotime($list->start_date)).' - '.date('Y', strtotime($list->end_date)) }}</option>
                                        @endforeach
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_month" name="holiday_month" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Month</option>
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="px-5 pb-8 text-center">
                                <button id="uploadEmpDocBtn" type="button" class="btn btn-success w-24 EmpSyncBtn">Save<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
                                </svg></button>
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    <!-- END: synPaySlipModal Modal -->
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
                        <button type="button" data-date="" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    
    @vite('resources/js/hr-p45slipsync.js')
@endsection