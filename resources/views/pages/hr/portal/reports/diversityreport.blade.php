@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Diversity Information Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">    
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-DR">
            <div class="grid grid-cols-12 gap-4 gap-y-5">
                <div class="col-span-3">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-diversity" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id"> 
                        <option value="" selected>Please Select</option>
                        @if($employeeWorkType->count() > 0)
                            @foreach($employeeWorkType as $si)
                                <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Department</label>
                    <select id="department_id-diversity" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-2">
                    <label class="form-label">Startdate</label>
                    <input type="text" id="startdate-DR" name="startdate-DR" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>                   
                </div>
                <div class="col-span-2">
                    <label class="form-label">Enddate</label>
                    <input type="text" id="enddate-DR" name="enddate-DR" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>                    
                </div>
                <div class="col-span-3">
                    <label class="form-label">Ethnicity</label>
                    <select id="ethnicity-DR" name="ethnicity" class="lccTom lcc-tom-select w-full form-control">
                        <option value="" selected>Please Select</option>
                        @if(!empty($ethnicity))
                            @foreach($ethnicity as $n)
                                <option {{ isset($employee->ethnicity_id) && $employee->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                            @endforeach 
                        @endif 
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="form-label">Nationality</label>
                    <select id="nationality-DR" name="nationality" class="lccTom lcc-tom-select w-full form-control">
                        <option value="" selected>Please Select</option>
                        @if(!empty($country))
                            @foreach($country as $n)
                                <option {{ isset($employee->country_id) && $employee->country_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                            @endforeach 
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Gender</label>
                    <select id="gender-DR" name="gender" class="lccTom lcc-tom-select w-full form-control">
                        <option value="" selected>Please Select</option>
                        @if(!empty($gender))
                            @foreach($gender as $n)
                                <option {{ isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                            @endforeach 
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Status</label>
                    <select id="status_id-DR" name="status_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select> 
                </div>
                <div class="col-span-2 mt-7">
                    <button id="tabulator-html-filter-go-DR" type="button" class="btn btn-primary w-auto" >Go</button>
                    <button id="tabulator-html-filter-reset-DR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-3 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end"> 
                        <button style="" id="tabulator-export-xlsx-DR" href="javascript:;" class="btn btn-outline-secondary w-1/2 w-auto mr-2">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                        </button>
                        <button id="diversitybySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none">Download Pdf</button>
                        <a type="button" href="{{ route('hr.portal.reports.diversityreport.pdf') }}" id="allDiversityReportPdf" class="btn btn-success text-white mr-2 w-1/2 w-auto">Download Pdf</a>                       
                    </div>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="diversityListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-diversityreport.js')
@endsection