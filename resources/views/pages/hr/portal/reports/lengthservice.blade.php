@php
    //dd($dataList);
@endphp
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Service Lengths</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button id="lengthservicebySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
            <button id="lengthservicebySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none"></i>Download Pdf</button>
            <a href="{{route('hr.portal.reports.lengthservice.excel')}}" id="allLengthServiceExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
            <a href="{{route('hr.portal.reports.lengthservice.pdf')}}" id="allLengthServicePdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-LS">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Type</div>
                        <select id="employee_work_type_id-lengthservice" class="lccTom lcc-tom-select w-full" name="employee_work_type_id"> 
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
                        <select id="department_id-lengthservice" name="department_id" class="w-full lccTom lcc-tom-select">     
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
                        <input type="text" id="startdate-lengthservice" name="startdate-lengthservice" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Enddate</div>
                        <input type="text" id="enddate-lengthservice" name="enddate-lengthservice" placeholder="DD-MM-YYYY" value="" data-format="YYYY-MM-DD"  data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Ethnicity</div>
                        <select id="ethnicity-lengthservice" name="ethnicity" class="lccTom lcc-tom-select w-full">
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
                        <select id="nationality-lengthservice" name="nationality" class="lccTom lcc-tom-select w-full">
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
                        <select id="gender-lengthservice" name="gender" class="lccTom lcc-tom-select w-full">
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
                        <select id="status_id-lengthservice" name="status_id" class="w-full lccTom tom-selects">     
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
                            <option value="2">All</option>
                        </select> 
                    </div>
                </div>
                <div class="col-span-4">
                    <button id="tabulator-html-filter-go-LS" type="button" class="btn btn-primary w-auto" >Generate</button>
                    <button id="tabulator-html-filter-reset-LS" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="lengthserviceTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <div class="lengthserviceAllData">
        @foreach ($dataList as $item)
            <div class="intro-y mt-5">
                <div class="intro-y box p-5">
                    <div class="items-center mb-5">
                        <div class="col-span-12">
                            <div class="text-lg font-medium">{{ $item['year'] }} Years</div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-bordered">
                            <thead style="">
                                <tr>
                                    <th class="whitespace-nowrap" data-priority="1" scope="col">Name</th>
                                    <th class="whitespace-nowrap" data-priority="2" scope="col">Started On</th>
                                    <th class="whitespace-nowrap" data-priority="3" scope="col">Ended On</th>
                                    <th class="whitespace-nowrap" data-priority="4" scope="col">Length of Service</th>
                                </tr>
                            </thead>
                            <tbody style="">
                                @foreach ($item["dataArray"] as $normalItem)
                                <tr>
                                    <td>{{ $normalItem['name'] }}</td>
                                    <td>{{ $normalItem['started_on'] }}</td>
                                    <td>{{ $normalItem['ended_on'] }}</td>
                                    <td>{{ $normalItem['length'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-lengthservice.js')
@endsection