{{--
    Applicant hero card — shared by the Information / Communication /
    Uploaded Files / Notes / Processes screens.

    It bundles what the old design split across three places: the
    "Application Ref No." title bar from each page, the profile + work
    progress boxes, and the tab strip (included at the bottom from
    show-menu). Every JS hook is preserved: .changeApplicantStatus,
    .rejectApplicationBtn, #addApplicantPhotoModal, .progressBarWrap.
--}}
@php
    $admFullName = trim(($applicant->title->name ?? '').' '.$applicant->first_name.' '.$applicant->last_name);

    // photo_url returns a generated initials SVG (a data: URI) when the
    // applicant has no uploaded photo. Show a real photo when there is one;
    // otherwise draw the initials in the course-themed circle so the avatar
    // follows the palette instead of the fixed brand colour.
    $admPhotoUrl = (string) $applicant->photo_url;
    $admHasPhoto = $admPhotoUrl !== '' && !str_starts_with($admPhotoUrl, 'data:');
    $admInitials = strtoupper(mb_substr((string) $applicant->first_name, 0, 1).mb_substr((string) $applicant->last_name, 0, 1));
    $admInitials = $admInitials !== '' ? $admInitials : '--';

    // Mirrors admissionStatusTone() in resources/js/admission.js so the
    // list pill and the hero pill agree.
    $admStatusTone = match((int) ($applicant->status_id ?? 0)) {
        5, 7 => 'success',
        8, 9, 46 => 'danger',
        4, 6 => 'progress',
        1, 2 => 'muted',
        default => 'pending',
    };

    $admPending = $applicant->pendingTasks->count();
    $admInProgress = $applicant->inProgressTasks->count();
    $admCompleted = $applicant->completedTasks->count();
    $admTotalTask = $admPending + $admInProgress + $admCompleted;
    $admPendingProgress = ($admTotalTask > 0 ? round(($admPending + $admInProgress) / $admTotalTask, 2) * 100 : '0');
    $admCompletedProgress = ($admTotalTask > 0 ? round($admCompleted / $admTotalTask, 2) * 100 : '0');
@endphp

<div class="adm-hero">
    <div class="adm-hero__strip"></div>

    <div class="adm-hero__body">
        <div class="adm-hero__top">
            <div class="adm-hero__refgroup">
                <span class="adm-hero__reflabel">Application Ref</span>
                <span class="adm-hero__ref">{{ (isset($applicant->application_no) && !empty($applicant->application_no) ? $applicant->application_no : '---') }}</span>
                <span class="adm-hero__pill adm-hero__pill--{{ $admStatusTone }}">
                    <span class="adm-pill__dot"></span>{{ $applicant->status->name ?? '--' }}
                </span>
                @if($applicant->status_id == 8 && isset($applicant->application_rejected_reason_id) && $applicant->application_rejected_reason_id > 0 && isset($applicant->reason->name) && !empty($applicant->reason->name))
                    <span class="adm-hero__reason">Rejection Reason: <b>{{ $applicant->reason->name }}</b></span>
                @endif
            </div>

            <div class="adm-hero__actions">
                {{-- Hidden by default; admission-global.js shows it while the
                     offer-accepted job batch runs. --}}
                <button data-tw-toggle="modal" data-tw-target="#progressBarModal" class="add_btn adm-btn adm-btn--danger hidden" type="button">Progress Bar</button>

                <a href="{{ route('applicantprofile.print', $applicant->id) }}" data-id="{{ $applicant->id }}" class="adm-btn adm-btn--tint">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6M9 15l3 3 3-3M12 12v6"></path></svg>
                    Download Pdf
                </a>

                @if(isset(auth()->user()->priv()['login_as_applicant']) && auth()->user()->priv()['login_as_applicant'] == 1)
                    <a target="__blank" href="{{ route('impersonate', ['id' => $applicant->applicant_user_id, 'guardName' => 'applicant']) }}" class="adm-btn adm-btn--gold">
                        Login As Applicant
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"></path></svg>
                    </a>
                @endif
            </div>
        </div>

        <div class="adm-hero__identity">
            <div class="adm-hero__avatar-wrap">
                <div class="adm-hero__avatar">
                    @if($admHasPhoto)
                        <img alt="{{ $admFullName }}" src="{{ $admPhotoUrl }}">
                    @else
                        {{ $admInitials }}
                    @endif
                </div>
                <button data-tw-toggle="modal" data-tw-target="#addApplicantPhotoModal" type="button" class="adm-hero__camera" aria-label="Change photo">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                </button>
            </div>

            <div class="adm-hero__summary">
                <h2 class="adm-hero__name">{{ $admFullName }}</h2>
                <div class="adm-hero__course">{{ ($applicant->course->creation->course->name ?? '').(isset($applicant->course->semester->name) ? ' · '.$applicant->course->semester->name : '') }}</div>
                <div class="adm-hero__contacts">
                    <span class="adm-hero__contact">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M2 6l10 7 10-7"></path></svg>
                        <b>{{ $applicant->users->email ?? '—' }}</b>
                    </span>
                    <span class="adm-hero__contact">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.6A2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.3a2 2 0 012.1-.5c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z"></path></svg>
                        @if(!empty($applicant->contact->home))
                            Phone: <b>{{ $applicant->contact->home }}</b>
                        @else
                            <span class="adm-hero__empty">Phone: —</span>
                        @endif
                    </span>
                    <span class="adm-hero__contact">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="2" width="12" height="20" rx="2"></rect><path d="M11 18h2"></path></svg>
                        @if(!empty($applicant->contact->mobile))
                            Mobile: <b>{{ $applicant->contact->mobile }}</b>
                        @else
                            <span class="adm-hero__empty">Mobile: —</span>
                        @endif
                    </span>
                </div>
            </div>

            <div class="adm-work">
                <div class="adm-work__head">
                    <span class="adm-work__title">Work Progress</span>

                    @if($applicant->status_id == 4 || $applicant->status_id == 5 || $applicant->status_id == 6)
                        <div class="dropdown adm-statusmenu" data-tw-placement="bottom-end">
                            <button class="dropdown-toggle adm-statusmenu__trigger" aria-expanded="false" data-tw-toggle="dropdown">
                                <span class="adm-statusmenu__dot"></span>{{ $applicant->status->name }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="dropdown-menu adm-statusmenu__menu">
                                <ul class="adm-statusmenu__panel">
                                    <li class="adm-statusmenu__head">Status List</li>
                                    <li class="adm-statusmenu__rule"></li>
                                    @if(!empty($allStatuses))
                                        @foreach($allStatuses as $sts)
                                            @if(($applicant->status_id == 4 && in_array($sts->id, [5, 8])) || ($applicant->status_id == 5 && in_array($sts->id, [6])) || ($applicant->status_id == 6 && in_array($sts->id, [7, 9])))
                                                <li>
                                                    <a href="javascript:void(0);" data-statusid="{{ $sts->id }}" data-applicantid="{{ $applicant->id }}" class="adm-statusmenu__item changeApplicantStatus">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $sts->id == 8 ? '#d64545' : 'currentColor' }}" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M9 12l2 2 4-4"></path></svg>
                                                        <span>{{ $sts->name }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @elseif(($applicant->status_id == 3 || $applicant->status_id == 8) && isset(auth()->user()->priv()['applicant_rejected']) && auth()->user()->priv()['applicant_rejected'] == 1)
                        <div class="dropdown adm-statusmenu" data-tw-placement="bottom-end">
                            <button class="dropdown-toggle adm-statusmenu__trigger {{ $applicant->status_id == 8 ? 'adm-statusmenu__trigger--danger' : '' }}" aria-expanded="false" data-tw-toggle="dropdown">
                                <span class="adm-statusmenu__dot"></span>{{ $applicant->status->name }}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="dropdown-menu adm-statusmenu__menu">
                                <ul class="adm-statusmenu__panel">
                                    <li class="adm-statusmenu__head">Status List</li>
                                    <li class="adm-statusmenu__rule"></li>
                                    @if(!empty($allStatuses))
                                        @foreach($allStatuses as $sts)
                                            @if(($applicant->status_id == 3 && in_array($sts->id, [8])) || ($applicant->status_id == 8 && in_array($sts->id, [3])))
                                                <li>
                                                    <a href="javascript:void(0);" data-statusid="{{ $sts->id }}" data-applicantid="{{ $applicant->id }}" class="adm-statusmenu__item rejectApplicationBtn">
                                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $sts->id == 8 ? '#d64545' : 'currentColor' }}" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M9 12l2 2 4-4"></path></svg>
                                                        <span>{{ $sts->name }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @else
                        <span class="adm-statusmenu__trigger {{ $applicant->status_id == 8 ? 'adm-statusmenu__trigger--danger' : '' }}" style="cursor:default;">
                            <span class="adm-statusmenu__dot"></span>{{ $applicant->status->name ?? '--' }}
                        </span>
                    @endif
                </div>

                <div class="progressBarWrap adm-work__bars">
                    <div class="singleProgressBar adm-work__bar adm-work__bar--pending">
                        <div class="adm-work__bar-head">
                            <span class="adm-work__bar-label">Pending</span>
                            <span class="adm-work__bar-count">{{ $admPending + $admInProgress }}/{{ $admTotalTask }}</span>
                        </div>
                        <div class="progress adm-work__track">
                            <div class="progress-bar adm-work__fill" style="width: {{ $admPendingProgress }}%;" role="progressbar" aria-valuenow="{{ $admPendingProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="singleProgressBar adm-work__bar adm-work__bar--done">
                        <div class="adm-work__bar-head">
                            <span class="adm-work__bar-label">Completed</span>
                            <span class="adm-work__bar-count">{{ $admCompleted }}/{{ $admTotalTask }}</span>
                        </div>
                        <div class="progress adm-work__track">
                            <div class="progress-bar adm-work__fill" style="width: {{ $admCompletedProgress }}%;" role="progressbar" aria-valuenow="{{ $admCompletedProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.students.admission.show-menu')
</div>

    <!-- BEGIN: Import Modal -->
    <div id="addApplicantPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Profile Photo</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('admission.upload.photo') }}" class="dropzone" id="addApplicantPhotoForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop file here or click to upload.</div>
                            <div class="text-slate-500">
                                Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadPhotoBtn" class="btn btn-primary w-auto">     
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

    <!-- BEGIN: Status Confirm Modal -->
    <div id="statusConfirmModal" class="modal"  data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>

                        <div class="mt-3 rejectedReasonArea border-t border-slate-200/60 border-b pt-4 pb-6" style="display: none;">
                            <label for="rejected_reason" class="form-label">Rejected Reason <span class="text-danger">*</span></label>
                            <select id="rejected_reason" name="rejected_reason" class="form-control w-3/4">
                                <option value="">Please select a reason</option>
                                @if(isset($reasons) && $reasons->count() > 0)
                                    @foreach($reasons as $resn)
                                        <option value="{{ $resn->id }}">{{ $resn->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mt-3 offerAcceptedErrorArea border-t border-slate-200/60 border-b pt-4 pb-6" style="display: none;">
                            <div class="pb-2 proof_type" style="display: none;">
                                <label for="sts_proof_type" class="form-label block mb-1">Proof of Id Type <span class="text-danger">*</span></label>
                                <select id="sts_proof_type" class="form-control  w-3/4" name="proof_type">
                                    <option value="">Please Select</option>
                                    <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'passport' ? 'Selected' : '' }} value="passport">Passport</option>
                                    <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'birth' ? 'Selected' : '' }} value="birth">Birth Certificate</option>
                                    <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'driving' ? 'Selected' : '' }} value="driving">Driving Licence</option>
                                    <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'nid' ? 'Selected' : '' }} value="nid">National ID Card</option>
                                    <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'respermit' ? 'Selected' : '' }} value="respermit">Residence Permit No</option>
                                </select>
                            </div>
                            <div class="pb-2 proof_id" style="display: none;">
                                <label for="sts_proof_id" class="form-label block mb-1">ID No <span class="text-danger">*</span></label>
                                <input type="text" value="{{ isset($applicant->proof->proof_id) ? $applicant->proof->proof_id : '' }}" placeholder="ID No" id="sts_proof_id" class="form-control  w-3/4" name="proof_id">
                            </div>
                            <div class="pb-2 proof_expiredate" style="display: none;">
                                <label for="sts_proof_expiredate" class="form-label block mb-1">Expiry Date <span class="text-danger">*</span></label>
                                <input type="text" value="{{ isset($applicant->proof->proof_expiredate) ? $applicant->proof->proof_expiredate : '' }}" placeholder="DD-MM-YYYY" id="sts_proof_expiredate" class="form-control  w-3/4 datepicker" data-format="DD-MM-YYYY" data-single-mode="true" name="proof_expiredate">
                            </div>
                            <div class="pb-2 fee_eligibility_id" style="display: none;">
                                <label for="sts_fee_eligibility_id" class="form-label block mb-1">Fee Eligibility <span class="text-danger">*</span></label>
                                <select id="sts_fee_eligibility_id" class="form-control  w-3/4" name="fee_eligibility_id">
                                    <option value="">Please Select</option>
                                    @if($feeelegibility->count() > 0)
                                        @foreach($feeelegibility as $fl)
                                            <option {{ isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id == $fl->id ? 'Selected' : ($fl->id == 3 ? 'Selected' : '') }} value="{{ $fl->id }}">{{ $fl->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <!--This Id required for Vue-->
                        <button id="statusAgreement" type="button" data-statusid="0" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Status Confirm Modal -->

    <!-- BEGIN: Rejected Confirm Modal Content -->
    <div id="rejectedConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 rejectedConfModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 rejectedConfModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-statusid="0" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Rejected Confirm Modal Content -->


    <!-- BEGIN: Progress bar Modal -->
    <div id="progressBarModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <!-- Start Vue Component -->
        <div id="app" class="modal-dialog">
            <form method="POST" action="#" id="" enctype="multipart/form-data">
                <div  class="modal-content">
                    <div class="modal-header border-0" >
                        <h2 v-if="failedJobs.length" class="font-medium text-xl mr-auto text-danger">Conversion stopped</h2>
                        <h2 v-else-if="progressPercentage<100" class="font-medium text-xl mr-auto">@{{ progress }}  ....</h2>
                        <h2 v-else-if="progressPercentage==100"class="font-medium text-xl mr-auto">@{{ progress }} Done</h2>

                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
                        </a>
                    </div>
                    <input id="batchId" type="hidden" value="" />
                    <input id="progress" type="hidden" :value="progressPercentage" />
                    {{-- Non-zero while conversion steps failed; admission-global.js skips
                         its auto-close/reload so the errors below stay readable. --}}
                    <input id="conversionFailed" type="hidden" :value="failedJobs.length" />
                    <div class="modal-body">
                        <div>
                            <div class="progress h-3 mt-1">
                                <div id="progress-bar" :style="{width: `${progressPercentage}%`}" class="progress-bar transition-all ease-out duration-1000" :class="failedJobs.length ? 'bg-danger' : 'bg-success'" role="progressbar" :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100"> @{{progressPercentage}}%</div>
                            </div>
                        </div>

                        {{-- Failed steps from student_conversion_logs (via the
                             admission.progress.data payload). Inline SVG rather than
                             data-lucide: this block mounts after createIcons ran. --}}
                        <div v-if="failedJobs.length" class="alert alert-danger-soft show flex items-start mt-4" role="alert">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6 mr-2 flex-none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><path d="M12 9v4M12 17h.01"></path></svg>
                            <div>
                                <div class="font-medium">The student conversion stopped because a step failed. The remaining steps were skipped.</div>
                                <ul class="list-disc ml-4 mt-2">
                                    <li v-for="job in failedJobs">@{{ job.job_name }}: @{{ job.message }}</li>
                                </ul>
                                <a v-if="conversionLogUrl" :href="conversionLogUrl" class="btn btn-danger w-auto mt-3">View Conversion Log</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--End Vue Component -->
    </div>
    <!-- END: Progress bar Modal -->

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


<!-- BEGIN: Offer Acceptance Modal -->
<div id="sendOfferAcceptanceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="sendOfferAcceptanceForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Send E-Signature Request</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Send Email</label>
                        <div class="flex items-center">
                            <div class="form-check form-switch">
                                <input id="esign_contact_email" class="form-check-input" name="contact_email" value="1" type="checkbox">
                                <label class="form-check-label ml-5" for="esign_contact_email">{{ $applicant->users->email ?? '' }}</label>
                            </div>
                        </div>
                    </div>
                    @if($applicant->contact->mobile)
                    <div class="mb-3">
                        <label class="form-label">Send SMS</label>
                        <div class="flex items-center">
                            <div class="form-check form-switch">
                                <input id="esign_contact_phone" class="form-check-input" name="contact_phone" value="1" type="checkbox">
                                <label class="form-check-label ml-5" for="esign_contact_phone">
                                    {{ $applicant->contact->mobile }}
                                    @if($applicant->contact->mobile_verification == 1)
                                        <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                                    @else
                                        <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="sendOfferBtn" class="btn btn-primary w-auto">
                        Send
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
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Offer Acceptance Modal -->

<!-- BEGIN: Location Permission Modal Content -->
<div id="LocationPermissionModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="map-pin" class="w-16 h-16 text-warning mx-auto mt-3"></i>
                    <div class="text-2xl mt-5 warningModalTitle">Location Permission Required</div>
                    <div class="text-slate-500 mt-2 warningModalDesc">
                        We need your location to proceed. Please allow access.
                    </div>
                </div>
                <div class="px-5 pb-8 text-center flex justify-center gap-4">
                    <button type="button" id="denyLocationBtn" class="btn btn-outline-secondary w-24" data-tw-dismiss="modal">Deny</button>
                    <button type="button" id="allowLocationBtn" class="btn btn-primary w-24">Allow</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Location Permission Modal Content -->
