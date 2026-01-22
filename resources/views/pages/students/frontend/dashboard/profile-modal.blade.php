<!-- BEGIN: Edit Other Personal Information Modal -->
<div id="editOtherPersonalInfoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editOtherPersonalInfoForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Other Personal Information</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-4">
                            <label for="disability_status" class="form-label">Do you have any disabilities?</label>
                            <div class="form-check form-switch">
                                <input {{ isset($student->other->disability_status) && $student->other->disability_status == 1 ? 'checked' : '' }} id="disability_status" class="form-check-input" name="disability_status" value="1" type="checkbox">
                                <label class="form-check-label" for="disability_status">&nbsp;</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-8 disabilityItems" style="display: {{ isset($student->other->disability_status) && $student->other->disability_status == 1 ? 'block' : 'none' }};">
                            <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                            @php 
                                $ids = [];
                                if(!empty($student->disability)):
                                    foreach($student->disability as $dis): $ids[] = $dis->disability_id; endforeach;
                                endif;
                            @endphp
                            @if(!empty($disability))
                                @foreach($disability as $d)
                                    <div class="form-check {{ !$loop->first ? 'mt-2' : '' }} items-start">
                                        <input {{ (in_array($d->id, $ids) ? 'checked' : '' ) }} id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="form-check-input disability_ids" type="checkbox" value="{{ $d->id }}">
                                        <label class="form-check-label" for="disabilty_id_{{ $d->id }}">{{ $d->name }}</label>
                                    </div>
                                @endforeach 
                            @endif 
                            <div class="acc__input-error error-disability_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 disabilityAllowance" style="display: {{ !empty($ids) && isset($student->other->disability_status) && $student->other->disability_status == 1 ? 'block' : 'none' }};">
                            <label for="disability_id" class="form-label">Do You Claim Disabilities Allowance?</label>
                            <div class="form-check form-switch">
                                <input {{ isset($student->other->disabilty_allowance) && $student->other->disabilty_allowance == 1 ? 'checked' : '' }} id="disabilty_allowance" class="form-check-input" name="disabilty_allowance" value="1" type="checkbox">
                                <label class="form-check-label" for="disabilty_allowance">&nbsp;</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveSOI" class="btn btn-primary w-auto">     
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
                    <input type="hidden" value="{{ isset($student->other->id) && $student->other->id > 0 ? $student->other->id : 0 }}" name="student_other_detail_id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Personal Details Modal -->

<!-- BEGIN: Edit Contact Details Modal -->
<div id="editAdmissionContactDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionContactDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Contact Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="personal_email" class="form-label">Personal Email <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->users->email) ? $student->users->email : '' }}" type="text" placeholder="Email" id="email" class="form-control" name="personal_email">
                            <div class="acc__input-error error-personal_email text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="phone" class="form-label">Home Phone <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->contact->home) ? $student->contact->home : '' }}" type="text" placeholder="Home Phone" id="phone" class="form-control" name="phone">
                            <div class="acc__input-error error-phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div>
                        @php
                            $term_time_address_id = (isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0 ? $student->contact->term_time_address_id : 0);
                            $address = '';
                            if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->address_line_1.'</span><br/>';
                            endif;
                            if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->address_line_2.'</span><br/>';
                            endif;
                            if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->city.'</span>, ';
                            endif;
                            if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->state.'</span>, <br/>';
                            endif;
                            if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->post_code.'</span>,<br/>';
                            endif;
                            if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->termaddress->country.'</span><br/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="termTimeAddressWrap">
                            <label for="address_line_1" class="form-label">Term Time Address <span class="text-danger">*</span></label>
                            <div class="addresses mb-2">
                                @if($term_time_address_id > 0)
                                    {!! $address !!}
                                @else 
                                    <span class="text-warning font-medium">Not set yet!</span>
                                @endif
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ $term_time_address_id > 0 ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                                <input type="hidden" name="term_time_address_id" class="address_id_field" value="{{ $term_time_address_id }}"/>
                            </div>
                            <div class="acc__input-error error-term_time_address_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div>
                                <label for="mobile" class="form-label">Term Time Accomodation Type</label>
                                <select class="lcc-tom-select lccTom w-full" name="term_time_accommodation_type_id">
                                    <option value="">Please Select</option>
                                    @if($ttacom->count() > 0)
                                        @foreach($ttacom as $ttc)
                                            <option {{ (isset($student->contact->term_time_accommodation_type_id) && $ttc->id == $student->contact->term_time_accommodation_type_id ? 'Selected' : '') }} value="{{ $ttc->id }}">{{ $ttc->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <div class="mt-3 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        </div>
                        @php
                            $permanent_address_id = (isset($student->contact->permanent_address_id) && $student->contact->permanent_address_id > 0 ? $student->contact->permanent_address_id : 0);
                            $address2 = '';
                            if(isset($student->contact->permaddress->address_line_1) && !empty($student->contact->permaddress->address_line_1)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->address_line_1.'</span><br/>';
                            endif;
                            if(isset($student->contact->permaddress->address_line_2) && !empty($student->contact->permaddress->address_line_2)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->address_line_2.'</span><br/>';
                            endif;
                            if(isset($student->contact->permaddress->city) && !empty($student->contact->permaddress->city)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->city.'</span>, ';
                            endif;
                            if(isset($student->contact->permaddress->state) && !empty($student->contact->permaddress->state)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->state.'</span>, <br/>';
                            endif;
                            if(isset($student->contact->permaddress->post_code) && !empty($student->contact->permaddress->post_code)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->post_code.'</span>,<br/>';
                            endif;
                            if(isset($student->contact->permaddress->country) && !empty($student->contact->permaddress->country)):
                                $address2 .= '<span class="text-slate-600 font-medium">'.$student->contact->permaddress->country.'</span><br/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="permanentAddressWrap">
                            <label for="address_line_1" class="form-label">Permanent Address <span class="text-danger">*</span></label>
                            <div class="addresses mb-2">
                                @if($permanent_address_id > 0)
                                    {!! $address2 !!}
                                @else 
                                    <span class="text-warning font-medium">Not set yet!</span>
                                @endif
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ $permanent_address_id > 0 ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                                <input type="hidden" name="permanent_address_id" class="address_id_field" value="{{ $permanent_address_id }}"/>
                            </div>
                            <div class="acc__input-error error-permanent_address_id text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveCD" class="btn btn-primary w-auto">     
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
                    <input type="hidden" value="{{ (isset($student->contact->id) ? $student->contact->id : 0) }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Contact Details Modal -->

<!-- BEGIN: Edit Kin Details Modal -->
<div id="editAdmissionKinDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionKinDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Next of Kin</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->kin->name) ? $student->kin->name : '' }}" type="text" placeholder="Name" id="name" class="form-control" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="kins_relation_id" class="form-label">Relation <span class="text-danger">*</span></label>
                            <select id="kins_relation_id" class="lccTom lcc-tom-select w-full" name="kins_relation_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($relations))
                                    @foreach($relations as $r)
                                        <option {{ isset($student->kin->kins_relation_id) && $student->kin->kins_relation_id == $r->id ? 'Selected' : '' }} value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-kins_relation_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="kins_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->kin->mobile) ? $student->kin->mobile : '' }}" type="text" placeholder="Mobile" id="kins_mobile" class="form-control" name="kins_mobile">
                            <div class="acc__input-error error-kins_mobile text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="kins_email" class="form-label">Email</label>
                            <input value="{{ isset($student->kin->email) ? $student->kin->email : '' }}" type="email" placeholder="Email" id="kins_email" class="form-control" name="kins_email">
                            <div class="acc__input-error error-kins_email text-danger mt-2"></div>
                        </div>
                        
                        @php
                            $kin_address_id = (isset($student->kin->address_id) && $student->kin->address_id > 0 ? $student->kin->address_id : 0);
                            $address3 = '';
                            if(isset($student->kin->address->address_line_1) && !empty($student->kin->address->address_line_1)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->address_line_1.'</span><br/>';
                            endif;
                            if(isset($student->kin->address->address_line_2) && !empty($student->kin->address->address_line_2)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->address_line_2.'</span><br/>';
                            endif;
                            if(isset($student->kin->address->city) && !empty($student->kin->address->city)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->city.'</span>, ';
                            endif;
                            if(isset($student->kin->address->state) && !empty($student->kin->address->state)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->state.'</span>, <br/>';
                            endif;
                            if(isset($student->kin->address->post_code) && !empty($student->kin->address->post_code)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->post_code.'</span>,<br/>';
                            endif;
                            if(isset($student->kin->address->country) && !empty($student->kin->address->country)):
                                $address3 .= '<span class="text-slate-600 font-medium">'.$student->kin->address->country.'</span><br/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="kinAddressWrap">
                            <label for="address_line_1" class="form-label">Kin Address <span class="text-danger">*</span></label>
                            <div class="addresses mb-2">
                                @if($kin_address_id > 0)
                                    {!! $address3 !!}
                                @else 
                                    <span class="text-warning font-medium">Not set yet!</span>
                                @endif
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ $kin_address_id > 0 ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                                <input type="hidden" name="address_id" class="address_id_field" value="{{ $kin_address_id }}"/>
                            </div>
                            <div class="acc__input-error error-address_id text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveNOK" class="btn btn-primary w-auto">     
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
                    <input type="hidden" value="{{ (isset($student->kin->id) ? $student->kin->id : 0) }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Kin Details Modal -->

<!-- BEGIN: Student Consent Modal -->
<div id="editStudentConsentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editStudentConsentForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Consent</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        @if($consent->count() > 0)
                            @foreach($consent as $con)
                                <div class="col-span-12 sm:col-span-2">
                                    <div class="form-check form-switch m-0 mt-1 justify-end">
                                        <input {{ in_array($con->id, $stdConsentIds) ? 'Checked' : '' }} id="student_consent_{{ $con->id }}" name="student_consent[{{ $con->id }}]" value="1" class="form-check-input {{ in_array($con->id, $stdConsentIds) && $con->is_required == 'Yes' ? 'readOnlyConsent' : '' }}" type="checkbox">
                                        <label class="form-check-label" for="student_consent_{{ $con->id }}">&nbsp;</label>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-10 text-left">
                                    <div class="font-medium text-base">{{ $con->name }}</div>
                                    <div class="pt-1">{{ $con->description }}</div>
                                </div>
                            @endforeach
                            <div class="col-span-12 sm:col-span-2"></div>
                            <div class="col-span-12 sm:col-span-10"><div class="acc__input-error error-student_consent text-danger mt-2"></div></div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="editSCP" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Student Consent Modal -->

<!-- BEGIN: Address Modal -->
<div id="addressModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Address</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                    <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap" >
                        <div class="col-span-12">
                            <label for="address_lookup" class="form-label">Address Lookup</label>
                            <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full theAddressLookup" name="address_lookup">
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="address_line_1 form-control w-full" name="student_address_address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="address_line_2 form-control w-full" name="student_address_address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="city form-control w-full" name="student_address_city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_state_province_region" class="form-label">State</label>
                            <input type="text" placeholder="State" id="student_address_state_province_region" class="state form-control w-full" name="student_address_state_province_region">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="postal_code form-control w-full" name="student_address_postal_zip_code">
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
                    <input type="hidden" name="address_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->

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
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

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