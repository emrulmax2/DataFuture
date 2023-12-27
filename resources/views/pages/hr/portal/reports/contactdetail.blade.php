@php
    //dd($dataList);
@endphp
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Contact Details</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button id="contactbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
            <button id="contactbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
            <a href="{{route('hr.portal.reports.contactdetail.excel')}}" id="allContactExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
            <a href="{{route('hr.portal.reports.contactdetail.pdf')}}" id="allContactPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-ECD">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Type</div>
                        <select id="employee_work_type_id-contact" class="lccTom lcc-tom-select w-full" name="employee_work_type_id"> 
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
                        <select id="department_id-contact" name="department_id" class="w-full lccTom lcc-tom-select">     
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
                        <input type="text" id="startdate-contact" name="startdate-contact" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Enddate</div>
                        <input type="text" id="enddate-contact" name="enddate-contact" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Ethnicity</div>
                        <select id="ethnicity-contact" name="ethnicity" class="lccTom lcc-tom-select w-full">
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
                        <select id="nationality-contact" name="nationality" class="lccTom lcc-tom-select w-full">
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
                        <select id="gender-contact" name="gender" class="lccTom lcc-tom-select w-full">
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
                        <select id="status_id-contact" name="status_id" class="w-full lccTom tom-selects">     
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
                            <option value="2">All</option>
                        </select> 
                    </div>
                </div>
                <div class="col-span-4">
                    <button id="tabulator-html-filter-go-ECD" type="button" class="btn btn-primary w-auto" >Generate</button>
                    <button id="tabulator-html-filter-reset-ECD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        {{-- <div class="overflow-x-auto scrollbar-hidden">
            <div id="contactListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div> --}}
    </div>
    <div class="contactAllData">
        <div class="intro-y mt-5">
            <div class="intro-y box p-5">
                <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                <div class="grid grid-cols-12 gap-4">
                    @foreach ($dataList as $item) 
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Name</div>
                            <div class="col-span-8 font-medium">{{ $item['name'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Address</div>
                            <div class="col-span-8 font-medium">{{ $item['address'] }}</div>
                        </div>
                    </div> 
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Post Code</div>
                            <div class="col-span-8 font-medium">{{ $item['post_code'] }}</div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Telephone</div>
                            <div class="col-span-8 font-medium">{{ $item['telephone'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                            <div class="col-span-8 font-medium">{{ $item['mobile'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Email</div>
                            <div class="col-span-8 font-medium">{{ $item['email'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Emergency Telephone</div>
                            <div class="col-span-8 font-medium">{{ $item['emergency_telephone'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Emergency Mobile</div>
                            <div class="col-span-8 font-medium">{{ $item['emergency_mobile'] }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Emergency Email</div>
                            <div class="col-span-8 font-medium">{{ $item['emergency_email'] }}</div>
                        </div>
                    </div>  
                    <div class="col-span-12">
                        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    </div>            
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="contactBySearchData" id="contactBySearchData" style='display:none'>
        <div class="intro-y mt-5">
            <div class="intro-y box p-5">
                <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                <div id="contactBySearchDataGrid" class="grid grid-cols-12 gap-4"></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-contactdetail.js')
@endsection