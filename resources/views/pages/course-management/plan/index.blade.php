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
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.course-management.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Class plans List</h2>
                    <a href="{{ route('class.plan.add') }}" class="add_btn btn btn-primary shadow-md ml-auto">Add New Plan</a>
                </div>
                <div class="p-5">
                    <form id="tabulatorFilterForm-CPL">
                        <div class="grid grid-cols-12 gap-3 gap-y-2">
                            <div class="col-span-3">
                                <label class="form-label flex items-center">Courses <i data-loading-icon="three-dots" class="w-6 h-6 ml-3 courseCplLoader hidden"></i></label>
                                <select id="courses-CPL" name="courses" class="w-full tom-selects">
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label flex items-center">Terms <i data-loading-icon="three-dots" class="w-6 h-6 ml-3 termCplLoader hidden"></i></label>
                                <select data-placeholder="Select Term" id="instance_term-CPL" name="term_declaration_id" class="w-full tom-selects">
                                    <option value="">Please Select</option>
                                    @if(!empty($terms))
                                        @foreach($terms as $trm)
                                            <option value="{{ $trm->id }}">{{ $trm->name }} - {{ $trm->termType->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label">Groups</label>
                                <select data-placeholder="Select Group" id="group-CPL" name="groups" class="w-full tom-selects">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label">Tutors</label>
                                <select data-placeholder="Select Tutor" id="tutor-CPL" name="tutors[]" class="tom-selects w-full" multiple>
                                    @if(!empty($tutor))
                                        @foreach($tutor as $tr)
                                            <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label">Personal Tutors</label>
                                <select data-placeholder="Select Tutor" id="ptutor-CPL" name="ptutors[]" class="tom-selects w-full" multiple>
                                    @if(!empty($ptutor))
                                        @foreach($ptutor as $ptr)
                                            <option value="{{ $ptr->id }}">{{ $ptr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label">Rooms</label>
                                <select data-placeholder="Select Room" id="room-CPL" name="rooms[]" class="w-full tom-selects" multiple>
                                    @if(!empty($room))
                                        @foreach($room as $rm)
                                            <option value="{{ $rm->id }}">{{ $rm->venue->name }} - {{ $rm->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Days</label>
                                <select data-placeholder="Select Tutor" id="days-CPL" name="days[]" class="tom-selects w-full" multiple>
                                    <option value="mon">Mon</option>
                                    <option value="tue">Tue</option>
                                    <option value="wed">Wed</option>
                                    <option value="thu">Thu</option>
                                    <option value="fri">Fri</option>
                                    <option value="sat">Sat</option>
                                    <option value="sun">Sun</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Date</label>
                                <input type="text" name="date_cpl" id="date-CPL" class="w-full form-control datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Status</label>
                                <select id="statusCPL" name="status" class="w-full form-control">
                                    <option value="1" selected>Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Views</label>
                                <select id="view-CPL" name="view" class="w-full form-control">
                                    <option value="1" selected>List View</option>
                                    <option value="2">Grid View</option>
                                    <option value="3">Tree View</option>
                                </select>
                            </div>
                            <div class="col-span-12"></div>
                            <div class="col-span-6">
                                <button id="tabulator-html-filter-go-CPL" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-CPL" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                            <div class="col-span-6 text-right">
                                <div class="flex mt-5 sm:mt-0 justify-end">
                                    <button type="button" id="exportPlansXLSX" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XL 
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                            stroke="#164e63e6" class="w-4 h-4 ml-2 theLoader">
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
                                    <button id="generateDaysBtn" style="display: none;" type="button" class="btn btn-primary shadow-md ml-2 w-auto">
                                        Generate Days
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
                    </form>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="classPlansListTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- BEGIN: Add Modal -->
    <div id="editPlanModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editPlanForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Plan</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                    <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-6">
                                <div class="grid grid-cols-12 gap-0">
                                    <label class="col-span-4"><div class="text-left text-slate-500 font-medium">Term</div></label>
                                    <div class="col-span-8"><div class="text-left font-medium font-bold termName">Term Name</div></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div class="grid grid-cols-12 gap-0">
                                    <label class="col-span-4"><div class="text-left text-slate-500 font-medium">Course</div></label>
                                    <div class="col-span-8"><div class="text-left font-medium font-bold courseName">Course Name</div></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div class="grid grid-cols-12 gap-0">
                                    <label class="col-span-4"><div class="text-left text-slate-500 font-medium">Group</div></label>
                                    <div class="col-span-8"><div class="text-left font-medium font-bold groupName">Group Name</div></div>
                                </div>
                            </div>
                            <div class="col-span-6"></div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="module_creation_id" class="form-label">Module <span class="text-danger">*</span></label>
                                <select id="module_creation_id" name="module_creation_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                </select>
                                <div class="acc__input-error error-module_creation_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="rooms_id" class="form-label">Room <span class="text-danger">*</span></label>
                                <select id="rooms_id" name="rooms_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($room))
                                        @foreach($room as $rm)
                                            <option value="{{ $rm->id }}">{{ $rm->name }} - {{ $rm->venue->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-rooms_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="class_type" class="form-label">Class Type <span class="text-danger">*</span></label>
                                <select id="class_type" name="class_type" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="Theory">Theory</option>
                                    <option value="Practical">Practical</option>
                                    <option value="Tutorial">Tutorial</option>
                                    <option value="Seminar">Seminar</option>
                                </select>
                                <div class="acc__input-error error-class_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4 tutorWrap" style="display: none;">
                                <label for="tutor_id" class="form-label">Tutor <span class="text-danger">*</span></label>
                                <select id="tutor_id" name="tutor_id" class="tom-selects w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($tutor))
                                        @foreach($tutor as $tr)
                                            <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-tutor_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4 PersonalTutorWrap">
                                <label for="personal_tutor_id" class="form-label">Personal Tutor</label>
                                <select id="personal_tutor_id" name="personal_tutor_id" class="tom-selects w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($ptutor))
                                        @foreach($ptutor as $ptr)
                                            <option value="{{ $ptr->id }}">{{ $ptr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-personal_tutor_id text-danger mt-2"></div>
                            </div>
                            {{--<div class="col-span-6 sm:col-span-4">
                                <label for="module_enrollment_key" class="form-label">Enrollment Key <span class="text-danger">*</span></label>
                                <input id="module_enrollment_key" type="text" name="module_enrollment_key" class="form-control w-full">
                                <div class="acc__input-error error-module_enrollment_key text-danger mt-2"></div>
                            </div>--}}
                            <div class="col-span-6 sm:col-span-4">
                                <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input id="start_time" type="text" name="start_time" class="form-control w-full theTimeField" placeholder="00:00">
                                <div class="acc__input-error error-start_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                <input id="end_time" type="text" name="end_time" class="form-control w-full theTimeField" placeholder="00:00">
                                <div class="acc__input-error error-end_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="submission_date" class="form-label">Submission Date</label>
                                <input id="submission_date" type="text" name="submission_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                <div class="acc__input-error error-submission_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label class="form-label">Class Day <span class="text-danger">*</span></label>
                                <div class="flex flex-col sm:flex-row mt-2">
                                    <div class="form-check mr-3">
                                        <input id="day_mon" class="form-check-input" type="radio" name="class_day" value="mon">
                                        <label class="form-check-label" for="day_mon">Mon</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_tue" class="form-check-input" type="radio" name="class_day" value="tue">
                                        <label class="form-check-label" for="day_tue">Tue</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_wed" class="form-check-input" type="radio" name="class_day" value="wed">
                                        <label class="form-check-label" for="day_wed">Wed</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_thu" class="form-check-input" type="radio" name="class_day" value="thu">
                                        <label class="form-check-label" for="day_thu">Thu</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_fri" class="form-check-input" type="radio" name="class_day" value="fri">
                                        <label class="form-check-label" for="day_fri">Fri</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_sat" class="form-check-input" type="radio" name="class_day" value="sat">
                                        <label class="form-check-label" for="day_sat">Sat</label>
                                    </div>
                                    <div class="form-check mr-3">
                                        <input id="day_sun" class="form-check-input" type="radio" name="class_day" value="sun">
                                        <label class="form-check-label" for="day_sun">Sun</label>
                                    </div>
                                </div>
                                <div class="acc__input-error error-class_day text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="virtual_room" class="form-label">Virtual Room</label>
                                <textarea id="virtual_room" name="virtual_room" class="form-control w-full"></textarea>
                                <div class="acc__input-error error-virtual_room text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="note" class="form-label">Note</label>
                                <textarea id="note" name="note" class="form-control w-full"></textarea>
                                <div class="acc__input-error error-note text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updatePlans" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalCP" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleCP"></div>
                        <div class="text-slate-500 mt-2 successModalDescCP"></div>
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
    <div id="confirmModalCP" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCP">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCP"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCP btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModalCP" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitleCP">Oops!</div>
                        <div class="text-slate-500 mt-2 warningModalDescCP"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">OK, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/plan.js')
@endsection