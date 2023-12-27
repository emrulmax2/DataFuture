@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Starter Information</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">         
            <button id="tabulator-export-xlsx-SR" href="javascript:;" class="btn btn-outline-secondary w-1/2 w-auto mr-2">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
            </button>
            <button id="starterreportbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none">Download Pdf</button>
            <a type="button" id="starterreportPdfBtn" href="{{ route('hr.portal.reports.starterreport.pdf') }}" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-SR">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Type</div>
                        <select id="employee_work_type_id-starter" class="lccTom lcc-tom-select w-full" name="employee_work_type_id"> 
                            <option value="" selected>Please Select</option>
                            @if($employeeWorkType->count() > 0)
                                @foreach($employeeWorkType as $si)
                                    <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Department</div>
                        <select id="department_id-starter" name="department_id" class="w-full lccTom lcc-tom-select">     
                            <option value="" selected>Please Select</option>             
                            @foreach($departments as $si)
                                <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                            @endforeach
                        </select> 
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Startdate</div>
                        <input type="text" id="startdate-starter" name="startdate-starter" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Enddate</div>
                        <input type="text" id="enddate-starter" name="enddate-starter" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Ethnicity</div>
                        <select id="ethnicity-starter" name="ethnicity" class="lccTom lcc-tom-select w-full">
                            <option value="" selected>Please Select</option>
                            @if(!empty($ethnicity))
                                @foreach($ethnicity as $n)
                                    <option {{ isset($employee->ethnicity_id) && $employee->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Nationality</div>
                        <select id="nationality-starter" name="nationality" class="lccTom lcc-tom-select w-full">
                            <option value="" selected>Please Select</option>
                            @if(!empty($country))
                                @foreach($country as $n)
                                    <option {{ isset($employee->country_id) && $employee->country_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                @endforeach 
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Gender</div>
                        <select id="gender-starter" name="gender" class="lccTom lcc-tom-select w-full">
                            <option value="" selected>Please Select</option>
                            @if(!empty($gender))
                                @foreach($gender as $n)
                                    <option {{ isset($employee->sex_identifier_id) && $employee->sex_identifier_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                @endforeach 
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Status</div>
                        <select id="status_id-starter" name="status_id" class="w-full lccTom tom-selects">     
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
                            <option value="2">All</option>
                        </select> 
                    </div>
                </div>
                <div class="col-span-4">
                    <button id="tabulator-html-filter-go-SR" type="button" class="btn btn-primary w-auto" >Generate</button>
                    <button id="tabulator-html-filter-reset-SR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="starterListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-starterreport.js')
@endsection