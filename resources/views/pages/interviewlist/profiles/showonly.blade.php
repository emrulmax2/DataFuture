@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</strong></u></h2>
        <div class="ml-auto flex justify-end">
            <a id="assignedPageLoad" href="{{ route('interviewlist') }}" type="button" class="btn btn-primary text-white w-auto  mt-2 sm:mt-0 sm:ml-1  mr-2" ><i data-lucide="arrow-left"  class="w-4 h-4 mr-2"></i> Back</a>
        </div>
    </div>
    <!-- BEGIN: Profile Info -->
    {{-- {{ route('applicantprofile.export') }} --}}
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
                        <div class="flex-none w-auto font-medium text-base">Status : <span id="ProgressStatus" class="text-warning">Pending</span></div>
                        <div id="loading" class="flex-initial w-12 ml-2" style="display: none;"><i data-loading-icon="bars" class="w-6 h-6 ml-2 "></i></div>
                    </div>
                    <div class="col-span-6 text-right">
                        <form method="post" id="interviewStartFromProfile" >
                            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}" />
                            <input type="hidden" name="applicant_task_id" value="{{ $applicantTask->id }}" />
                            @csrf
                            <button type="submit" id="startInterviewSession" class="btn btn-primary w-auto">
                                Start Interview
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
                        </form>
                    </div>
                </div>
                <div class="mt-3 mb-4 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                <div class="progressBarWrap">
                    <div class="singleProgressBar mb-3">
                        <div class="flex justify-between mb-2">
                            <div class="font-medium">Start Time :</div>
                            <div id="progressStart" class="font-medium">--:--</div>
                        </div>
                    </div>
                    <div class="singleProgressBar">
                        <div class="flex justify-between mb-2">
                            <div class="font-medium">End Time :</div>
                            <div id="progressEnd" class="font-medium">--:--</div>
                        </div>
                    </div>
                    
                    <div class="singleProgressBar">
                        <div class="flex justify-between mb-2">
                            <div class="font-medium">Result :</div>
                            <div id="progressInterviewStatus" class="font-medium">N/A</div>
                        </div>
                    </div>
                    
                    <div class="singleProgressBar">
                        <div class="flex flex-wrap items-center justify-center font-medium lg:flex-nowrap">
                            <div class="w-full mb-4 mr-auto lg:mb-0 lg:w-1/2">Document :</div>
                            
                                <div class="inline-flex items-center justify-center cursor-pointer font-medium"><i data-lucide="slash" class="w-5 h-5"></i></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="intro-y mt-5 flex justify-start">
        <a href="javascript::void(0)" class="btn shadow-lg border-0 bg-white mr-3 inline-block p-5 text-left w-54 md:w-64 active-bg-success active-text-white hover-bg-success hover-text-white">
            <span class="block text-lg text-dark font-semibold">Information</span>
            <span class="block text-base font-normal text-slate-500">Details</span>
        </a>
    </div>
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>
                <div class="col-span-6 text-right">
                    {{-- <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Personal Details
                    </button> --}}
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-8 font-medium">{{ $applicant->date_of_birth }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Gender</div>
                        <div class="col-span-8 font-medium">{{ $applicant->sexid->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-8 font-medium">{{ $applicant->nation->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Country of Birth</div>
                        <div class="col-span-8 font-medium">{{ $applicant->country->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-8 font-medium">{{ $applicant->other->ethnicity->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Disability Status</div>
                        <div class="col-span-8 font-medium">
                            {!! (isset($applicant->other->disability_status) && $applicant->other->disability_status == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                        </div>
                    </div>
                </div>
                @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                            <div class="col-span-8 font-medium">
                                {!! (isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-500 font-medium">Disabilities</div>
                            <div class="col-span-12 font-medium">
                                @if(isset($applicant->disability) && !empty($applicant->disability))
                                    <ul class="m-0 p-0">
                                        @foreach($applicant->disability as $dis)
                                            <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->disabilities->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Contact Details</div>
                </div>
                <div class="col-span-6 text-right">
                    {{-- <button data-applicant="{{ $applicant->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Contact Details
                    </button> --}}
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">
                            {{ $applicant->users->email }}
                            @if ($applicant->users->email_verified_at == NULL)
                                <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                            @else
                                @if(isset($tempEmail->applicant_id) && $tempEmail->applicant_id > 0 && (isset($tempEmail->status) && $tempEmail->status == 'Pending'))
                                    <span class="btn inline-flex btn-warning px-2 ml-2 py-0 text-white rounded-0">Awaiting Verification</span><br/>
                                    <span>({{ $tempEmail->email }})</span>
                                @else
                                    <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Home Phone</div>
                                <div class="col-span-8 font-medium">{{ $applicant->contact->home }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                                <div class="col-span-8 font-medium">
                                    {{ $applicant->contact->mobile }}
                                    @if($applicant->contact->mobile_verification == 1)
                                        <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                                    @else
                                        <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1))
                                <span class="font-medium">{{ $applicant->contact->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2))
                                <span class="font-medium">{{ $applicant->contact->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($applicant->contact->city) && !empty($applicant->contact->city))
                                <span class="font-medium">{{ $applicant->contact->city }}</span>,
                            @endif
                            @if(isset($applicant->contact->state) && !empty($applicant->contact->state))
                                <span class="font-medium">{{ $applicant->contact->state }}</span>, <br/>
                            @endif
                            @if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code))
                                <span class="font-medium">{{ $applicant->contact->post_code }}</span>,
                            @endif
                            @if(isset($applicant->contact->country) && !empty($applicant->contact->country))
                                <span class="font-medium">{{ $applicant->contact->country }}</span><br/>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Next of Kin</div>
                </div>
                <div class="col-span-6 text-right">
                    {{-- <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Next of Kin
                    </button> --}}
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-6">
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Name</div>
                            <div class="col-span-8 font-medium">{{ $applicant->kin->name }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 ">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                            <div class="col-span-8 font-medium">{{ $applicant->kin->relation->name }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 ">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                            <div class="col-span-8 font-medium">{{ $applicant->kin->mobile }}</div>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Email</div>
                            <div class="col-span-8 font-medium">{{ (isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '---') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1))
                                <span class="font-medium">{{ $applicant->kin->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2))
                                <span class="font-medium">{{ $applicant->kin->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($applicant->kin->city) && !empty($applicant->kin->city))
                                <span class="font-medium">{{ $applicant->kin->city }}</span>,
                            @endif
                            @if(isset($applicant->kin->state) && !empty($applicant->kin->state))
                                <span class="font-medium">{{ $applicant->kin->state }}</span>, <br/>
                            @endif
                            @if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code))
                                <span class="font-medium">{{ $applicant->kin->post_code }}</span>,
                            @endif
                            @if(isset($applicant->kin->country) && !empty($applicant->kin->country))
                                <span class="font-medium">{{ $applicant->kin->country }}</span><br/>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Proposed Course & Programme</div>
                </div>
                <div class="col-span-6 text-right">
                    {{-- <button data-tw-toggle="modal" data-tw-target="#editAdmissionCourseDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Course & Programme
                    </button> --}}
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                        <div class="col-span-8 font-medium">{{ $applicant->course->creation->course->name.' - '.$applicant->course->semester->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">How are you funding your education at London Churchill College?</div>
                        <div class="col-span-8 font-medium">{{ $applicant->course->student_loan }}</div>
                    </div>
                </div>
                @if($applicant->course->student_loan == 'Student Loan')
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>
                            <div class="col-span-8 font-medium">{!! (isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn rounded-0 btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                        </div>
                    </div>
                    @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Are you already in receipt of funds?</div>
                                <div class="col-span-8 font-medium">{!! (isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                            </div>
                        </div>
                    @endif
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                            <div class="col-span-8 font-medium">{!! (isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn rounded-0 btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                        </div>
                    </div>
                @elseif($applicant->course->student_loan == 'Others')
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Other Funding</div>
                            <div class="col-span-8 font-medium">{{ (isset($applicant->course->other_funding) && $applicant->course->other_funding != '' ? $applicant->course->other_funding : '') }}</div>
                        </div>
                    </div>
                @endif
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Are you applying for evening and weekend classes (Full Time)</div>
                        <div class="col-span-8 font-medium">{!! (isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5" id="applicantQualification">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Education Qualification</div>
                </div>
                <div class="col-span-6 text-right">
                    {{-- <div class="form-check form-switch justify-end">
                        <label class="form-check-label m-0 mr-2" for="is_edication_qualification">Do you have any formal academic qualification?</label>
                        <input data-applicant="{{ $applicant->id }}" {{ (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'checked' : '') }} id="is_edication_qualification" value="1" name="is_edication_qualification" class="form-check-input" type="checkbox">
                    </div> --}}
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                {{--<div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Do you have any formal academic qualification? </div>
                        <div class="col-span-8 font-medium">{!! (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                    </div>
                </div>--}}
                <div class="col-span-12 educationQualificationTableWrap" style="display: {{ isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'block' : 'none' }};">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div id="tabulatorFilterForm-EQ" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-EQ" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-EQ" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-EQ" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-EQ" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </div>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-EQ" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto mr-2">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                            {{-- <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Qualification
                            </button> --}}
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="educationQualTable" data-applicant="{{ $applicant->id }}" class="mt-5 table-report table-report--tabulator {{ isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
                <div class="col-span-12 educationQualificationTableNoWrap" style="display: {{ !isset($applicant->other->is_edication_qualification) || $applicant->other->is_edication_qualification != 1 ? 'block' : 'none' }};">
                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Education Qualification status are disabled.
                    </div>
                </div>
            </div>
        </div>

        @php 
            if(!isset($applicant->other->employment_status) || ($applicant->other->employment_status == 'Unemployed' || $applicant->other->employment_status == 'Contractor' || $applicant->other->employment_status == 'Consultant' || $applicant->other->employment_status == 'Office Holder')):
                $emptStatus = false;
            else:
                $emptStatus = true;
            endif;
        @endphp
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Empoyment History</div>
                </div>
                <div class="col-span-6">
                    <div class="flex justify-end items-center">
                        <label class="form-check-label m-0 mr-2" for="employment_status">What is your current employment status?</label>
                        <select id="employment_status" data-applicant="{{ $applicant->id }}" class="lcc-tom-select w-56 text-left" name="employment_status" disabled>
                            <option value="">Please Select</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Part Time' ? 'Selected' : '' }} value="Part Time">Part Time</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Fixed Term' ? 'Selected' : '' }} value="Fixed Term">Fixed Term</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Contractor' ? 'Selected' : '' }} value="Contractor">Contractor</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Zero Hour' ? 'Selected' : '' }} value="Zero Hour">Zero Hour</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Seasonal' ? 'Selected' : '' }} value="Seasonal">Seasonal</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Agency or Temp' ? 'Selected' : '' }} value="Agency or Temp">Agency or Temp</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Consultant' ? 'Selected' : '' }} value="Consultant">Consultant</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Office Holder' ? 'Selected' : '' }} value="Office Holder">Office Holder</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Volunteer' ? 'Selected' : '' }} value="Volunteer">Volunteer</option>
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Unemployed' ? 'Selected' : '' }} value="Unemployed">Unemployed</option> 
                            <option {{ isset($applicant->other->employment_status) && $applicant->other->employment_status == 'Full Time' ? 'Selected' : '' }} value="Full Time">Full Time</option> 
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4">

                <div class="col-span-12 educationEmploymentTableWrap" style="display: {{ $emptStatus ? 'block' : 'none' }};">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <div id="tabulatorFilterForm-EH" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-EH" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-EH" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-EH" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-EH" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </div>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-EH" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                            {{-- <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="btn btn-primary w-auto ml-2 mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Employement History
                            </button> --}}
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="employmentHistoryTable" data-applicant="{{ $applicant->id }}" class="mt-5 table-report table-report--tabulator {{ $emptStatus ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
                <div class="col-span-12 educationEmploymentTableWrap" style="display: {{ !$emptStatus ? 'block' : 'none' }};">
                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Based on selected employment status there are no employment history found!
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="font-medium text-base">Others</div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">If you referred by Somone/ Agent, Please enter the Referral Code.</div>
                        <div class="col-span-8 font-medium">{!! ($applicant->referral_code != '' ? $applicant->referral_code : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Profile Info -->
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

<!-- BEGIN: Error Modal Content -->
<div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 errorModalTitle"></div>
                    <div class="text-slate-500 mt-2 errorModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-action="DISMISS" class="errorCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    @vite('resources/js/staff-interview-start.js')
@endsection