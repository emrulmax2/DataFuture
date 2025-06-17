@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    @if($workplacement_details)
    <div class="intro-y mt-5">
        <ul class="nav workplacementNwtab" role="tablist">
            <li id="std_work_placement_nw_item" class="nav-item" role="presentation">
                <button class="nav-link active btn btn-outline-secondary active-bg-white hover-bg-white hover:text-primary rounded-0" data-tw-toggle="pill" data-tw-target="#std_workplacement_nw" type="button" role="tab" aria-controls="std_work_placement" aria-selected="true">
                    Work Placement
                </button>
            </li>
        </ul>
    </div>
    <div class="tab-content workplacementNwtabcontent">
        <div id="std_workplacement_nw" class="tab-pane active" role="tabpanel" aria-labelledby="example-3-tab">
            <div class="intro-y box p-5">
                @if(isset(auth()->user()->priv()['placement_add']) && auth()->user()->priv()['placement_add'] == 1)
                <div class="absolute top-3 md:-top-10 right-0 thebtnarea">
                    <button data-tw-toggle="modal" data-tw-target="#addWpHourModal" type="button" class="btn btn-success rounded-0 text-white"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Hours</button>
                </div>
                @endif
                <div class="intro-y">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium">Workplacement Details</div>
                                <div class="col-span-8">
                                    <span class=" inline-flex px-2 py-0 ml-2 rounded-0">
                                        {{ $workplacement_details->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium mb-4">Hours Required</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0 mb-4 theTogglers">
                                        {{ $student->crel->creation->required_hours.' Hours' }}
                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-x-auto collapsibles" style="display: none;">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        @foreach($total_hours_calculations as $level_hours)
                                                @foreach($level_hours->learning_hours as $learningHour)
                                                    <tr>
                                                        <td>{{ (isset($level_hours->name) && $level_hours->name) ? $level_hours->name : '' }}</td>
                                                        <td>{{ (isset($learningHour->name) && $learningHour->name) ? $learningHour->name : '' }}</td>
                                                        <td>{{ (isset($learningHour->hours) && $learningHour->hours > 0 ? $learningHour->hours.' Hours' : '0 Hours') }}</td>
                                                    </tr>
                                                @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium mb-4">Hours Completed</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-success px-2 py-0 ml-2 text-white rounded-0 hoursCompleted mb-4 theTogglers">
                                        {{ (isset($work_hours) && $work_hours > 0 ? $work_hours.' Hours' : '0 Hours') }}
                                        <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                    </span>
                                </div>
                            </div>
                            @if(!empty($confirmed_hours))
                            <div class="overflow-x-auto collapsibles" style="display: none;">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        @foreach($confirmed_hours as $hours)
                                            <tr>
                                                <td>{{ (isset($hours['lavel_hours']) && !empty($hours['lavel_hours'])) ? $hours['lavel_hours'] : '' }}</td>
                                                <td>{{ (isset($hours['learning_hours']) && !empty($hours['learning_hours'])) ? $hours['learning_hours'] : '' }}</td>
                                                <td>{{ (isset($hours['confirmed_hours']) && !empty($hours['confirmed_hours'])) ? $hours['confirmed_hours'] : '' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="intro-y pt-5 pb-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-center">
                        <form id="tabulatorFilterForm" class="noWrapTomselect xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0 ">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Level</label>
                                <select id="src_level_hours_id" name="src_level_hours_id" class="tom-selects w-full mt-2 sm:mt-0 sm:w-52" >
                                    <option value="0">All</option>
                                    @if(isset($workplacement_details->level_hours) && $workplacement_details->level_hours->count() > 0)
                                        @foreach($workplacement_details->level_hours as $level_hour)
                                            <option value="{{ $level_hour->id }}">{{ $level_hour->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0 ">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Learning</label>
                                <select id="src_learning_hours_id" name="src_learning_hours_id" class="tom-selects w-full mt-2 sm:mt-0 sm:w-52" >
                                    <option value="0">All</option>
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0 ">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Modules</label>
                                <select id="wp_modules" name="wp_modules" class="tom-selects w-full mt-2 sm:mt-0 sm:w-56" >
                                    <option value="0">All</option>
                                    @if($assign_modules->count() > 0)
                                        @foreach($assign_modules as $aml)
                                            <option value="{{ $aml->id }}">{{ $aml->module_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="wp_status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="All">All</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="wp_tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="wp_tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="ml-auto totalHourCounter font-medium inline-flex items-center justify-end">
                            <span class="btn inline-flex btn-success px-2 py-0 mr-1 text-white rounded-0 completedHours">0 Hours</span>
                            <span class="btn inline-flex btn-pending px-2 py-0 mr-1 text-white rounded-0 pendingHours">0 Hours</span>
                            <span class="btn inline-flex btn-danger px-2 py-0 text-white rounded-0 rejectedHours">0 Hours</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="studentWorkPlacementNwTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif






    <div class="intro-y mt-5">
        <ul class="nav workplacementtab" role="tablist">
            <li id="std_work_placement_item" class="nav-item" role="presentation">
                <button class="nav-link active btn btn-outline-secondary active-bg-white hover-bg-white hover:text-primary rounded-0" data-tw-toggle="pill" data-tw-target="#std_work_placement" type="button" role="tab" aria-controls="std_work_placement" aria-selected="true">
                    Work Placement (Archived)
                </button>
            </li>
            <li id="std_wbl_profile_item" class="nav-item" role="presentation">
                <button class="nav-link btn btn-outline-secondary rounded-0 active-bg-white hover-bg-white hover:text-primary" data-tw-toggle="pill" data-tw-target="#std_wbl_profile" type="button" role="tab" aria-controls="std_wbl_profile" aria-selected="false" >
                    Student WBL Profile (Archived)
                </button>
            </li>
        </ul>
    </div>
    <div class="tab-content workplacementtabcontent">
        <div id="std_work_placement" class="tab-pane active" role="tabpanel" aria-labelledby="example-3-tab">
            <div class="intro-y box p-5">
                <div class="absolute top-3 md:-top-10 right-0 thebtnarea">
                    <button data-tw-toggle="modal" data-tw-target="#addHourModal" type="button" class="btn btn-success rounded-0 text-white"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Hours</button>
                    <button type="button" class="btn btn-primary rounded-0 hidden md:inline-flex"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF</button>
                </div>
                <div class="intro-y">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium">Hours Required</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">
                                        {{ $student->crel->creation->required_hours.' Hours' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium">Hours Completed</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-success px-2 py-0 ml-2 text-white rounded-0">
                                        {{ (isset($work_hours) && $work_hours > 0 ? $work_hours.' Hours' : '0 Hours') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y pt-5 pb-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="mt-5 sm:mt-0 hidden md:inline-flex">
                            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="studentWorkPlacementTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="std_wbl_profile" class="tab-pane" role="tabpanel" aria-labelledby="example-4-tab">
            <div class="intro-y box p-5">
                <div class="absolute thebtnarea">
                    <button style="display: none;" data-tw-toggle="modal" data-tw-target="#addWBLProfileModal" type="button" class="btn addWBLProfileBtn btn-success rounded-0 text-white"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add WBL Profile</button>
                    <button type="button" class="btn btn-primary rounded-0"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF</button>
                </div>
                <div class="intro-y">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-right">
                            <select name="student_work_placement_id" id="student_work_placement_id" class="form-control w-auto" style="max-width: 270px;">
                                <option value="">Please Select a Company</option>
                                @if($placement->count() > 0)
                                    @foreach($placement as $plc)
                                        <option value="{{ $plc->id }}">{{ (isset($plc->company->name) ? $plc->company->name : 'Unknown Company') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="intro-y pt-5 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm-WBL" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                    <select id="status-WBL" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go-WBL" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset-WBL" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div class="mt-5 sm:mt-0 hidden md:inline-flex">
                                <button id="tabulator-print-WBL" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                </button>
                                <div class="dropdown w-1/2 sm:w-auto">
                                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                    </button>
                                    <div class="dropdown-menu w-40">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv-WBL" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-WBL" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="studentWBLProfileTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    @if($workplacement_details)
    <!-- BEGIN: Add Hour Modal -->
    <div id="addWpHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addWpHourForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Workplacement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="intro-y py-5 mt-5">
                            <div class="form-wizard">
                                <div class="form-wizard-header">
                                    <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                                        <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 add-form-wizard-step-item active">
                                            <button type="button" class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">1</button>
                                            <div class="text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Workplacement Settings</div>
                                        </li>
                                        <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 add-form-wizard-step-item">
                                            <button type="button" class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                                            <div class="text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Hours Settings</div>
                                        </li>
                                    </ul>
                                </div>
                                
                                <!-- Step 1 Fields -->
                                <div class="add-step1-wizard-step px-5 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400" id="add_step1">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="level_hours_id" class="form-label">Level Hours <span class="text-danger">*</span></label>
                                            <select id="level_hours_id" class="form-control w-full tom-selects" name="level_hours_id" required>
                                                <option value="">Please Select</option>
                                                @if(isset($workplacement_details->level_hours) && $workplacement_details->level_hours->count() > 0)
                                                    @foreach($workplacement_details->level_hours as $level_hour)
                                                        <option value="{{ $level_hour->id }}">{{ $level_hour->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-level_hours_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="learning_hours_id" class="form-label">Learning Hours <span class="text-danger">*</span></label>
                                            <select id="learning_hours_id" class="form-control w-full tom-selects" name="learning_hours_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-learning_hours_id text-danger mt-2"></div>
                                            <input type="hidden" name="module_required" value="0"/>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="workplacement_setting_id" class="form-label">Workplacement Setting <span class="text-danger">*</span></label>                                       
                                            <select id="workplacement_setting_id" class="form-control w-full tom-selects" name="workplacement_setting_id" required>
                                                <option value="">Please Select</option>
                                                @if($workplacement_settings->count() > 0)
                                                    @foreach($workplacement_settings as $wps)
                                                        <option value="{{ $wps->id }}">{{ $wps->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-workplacement_setting_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="workplacement_setting_type_id" class="form-label">Workplacement Setting Type <span class="text-danger">*</span></label>
                                            <select id="workplacement_setting_type_id" class="form-control w-full tom-selects" name="workplacement_setting_type_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-workplacement_setting_type_id text-danger mt-2"></div>
                                        </div>
                                                
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                            <select id="company_id" class="form-control w-full tom-selects" name="company_id" required>
                                                <option value="">Please Select</option>
                                                @if($company->count() > 0)
                                                @foreach($company as $com)
                                                <option value="{{ $com->id }}">{{ $com->name }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                                            <select id="company_supervisor_id" class="form-control w-full tom-selects" name="company_supervisor_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="assign_module_list_id" class="form-label">Assign Module List <span class="text-danger modReq hidden">*</span></label>
                                            <select id="assign_module_list_id" class="form-control w-full tom-selects" name="assign_module_list_id">
                                                <option value="">Please Select</option>
                                                @if($assign_modules->count() > 0)
                                                    @foreach($assign_modules as $aml)
                                                        <option value="{{ $aml->id }}">{{ $aml->module_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-assign_module_list_id text-danger mt-2"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-12 flex items-center justify-end sm:justify-end mt-5">
                                        <button type="button" class="btn btn-primary w-auto add-step1-wizard-next-btn" data-next="add_step2">
                                            Continue 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 svg_2">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Step 2 Fields -->
                                <div class="add-step2-wizard-step px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400" id="add_step2" style="display: none;">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="hours" class="form-label">Hours <span class="text-danger">*</span></label>
                                            <input type="number" step="any" value="" id="hours" class="form-control" name="hours">
                                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                                            <select id="contract_type" class="form-control w-full tom-selects" name="contract_type">
                                                <option value="">Please Select</option>
                                                <option value="Permanent">Permanent</option>
                                                <option value="Temporary">Temporary</option>
                                                <option value="Contract Base">Contact Base</option>
                                                <option value="Part-time">Part-time</option>
                                            </select>
                                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="text" value="" placeholder="DD-MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="text" value="" placeholder="DD-MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        </div>
                                    </div>
                                    <div class="col-span-12 flex items-center justify-between sm:justify-between mt-5">
                                        <button type="button" class="btn btn-secondary w-auto add-step1-wizard-prev-btn" data-prev="add_step1">
                                            Back
                                        </button>
                                        <button type="submit" id="wpHourInsertBtn" class="btn btn-primary w-auto">
                                            Save
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>
                                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                                        <input type="hidden" name="workplacement_details_id" value="{{ $workplacement_details->id }}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Hour Modal -->
    <!-- BEGIN: Edit Hour Modal -->
    <div id="editWpHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editWpHourForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Workplacement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="intro-y py-5 mt-5">
                            <div class="form-wizard">
                                <div class="form-wizard-header">
                                    <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                                        <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 edit-form-wizard-step-item active">
                                            <button type="button" class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">1</button>
                                            <div class="text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Workplacement Settings</div>
                                        </li>
                                        <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 edit-form-wizard-step-item">
                                            <button type="button" class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                                            <div class="text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Hours Settings</div>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="edit-step1-wizard-step px-5 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400" id="edit_step1">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="level_hours_id" class="form-label">Level Hours <span class="text-danger">*</span></label>
                                            <select id="level_hours_id" class="form-control w-full tom-selects" name="level_hours_id">
                                                <option value="">Please Select</option>
                                                @if(isset($workPlacementDetails->level_hours) && $workPlacementDetails->level_hours->count() > 0)
                                                    @foreach($workPlacementDetails->level_hours as $level_hour)
                                                        <option value="{{ $level_hour->id }}">{{ $level_hour->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-level_hours_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="learning_hours_id" class="form-label">Learning Hours <span class="text-danger">*</span></label>
                                            <select id="learning_hours_id" class="form-control w-full tom-selects" name="learning_hours_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-learning_hours_id text-danger mt-2"></div>
                                            <input type="hidden" name="module_required" value="0"/>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="workplacement_setting_id" class="form-label">Workplacement Setting <span class="text-danger">*</span></label>
                                            <select id="workplacement_setting_id" class="form-control w-full tom-selects" name="workplacement_setting_id" required>
                                                <option value="">Please Select</option>
                                                @if($workplacement_settings->count() > 0)
                                                    @foreach($workplacement_settings as $wps)
                                                        <option value="{{ $wps->id }}">{{ $wps->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-workplacement_setting_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="workplacement_setting_type_id" class="form-label">Workplacement Setting Type <span class="text-danger">*</span></label>
                                            <select id="workplacement_setting_type_id" class="form-control w-full tom-selects" name="workplacement_setting_type_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-workplacement_setting_type_id text-danger mt-2"></div>
                                        </div>
                                                
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                                            <select id="company_id" class="form-control w-full tom-selects" name="company_id" required>
                                                <option value="">Please Select</option>
                                                @if($company->count() > 0)
                                                @foreach($company as $com)
                                                <option value="{{ $com->id }}">{{ $com->name }}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                                            <select id="company_supervisor_id" class="form-control w-full tom-selects" name="company_supervisor_id" required>
                                                <option value="">Please Select</option>
                                            </select>
                                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="assign_module_list_id" class="form-label">Assign Module List <span class="text-danger modReq hidden">*</span></label>
                                            <select id="assign_module_list_id" class="form-control w-full tom-selects" name="assign_module_list_id">
                                                <option value="">Please Select</option>
                                                @if($assign_modules->count() > 0)
                                                    @foreach($assign_modules as $aml)
                                                        <option value="{{ $aml->id }}">{{ $aml->module_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-assign_module_list_id text-danger mt-2"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-12 flex items-center justify-end sm:justify-end mt-5">
                                        <button type="button" class="btn btn-primary w-auto edit-step1-wizard-next-btn" data-next="edit_step2">
                                            Continue 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 svg_2">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                    
                                <div class="edit-step2-wizard-step px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400" id="edit_step2" style="display: none;">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="hours" class="form-label">Hours <span class="text-danger">*</span></label>
                                            <input type="number" step="any" value="" id="hours" class="form-control" name="hours">
                                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                                            <select id="contract_type" class="form-control w-full tom-selects" name="contract_type">
                                                <option value="">Please Select</option>
                                                <option value="Permanent">Permanent</option>
                                                <option value="Temporary">Temporary</option>
                                                <option value="Contract Base">Contact Base</option>
                                                <option value="Part-time">Part-time</option>
                                            </select>
                                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="text" value="" placeholder="DD-MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="text" value="" placeholder="DD-MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        </div>
                                        <div class="col-span-12 md:col-span-6 mt-3">
                                            <label for="status" class="form-label">Status<span class="text-danger">*</span></label>
                                            <select id="status" class="form-control w-full tom-selects" name="status">
                                                <option value="Pending">Pending</option>
                                                <option value="Rejected">Rejected</option>
                                                <option value="Confirmed">Confirmed</option>
                                            </select>
                                            <div class="acc__input-error error-status text-danger mt-2"></div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 flex items-center justify-between sm:justify-between mt-5">
                                        <button type="button" class="btn btn-secondary w-auto edit-wizard-prev-btn" data-prev="edit_step1">
                                            Back
                                        </button>
                                        <button type="submit" id="wpHourUpdateBtn" class="btn btn-primary w-auto">
                                            Update
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </button>
                                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                                            <input type="hidden" name="workplacement_details_id" value="{{ $workplacement_details->id }}"/>
                                            <input type="hidden" name="id" value="0"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Hour Modal -->
    @endif


    <!-- BEGIN: Add Hour Modal -->
    <div id="addHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addHourForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Work Placement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select id="company_id" class="form-control w-full" name="company_id">
                                <option value="">Please Select</option>
                                @if($company->count() > 0)
                                    @foreach($company as $com)
                                        <option value="{{ $com->id }}">{{ $com->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 supervisorWrap">
                            <label for="company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                            <select id="company_supervisor_id" class="form-control w-full" name="company_supervisor_id">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-3">
                            <label for="hours" class="form-label">Hours <span class="text-danger">*</span></label>
                            <input type="number" value="" id="hours" class="form-control" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select id="contract_type" class="form-control w-full" name="contract_type">
                                <option value="">Please Select</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Contract Base">Contact Base</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveWP" class="btn btn-primary w-auto">     
                            Save                      
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Hour Modal -->

    <!-- BEGIN: Edit Hour Modal -->
    <div id="editHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editHourForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Work Placement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select id="edit_company_id" class="form-control w-full" name="company_id">
                                <option value="">Please Select</option>
                                @if($company->count() > 0)
                                    @foreach($company as $com)
                                        <option value="{{ $com->id }}">{{ $com->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 supervisorWrap">
                            <label for="edit_company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                            <select id="edit_company_supervisor_id" class="form-control w-full" name="company_supervisor_id">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-3">
                            <label for="edit_hours" class="form-label">Hours <span class="text-danger">*</span></label>
                            <input type="number" value="" id="edit_hours" class="form-control" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select id="edit_contract_type" class="form-control w-full" name="contract_type">
                                <option value="">Please Select</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Contract Base">Contact Base</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateWP" class="btn btn-primary w-auto">     
                            Update                      
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Hour Modal -->

    <!-- BEGIN: Add WBL Profile Modal -->
    <div id="addWBLProfileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addWBLProfileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add WBL Profile</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">WEIF form provided</label>
                            </div>
                            <div class="col-span-4">
                                <input name="weif_form_provided_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_1_1" class="form-check-input" type="radio" name="weif_form_provided_status" value="1">
                                        <label class="form-check-label" for="R_1_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_1_0" class="form-check-input" type="radio" name="weif_form_provided_status" value="0">
                                        <label class="form-check-label" for="R_1_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Received completed WEIF form</label>
                            </div>
                            <div class="col-span-4">
                                <input name="received_completed_weif_form_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_2_1" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="1">
                                        <label class="form-check-label" for="R_2_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_2_0" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="0">
                                        <label class="form-check-label" for="R_2_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work hours update by terms</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_hour_update_term_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_3_1" class="form-check-input" type="radio" name="work_hour_update_term_status" value="1">
                                        <label class="form-check-label" for="R_3_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_3_0" class="form-check-input" type="radio" name="work_hour_update_term_status" value="0">
                                        <label class="form-check-label" for="R_3_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook completed</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_complete_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_4_1" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="1">
                                        <label class="form-check-label" for="R_4_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_4_0" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="0">
                                        <label class="form-check-label" for="R_4_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook checked</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_checked_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_5_1" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="1">
                                        <label class="form-check-label" for="R_5_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_5_0" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="0">
                                        <label class="form-check-label" for="R_5_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer handbook sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_handbook_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_6_1" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="1">
                                        <label class="form-check-label" for="R_6_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_6_0" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="0">
                                        <label class="form-check-label" for="R_6_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employers letter sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_letter_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_7_1" class="form-check-input" type="radio" name="emp_letter_sent_status" value="1">
                                        <label class="form-check-label" for="R_7_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_7_0" class="form-check-input" type="radio" name="emp_letter_sent_status" value="0">
                                        <label class="form-check-label" for="R_7_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer Confirmation Received</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_confirm_rec_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_8_1" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="1">
                                        <label class="form-check-label" for="R_8_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_8_0" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="0">
                                        <label class="form-check-label" for="R_8_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Company visit</label>
                            </div>
                            <div class="col-span-4">
                                <input name="company_visit_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_9_1" class="form-check-input" type="radio" name="company_visit_status" value="1">
                                        <label class="form-check-label" for="R_9_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_9_0" class="form-check-input" type="radio" name="company_visit_status" value="0">
                                        <label class="form-check-label" for="R_9_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of student meetings</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_std_meeting_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_10_1" class="form-check-input" type="radio" name="record_std_meeting_status" value="1">
                                        <label class="form-check-label" for="R_10_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_10_0" class="form-check-input" type="radio" name="record_std_meeting_status" value="0">
                                        <label class="form-check-label" for="R_10_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of all contacts to student (Calls, Class visit, text, letter)</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_all_contact_student_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_11_1" class="form-check-input" type="radio" name="record_all_contact_student_status" value="1">
                                        <label class="form-check-label" for="R_11_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_11_0" class="form-check-input" type="radio" name="record_all_contact_student_status" value="0">
                                        <label class="form-check-label" for="R_11_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Email sent to employer</label>
                            </div>
                            <div class="col-span-4">
                                <input name="email_sent_emp_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_12_1" class="form-check-input" type="radio" name="email_sent_emp_status" value="1">
                                        <label class="form-check-label" for="R_12_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_12_0" class="form-check-input" type="radio" name="email_sent_emp_status" value="0">
                                        <label class="form-check-label" for="R_12_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addWBL" class="btn btn-primary w-auto">     
                            Save                      
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="student_work_placement_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add WBL Profile Modal -->

    <!-- BEGIN: Edit WBL Profile Modal -->
    <div id="editWBLProfileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editWBLProfileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit WBL Profile</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">WEIF form provided</label>
                            </div>
                            <div class="col-span-4">
                                <input name="weif_form_provided_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_1_1" class="form-check-input" type="radio" name="weif_form_provided_status" value="1">
                                        <label class="form-check-label" for="E_R_1_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_1_0" class="form-check-input" type="radio" name="weif_form_provided_status" value="0">
                                        <label class="form-check-label" for="E_R_1_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Received completed WEIF form</label>
                            </div>
                            <div class="col-span-4">
                                <input name="received_completed_weif_form_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_2_1" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="1">
                                        <label class="form-check-label" for="E_R_2_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_2_0" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="0">
                                        <label class="form-check-label" for="E_R_2_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work hours update by terms</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_hour_update_term_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_3_1" class="form-check-input" type="radio" name="work_hour_update_term_status" value="1">
                                        <label class="form-check-label" for="E_R_3_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_3_0" class="form-check-input" type="radio" name="work_hour_update_term_status" value="0">
                                        <label class="form-check-label" for="E_R_3_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook completed</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_complete_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_4_1" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="1">
                                        <label class="form-check-label" for="E_R_4_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_4_0" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="0">
                                        <label class="form-check-label" for="E_R_4_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook checked</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_checked_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_5_1" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="1">
                                        <label class="form-check-label" for="E_R_5_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_5_0" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="0">
                                        <label class="form-check-label" for="E_R_5_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer handbook sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_handbook_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_6_1" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="1">
                                        <label class="form-check-label" for="E_R_6_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_6_0" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="0">
                                        <label class="form-check-label" for="E_R_6_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employers letter sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_letter_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_7_1" class="form-check-input" type="radio" name="emp_letter_sent_status" value="1">
                                        <label class="form-check-label" for="E_R_7_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_7_0" class="form-check-input" type="radio" name="emp_letter_sent_status" value="0">
                                        <label class="form-check-label" for="E_R_7_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer Confirmation Received</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_confirm_rec_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_8_1" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="1">
                                        <label class="form-check-label" for="E_R_8_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_8_0" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="0">
                                        <label class="form-check-label" for="E_R_8_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Company visit</label>
                            </div>
                            <div class="col-span-4">
                                <input name="company_visit_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_9_1" class="form-check-input" type="radio" name="company_visit_status" value="1">
                                        <label class="form-check-label" for="E_R_9_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_9_0" class="form-check-input" type="radio" name="company_visit_status" value="0">
                                        <label class="form-check-label" for="E_R_9_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of student meetings</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_std_meeting_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_10_1" class="form-check-input" type="radio" name="record_std_meeting_status" value="1">
                                        <label class="form-check-label" for="E_R_10_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_10_0" class="form-check-input" type="radio" name="record_std_meeting_status" value="0">
                                        <label class="form-check-label" for="E_R_10_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of all contacts to student (Calls, Class visit, text, letter)</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_all_contact_student_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_11_1" class="form-check-input" type="radio" name="record_all_contact_student_status" value="1">
                                        <label class="form-check-label" for="E_R_11_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_11_0" class="form-check-input" type="radio" name="record_all_contact_student_status" value="0">
                                        <label class="form-check-label" for="E_R_11_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Email sent to employer</label>
                            </div>
                            <div class="col-span-4">
                                <input name="email_sent_emp_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_12_1" class="form-check-input" type="radio" name="email_sent_emp_status" value="1">
                                        <label class="form-check-label" for="E_R_12_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_12_0" class="form-check-input" type="radio" name="email_sent_emp_status" value="0">
                                        <label class="form-check-label" for="E_R_12_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateWBL" class="btn btn-primary w-auto">     
                            Update                      
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit WBL Profile Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <div id="workplacementDocumentsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="intro-y">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm-UP" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                    <input id="query-UP" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                    <select id="status-UP" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option selected value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go-UP" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset-UP" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div>
                                <button class=" btn btn-primary" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal"><i data-lucide="activity" class="w-4 h-4 mr-2"></i>  Add Document</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="studentWorkPlacementDocumentsTable" data-student="{{ $student->id }}" data-row="0" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
    <!-- END: Add Hour Modal -->

    <!-- BEGIN: Import Modal -->
    <div id="uploadDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.workplacement.documents.store') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="student_workplacement_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="mt-3">
                        <label class="form-label">Document Name</label>
                        <span id="documentNameDisplay" class="block mb-1"></span>
                        <input type="text" name="display_name" value="" class="displayNameInput form-control w-full"/>
                    </div>
                    <div class="mt-3">
                        <label>Hard Copy Checked?</label>
                        <div class="form-check mt-2">
                            <input id="hard_copy_check-1" class="form-check-input" type="radio" value="1" name="hard_copy_check_status" value="vertical-radio-chris-evans">
                            <label class="form-check-label" for="hard_copy_check-1">Yes</label>
                        </div>
                        <div class="form-check mt-2">
                            <input checked id="hard_copy_check-2" class="form-check-input" type="radio" value="0" name="hard_copy_check_status" value="vertical-radio-liam-neeson">
                            <label class="form-check-label" for="hard_copy_check-2">No</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadDocBtn" class="btn btn-primary w-auto">     
                        Upload                      
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Import Modal -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-workplacement.js')
    @vite('resources/js/student-wbl-profile.js')
    @vite('resources/js/student-workplacement-documents.js')

    <script type="module">
        (function(){
            document.addEventListener('DOMContentLoaded', function() {
                let accordionButtons = document.querySelectorAll('.accordion-button');
                if(accordionButtons.length > 0) {        
                    accordionButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const targetId = this.getAttribute('data-target');
                            const target = document.querySelector(targetId);
                            const plusIcon = this.querySelector('.accordion-icon-plus');
                            const minusIcon = this.querySelector('.accordion-icon-minus');
                            
                            target.classList.toggle('collapse');
                            target.classList.toggle('show');
                            
                            plusIcon.classList.toggle('hidden');
                            minusIcon.classList.toggle('hidden');
                            
                            const isExpanded = this.getAttribute('aria-expanded') === 'true';
                            this.setAttribute('aria-expanded', !isExpanded);
                        });
                    });
                }
            });
        })()
    </script>

@endsection