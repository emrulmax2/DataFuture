@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Birthday List Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button id="bdayListbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
            <button id="bdayListbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none">Download Pdf</button>
            <a href="{{route('hr.portal.reports.birthdaylist.excel')}}" id="allBdayListExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
            <a href="{{route('hr.portal.reports.birthdaylist.pdf')}}" id="allBdayListPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md">Back to Employment Reports</a>
        </div>
    </div>
    
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-BR">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2">
                    <input type="text" id="dob-BR" name="dob-BR" placeholder="Select Month of Birth" value="" data-format="YYYY-MM"  data-single-mode="true" class="w-full datepicker"/>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Type</div>
                        <select id="employee_work_type_id-birtdaylist" class="lccTom tom-selects w-full" name="employee_work_type_id">    
                            <option></option> 
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
                        <select id="department_id-birtdaylist" name="department_id" class="w-full lccTom tom-selects">     
                            <option></option>             
                            @foreach($departments as $si)
                                <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>             
                            @endforeach
                        </select> 
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Status</div>
                        <select id="status_id-birtdaylist" name="status_id" class="w-full lccTom tom-selects">     
                            <option value="1">Active</option>
                            <option value="0">In Active</option>
                            <option value="2">All</option>
                        </select> 
                    </div>
                </div>
                <div class="col-span-2">
                    <button id="tabulator-html-filter-go-BR" type="button" class="btn btn-primary w-auto">Generate</button>
                    <button id="tabulator-html-filter-reset-BR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden w-full">
            <div id="birthdayListSearchTable" class="mt-5 table-report table-report--tabulator"></div>
        </div> 
    </div>

    <div class="birthdayListAllData">
        @foreach ($dataList as $item)
            <div class="intro-y mt-5">
                <div class="intro-y box p-5">
                    <div class="items-center mb-5">
                        <div class="col-span-12">
                            <div class="text-lg font-medium">{{ $item['month'] }}</div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table table-sm table-bordered">
                            <thead style="">
                                <tr>
                                    <th class="whitespace-nowrap" data-priority="1" scope="col">Name</th>
                                    <th class="whitespace-nowrap" data-priority="2" scope="col">Works No</th>
                                    <th class="whitespace-nowrap" data-priority="3" scope="col">Gender</th>
                                    <th class="whitespace-nowrap" data-priority="4" scope="col">Date of birth</th>
                                    <th class="whitespace-nowrap" data-priority="5" scope="col">Age</th>
                                </tr>
                            </thead>
                            <tbody style="">
                                @foreach ($item["dataArray"] as $normalItem)
                                <tr>
                                    <td>{{ $normalItem['name'] }}</td>
                                    <td>{{ $normalItem['works_no'] }}</td>
                                    <td>{{ $normalItem['gender'] }}</td>
                                    <td>{{ $normalItem['date_of_birth'] }}</td>
                                    <td>{{ $normalItem['age'] }}</td>
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
    @vite('resources/js/hr-portal-birthdayreport.js')
@endsection