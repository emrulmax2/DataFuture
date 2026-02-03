<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application of {{ $applicant->first_name }} {{ $applicant->last_name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f2fc] text-slate-800 text-[13px] leading-normal min-h-screen">
    <div class="max-w-5xl mx-auto p-0 md:p-6">
        @if($alreadyAccepted)
            <div class="intro-y box p-5 mb-4 text-center bg-green-50 border border-green-200 rounded">
                <p class="text-lg font-semibold text-green-700">This E-Signature Has Already Been Accepted.</p>
            </div>
        @else
            
        
        <form id="applicationForm" class="block shadow-lg bg-white" method="POST">
            @csrf
            <div class="intro-y box shadow-none rounded-bl-none rounded-br-none p-5 m-0 bg-primary">
                <div class="card-body">
                    <div class="flex justify-between items-center">
                        <div>
                            <img class="h-16 w-auto" src="{{ asset('build/assets/images/red_and_white_logo.png') }}" alt="London Churchill College">
                        </div>
                        <div class="text-right">
                            <img class="h-16 w-auto mx-auto rounded-md border inline-block" 
                                 alt="{{ $applicant->title->name }} {{ $applicant->first_name }} {{ $applicant->last_name }}" 
                                 src="{{ isset($applicant->photo) && !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/'.$applicant->id.'/'.$applicant->photo) ? asset('storage/applicants/'.$applicant->id.'/'.$applicant->photo) : asset('build/assets/images/placeholders/200x200.jpg') }}">
                            @if(!empty($applicant->application_no))
                                <span class="text-sm text-white font-bold block mt-1">Application Ref: {{ $applicant->application_no }}</span>
                            @endif
                        </div>
                    </div>
                    <!-- <div class="text-center mt-4 py-2 bg-[#64189e] text-white rounded-md font-bold">
                        UK/EU Student Application
                    </div> -->
                </div>
            </div>
            <div class="alert alert-success-soft show rounded-none m-0 p-5 py-10 text-[16px] flex items-start">
                <i data-lucide="alert-octagon" class="w-6 h-6 mr-3 relative" style="flex: 0 0 24px; top: 3px;"></i>
                Please review all the details of the application carefully and sign it if everything is correct. If you notice any incorrect 
                information, do not sign the document and kindly inform us. Thank you.
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse active" type="button">
                        Personal Details
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Full Name</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="user" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->title->name }} {{ $applicant->first_name }} {{ $applicant->last_name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Date of Birth</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="calendar" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ date('jS F, Y', strtotime($applicant->date_of_birth)) }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Gender</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="shield" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ isset($applicant->sexid->name) && !empty($applicant->sexid->name) ? $applicant->sexid->name : '' }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Nationality</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="globe" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->nation->name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Country of Birth</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="globe" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->country->name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Ethnicity</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="globe" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->other->ethnicity->name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Disability Status</div>
                            @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                                <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                            @else
                                <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                            @endif
                        </div>
                        @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Allowance Claimed?</div>
                            @if((isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1))
                                <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                            @else
                                <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                            @endif
                        </div>
                        @endif
                        @if((isset($applicant->other->disability_status) && $applicant->other->disability_status == 1))
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Disabilities</div>
                            @if(isset($applicant->disability) && !empty($applicant->disability))
                            <ul class="list-disc p-0">
                                @foreach($applicant->disability as $dis)
                                <li class="flex items-start font-medium text-slate-800 mb-2"><i data-lucide="check-circle" class="w-4 h-4 mr-3 text-success relative" style="flex: 0 0 16px; top: 3px;"></i>{{ $dis->disabilities->name }}</li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Contact Details
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Email</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="mail" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->users->email }}</span>
                            </div>
                        </div>
                        @if(isset($applicant->contact->home) && !empty($applicant->contact->home))
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Home Phone</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="phone" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->contact->home }}</span>
                            </div>
                        </div>
                        @endif
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Mobile</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="smartphone" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->contact->mobile }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Address</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                <span class="font-medium text-slate-800">
                                    @if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1))
                                        {{ $applicant->contact->address_line_1 }}<br/>
                                    @endif
                                    @if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2))
                                        {{ $applicant->contact->address_line_2 }}<br/>
                                    @endif
                                    @if(isset($applicant->contact->city) && !empty($applicant->contact->city))
                                        {{ $applicant->contact->city }},
                                    @endif
                                    @if(isset($applicant->contact->state) && !empty($applicant->contact->state))
                                        {{ $applicant->contact->state }}, <br/>
                                    @endif
                                    @if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code))
                                        {{ $applicant->contact->post_code }},
                                    @endif
                                    @if(isset($applicant->contact->country) && !empty($applicant->contact->country))
                                        {{ $applicant->contact->country }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Next of Kin
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Name</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="user" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->kin->name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Relation</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="user-plus" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ isset($applicant->kin->relation->name) ? $applicant->kin->relation->name : '' }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Mobile</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="smartphone" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->kin->mobile }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Email</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="mail" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Address</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                <span class="font-medium text-slate-800">
                                    @if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1))
                                        {{ $applicant->kin->address_line_1 }}<br/>
                                    @endif
                                    @if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2))
                                        {{ $applicant->kin->address_line_2 }}<br/>
                                    @endif
                                    @if(isset($applicant->kin->city) && !empty($applicant->kin->city))
                                        {{ $applicant->kin->city }},
                                    @endif
                                    @if(isset($applicant->kin->state) && !empty($applicant->kin->state))
                                        {{ $applicant->kin->state }}, <br/>
                                    @endif
                                    @if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code))
                                        {{ $applicant->kin->post_code }},
                                    @endif
                                    @if(isset($applicant->kin->country) && !empty($applicant->kin->country))
                                        {{ $applicant->kin->country }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Proposed Course
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">When would you like to start your course?</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="calendar-days" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->course->semester->name }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Which course do you propose to take?</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                <i data-lucide="book" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->course->creation->course->name }}</span>
                            </div>
                        </div>
                        @if(isset($applicant->course->venue) && !empty($applicant->course->venue))
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Which course do you propose to take?</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                <i data-lucide="map-pin" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->course->venue->name }}</span>
                            </div>
                        </div>
                        @endif
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-1 font-medium text-slate-500 mb-2">How are you funding your education at London Churchill College?</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="piggy-bank" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->course->student_loan }}</span>
                            </div>
                        </div>
                        @if($applicant->course->student_loan == 'Student Loan')
                            <div class="col-span-12 sm:col-span-4">
                                <div class="leading-1 font-medium text-slate-500 mb-2">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>
                                @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                                    <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                                @else
                                    <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                                @endif
                            </div>
                            @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                            <div class="col-span-12 sm:col-span-4">
                                <div class="leading-1 font-medium text-slate-500 mb-2">Are you already in receipt of funds?</div>
                                @if(isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1)
                                    <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                                @else
                                    <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                                @endif
                            </div>
                            @endif
                            <div class="col-span-12 sm:col-span-4">
                                <div class="leading-1 font-medium text-slate-500 mb-2">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                                @if(isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1)
                                    <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                                @else
                                    <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                                @endif
                            </div>
                        @elseif($applicant->course->student_loan == 'Others')
                            <div class="col-span-12 sm:col-span-4">
                                <div class="leading-none font-medium text-slate-500 mb-2">Other Funding</div>
                                <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                    <i data-lucide="badge-pound-sterling" class="w-4 h-4 mr-3 text-slate-500"></i>
                                    <span class="font-medium text-slate-800">{{ isset($applicant->course->other_funding) && $applicant->course->other_funding != '' ? $applicant->course->other_funding : '' }}</span>
                                </div>
                            </div>
                        @endif
                        @if(isset($applicant->course->creation->has_evening_and_weekend) && $applicant->course->creation->has_evening_and_weekend == 1)
                            <div class="col-span-12 sm:col-span-4">
                                <div class="leading-1 font-medium text-slate-500 mb-2">Are you applying for evening and weekend classes (Full Time)</div>
                                @if(isset($applicant->course->full_time) && $applicant->course->full_time == 1)
                                    <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                                @else
                                    <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                                @endif
                            </div>
                        @endif
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Fee Eligibility</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">
                                    @if(isset($applicant->feeeligibility->elegibility->name) && isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id > 0)
                                        {{ $applicant->feeeligibility->elegibility->name }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Educational Qualification
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-1 font-medium text-slate-500 mb-2">Do you have any formal academic qualification?</div>
                            @if(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1)
                                <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                            @else
                                <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                            @endif
                        </div>
                        <div class="col-span-12 sm:col-span-8"></div>
                        @if(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1)
                            @if(isset($applicant->quals) && $applicant->quals->count() > 0)
                                @php $i = 1; @endphp
                                @foreach($applicant->quals as $qual)
                                    <div class="col-span-12 sm:col-span-4">
                                        <div class="leading-none font-medium text-slate-500 mb-2">Awarding Body</div>
                                        <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                            <span class="font-medium text-slate-800">{{ $qual->awarding_body }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <div class="leading-none font-medium text-slate-500 mb-2">Highest Academic Qualification</div>
                                        <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                            <span class="font-medium text-slate-800">{{ $qual->highest_academic }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <div class="leading-none font-medium text-slate-500 mb-2">Subjects</div>
                                        <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                            <span class="font-medium text-slate-800">{{ $qual->subjects }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <div class="leading-none font-medium text-slate-500 mb-2">Result</div>
                                        <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                            <span class="font-medium text-slate-800">{{ $qual->result }}</span>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <div class="leading-none font-medium text-slate-500 mb-2">Award Date</div>
                                        <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                            <i data-lucide="calendar" class="w-4 h-4 mr-3 text-slate-500"></i>
                                            <span class="font-medium text-slate-800">{{ date('F, Y', strtotime($qual->degree_award_date)) }}</span>
                                        </div>
                                    </div>
                                    @if($i != $applicant->quals->count())
                                        <div class="col-span-12"><div class="bg-slate-200" style="height: 1px;"></div></div>
                                    @endif
                                    @php $i++; @endphp
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Employment History
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Employment Status</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ isset($applicant->other->employment_status) && $applicant->other->employment_status != '' ? $applicant->other->employment_status : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-8"></div>
                        @php
                            if(!isset($applicant->other->employment_status) || 
                            in_array($applicant->other->employment_status, ['Unemployed', 'Contractor', 'Consultant', 'Office Holder'])) {
                                $emptStatus = false;
                            } else {
                                $emptStatus = true;
                            }
                            $i = 1;
                        @endphp
                        @if($emptStatus && isset($applicant->employment) && $applicant->employment->count() > 0)
                            @foreach($applicant->employment as $empt)
                                @php
                                    $address = '';
                                    $address .= $empt->address_line_1.'<br/>';
                                    $address .= ($empt->address_line_2 != '' ? $empt->address_line_2.'<br/>' : '');
                                    $address .= ($empt->city != '' ? $empt->city.', ' : '');
                                    $address .= ($empt->state != '' ? $empt->state.', ' : '');
                                    $address .= ($empt->post_code != '' ? $empt->post_code.', ' : '');
                                    $address .= ($empt->country != '' ? '<br/>'.$empt->country : '');
                                @endphp

                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Organization</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->company_name }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Phone</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="phone" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->company_phone }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Position</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->position }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Start Date</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="calendar" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ !empty($empt->start_date) ? date('F, Y', strtotime('01-'.$empt->start_date)) : 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">End Date</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="calendar" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ !empty($empt->end_date) ? date('F, Y', strtotime('01-'.$empt->end_date)) : 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Contact Person</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="calendar" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->reference[0]->name }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Contact Position</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->reference[0]->position }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Contact Phone</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                        <span class="font-medium text-slate-800">{{ $empt->reference[0]->phone }}</span>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <div class="leading-none font-medium text-slate-500 mb-2">Address</div>
                                    <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                        <span class="font-medium text-slate-800">
                                            {!! $address !!}
                                        </span>
                                    </div>
                                </div>

                                @if($i != $applicant->employment->count())
                                    <div class="col-span-12"><div class="bg-slate-200" style="height: 1px;"></div></div>
                                @endif
                                @php $i++; @endphp
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <!-- Residency status and criminal conviction details with proper ui as like as current design -->
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Residency Status & Criminal Convictions
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Residency Status</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="home" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ isset($applicant->residency->residencyStatus->name) ? $applicant->residency->residencyStatus->name : 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-none font-medium text-slate-500 mb-2">Have you been convicted of any criminal offence in the UK or any other Country?</div>
                            @if(isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1)
                                <span class="btn btn-success-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-success font-medium"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Yes</span>
                            @else
                                <span class="btn btn-danger-soft py-1 px-2 inline-flex rounded-md justify-start items-center w-auto text-danger font-medium"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>No</span>
                            @endif
                        </div>
                        @if(isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1)
                        <div class="col-span-12 sm:col-span-8">
                            <div class="leading-none font-medium text-slate-500 mb-2">Conviction Details</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-start px-3 py-2">
                                <i data-lucide="file-text" class="w-4 h-4 mr-3 text-slate-500 relative" style="flex: 0 0 16px; top: 3px;"></i>
                                <span class="font-medium text-slate-800">
                                    {{ (isset($applicant->criminalConviction->criminal_conviction_details) && $applicant->criminalConviction->criminal_conviction_details != '' ? $applicant->criminalConviction->criminal_conviction_details : 'N/A') }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Others
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        @if(isset($applicant->referral_code) && $applicant->referral_code != '')
                        <div class="col-span-12 sm:col-span-4">
                            <div class="leading-1 font-medium text-slate-500 mb-2">If you referred by Someone/Agent, Please enter the Referral Code.</div>
                            <div class="inputGroup border border-slate-200 relative rounded-md flex justify-start items-center px-3 py-2">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-3 text-slate-500"></i>
                                <span class="font-medium text-slate-800">{{ $applicant->referral_code }}</span>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="box-header">
                    <button class="relative w-full text-lg font-semibold bg-slate-200 p-5 text-left eSignCollapse" type="button">
                        Consents
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div class="p-5 pb-10">
                    <div class="grid grid-cols-12 gap-x-5 gap-y-7">
                        <div class="col-span-12">
                            <div class="form-check m-0 items-start">
                                <input id="video_consent" class="form-check-input border-[#2563eb] h-9 w-9 mr-5 bg-[#2563eb] checked:bg-[#2563eb]" type="checkbox" name="video_consent" value="1">
                                <label class="leading-1 font-medium text-slate-500 cursor-pointer" for="video_consent">
                                    I hereby authorize the filming and utilization of recordings featuring my person, 
                                    conducted by members or staff of London Churchill College, exclusively for admission purposes
                                </label>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <div class="form-check m-0 items-start">
                                <input id="declaration" class="form-check-input border-[#2563eb] h-9 w-9 mr-5 bg-[#2563eb] checked:bg-[#2563eb]" type="checkbox" name="declaration" value="1">
                                <label class="leading-1 font-medium text-slate-500 cursor-pointer" for="declaration">
                                    I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my knowledge.
                                     It is my responsibility to stay informed about the terms and conditions as well as the policies of the college, 
                                     and I commit to comply with them. I have thoroughly reviewed the college's terms and conditions and student 
                                     privacy policy and pledge to adhere to them throughout my entire course of study.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box shadow-none rounded-none p-0">
                <div class="signatureWrap cursor-pointer relative bg-[#CCD7FF]">
                    <span class="bg-[#4636E3] text-white text-xs px-2 py-1 leading-none inline-flex font-medium">Sign</span>
                    <img src="{{ asset('build/assets/images/default_signature.jpg') }}" alt="signature image"/>
                    <img src="" alt="theSignature" id="theSignature" class="hidden"/>
                    <input type="hidden" id="signature-input" name="signature" value=""/>
                </div>
            </div>
            <div class="intro-y box shadow-none rounded-none p-10 text-center">
                <button type="submit" id="saveForm" class="btn btn-success text-white w-auto">     
                    Submit                      
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
            <input type="hidden" name="applicant_id" value="{{ $hashedId }}">
        </form>
        @endif
    </div>

    <!-- BEGIN: Edit Contact Details Modal -->
    <div id="addSignatureModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addSignatureForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header bg-[#f0f3f9]">
                        <h2 class="font-medium text-base mr-auto">Add Signature</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body p-0">
                        <ul class="nav nav-tabs bg-[#f0f3f9] px-5 signatureTabMenu" role="tablist">
                            <li id="signatureTab-label" class="nav-item" role="presentation">
                                <button
                                    class="nav-link w-full py-2 px-3 font-medium active"
                                    data-tw-toggle="pill"
                                    data-tw-target="#signatureTab"
                                    type="button"
                                    role="tab"
                                    aria-controls="signatureTab"
                                    aria-selected="true"
                                >
                                    Draw
                                </button>
                            </li>
                            <li id="imageTab-label" class="nav-item" role="presentation">
                                <button
                                    class="nav-link w-full py-2 px-3 font-medium"
                                    data-tw-toggle="pill"
                                    data-tw-target="#imageTab"
                                    type="button"
                                    role="tab"
                                    aria-controls="imageTab"
                                    aria-selected="false"
                                >
                                    Image
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content border-l border-r border-b">
                            <div id="signatureTab" class="tab-pane leading-relaxed text-center p-5 sm:p-7 sm:px-10 active" role="tabpanel" aria-labelledby="signatureTab-label">
                                <div class="signatureWrap text-center">
                                    <canvas id="theSignaturePad"></canvas>
                                </div>
                                <button disabled class="clearSignature cursor-pointer disabled:cursor-not-allowed font-medium text-success disabled:text-slate-400 leading-none mt-5 inline-flex">Sign Here</button>
                            </div>
                            <div id="imageTab" class="tab-pane leading-relaxed text-center p-5  sm:p-7 sm:px-10" role="tabpanel" aria-labelledby="imageTab-label">
                                <label for="signatureImageFile" class="signatureImgWrap block text-center cursor-pointer relative">
                                    <span type="button" class="shadow-none btn btn-secondary border-slate-300 hover:border-slate-300 rounded-none selectImgBtn absolute position-0 m-auto w-[120px] h-[40px] justify-center">Select Image</span>
                                    <input id="signatureImageFile" accept=".png,.jpg,.jpeg" type="file" name="signatureImage" class="absolute left-0 top-0 w-0 h-0 opacity-0"/>
                                    <img id="signatureImage" class="hidden" src="" alt="customSignature"/>
                                    <input type="hidden" id="imageData" name="imageData" value=""/>
                                </label>
                                <button disabled class="clearSignatureImg cursor-pointer disabled:cursor-not-allowed font-medium text-success disabled:text-slate-400 leading-none mt-5 inline-flex">Select or Drop Image</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-[#f0f3f9]">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button disabled type="submit" id="saveSign" class="btn btn-primary w-auto">     
                            Done                      
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
    <!-- END: Edit Contact Details Modal -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Location Permission Modal Content -->
    <div id="LocationPermissionModal" class="modal" tabindex="-1" aria-hidden="true" data-tw-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="map-pin" class="w-16 h-16 text-warning mx-auto mt-3"></i>
                        <div class="text-2xl mt-5 warningModalTitle">Location Permission Required</div>
                        <div class="text-slate-500 mt-2 warningModalDesc">
                            We need your location to proceed. Please allow access.
                        </div>
                        <button id="allowLocationBtn" class="mt-5 btn btn-primary">Allow Location</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Location Permission Modal Content -->


    @routes
    @vite(['resources/js/admission-offer-acceptance.js'])
    <script type="text/javascript">
        (function () {
            document.addEventListener("DOMContentLoaded", function () {
                const isInApp = /FBAN|FBAV|Instagram|Line|Messenger|wv/.test(navigator.userAgent);

                if (isInApp) {
                    alert("To continue, please open this link in Chrome / Safari for full functionality (location and form submission).");
                }
            });
        })()
    </script>
</body>
</html>