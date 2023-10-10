@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Students</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-LSD">
            <div class="grid grid-cols-12 gap-0 gap-x-4">
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 gap-x-4">
                        <label class="col-span-12 sm:col-span-4 form-label pt-2">Student Search</label>
                        <div class="col-span-12 sm:col-span-8">
                            <div class="autoCompleteField" data-table="students" data-fields="registration_no,application_no,uhn_no,ssn_no">
                                <input type="text" name="student_id" class="form-control" value="" placeholder="LCC000001"/>
                                <ul class="autoFillDropdown">
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">LCC000001</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="dropdown-item">LCC000002</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4 text-right"></div>
                <div class="col-span-12 sm:col-span-4 text-right">
                    <div class="flex justify-end items-center">
                        <button type="submit" class="btn btn-facebook ml-1 w-auto">Advance Search <i class="w-4 h-4 ml-2" data-lucide="chevron-down"></i></button>
                        <button type="button" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-5">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Ref. No.</div>
                        <input type="text" id="refno-LSD" name="refno-LSD" placeholder="Ref. No." value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">First Name(s)</div>
                        <input type="text" id="firstname-LSD" name="firstname-LSD" placeholder="First Name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Last Name</div>
                        <input type="text" id="lastname-LSD" name="lastname-LSD" placeholder="Last Name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Date of Birth</div>
                        <input type="text" id="dob-LSD" name="dob-LSD" placeholder="DD-MM-YYYY" value="" data-format="DD-MM-YYYY" data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Semester</div>
                        <select id="semesters-LSD" name="semesters[]" class="w-full tom-selects" multiple>
                            @if(!empty($semesters))
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Courses</div>
                        <select id="courses-LSD" name="courses[]" class="w-full tom-selects" multiple>
                            @if(!empty($courses))
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Status</div>
                        <select id="statuses-LSD" name="statuses[]" class="w-full tom-selects" multiple>
                            @if(!empty($allStatuses))
                                @foreach($allStatuses as $sts)
                                    <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-12"></div>
                <div class="col-span-6">
                    <button id="tabulator-html-filter-go-LSD" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset-LSD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-6 text-right">
                    <div class="flex mt-5 sm:mt-0 justify-end">
                        <button id="tabulator-print-LSD" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <div class="dropdown w-1/2 sm:w-auto mr-2" id="tabulator-export-LSD">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv-LSD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-json-LSD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx-LSD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-html-LSD" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden">
            <div id="liveStudentsListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/students.js')
    @vite('resources/js/student-global.js')
@endsection