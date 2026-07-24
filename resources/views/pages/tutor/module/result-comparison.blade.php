@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('body_class', 'staff-result-submission-body')

@section('subcontent')
@php
    $srInitialsFor = function ($name) {
        $clean = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
        $first = mb_substr($parts[0] ?? 'L', 0, 1);
        $last = mb_substr($parts[count($parts) - 1] ?? 'C', 0, 1);
        return mb_strtoupper($first . $last);
    };
    $srGradeClass = function ($grade) {
        $code = strtoupper(trim(strtok((string) $grade, '-')));
        return match ($code) {
            'P' => 'is-pass',
            'M' => 'is-merit',
            'D' => 'is-distinction',
            'R' => 'is-referred',
            'A' => 'is-absent',
            default => 'is-na',
        };
    };
    $srCurrentSubmission = $AssessmentPlan ?? null;
    $srCurrentAssessmentCode = $srCurrentSubmission->courseModuleBase->assesment_code ?? $srCurrentSubmission->assesment_code ?? '';
    $srCurrentAssessmentName = $srCurrentSubmission->courseModuleBase->assesment_name ?? $srCurrentSubmission->assesment_name ?? '';
    $srCurrentPublishedAt = ($srCurrentSubmission && !empty($srCurrentSubmission->published_at))
        ? \Carbon\Carbon::parse($srCurrentSubmission->published_at)->format('jS M y H:i')
        : null;
    $srCurrentSubmissionLabel = trim($srCurrentAssessmentCode . ' · ' . $srCurrentAssessmentName, ' ·');
    $srCurrentSubmissionLabel = $srCurrentSubmissionLabel ?: 'Select submission';
    $srCurrentSubmissionLabel .= $srCurrentPublishedAt ? ' — ' . $srCurrentPublishedAt : '';
@endphp
<div id="staffResultSubmission" class="staff-result-submission">
    @include('pages.tutor.module.includes.staff-result-header', ['activeResultMenu' => 'comparison', 'currentAssessmentPlanId' => $AssessmentPlan->id ?? null])

<div class="intro-y tab-content mt-5 sr-tab-content">
    <form id="resultComparisonForm"  method="POST" action="#">
        @csrf
    <div class="intro-y box sr-panel sr-comparison-panel">
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400 sr-panel__head sr-comparison-head">
            <div class="sr-comparison-title-wrap">
                <h2 class="font-medium text-base sr-panel__title">Result Comparison <span class="total-select ml-2"></span></h2>
                <div class="sr-review-meta">
                    <span>Reviewing</span>
                    <span class="sr-review-pill">{{ $srCurrentSubmissionLabel }}</span>
                </div>
            </div>
            <div class="sr-comparison-head__actions">
                <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#PublishDateConfirmUploadTask" id="updatePublishDateTop" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} class="updatePublishDate transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-info text-info dark:border-info [&:hover:not(:disabled)]:bg-info/10 sr-btn is-info"><i data-lucide="calendar-days" class="w-4 h-4"></i><span>Publish Date</span></button>
            </div>
        </div>
        <div class="p-5 pt-0 sr-comparison-body">
            <div class="grid grid-cols-12 gap-4 sr-comparison-body-grid">
                    <div class="col-span-12">
                        <div class="mt-3 sr-comparison-table-wrap">
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
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}" />
                                <div class="sr-table-shell">
                                <table class="table border-none sr-table sr-comparison-table">
                                    <thead>
                                        <tr class="bg-slate-100">
                                            <th class="whitespace-nowrap border sr-check-cell">
                                                <div data-tw-merge class="flex items-center">
                                                    <input id="checkbox-switch-all" data-tw-merge type="checkbox" class="checkbox-switch-all transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50 sr-check" value="" />
                                                    <label data-tw-merge for="checkbox-switch-all" class="sr-sr-only">Select all</label>
                                                </div>
                                            </th>
                                            <th class="whitespace-nowrap border sr-sn-cell">S.N.</th>
                                            <th class="whitespace-nowrap border sr-reg-cell">Reg. No</th>
                                            <th class="whitespace-nowrap border sr-name-cell">Name</th>
                                            <th class="whitespace-nowrap border">Status</th>
                                            <th class="whitespace-nowrap border">Assessment</th>
                                            <th class="whitespace-nowrap border">Grade By P.T</th>
                                            <th class="whitespace-nowrap border">Grade By Staff</th>
                                            <th class="whitespace-nowrap border">Final Grade</th>
                                            <th class="whitespace-nowrap border">Publish At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @php $serial=1; @endphp
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
                                                        $srRowState = $data['grade_matched'] == 'Matched' ? 'is-matched' : 'is-mismatch';
                                                        $srRowState .= $data['attendance'] === 0 ? ' is-absent' : '';
                                                    @endphp
                                                    <tr id="row{{ $serial }}" class="{{ $studentClass }} {{ $srRowState }}">
                                                        <td class="sr-check-cell">
                                                            <div data-tw-merge class="flex items-center">
                                                                <input type="hidden" name="paper_id[{{ $serial }}]" value="{{ $data['paper_id'] }}" />
                                                                <input type="hidden" name="student_id[{{ $serial }}]" value="{{ $data['student_id'] }}" />
                                                                <input type="hidden" name="assessment_plan_id[{{ $serial }}]" value="{{ $data['assessment_plan_id'] }}" />
                                                                <input type="hidden" name="result_id[{{ $serial }}]" value="{{ isset($data['id']) ? $data['id'] : '' }}" />
                                                                <input type="hidden" name="result_submission_staff_id[{{ $serial }}]" value="{{ $data['result_submission_staff_id'] }}" />
                                                                
                                                                <input data-tw-merge type="checkbox" data-result_submission_staff_id="{{ $data['result_submission_staff_id'] }}" {{ ($data['attendance']===null || $data['attendance']===1) ? '' : 'disabled' }} name="id[{{ $serial }}]" 
                                                                class="fill-box {{ $checkboxCssClass }} sr-check" id="checkbox-switch-{{ $serial }}" value="{{ isset($data['id']) ? $data['id'] : $serial }}" />
                                                                <label data-tw-merge for="checkbox-switch-{{ $serial }}" class="sr-sr-only">Select row {{ $serial }}</label>
                                                            </div>
                                                        </td>
                                                        <td class="sr-sn-cell">
                                                            <label data-tw-merge for="checkbox-switch-{{ $serial }}" class="cursor-pointer">{{ $serial }}</label>
                                                        </td>
                                                        <td class="sr-reg-cell">{{ $data['registration_no'] }}</td>
                                                        <td class="sr-name-cell">{{ $data['full_name'] }}</td>
                                                        <td class="sr-status-cell">{{ $data['status'] }}</td>
                                                        <td class="sr-assessment-cell">{{ $data['assement'] }}</td>
                                                        <td class=""><span class="sr-grade sr-compare-grade {{ $srGradeClass($data['tutor_given_grade']) }}">{{ $data['tutor_given_grade'] }}</span></td>
                                                        <td class=""><span class="sr-grade sr-compare-grade {{ $srGradeClass($data['staff_given_grade']) }}">{{ $data['staff_given_grade'] }}</span></td>
                                                        <td class="sr-final-grade-cell">
                                                            @if($data['attendance'] !==0)
                                                            <select id="grade_id" class="lccTom lcc-tom-select w-full sr-select sr-final-select" name="grade_id[{{ $serial }}]">
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
                                                        <td class="sr-publish-at-cell">
                                                            @if($data['attendance'] !==0)
                                                            <div class="flex sr-publish-fields">
                                                                <input type="text" value="{{ $data['publish_at'] }}" placeholder="DD-MM-YYYY" id="publish_at" class="form-control datepicker flex-inline sr-date-field" name="publish_at[{{ $serial }}]" data-format="DD-MM-YYYY" data-single-mode="true">
                                                                <input type="text" value="{{ $data['publish_time'] }}" placeholder="HH:MM" id="publish_time" class="theTimeField form-control flex-inline sr-time-field" name="publish_time[{{ $serial }}]">
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
                                            <th class="whitespace-nowrap border sr-check-cell">
                                                <div data-tw-merge class="flex items-center">
                                                    <input id="checkbox-switch-all1" data-tw-merge type="checkbox" class="checkbox-switch-all transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50 sr-check" value="" />
                                                    <label data-tw-merge for="checkbox-switch-all1" class="sr-sr-only">Select all</label>
                                                </div>
                                            </th>
                                            <th class="whitespace-nowrap border sr-sn-cell">S.N.</th>
                                            <th class="whitespace-nowrap border sr-reg-cell">Reg. No</th>
                                            <th class="whitespace-nowrap border sr-name-cell">Name</th>
                                            <th class="whitespace-nowrap border ">Status</th>
                                            <th class="whitespace-nowrap border ">Assessment</th>
                                            <th class="whitespace-nowrap border ">Grade By P.T</th>
                                            <th class="whitespace-nowrap border ">Grade By Staff</th>
                                            <th class="whitespace-nowrap border ">Final Grade</th>
                                            <th class="whitespace-nowrap border ">Publish At</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                            @else
                                <div class="text-center w-full text-xl">No Submission Found</div>
                            @endif
                        </div>
                    </div>
            </div>
        </div>
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400 sr-panel__head sr-panel__foot-actions sr-comparison-footer-actions">
            <h2 class="font-medium text-base mr-auto my-5"><span class="total-select"></span></h2>
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#PublishDateConfirmUploadTask" id="updatePublishDateFooter" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} class="updatePublishDate transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-info text-info dark:border-info [&:hover:not(:disabled)]:bg-info/10 mr-1 w-48 sr-btn is-info"><i data-lucide="calendar-days" class="w-4 h-4"></i><span>Publish Date</span></button>
            @if(count($resultIds) > 0)
                <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="updateSubmission1" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="updateSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-warning text-warning dark:border-warning [&:hover:not(:disabled)]:bg-warning/10 mr-1 w-48 sr-btn is-gold"><i data-lucide="refresh-cw" class="w-4 h-4"></i><span>Update Result</span></button>
            @endif
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="savedSubmission1" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="savedSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&:hover:not(:disabled)]:bg-success/10  mr-1 w-48 sr-btn is-green"><i data-lucide="check" class="w-4 h-4"></i><span>Save as New</span></button>
            <button type="button" data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmDeleteTask" id="deleteSubmission1" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} data-planid={{ $plan->id }} class="deleteSubmission hidden transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-danger focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-danger text-danger dark:border-danger [&:hover:not(:disabled)]:bg-danger/10  mr-1 w-48 sr-btn is-danger"><i data-lucide="trash-2" class="w-4 h-4"></i><span>Delete Selected</span></button>
        </div>
    </div>
    </form>
</div>

    <!-- BEGIN: Import Modal -->
    <div id="uploadSubmissionDocumentModal" class="modal sr-modal sr-upload-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header sr-modal__head">
                    <span class="sr-modal__icon"><i data-lucide="upload" class="w-5 h-5"></i></span>
                    <h2 class="font-medium text-base mr-auto">Upload Submission</h2>
                    <a data-tw-dismiss="modal" href="javascript:;" class="sr-modal__close">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </a>
                </div>
                <div class="modal-body">

                    <form method="post"  action="{{ route('results-staff-submission.upload',$plan->id) }}" class="dropzone sr-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input name="documents[]"  type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <span class="sr-dropzone__icon"><i data-lucide="upload" class="w-6 h-6"></i></span>
                            <div class="text-lg font-medium">Drop the submission excel file here.</div>
                            <div class="text-slate-500 sr-dropzone__hint">
                                Download the <a href="{{ route('results-staff-submission.sample.download',$plan->id) }}" class="sr-dropzone__link">Sample Excel</a> first, then fill in student grades and upload it back.
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
                        <i data-lucide="upload" class="w-4 h-4 mr-1"></i>
                        Upload
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 upload-spinner">
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
    <div id="confirmModal" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center sr-confirm__body">
                        <div class="sr-confirm__badge is-success"><i data-lucide="help-circle" class="w-8 h-8"></i></div>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center sr-confirm__actions">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithPlanTask btn btn-primary w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->
    
    <!-- BEGIN: Plan Task  Confirm Modal Content -->
    <div id="finalConfirmUploadTask" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center sr-confirm__body">
                        <div class="sr-confirm__badge is-success"><i data-lucide="check-circle" class="w-8 h-8"></i></div>
                        <div class="text-3xl mt-5 title">Are you sure?</div>
                        <div class="text-slate-500 mt-2 description">Result will save as New</div>
                    </div>
                        <div class="append-input"></div>
                        <div class="px-5 pb-8 text-center sr-confirm__actions">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="submit" data-action="SAVE" class="updateResult btn btn-primary w-auto">Yes, I agree
                                <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i>
                            </button>
                        </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Plan Task Confirm Modal Content -->

        <!-- BEGIN: Plan Task  Confirm Modal Content -->
        <div id="finalConfirmDeleteTask" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center sr-confirm__body">
                            <div class="sr-confirm__badge is-danger"><i data-lucide="trash-2" class="w-8 h-8"></i></div>
                            <div class="text-3xl mt-5 title">Are you sure?</div>
                            <div class="text-slate-500 mt-2 description">The selected result records will be permanently removed.</div>
                        </div>
                        <form id="deleteStaffSubmissionForm" method="post" >
                            @csrf
                            <input type="hidden" name="id[]" value="" />
                            <div class="append-input"></div>
                            <div class="px-5 pb-8 text-center sr-confirm__actions">
                                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                                <button type="submit" data-action="SAVE" class="updateResult btn btn-danger w-auto">Yes, delete
                                    <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden " ></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Plan Task Confirm Modal Content -->

    <!-- BEGIN: PublishDateConfirmUploadTask Task  Confirm Modal Content -->
    <div id="PublishDateConfirmUploadTask" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <form id="publishDateForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-5 text-center sr-confirm__body">
                            <div class="sr-confirm__badge is-info"><i data-lucide="calendar-clock" class="w-8 h-8"></i></div>
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
                        <div class="px-5 pb-8 text-center sr-confirm__actions">
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
<div id="successModal" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center sr-confirm__body">
                    <div class="sr-confirm__badge is-success"><i data-lucide="check-circle" class="w-8 h-8"></i></div>
                    <div class="text-3xl mt-5 successModalTitle"></div>
                    <div class="text-slate-500 mt-2 successModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center sr-confirm__actions">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center sr-confirm__body">
                    <div class="sr-confirm__badge is-danger"><i data-lucide="alert-triangle" class="w-8 h-8"></i></div>
                    <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                    <div class="text-slate-500 mt-2 confModDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center sr-confirm__actions">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal sr-modal sr-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center sr-confirm__body">
                    <div class="sr-confirm__badge is-danger"><i data-lucide="alert-triangle" class="w-8 h-8"></i></div>
                    <div class="text-3xl mt-5 warningModalTitle">Oops!</div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center sr-confirm__actions">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">OK, Got it</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->
</div>
@endsection

@section('script')

    @vite('resources/js/result-comparison.js')
@endsection
