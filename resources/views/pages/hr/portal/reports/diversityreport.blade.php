@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-diversity-report.css')
@endsection

@section('subcontent')
    {{-- New version (user's diversity-information-report design, 26 Nov 2026) beside the old one, as on
         Birthday List. Toggle buttons, not role="tab": the theme's tab script grabs every [role=tab].
         The Old pane is moved off screen rather than display:none, so its table can be built while
         hidden when a New search fills it in. --}}
    <div class="dvr" data-dvr
         data-data-url="{{ route('hr.portal.reports.diversityreport.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.diversityreport.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.diversityreport.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-ethnicities='@json($ethnicity->map(fn ($e) => [$e->id, $e->name, (int) $e->active])->values())'>
        <div class="dvr-head">
            <div class="dvr-head__lead">
                <span class="dvr-head__icon" aria-hidden="true"><i data-lucide="users"></i></span>
                <div>
                    <h1 class="dvr-head__title">Diversity Information</h1>
                    <p class="dvr-head__sub" id="dvrSub">Gender, ethnicity, nationality and disability across staff.</p>
                </div>
            </div>
            <div class="dvr-head__actions">
                <div class="dvr-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-dvr-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-dvr-version="old">Old version</button>
                </div>
                <button type="button" class="dvr-btn" data-dvr-export="excel" data-dvr-new-only>Export XLSX</button>
                <button type="button" class="dvr-btn dvr-btn--primary" data-dvr-export="pdf" data-dvr-new-only>Download PDF</button>
                <a class="dvr-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="dvr-pane" data-dvr-pane="new">
            @include('pages.hr.portal.reports.diversity.new')
        </div>

        <div class="dvr-pane is-off" data-dvr-pane="old" aria-hidden="true">
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
                                @if($n->active == 1)
                                    <option {{ isset($employee->ethnicity_id) && $employee->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                @endif
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
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-diversityreport.js')
    @vite('resources/js/hr-diversity-report-new.js')
@endsection
