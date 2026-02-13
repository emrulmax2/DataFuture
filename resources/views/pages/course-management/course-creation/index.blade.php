@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div id="settings-sidebar" class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.course-management.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div id="courseCreation-content" class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Course Creations List</h2>
                    <div class="flex items-center ml-auto">
                        <button id="toggleSidebarBtn" type="button" class="btn btn-outline-secondary mr-2" title="Toggle sidebar">
                            <i data-lucide="chevrons-left" class="w-4 h-4"></i>
                        </button>
                        <button data-tw-toggle="modal" data-tw-target="#addCourseCreationModal" type="button" class="add_btn btn btn-primary shadow-md">Add Course Creation</button>
                    </div>
                </div>
                <div class="p-5">
                    <form id="tabulatorFilterForm-01">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-2">
                                <label class="form-label">Query</label>
                                <input id="query-01" name="query" type="text" class="form-control w-full"  placeholder="Search...">
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label class="form-label">Course</label>
                                <select id="course-01" name="course" class="form-select w-full" >
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-2">
                                <label class="form-label">Semester</label>
                                <select id="semester-01" name="semester" class="form-select w-full" >
                                    <option value="">Please Select</option>
                                    @if(!empty($semesters))
                                        @foreach($semesters as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-2">
                                <label class="form-label">Status</label>
                                <select id="status-01" name="status" class="form-select w-full" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-3 text-right pt-4">
                                <button id="tabulator-html-filter-go-01" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-01" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                <div class="flex justify-end ml-1 mt-2">
                                    <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                    </button>
                                    <div class="dropdown w-1/2 sm:w-auto ml-1">
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
                        </div>
                    </form>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="courseCreationTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="addCourseCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addCourseCreationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Course Creation</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="course_id" name="course_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-course_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="course_creation_qualification_id" class="form-label">Qualification <span class="text-danger">*</span></label>
                                <select id="course_creation_qualification_id" name="course_creation_qualification_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($qualifications))
                                        @foreach($qualifications as $qua)
                                            <option value="{{ $qua->id }}">{{ $qua->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-course_creation_qualification_id text-danger mt-2"></div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select id="semester_id" name="semester_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($semesters))
                                        @foreach($semesters as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-semester_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="duration" class="form-label">Duration <span class="text-danger">*</span></label>
                                <input id="duration" type="number" name="duration" class="form-control w-full">
                                <div class="acc__input-error error-duration text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="unit_length" class="form-label">Unit Length <span class="text-danger">*</span></label>
                                <select id="unit_length" name="unit_length" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="Years">Years</option>
                                    <option value="Months">Months</option>
                                    <option value="Days">Days</option>
                                    <option value="Hours">Hours</option>
                                    <option value="Not applicable">Not applicable</option>
                                </select>
                                <div class="acc__input-error error-unit_length text-danger mt-2"></div>
                            </div> 
                            
                            <div class="col-span-12 sm:col-span-6">
                                <label for="fees" class="form-label">Fees(UK)</label>
                                <input id="fees" type="number" step="any" name="fees" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="reg_fees" class="form-label">Reg. Fees(UK)</label>
                                <input id="reg_fees" type="number" step="any" name="reg_fees" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="university_commission" class="form-label">University Commission</label>
                                <input id="university_commission" type="number" step="any" name="university_commission" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6 commissionAmountWrap" style="display: none;">
                                <label for="university_commission" class="form-label mb-1">Commission Amount</label>
                                <div class="font-medium text-danger leading-none">£0.00</div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="has_evening_and_weekend" class="form-label">Has Evening / Weekend</label>
                                <div class="form-check form-switch">
                                    <input id="has_evening_and_weekend" name="has_evening_and_weekend" class="form-check-input" value="1" type="checkbox">
                                    <label class="form-check-label ml-3 hew_label" for="has_evening_and_weekend">No</label>
                                </div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="is_workplacement" class="form-label">Has Workplacement</label>
                                <div class="form-check form-switch">
                                    <input id="is_workplacement" name="is_workplacement" class="form-check-input" value="1" type="checkbox">
                                    <label class="form-check-label ml-3 iwkp_label" for="is_workplacement">No</label>
                                </div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6 requiredHoursWrap" style="display: none;">
                                <label for="required_hours" class="form-label">Required Hours</label>
                                <input id="required_hours" type="number" step="any" name="required_hours" class="form-control w-full">
                                <div class="acc__input-error error-required_hours text-danger mt-2"></div>
                            </div> 
                        </div>
                        <div class="grid grid-cols-12 gap-3 border border-gray-100 p-5 mt-2">
                            
                            <div class="col-span-12 text-right">
                                <button class="venueAdd btn btn-primary w-auto text-shite">
                                    <i data-tw-merge data-lucide="plus" class="stroke-1.5  mr-2 h-4 w-4"></i>
                                    Add more venue 
                                    <i  data-loading-icon="oval" data-color="white" class="load-icon w-4 h-4 ml-2 hidden"></i>
                                </button>
                            </div>
                            <div class="col-span-12">
                                <table id="add-newvenue" class="table table-sm table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Venue</th>
                                            <th>SLC Code</th>
                                            <th>Evening/Weekend</th>
                                            <th>Weekdays</th>
                                            <th>Weekends</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="w-2/6">
                                                <select id="venue_id1" name="venue_id[]" class="form-control w-full">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($venues))
                                                        @foreach($venues as $vn)
                                                            <option value="{{ $vn->id }}">{{ $vn->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td class="w-1/6">
                                                <input id="slc_code1" type="text" name="slc_code[]" class="form-control w-full">
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0 justify-center">
                                                    <input name="evWkToggle[]" class="form-check-input eveningAndWeekend" value="1" type="checkbox">
                                                </div>
                                                <input type="hidden" class="evening_and_weekend" name="evening_and_weekend[]" value="0"/>
                                            </td>
                                            <td>
                                                <input type="number" class="w-full form-control weekdays" step="1" name="weekdays[]" value=""/>
                                            </td>
                                            <td>
                                                <input readonly type="number" class="w-full form-control weekends" step="1" name="weekends[]" value=""/>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>      
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveCourseCreation" class="btn btn-primary w-auto">
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editCourseCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editCourseCreationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Course Creation</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_course_id" class="form-label">Course <span class="text-danger">*</span></label>
                                <select id="edit_course_id" name="course_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-course_id text-danger mt-2"></div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_course_creation_qualification_id" class="form-label">Qualification <span class="text-danger">*</span></label>
                                <select id="edit_course_creation_qualification_id" name="course_creation_qualification_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($qualifications))
                                        @foreach($qualifications as $qua)
                                            <option value="{{ $qua->id }}">{{ $qua->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-course_creation_qualification_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select id="edit_semester_id" name="semester_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($semesters))
                                        @foreach($semesters as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-semester_id text-danger mt-2"></div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_duration" class="form-label">Duration <span class="text-danger">*</span></label>
                                <input id="edit_duration" type="number" name="duration" class="form-control w-full">
                                <div class="acc__input-error error-duration text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_unit_length" class="form-label">Unit Length <span class="text-danger">*</span></label>
                                <select id="edit_unit_length" name="unit_length" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="Years">Years</option>
                                    <option value="Months">Months</option>
                                    <option value="Days">Days</option>
                                    <option value="Hours">Hours</option>
                                    <option value="Not applicable">Not applicable</option>
                                </select>
                                <div class="acc__input-error error-unit_length text-danger mt-2"></div>
                            </div>
                            {{-- <div class="col-span-12 sm:col-span-6">
                                <label for="edit_slc_code" class="form-label">SLC Code</label>
                                <input id="edit_slc_code" type="text" name="slc_code" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="venue_id" class="form-label">Venue</label>
                                <select id="venue_id" name="venue_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($venues))
                                        @foreach($venues as $vn)
                                            <option value="{{ $vn->id }}">{{ $vn->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>  --}}
                            <div class="col-span-12 sm:col-span-6">
                                <label for="fees" class="form-label">Fees(UK)</label>
                                <input id="edit_fees" type="number" step="any" name="fees" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="reg_fees" class="form-label">Reg. Fees(UK)</label>
                                <input id="edit_reg_fees" type="number" step="any" name="reg_fees" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_university_commission" class="form-label">University Commission</label>
                                <input id="edit_university_commission" type="number" step="any" name="university_commission" class="form-control w-full">
                            </div> 
                            <div class="col-span-12 sm:col-span-6 editCommissionAmountWrap" style="display: none;">
                                <label for="university_commission" class="form-label mb-1">Commission Amount</label>
                                <div class="font-medium text-danger leading-none">£0.00</div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_has_evening_and_weekend" class="form-label">Has Evening / Weekend</label>
                                <div class="form-check form-switch">
                                    <input id="edit_has_evening_and_weekend" name="has_evening_and_weekend" class="form-check-input" value="1" type="checkbox">
                                    <label class="form-check-label ml-3 hew_label" for="edit_has_evening_and_weekend">No</label>
                                </div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_is_workplacement" class="form-label">Workplacement</label>
                                <div class="form-check form-switch">
                                    <input id="edit_is_workplacement" name="is_workplacement" class="form-check-input" value="1" type="checkbox">
                                    <label class="form-check-label ml-3 iwkp_label" for="is_workplacement">No</label>
                                </div>
                            </div> 
                            <div class="col-span-12 sm:col-span-6 requiredHoursWrap" style="display: none;">
                                <label for="edit_required_hours" class="form-label">Required Hours</label>
                                <input id="edit_required_hours" type="number" step="any" name="required_hours" class="form-control w-full">
                                <div class="acc__input-error error-required_hours text-danger mt-2"></div>
                            </div> 
                        </div>
                        <div class="grid grid-cols-12 gap-3 border border-gray-100 p-5 mt-2">
                            
                            <div class="col-span-12 text-right">
                                <button class="venueAddForEdit  btn btn-primary w-auto text-shite">
                                    <i data-tw-merge data-lucide="plus" class="stroke-1.5  mr-2 h-4 w-4"></i>
                                    Add more venue 
                                    <i  data-loading-icon="oval" data-color="white" class="load-icon w-4 h-4 ml-2 hidden"></i>
                                </button>
                            </div>
                            <div class="col-span-12">
                                <table id="edit-newvenue" class="table table-sm table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Venue</th>
                                            <th>SLC Code</th>
                                            <th>Evening/Weekend</th>
                                            <th>Weekdays</th>
                                            <th>Weekends</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="acc__input-error error-venue_id text-danger mt-2 col-span-12"></div>
                        </div>    
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateCourseCreation" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <div id="confirmModalVenue" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithVenue btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/course-creation.js')
@endsection