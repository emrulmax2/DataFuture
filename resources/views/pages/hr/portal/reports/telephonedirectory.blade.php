@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-telephone-directory.css')
@endsection

@section('subcontent')
    {{-- New version (user's employee-telephone-directory design, 26 Nov 2026) beside the old one, as on the other
         HR reports. The Old pane is moved off screen rather than display:none, so a New search can fill it
         and run it while hidden. --}}
    @php($etdEthnicities = $ethnicity->map(fn ($e) => [$e->id, $e->name, (int) $e->active])->values())
    <div class="etd" data-etd
         data-data-url="{{ route('hr.portal.reports.telephonedirectory.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.telephonedirectory.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.telephonedirectory.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-ethnicities='@json($etdEthnicities)'>
        <div class="etd-head">
            <div class="etd-head__lead">
                <span class="etd-head__icon" aria-hidden="true"><i data-lucide="phone"></i></span>
                <div>
                    <h1 class="etd-head__title">Employee Telephone Directory</h1>
                    <p class="etd-head__sub" id="etdSub">Phone numbers and emails for every member of staff, A to Z.</p>
                </div>
            </div>
            <div class="etd-head__actions">
                <div class="etd-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-etd-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-etd-version="old">Old version</button>
                </div>
                <button type="button" class="etd-btn" data-etd-export="excel" data-etd-new-only>Export XLSX</button>
                <button type="button" class="etd-btn etd-btn--primary" data-etd-export="pdf" data-etd-new-only>Download PDF</button>
                <a class="etd-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="etd-pane" data-etd-pane="new">
            @include('pages.hr.portal.reports.directory.new')
        </div>

        <div class="etd-pane is-off" data-etd-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-TD">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-telephonedirectory" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id"> 
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
                    <select id="department_id-telephonedirectory" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-2">
                    <label class="form-label">Startdate</label>
                    <input type="text" id="startdate-telephonedirectory" name="startdate-telephonedirectory" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Enddate</label>
                    <input type="text" id="enddate-telephonedirectory" name="enddate-telephonedirectory" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-3">
                    <label class="form-label">Ethnicity</label>
                    <select id="ethnicity-telephonedirectory" name="ethnicity" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="nationality-telephonedirectory" name="nationality" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="gender-telephonedirectory" name="gender" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="status_id-telephonedirectory" name="status_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select> 
                </div>
                <div class="col-span-2 mt-7">
                    <button id="tabulator-html-filter-go-TD" type="button" class="btn btn-primary w-auto" >Go</button>
                    <button id="tabulator-html-filter-reset-TD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-3 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end"> 
                        <button id="telephonedirectorybySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
                        <button id="telephonedirectorybySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
                        <a href="{{route('hr.portal.reports.telephonedirectory.excel')}}" id="allTelephoneDirectoryExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
                        <a href="{{route('hr.portal.reports.telephonedirectory.pdf')}}" id="allTelephoneDirectoryPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>                       
                    </div>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="telephonedirectoryTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-telephonedirectory.js')
    @vite('resources/js/hr-telephone-directory-new.js')
@endsection
