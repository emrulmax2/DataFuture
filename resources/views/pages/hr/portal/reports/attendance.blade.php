@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to List</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="attendanceReportForm" method="post" action="#">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Month <span class="text-danger">*</span></label>
                    <input readonly type="text" id="the_month" name="the_month" placeholder="MM-YYYY" value="{{ date('m-Y') }}" class="w-full form-control"/>                    
                </div>
                <div class="col-span-3">
                    <label class="form-label">Department</label>
                    <select id="department_id" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-3">
                    <label class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>All Employee</option>             
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-3 text-right mt-7">
                    <button type="submit" id="generateReport" class="btn btn-primary w-auto">     
                        Generate Report                      
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
                    <button id="downloadExcel" type="button" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Download Excel</button>
                    <button id="resetForm" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden attendanceReportWrap mt-7" style="display: none;"></div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report.js')
@endsection