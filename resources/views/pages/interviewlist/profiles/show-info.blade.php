<div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-5">
    <div class="col-span-12 intro-y md:col-span-8">
        <div class="intro-y box px-5 pt-5 h-60">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                        <img alt="London Churchill College" class="rounded-full" src="{{ (isset($student->photo) && !empty($student->photo) && Storage::disk('s3')->exists('public/applicants/'.$student->applicant_id.'/'.$student->photo) ? Storage::disk('s3')->url('public/applicants/'.$student->applicant_id.'/'.$student->photo) : asset('build/assets/images/avater.png')) }}">
                        <div class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2">
                            <i class="w-4 h-4 text-white" data-lucide="camera"></i>
                        </div>
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
    <div class="col-span-12 mt-2 md:mt-0 intro-y md:col-span-4">
        <div class="intro-y box p-5 pt-3 h-60">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6 flex">
                    <div class="flex-none w-auto font-medium text-base">Status : <span id="ProgressStatus" class="text-warning">{{ $interview->task->status }}</span></div>
                    <div id="loading" class="flex-initial w-12 ml-2" style="display: none;"><i data-loading-icon="bars" class="w-6 h-6 ml-2 "></i></div>
                </div>
                <div class="col-span-6 text-right">
                    
                        <button id="magic-button1" type="button" data-id="{{ $interview->id }}" class="into-x interview-end hover-bg-success hover-text-white btn w-40 {{ ($interview->end_time==null) ? '' : "hidden" }}" ><i data-lucide="alarm-clock-off" class="w-4 h-4 mr-2"></i> End interview</button>
                    
                        <button id="magic-button2" type="button" data-id="{{ $interview->id }}" data-tw-toggle="modal" data-tw-target="#editModal" class="into-x  w-40 interview-result btn-success hover-text-white btn {{ ($interview->applicant_document_id==null && $interview->end_time!=null) ? '' : "hidden" }}" ><i data-lucide="activity" class="w-4 h-4 mr-2"></i> Update Result</button>
                    
                        <button id="magic-button3" type="button" data-id="{{ $interview->id }}"  class="into-x  interview-taskend btn-danger text-white w-40 btn {{ ($interview->interview_status!='Completed' && $interview->applicant_document_id!=null && $interview->end_time!=null) ? "" : 'hidden' }}" ><i data-lucide="archive" class="w-4 h-4 mr-2"></i> Finish Task</button>
                    
                    {{-- <div class="dropdown">
                        <button aria-expanded="false" data-tw-toggle="dropdown"  data-id="{{ $interview->id }}" data-tw-merge class="interview-result dropdown-toggle transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-2 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&:hover:not(:disabled)]:bg-slate-100 [&:hover:not(:disabled)]:border-slate-100 [&:hover:not(:disabled)]:dark:border-darkmode-300/80 [&:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1"><i data-lucide="more-vertical" width="24" height="24" class="stroke-1.5 h-5 w-5"></i></button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <div class="dropdown-header">Options</div>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="step-1">
                                    <a href="javascript:void(0)" data-id="{{ $interview->id }}"class="dropdown-item interview-end hover-bg-success hover-text-white">
                                        <i data-lucide="alarm-clock-off" class="w-4 h-4 mr-2"></i> End interview
                                    </a>
                                </li>
                                <li class="step-2">
                                    <a href="javascript:void(0)" data-id="{{ $interview->id }}" data-tw-toggle="modal" data-tw-target="#editModal" class="dropdown-item interview-result hover-bg-success hover-text-white">
                                        <i data-lucide="activity" class="w-4 h-4 mr-2"></i> Update Result
                                    </a>
                                </li>
                                <li class="step-3">
                                    <a href="javascript:void(0)" data-id="{{ $interview->id }}" class="dropdown-item interview-taskend hover-bg-success hover-text-white">
                                        <i data-lucide="archive" class="w-4 h-4 mr-2"></i> Finish Task
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div> --}}
                </div>
            </div>
            <div class="mt-3 mb-4 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="progressBarWrap">
                <div class="singleProgressBar mb-3">
                    <div class="flex justify-between mb-2">
                        <div class="font-medium">Start Time :</div>
                        <div id="progressStart" class="font-medium">{{($interview->start_time ? $interview->start_time : '--:--')}}</div>
                    </div>
                </div>
                <div class="singleProgressBar">
                    <div class="flex justify-between mb-2">
                        <div class="font-medium">End Time :</div>
                        <div id="progressEnd" class="font-medium">{{($interview->end_time ? $interview->end_time : '--:--')}}</div>
                    </div>
                </div>
                
                <div class="singleProgressBar">
                    <div class="flex justify-between mb-2">
                        <div class="font-medium">Result :</div>
                        <div id="progressInterviewStatus" class="font-medium">{{ ($interview->interview_result ? $interview->interview_result : 'N/A') }}</div>
                    </div>
                </div>
                
                <div class="singleProgressBar">
                    <div class="flex flex-wrap items-center justify-center font-medium lg:flex-nowrap">
                        <div class="w-full mb-4 mr-auto lg:mb-0 lg:w-1/2">Document :</div>
                        @if(isset($interview->document->current_file_name))
                        <div id="fileLoadedView" class="inline-flex items-center justify-center cursor-pointer">
                            {{-- <table class="table ">
                                <tr>
                                    <td> --}}
                                        <a class="inline-flex items-center justify-center cursor-pointer" target="_blank" href="{{   Storage::disk('s3')->url('public/applicants/'.$applicant->id."/".$interview->document->current_file_name)   }}"><i data-lucide="paperclip" class="w-4 h-4 mr-2"></i>{{ $interview->document->current_file_name }}</a>
                                    {{-- </td>
                                    <td> --}}
                                        <a class="inline-flex items-center justify-center cursor-pointer" data-tw-toggle="modal" data-tw-target="#confirmModal"><i data-lucide="delete" class="w-5 h-5 ml-2 text-danger "></i></a>
                                    {{-- </td>
                                </tr>
                            </table> --}}
                            
                        </div>
                        @else
                            <div class="inline-flex items-center justify-center cursor-pointer font-medium"><i data-lucide="slash" class="w-5 h-5"></i></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
