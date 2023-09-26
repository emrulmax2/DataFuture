@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Proposed Course & Programme</div>
            </div>
            <div class="col-span-6 text-right">
                <button data-tw-toggle="modal" data-tw-target="#editStudentCourseDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Course & Programme
                </button>
            </div>
        </div>
        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="grid grid-cols-12 gap-4"> 
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                    <div class="col-span-8 font-medium">{{ $student->course->creation->course->name.' - '.$student->course->semester->name }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">How are you funding your education at London Churchill College?</div>
                    <div class="col-span-8 font-medium">{{ $student->course->student_loan }}</div>
                </div>
            </div>
            @if($student->course->student_loan == 'Student Loan')
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
            @elseif($student->course->student_loan == 'Others')
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

    <!-- BEGIN: Edit Modal -->
    <div id="editStudentCourseDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editStudentCourseDetailsForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Proposed Course & Programme</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="course_creation_id" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Course & Semester <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <select id="course_creation_id" class="lcc-tom-select lccTom w-full" name="course_creation_id">
                                            <option value="" selected>Please Select</option>
                                            @if(!empty($instance))
                                                @foreach($instance as $ci)
                                                    <option {{ isset($student->course->course_creation_id) && $student->course->course_creation_id == $ci->creation->id ? 'selected' : ''}} value="{{ $ci->creation->id }}">{{ $ci->creation->course->name }} - {{ $ci->creation->semester->name }}</option>
                                                @endforeach 
                                            @endif 
                                        </select>
                                        <div class="acc__input-error error-course_creation_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="student_loan" class="form-label sm:pt-2 col-span-12 sm:col-span-6">How are you funding your education at London Churchill College? <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <select id="student_loan" class="lcc-tom-select lccTom w-full" name="student_loan">
                                            <option value="">Please Select</option>
                                            <option {{ isset($student->course->student_loan) && $student->course->student_loan == 'Private' ? 'selected' : ''}} value="Private">Independently/Private</option>
                                            <option {{ isset($student->course->student_loan) && $student->course->student_loan == 'Funding Body' ? 'selected' : ''}} value="Funding Body">Funding Body</option>
                                            <option {{ isset($student->course->student_loan) && $student->course->student_loan == 'Sponsor' ? 'selected' : ''}} value="Sponsor">Sponsor</option>
                                            <option {{ isset($student->course->student_loan) && $student->course->student_loan == 'Student Loan' ? 'selected' : ''}} value="Student Loan">Student Loan</option>
                                            <option {{ isset($student->course->student_loan) && $student->course->student_loan == 'Others' ? 'selected' : ''}} value="Others">Other</option>  
                                        </select>
                                        <div class="acc__input-error error-student_loan text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 studentLoanEnglandFunding" style="display: {{ isset($student->course->student_loan) && $student->course->student_loan == 'Student Loan' ? 'block' : 'none'}};">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="student_finance_england" class="form-label col-span-12 sm:col-span-6">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course? <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="form-check form-switch">
                                            <input {{ isset($student->course->student_finance_england) && $student->course->student_finance_england == 1 ? 'checked' : '' }} id="student_finance_england" class="form-check-input" name="student_finance_england" value="1" type="checkbox">
                                            <label class="form-check-label" for="student_finance_england">&nbsp;</label>
                                        </div>
                                        <div class="acc__input-error error-student_finance_england text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 studentLoanFundReceipt" style="display: {{ isset($student->course->student_loan) && $student->course->student_loan == 'Student Loan' && isset($student->course->student_finance_england) && $student->course->student_finance_england == 1 ? 'block' : 'none' }};">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="fund_receipt" class="form-label col-span-12 sm:col-span-6">Are you already in receipt of funds? <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="form-check form-switch">
                                            <input {{ isset($student->course->fund_receipt) && $student->course->fund_receipt == 1 ? 'checked' : '' }} id="fund_receipt" class="form-check-input" name="fund_receipt" value="1" type="checkbox">
                                            <label class="form-check-label" for="fund_receipt">&nbsp;</label>
                                        </div>
                                        <div class="acc__input-error error-fund_receipt text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 studentLoanApplied" style="display: {{ isset($student->course->student_loan) && $student->course->student_loan == 'Student Loan' ? 'block' : 'none'}};">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="applied_received_fund" class="form-label col-span-12 sm:col-span-6">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution? <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="form-check form-switch">
                                            <input {{ isset($student->course->applied_received_fund) && $student->course->applied_received_fund == 1 ? 'checked' : '' }} id="applied_received_fund" class="form-check-input" name="applied_received_fund" value="1" type="checkbox">
                                            <label class="form-check-label" for="applied_received_fund">&nbsp;</label>
                                        </div>
                                        <div class="acc__input-error error-applied_received_fund text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 otherFundings" style="display: {{ isset($student->course->student_loan) && $student->course->student_loan == 'Others' ? 'block' : 'none'}};">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="other_funding" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Please type other fundings <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <input type="text" placeholder="Other Funding" value="{{ isset($student->course->other_funding) && !empty($student->course->other_funding) ? $student->course->other_funding : '' }}" id="other_funding" class="form-control" name="other_funding">
                                        <div class="acc__input-error error-other_funding text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="full_time" class="form-label col-span-12 sm:col-span-6">Are you applying for evening and weekend classes (Full Time) <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="form-check form-switch">
                                            <input {{ isset($student->course->full_time) && $student->course->full_time == 1 ? 'checked' : '' }} id="full_time" class="form-check-input" name="full_time" value="1" type="checkbox">
                                            <label class="form-check-label" for="full_time">&nbsp;</label>
                                        </div>
                                        <div class="acc__input-error error-full_time text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="fee_eligibility_id" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Fee Eligibility</label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <select id="fee_eligibility_id" class="lcc-tom-select lccTom w-full" name="fee_eligibility_id">
                                            <option value="">Please Select</option>
                                            @if($feeelegibility->count() > 0)
                                                @foreach($feeelegibility as $fl)
                                                    <option {{ isset($student->feeeligibility->fee_eligibility_id) && $student->feeeligibility->fee_eligibility_id == $fl->id ? 'Selected' : '' }} value="{{ $fl->id }}">{{ $fl->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="savePCP" class="btn btn-primary w-auto">     
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
                        <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                        <input type="hidden" value="{{ (isset($student->course->id) ? $student->course->id : 0) }}" name="id"/>
                        <input type="hidden" value="{{ (isset($student->feeeligibility->id) && $student->feeeligibility->id > 0 ? $student->feeeligibility->id : 0) }}" name="student_fee_eligibility_id"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Modal -->


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

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-course.js')
@endsection