@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-record-card.css')
@endsection

@section('subcontent')
    {{-- New version (user's employee-record-card design, 26 Nov 2026) beside the old one, as on the other
         HR reports. The Old pane is moved off screen rather than display:none, so a New search can fill it
         and run it while hidden. --}}
    @php($ercEthnicities = $ethnicity->map(fn ($e) => [$e->id, $e->name, (int) $e->active])->values())
    <div class="erc" data-erc
         data-data-url="{{ route('hr.portal.reports.recordcard.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.recordcard.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.recordcard.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-ethnicities='@json($ercEthnicities)'>
        <div class="erc-head">
            <div class="erc-head__lead">
                <span class="erc-head__icon" aria-hidden="true"><i data-lucide="user-square"></i></span>
                <div>
                    <h1 class="erc-head__title">Employee Record Card</h1>
                    <p class="erc-head__sub" id="ercSub">Each member of staff's personal, employment and contact details.</p>
                </div>
            </div>
            <div class="erc-head__actions">
                <div class="erc-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-erc-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-erc-version="old">Old version</button>
                </div>
                <button type="button" class="erc-btn" data-erc-export="excel" data-erc-new-only>Export XLSX</button>
                <button type="button" class="erc-btn erc-btn--primary" data-erc-export="pdf" data-erc-new-only>Download PDF</button>
                <a class="erc-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="erc-pane" data-erc-pane="new">
            @include('pages.hr.portal.reports.record.new')
        </div>

        <div class="erc-pane is-off" data-erc-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-RCD">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-recordcard" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id"> 
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
                    <select id="department_id-recordcard" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-2">
                    <label class="form-label">Startdate</label>
                    <input type="text" id="startdate-recordcard" name="startdate-recordcard" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Enddate</label>
                    <input type="text" id="enddate-recordcard" name="enddate-recordcard" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-3">
                    <label class="form-label">Ethnicity</label>
                    <select id="ethnicity-recordcard" name="ethnicity" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="nationality-recordcard" name="nationality" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="gender-recordcard" name="gender" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="status_id-recordcard" name="status_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select> 
                </div>
                <div class="col-span-2 mt-7">
                    <button id="tabulator-html-filter-go-RCD" type="button" class="btn btn-primary w-auto" >Go</button>
                    <button id="tabulator-html-filter-reset-RCD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-3 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end"> 
                        <button id="recordcardbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
                        <button id="recordcardbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
                        <a href="{{route('hr.portal.reports.recordcard.excel')}}" id="allRecordCardExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
                        <a href="{{route('hr.portal.reports.recordcard.pdf')}}" id="allRecordCardPdfBtn" class="btn btn-success text-white w-1/2 w-auto">Download Pdf</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="recordcardBySearchData" id="recordcardBySearchData" style='display:none'>
            <div class="intro-y mt-5">
                <div class="intro-y box p-5">
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div id="recordcardBySearchDataGrid" class="grid grid-cols-12 gap-4"></div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-recordcard.js')
    @vite('resources/js/hr-record-card-new.js')
@endsection
