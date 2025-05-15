<!-- BEGIN: Edit Personal Details Modal -->
<div id="editAdmissionPersonalDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editAdmissionPersonalDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Personal Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-4">
                            <label for="title_id" class="form-label">Title <span class="text-danger">*</span></label>
                            <select id="title_id" class="lccTom lcc-tom-select w-full" name="title_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($titles))
                                    @foreach($titles as $t)
                                        <option {{ isset($student->title_id) && $student->title_id == $t->id ? 'Selected' : '' }} value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-title_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="first_name" class="form-label">First Name(s) <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($student->first_name) ? $student->first_name : '' }}" placeholder="First Name" id="first_name" class="form-control" name="first_name">
                            <div class="acc__input-error error-first_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($student->last_name) ? $student->last_name : '' }}" placeholder="Last Name" id="last_name" class="form-control" name="last_name">
                            <div class="acc__input-error error-last_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($student->date_of_birth) ? $student->date_of_birth : '' }}" placeholder="DD-MM-YYYY" id="date_of_birth" class="form-control datepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="sex_identifier_id" class="form-label">Sex Identifier/Gender <span class="text-danger">*</span></label>
                            <select id="sex_identifier_id" class="lccTom lcc-tom-select w-full" name="sex_identifier_id">
                                <option value="" selected>Please Select</option>
                                @if($sexid->count() > 0)
                                    @foreach($sexid as $si)
                                        <option {{ isset($student->sex_identifier_id) && $student->sex_identifier_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-sex_identifier_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="nationality_id" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <select id="nationality_id" class="lccTom lcc-tom-select w-full" name="nationality_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($student->nationality_id) && $student->nationality_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-nationality_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="country_id" class="form-label">Country of Birth <span class="text-danger">*</span></label>
                            <select id="country_id" class="lccTom lcc-tom-select w-full" name="country_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($student->country_id) && $student->country_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-country_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="ethnicity_id" class="form-label">Ethnicity <span class="text-danger">*</span></label>
                            <select id="ethnicity_id" class="lccTom lcc-tom-select w-full" name="ethnicity_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($ethnicity))
                                    @foreach($ethnicity as $n)
                                        <option {{ isset($student->other->ethnicity_id) && $student->other->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-ethnicity_id text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto">     
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
                    <input type="hidden" value="{{ $student->id }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Personal Details Modal -->

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
                            <label for="sexual_orientation_id" class="form-label">Sexual Orientation <span class="text-danger">*</span></label>
                            <select id="sexual_orientation_id" class="lccTom lcc-tom-select w-full" name="sexual_orientation_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($sexualOrientation))
                                    @foreach($sexualOrientation as $so)
                                        <option {{ isset($student->other->sexual_orientation_id) && $so->id == $student->other->sexual_orientation_id ? 'Selected' : '' }} value="{{ $so->id }}">{{ $so->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-sexual_orientation_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="hesa_gender_id" class="form-label">Gender Identity <span class="text-danger">*</span></label>
                            <select id="hesa_gender_id" class="lccTom lcc-tom-select w-full" name="hesa_gender_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($hesaGender))
                                    @foreach($hesaGender as $hg)
                                        <option {{ isset($student->other->hesa_gender_id) && $hg->id == $student->other->hesa_gender_id ? 'Selected' : '' }} value="{{ $hg->id }}">{{ $hg->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-hesa_gender_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label for="religion_id" class="form-label">Religion or Belief <span class="text-danger">*</span></label>
                            <select id="religion_id" class="lccTom lcc-tom-select w-full" name="religion_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($religion))
                                    @foreach($religion as $reg)
                                        <option {{ isset($student->other->religion_id) && $reg->id == $student->other->religion_id ? 'Selected' : '' }} value="{{ $reg->id }}">{{ $reg->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-religion_id text-danger mt-2"></div>
                        </div>
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

<!-- BEGIN: Edit Other Personal Information Modal -->
<div id="editOtherItentificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editOtherItentificationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Student Other Identification</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="application_no" class="form-label">Application Ref. No <span class="text-danger">*</span></label>
                            <input type="text" id="application_no" class="form-control w-full" name="application_no" value="{{ $student->application_no }}" placeholder="Application Ref No.">
                            <div class="acc__input-error error-application_no text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="ssn_no" class="form-label">SSN <span class="text-danger">*</span></label>
                            <input type="text" id="ssn_no" class="form-control w-full" name="ssn_no" value="{{ $student->ssn_no }}" placeholder="SSN">
                            <div class="acc__input-error error-ssn_no text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="uhn_no" class="form-label">UHN Number</label>
                            <input type="text" id="uhn_no" class="form-control w-full" name="uhn_no" value="{{ $student->uhn_no }}" placeholder="UHN Number">
                            <div class="acc__input-error error-uhn_no text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="registration_no" class="form-label">DF SID Number <span class="text-danger">*</span></label>
                            <input type="text" id="registration_no" class="form-control w-full" name="registration_no" value="{{ $student->registration_no }}" placeholder="DF SID Number">
                            <div class="acc__input-error error-registration_no text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="study_mode_id" class="form-label">Study Mode</label>
                            <select name="study_mode_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($StudyModes->count() > 0)
                                    @foreach($StudyModes as $stm)
                                        <option {{ isset($student->other->study_mode_id) && $student->other->study_mode_id == $stm->id ? 'Selected' : '' }} value="{{ $stm->id }}">{{ $stm->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateSOID" class="btn btn-primary w-auto">     
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
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Personal Details Modal -->

<!-- BEGIN: Edit Contact Details Modal -->
<div id="editAdmissionContactDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
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
                        {{--<div class="col-span-12 sm:col-span-6">
                            <label for="personal_email" class="form-label">Personal Email <span class="text-danger">*</span></label>
                            <input value="{{ (isset($student->contact->personal_email) && !empty($student->contact->personal_email) ? $student->contact->personal_email : '') }}" type="text" placeholder="Email" id="email" class="form-control" name="personal_email">
                            <div class="acc__input-error error-personal_email text-danger mt-2"></div>
                        </div>--}}
                        <div class="col-span-12 sm:col-span-6">
                            <label for="personal_email" class="form-label">Personal Email <span class="text-danger">*</span></label>
                            <div class="validationGroup">
                                <input value="{{ isset($student->contact->personal_email) ? $student->contact->personal_email : '' }}" data-org="{{ isset($student->contact->personal_email) ? $student->contact->personal_email : '' }}" id="personal_email" name="personal_email" type="text" class="form-control w-full"  placeholder="Personal Email">
                                <button id="sendEmailVerifiCode" 
                                    data-student-id="{{ isset($student->id) && $student->id > 0 ? $student->id : 0 }}" 
                                    class="btn w-auto mr-0 mb-0 absolute h-full  {{ isset($student->contact->personal_email_verification) && !empty($student->contact->personal_email_verification) && $student->contact->personal_email_verification == 1 ? 'btn-primary verified' : 'btn-danger' }}"
                                    
                                    {{ isset($student->contact->personal_email_verification) && $student->contact->personal_email_verification == 1 ? 'readonly' : '' }}
                                    >
                                    @if(isset($student->contact->personal_email_verification) && $student->contact->personal_email_verification == 1)
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                                    @else
                                        <i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code
                                    @endif 
                                </button>
                                <input type="hidden" class="personal_email_verification" name="personal_email_verification" value="{{ isset($student->contact->personal_email_verification) && $student->contact->personal_email_verification > 0 ? $student->contact->personal_email_verification : 0 }}" data-org="{{ isset($student->contact->personal_email_verification) && $student->contact->personal_email_verification > 0 ? $student->contact->personal_email_verification : 0 }}" />
                            </div>
                            <div class="acc__input-error error-email text-danger mt-2"></div>
                            <div class="acc__input-error error-personal_email_verification text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 emailVerifyCodeGroup" style="display: none;">
                            <label for="pemail_mobile" class="form-label">Email Verification Code <span class="text-danger">*</span></label>
                            <div class="validationGroup">
                                <input value="" id="email_verification_code" name="email_verification_code" type="text" class="form-control w-full"  placeholder="Verification Code">
                                <button id="verifyEmail" data-student-id="{{ isset($student->id) && $student->id > 0 ? $student->id : 0 }}" class="btn w-auto mr-0 mb-0 absolute h-full  btn-primary" >
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verify
                                </button>
                            </div>
                            <div class="acc__input-error error-email_verification_error text-danger mt-2"></div>
                        </div>

                        <div class="col-span-12 sm:col-span-6">
                            <label for="institutional_email" class="form-label">Institutional Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input readonly value="{{ isset($student->contact->institutional_email) && !empty($student->contact->institutional_email) ? $student->contact->institutional_email : '' }}" type="text" placeholder="lcc000001@lcc.ac.uk" id="org_email" class="form-control" name="org_email">
                                <div id="editInstEmail" class="input-group-text cursor-pointer"><i data-lucide="pencil-line" class="w-4 h-4"></i></div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="phone" class="form-label">Home Phone</label>
                            <input value="{{ isset($student->contact->home) ? $student->contact->home : '' }}" type="text" placeholder="Home Phone" id="phone" class="form-control" name="phone">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="mobile" class="form-label">Mobile Phone <span class="text-danger">*</span></label>
                            <div class="validationGroup">
                                <input value="{{ isset($student->contact->mobile) ? $student->contact->mobile : '' }}" data-org="{{ isset($student->contact->mobile) ? $student->contact->mobile : '' }}" id="mobile" name="mobile" type="text" class="form-control w-full phoneMask"  placeholder="Mobile Phone">
                                <button id="sendMobileVerifiCode" 
                                    data-student-id="{{ isset($student->id) && $student->id > 0 ? $student->id : 0 }}" 
                                    class="btn w-auto mr-0 mb-0 absolute h-full  {{ isset($student->contact->mobile_verification) && !empty($student->contact->mobile_verification) && $student->contact->mobile_verification == 1 ? 'btn-primary verified' : 'btn-danger' }}"
                                    
                                    {{ isset($student->contact->mobile_verification) && $student->contact->mobile_verification == 1 ? 'readonly' : '' }}
                                    >
                                    @if(isset($student->contact->mobile_verification) && $student->contact->mobile_verification == 1)
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                                    @else
                                        <i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code
                                    @endif 
                                </button>
                                <input type="hidden" class="mobile_verification" name="mobile_verification" value="{{ isset($student->contact->mobile_verification) && $student->contact->mobile_verification > 0 ? $student->contact->mobile_verification : 0 }}" data-org="{{ isset($student->contact->mobile_verification) && $student->contact->mobile_verification > 0 ? $student->contact->mobile_verification : 0 }}" />
                            </div>
                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                            <div class="acc__input-error error-mobile_verification text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 verifyCodeGroup" style="display: none;">
                            <label for="mobile" class="form-label">Verification Code <span class="text-danger">*</span></label>
                            <div class="validationGroup">
                                <input value="" id="verification_code" name="verification_code" type="text" class="form-control w-full"  placeholder="Verification Code">
                                <button id="verifyMobile" data-student-id="{{ isset($student->id) && $student->id > 0 ? $student->id : 0 }}" class="btn w-auto mr-0 mb-0 absolute h-full  btn-primary" >
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verify
                                </button>
                            </div>
                            <div class="acc__input-error error-mobile_verification_error text-danger mt-2"></div>
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
                            <div class="mt-3">
                                <label for="mobile" class="form-label">Term Time Address Postcode <span class="text-danger">*</span></label>
                                <input value="{{ isset($student->contact->term_time_post_code) ? $student->contact->term_time_post_code : '' }}" type="text" placeholder="Post Code" class="form-control" name="term_time_post_code">
                                <div class="acc__input-error error-term_time_post_code text-danger mt-2"></div>
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
                            <label for="address_line_1" class="form-label">Permanent Address</label>
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
                        <div class="col-span-12 sm:col-span-6">
                            <div>
                                <label for="permanent_country_id" class="form-label">Permanent Country</label>
                                <select class="lcc-tom-select lccTom w-full" name="permanent_country_id">
                                    <option value="">Please Select</option>
                                    @if($country->count() > 0)
                                        @foreach($country as $con)
                                            <option {{ isset($student->contact->permanent_country_id) && $con->id == $student->contact->permanent_country_id ? 'Selected' : '' }} value="{{ $con->id }}">{{ $con->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mt-3">
                                <label for="permanent_post_code" class="form-label">Permanent Postcode</label>
                                <input value="{{ isset($student->contact->permanent_post_code) ? $student->contact->permanent_post_code : '' }}" type="text" placeholder="Post Code" class="form-control" name="permanent_post_code">
                                <div class="acc__input-error error-permanent_post_code text-danger mt-2"></div>
                            </div>
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
                    <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap" >
    
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_1" class="form-control w-full address_line_1" name="student_address_address_line_1">
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
                        <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key=gy26-rh34-cf82-wd85" />
                        <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key=gy26-rh34-cf82-wd85"></script>
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

<!-- BEGIN: Update Qualification Status Modal -->
<div id="editStudentQualStatusModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editStudentQualStatusForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Education Qualification Status</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="form-check form-switch justify-start">
                            <label class="form-check-label m-0 mr-2" for="is_education_qualification">Student have any formal academic qualification?</label>
                            <input {{ (isset($student->other->is_education_qualification) && $student->other->is_education_qualification == 1 ? 'checked' : '') }} id="is_education_qualification" value="1" name="is_education_qualification" class="form-check-input" type="checkbox">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateSQS" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="student_other_detail_id" value="{{ (isset($student->other->id) && $student->other->id > 0 ? $student->other->id : 0) }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Update Qualification Status Modal -->

<!-- BEGIN: Add Qualification Modal -->
<div id="addQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="addQualificationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Additonal Education Qualification</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-4">
                        <label for="highest_academic" class="form-label">Highest Academic Qualification<span class="text-danger">*</span></label>
                        <input type="text" placeholder="Qualification" id="highest_academic" class="form-control w-full" name="highest_academic">
                        <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="awarding_body" class="form-label">Awarding Body</label>
                        <input type="text" placeholder="Awarding Body" id="awarding_body" class="form-control w-full" name="awarding_body">
                        <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="subjects" class="form-label">Subjects</label>
                        <input type="text" placeholder="Subjects" id="subjects" class="form-control" name="subjects">
                        <div class="acc__input-error error-subjects text-danger mt-2"></div>
                    </div>

                    {{--<div class="col-span-12 sm:col-span-3">
                        <label for="result" class="form-label">Result <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Result" id="result" class="form-control" name="result">
                        <div class="acc__input-error error-result text-danger mt-2"></div>
                    </div>--}}
                    <div class="col-span-12 sm:col-span-3">
                        <label for="qualification_grade_id" class="form-label">Result <span class="text-danger">*</span></label>
                        <select id="qualification_grade_id" class="w-full tom-selects" name="qualification_grade_id">
                            <option value="">Please Select</option>
                            @if($qualgrades->count() > 0)
                                @foreach($qualgrades as $qg)
                                    <option value="{{ $qg->id }}">{{ $qg->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-qualification_grade_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label for="degree_award_date" class="form-label">Date Of Award <span class="text-danger">*</span></label>
                        <input type="text" placeholder="DD-MM-YYYY" id="degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label for="previous_provider_id1" class="form-label">Provider Name <span class="text-danger">*</span></label>
                        <select id="previous_provider_id1" class="lcc-tom-select w-full" name="previous_provider_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($PreviousProviders))
                                @foreach($PreviousProviders as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code : $n->df_code }} - {{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-previous_provider_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label for="qualification_type_identifier_id1" class="form-label">Qualification Type <span class="text-danger">*</span></label>
                        <select id="qualification_type_identifier_id1" class="lcc-tom-select w-full" name="qualification_type_identifier_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($QualificationTypeIdentifiers))
                                @foreach($QualificationTypeIdentifiers as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code : $n->df_code }} - {{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-qualification_type_identifier_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="hesa_qualification_subject_id1" class="form-label">Hesa Qualification Subject <span class="text-danger">*</span></label>
                        <select id="hesa_qualification_subject_id1" class="lcc-tom-select w-full" name="hesa_qualification_subject_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HesaQualificationSubjects))
                                @foreach($HesaQualificationSubjects as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code : $n->df_code }} - {{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-hesa_qualification_subject_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="highest_qualification_on_entry_id1" class="form-label">HIghest Qualification Entry (QualEnt3)<span class="text-danger">*</span></label>
                        <select id="highest_qualification_on_entry_id1" class="lcc-tom-select w-full" name="highest_qualification_on_entry_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HighestQualificationOnEntrys))
                                @foreach($HighestQualificationOnEntrys as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code : $n->df_code }} - {{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="hesa_exam_sitting_venue_id1" class="form-label">Exam Sitting</label>
                        <select id="hesa_exam_sitting_venue_id1" class="lcc-tom-select w-full" name="hesa_exam_sitting_venue_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HesaExamSittingVenues))
                                @foreach($HesaExamSittingVenues as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code : $n->df_code }} - {{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-hesa_exam_sitting_venue_id text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEducationQualification" class="btn btn-primary w-auto">     
                        Save                      
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
<!-- END: Add Qualification Modal -->


<!-- BEGIN: Edit Qualification Modal -->
<div id="editQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editQualificationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Education Qualification</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-4">
                        <label for="edit_highest_academic" class="form-label">Highest Academic Qualification <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Qualification" id="edit_highest_academic" class="form-control w-full" name="highest_academic">
                        <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                    </div>
                    <div  class="col-span-12 sm:col-span-4">
                        <label for="edit_awarding_body" class="form-label">Awarding Body </label>
                        <input type="text" placeholder="Awarding Body" id="edit_awarding_body" class="form-control w-full" name="awarding_body">
                        <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                    </div>
                    <div  class="col-span-12 sm:col-span-4">
                        <label for="edit_subjects" class="form-label">Subjects </label>
                        <input type="text" placeholder="Subjects" id="edit_subjects" class="form-control" name="subjects">
                        <div class="acc__input-error error-subjects text-danger mt-2"></div>
                    </div>
                    {{--<div  class="col-span-12 sm:col-span-4">
                        <label for="edit_result" class="form-label">Result <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Result" id="edit_result" class="form-control" name="result">
                        <div class="acc__input-error error-result text-danger mt-2"></div>
                    </div>--}}
                    <div class="col-span-12 sm:col-span-3">
                        <label for="edit_qualification_grade_id" class="form-label">Result <span class="text-danger">*</span></label>
                        <select id="edit_qualification_grade_id" class="w-full tom-selects" name="qualification_grade_id">
                            <option value="">Please Select</option>
                            @if($qualgrades->count() > 0)
                                @foreach($qualgrades as $qg)
                                    <option value="{{ $qg->id }}">{{ $qg->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-qualification_grade_id text-danger mt-2"></div>
                    </div>
                    <div  class="col-span-12 sm:col-span-3">
                        <label for="edit_degree_award_date" class="form-label">Date Of Award <span class="text-danger">*</span></label>
                        <input type="text" placeholder="DD-MM-YYYY" id="edit_degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
                    </div>
                    
                    <div class="col-span-12 sm:col-span-3">
                        <label for="previous_provider_id" class="form-label">Provider Name <span class="text-danger">*</span></label>
                        <select id="previous_provider_id" class=" lcc-tom-select w-full" name="previous_provider_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($PreviousProviders))
                                @foreach($PreviousProviders as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code.' - ' : '' }} {{ (isset($n->df_code) && !empty($n->df_code)) ? $n->df_code.' - ' : '' }}{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-previous_provider_id text-danger mt-2"></div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <label for="qualification_type_identifier_id" class="form-label">Qualification Type <span class="text-danger">*</span></label>
                        <select id="qualification_type_identifier_id" class=" lcc-tom-select w-full" name="qualification_type_identifier_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($QualificationTypeIdentifiers))
                                @foreach($QualificationTypeIdentifiers as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code.' - ' : '' }} {{ (isset($n->df_code) && !empty($n->df_code)) ? $n->df_code.' - ' : '' }}{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-qualification_type_identifier_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="hesa_qualification_subject_id" class="form-label">Hesa Qualification Subject <span class="text-danger">*</span></label>
                        <select id="hesa_qualification_subject_id" class=" lcc-tom-select w-full" name="hesa_qualification_subject_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HesaQualificationSubjects))
                                @foreach($HesaQualificationSubjects as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code.' - ' : '' }}{{ (isset($n->df_code) && !empty($n->df_code)) ? $n->df_code.' - ' : '' }}{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-hesa_qualification_subject_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label for="highest_qualification_on_entry_id" class="form-label">HIghest Qualification Entry (QualEnt3) <span class="text-danger">*</span></label>
                        <select id="highest_qualification_on_entry_id" class=" lcc-tom-select w-full" name="highest_qualification_on_entry_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HighestQualificationOnEntrys))
                                @foreach($HighestQualificationOnEntrys as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code.' - ' : '' }}{{ (isset($n->df_code) && !empty($n->df_code)) ? $n->df_code.' - ' : '' }}{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-highest_qualification_on_entry_id text-danger mt-2"></div>
                    </div>
                    
                    <div class="col-span-12 sm:col-span-4">
                        <label for="hesa_exam_sitting_venue_id" class="form-label">Exam Sitting</label>
                        <select id="hesa_exam_sitting_venue_id" class="lcc-tom-select w-full" name="hesa_exam_sitting_venue_id">
                            <option value="" selected>Please Select</option>
                            @if(!empty($HesaExamSittingVenues))
                                @foreach($HesaExamSittingVenues as $n)
                                    <option value="{{ $n->id }}">{{ (isset($n->hesa_code) && !empty($n->hesa_code)) ? $n->hesa_code.' - ' : '' }} {{ (isset($n->df_code) && !empty($n->df_code)) ? $n->df_code.' - ' : '' }}{{ $n->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-hesa_exam_sitting_venue_id text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEducationQualification" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Qualification Modal -->


<!-- BEGIN: Update Employement Status Modal -->
<div id="editStudentEmpStatusModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editStudentEmpStatusForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Employement Status</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="student_employment_status" class="form-label">Employment Status <span class="text-danger"></span></label>
                        <select id="student_employment_status" class="lcc-tom-select w-full" name="employment_status">
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
                        <div class="acc__input-error error-employment_status text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateSES" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="student_other_detail_id" value="{{ (isset($student->other->id) && $student->other->id > 0 ? $student->other->id : 0) }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Update Employement Status Modal -->

<!-- BEGIN: Add Employement History Modal -->
<div id="addEmployementHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addEmployementHistoryForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Employment History</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Company Name" id="company_name" class="form-control w-full" name="company_name">
                            <div class="acc__input-error error-company_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="company_phone" class="form-label">Company Phone <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Company Phone" id="company_phone" class="form-control w-full" name="company_phone">
                            <div class="acc__input-error error-company_phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Position" id="position" class="form-control w-full" name="position">
                            <div class="acc__input-error error-position text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-5">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="start_date" class="form-control employmentPicker monthYearMask" name="start_date">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-2 text-center">
                            <label for="continuing" class="form-label">Continuing</label>
                            <div class="form-check form-switch mt-2 justify-center">
                                <input id="continuing" class="form-check-input" type="checkbox" name="continuing" value="1">
                                <label class="form-check-label" for="continuing">&nbsp;</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-5">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="end_date" class="form-control employmentPicker monthYearMask" name="end_date">
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>

                        
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="addEmpHistoryAddress">
                            <label for="address_line_1" class="form-label">Company Address <span class="text-danger">*</span></label>
                            <div class="addresses mb-2">
                                <span class="text-warning font-medium">Not set yet!</span>
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                </button>
                                <input type="hidden" name="address_id" class="address_id_field" value="0"/>
                            </div>
                            <div class="acc__input-error error-address_id text-danger mt-2"></div>
                        </div>

                        <div class="col-span-12">
                            <div class="pt-2 mb-2 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                            <div class="font-medium text-base">Reference</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Name" id="contact_name" class="form-control w-full" name="contact_name">
                            <div class="acc__input-error error-contact_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contact_position" class="form-label">Contact Position <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Position" id="contact_position" class="form-control w-full" name="contact_position">
                            <div class="acc__input-error error-contact_position text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Phone" id="contact_phone" class="form-control w-full" name="contact_phone">
                            <div class="acc__input-error error-contact_phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contact_email" class="form-label">Contact Email</label>
                            <input type="email" placeholder="Contact Email" id="contact_email" class="form-control w-full" name="contact_email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEmpHistory" class="btn btn-primary w-auto">     
                        Save                      
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
<!-- END: Add Employement History Modal -->

<!-- BEGIN: Add Employement History Modal -->
<div id="editEmployementHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editEmployementHistoryForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Employment History</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="edit_company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Company Name" id="edit_company_name" class="form-control w-full" name="company_name">
                            <div class="acc__input-error error-company_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_company_phone" class="form-label">Company Phone <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Company Phone" id="edit_company_phone" class="form-control w-full" name="company_phone">
                            <div class="acc__input-error error-company_phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Position" id="edit_position" class="form-control w-full" name="position">
                            <div class="acc__input-error error-position text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-5">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="edit_start_date" class="form-control employmentPicker monthYearMask" name="start_date">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-2 text-center">
                            <label for="edit_continuing" class="form-label">Continuing</label>
                            <div class="form-check form-switch mt-2 justify-center">
                                <input id="edit_continuing" class="form-check-input" type="checkbox" name="continuing" value="1">
                                <label class="form-check-label" for="continuing">&nbsp;</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-5">
                            <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="edit_end_date" class="form-control employmentPicker monthYearMask" name="end_date">
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 addressWrap" id="editEmpHistoryAddress">
                            <label for="address_line_1" class="form-label">Company Address <span class="text-danger">*</span></label>
                            <div class="addresses mb-2">
                                <span class="text-warning font-medium">Not set yet!</span>
                            </div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                </button>
                                <input type="hidden" name="address_id" class="address_id_field" value="0"/>
                            </div>
                            <div class="acc__input-error error-address_id text-danger mt-2"></div>
                        </div>

                        <div class="col-span-12">
                            <div class="pt-2 mb-2 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                            <div class="font-medium text-base">Reference</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Name" id="edit_contact_name" class="form-control w-full" name="contact_name">
                            <div class="acc__input-error error-contact_name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_contact_position" class="form-label">Contact Position <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Position" id="edit_contact_position" class="form-control w-full" name="contact_position">
                            <div class="acc__input-error error-contact_position text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Contact Phone" id="edit_contact_phone" class="form-control w-full" name="contact_phone">
                            <div class="acc__input-error error-contact_phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_contact_email" class="form-label">Contact Email</label>
                            <input type="email" placeholder="Contact Email" id="edit_contact_email" class="form-control w-full" name="contact_email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEmpHistory" class="btn btn-primary w-auto">     
                        update                      
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
                    <input type="hidden" name="id" value="0"/>
                    <input type="hidden" name="ref_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Employement History Modal -->

<!-- BEGIN: Add Proof ID Check Modal -->
<div id="addProoOfIdCheckModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addProoOfIdCheckForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Proof ID Check</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="proof_type" class="form-label">Proof Type <span class="text-danger">*</span></label>
                            <select id="proof_type" class="form-control w-full" name="proof_type">
                                <option value="">Please Select</option>
                                <option value="passport">Passport</option>
                                <option value="birth">Birth</option>
                                <option value="driving">Driving</option>
                                <option value="nid">NID</option>
                                <option value="respermit">Respermit</option>
                            </select>
                            <div class="acc__input-error error-proof_type text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="proof_id" class="form-label">Proof ID <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Proof ID" id="proof_id" class="form-control w-full" name="proof_id">
                            <div class="acc__input-error error-proof_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="proof_expiredate" class="form-label">Expire Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="proof_expiredate" class="form-control w-full datepicker" name="proof_expiredate" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-proof_expiredate text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="addPIC" class="btn btn-primary w-auto">     
                        Save                      
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
<!-- END: Edit Proof ID Check Modal -->

<!-- BEGIN: Add Proof ID Check Modal -->
<div id="editProoOfIdCheckModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editProoOfIdCheckForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Proof ID Check</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="edit_proof_type" class="form-label">Proof Type <span class="text-danger">*</span></label>
                            <select id="edit_proof_type" class="form-control w-full" name="proof_type">
                                <option value="">Please Select</option>
                                <option value="passport">Passport</option>
                                <option value="birth">Birth</option>
                                <option value="driving">Driving</option>
                                <option value="nid">NID</option>
                                <option value="respermit">Respermit</option>
                            </select>
                            <div class="acc__input-error error-proof_type text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="edit_proof_id" class="form-label">Proof ID <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Proof ID" id="edit_proof_id" class="form-control w-full" name="proof_id">
                            <div class="acc__input-error error-proof_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="edit_proof_expiredate" class="form-label">Expire Date <span class="text-danger">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="edit_proof_expiredate" class="form-control w-full datepicker" name="proof_expiredate" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-proof_expiredate text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="editPIC" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Proof ID Check Modal -->

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

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmEmploymentModal" class="modal" tabindex="-1" aria-hidden="true">
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
                    <button type="button" data-status="none" data-applicant="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->


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
                    
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">To update the student's mobile number, please enter the new number below. An OTP will be sent to the new number. Once the OTP is entered, the student's mobile number will be successfully updated.</div>
                   
                    <div  id="modal-mobileverified" class="mt-5">
                            <form method="POST" action="#" id="confirmModalForm2" class="flex-none sm:w-full" enctype="multipart/form-data">
                                <input class="id" type="hidden" name="id" value="">
                                <input type="hidden" name="url" value="{{ route('student.verify.mobile') }}" />
                                <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                                <div class="sm:flex sm:justify-center">
                                    <div class="flex justify-start items-center">
                                        <label for="horizontal-form-2" class="w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Mobile</label>
                                    </div>
                                    <div class="flex justify-start">
                                        <input id="horizontal-form-2" name="mobile" type="text" class="form-control md:w-60 mr-2" placeholder="079XXXXXXXX">
                                        <button id="resend-mobile" type="submit" data-id="0" data-action="none" class="save btn btn-primary flex-auto min-w-max">
                                            <i data-lucide="send" class="w-4 h-4 mr-2 "></i> SEND OTP
                                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <form method="POST" action="#" id="confirmModalForm3" enctype="multipart/form-data" class="hidden">
                                <div class="flex py-2 items-center">
                                    <div>
                                        <div class="flex justify-start items-center">
                                            <label for="horizontal-form-3" class="form-label w-20 text-left flex-none"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning inline-flex"></i> OTP </label>
                                        </div>
                                        <input type="hidden" name="url" value="{{ route('student.update.mobile') }}" />
                                    <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                                    <div>
                                        <input id="horizontal-form-3" name="code" type="text" class="form-control w-60 mr-1 flex-auto" placeholder="XXXX">
                                        <button type="button" data-id="0" data-action="none" class="save btn btn-danger w-auto flex-auto">
                                            <i data-lucide="send" class="w-4 h-4 mr-2 "></i> VERIFY
                                            <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                                        </button>
                                    </div>
                                </div>

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
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">To update the student's personal email, please enter the new email address below. A verification link will be sent to the new email, which the student must click to verify and complete the update.</div>
                    <div id="modal-emailverified" class="form-inline ">
                        <form method="POST" action="#" id="confirmModalForm1" enctype="multipart/form-data" class="sm:w-full">
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('student.verify.email') }}" />
                        <input type="hidden" name="student_user_id" value="{{ $student->users->id }}" />
                        <div class="sm:flex sm:justify-center">
                            <div class="flex justify-start items-center">
                                <label for="horizontal-form-1" class="form-label w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Email </label>
                            </div>
                            
                            <input name="type" value="email" type="hidden">
                            <div class="flex justify-start">
                                <input id="horizontal-form-1" name="email" type="text" class="form-control w-40 md:w-60 mr-2" placeholder="email@example.com">
                            
                                <button id="send-email" type="submit" data-id="0" data-action="none" class="save btn btn-primary w-auto ml-auto">
                                    <i data-lucide="send" class="w-4 h-4 mr-2 "></i> SEND
                                    <i data-loading-icon="oval" data-color="white" class="loadingClass w-4 h-4 ml-2 hidden"></i>
                                </button>
                            </div>
                        </div>
                        <div class="acc__input-success success-email text-success mt-2 w-full text-right"></div>
                        <div class="acc__input-error error-email text-danger mt-2 w-full text-right"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

