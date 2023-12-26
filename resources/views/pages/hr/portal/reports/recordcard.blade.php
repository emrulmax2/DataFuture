@php
    //dd($dataList);
@endphp
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Record Card</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button id="recordcardbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
            <button id="recordcardbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
            <a href="{{route('hr.portal.reports.recordcard.excel')}}" id="allRecordCardExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
            <a href="{{route('hr.portal.reports.recordcard.pdf')}}" id="allRecordCardPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-RCD">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Type</div>
                        <select id="employee_work_type_id-recordcard" class="lccTom lcc-tom-select w-full" name="employee_work_type_id"> 
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
                        <select id="department_id-recordcard" name="department_id" class="w-full lccTom lcc-tom-select">     
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
                        <input type="text" id="startdate-recordcard" name="startdate-recordcard" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Enddate</div>
                        <input type="text" id="enddate-recordcard" name="enddate-recordcard" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Ethnicity</div>
                        <select id="ethnicity-recordcard" name="ethnicity" class="lccTom lcc-tom-select w-full">
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
                        <select id="nationality-recordcard" name="nationality" class="lccTom lcc-tom-select w-full">
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
                        <select id="gender-recordcard" name="gender" class="lccTom lcc-tom-select w-full">
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
                        <select id="status_id-recordcard" name="status_id" class="w-full lccTom tom-selects">     
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
                            <option value="2">All</option>
                        </select> 
                    </div>
                </div>
                <div class="col-span-4">
                    <button id="tabulator-html-filter-go-RCD" type="button" class="btn btn-primary w-auto" >Generate</button>
                    <button id="tabulator-html-filter-reset-RCD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div class="recordcardAllData">
        @foreach ($dataList as $item)
            <div class="intro-y mt-5">
                <div class="intro-y box p-5">
                    
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12">
                            <div class="text-lg font-medium">Record Card For {{ $item['title'].' '.$item['full_name'] }}</div>
                        </div>
                        <div class="col-span-12">
                            <div class="text-lg font-medium">Personal Details</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Title</div>
                                <div class="col-span-8 font-medium">{{ $item['title'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                                <div class="col-span-8 font-medium">{{ $item['dob'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Surname</div>
                                <div class="col-span-8 font-medium">{{ $item['last_name'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Ethnic Origin</div>
                                <div class="col-span-8 font-medium">{{ $item['ethnicity'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Forename</div>
                                <div class="col-span-8 font-medium">{{ $item['first_name'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                                <div class="col-span-8 font-medium">{{ $item['nationality'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Gender</div>
                                <div class="col-span-8 font-medium">{{ $item['gender'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">NI Number</div>
                                <div class="col-span-8 font-medium">{{ $item['ni_number'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div> 

                        <div class="col-span-12">
                            <div class="text-lg font-medium">Employment Details</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Company Name</div>
                                <div class="col-span-8 font-medium">London Churchill College</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Started On</div>
                                <div class="col-span-8 font-medium">{{ $item['started_on'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Work No</div>
                                <div class="col-span-8 font-medium">{{ $item['works_number'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Ended On</div>
                                <div class="col-span-8 font-medium">{{ $item['end_to'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Job Title</div>
                                <div class="col-span-8 font-medium">{{ $item['job_title'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Grade</div>
                                <div class="col-span-8 font-medium">{{ $item['job_title'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Emergency Telephone</div>
                                <div class="col-span-8 font-medium">{{ $item['emergency_telephone'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Emergency Mobile</div>
                                <div class="col-span-8 font-medium">{{ $item['emergency_mobile'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Current Status</div>
                                <div class="col-span-8 font-medium">{{ $item['job_status'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Emergency Email</div>
                                <div class="col-span-8 font-medium">{{ $item['emergency_email'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12">
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div> 

                        <div class="col-span-12">
                            <div class="text-lg font-medium">Contact Information</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Address</div>
                                <div class="col-span-8 font-medium">{{ $item['address'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Telephone</div>
                                <div class="col-span-8 font-medium">{{ $item['telephone'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                                <div class="col-span-8 font-medium">{{ $item['mobile'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Email</div>
                                <div class="col-span-8 font-medium">{{ $item['email'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12">
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div> 

                        <div class="col-span-12">
                            <div class="text-lg font-medium">Other Details</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Disabled</div>
                                <div class="col-span-8 font-medium">{{ $item['disability'] }}</div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Car Reg.</div>
                                <div class="col-span-8 font-medium">{{ $item['car_reg'] }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    </div> 
                </div>
            </div>
        @endforeach
    </div>

    <div class="recordcardBySearchData" id="recordcardBySearchData" style='display:none'>
        <div class="intro-y mt-5">
            <div class="intro-y box p-5">
                <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                <div id="recordcardBySearchDataGrid" class="grid grid-cols-12 gap-4"></div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-recordcard.js')
@endsection