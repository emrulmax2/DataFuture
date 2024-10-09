@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 mt-8">
            <div class="intro-y flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">All Pending Tasks</h2>
                <div class="ml-auto text-right inline-flex items-center">
                    <div class="dropdown inline-flex" data-tw-placement="bottom-end">
                        <button class="dropdown-toggle btn btn-success text-white" aria-expanded="false" data-tw-toggle="dropdown"><i class="w-5 h-5" data-lucide="list-checks"></i></button>
                        <div class="dropdown-menu w-60">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item" data-tw-toggle="modal" data-tw-target="#addPearsonRegTaskModal">
                                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2 text-success"></i> Create Pearson Reg. Task
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item" data-tw-toggle="modal" data-tw-target="#updateBulkStatusModal">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i> Change Bulk Status
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>  
                    {{--<button type="button" data-tw-toggle="modal" data-tw-target="#addPearsonRegTaskModal" class="btn btn-facebook text-white mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Create Pearson Reg. Task</button>--}}
                    <a href="{{ route('task.manager') }}" class="btn btn-primary text-white ml-2">
                        Back to Task Manager
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6 mt-8">
                @if(!empty($processTasks))
                    <div class="col-span-12">
                        <div id="userPendingTaskAccordion" class="accordion mb-2">
                            @foreach($processTasks as $process_id => $process)
                                @if($process['outstanding_tasks'] > 0)
                                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                                        <div id="userPendingTaskAccordion-{{ $loop->index }}" class="accordion-header">
                                            <button class="accordion-button collapsed relative w-full text-lg font-semibold px-5 flex items-center" type="button" data-tw-toggle="collapse" data-tw-target="#userPendingTaskAccordion-collapse-{{ $loop->index }}" aria-expanded="false" aria-controls="userPendingTaskAccordion-collapse-{{ $loop->index }}">
                                                {{ $process['name'] }} 
                                                <span class="w-10 h-10 justify-center items-center inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-auto relative">{{ $process['outstanding_tasks'] }}</span>
                                            </button>
                                        </div>
                                        <div id="userPendingTaskAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="userPendingTaskAccordion-{{ $loop->index }}" data-tw-parent="#userPendingTaskAccordion">
                                            <div class="accordion-body px-5 border-t pt-5">
                                                <div class="grid grid-cols-12 gap-6">
                                                    @if(isset($process['tasks']) && !empty($process['tasks']))
                                                        @foreach($process['tasks'] as $task_id => $pts)
                                                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                                                                <a href="{{ route('task.manager.show', $task_id) }}" class="intro-x block">
                                                                    <div class="box px-5 py-3 mb-3 flex items-center zoom-in bg-success-soft-1">
                                                                        <div class="mr-auto">
                                                                            <div class="font-medium">{{ $pts->name }}</div>
                                                                            @if(!empty($pts->short_description))
                                                                                <div class="text-slate-500 text-xs mt-0.5">{{ $pts->short_description }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="w-10 h-10 rounded-full bg-primary text-white text-danger inline-flex justify-center items-center font-medium">{{ $pts->pending_task }}</div>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach 
                        </div>
                    </div>
                @else 
                    <div class="col-span-12">
                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> There are no pending Process Task found.
                        </div>
                    </div>
                @endif
                <div class="col-span-12">
                    <a href="{{ route('followups.all') }}" class="relative w-full font-medium text-lg bg-white  px-5 py-4 flex items-center rounded  mb-3">
                        Pending Followups
                        <span class="w-10 h-10 justify-center items-center inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-auto relative">{{ ($followups > 0 ? $followups : 0) }}</span>
                    </a>
                    <a href="{{ route('raised.flags') }}" class="relative w-full font-medium text-lg bg-white  px-5 py-4 flex items-center rounded" >
                        Open Flags
                        <span class="w-10 h-10 justify-center items-center inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-auto relative">{{ ($flags > 0 ? $flags : 0) }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Update Bulk Status Modal -->
    <div id="updateBulkStatusModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="updateBulkStatusForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Bulk Status</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="flex justify-start items-center">
                                <label for="name" class="form-label">Registration No <span class="text-danger">*</span></label>
                                <span class="studentCount ml-auto font-medium text-primary">No of Student: 0</span>
                            </div>
                            <textarea id="bulk_student_ids" name="student_ids" class="form-control w-full" rows="4"></textarea>
                            <div class="acc__input-error error-student_ids text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="change_status_id" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="change_status_id" name="status_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(isset($statuses))
                                    @foreach($statuses as $stst)
                                        <option value="{{ $stst->id }}">{{ $stst->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="term_declaration_id" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="term_declaration_id" name="term_declaration_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_reason" class="form-label">Change Reason</label>
                            <textarea name="status_change_reason" id="status_change_reason" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_date" class="form-label">Change Date <span class="text-danger">*</span></label>
                            <input type="text" name="status_change_date" id="status_change_date" value="<?php echo date('d-m-Y') ?>" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="upBulkStsBtn" class="btn btn-primary w-auto">     
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Bulk Status Modal -->

    <!-- BEGIN: Add Modal -->
    <div id="addPearsonRegTaskModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addPearsonRegTaskForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Pearson Registration</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="flex justify-start items-center">
                                <label for="name" class="form-label">Registration No <span class="text-danger">*</span></label>
                                <span class="studentCount ml-auto font-medium text-primary">No of Student: 0</span>
                            </div>
                            <textarea id="student_ids" name="student_ids" class="form-control w-full" rows="4"></textarea>
                            <div class="acc__input-error error-student_ids text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="PearsonRegBtn" class="btn btn-primary w-auto">     
                            Create Task                      
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
                        <button type="button" data-action="None" class="successCloser btn btn-primary w-24">Ok</button>
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
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/task-manager-all.js')
@endsection
