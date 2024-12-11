<div class="intro-y box">
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center px-5" role="tablist">
        <li id="result-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#result" aria-controls="result" aria-selected="true" role="tab" >
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Result Submission 
            </a>
        </li>
        <li id="log-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#log" aria-controls="log" aria-selected="true" role="tab" >
                <i data-lucide="files" class="w-4 h-4 mr-2"></i> Submission Log
            </a>
        </li>
    </ul>
    </div>
    <div class="intro-y tab-content mt-5">
        <div id="result" class="tab-pane active w-full" role="tabpanel" aria-labelledby="result-tab">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Submission Document</h2>
                    
                    <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#uploadSubmissionDocumentModal" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} class="callModalPlanTask ml-auto transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2"><i data-lucide="activity" class="w-4 h-4 mr-1"></i> Upload Submission</button>
                    
                    <a href="{{ route('results-staff-submission.sample.download',$plan->id) }}" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-warning text-warning dark:border-warning [&:hover:not(:disabled)]:bg-warning/10 mb-2 mr-1 inline-block w-48  ml-2"> Sample Excel</a>
                    @if((isset(auth()->user()->priv()['result_management_staff_delete']) && auth()->user()->priv()['result_management_staff_delete'] == 1))
                                                       
                    <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#confirmDeleteModal" id="deleteBtnAll" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-danger focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-danger text-danger dark:border-danger [&:hover:not(:disabled)]:bg-danger/10 mb-2 mr-1 inline-block w-48">Delete Selected</button>
                    @endif
                </div>
                <div class="p-5 pt-0">
                    <div class="grid grid-cols-12 gap-4">        
                            <div class="col-span-12">
                                <div class="mt-3"> 
                                    <div id="displayError" class="my-3 hidden">
                                        <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger text-white dark:border-danger mb-2 flex items-center"><i data-tw-merge data-lucide="alert-octagon" class="stroke-1.5 w-5 h-5 mr-2 h-6 w-6 mr-2 h-6 w-6"></i>
                                            <span class="errorMessage">TEST TDATA</span>
                                            <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 text-white"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 h-4 w-4 h-4 w-4"></i></button>
                                        </div>
                                        <div role="alert" class="alert relative border rounded-md px-5 my-3 py-4 bg-danger border-danger text-white dark:border-danger mb-2">
                                            <div class="flex items-center">
                                                <div class="text-md font-medium">
                                                    <span class="errorList">Error List</span>
                                                </div>
                                                <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-3 px-3 absolute right-0 my-auto mr-2 text-white"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 "></i></button>
                                            </div>
                                            <div class="mt-3 error-students">TEST TDATA</div>
                                        </div>
                                    </div>
                                        @if((isset($resultSet) && count($resultSet) > 0))
                                        <form id="resultActiveData" method="POST" >
                                            <table class="table table-report -mt-2">
                                                <thead>
                                                    <tr>
                                                        <th class="whitespace-nowrap"><div data-tw-merge class="flex items-center mt-2"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" id="checkbox-switch-all" value="" />
                                                            <label data-tw-merge for="checkbox-switch-all" class="cursor-pointer ml-2">S.N.</label>
                                                        </div></th>
                                                        <th class="whitespace-nowrap">Reg. No</th>
                                                        <th class="whitespace-nowrap">Status</th>
                                                        <th class="whitespace-nowrap">Assessment</th>
                                                        <th class="whitespace-nowrap">Grade</th>   
                                                        <th class="whitespace-nowrap">Attempted</th>  
                                                        <th class="whitespace-nowrap">Updated By</th>  
                                                        {{-- <th class="whitespace-nowrap">Action</th> --}}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                        @php $serial=1; @endphp
                                                        
                                                        @foreach($studentAssignActiveOnly as $assign)
                                                            @php $result = isset($assign->student_id) ? $resultSet[$assign->student_id]['latest'] : null; @endphp
                                                            @php
                                                                $warningCheck = "transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-warning focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-warning [&[type='radio']]:checked:border-warning [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-warning [&[type='checkbox']]:checked:border-warning [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                                                                $primaryCheck ="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                                                                $checkboxCssClass = (isset($result->id)) ? $warningCheck : $primaryCheck ; 
                                                            @endphp
                                                            @if($result!=null)    
                                                            <tr>
                                                                <td class="">
                                                                    <div data-tw-merge class="flex items-center">
                                                                        <input type="hidden" name="student_id[{{ $serial }}]" value="{{ $result->student->id }}" />
                                                                        <input type="hidden" name="assessment_plan_id[{{ $serial }}]" value="{{ $result->assessment_plan_id }}" />
                                                                        <input type="hidden" name="result_id[{{ $serial }}]" value="{{ isset($result->id) ? $result->id : '' }}" />
                                                                        <input data-tw-merge type="checkbox" {{ $assign->attendance===null || $assign->attendance===1 ? '' : 'disabled' }} name="id[{{ $serial }}]" 
                                                                        class="fill-box {{ $checkboxCssClass }}" data-assessment_plan_id={{ $result->assessment_plan_id  }} id="checkbox-switch-{{ $serial }}" value="{{ isset($result->id) ? $result->id : $serial }}" />
                                                                        <label data-tw-merge for="checkbox-switch-{{ $serial }}" class="cursor-pointer ml-2">{{ isset($result->id) ? $result->id : $serial }}</label>
                                                                    </div>
                                                                </td>
                                                                <td class="">
                                                                    <div class="text-lg">
                                                                        <div class="font-medium whitespace-nowrap">{{ $result->student->registration_no }}</div>
                                                                        <div class="text-slate-500 text-xs whitespace-nowrap">{{ $result->student->full_name }} </div>
                                                                    </div>
                                                                </td>
                                                                <td class="">{{ $result->student->status->name }}</td>
                                                                <td class=""> {{ (isset($result->assementPlan)) ? $result->assementPlan->courseModuleBase->assesment_code .'-'. $result->assementPlan->courseModuleBase->assesment_name : '' }}</td>
                                                                <td class=""> {{ $result->grade->code }} - {{ $result->grade->name }} </td>
                                                                <td>
                                                                    <a href="javascript:;" data-theme="light" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $result->id }}" data-trigger="click" class="intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="attempt count">{{ count($resultSet[$assign->student_id]['all']) }}</a>
                                                                </td>
                                                                <td class="">{{ isset($result->updatedBy) ? $result->updatedBy->full_name : $result->createdBy->full_name }}</td>
                                                                {{-- <td class="">
                                                                    @if((isset(auth()->user()->priv()['result_management_staff_delete']) && auth()->user()->priv()['result_management_staff_delete'] == 1))
                    
                                                                      <button type="button" data-id="{{ $result->id }}" data-action="delete" data-url="result" class="delete_btn p-0 border-0 rounded-0 text-danger inline-flex ml-2"><i class="w-4 h-4" data-lucide="trash-2"></i> Delete</button>
                                                                    @endif
                                                                </td> --}}
                                                            </tr>
                                                            @php $serial++; @endphp
                                                            @endif
                                                        @endforeach                                                    
                                                </tbody>
                                            </table>
                                        </form>
                                        @else
                                            <div class="text-center w-full text-xl">No Result Found Yet</div>
                                        @endif
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="log" class="tab-pane" role="tabpanel" aria-labelledby="log-tab">
            <div class="intro-y box">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Module Submission List By Staff</h2>
                </div>
                <div class="p-5 pt-0">
                    <div class="grid grid-cols-12 gap-4">        
                        <div class="col-span-12">
                            <div class="mt-3"> 
                                @if($submissionAssessment->count() > 0)
                                        <table id="staff-submission" class="table table-report -mt-2">
                                            <thead>
                                                <tr>
                                                    <th class="whitespace-nowrap"><div data-tw-merge class="flex items-center mt-2">S.N.
                                                    </div></th>
                                                    <th class="whitespace-nowrap">Assessment</th>
                                                    <th class="whitespace-nowrap">Uploaded By</th>
                                                    <th class="whitespace-nowrap">Submission Date</th>
                                                    <th class="whitespace-nowrap">Action</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($submissionAssessment as $key => $submission)
                                                
                                                <tr>
                                                    <td class="border-b dark:border-darkmode-500 ">
                                                        <div class="mt-3">
                                                            <div class="mt-2">
                                                                {{-- <div data-tw-merge class="flex items-center"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white" id="checkbox-switch-{{ $key+1 }}" />
                                                                    <label data-tw-merge for="checkbox-switch-{{ $key+1 }}" class="cursor-pointer ml-2">{{ $key+1 }}</label>
                                                                </div> --}}
                                                                <div data-tw-merge class="flex items-center mt-2">{{ $key+1 }}
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </td>
                                                    <td class="border-b dark:border-darkmode-500">{{ $submission->courseModuleBase->assesment_name }} - {{ $submission->courseModuleBase->assesment_code }}</td>
                                                    
                                                    <td class="border-b dark:border-darkmode-500">{{ isset($submission->createdBy->employee) ? $submission->createdBy->employee->full_name : "" }}</td>
                                                    <td class="border-b dark:border-darkmode-500">{{ $submission->created_at }}</td>
                                                    <td class="border-b dark:border-darkmode-500">
                                                        @if($submission->is_it_final > 0)
                                                        <a href="javascript:void(0);" data-plan="{{ $plan->id }}" data-assesmentPlanId="{{ $submission->id }}" data-tw-toggle="modal" data-tw-target="#student-preview-modal"  class="edit_btn_submission btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>
                                                        
                                                            @if((isset(auth()->user()->priv()['result_management_staff_delete']) && auth()->user()->priv()['result_management_staff_delete'] == 1))
                                                                <button data-id="{{$submission->id}}" data-action="delete" data-url="staff" class="delete_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9 ml-1"><i class="w-4 h-4" data-lucide="trash-2"></i></button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                        @else
                                        <div class="text-center w-full text-xl">No Submission Found</div>
                                        @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box my-3">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Module Submission List By Tutor</h2>
                </div>
                <div class="p-5 pt-0">
                    <div class="grid grid-cols-12 gap-4">        
                        <div class="col-span-12">
                            <div class="mt-3"> 
                                @if($submissionAssessmentTutor->count() > 0)
                                        <table id="tutor-submission" class="table table-report -mt-2">
                                            <thead>
                                                <tr>
                                                    <th class="whitespace-nowrap"><div data-tw-merge class="flex items-center mt-2">S.N.
                                                    </div></th>
                                                    <th class="whitespace-nowrap">Assessment</th>
                                                    <th class="whitespace-nowrap">Uploaded By</th>
                                                    <th class="whitespace-nowrap">Submission Date</th>
                                                    <th class="whitespace-nowrap">Action</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($submissionAssessmentTutor as $key => $submission)
                                                
                                                <tr>
                                                    <td class="border-b dark:border-darkmode-500 ">
                                                        <div class="mt-3">
                                                            <div class="mt-2">
                                                                {{-- <div data-tw-merge class="flex items-center"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white" id="checkbox-switch-{{ $key+1 }}" />
                                                                    <label data-tw-merge for="checkbox-switch-{{ $key+1 }}" class="cursor-pointer ml-2">{{ $key+1 }}</label>
                                                                </div> --}}
                                                                <div data-tw-merge class="flex items-center mt-2">{{ $key+1 }}
                                                                </div>
                                                            </div>
                                                            
                                                        </div>
                                                    </td>
                                                    <td class="border-b dark:border-darkmode-500">{{ $submission->courseModuleBase->assesment_name }} - {{ $submission->courseModuleBase->assesment_code }}</td>
                                                    
                                                    <td class="border-b dark:border-darkmode-500">{{ isset($submission->createdBy->employee) ? $submission->createdBy->employee->full_name : "" }}</td>
                                                    <td class="border-b dark:border-darkmode-500">{{ $submission->created_at }}</td>
                                                    <td class="border-b dark:border-darkmode-500">
                                                        @if($submission->is_it_final > 0)
                                                        <a href="javascript:void(0);" data-plan="{{ $plan->id }}" data-assesmentPlanId="{{ $submission->id }}" data-tw-toggle="modal" data-tw-target="#student-preview-modal"  class="edit_btn_submission_tutor btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>
                                                            @if((isset(auth()->user()->priv()['result_management_staff_delete']) && auth()->user()->priv()['result_management_staff_delete'] == 1))
                                                            
                                                            <button data-id="{{$submission->id}}" data-action="delete" data-url="tutor" class="delete_btn btn-rounded btn btn-danger text-white p-0 w-9 h-9 ml-1"><i class="w-4 h-4" data-lucide="trash-2"></i></button>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                        @else
                                        <div class="text-center w-full text-xl">No Submission Found</div>
                                        @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- BEGIN: Import Modal -->
    <div id="uploadSubmissionDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Submisson</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    
                    <form method="post"  action="{{ route('results-staff-submission.upload',$plan->id) }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents[]"  type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="assessment_plan_id" value=""/>
                    </form>
                    <div class="mt-3">
                        <label class="block mb-1">Assessment</label>
                        <select data-search="true" class="tom-select w-full" id="assessmentPlanId" name="assessmentPlanId">
                            <option value="">Select Assessment</option>
                            @foreach ($assessmentlist as $assessmentPlan)
                                <option value="{{ $assessmentPlan->id }}">{{ $assessmentPlan->assesment_name }} - {{ $assessmentPlan->assesment_code }}</option>
                            @endforeach
                        </select>
                    </div>
                            
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadEmpDocBtn" class="btn btn-primary w-auto">     
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
     <!-- BEGIN: Plan Task  Confirm Modal Content -->
     <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="info" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" data-url="no" class="agreeWith btn btn-primary w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->
    <!-- BEGIN: Plan Task  Confirm Modal Content -->
    <div id="confirmModalSingle" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="info" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" data-url="no" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->
    
    <!-- BEGIN: Plan Task  Confirm Modal Content -->
    <div id="confirmDeleteModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="info" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Do you want to Delete?</div>
                        <div class="text-slate-500 mt-2 confModDesc">Please make sure before deletion. it is parmanent.</div>
                    </div>
                    <form id="resultDeleteAllForm" method="post">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}"/>
                        <div class="append-input">
                            <input type="hidden" name="ids[]" value=""/>
                        </div>
                        <div class="append-second">
                            <input type="hidden" name="assessment_plan_ids[]" value=""/>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="submit" data-id="0" data-action="none" class="update btn btn-danger w-auto">Yes, I agree <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->

     <!-- BEGIN: Plan Task  Confirm Modal Content -->
     <div id="finalConfirmUploadTask" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="info" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 title">Are you sure?</div>
                        <div class="text-slate-500 mt-2 description">Result will save as final</div>
                    </div>
                    <form id="resultFinalForm" method="post">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}"/>
                        <input type="hidden" name="ids[]" value=""/>
                        <div class="append-input"></div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="submit" class="update btn btn-primary w-auto">Yes, I agree
                                <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->
    
    <div data-tw-backdrop="static" aria-hidden="true" tabindex="-1" id="student-preview-modal" class="modal group bg-black/60 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s]">
        
        <div data-tw-merge class="w-[90%] mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-16 group-[.show]:mt-16 group-[.modal-static]:scale-[1.05] dark:bg-darkmode-600    sm:w-[900px] lg:w-[900px] p-10 text-center">
            <a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                <i data-tw-merge data-lucide="x" class="stroke-1.5 h-8 w-8 text-slate-400 "></i>
            </a>
            <div id="form-data" class="text-center">
                <h2 class="text-xl font-medium">Student Submission</h2>
                <div class="mt-5">
                    <div class="grid grid-cols-12 gap-4">        
                        <div class="col-span-12">
                            <div class="overflow-x-auto scrollbar-hidden mt-3">
                                <div id="submissionListTable" class="mt-5 table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    </div>
                </div>
    
            </div>
        </div>
    </div>


    <!-- BEGIN: Student Profile Lock Modal -->
   @if($resultSet)
   @foreach($resultSet as $key => $data)
    @php  $resultDataSet = $data['all']; @endphp
        @if($resultDataSet->count()>0)
        <div id="callLockModal{{ $resultDataSet[0]->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="font-medium text-base mr-auto">Attempt List</h2>
                            <a data-tw-dismiss="modal" href="javascript:;">
                                <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                            </a>
                        </div>
                        <div class="modal-body  overflow-x-auto"> 
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead data-tw-merge class="">
                                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Term
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Code
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Created At
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Published At
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Grade
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Status
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Last Updated By
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultDataSet as $result)
                                    @php
                                        if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                                                $termData = $result->term->name;
                                            else
                                                $termData = $result->plan->attenTerm->name;
                                        @endphp
                                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                                {{ $termData }} 
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                                {{ ($result->module_code)? $result->module_code : $result->plan->creations->code  }}
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ date('d F,Y h:i a',strtotime($result->created_at))  }}
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ date('d F,Y h:i a',strtotime($result->published_at))  }}
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $result->grade->code }} 
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $result->grade->name }}
                                            </td>
                                            <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ isset($result->updatedBy->employee->full_name)  ? $result->updatedBy->employee->full_name : (isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name)  }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        </div>
       @endif
   @endforeach
@endif
<!-- END: Student Profile Lock Modal -->
    
    
    
    
    