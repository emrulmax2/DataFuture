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
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select id="gender" class="lccTom lcc-tom-select w-full" name="gender">
                                <option value="" selected>Please Select</option>
                                <option {{ isset($student->gender) && $student->gender == 'MALE' ? 'Selected' : '' }} value="MALE">MALE</option>
                                <option {{ isset($student->gender) && $student->gender == 'FEMALE' ? 'Selected' : '' }} value="FEMALE">FEMALE</option>
                                <option {{ isset($student->gender) && $student->gender == 'OTHERS' ? 'Selected' : '' }} value="OTHERS">OTHERS</option>
                            </select>
                            <div class="acc__input-error error-gender text-danger mt-2"></div>
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
                                    foreach($student->disability as $dis): $ids[] = $dis->disabilitiy_id; endforeach;
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
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->users->email) ? $student->users->email : '' }}" type="text" placeholder="Email" id="email" class="form-control" name="email">
                            <div class="acc__input-error error-email text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="phone" class="form-label">Home Phone <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->contact->home) ? $student->contact->home : '' }}" type="text" placeholder="Home Phone" id="phone" class="form-control" name="phone">
                            <div class="acc__input-error error-phone text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="mobile" class="form-label">Mobile Phone <span class="text-danger">*</span></label>
                            <input value="{{ isset($student->contact->mobile) ? $student->contact->mobile : '' }}" data-original="{{ isset($student->contact->mobile) ? $student->contact->mobile : '' }}" type="text" placeholder="Mobile Phone" id="mobile" class="form-control" name="mobile">
                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                        </div>
                        @php 
                            $address = $address_line_1 = $address_line_2 = $city = $state = $post_code = $country = '';
                            if(isset($student->contact->address_line_1) && !empty($student->contact->address_line_1)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->address_line_1.'</span><br/>';
                                $address_line_1 = $student->contact->address_line_1;
                            endif;
                            if(isset($student->contact->address_line_2) && !empty($student->contact->address_line_2)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->address_line_2.'</span><br/>';
                                $address_line_2 = $student->contact->address_line_2;
                            endif;
                            if(isset($student->contact->city) && !empty($student->contact->city)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->city.'</span>, ';
                                $city = $student->contact->city;
                            endif;
                            if(isset($student->contact->state) && !empty($student->contact->state)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->state.'</span>, <br/>';
                                $state = $student->contact->state;
                            endif;
                            if(isset($student->contact->post_code) && !empty($student->contact->post_code)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->post_code.'</span>,<br/>';
                                $post_code = $student->contact->post_code;
                            endif;
                            if(isset($student->contact->country) && !empty($student->contact->country)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->contact->country.'</span><br/>';
                                $country = $student->contact->country;
                            endif;

                            if($address != ''):
                                $address .= '<input type="hidden" name="applicant_address" value="'.$address_line_1.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_line_1" value="'.$address_line_1.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_line_2" value="'.$address_line_2.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_city" value="'.$city.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_state" value="'.$state.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_postal_zip_code" value="'.$post_code.'"/>';
                                $address .= '<input type="hidden" name="applicant_address_country" value="'.$country.'"/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-12">
                            <label for="address_line_1" class="form-label">Address <span class="text-danger">*</span></label>
                            <div class="addressWrap mb-2 {{ !empty($address) ? 'active' : '' }}" id="applicanAddress" style="display: {{ !empty($address) ? 'block' : 'none' }};">{!! $address !!}</div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-prefix="applicant" data-address-wrap="#applicanAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ !empty($address) ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                            </div>
                            <div class="acc__input-error error-applicant_address text-danger mt-2"></div>
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
                    <input type="hidden" value="{{ $student->id }}" name="applicant_id"/>
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
                            $address = $address_line_1 = $address_line_2 = $city = $state = $post_code = $country = '';
                            if(isset($student->kin->address_line_1) && !empty($student->kin->address_line_1)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->address_line_1.'</span><br/>';
                                $address_line_1 = $student->kin->address_line_1;
                            endif;
                            if(isset($student->kin->address_line_2) && !empty($student->kin->address_line_2)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->address_line_2.'</span><br/>';
                                $address_line_2 = $student->kin->address_line_2;
                            endif;
                            if(isset($student->kin->city) && !empty($student->kin->city)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->city.'</span>, ';
                                $city = $student->kin->city;
                            endif;
                            if(isset($student->kin->state) && !empty($student->kin->state)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->state.'</span>, <br/>';
                                $state = $student->kin->state;
                            endif;
                            if(isset($student->kin->post_code) && !empty($student->kin->post_code)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->post_code.'</span>,<br/>';
                                $post_code = $student->kin->post_code;
                            endif;
                            if(isset($student->kin->country) && !empty($student->kin->country)):
                                $address .= '<span class="text-slate-600 font-medium">'.$student->kin->country.'</span><br/>';
                                $country = $student->kin->country;
                            endif;

                            if($address != ''):
                                $address .= '<input type="hidden" name="kin_address" value="'.$address_line_1.'"/>';
                                $address .= '<input type="hidden" name="kin_address_line_1" value="'.$address_line_1.'"/>';
                                $address .= '<input type="hidden" name="kin_address_line_2" value="'.$address_line_2.'"/>';
                                $address .= '<input type="hidden" name="kin_address_city" value="'.$city.'"/>';
                                $address .= '<input type="hidden" name="kin_address_state" value="'.$state.'"/>';
                                $address .= '<input type="hidden" name="kin_address_postal_zip_code" value="'.$post_code.'"/>';
                                $address .= '<input type="hidden" name="kin_address_country" value="'.$country.'"/>';
                            endif;
                        @endphp
                        <div class="col-span-12 sm:col-span-6">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <div class="addressWrap mb-2 {{ !empty($address) ? 'active' : '' }}" id="kinAddress" style="display: {{ !empty($address) ? 'block' : 'none' }};">{!! $address !!}</div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-prefix="kin" data-address-wrap="#kinAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>{{ !empty($address) ? 'Update Address' : 'Add Address' }}</span>
                                </button>
                            </div>
                            <div class="acc__input-error error-kin_address text-danger mt-2"></div>
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
                    <input type="hidden" value="{{ $student->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ (isset($student->kin->id) ? $student->kin->id : 0) }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Kin Details Modal -->

<!-- BEGIN: Edit Kin Details Modal -->
<div id="editAdmissionCourseDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editAdmissionCourseDetailsForm" enctype="multipart/form-data">
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
                                    <select id="course_creation_id" class="lcc-tom-select w-full" name="course_creation_id">
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
                                    <select id="student_loan" class="lcc-tom-select w-full" name="student_loan">
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
                                    <select id="fee_eligibility_id" class="lcc-tom-select w-full" name="fee_eligibility_id">
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
                    <input type="hidden" value="{{ $student->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ (isset($student->course->id) ? $student->course->id : 0) }}" name="id"/>
                    <input type="hidden" value="{{ (isset($student->feeeligibility->id) && $student->feeeligibility->id > 0 ? $student->feeeligibility->id : 0) }}" name="applicant_proof_of_id"/>
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
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="form-control w-full required" name="student_address_address_line_1">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="form-control w-full" name="student_address_address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="form-control w-full required" name="student_address_city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_state_province_region" class="form-label">State</label>
                            <input type="text" placeholder="State" id="student_address_state_province_region" class="form-control w-full" name="student_address_state_province_region">
                            <div class="acc__input-error error-student_address_state_province_region text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="form-control w-full required" name="student_address_postal_zip_code">
                            <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Country" id="student_address_country" class="form-control w-full required" name="student_address_country">
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
                    <input type="hidden" name="prefix" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->



<!-- BEGIN: Add Qualification Modal -->
<div id="addQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addQualificationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Education Qualification</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="highest_academic" class="form-label">Highest Academic Qualification <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Qualification" id="highest_academic" class="form-control w-full" name="highest_academic">
                        <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="awarding_body" class="form-label">Awarding Body <span class="text-danger"></span></label>
                        <input type="text" placeholder="Awarding Body" id="awarding_body" class="form-control w-full" name="awarding_body">
                        <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="subjects" class="form-label">Subjects <span class="text-danger"></span></label>
                        <input type="text" placeholder="Subjects" id="subjects" class="form-control" name="subjects">
                        <div class="acc__input-error error-subjects text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="result" class="form-label">Result <span class="text-danger"></span></label>
                        <input type="text" placeholder="Result" id="result" class="form-control" name="result">
                        <div class="acc__input-error error-result text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="degree_award_date" class="form-label">Date Of Award <span class="text-danger"></span></label>
                        <input type="text" placeholder="DD-MM-YYYY" id="degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
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
                    <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Qualification Modal -->


<!-- BEGIN: Edit Qualification Modal -->
<div id="editQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editQualificationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Education Qualification</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="edit_highest_academic" class="form-label">Highest Academic Qualification <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Qualification" id="edit_highest_academic" class="form-control w-full" name="highest_academic">
                        <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_awarding_body" class="form-label">Awarding Body <span class="text-danger"></span></label>
                        <input type="text" placeholder="Awarding Body" id="edit_awarding_body" class="form-control w-full" name="awarding_body">
                        <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subjects" class="form-label">Subjects <span class="text-danger"></span></label>
                        <input type="text" placeholder="Subjects" id="edit_subjects" class="form-control" name="subjects">
                        <div class="acc__input-error error-subjects text-danger mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_result" class="form-label">Result <span class="text-danger"></span></label>
                        <input type="text" placeholder="Result" id="edit_result" class="form-control" name="result">
                        <div class="acc__input-error error-result text-danger mt-2"></div>
                    </div>
                    <div>
                        <label for="edit_degree_award_date" class="form-label">Date Of Award <span class="text-danger"></span></label>
                        <input type="text" placeholder="DD-MM-YYYY" id="edit_degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
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
                    <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Qualification Modal -->

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
                            <input type="text" placeholder="MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="MM-YYYY" data-single-mode="true">
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
                            <input type="text" placeholder="MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                            <div class="addressWrap mb-2" id="empHistoryAddress" style="display: none;"></div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-prefix="employment" data-address-wrap="#empHistoryAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                </button>
                            </div>
                            <div class="acc__input-error error-employment_address text-danger mt-2"></div>
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
                    <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
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
                            <input type="text" placeholder="MM-YYYY" id="edit_start_date" class="form-control datepicker" name="start_date" data-format="MM-YYYY" data-single-mode="true">
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
                            <input type="text" placeholder="MM-YYYY" id="edit_end_date" class="form-control datepicker" name="end_date" data-format="MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <label for="company_address" class="form-label">Company Address <span class="text-danger">*</span></label>
                            <div class="addressWrap mb-2" id="editEmpHistoryAddress" style="display: none;"></div>
                            <div>
                                <button type="button" data-tw-toggle="modal" data-prefix="employment" data-address-wrap="#editEmpHistoryAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                    <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                </button>
                            </div>
                            <div class="acc__input-error error-employment_address text-danger mt-2"></div>
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
                    <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                    <input type="hidden" name="ref_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Employement History Modal -->

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

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmEducationModal" class="modal" tabindex="-1" aria-hidden="true">
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
                    <button type="button" data-applicant="{{ $student->id }}" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
