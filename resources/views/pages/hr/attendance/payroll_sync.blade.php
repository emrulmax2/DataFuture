@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">PaySlip Sync Of <u>{{ $month_year }}</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.attendance') }}" class="btn btn-primary shadow-md mr-2"><i data-lucide="arrow-left" class="w-4 h-4 mx-2"></i> Back to Attendance</a>
            
            <button id="hrPaySlipBtn"  class="btn btn-outline-success shadow-md mr-2 w-36"><i data-lucide="check-circle" class="w-4 h-4 mx-2"></i> Confirm All <i data-loading-icon="oval" class="loading w-4 h-4 ml-2 hidden"></i></a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        {{-- <div class="overflow-x-auto scrollbar-hidden">
            <div id="hrPayslipListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div> --}}
        <form action="{{ route('payslip-upload.store') }}" method="post" id="hrPayslipSyncForm">
            <table class="table table-report table-report--tabulator">
                <thead>
                    <tr>
                        <th class="border-b-2 whitespace-no-wrap">ID</th>
                        <th class="border-b-2 whitespace-no-wrap">Payslip Name</th>
                        <th class="border-b-2 whitespace-no-wrap">Designated Employee</th>
                        <th class="border-b-2 whitespace-no-wrap">Payslip Month</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 0; $serial=1; @endphp
                    @foreach ($paySlipUploadSync as $paySlip)
                        <tr >
                            <td class="border-b {{ isset($paySlip->employee) ? 'border-teal-400 text-teal-800' : 'border-red-600 text-red-700' }}">
                                <div class="font-medium whitespace-no-wrap">{{ $serial++ }}</div>
                                <input type="hidden" name="id[]" value="{{ $paySlip->id }}">
                            </td>
                            <td class="border-b {{ isset($paySlip->employee) ? 'border-teal-400 text-teal-800' : 'border-red-600 text-red-700' }}">
                                <div class="font-medium whitespace-no-wrap">{{ $paySlip->file_name }}</div>
                            </td>
                            <td class="border-b {{ isset($paySlip->employee) ? 'border-teal-400 text-teal-800' : 'border-red-600 text-red-700' }}">
                                {{-- implent employee form dropdown list --}}
                                        <select id="employee_id" class="lccTom lcc-tom-select w-full " name="employee_id[]">
                                            <option value="">Please Select</option>
                                                @foreach($employees as $data)
                                                    <option {{ isset($paySlip->employee) && ($paySlip->employee->id ==$data->id) ? "selected" : ""  }} value="{{ $data->id }}">{{ $data->full_name }}</option>
                                                @endforeach
                                        </select>
                                        <div class="acc__input-error error-employee_id text-danger mt-2"></div>
                                    
                            </td>
                            <td class="border-b {{ isset($paySlip->employee) ? 'border-teal-400 text-teal-800' : 'border-red-600 text-red-700' }}">
                                <div class="font-medium whitespace-no-wrap">{{ $paySlip->month_year }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </form>
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
@endsection

@section('script')
@vite('resources/js/hr-payslipsync-show.js')
@endsection