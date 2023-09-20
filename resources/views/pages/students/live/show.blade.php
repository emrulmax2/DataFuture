@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
        
        <div class="ml-auto flex justify-end">
            <button type="button" class="btn btn-success text-white w-auto mr-1 mb-0">
                {{ $student->status->name }}
            </button>
            <!-- <a style="float: right;" href="{{ route('applicantprofile.print',$student->id) }}" data-id="{{ $student->id }}" class="btn btn-success text-white w-auto">Download Pdf</a> -->
            <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
        </div>
        
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Personal Details</div>
                </div>

                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionPersonalDetailsModal" type="button" class="editPersonalDetails btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Personal Details
                    </button>
                </div>
            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>
                        <div class="col-span-8 font-medium">{{ $student->date_of_birth }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Gender</div>
                        <div class="col-span-8 font-medium">{{ $student->gender }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Nationality</div>
                        <div class="col-span-8 font-medium">{{ $student->nation->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Country of Birth</div>
                        <div class="col-span-8 font-medium">{{ $student->country->name }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                        <div class="col-span-8 font-medium">{{ isset($student->other->ethnicity->name) ? $student->other->ethnicity->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Disability Status</div>
                        <div class="col-span-8 font-medium">
                            {!! (isset($student->other->disability_status) && $student->other->disability_status == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                        </div>
                    </div>
                </div>
                @if(isset($student->other->disability_status) && $student->other->disability_status == 1)
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                            <div class="col-span-8 font-medium">
                                {!! (isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-500 font-medium">Disabilities</div>
                            <div class="col-span-12 font-medium">
                                @if(isset($student->disability) && !empty($student->disability))
                                    <ul class="m-0 p-0">
                                        @foreach($student->disability as $dis)
                                            <li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>{{ $dis->disabilities->name }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Proof of Id Type</div>
                        <div class="col-span-8 font-medium">{{ isset($student->proof->proof_type) && !empty($student->proof->proof_type) ? ucfirst($student->proof->proof_type) : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">ID No</div>
                        <div class="col-span-8 font-medium">{{ isset($student->proof->proof_id) && !empty($student->proof->proof_id) ? $student->proof->proof_id : '---' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Expiry date</div>
                        <div class="col-span-8 font-medium">{{ isset($student->proof->proof_expiredate) && !empty($student->proof->proof_expiredate) ? $student->proof->proof_expiredate : '---' }}</div>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Contact Details</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-applicant="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#editAdmissionContactDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Contact Details
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($student->contact->address_line_1) && !empty($student->contact->address_line_1))
                                <span class="font-medium">{{ $student->contact->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($student->contact->address_line_2) && !empty($student->contact->address_line_2))
                                <span class="font-medium">{{ $student->contact->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($student->contact->city) && !empty($student->contact->city))
                                <span class="font-medium">{{ $student->contact->city }}</span>,
                            @endif
                            @if(isset($student->contact->state) && !empty($student->contact->state))
                                <span class="font-medium">{{ $student->contact->state }}</span>, <br/>
                            @endif
                            @if(isset($student->contact->post_code) && !empty($student->contact->post_code))
                                <span class="font-medium">{{ $student->contact->post_code }}</span>,
                            @endif
                            @if(isset($student->contact->country) && !empty($student->contact->country))
                                <span class="font-medium">{{ $student->contact->country }}</span><br/>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Permanent Address</div>
                        <div class="col-span-12 font-medium">

                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->users->email }}
                            @if ($student->users->email_verified_at == NULL)
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
                    <div class="grid grid-cols-12 gap-0 mb-4">
                        <div class="col-span-4 text-slate-500 font-medium">Home Phone</div>
                        <div class="col-span-8 font-medium">{{ $student->contact->home }}</div>
                    </div>
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->contact->mobile }}
                            @if($student->contact->mobile_verification == 1)
                                <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                            @else
                                <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">Unverified</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->users->email }}
                            @if ($student->users->email_verified_at == NULL)
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
                        <div class="col-span-8 font-medium">{{ $student->contact->home }}</div>
                    </div>
                </div> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">
                            {{ $student->contact->mobile }}
                            @if($student->contact->mobile_verification == 1)
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
                            @if(isset($student->contact->address_line_1) && !empty($student->contact->address_line_1))
                                <span class="font-medium">{{ $student->contact->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($student->contact->address_line_2) && !empty($student->contact->address_line_2))
                                <span class="font-medium">{{ $student->contact->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($student->contact->city) && !empty($student->contact->city))
                                <span class="font-medium">{{ $student->contact->city }}</span>,
                            @endif
                            @if(isset($student->contact->state) && !empty($student->contact->state))
                                <span class="font-medium">{{ $student->contact->state }}</span>, <br/>
                            @endif
                            @if(isset($student->contact->post_code) && !empty($student->contact->post_code))
                                <span class="font-medium">{{ $student->contact->post_code }}</span>,
                            @endif
                            @if(isset($student->contact->country) && !empty($student->contact->country))
                                <span class="font-medium">{{ $student->contact->country }}</span><br/>
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
                    <button data-tw-toggle="modal" data-tw-target="#editAdmissionKinDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Next of Kin
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->name) ? $student->kin->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->relation->name) ? $student->kin->relation->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                        <div class="col-span-8 font-medium">{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">{{ (isset($student->kin->email) && !empty($student->kin->email) ? $student->kin->email : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-slate-500 font-medium">Address</div>
                        <div class="col-span-12 font-medium">
                            @if(isset($student->kin->address_line_1) && !empty($student->kin->address_line_1))
                                <span class="font-medium">{{ $student->kin->address_line_1 }}</span><br/>
                            @endif
                            @if(isset($student->kin->address_line_2) && !empty($student->kin->address_line_2))
                                <span class="font-medium">{{ $student->kin->address_line_2 }}</span><br/>
                            @endif
                            @if(isset($student->kin->city) && !empty($student->kin->city))
                                <span class="font-medium">{{ $student->kin->city }}</span>,
                            @endif
                            @if(isset($student->kin->state) && !empty($student->kin->state))
                                <span class="font-medium">{{ $student->kin->state }}</span>, <br/>
                            @endif
                            @if(isset($student->kin->post_code) && !empty($student->kin->post_code))
                                <span class="font-medium">{{ $student->kin->post_code }}</span>,
                            @endif
                            @if(isset($student->kin->country) && !empty($student->kin->country))
                                <span class="font-medium">{{ $student->kin->country }}</span><br/>
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
                    <button data-tw-toggle="modal" data-tw-target="#editAdmissionCourseDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                        <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Course & Programme
                    </button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                        <div class="col-span-8 font-medium">{{ isset($student->course->creation->course->name) ? $student->course->creation->course->name : '' }} - {{ isset($student->course->semester->name) ? $student->course->semester->name : '' }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">How are you funding your education at London Churchill College?</div>
                        <div class="col-span-8 font-medium">{{ isset($student->course->student_loan) ? $student->course->student_loan : '' }}</div>
                    </div>
                </div>
                @if(isset($student->course->student_loan) && $student->course->student_loan == 'Student Loan')
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>
                            <div class="col-span-8 font-medium">{!! (isset($student->course->student_finance_england) && $student->course->student_finance_england == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn rounded-0 btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                        </div>
                    </div>
                    @if(isset($student->course->student_finance_england) && $student->course->student_finance_england == 1)
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Are you already in receipt of funds?</div>
                                <div class="col-span-8 font-medium">{!! (isset($student->course->fund_receipt) && $student->course->fund_receipt == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                            </div>
                        </div>
                    @endif
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                            <div class="col-span-8 font-medium">{!! (isset($student->course->applied_received_fund) && $student->course->applied_received_fund == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn rounded-0 btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                        </div>
                    </div>
                @elseif(isset($student->course->student_loan) && $student->course->student_loan == 'Others')
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Other Funding</div>
                            <div class="col-span-8 font-medium">{{ (isset($student->course->other_funding) && $student->course->other_funding != '' ? $student->course->other_funding : '') }}</div>
                        </div>
                    </div>
                @endif
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Are you applying for evening and weekend classes (Full Time)</div>
                        <div class="col-span-8 font-medium">{!! (isset($student->course->full_time) && $student->course->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Fee Eligibility</div>
                        <div class="col-span-8 font-medium">{!! (isset($student->feeeligibility->elegibility->name) && isset($student->feeeligibility->fee_eligibility_id) && $student->feeeligibility->fee_eligibility_id > 0 ? $student->feeeligibility->elegibility->name : '---') !!}</div>
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
                    <div class="form-check form-switch justify-end">
                        <label class="form-check-label m-0 mr-2" for="is_edication_qualification">Do you have any formal academic qualification?</label>
                        <input data-applicant="{{ $student->id }}" {{ (isset($student->other->is_edication_qualification) && $student->other->is_edication_qualification == 1 ? 'checked' : '') }} id="is_edication_qualification" value="1" name="is_edication_qualification" class="form-check-input" type="checkbox">
                    </div>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                {{--<div class="col-span-12 sm:col-span-12">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Do you have any formal academic qualification? </div>
                        <div class="col-span-8 font-medium">{!! (isset($student->other->is_edication_qualification) && $student->other->is_edication_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                    </div>
                </div>--}}
                <div class="col-span-12 educationQualificationTableWrap" style="display: {{ isset($student->other->is_edication_qualification) && $student->other->is_edication_qualification == 1 ? 'block' : 'none' }};">
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
                                        <li>
                                            <a id="tabulator-export-json-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-html-EQ" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Qualification
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="educationQualTable" data-applicant="{{ $student->id }}" class="mt-5 table-report table-report--tabulator {{ isset($student->other->is_edication_qualification) && $student->other->is_edication_qualification == 1 ? 'activeTable' : '' }}"></div>
                    </div>
                </div>
                <div class="col-span-12 educationQualificationTableNoWrap" style="display: {{ !isset($student->other->is_edication_qualification) || $student->other->is_edication_qualification != 1 ? 'block' : 'none' }};">
                    <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Education Qualification status are disabled.
                    </div>
                </div>
            </div>
        </div>

        @php 
            if(!isset($student->other->employment_status) || ($student->other->employment_status == 'Unemployed' || $student->other->employment_status == 'Contractor' || $student->other->employment_status == 'Consultant' || $student->other->employment_status == 'Office Holder')):
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
                        <select id="employment_status" data-applicant="{{ $student->id }}" class="lcc-tom-select w-56 text-left" name="employment_status">
                            <option value="">Please Select</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Part Time' ? 'Selected' : '' }} value="Part Time">Part Time</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Fixed Term' ? 'Selected' : '' }} value="Fixed Term">Fixed Term</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Contractor' ? 'Selected' : '' }} value="Contractor">Contractor</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Zero Hour' ? 'Selected' : '' }} value="Zero Hour">Zero Hour</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Seasonal' ? 'Selected' : '' }} value="Seasonal">Seasonal</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Agency or Temp' ? 'Selected' : '' }} value="Agency or Temp">Agency or Temp</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Consultant' ? 'Selected' : '' }} value="Consultant">Consultant</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Office Holder' ? 'Selected' : '' }} value="Office Holder">Office Holder</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Volunteer' ? 'Selected' : '' }} value="Volunteer">Volunteer</option>
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Unemployed' ? 'Selected' : '' }} value="Unemployed">Unemployed</option> 
                            <option {{ isset($student->other->employment_status) && $student->other->employment_status == 'Full Time' ? 'Selected' : '' }} value="Full Time">Full Time</option> 
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
                                        <li>
                                            <a id="tabulator-export-json-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-html-EH" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="btn btn-primary w-auto ml-2 mr-0 mb-0">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Employement History
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="employmentHistoryTable" data-applicant="{{ $student->id }}" class="mt-5 table-report table-report--tabulator {{ $emptStatus ? 'activeTable' : '' }}"></div>
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
                        <div class="col-span-8 font-medium">{!! ($student->referral_code != '' ? $student->referral_code : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                    </div>
                </div>

                @if($student->status_id >= 6)
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div> 

                @endif

            </div>
        </div>
    </div>

    @include('pages.students.live.show-modals')

@endsection

@section('script')
    @vite('resources/js/student-profile.js')
    @vite('resources/js/student-global.js')
@endsection