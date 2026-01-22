<!-- BEGIN: Address Modal -->
<div id="addressCorrespondenceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressCorrespondenceForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Correspondence Address</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap" >
                        <div class="col-span-12">
                            <label for="address_lookup" class="form-label">Address Lookup</label>
                            <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full theAddressLookup" name="address_lookup">
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full address_line_1" name="student_address_address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full address_line_2" name="student_address_address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full city" name="student_address_city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_state_province_region" class="form-label">State</label>
                            <input type="text" placeholder="State" id="student_address_state_province_region" class="form-control w-full state" name="student_address_state_province_region">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="form-control w-full postal_code" name="student_address_postal_zip_code">
                            <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full country" name="student_address_country">
                            <div class="acc__input-error error-student_address_country text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="insertAddress" class="btn btn-primary w-auto">     
                        Add Address                      
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
                    <input type="hidden" name="place" value=""/>
                    <input type="hidden" name="address_id" value="@if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0) {{ $student->contact->term_time_address_id }} @endif"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->
@if(isset($student->crel->abody) && $student->crel->abody->registration_document_verified==null)
<!-- BEGIN: Edit Modal -->
<div id="awardingBodyEditModal" data-tw-backdrop="static" aria-hidden="true" tabindex="-1"  class="modal group bg-black/60 transition-[visibility,opacity] w-screen h-screen fixed left-0 top-0 [&:not(.show)]:duration-[0s,0.2s] [&:not(.show)]:delay-[0.2s,0s] [&:not(.show)]:invisible [&:not(.show)]:opacity-0 [&.show]:visible [&.show]:opacity-100 [&.show]:duration-[0s,0.4s]">
    <div data-tw-merge class=" w-[90%]  mx-auto bg-white relative rounded-md shadow-md transition-[margin-top,transform] duration-[0.4s,0.3s] -mt-16 group-[.show]:mt-16 group-[.modal-static]:scale-[1.05] dark:bg-darkmode-600 sm:w-[460px]">
        <form method="POST" action="#" id="awardingBodyDetailsVerificationEditModalForm" enctype="multipart/form-data">
            <a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                <i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 h-8 w-8 text-slate-400 h-8 w-8 text-slate-400"></i>
            </a>
            <div class="p-5 text-center">
                <i data-tw-merge data-lucide="info" class="stroke-1.5  mx-auto  mt-3 h-16 w-16 text-warning"></i>
                <div class="mt-5 text-2xl">Pearson Registration Verification</div>
                <div class="mt-5 text-slate-500 grid grid-cols-12 gap-4 text-left mb-2">

                    <div class="col-start-3 col-span-4">
                        <div class="w-auto   text-normal ">Student ID : </div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto  font-medium text-normal "><span class="">{{ $student->registration_no }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto   text-normal "><span class="">First Name : </span></div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto font-medium  text-normal "><span class="">{{ $student->first_name }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto   text-normal"><span class="">Last Name : </span></div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto font-medium  text-normal "><span class="">{{ $student->last_name }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto   text-normal "><span class="">Date of Birth :</span></div>
                    </div>
                    <div class="col-span-6">
                        <div class="w-auto  font-medium text-normal "><span class="">{{ date('jS F, Y' ,strtotime($student->date_of_birth)) }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto   text-normal "><span class="">Course : </span></div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto font-medium text-normal "><span class="">{{ $student->crel->course->name }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto  text-normal "><span class="">Course Code : </span></div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto font-medium text-normal "><span class="">{{ $student->crel->abody->course_code }}</span></div>
                    </div>
                    <div class="col-start-3 col-span-4">
                        <div class="w-auto  text-normal "><span class="">Awarding Body Ref :</span></div>
                    </div>
                    <div class="col-span-5">
                        <div class="w-auto font-medium text-normal "><span class="">{{ ($student->crel->abody->reference ) ? $student->crel->abody->reference : "N/A" }}</span></div>
                    </div>
                    <div class="col-span-12 pt-2 w-auto mx-auto">
                        <div role="alert" class="alert relative border rounded-md px-5 py-4 bg-slate-300 border-secondary bg-opacity-10 text-slate-500 dark:bg-darkmode-100/20 dark:border-darkmode-100/30 dark:text-slate-300 mb-2 flex items-center"><i data-tw-merge data-lucide="check-circle" class="stroke-1.5  mr-2 h-6 w-6 text-success"></i>
                            I here by confirm that my registration abobe are correct 
                        </div>
                    </div>
                </div>
                <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    <input type="hidden" name="student_crel_id" value="{{ $student->crel->id }}" />
                    <input type="hidden" name="id" value="{{ $student->crel->abody->id }}" />
                    <input type="hidden" name="status" value="Yes" />
            </div>
            <div class="px-5 pb-8 text-center">
                <button id="agreeWithAwarding" data-tw-merge type="submit" class="agreeWith transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary w-24 w-24">Yes
                    <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden loadingClass"></i>
                </button>
                <button data-tw-merge type="button" data-tw-toggle="modal" data-tw-target="#confirmAwardingBodyMissingInformationModal" class="disAgreeWith transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger w-24 w-24">No
                    <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden"></i>
                </button>
            </div>
        </form>
    </div>

</div>

<div id="confirmAwardingBodyMissingInformationModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="confirmModalconfirmAwardingBodyMissingInformationModalForm" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="alert-circle" class="w-16 h-16 text-orange-500 mx-auto mt-3"></i>
                    <div class="mt-2 mb-2 text-orange-500 confModDesc text-xl">Could you please let us know which personal details are incorrect?</div>
                    
                    <div class="relative w-[90%] min-w-[200px] mx-auto text-black">
                        <textarea id="note" class="peer h-full focus:ring-0 focus:ring-offset-0 min-h-[100px] w-full resize-none border-b border-0 border-blue-gray-200 bg-transparent pt-4 pb-1.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border-blue-gray-200 focus:border-orange-400 focus:outline-0 disabled:resize-none disabled:border-0 disabled:bg-blue-gray-50"
                          placeholder=" "name = "remarks"></textarea>
                        <label id="note" class="after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[14px] font-normal leading-tight text-blue-gray-500 transition-all after:absolute after:-bottom-0 after:block after:w-full after:scale-x-0 after:border-b-2 after:border-orange-400 after:transition-transform after:duration-300 peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[4.25] peer-placeholder-shown:text-blue-gray-500 peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-orange-400 peer-focus:after:scale-x-100 peer-focus:after:border-orange-400  peer-disabled:text-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
                          Type Here
                        </label>
                        
                    </div>
                    <div class="acc__input-error error-note text-danger mt-2"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    <input type="hidden" name="student_crel_id" value="{{ $student->crel->id }}" />
                    <input type="hidden" name="id" value="{{ $student->crel->abody->id }}" />
                    <input type="hidden" name="status" value="No" />
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button id="formSubmitAward" data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-warning border-warning text-slate-900 dark:border-warning shadow-md mb-2 mr-1 w-24 mb-2  w-24">
                        Submit
                        <i data-loading-icon="three-dots" class="ml-2 w-6 h-6 theLoader loadingClass hidden"></i>
                    </button>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
@endif
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


    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-orange-400 mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmPersonalMobileUpdateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content w-full">
            <div class="modal-body p-0">
                <a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="javascript::void()">
                    <i data-tw-merge data-lucide="x" class="stroke-1.5 w-8 h-8  text-slate-400 "></i>
                </a>
                <div class="p-5 text-center">
                    <i data-lucide="message-square" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">UPDATE PHONE</div>
                    
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">To update your mobile number, please enter the new number below. We will send an OTP to that number. Once you enter the OTP code, your mobile number will be updated.</div>
                   
                    <div  id="modal-mobileverified" class="mt-5">
                            <form method="POST" action="#" id="confirmModalForm2" class="flex-none" enctype="multipart/form-data">
                                <input class="id" type="hidden" name="id" value="">
                                <input type="hidden" name="url" value="{{ route('students.verify.mobile') }}" />
                                <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                                <div class="flex py-2 items-center">
                                    <label for="horizontal-form-2" class="form-label w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Mobile</label>
                                    <input id="horizontal-form-2" name="mobile" type="text" class=" form-control w-60 mr-1 flex-auto" placeholder="079XXXXXXXX">
                                    <button id="resend-mobile" type="submit" data-id="0" data-action="none" class="save btn btn-primary  w-auto ml-auto flex-auto">
                                        <i data-lucide="send" class="w-4 h-4 mr-2 "></i> SEND OTP
                                        <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                                    </button>
                                </div>
                            </form>
                            <form method="POST" action="#" id="confirmModalForm3" enctype="multipart/form-data" class="hidden">
                                <div class="flex py-2 items-center">
                                    <label for="horizontal-form-3" class="form-label w-20 text-left flex-none"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning inline-flex"></i> OTP </label>
                                    <input type="hidden" name="url" value="{{ route('students.update.mobile') }}" />
                                    <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                                    <input id="horizontal-form-3" name="code" type="text" class="form-control w-60 mr-1 flex-auto" placeholder="XXXX">
                                    
                                    <button type="button" data-id="0" data-action="none" class="save btn btn-danger w-auto flex-auto">
                                        <i data-lucide="send" class="w-4 h-4 mr-2 "></i> VERIFY
                                        <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                                    </button>
                                    <div class="acc__input-error error-verify_code text-danger mt-2 w-full text-right"></div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<div id="confirmPersonalEmailUpdateModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content w-full">
            <div class="modal-body p-0">
                <a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="javascript::void()">
                    <i data-tw-merge data-lucide="x" class="stroke-1.5 w-8 h-8  text-slate-400 "></i>
                </a>
                <div class="p-5 text-center">
                    <i data-lucide="message-square" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Update Email</div>
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">If you’d like to change your personal email, please enter the new email address below. Keep in mind that we will send you a verification link, which you need to click to complete the update.</div>
                    <div id="modal-emailverified" class="form-inline">
                        <form method="POST" action="#" id="confirmModalForm1" enctype="multipart/form-data">
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('students.verify.email') }}" />
                        <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                        <label for="horizontal-form-1" class="form-label w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Email </label>
                        
                        <input id="horizontal-form-1" name="email" type="text" class="form-control w-60 mr-2" placeholder="email@example.com">
                        <input name="type" value="email" type="hidden">

                        <button id="send-email" type="submit" data-id="0" data-action="none" class="save btn btn-primary w-auto ml-auto">
                            <i data-lucide="send" class="w-4 h-4 mr-2 "></i> SEND
                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                        </button>
                        <div class="acc__input-success success-email text-success mt-2 w-full text-right"></div>
                        <div class="acc__input-error error-email text-danger mt-2 w-full text-right"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<div id="addressUpdateModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressUpdateForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Address Update  Request</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="validation_status" value="{{ (isset($student->addressUpdateRequest->task->status) && !empty($student->addressUpdateRequest->task->status) ? $student->addressUpdateRequest->task->status : 'NEW') }}"/>
                    @if(isset($student->addressUpdateRequest->task->status) && ($student->addressUpdateRequest->task->status == 'Pending' || $student->addressUpdateRequest->task->status == 'In Progress') )
                        <div class="mb-5">
                            @if($student->addressUpdateRequest->task->status == 'Pending')
                                <span class="bg-pending py-1 px-3 w-auto text-white font-medium rounded">Pending</span>
                            @elseif($student->addressUpdateRequest->task->status == 'In Progress')
                                <span class="bg-warning py-1 px-3 w-auto text-white font-medium rounded">Hold</span>
                            @endif
                        </div>
                    @endif
                    <div>
                        <div class="font-medium mb-1 flex items-center">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>Old Address
                        </div>
                        <div class="pl-6 text-slate-500 uppercase">
                            @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                    <span>{{ $student->contact->termaddress->address_line_1 }}</span>
                                @endif
                                @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                    <span>{{ $student->contact->termaddress->address_line_2 }}</span>
                                @endif
                                <br/>
                                @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                    <span>{{ $student->contact->termaddress->city }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                    <span>{{ $student->contact->termaddress->state }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                    <span>{{ $student->contact->termaddress->post_code }}</span>,
                                @endif
                                <br/>
                                @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                    <span>{{ $student->contact->termaddress->country }}</span>
                                @endif
                            @else 
                                <span class="font-normal text-warning">Not Set Yet!</span><br/>
                            @endif
                        </div>
                    </div>

                    @if(isset($student->addressUpdateRequest->task->status) && ($student->addressUpdateRequest->task->status == 'Pending' || $student->addressUpdateRequest->task->status == 'In Progress') )
                        <div class="font-medium mt-5 mb-2 flex items-center">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>Requested Address
                        </div>
                        <div class="pl-6 text-slate-500 uppercase">
                            @if(isset($student->addressUpdateRequest->id) && $student->addressUpdateRequest->id > 0)
                                @if(isset($student->addressUpdateRequest->address_line_1) && !empty($student->addressUpdateRequest->address_line_1))
                                    <span>{{ $student->addressUpdateRequest->address_line_1 }}</span>
                                @endif
                                @if(isset($student->addressUpdateRequest->address_line_2) && !empty($student->addressUpdateRequest->address_line_2))
                                    <span>{{ $student->addressUpdateRequest->address_line_2 }}</span>
                                @endif
                                <br/>
                                @if(isset($student->addressUpdateRequest->city) && !empty($student->addressUpdateRequest->city))
                                    <span>{{ $student->addressUpdateRequest->city }}</span>,
                                @endif
                                @if(isset($student->addressUpdateRequest->state) && !empty($student->addressUpdateRequest->state))
                                    <span>{{ $student->addressUpdateRequest->state }}</span>,
                                @endif
                                @if(isset($student->addressUpdateRequest->postal_code) && !empty($student->addressUpdateRequest->postal_code))
                                    <span>{{ $student->addressUpdateRequest->postal_code }}</span>,
                                @endif
                                <br/>
                                @if(isset($student->addressUpdateRequest->country) && !empty($student->addressUpdateRequest->country))
                                    <span>{{ $student->addressUpdateRequest->country }}</span>
                                @endif
                            @else 
                                <span class="font-normal text-warning">Not Set Yet!</span><br/>
                            @endif
                        </div>

                        @if (isset($student->addressUpdateRequest->docs) && !empty($student->addressUpdateRequest->docs))
                            <div class="font-medium mt-5 mb-2 flex items-center">
                                <i data-lucide="download-cloud" class="w-4 h-4 mr-2"></i>Download Proofs
                            </div>
                            @foreach($student->addressUpdateRequest->docs as $doc)
                                @if(Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$doc->current_file_name))
                                    <a target="_blank" href="{{ Storage::disk('s3')->temporaryUrl('public/students/'.$student->id.'/'.$doc->current_file_name, now()->addMinutes(30)) }}" class="text-primary font-medium flex justify-start items-start {{ $loop->last ? '' : 'mb-2' }}">
                                        <i data-lucide="disc" class="w-4 h-4 mr-2"></i>
                                        <span>
                                            {!! (isset($doc->created_at) && !empty($doc->created_at) ? '<span class="block mb-1 text-slate-500">[ '.date('jS M, Y \- h:i A', strtotime($doc->created_at)).' ]</span>' : '') !!}
                                            <span class="block">{!! $doc->display_file_name !!}</span>
                                        </span>
                                    </a>
                                @endif
                            @endforeach
                        @endif

                        @if (isset($student->addressUpdateRequest->notes) && $student->addressUpdateRequest->notes->count() > 0)
                            <div class="font-medium mt-5 mb-2 flex items-center">
                                <i data-lucide="pencil" class="w-4 h-4 mr-2"></i>Notes
                            </div>
                            @foreach($student->addressUpdateRequest->notes as $not)
                                <ul>
                                    <li class="mb-1 flex justify-start items-start">
                                        <i data-lucide="disc" class="w-4 h-4 mr-2"></i>
                                        <div>
                                            <div class="font-medium text-slate-500 mb-1">{{ (isset($not->created_at) && !empty($not->created_at) ? date('jS M, Y \- h:i A', strtotime($not->created_at)) : '') }}</div>
                                            <div>{!! $not->note !!}</div>
                                        </div>
                                    </li>
                                </ul>
                            @endforeach
                        @endif

                        @if(isset($student->addressUpdateRequest->task->status) && $student->addressUpdateRequest->task->status == 'In Progress' )
                            <div class="mt-6 flex justify-start items-center relative">
                                <label for="addrProofDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                    <i data-lucide="upload-cloud" class="w-4 h-4 mr-2 text-white"></i> Upload New Document
                                </label>
                                <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addrProofDocument"/>
                            </div>
                            <div id="addrProofDocumentNames" class="addrProofDocumentNames mt-3" style="display: none"></div>
                            <div class="acc__input-error error-document text-danger mt-2"></div>
                        @endif
                        <input type="hidden" name="id" value="{{ isset($student->addressUpdateRequest->id) && $student->addressUpdateRequest->id > 0 ? $student->addressUpdateRequest->id : 0 }}"/>
                    @else
                        <div class="font-medium mt-5 mb-2 flex items-center">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>New Address
                        </div>
                        <div class="theAddressWrap grid grid-cols-12 gap-x-4 gap-y-3" id="studentAddressWrap9898">
                            <div class="col-span-12">
                                <label for="address_lookup" class="form-label">Address Lookup</label>
                                <input type="text" placeholder="Search address here..." id="address_lookup_2" class="theAddressLookup form-control w-full address_line_1" name="address_lookup">
                            </div>
                            <div class="col-span-12">
                                <label for="address_line_1" class="form-label">Address Line 1 <span class="text-danger ml-2">*</span></label>
                                <input type="text" placeholder="Address Line 1" id="address_line_1" class="address_line_1 form-control w-full" name="address_line_1">
                                <div class="acc__input-error error-address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="address_line_2" class="form-label">Address Line 2</label>
                                <input type="text" placeholder="Address Line 2 (optional)" id="address_line_2" class="address_line_2 form-control w-full" name="address_line_2">
                            </div>
                            <div class="col-span-4">
                                <label for="city" class="form-label">Town/City<span class="text-danger ml-2">*</span></label>
                                <input type="text" placeholder="Town / City" id="city" class="city form-control w-full" name="city">
                                <div class="acc__input-error error-city text-danger mt-2"></div>
                            </div>
                            <div class="col-span-4">
                                <label for="state" class="form-label">Region/County</label>
                                <input type="text" placeholder="Region/County" id="state" class="state form-control w-full" name="state">
                            </div>
                            <div class="col-span-4">
                                <label for="postal_code" class="form-label">Post Code <span class="text-danger ml-2">*</span></label>
                                <input type="text" placeholder="Post Code" id="postal_code" class="postal_code form-control w-full" name="postal_code">
                                <div class="acc__input-error error-postal_code text-danger mt-2"></div>
                            </div>
                            <input type="hidden" id="country" class="country form-control w-full" name="country">
                            <input type="hidden" id="latitude" class="latitude form-control w-full" name="latitude">
                            <input type="hidden" id="longitude" class="longitude form-control w-full" name="longitude">
                        </div>
                        <div class="mt-6 flex justify-start items-center relative">
                            <label for="addrProofDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="upload-cloud" class="w-4 h-4 mr-2 text-white"></i> Upload Proof
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addrProofDocument"/>
                        </div>
                        <div id="addrProofDocumentNames" class="addrProofDocumentNames mt-3" style="display: none"></div>
                        <div class="acc__input-error error-document text-danger mt-2"></div>
                        <input type="hidden" name="id" value="0"/>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    @if(!isset($student->addressUpdateRequest->task->status) || ($student->addressUpdateRequest->task->status != 'Pending' || $student->addressUpdateRequest->task->status != 'In Progress'))
                    <button type="submit" id="updtAddress" class="btn btn-primary w-auto">     
                        Submit Request                    
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
                    @endif
                    <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>