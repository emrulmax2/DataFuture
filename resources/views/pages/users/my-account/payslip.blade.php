@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My HR</h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
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
                                                            $uploadRecords = $paySlipUploadSync->where('type', 'Payslips');
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
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i>Download</a>
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
                                            $uploadRecords = $paySlipUploadSync->where('type', 'P45');
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
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i> Download</a>
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
                                                                                <a href="{{ Storage::disk('s3')->temporaryUrl('public/employee_payslips/'.$record->month_year.'/'.$record->file_name, now()->addMinutes(120)) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="download" class="w-4 h-4 mr-2"></i>Download</a>
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

@endsection

@section('script')
    @vite('resources/js/employee-global.js')
@endsection