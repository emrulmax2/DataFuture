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
                                    <div class="accordion-item">
                                        <div id="employeeHolidayHeading-{{ $holidayYearData->id }}" class="accordion-header">
                                            <button class="accordion-button font-medium" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayCollapse-{{ $holidayYearData->id }}" aria-expanded="true" aria-controls="employeeHolidayCollapse-{{ $holidayYearData->id }}">
                                                Payslip Uploads for Holiday Year: {{ $holidayYearData->holiday_year }}
                                            </button>
                                        </div>
                                        <div id="employeeHolidayCollapse-{{ $holidayYearData->id }}" class="accordion-collapse collapse show" aria-labelledby="employeeHolidayHeading-{{ $holidayYearData->id }}" data-tw-parent="#employeeHolidayAccordion">
                                            <div class="accordion-body">
                                                <div class="overflow-x-auto">
                                                    @php
                                                        $uploadRecords = $paySlipUploadSync->where('holiday_year_id', $holidayYearData->id)->where('employee_id', $employee->id)->sortByDesc('created_at');
                                                    @endphp
                                                    @if($uploadRecords && count($uploadRecords) > 0)
                                                        <table class="table table-bordered table-hover table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th class="whitespace-nowrap">#</th>
                                                                    <th class="whitespace-nowrap">Uploaded By</th>
                                                                    <th class="whitespace-nowrap">Upload Date</th>
                                                                    <th class="whitespace-nowrap">File Name</th>
                                                                    <th class="whitespace-nowrap">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach($uploadRecords as $key => $record)
                                                            <tr>
                                                                <td class="whitespace-nowrap">{{ $key + 1 }}</td>
                                                                <td class="whitespace-nowrap">
                                                                    @if($record->uploadedBy)
                                                                        {{ $record->uploadedBy->name }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="whitespace-nowrap">{{ date('d M, Y', strtotime($record->created_at)) }}</td>
                                                                <td class="whitespace-nowrap">{{ $record->file_name }}</td>
                                                                <td class="whitespace-nowrap">
                                                                    <a href="{{ asset('storage/payslip_uploads/' . $record->file_name) }}" target="_blank" class="btn btn-primary btn-sm"><i data-lucide="eye" class="w-4 h-4 mr-2"></i>View</a>
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