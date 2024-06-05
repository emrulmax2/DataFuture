@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Students Admission</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-ADM">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Ref. No.</div>
                        <input type="text" id="refno-ADM" name="refno-ADM" placeholder="Ref. No." value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">First Name(s)</div>
                        <input type="text" id="firstname-ADM" name="firstname-ADM" placeholder="First Name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Last Name</div>
                        <input type="text" id="lastname-ADM" name="lastname-ADM" placeholder="Last Name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Date of Birth</div>
                        <input type="text" id="dob-ADM" name="dob-ADM" placeholder="DD-MM-YYYY" value="" data-format="DD-MM-YYYY" data-single-mode="true" class="w-full datepicker"/>
                    </div>
                </div>

                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Email</div>
                        <input type="text" id="email-ADM" name="email-ADM" placeholder="xyz@zyx.com" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Phone</div>
                        <input type="text" id="phone-ADM" name="phone-ADM" placeholder="012XXXXXXXX" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Semester</div>
                        <select id="semesters-ADM" name="semesters[]" class="w-full tom-selects" multiple>
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
                        <select id="courses-ADM" name="courses[]" class="w-full tom-selects" multiple>
                            {{-- @if(!empty($courses))
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            @endif --}}
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Status</div>
                        <select id="statuses-ADM" name="statuses[]" class="w-full tom-selects" multiple>
                            @if(!empty($allStatuses))
                                @foreach($allStatuses as $sts)
                                    <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Agent</div>
                        <select id="agents-ADM" name="agents[]" class="w-full tom-selects" multiple>
                            @if(!empty($agents))
                                @foreach($agents as $agt)
                                    <option value="{{ $agt->agent_user_id }}">{{ (isset($agt->organization) ? $agt->organization : 'Unknown Organization') }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-span-12"></div>
                <div class="col-span-6">
                    <button id="tabulator-html-filter-go-ADM" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset-ADM" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-6 text-right">
                    <div class="flex mt-5 sm:mt-0 justify-end">
                        <button id="tabulator-print-ADM" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <button id="tabulator-export-xlsx-ADM" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel <i id="excel-loading" data-loading-icon="oval"  class="w-4 h-4 ml-2 mx-auto hidden"></i>
                        </button>
                        {{-- <div class="dropdown w-1/2 sm:w-auto mr-2">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    
                                    <li>
                                        <a id="tabulator-export-xlsx-ADM" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel <i id="excel-loading" data-loading-icon="oval"  class="w-4 h-4 ml-2 mx-auto hidden"></i>
                                        </a>
                                    </li>
                                    
                                </ul>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden">
            <div id="admissionListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/admission.js')
@endsection