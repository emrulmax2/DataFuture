@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Module Details</h2>
</div>
<!-- BEGIN: Profile Info -->
<div class="intro-y box px-5 pt-5 mt-5">
    <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
            <div class="ml-auto mr-auto">
                <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $data->module }}</div>
                <div class="text-slate-500 font-medium">{{ $data->course }} - {{ $data->term_name }}</div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l  border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left lg:mt-3">Module Details</div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Group:</span> <span class="font-medium ml-2">{{ $data->group }}</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Student : </span> <span class="font-medium ml-2">{{ $studentCount }}</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ (isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : '') }}</span>
                </div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
            @if($plan->tutor_id > 0)
                <div class="flex items-center lg:mt-3">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->tutor->employee->photo_url) ? $plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Tutor</div>
                    </div>
                </div>
            @endif
            @if($plan->personal_tutor_id > 0)
                <div class="flex items-center mt-4">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->personalTutor->employee->photo_url) ? $plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Personal Tutor</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
        <li id="submission-tab" class="nav-item mr-5 " role="presentation">
            <a href="{{ route('results-staff-submission.show',$plan->id) }} " class="nav-link py-4 inline-flex px-0" data-tw-target="#submission" aria-controls="submission" aria-selected="true" role="tab" >
                <i data-lucide="files" class="w-4 h-4 mr-2"></i> Result Submission
            </a>
        </li>
        <li id="comparison-tab" class="nav-item mr-5 " role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#submission" aria-controls="submission" aria-selected="true" role="tab" >
                <i data-lucide="files" class="w-4 h-4 mr-2"></i> Result Comparison
            </a>
        </li>
    </ul>
</div>
<div class="intro-y tab-content mt-5">
    <form id="resultComparisonForm"  method="POST" action="#">
        @csrf
    <div class="intro-y box">
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
            <h2 class="font-medium text-base mr-auto my-3">Result Confirmation <span class="total-select ml-2"></span></h2>
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#PublishDateConfirmUploadTask" id="updatePublishDate" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="updatePublishDate transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-info text-info dark:border-info [&:hover:not(:disabled)]:bg-info/10 mb-2 mr-1 w-48">Publish Date</button>
            @if(count($resultIds) > 0)
                <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="updateSubmission" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="updateSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-warning text-warning dark:border-warning [&:hover:not(:disabled)]:bg-warning/10 mb-2 mr-1 w-48">Update Result</button>
            @endif
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="savedSubmission" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="savedSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&:hover:not(:disabled)]:bg-success/10 mb-2 mr-1  w-48">Save as New</button>
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
                                @if($studentAssign->count() > 0)
                                
                                <table class="table border-none">
                                    <thead>
                                        <tr class="bg-slate-100">
                                            <th class="whitespace-nowrap border "><div data-tw-merge class="flex items-center">
                                                
                                                <input id="checkbox-switch-all" data-tw-merge type="checkbox" class="checkbox-switch-all transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  value="" />
                                                <label data-tw-merge for="checkbox-switch-all" class="cursor-pointer ml-2">S.N.</label>
                                            </div></th>
                                            <th class="whitespace-nowrap border">Reg. No</th>
                                            <th class="whitespace-nowrap border">Name</th>
                                            <th class="whitespace-nowrap border">Status</th>
                                            <th class="whitespace-nowrap border">Assessment</th>
                                            <th class="whitespace-nowrap border">Grade By Staff</th>
                                            <th class="whitespace-nowrap border">Grade By P.T</th>
                                            <th class="whitespace-nowrap border">Final Grade</th>
                                            <th class="whitespace-nowrap border">Publish At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @php $serial=1; @endphp
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                                            @foreach ($resultSet as $key => $data)
                                                @if($data['staff_given_grade']!="N/A")
                                                    @if($data['grade_matched'] == "Matched")
                                                        @php $studentClass="bg-success-100 text-success-600"; @endphp
                                                    @else
                                                        @php $studentClass="bg-red-100 text-red-600"; @endphp
                                                    @endif
                                                    @if($data['attendance'] ===0)
                                                        @php $studentClass="bg-orange-100 text-orange-600"; @endphp
                                                    @endif
                                                    @php
                                                        $warningCheck = "transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-warning focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-warning [&[type='radio']]:checked:border-warning [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-warning [&[type='checkbox']]:checked:border-warning [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                                                        $primaryCheck ="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50";
                                                        $checkboxCssClass = (isset($data['id'])) ? $warningCheck : $primaryCheck ; 
                                                    @endphp
                                                    <tr id="row{{ $serial }}" class="{{ $studentClass }}">
                                                        <td class="">
                                                            <div data-tw-merge class="flex items-center">
                                                                <input type="hidden" name="paper_id[{{ $serial }}]" value="{{ $data['paper_id'] }}" />
                                                                <input type="hidden" name="student_id[{{ $serial }}]" value="{{ $data['student_id'] }}" />
                                                                <input type="hidden" name="assessment_plan_id[{{ $serial }}]" value="{{ $data['assessment_plan_id'] }}" />
                                                                <input type="hidden" name="result_id[{{ $serial }}]" value="{{ isset($data['id']) ? $data['id'] : '' }}" />
                                                                <input data-tw-merge type="checkbox" {{ ($data['attendance']===null || $data['attendance']===1) ? '' : 'disabled' }} name="id[{{ $serial }}]" 
                                                                class="fill-box {{ $checkboxCssClass }}" id="checkbox-switch-{{ $serial }}" value="{{ isset($data['id']) ? $data['id'] : $serial }}" />
                                                                <label data-tw-merge for="checkbox-switch-{{ $serial }}" class="cursor-pointer ml-2">{{ isset($data['id']) ? $data['id'] : $serial }}</label>
                                                            </div>
                                                        </td>
                                                        <td class="">{{ $data['registration_no'] }}</td>
                                                        <td class="">{{ $data['full_name']}}</td>
                                                        <td class="">{{ $data['status'] }}</td>
                                                        <td class="">{{ $data['assement'] }}</td>
                                                        <td class="">{{ $data['staff_given_grade'] }}</td>
                                                        <td class="">{{ $data['tutor_given_grade'] }}</td>
                                                        <td class="">
                                                            @if($data['attendance'] !==0)
                                                            <select id="grade_id" class="lccTom lcc-tom-select w-full" name="grade_id[{{ $serial }}]">
                                                                <option value="" selected>Please Select</option>
                                                                @if(!empty($grades))
                                                                    @foreach($grades as $grade)
                                                                        <option {{ ($data['grade'] == $grade->id) ? "selected" : "" }} value="{{ $grade->id }}">{{ $grade->code }} - {{ $grade->name }}</option>
                                                                    @endforeach 
                                                                @endif 
                                                            </select>
                                                            <div class="acc__input-error error-grade_id-{{ $serial }} text-danger mt-2"></div>
                                                            @endif
                                                        </td>
                                                        <td class="">
                                                            @if($data['attendance'] !==0)
                                                            <div class="flex">
                                                                <input type="text" value="{{ $data['publish_at'] }}" placeholder="DD-MM-YYYY" id="publish_at" class="form-control datepicker flex-inline w-28" name="publish_at[{{ $serial }}]" data-format="DD-MM-YYYY" data-single-mode="true">
                                                                <input type="text" value="{{ $data['publish_time'] }}" placeholder="HH:MM" id="publish_time" class="theTimeField form-control flex-inline w-24" name="publish_time[{{ $serial }}]">
                                                            </div>
                                                            <div  class="acc__input-error error-publish_at-{{ $serial }} text-danger mt-2"></div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $serial++; @endphp
                                                @endif
                                            @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-slate-100">
                                            <th class="whitespace-nowrap border "><div data-tw-merge class="flex items-center">
                                                <input id="checkbox-switch-all1" data-tw-merge type="checkbox" class="checkbox-switch-all transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  value="" />
                                                <label data-tw-merge for="checkbox-switch-all1" class="cursor-pointer ml-2">S.N.</label>
                                            </div></th>
                                            <th class="whitespace-nowrap border ">Reg. No</th>
                                            <th class="whitespace-nowrap border ">Name</th>
                                            <th class="whitespace-nowrap border ">Status</th>
                                            <th class="whitespace-nowrap border ">Assessment</th>
                                            <th class="whitespace-nowrap border ">Grade By Staff</th>
                                            <th class="whitespace-nowrap border ">Grade By P.T</th>
                                            <th class="whitespace-nowrap border ">Final Grade</th>
                                            <th class="whitespace-nowrap border ">Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                @else
                                <div class="text-center w-full text-xl">No Submission Found</div>
                                @endif
                        </div>
                    </div>
            </div>
        </div>
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400 ">
            <h2 class="font-medium text-base mr-auto my-5"><span class="total-select"></span></h2>
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#PublishDateConfirmUploadTask" id="updatePublishDate" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="updatePublishDate transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-info text-info dark:border-info [&:hover:not(:disabled)]:bg-info/10 mr-1 w-48">Publish Date</button>
            @if(count($resultIds) > 0)
                <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="updateSubmission1" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="updateSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-warning text-warning dark:border-warning [&:hover:not(:disabled)]:bg-warning/10 mr-1 w-48">Update Result</button>
            @endif
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="savedSubmission1" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="savedSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&:hover:not(:disabled)]:bg-success/10  mr-1 w-48">Save as New</button>
        </div>
    </div>
    </form>
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
                        <button type="button" data-id="0" data-action="none" class="agreeWithPlanTask btn btn-primary w-auto">Yes, I agree</button>
                    </div>
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
                        <div class="text-slate-500 mt-2 description">Result will save as New</div>
                    </div>
                        <div class="append-input"></div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="button" data-action="SAVE" class="updateResult btn btn-primary w-auto">Yes, I agree
                                <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i>
                            </button>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->

    <!-- BEGIN: PublishDateConfirmUploadTask Task  Confirm Modal Content -->
    <div id="PublishDateConfirmUploadTask" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <form id="publishDateForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-5 text-center">
                            <i data-lucide="info" class="w-16 h-16 text-success mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 title">Set A Publish Date</div>
                            <div class="text-slate-500 mt-2 description">
                                <div class="mt-1 w-48 mx-auto">
                                    
                                    <select data-search="true" class="tom-select w-full" id="published_at" name="published_at" >
                                        <option value="">Please Select A Publish Type</option>
                                        @if(isset($term_publish_date) && !empty($term_publish_date))
                                            
                                        <option value="{{ $term_publish_date->exam_publish_date }} {{ $term_publish_date->exam_publish_time }}">{{ $term_publish_date->exam_publish_date }} {{ $term_publish_date->exam_publish_time }}</option>
                                        <option value="{{ $term_publish_date->exam_resubmission_publish_date }} {{ $term_publish_date->exam_resubmission_publish_time }}">{{ $term_publish_date->exam_resubmission_publish_date }} {{ $term_publish_date->exam_resubmission_publish_time }}</option>
                                            
                                        @endif
                                    </select>

                                    <input type="hidden" name="id" value="{{ $AssessmentPlan->id }}" />
                                </div>
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="submit" class="updateResult btn btn-primary w-auto">Yes, I agree
                                <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden text-white" ></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: PublishDateConfirmUploadTask Task Confirm Modal Content -->

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

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 warningModalTitle">Oops!</div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
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

    @vite('resources/js/result-comparison.js')
@endsection