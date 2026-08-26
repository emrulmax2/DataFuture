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
<!-- BEGIN: Pearson registration verification -->
<div id="awardingBodyEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog spf-modal spf-modal--verify @impersonating($guard = 'student') spf-modal--closable @endImpersonating">
        <form method="POST" action="#" id="awardingBodyDetailsVerificationEditModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="spf-modal__head">
                    <span class="spf-modal__icon">
                        <i data-lucide="info" class="w-[18px] h-[18px]"></i>
                    </span>
                    <div class="spf-modal__headtext">
                        <h3 class="spf-modal__title">Pearson registration verification</h3>
                        <div class="spf-modal__subtitle">Please check your registration details before continuing</div>
                    </div>
                    {{-- Staff viewing the portal as a student can step past this. The
                         student themselves has to answer: the dialog has a static
                         backdrop, so without this there is no way out of it. --}}
                    @impersonating($guard = 'student')
                        <a href="javascript:;" data-tw-dismiss="modal" class="spf-modal__close" aria-label="Close">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </a>
                    @endImpersonating
                </div>

                <div class="spf-vtable">
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Student ID</div>
                        <div class="spf-vtable__value">{{ $student->registration_no }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">First name</div>
                        <div class="spf-vtable__value">{{ $student->first_name }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Last name</div>
                        <div class="spf-vtable__value">{{ $student->last_name }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Date of birth</div>
                        <div class="spf-vtable__value">{{ !empty($student->date_of_birth) ? date('jS F, Y', strtotime($student->date_of_birth)) : 'N/A' }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Course</div>
                        <div class="spf-vtable__value">{{ optional($student->crel->course)->name ?? 'N/A' }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Course code</div>
                        <div class="spf-vtable__value">{{ !empty($student->crel->abody->course_code) ? $student->crel->abody->course_code : 'N/A' }}</div>
                    </div>
                    <div class="spf-vtable__row">
                        <div class="spf-vtable__label">Awarding body ref</div>
                        <div class="spf-vtable__value">{{ !empty($student->crel->abody->reference) ? $student->crel->abody->reference : 'N/A' }}</div>
                    </div>
                </div>

                {{-- Ticking this is what enables the confirm button below. --}}
                <label class="spf-consent" for="awardingBodyConsent">
                    <input type="checkbox" id="awardingBodyConsent">
                    <span class="spf-consent__text">I hereby confirm that my registration details above are correct.</span>
                </label>

                <input type="hidden" name="student_id" value="{{ $student->id }}" />
                <input type="hidden" name="student_crel_id" value="{{ $student->crel->id }}" />
                <input type="hidden" name="id" value="{{ $student->crel->abody->id }}" />
                <input type="hidden" name="status" value="Yes" />

                <div class="spf-modal__foot">
                    <button type="button" data-tw-toggle="modal" data-tw-target="#confirmAwardingBodyMissingInformationModal" class="disAgreeWith spf-btn spf-btn--danger">
                        No, details are wrong
                    </button>
                    <button type="submit" id="agreeWithAwarding" class="agreeWith spf-btn spf-btn--dark" disabled>
                        Yes, confirm
                        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 hidden loadingClass"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="confirmAwardingBodyMissingInformationModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog spf-modal">
        <form method="POST" action="#" id="confirmModalconfirmAwardingBodyMissingInformationModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="spf-modal__head">
                    <span class="spf-modal__icon">
                        <i data-lucide="alert-circle" class="w-[18px] h-[18px]"></i>
                    </span>
                    <h3 class="spf-modal__title">Which details are wrong?</h3>
                </div>

                <p class="spf-modal__desc">Tell us which of the registration details are incorrect and the college will check them against your record.</p>

                <div class="spf-modal__field">
                    <label class="spf-modal__label" for="awardingBodyRemarks">Details to correct</label>
                    <textarea id="awardingBodyRemarks" name="remarks" class="spf-modal__input spf-modal__input--area" placeholder="For example: my date of birth is shown incorrectly."></textarea>
                    <div class="spf-modal__note error-note error-remarks"></div>
                </div>

                <input type="hidden" name="student_id" value="{{ $student->id }}" />
                <input type="hidden" name="student_crel_id" value="{{ $student->crel->id }}" />
                <input type="hidden" name="id" value="{{ $student->crel->abody->id }}" />
                <input type="hidden" name="status" value="No" />

                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn">Cancel</button>
                    <button type="submit" id="formSubmitAward" class="spf-btn spf-btn--dark">
                        Submit
                        <i data-loading-icon="three-dots" class="w-4 h-4 hidden theLoader loadingClass"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
<!-- END: Pearson registration verification -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--ok">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </span>
                <div class="successModalTitle"></div>
                <div class="successModalDesc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn spf-btn--dark">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->


    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--warn">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </span>
                <div class="successModalTitle"></div>
                <div class="successModalDesc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmPersonalMobileUpdateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog spf-modal">
        <div class="modal-content">
            <div class="spf-modal__head">
                <span class="spf-modal__icon"><i data-lucide="smartphone" class="w-5 h-5"></i></span>
                <h3 class="spf-modal__title confModTitle">Change Mobile</h3>
                <div class="spf-spacer"></div>
                <a href="javascript:;" data-tw-dismiss="modal" class="spf-modal__close" aria-label="Close">
                    <i data-lucide="x" class="w-3 h-3"></i>
                </a>
            </div>

            <p class="spf-modal__desc confModDesc">Request an update to your mobile number. We will text a one-time code to the new number to confirm the change.</p>

            <div class="spf-modal__field">
                <span class="spf-modal__label">Current</span>
                <div class="spf-modal__current">{{ !empty($student->contact->mobile) ? $student->contact->mobile : 'Not set yet' }}</div>
            </div>

            <div id="modal-mobileverified">
                {{-- Step 1: request the code. The script swaps this form for the
                     one below once the code has been sent. --}}
                <form method="POST" action="#" id="confirmModalForm2" enctype="multipart/form-data">
                    <div class="spf-modal__field">
                        <label for="horizontal-form-2" class="spf-modal__label">New mobile number</label>
                        <input id="horizontal-form-2" name="mobile" type="text" class="spf-modal__input mobile" placeholder="07xxx xxx xxx">
                        <div class="acc__input-error error-mobile spf-modal__note"></div>
                    </div>

                    <input class="id" type="hidden" name="id" value="">
                    <input type="hidden" name="url" value="{{ route('students.verify.mobile') }}" />
                    <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />

                    <div class="spf-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="spf-btn">Cancel</button>
                        <button id="resend-mobile" type="submit" data-id="0" data-action="none" class="save spf-btn spf-btn--dark">
                            Send code
                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 hidden"></i>
                        </button>
                    </div>
                </form>

                {{-- Step 2: confirm the code. --}}
                <form method="POST" action="#" id="confirmModalForm3" enctype="multipart/form-data" class="hidden">
                    <div class="spf-modal__field">
                        <label for="horizontal-form-3" class="spf-modal__label">One-time code</label>
                        <input id="horizontal-form-3" name="code" type="text" class="spf-modal__input code" placeholder="XXXX">
                        <div class="acc__input-error error-verify_code spf-modal__note"></div>
                    </div>

                    <input type="hidden" name="url" value="{{ route('students.update.mobile') }}" />
                    <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />

                    <div class="spf-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="spf-btn">Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="save spf-btn spf-btn--dark">
                            Verify &amp; update
                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<div id="confirmPersonalEmailUpdateModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog spf-modal">
        <div class="modal-content">
            <div id="modal-emailverified">
                <form method="POST" action="#" id="confirmModalForm1" enctype="multipart/form-data">
                    <div class="spf-modal__head">
                        <span class="spf-modal__icon"><i data-lucide="mail" class="w-5 h-5"></i></span>
                        <h3 class="spf-modal__title confModTitle">Change Email</h3>
                        <div class="spf-spacer"></div>
                        <a href="javascript:;" data-tw-dismiss="modal" class="spf-modal__close" aria-label="Close">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </a>
                    </div>

                    <p class="spf-modal__desc confModDesc">Request an update to your personal email address. The college will verify the new address before it takes effect.</p>

                    <div class="spf-modal__field">
                        <span class="spf-modal__label">Current</span>
                        <div class="spf-modal__current">{{ !empty($student->contact->personal_email) ? $student->contact->personal_email : 'Not set yet' }}</div>
                    </div>

                    <div class="spf-modal__field">
                        <label for="horizontal-form-1" class="spf-modal__label">New email address</label>
                        <input id="horizontal-form-1" name="email" type="text" class="spf-modal__input email" placeholder="name@example.com">
                        <div class="acc__input-success success-email spf-modal__note spf-modal__note--ok"></div>
                        <div class="acc__input-error error-email spf-modal__note"></div>
                    </div>

                    <input class="id" type="hidden" name="id" value="">
                    <input type="hidden" name="url" value="{{ route('students.verify.email') }}" />
                    <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                    <input name="type" value="email" type="hidden">

                    <div class="spf-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="spf-btn">Cancel</button>
                        <button id="send-email" type="submit" data-id="0" data-action="none" class="save spf-btn spf-btn--dark">
                            Submit request
                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="addressUpdateModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog spf-modal spf-modal--wide">
        <form method="POST" action="#" id="addressUpdateForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="spf-modal__head">
                    <span class="spf-modal__icon"><i data-lucide="map-pin" class="w-5 h-5"></i></span>
                    <h3 class="spf-modal__title">Change Address</h3>
                    <div class="spf-spacer"></div>
                    <a href="javascript:;" data-tw-dismiss="modal" class="spf-modal__close" aria-label="Close">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </a>
                </div>
                <p class="spf-modal__desc">Request an update to your correspondence address. Supporting evidence may be required.</p>
                <div class="spf-modal__body-scroll">
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
                        <div class="spf-modal__section-title" style="margin-top:18px">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>Old Address
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
                        <div class="spf-modal__section-title" style="margin-top:18px">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>Requested Address
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
                            <div class="spf-modal__section-title" style="margin-top:18px">
                                <i data-lucide="download-cloud" class="w-4 h-4"></i>Download Proofs
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
                            <div class="spf-modal__section-title" style="margin-top:18px">
                                <i data-lucide="pencil" class="w-4 h-4"></i>Notes
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
                                <label for="addrProofDocument" class="spf-btn" style="cursor:pointer">
                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload new document
                                </label>
                                <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addrProofDocument"/>
                            </div>
                            <div id="addrProofDocumentNames" class="addrProofDocumentNames mt-3" style="display: none"></div>
                            <div class="acc__input-error error-document text-danger mt-2"></div>
                        @endif
                        <input type="hidden" name="id" value="{{ isset($student->addressUpdateRequest->id) && $student->addressUpdateRequest->id > 0 ? $student->addressUpdateRequest->id : 0 }}"/>
                    @else
                        <div class="spf-modal__section-title" style="margin-top:18px">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>New Address
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
                            <label for="addrProofDocument" class="spf-btn" style="cursor:pointer">
                                <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload proof
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addrProofDocument"/>
                        </div>
                        <div id="addrProofDocumentNames" class="addrProofDocumentNames mt-3" style="display: none"></div>
                        <div class="acc__input-error error-document text-danger mt-2"></div>
                        <input type="hidden" name="id" value="0"/>
                    @endif
                </div>
                <div class="spf-modal__foot" style="margin-top:22px">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn">Cancel</button>
                    @if(!isset($student->addressUpdateRequest->task->status) || ($student->addressUpdateRequest->task->status != 'Pending' || $student->addressUpdateRequest->task->status != 'In Progress'))
                    <button type="submit" id="updtAddress" class="spf-btn spf-btn--dark">
                        Submit request
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