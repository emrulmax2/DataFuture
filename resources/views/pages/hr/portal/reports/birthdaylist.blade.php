@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Birthday List Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md">Back to Employment Reports</a>
        </div>
    </div>
    
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-BR">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" id="dob-BR" name="dob-BR" placeholder="Select Month of Birth" value="" data-format="YYYY-MM"  data-single-mode="true" class="w-full datepicker"/>
                </div>
                <div class="col-span-4">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-birtdaylist" class="lccToms tom-selects w-full" name="employee_work_type_id">    
                        <option value="" selected>Please Select</option> 
                        @if($employeeWorkType->count() > 0)
                            @foreach($employeeWorkType as $si)
                                <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="form-label">Department</label>
                    <select id="department_id-birtdaylist" name="department_id" class="w-full lccToms tom-selects">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-4">
                    <label class="form-label">Status</label>
                    <select id="status_id-birtdaylist" name="status_id" class="w-full lccToms tom-selects">     
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select> 
                </div>
                <div class="col-span-4 mt-7">
                    <button id="tabulator-html-filter-go-BR" type="button" class="btn btn-primary w-auto">Go</button>
                    <button id="tabulator-html-filter-reset-BR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-4 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end"> 
                        <button id="bdayListbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
                        <button id="bdayListbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none">Download Pdf</button>
                        <a href="{{route('hr.portal.reports.birthdaylist.excel')}}" id="allBdayListExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
                        <a href="{{route('hr.portal.reports.birthdaylist.pdf')}}" id="allBdayListPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>                      
                    </div>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden w-full">
            <div id="birthdayListSearchTable" class="mt-5 table-report table-report--tabulator"></div>
        </div> 
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-birthdayreport.js')
@endsection