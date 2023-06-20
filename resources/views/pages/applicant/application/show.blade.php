@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection


@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Application View</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('applicant.dashboard') }}" class="btn btn-primary shadow-md mr-2">Back To Dashobard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 py-10 mt-5">
        <div id="applicantReviewAccordion" class="accordion">
            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-1" class="accordion-header">
                    <button class="accordion-button px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-1" aria-expanded="true" aria-controls="applicantReviewAccordion-col-1">
                        Personal Details
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-1" class="accordion-collapse collapse show" aria-labelledby="applicantReviewAccordion-c-1" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
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
                                        {!! (isset($applicant->other->disability_status) && $applicant->other->disability_status == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}
                                    </div>
                                </div>
                            </div>
                            @if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1)
                                <div class="col-span-12 sm:col-span-3">
                                    <div class="grid grid-cols-12 gap-0">
                                        <div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>
                                        <div class="col-span-8 font-medium">
                                            {!! (isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}
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
                </div>
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-2" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-2" aria-expanded="false" aria-controls="applicantReviewAccordion-col-2">
                        Contact Details
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-2" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-2" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
                        <div class="grid grid-cols-12 gap-4"> 

                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Home Phone</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->contact->home }}</div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->contact->mobile }}</div>
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
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-3" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-3" aria-expanded="false" aria-controls="applicantReviewAccordion-col-3">
                        Next of Kin
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-3" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-3" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
                        <div class="grid grid-cols-12 gap-4"> 

                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Name</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->kin->name }}</div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Relation</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->kin->relation->name }}</div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Mobile</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->kin->mobile }}</div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Email</div>
                                    <div class="col-span-8 font-medium">{{ (isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '---') }}</div>
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
                </div>
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-4" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-4" aria-expanded="false" aria-controls="applicantReviewAccordion-col-4">
                        Proposed Course & Programme
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-4" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-4" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
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
                                        <div class="col-span-8 font-medium">{!! (isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                                    </div>
                                </div>
                                @if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1)
                                    <div class="col-span-12 sm:col-span-12">
                                        <div class="grid grid-cols-12 gap-0">
                                            <div class="col-span-4 text-slate-500 font-medium">Are you already in receipt of funds?</div>
                                            <div class="col-span-8 font-medium">{!! (isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-span-12 sm:col-span-12">
                                    <div class="grid grid-cols-12 gap-0">
                                        <div class="col-span-4 text-slate-500 font-medium">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>
                                        <div class="col-span-8 font-medium">{!! (isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                                    </div>
                                </div>
                            @elseif($applicant->course->student_loan == 'Others')
                                <div class="col-span-12 sm:col-span-12">
                                    <div class="grid grid-cols-12 gap-0">
                                        <div class="col-span-4 text-slate-500 font-medium">Other Funding</div>
                                        <div class="col-span-8 font-medium">{{ (isset($applicant->course->other_funding) && $applicant->course->other_funding == '' ? $applicant->course->other_funding : '') }}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="col-span-12 sm:col-span-12">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Are you applying for evening and weekend classes (Full Time)</div>
                                    <div class="col-span-8 font-medium">{!! (isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-5" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-5" aria-expanded="false" aria-controls="applicantReviewAccordion-col-5">
                        Education Qualifications
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-5" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-5" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
                        <div class="grid grid-cols-12 gap-4"> 

                            <div class="col-span-12 sm:col-span-12">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">Do you have any formal academic qualification? </div>
                                    <div class="col-span-8 font-medium">{!! (isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>') !!}</div>
                                </div>
                            </div>
                            @if(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1)
                                <div class="col-span-12 sm:col-span-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="whitespace-nowrap">#</th>
                                                <th class="whitespace-nowrap">Awarding Body</th>
                                                <th class="whitespace-nowrap">Highest Academic Qualification</th>
                                                <th class="whitespace-nowrap">Subjects</th>
                                                <th class="whitespace-nowrap">Result</th>
                                                <th class="whitespace-nowrap">Award Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(!empty($applicant->quals))
                                                @php $i = 1; @endphp
                                                @foreach($applicant->quals as $qual)
                                                    <tr> 
                                                        <td>{{ $i }}</td>
                                                        <td>{{ $qual->awarding_body }}</td>
                                                        <td>{{ $qual->highest_academic }}</td>
                                                        <td>{{ $qual->subjects }}</td>
                                                        <td>{{ $qual->result }}</td>
                                                        <td>{{ $qual->degree_award_date }}</td>
                                                    </tr>
                                                    @php $i++; @endphp
                                                @endforeach
                                            @else
                                                <tr> 
                                                    <td colspan="6" class="text-center">No Record Found!</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-6" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-6" aria-expanded="false" aria-controls="applicantReviewAccordion-col-6">
                        Employment History
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-6" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-6" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
                        <div class="grid grid-cols-12 gap-4"> 

                            <div class="col-span-12 sm:col-span-12">
                                <div class="grid grid-cols-12 gap-0">
                                    <div class="col-span-4 text-slate-500 font-medium">What is your current employment status?</div>
                                    <div class="col-span-8 font-medium">{{ $applicant->other->employment_status }}</div>
                                </div>
                            </div>
                            @if(isset($applicant->other->employment_status) && ($applicant->other->employment_status != 'Unemployed' && $applicant->other->employment_status != 'Contractor' && $applicant->other->employment_status != 'Consultant' && $applicant->other->employment_status != 'Office Holder'))
                                <div class="col-span-12 sm:col-span-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="whitespace-nowrap">#</th>
                                                <th class="whitespace-nowrap">Company</th>
                                                <th class="whitespace-nowrap">Phone</th>
                                                <th class="whitespace-nowrap">Position</th>
                                                <th class="whitespace-nowrap">Start</th>
                                                <th class="whitespace-nowrap">End</th>
                                                <th class="whitespace-nowrap">Address</th>
                                                <th class="whitespace-nowrap">Contact Person</th>
                                                <th class="whitespace-nowrap">Position</th>
                                                <th class="whitespace-nowrap">Phone</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(!empty($applicant->employment))
                                                @php $i = 1; @endphp
                                                @foreach($applicant->employment as $emps)
                                                    @php 
                                                        $continuing = (isset($emps->continuing) && $emps->continuing > 0 ? $emps->continuing : 0);
                                                        $address = '';
                                                        if(isset($emps->address_line_1) && !empty($emps->address_line_1)):
                                                            $address .= '<span class="font-medium">'.$emps->address_line_1.'</span><br/>';
                                                        endif;
                                                        if(isset($emps->address_line_2) && !empty($emps->address_line_2)):
                                                            $address .= '<span class="font-medium">'.$emps->address_line_2.'</span><br/>';
                                                        endif;
                                                        if(isset($emps->city) && !empty($emps->city)):
                                                            $address .= '<span class="font-medium">'.$emps->city.'</span>, ';
                                                        endif;
                                                        if(isset($emps->state) && !empty($emps->state)):
                                                            $address .= '<span class="font-medium">'.$emps->state.'</span>, <br/>';
                                                        endif;
                                                        if(isset($emps->post_code) && !empty($emps->post_code)):
                                                            $address .= '<span class="font-medium">'.$emps->post_code.'</span>, ';
                                                        endif;
                                                        if(isset($emps->country) && !empty($emps->country)):
                                                            $address .= '<span class="font-medium">'.$emps->country.'</span><br/>';
                                                        endif;
                                                    @endphp
                                                    <tr> 
                                                        <td>{{ $i }}</td>
                                                        <td>{{ $emps->company_name }}</td>
                                                        <td>{{ $emps->company_phone }}</td>
                                                        <td>{{ $emps->position }}</td>
                                                        <td>{{ $emps->start_date }}</td>
                                                        <td>{{ ($continuing == 1 ? 'Continue' : $qual->end_date) }}</td>
                                                        <td>{!! $address !!}</td>
                                                        <td>{{ $emps->reference[0]->name }}</td>
                                                        <td>{{ $emps->reference[0]->position }}</td>
                                                        <td>{{ $emps->reference[0]->phone }}</td>
                                                    </tr>
                                                    @php $i++; @endphp
                                                @endforeach
                                            @else
                                                <tr> 
                                                    <td colspan="6" class="text-center">No Record Found!</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item mb-1">
                <div id="applicantReviewAccordion-c-7" class="accordion-header">
                    <button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-7" aria-expanded="false" aria-controls="applicantReviewAccordion-col-7">
                        Others
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="applicantReviewAccordion-col-7" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-7" data-tw-parent="#applicantReviewAccordion">
                    <div class="accordion-body px-5 pt-6">
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
            </div>

        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/application.js')
@endsection