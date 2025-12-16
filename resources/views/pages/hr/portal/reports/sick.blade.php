@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee's Sick Leave</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Report</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="#">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id[]" multiple class="lccTom lcc-tom-select w-full form-control">
                        @if(!empty($employees))
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach 
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Days</label>
                    <select id="no_of_days" name="no_of_days" class="w-full form-control">
                        <option value="">Please Select</option>
                        <option selected value="30">Last 30 Days</option>
                        <option value="45">Last 45 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-2 xl:col-span-2">
                    <label class="form-label">From</label>
                    <input readonly required type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-2 xl:col-span-2">
                    <label class="form-label">To</label>
                    <input readonly required type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-4 xl:col-span-4 text-right mt-7">
                    <button type="button" id="searchSickLeave" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" ><i data-lucide="search" class="w-4 h-4 mr-2"></i>Search</button>
                    <button type="button" style="display: none;" id="exportSickLeave" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export XL
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                    
                </div>
            </div>
        </form>
        
        <div class="empSickLeaveWrap overflow-x-auto scrollbar-hidden mt-5" style="display: none;">
            <div id="empSickLeaveListTable" class="table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-employee-sick-leave-report.js')
@endsection