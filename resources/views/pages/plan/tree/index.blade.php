@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Class plan Tree</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('class.plan') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to List</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="grid grid-cols-12 gap-6  mt-5">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <div class="intro-y box p-5">
                <div class="planTreeWrap">
                    @if(!empty($acyers))
                        <ul class="classPlanTree">
                            @foreach($acyers as $year)
                                @if(isset($year->terms) && $year->terms->count() > 0)
                                    <li class="hasChildren">
                                        <a href="javascript:void(0);" data-yearid="{{ $year->id }}" class="academicYear flex items-center text-primary font-medium">{{ $year->name }} <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <div class="intro-y box p-5">
                <div class="classPlanTreeResultWrap" style="display: none;"></div>
                <div class="classPlanTreeResultNotice">
                    <div class="alert alert-success-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select a group to view the details.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Assign Manager Or Co-Ordinator Modal -->
    <div id="assignManagerOrCoOrdinatorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="assignManagerOrCoOrdinatorForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto theModTitle">Assign <span class="assignRoleTitle">Role</span></h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12">
                                <label for="assigned_user_ids" class="form-label">Existing Staffs <span class="text-danger">*</span></label>
                                <select id="assigned_user_ids" name="assigned_user_ids[]" class="lcc-tom-select w-full" multiple>
                                    <option value="">Please Select</option>
                                    @if(!empty($users))
                                        @foreach($users as $ur)
                                            <option value="{{ $ur->id }}">{{ $ur->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-assigned_user_ids text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateParticipants" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="plan_ids" value=""/>
                        <input type="hidden" name="type" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Assign Manager Or Co-Ordinator Modal -->

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
                                    <label class="col-span-4"><div class="text-left text-slate-500 font-medium">Course</div></label>
                                    <div class="col-span-8"><div class="text-left font-medium font-bold courseName">Course Name</div></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div class="grid grid-cols-12 gap-0">
                                    <label class="col-span-4"><div class="text-left text-slate-500 font-medium">Module</div></label>
                                    <div class="col-span-8"><div class="text-left font-medium font-bold moduleName">Module Name</div></div>
                                </div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="group_id" class="form-label">Group <span class="text-danger">*</span></label>
                                <select id="group_id" name="group_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($group))
                                        @foreach($group as $gr)
                                            <option value="{{ $gr->id }}">{{ $gr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-group_id text-danger mt-2"></div>
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
                                <label for="tutor_id" class="form-label">Tutor <span class="text-danger">*</span></label>
                                <select id="tutor_id" name="tutor_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($tutor))
                                        @foreach($tutor as $tr)
                                            <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-tutor_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="personal_tutor_id" class="form-label">Personal Tutor <span class="text-danger">*</span></label>
                                <select id="personal_tutor_id" name="personal_tutor_id" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($ptutor))
                                        @foreach($ptutor as $ptr)
                                            <option value="{{ $ptr->id }}">{{ $ptr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-personal_tutor_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="class_type" class="form-label">Class Type</label>
                                <select id="class_type" name="class_type" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="Theory">Theory</option>
                                    <option value="Practical">Practical</option>
                                    <option value="Tutorial">Tutorial</option>
                                    <option value="Seminar">Seminar</option>
                                </select>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="module_enrollment_key" class="form-label">Enrollment Key <span class="text-danger">*</span></label>
                                <input id="module_enrollment_key" type="text" name="module_enrollment_key" class="form-control w-full">
                                <div class="acc__input-error error-module_enrollment_key text-danger mt-2"></div>
                            </div>
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
                                <label for="submission_date" class="form-label">Submission Date <span class="text-danger">*</span></label>
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
    @vite('resources/js/plan-tree.js')
@endsection