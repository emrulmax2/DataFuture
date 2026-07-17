<div id="submission-result" class="tab-pane w-full" role="tabpanel" aria-labelledby="submission-tab">
    <div class="tm-panel">
        <div class="tm-section-head">
            <h2 class="tm-section-title">Submission Document</h2>
            <div class="tm-actions">
                <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#uploadSubmissionDocumentModal" data-planid="{{ $plan->id }}" data-moduleCretionId="{{ $plan->module_creation_id }}" class="callModalPlanTask btn btn-primary">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload Submission
                </button>
                <a href="{{ route('result-submission.sample.download',$plan->id) }}" class="btn btn-outline-secondary">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Sample Excel
                </a>
                <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#finalConfirmUploadTask" id="savedSubmission" data-planid="{{ $plan->id }}" data-moduleCretionId="{{ $plan->module_creation_id }}" class="hidden btn btn-success">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Confirm Submission
                </button>
            </div>
        </div>

        <div class="p-5">
            <div id="displayError" class="my-3 hidden">
                <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-danger border-danger text-white dark:border-danger mb-2 flex items-center">
                    <i data-tw-merge data-lucide="alert-octagon" class="stroke-1.5 w-5 h-5 mr-2"></i>
                    <span class="errorMessage"></span>
                    <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 text-white">
                        <i data-tw-merge data-lucide="x" class="stroke-1.5 w-4 h-4"></i>
                    </button>
                </div>
                <div role="alert" class="alert relative border rounded-md px-5 my-3 py-4 bg-danger border-danger text-white dark:border-danger mb-2">
                    <div class="flex items-center">
                        <div class="text-md font-medium">
                            <span class="errorList">Error List</span>
                        </div>
                        <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" class="text-slate-800 py-3 px-3 absolute right-0 my-auto mr-2 text-white">
                            <i data-tw-merge data-lucide="x" class="stroke-1.5 w-5"></i>
                        </button>
                    </div>
                    <div class="mt-3 error-students"></div>
                </div>
            </div>

            @if($resultSubmission->count() > 0)
                <div class="tm-table-wrap tm-static-table">
                    <table class="table table-report">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">
                                    <div data-tw-merge class="flex items-center">
                                        <input data-tw-merge type="checkbox" class="form-check-input" id="checkbox-switch-all" value="" />
                                        <label data-tw-merge for="checkbox-switch-all" class="cursor-pointer ml-2">S.N.</label>
                                    </div>
                                </th>
                                <th class="whitespace-nowrap">Reg. No</th>
                                <th class="whitespace-nowrap">Name</th>
                                <th class="whitespace-nowrap">Assessment</th>
                                <th class="whitespace-nowrap">Paper ID</th>
                                <th class="whitespace-nowrap">Submission Date</th>
                                <th class="whitespace-nowrap">Grade</th>
                                <th class="whitespace-nowrap">Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resultSubmission as $key => $submission)
                                @php
                                    $studentClass = "";
                                    $misMatched = false;
                                    if($submission->is_student_matched==0) {
                                        $studentClass = "text-orange-600";
                                        $misMatched = true;
                                    }
                                @endphp
                                <tr class="{{ $studentClass }}">
                                    <td>
                                        <div data-tw-merge class="flex items-center">
                                            <input data-tw-merge type="checkbox" name="id[]" class="fill-box form-check-input" id="checkbox-switch-{{ $key+1 }}" value="{{ $submission->id }}" />
                                            <label data-tw-merge for="checkbox-switch-{{ $key+1 }}" class="cursor-pointer ml-2">{{ $key+1 }}</label>
                                        </div>
                                    </td>
                                    <td class="tm-mono">
                                        {{ $submission->student->registration_no }}
                                        @if($misMatched)
                                            <div class="text-red-500">No Submission Found</div>
                                        @endif
                                    </td>
                                    <td>{{ $submission->student->full_name }}</td>
                                    <td>{{ $submission->AssessmentPlan->courseModuleBase->assesment_name }} - {{ $submission->assessmentPlan->courseModuleBase->assesment_code }}</td>
                                    <td class="tm-mono">{{ $submission->paper_id }}</td>
                                    <td class="tm-mono">{{ $submission->created_at }}</td>
                                    <td>
                                        <span class="tm-grade-chip">{{ $submission->grade->code }} - {{ $submission->grade->name }}</span>
                                    </td>
                                    <td class="tm-mono">{{ $submission->published_at }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tm-empty-state">
                    <span><i data-lucide="file-text" class="w-8 h-8"></i></span>
                    <strong>No Submission Found</strong>
                    <small>Upload a submission document to record student results.</small>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="submission-log" class="tab-pane w-full" role="tabpanel" aria-labelledby="submission-tab">
    <div class="tm-panel">
        <div class="tm-section-head">
            <h2 class="tm-section-title">Module Submission List</h2>
        </div>
        <div class="p-5">
            @if($submissionAssessment->count() > 0)
                <div class="tm-table-wrap tm-static-table">
                    <table class="table table-report">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">S.N.</th>
                                <th class="whitespace-nowrap">Assessment</th>
                                <th class="whitespace-nowrap">Uploaded By</th>
                                <th class="whitespace-nowrap">Submission Date</th>
                                <th class="whitespace-nowrap text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($submissionAssessment as $key => $submission)
                                @php
                                    $uploaderEmployee = $submission->createdBy->employee ?? null;
                                    $uploaderName = $uploaderEmployee ? trim($uploaderEmployee->full_name) : '';
                                    // photo_url returns a data: SVG initials avatar when no real photo is stored;
                                    // use the URL only when it is a real image, else fall back to initials.
                                    $uploaderPhoto = ($uploaderEmployee && isset($uploaderEmployee->photo_url) && !\Illuminate\Support\Str::startsWith($uploaderEmployee->photo_url, 'data:')) ? $uploaderEmployee->photo_url : null;
                                    // Mirror tmParticipantAvatarStyle()/tmParticipantInitials() in plan-tasks.js so
                                    // uploader avatars use the same colour and initials as the Participants table.
                                    $avatarColors = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
                                    $seed = $uploaderName !== '' ? $uploaderName : 'staff';
                                    $hash = 0;
                                    foreach (str_split($seed) as $char) {
                                        $hash = (($hash * 31) + ord($char)) & 0xFFFFFFFF;
                                    }
                                    $avatarBg = $avatarColors[$hash % count($avatarColors)];
                                    $nameParts = preg_split('/\s+/', $seed);
                                    $firstInitial = mb_substr($nameParts[0] ?? 'S', 0, 1);
                                    $secondInitial = mb_substr($nameParts[1] ?? mb_substr($nameParts[0] ?? 'S', 1, 1) ?: $firstInitial, 0, 1);
                                    $uploaderInitials = strtoupper($firstInitial . $secondInitial);
                                    $avatarStyle = $uploaderPhoto ? '' : 'background: ' . $avatarBg . ';';
                                @endphp
                                <tr>
                                    <td class="tm-mono">{{ $key+1 }}</td>
                                    <td><span class="tm-grade-chip">{{ $submission->courseModuleBase->assesment_name }} - {{ $submission->courseModuleBase->assesment_code }}</span></td>
                                    <td>
                                        <div class="tm-log-uploader">
                                            <span class="tm-log-avatar" style="{{ $avatarStyle }}">
                                                @if($uploaderPhoto)
                                                    <img src="{{ $uploaderPhoto }}" alt="{{ $uploaderName }}">
                                                @else
                                                    {{ $uploaderInitials }}
                                                @endif
                                            </span>
                                            <span class="tm-log-uploader-name">{{ $uploaderName !== '' ? $uploaderName : '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="tm-mono">{{ $submission->created_at }}</td>
                                    <td class="text-right">
                                        @if($submission->is_it_final > 0)
                                            <a href="javascript:void(0);" data-plan="{{ $plan->id }}" data-assesmentPlanId="{{ $submission->id }}" data-tw-toggle="modal" data-tw-target="#student-preview-modal" title="View submission" class="edit_btn_submission tm-log-view">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tm-empty-state">
                    <span><i data-lucide="history" class="w-8 h-8"></i></span>
                    <strong>No Submission Found</strong>
                    <small>Submission history will appear here.</small>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- BEGIN: Upload Submission Modal -->
<div id="uploadSubmissionDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <span class="tm-modal-icon"><i data-lucide="upload-cloud" class="w-5 h-5"></i></span>
                    <h2 class="font-medium text-base mr-auto">Upload Submission</h2>
                </div>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('result-submission.upload',$plan->id) }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="documents[]" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop files here or click to upload.</div>
                        <div class="text-slate-500">Max file size 5MB & max file limit 10.</div>
                    </div>
                    <input type="hidden" name="assessment_plan_id" value=""/>
                </form>
                <div class="mt-5">
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
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Upload Submission Modal -->

<!-- BEGIN: Final Confirm Modal Content -->
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
                        <button type="submit" class="update btn btn-primary w-auto">
                            Yes, I agree
                            <i data-loading-icon="oval" class="w-4 h-4 ml-2 hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END: Final Confirm Modal Content -->

<div data-tw-backdrop="static" aria-hidden="true" tabindex="-1" id="student-preview-modal" class="modal group bg-black/60 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s]">
    <div data-tw-merge class="tm-sub-modal w-[92%] mx-auto bg-white relative shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-16 group-[.show]:mt-16 group-[.modal-static]:scale-[1.05] dark:bg-darkmode-600 sm:w-[980px] lg:w-[980px]">
        <div class="tm-sub-modal-head">
            <h2 class="tm-sub-modal-title">Student Submission</h2>
            <a class="tm-sub-modal-close" data-tw-dismiss="modal" href="#" title="Close">
                <i data-lucide="x" class="w-[18px] h-[18px]"></i>
            </a>
        </div>
        <div id="form-data" class="tm-sub-modal-body">
            <div id="submissionListTable" class="tm-sub-table table-report table-report--tabulator"></div>
        </div>
    </div>
</div>

<style>
    #tutorModuleDetails .tm-log-uploader {
        align-items: center;
        display: inline-flex;
        gap: 11px;
        min-width: 0;
    }

    #tutorModuleDetails .tm-log-avatar {
        align-items: center;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 32px;
        font-size: 10.5px;
        font-weight: 700;
        height: 32px;
        justify-content: center;
        overflow: hidden;
        width: 32px;
    }

    #tutorModuleDetails .tm-log-avatar img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    #tutorModuleDetails .tm-log-uploader-name {
        color: #12312e;
        font-size: 13px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-log-view {
        align-items: center;
        background: rgba(47, 111, 176, .10);
        border: 1px solid rgba(47, 111, 176, .22);
        border-radius: 10px;
        color: #2f6fb0;
        cursor: pointer;
        display: inline-flex;
        height: 34px;
        justify-content: center;
        transition: background .12s ease-in-out;
        width: 34px;
    }

    #tutorModuleDetails .tm-log-view:hover {
        background: rgba(47, 111, 176, .20);
    }

    #tutorModuleDetails .tm-static-table {
        border: 1px solid #ecebe2;
        border-radius: 12px;
        overflow: auto;
    }

    #tutorModuleDetails .tm-grade-chip {
        align-items: center;
        background: #e8f1f9;
        border: 1px solid #c5ddf0;
        border-radius: 8px;
        box-sizing: border-box;
        color: #2f6fb0;
        display: inline-flex;
        font-size: 12px;
        font-weight: 600;
        height: 23px;
        line-height: 1;
        padding: 0 10px;
    }

    #tutorModuleDetails .tm-empty-state {
        align-items: center;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 54px 24px;
        text-align: center;
    }

    #tutorModuleDetails .tm-empty-state span {
        align-items: center;
        background: #f4f5f2;
        border-radius: 18px;
        color: #b6c0bd;
        display: flex;
        height: 64px;
        justify-content: center;
        width: 64px;
    }

    #tutorModuleDetails .tm-empty-state strong {
        color: #3a4a47;
        font-size: 16px;
        font-weight: 600;
    }

    #tutorModuleDetails .tm-empty-state small {
        color: var(--tm-faint);
        font-size: 13px;
    }

    /* The tw modal plugin relocates #student-preview-modal to <body> when shown, so these
       rules are scoped to the body class (always present) rather than to #tutorModuleDetails. */
    body.tutor-module-body #student-preview-modal .tm-sub-modal {
        border-radius: 20px;
        box-shadow: 0 30px 80px -20px rgba(11, 35, 32, .5);
        font-family: "IBM Plex Sans", system-ui, sans-serif;
        overflow: hidden;
    }

    body.tutor-module-body #student-preview-modal .tm-sub-modal-head {
        border-bottom: 1px solid #f0ede3;
        padding: 22px 28px;
        position: relative;
        text-align: center;
    }

    body.tutor-module-body #student-preview-modal .tm-sub-modal-title {
        color: #0f2d2a;
        font-family: "IBM Plex Serif", Georgia, serif;
        font-size: 20px;
        font-weight: 600;
        margin: 0;
    }

    body.tutor-module-body #student-preview-modal .tm-sub-modal-close {
        align-items: center;
        background: #f4f5f2;
        border: 1px solid #e6e8e3;
        border-radius: 10px;
        color: #8a9794;
        display: inline-flex;
        height: 36px;
        justify-content: center;
        position: absolute;
        right: 20px;
        top: 18px;
        transition: background .12s ease-in-out, color .12s ease-in-out;
        width: 36px;
    }

    body.tutor-module-body #student-preview-modal .tm-sub-modal-close:hover {
        background: #ecefe9;
        color: #5a6f6c;
    }

    body.tutor-module-body #student-preview-modal .tm-sub-modal-body {
        max-height: 70vh;
        overflow-y: auto;
        padding: 0;
    }

    /* ---- In-modal Tabulator (Student Submission list) ---- */
    body.tutor-module-body #submissionListTable.tabulator {
        background: #fff;
        border: 0;
        font-family: "IBM Plex Sans", system-ui, sans-serif;
    }

    body.tutor-module-body #submissionListTable .tabulator-header {
        background: #fafaf7 !important;
        border-bottom: 2px solid #eef0ea !important;
    }

    body.tutor-module-body #submissionListTable .tabulator-header .tabulator-col {
        background: #fafaf7 !important;
        border-right: 0 !important;
    }

    body.tutor-module-body #submissionListTable .tabulator-header .tabulator-col-title {
        color: #9aa8a5 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    body.tutor-module-body #submissionListTable .tabulator-row {
        border-bottom: 1px solid #f3f4f0 !important;
    }

    body.tutor-module-body #submissionListTable .tabulator-row.tabulator-row-even {
        background: #fbfbf9 !important;
    }

    body.tutor-module-body #submissionListTable .tabulator-row .tabulator-cell {
        border-right: 0 !important;
        color: #5a6f6c;
        font-size: 12.5px;
        padding: 12px 14px !important;
    }

    body.tutor-module-body #submissionListTable .tabulator-col-content,
    body.tutor-module-body #submissionListTable .tabulator-header .tabulator-col:first-child .tabulator-col-content {
        padding: 12px 14px !important;
    }

    body.tutor-module-body #submissionListTable .tm-sub-student {
        align-items: center;
        display: inline-flex;
        gap: 11px;
        min-width: 0;
    }

    body.tutor-module-body #submissionListTable .tm-sub-avatar {
        align-items: center;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: none;
        font-size: 11px;
        font-weight: 700;
        height: 38px;
        justify-content: center;
        overflow: hidden;
        width: 38px;
    }

    body.tutor-module-body #submissionListTable .tm-sub-avatar img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    body.tutor-module-body #submissionListTable .tm-sub-reg {
        color: #12312e;
        display: block;
        font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
        font-size: 12.5px;
        font-weight: 600;
    }

    body.tutor-module-body #submissionListTable .tm-sub-name {
        color: #7c8b88;
        display: block;
        font-size: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    body.tutor-module-body #submissionListTable .tm-sub-mono {
        font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
        font-size: 12px;
    }

    body.tutor-module-body #submissionListTable .tm-sub-grade {
        border-radius: 8px;
        display: inline-flex;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        white-space: nowrap;
    }
</style>
