<div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-5">
    <div class="col-span-8">
        <div class="intro-y box px-5 pt-5">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                        <img alt="{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}" class="rounded-full" src="{{ (isset($applicant->photo) && !empty($applicant->photo) ? asset('storage/applicants/'.$applicant->id.'/'.$applicant->photo) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
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
                    <div class="font-medium text-base">Work in Progress</div>
                </div>
                <div class="col-span-6 text-right">
                    <button type="button" class="btn btn-primary w-auto mr-1 mb-0">
                        {{ $applicant->status->name }}
                    </button>
                </div>
            </div>
            <div class="mt-3 mb-4 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            @php 
                $pending = $applicant->pendingTasks->count();
                $completed = $applicant->completedTasks->count();

                $totalTask = $pending + $completed;
                $pendingProgress = ( $totalTask > 0 ? round($pending / $totalTask, 2) * 100 : '0');
                $completedProgress = ( $totalTask > 0 ? round($completed / $totalTask, 2) * 100 : '0');
            @endphp
            <div class="progressBarWrap">
                <div class="singleProgressBar mb-3">
                    <div class="flex justify-between mb-1">
                        <div class="font-medium">Pending Task</div>
                        <div class="font-medium">{{ $applicant->completedTasks->count() }}/{{ $applicant->pendingTasks->count() + $applicant->completedTasks->count() }}</div>
                    </div>
                    <div class="progress h-1">
                        <div class="progress-bar bg-warning"  style="width: {{ $pendingProgress }}%;"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="singleProgressBar">
                    <div class="flex justify-between mb-1">
                        <div class="font-medium">Completed Task</div>
                        <div class="font-medium">{{ $applicant->completedTasks->count() }}/{{ $applicant->pendingTasks->count() + $applicant->completedTasks->count() }}</div>
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
<div id="addApplicantPhotoModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Upload Profile Photo</h2>
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