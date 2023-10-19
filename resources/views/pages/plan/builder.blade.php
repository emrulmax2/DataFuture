@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Class Plan Builder</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('class.plan') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form method="post" action="#" id="classPlanBuilderForm">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <h2 class="text-xl font-medium mb-4 text-left"><u>{{ $moduleCreation->module_name }}</u></h2>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Course:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold"><u>{{ (!empty($course->name) ? ucfirst($course->name) : '---')  }}</u></div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Code:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($moduleCreation->code) ? ucfirst($moduleCreation->code) : '---')  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Status:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ !empty($moduleCreation->status) ? $moduleCreation->status : $moduleCreation->status  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Credit Value:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ !empty($moduleCreation->credit_value) ? $moduleCreation->credit_value : '---'  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Unit Value:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ !empty($moduleCreation->unit_value) ? $moduleCreation->unit_value : '---'  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Enrollment Key:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ !empty($moduleCreation->moodle_enrollment_key) ? $moduleCreation->moodle_enrollment_key : '---'  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Class Type:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ !empty($moduleCreation->class_type) ? $moduleCreation->class_type : '---'  }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Submission Date:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($moduleCreation->submission_date) ? $moduleCreation->submission_date : '---') }}</div></div>
                    </div>
                </div>

                <div class="col-span-12">
                    <h2 class="text-xl font-medium mt-5 mb-4 text-left"><u>{{ $instanceTerm->name }}</u></h2>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Term:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold"><u>{{ $instanceTerm->term  }}</u></div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Session Term:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->session_term) ? 'Term '. $instanceTerm->session_term : 'Unknown') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Start Date:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->start_date) ? $instanceTerm->start_date : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">End Date:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->end_date) ? $instanceTerm->end_date : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching Weeks:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->total_teaching_weeks) ? $instanceTerm->total_teaching_weeks : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching Start:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->teaching_start_date) ? $instanceTerm->teaching_start_date : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching End:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->teaching_end_date) ? $instanceTerm->teaching_end_date : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Revision Start:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->revision_start_date) ? $instanceTerm->revision_start_date : '---') }}</div></div>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4"><div class="text-left text-slate-500 font-medium">Revision End:</div></div>
                        <div class="col-span-8"><div class="text-left font-medium font-bold">{{ (!empty($instanceTerm->revision_end_date) ? $instanceTerm->revision_end_date : '---') }}</div></div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-end xl:items-center mt-7 pt-3 border-t">
                <div class="xl:flex sm:mr-auto" >
                    <h2 class="text-xl font-medium mt-0 mb-0 text-left"><u>Routine Sets</u></h2>
                </div>
                <div class="flex mr-auto">
                    <span class="btn btn-course px-2 py-1 text-white"><i data-lucide="book" class="w-4 h-4 mr-1"></i> Course</span>
                    <span class="btn btn-module px-2 py-1 text-white"><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> Module</span>
                    <span class="btn btn-tutor px-2 py-1 text-white"><i data-lucide="user" class="w-4 h-4 mr-1"></i> Tutor</span>
                    <span class="btn btn-ptutor px-2 py-1 text-white"><i data-lucide="user-check" class="w-4 h-4 mr-1"></i> Personal Tutor</span>
                    <span class="btn btn-time px-2 py-1 text-white"><i data-lucide="clock" class="w-4 h-4 mr-1"></i> Time</span>
                    <span class="btn btn-class-type px-2 py-1 text-white"><i data-lucide="columns" class="w-4 h-4 mr-1"></i> Class Type</span>
                    <span class="btn btn-group px-2 py-1 text-white"><i data-lucide="tag" class="w-4 h-4 mr-1"></i> Group</span>
                    <span class="btn btn-ekey px-2 py-1 text-white"><i data-lucide="key" class="w-4 h-4 mr-1"></i> Enrollment</span>
                    <span class="btn btn-submission px-2 py-1 text-white"><i data-lucide="calendar" class="w-4 h-4 mr-1"></i> Submission</span>
                    <span class="btn btn-vroom px-2 py-1 text-white"><i data-lucide="video" class="w-4 h-4 mr-1"></i> Virtual Room</span>
                    <span class="btn btn-note px-2 py-1 text-white"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> Note</span>
                </div>
                <div class="flex">
                    <button id="saveUpdatePlans" type="submit" class="btn btn-primary w-auto" >
                        Save or Update
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
            <div class="overflow-x-auto mt-5">
                <table class="table table-striped table-bordered routineBuilderTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">Day</th>
                            @if(!empty($rooms))
                                @foreach($rooms as $rm)
                                    <th class="whitespace-nowrap">{{ $rm->name }} - {{ isset($rm->venue->name) ? $rm->venue->name : '' }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $day = [ 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                        @endphp
                        @for($i = 1; $i <= 7; $i++)
                            <tr data-day="{{ $i }}" class="routineRow">
                                <td class="text-center font-bold">{{ $day[$i] }}</td>
                                @if(!empty($rooms))
                                @foreach($rooms as $rm)
                                    <td class="routineDay relative" data-venuRoom="{{ $rm->venue_id }}_{{ $rm->id }}">
                                        <div class="routineDayBoxes">
                                            @if(isset($plans[$i][$rm->id]) && !empty($plans[$i][$rm->id]))
                                                {!! $plans[$i][$rm->id] !!}
                                            @endif
                                        </div>
                                        <button data-day="{{ $i }}" data-venue="{{ $rm->venue_id }}" data-room="{{ $rm->id }}" type="button" class="addPlanBox btn btn-success text-white absolute r-0 b-0 px-2 py-2 theAddBTN">
                                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                @endforeach
                            @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <input type="hidden" id="course_id" name="course_id" value="{{ $course->id }}"/>
            <input type="hidden" id="instance_term_id" name="instance_term_id" value="{{ $instanceTerm->id }}"/>
            <input type="hidden" id="module_creation_id" name="module_creation_id" value="{{ $moduleCreation->id }}"/>
        </form>
    </div>
    <!-- END: HTML Table Data -->
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalCPB2" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleCPB2"></div>
                        <div class="text-slate-500 mt-2 successModalDescCPB2"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" type="button" class="btn btn-primary w-auto">Ok, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content --> 
    
    <!-- BEGIN: Success Modal Content -->
    <div id="successModalCPB" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitleCPB"></div>
                        <div class="text-slate-500 mt-2 successModalDescCPB"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <a href="#" class="btn btn-primary w-auto">Ok, Got it</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->  

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModalCPB" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitleCPB"></div>
                        <div class="text-slate-500 mt-2 warningModalDescCPB"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" type="button" class="btn btn-primary w-24">Ok, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCPB" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCPB">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCPB"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="NONE" class="agreeWithCPB btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/plan.js')
@endsection