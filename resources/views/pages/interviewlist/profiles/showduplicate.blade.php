@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</strong></u></h2>
        <div class="ml-auto flex justify-end">
            <a id="assignedPageLoad" href="{{ route('applicant.interview.session.list',\Auth::id()) }}" type="button" class="btn btn-primary text-white w-auto  mt-2 sm:mt-0 sm:ml-1  mr-2" ><i data-lucide="arrow-left"  class="w-4 h-4 mr-2"></i> Back</a>
        </div>
    </div>
    <!-- BEGIN: Profile Info -->
    {{-- {{ route('applicantprofile.export') }} --}}
    @include('pages.interviewlist.profiles.show-duplicateinfo')
    @include('pages.interviewlist.profiles.show-duplicatemenu')
    
    
    <!-- END: Profile Info -->
    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>
                <div class="col-span-6 text-right">
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
                        <div class="col-span-8 font-medium">{{ $applicant->gender }}</div>
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
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
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
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Home Phone</div>
                        <div class="col-span-8 font-medium">{{ $applicant->contact->home }}</div>
                    </div>
                </div> 
                <div class="col-span-12 sm:col-span-4">
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
    </div>
<!-- BEGIN: Edit Modal -->
<div id="editModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Result</h2>
                </div>
                <div class="modal-body">
                    <div>       
                        <label for="interview_status" class="form-label">Result <span class="text-danger">*</span></label>
                            <input type="hidden" id="id" value="" name="id" />
                            <select id="interview_status" name="interview_status" class="form-control w-full interview_status__input">
                                <option value="">Please Select</option>
                                
                                    <option value="Pass">Pass</option>
                                    <option value="Fail">Fail</option>
                                    <option value="Unattainded">Unattainded</option>
                                  
                            </select>
                            <div id="error-interview_status" class="interview_status__input-error error-user text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="update" class="btn btn-primary w-auto">
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
<!-- END: Edit Modal -->
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
            </div>
        </div>
    </div>
</div>
@endsection
<!-- END: Success Modal Content -->
@section('script')
    @vite('resources/js/staff-interview-task.js')
@endsection