@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 2xl:col-span-9">
        <div class="grid grid-cols-12 gap-6">
            <!-- BEGIN: Profile Info -->
            @include('pages.students.frontend.dashboard.show-info')
            <!-- END: Profile Info -->
            <div class="intro-y mt-5 col-span-12">
                <div class="intro-y box p-5 ">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Personal Information</div>
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
                                <div class="col-span-8 font-medium">{{ (isset($student->date_of_birth) && !empty($student->date_of_birth) ? date('jS M, Y', strtotime($student->date_of_birth)) : '') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Sex Identifier/Gender</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->sexid->name) ? $student->sexid->name : '') }}</div>
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
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>
                                <div class="col-span-8 font-medium">{{ isset($student->other->ethnicity->name) ? $student->other->ethnicity->name : '' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="intro-y box p-5  mt-5">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Student Other Personal Information</div>
                        </div>
                        
                        
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Sexual Orientation</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->other->sexori->name) && !empty($student->other->sexori->name) ? $student->other->sexori->name : '---') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Gender Identity</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->other->gender->name) && !empty($student->other->gender->name) ? $student->other->gender->name : '---') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Religion or Belief</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->other->religion->name) && !empty($student->other->religion->name) ? $student->other->religion->name : '---') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3"></div>
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
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                                    <div class="col-span-8 font-medium">
                                        {!! (isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
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
  
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-12 text-slate-500 font-medium mb-2">Term Time / Correspondence Address</div>
                                <div class="col-span-12 font-medium">
                                    @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                        @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                            <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                            <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                            <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                                        @endif
                                        @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                            <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                                        @endif
                                        @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                            <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                                        @endif
                                        @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                            <span class="font-medium">{{ $student->contact->termaddress->country }}</span><br/>
                                        @endif
                                    @else 
                                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                    @endif
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Term Time Acco. Type</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->contact->ttacom->name) && !empty($student->contact->ttacom->name) ? $student->contact->ttacom->name : '---') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-12 text-slate-500 font-medium mb-2">Permanent Address</div>
                                <div class="col-span-12 font-medium">
                                    @if(isset($student->contact->permanent_address_id) && $student->contact->permanent_address_id > 0)
                                        @if(isset($student->contact->permaddress->address_line_1) && !empty($student->contact->permaddress->address_line_1))
                                            <span class="font-medium">{{ $student->contact->permaddress->address_line_1 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->permaddress->address_line_2) && !empty($student->contact->permaddress->address_line_2))
                                            <span class="font-medium">{{ $student->contact->permaddress->address_line_2 }}</span><br/>
                                        @endif
                                        @if(isset($student->contact->permaddress->city) && !empty($student->contact->permaddress->city))
                                            <span class="font-medium">{{ $student->contact->permaddress->city }}</span>,
                                        @endif
                                        @if(isset($student->contact->permaddress->state) && !empty($student->contact->permaddress->state))
                                            <span class="font-medium">{{ $student->contact->permaddress->state }}</span>, <br/>
                                        @endif
                                        @if(isset($student->contact->permaddress->post_code) && !empty($student->contact->permaddress->post_code))
                                            <span class="font-medium">{{ $student->contact->permaddress->post_code }}</span>,
                                        @endif
                                        @if(isset($student->contact->permaddress->country) && !empty($student->contact->permaddress->country))
                                            <span class="font-medium">{{ $student->contact->permaddress->country }}</span><br/>
                                        @endif
                                    @else 
                                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Personal Email</div>
                                <div class="col-span-8 font-medium">
                                    {{ $student->contact->personal_email }} <span class="btn inline-flex btn-success px-2 ml-2 py-0 text-white rounded-0">Verified</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Institutional/Login Email</div>
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
                            <div class="grid grid-cols-12 gap-0 mb-3">
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
                        
                    </div>
                </div>
                <div class="intro-y box p-5 mt-5">
                    <div class="grid grid-cols-12 gap-0 items-center">
                            <div class="font-medium text-base">Course Details</div>
                        
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                                <div class="col-span-8 font-medium">
                                        <span>{{ $student->crel->creation->course->name.' - '.$student->crel->propose->semester->name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Venue</div>
                                <div class="col-span-8 font-medium">
                                        <span>{{ $venue }}</span>
                                </div>
                            </div>
                        </div>
                        @if($studentCourseAvailability->count()>0)
                            @foreach ($studentCourseAvailability as $availability)
                                <div class="col-span-12 sm:col-span-12">
                                    <div class="grid grid-cols-12 gap-0">
                                        <div class="col-span-4 text-slate-500 font-medium">Course Start</div>
                                        <div class="col-span-8 font-medium">{{ $availability->course_start_date }}</div>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-12">
                                    <div class="grid grid-cols-12 gap-0">
                                        <div class="col-span-4 text-slate-500 font-medium">Course End</div>
                                        <div class="col-span-8 font-medium">{{ $availability->course_end_date }}</div>
                                    </div>
                                </div>
                                
                            @endforeach
                        @endif
            
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Awarding Body</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : 'Unknown')}}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Awarding Body Ref</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="intro-y box p-5 mt-5">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Next of Kin</div>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Name</div>
                                <div class="col-span-8 font-medium">{{ isset($student->kin->name) ? $student->kin->name : '' }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                                <div class="col-span-8 font-medium">{{ isset($student->kin->relation->name) ? $student->kin->relation->name : '' }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mb-3">
                                <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                                <div class="col-span-8 font-medium">{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}</div>
                            </div>
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Email</div>
                                <div class="col-span-8 font-medium">{{ (isset($student->kin->email) && !empty($student->kin->email) ? $student->kin->email : '---') }}</div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-12 text-slate-500 font-medium mb-2">Address</div>
                                <div class="col-span-12 font-medium">
                                    @if(isset($student->kin->address_id) && $student->kin->address_id > 0)
                                        @if(isset($student->kin->address->address_line_1) && !empty($student->kin->address->address_line_1))
                                            <span class="font-medium">{{ $student->kin->address->address_line_1 }}</span><br/>
                                        @endif
                                        @if(isset($student->kin->address->address_line_2) && !empty($student->kin->address->address_line_2))
                                            <span class="font-medium">{{ $student->kin->address->address_line_2 }}</span><br/>
                                        @endif
                                        @if(isset($student->kin->address->city) && !empty($student->kin->address->city))
                                            <span class="font-medium">{{ $student->kin->address->city }}</span>,
                                        @endif
                                        @if(isset($student->kin->address->state) && !empty($student->kin->address->state))
                                            <span class="font-medium">{{ $student->kin->address->state }}</span>, <br/>
                                        @endif
                                        @if(isset($student->kin->address->post_code) && !empty($student->kin->address->post_code))
                                            <span class="font-medium">{{ $student->kin->address->post_code }}</span>,
                                        @endif
                                        @if(isset($student->kin->address->country) && !empty($student->kin->address->country))
                                            <br/><span class="font-medium">{{ $student->kin->address->country }}</span>
                                        @endif
                                    @else 
                                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="intro-y box p-5 mt-5">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Others</div>
                        </div>

                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12 sm:col-span-12">
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-4 text-slate-500 font-medium">Communication Consent</div>
                                <div class="col-span-8"> 
                                    @if(!empty($stdConsentIds) && $consent->count() > 0)
                                        <ul class="m-0 p-0 mb-2">
                                            @foreach($consent as $con)
                                                @if(in_array($con->id, $stdConsentIds))
                                                <li class="text-left font-normal mb-3 pl-6 relative">
                                                    <i data-lucide="check-circle" class="w-4 h-4 text-success absolute" style="left: 0; top: 4px;"></i>
                                                    <div class="font-medium text-base">{{ $con->name }}</div>
                                                    <div class="pt-1">{{ $con->description }}</div>
                                                </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @else 
                                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Student consent not set yet.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!--End 2xl:col-span-9-->  
    @include('pages.students.frontend.dashboard.profile.sidebar')
 
</div><!--End GRID-->   
@endsection



