
    <div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-5">
        <div class="col-span-8">
            <div class="intro-y box px-5 pt-5">
                <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                    <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                            <img alt="{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}" class="rounded-full" src="{{ (isset($applicant->photo) && !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/'.$applicant->id.'/'.$applicant->photo) ? Storage::disk('local')->url('public/applicants/'.$applicant->id.'/'.$applicant->photo) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                            <button data-tw-toggle="modal" data-tw-target="#addApplicantPhotoModal" type="button" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2">
                                <i class="w-4 h-4 text-white" data-lucide="camera"></i>
                            </button>
                        </div>
                        <div class="ml-10">
                            <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</div>
                            <div class="text-slate-500 mb-3">{{ $applicant->course->creation->course->name.' - '.$applicant->course->semester->name }}</div>
                            <div class="truncate sm:whitespace-normal flex items-center font-medium">
                                <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> {{ $applicant->users->email }}
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                                <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $applicant->contact->home }}
                            </div>
                            <div class="truncate sm:whitespace-normal flex items-center mt-1 font-medium">
                                <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $applicant->contact->mobile }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-4">
            <div class="intro-y box p-5 pt-3">
                <div class="grid grid-cols-12 gap-0 items-center">
                    <div class="col-span-6">
                        <div class="font-medium text-base">Work Progress</div>
                    </div>
                    <div class="col-span-6 text-right">
                        @if($applicant->status_id == 4 || $applicant->status_id == 5 || $applicant->status_id == 6)
                            <div class="dropdown inline-block" data-tw-placement="bottom-start">
                                <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown">
                                    {{ $applicant->status->name }} <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-72">
                                    <ul class="dropdown-content">
                                        <li><h6 class="dropdown-header">Status List</h6></li>
                                        <li><hr class="dropdown-divider mt-0"></li>

                                        @if(!empty($allStatuses))
                                            @foreach($allStatuses as $sts)
                                                @if(($applicant->status_id == 4 && in_array($sts->id, [5, 8])) || ($applicant->status_id == 5 && in_array($sts->id, [6])) || ($applicant->status_id == 6 && in_array($sts->id, [7, 9])))
                                                <li>
                                                    <a href="javascript:void(0);" data-statusid="{{ $sts->id }}" data-applicantid="{{ $applicant->id }}" class="dropdown-item changeApplicantStatus">
                                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2 text-primary"></i> {{ $sts->name }}
                                                    </a>
                                                </li>
                                                @endif
                                            @endforeach
                                        @endif

                                        <!--<li><hr class="dropdown-divider"></li>
                                        <li>
                                            <div class="flex p-1">
                                                <button type="submit" id="updateStudentStatus" class="btn btn-primary py-1 px-2 w-auto">     
                                                    <i data-lucide="rotate-cw" class="w-3 h-3 mr-2"></i> Change Status                     
                                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                        stroke="white" class="w-4 h-4 ml-2 theLoaderTwo">
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
                                        </li>-->
                                    </ul>
                                </div>
                            </div>
                        @else
                            <button type="button" class="btn btn-primary w-auto mr-1 mb-0">
                                {{ $applicant->status->name }}
                            </button>
                        @endif
                    </div>
                </div>
                <div class="mt-3 mb-4 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                @php 
                    $pending = $applicant->pendingTasks->count();
                    $inprogress = $applicant->inProgressTasks->count();
                    $completed = $applicant->completedTasks->count();

                    $totalTask = $pending + $inprogress + $completed;
                    $pendingProgress = ( $totalTask > 0 ? round(($pending + $inprogress) / $totalTask, 2) * 100 : '0');
                    $completedProgress = ( $totalTask > 0 ? round($completed / $totalTask, 2) * 100 : '0');
                @endphp
                <div class="progressBarWrap">
                    <div class="singleProgressBar mb-3">
                        <div class="flex justify-between mb-1">
                            <div class="font-medium">Pending Task</div>
                            <div class="font-medium">{{ $pending + $inprogress }}/{{ $totalTask }}</div>
                        </div>
                        <div class="progress h-1">
                            <div class="progress-bar bg-warning"  style="width: {{ $pendingProgress }}%;"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="singleProgressBar">
                        <div class="flex justify-between mb-1">
                            <div class="font-medium">Completed Task</div>
                            <div class="font-medium">{{ $applicant->completedTasks->count() }}/{{ $totalTask }}</div>
                        </div>
                        <div class="progress h-1">
                            <div class="progress-bar" style="width: {{ $completedProgress }}%;" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                <option value="Failed Interview">Failed Interview</option>
                                <option value="Shortage of Document">Shortage of Document</option>
                                <option value="Wrong Information">Wrong Information</option>
                                <option value="Lack of Qualification">Lack of Qualification</option>
                                <option value="Unavailable for Communication">Unavailable for Communication</option>
                                <option value="Failure in English Test">Failure in English Test</option>
                                <option value="Previous Bad Records">Previous Bad Records</option> 
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
                                            <option {{ isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id == $fl->id ? 'Selected' : '' }} value="{{ $fl->id }}">{{ $fl->name }}</option>
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


    <!-- BEGIN: Progress bar Modal -->
    <div id="progressBarModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <!-- Start Vue Component -->
        <div id="app" class="modal-dialog">
            <form method="POST" action="#" id="" enctype="multipart/form-data">
                <div  class="modal-content">
                    <div class="modal-header border-0" >
                        <h2 class="font-medium text-xl mr-auto">@{{ progress }}  ....</h2>
                        
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-6 h-6 text-slate-400"></i>
                        </a>
                    </div>
                    <input id="batchId" type="hidden" value="" />
                    <div class="modal-body">
                        <div>
                            <div class="progress h-3 mt-1">
                                <div id="progress-bar" :style="{width: `${progressPercentage}%`}" class="progress-bar  bg-success transition-all ease-out duration-1000 " role="progressbar" :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100"> @{{progressPercentage}}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--End Vue Component -->
    </div>
    <!-- END: Progress bar Modal -->
