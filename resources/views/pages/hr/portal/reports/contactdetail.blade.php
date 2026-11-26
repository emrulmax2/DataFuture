@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-contact-details.css')
@endsection

@section('subcontent')
    @php($ecdNames = ['ethnicity' => $ethnicity->pluck('name', 'id'), 'nationality' => $country->pluck('name', 'id'), 'gender' => $gender->pluck('name', 'id')])
    {{-- New version (user's employee-contact-details design, 26 Nov 2026) beside the old one, as on
         Birthday List and Diversity. The Old pane is moved off screen rather than display:none, so a New
         search can fill it and run it while hidden. --}}
    <div class="ecd" data-ecd
         data-data-url="{{ route('hr.portal.reports.contactdetail.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.contactdetail.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.contactdetail.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-names='@json($ecdNames)'>
        <div class="ecd-head">
            <div class="ecd-head__lead">
                <span class="ecd-head__icon" aria-hidden="true"><i data-lucide="contact"></i></span>
                <div>
                    <h1 class="ecd-head__title">Employee Contact Details</h1>
                    <p class="ecd-head__sub" id="ecdSub">Phone, email, address and emergency contact for each member of staff.</p>
                </div>
            </div>
            <div class="ecd-head__actions">
                <div class="ecd-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-ecd-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-ecd-version="old">Old version</button>
                </div>
                <button type="button" class="ecd-btn" data-ecd-export="excel" data-ecd-new-only>Export XLSX</button>
                <button type="button" class="ecd-btn ecd-btn--primary" data-ecd-export="pdf" data-ecd-new-only>Download PDF</button>
                <a class="ecd-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="ecd-pane" data-ecd-pane="new">
            @include('pages.hr.portal.reports.contact.new')
        </div>

        <div class="ecd-pane is-off" data-ecd-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-ECD">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-contact" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id"> 
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
                    <select id="department_id-contact" name="department_id" class="w-full lccTom lcc-tom-select form-control">     
                        <option value="" selected>Please Select</option>             
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-2">
                    <label class="form-label">Startdate</label>
                    <input type="text" id="startdate-contact" name="startdate-contact" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>                    
                </div>
                <div class="col-span-2">
                    <label class="form-label">Enddate</label>
                    <input type="text" id="enddate-contact" name="enddate-contact" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-3">
                    <label class="form-label">Ethnicity</label>
                    <select id="ethnicity-contact" name="ethnicity" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="nationality-contact" name="nationality" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="gender-contact" name="gender" class="lccTom lcc-tom-select w-full form-control">
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
                    <select id="status-contact" name="status_id" class="w-full lccTom lcc-tom-select form-control">   
                        
                        <option value="1">Active</option> 
                        <option value="0">In Active</option>
                        <option value="2">All</option> 
                    </select> 
                </div>
                <div class="col-span-2 mt-7">
                    <button id="tabulator-html-filter-go-ECD" type="button" class="btn btn-primary w-auto" >Go</button>
                    <button id="tabulator-html-filter-reset-ECD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-3 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end"> 
                        <button id="contactbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
                        <button id="contactbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
                        <a href="{{route('hr.portal.reports.contactdetail.excel')}}" id="allContactExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
                        <a href="{{route('hr.portal.reports.contactdetail.pdf')}}" id="allContactPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="contactBySearchData" id="contactBySearchData" style='display:none'>
            <div class="intro-y box p-5 mt-5">
                <div id="contactBySearchDataGrid" class="grid grid-cols-12 gap-4"></div>
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-contactdetail.js')
    @vite('resources/js/hr-contact-details-new.js')
@endsection
