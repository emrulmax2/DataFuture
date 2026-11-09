@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

{{-- Loaded here rather than from a layout: applicants and agents render this
     same form inside different shells. --}}
@section('styles')
    @vite('resources/css/applicant-portal.css')
@endsection


@section('subcontent')

    <div class="apd-form">
        <div class="form-wizard">
            <div class="form-wizard-header">
                @php $apdRef = isset($apply->application_no) && !empty($apply->application_no) ? $apply->application_no : null; @endphp
                <div style="display:grid;gap:10px">
                    <span class="apd-eyebrow">Application form</span>
                    <span class="apd-stamp"><small>Reference</small><b>{{ $apdRef ?: 'pending' }}</b></span>
                    <p style="font-size:12.5px;color:var(--muted);margin:0">
                        {{ $apdRef ? 'Draft — not yet submitted. Quote this number if you contact admissions.' : 'Issued when you save the first step.' }}
                    </p>
                </div>
                <ul class="form-wizard-steps">
                    <li class="form-wizard-step-item active">
                        <span class="n">01</span>
                        <span><b>Personal &amp; contact details</b><small>Who you are, where you live, next of kin</small></span>
                    </li>
                    <li class="form-wizard-step-item">
                        <span class="n">02</span>
                        <span><b>Course &amp; others</b><small>Course, campus, funding, qualifications, work, referral</small></span>
                    </li>
                    <li class="form-wizard-step-item">
                        <span class="n">03</span>
                        <span><b>Residency &amp; convictions</b><small>Immigration status and declarations</small></span>
                    </li>
                    <li class="form-wizard-step-item">
                        <span class="n">04</span>
                        <span><b>Review &amp; apply</b><small>Check everything and submit</small></span>
                    </li>
                </ul>
                <div style="display:grid;gap:8px">
                    @if(isset(auth('agent')->user()->id))
                        <a href="{{ route('agent.dashboard') }}" class="apd-btn apd-btn--ghost">Save &amp; exit</a>
                    @else
                        <a href="{{ route('applicant.dashboard') }}" class="apd-btn apd-btn--ghost">Save &amp; exit</a>
                    @endif
                    <p style="font-size:12.5px;color:var(--muted);margin:0">Questions? admission@lcc.ac.uk &middot; 020 7377 1077</p>
                </div>
            </div>

            <fieldset class="wizard-fieldset show">
                <form method="post" action="#" id="appicantFormStep_1" class="wizard-step-form">
                    <div class="apd-step-head">
                        <span class="apd-eyebrow">Step 1 of 4</span>
                        <h1 class="apd-step-title">Personal &amp; contact details</h1>
                    </div>
                    <div class="apd-sheet">
                    {{-- @if(isset(auth('agent')->user()->id))
                        <input type="hidden" name="agent_user_id" value=" {{ auth('agent')->user()->id }}" />
                    @endif --}}
                    <h3 class="apd-h3">Personal Details</h3>
                    <div class="apd-grid-2">
                        <div class="">
                            <label for="title_id" class="form-label">Title <span class="text-danger">*</span></label>
                            <select id="title_id" class="applicationLccTom lcc-tom-select w-full" name="title_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($titles))
                                    @foreach($titles as $t)
                                        <option {{ isset($apply->title_id) && $apply->title_id == $t->id ? 'Selected' : '' }} value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-title_id text-danger mt-2"></div>
                        </div>
                        
                        <div class="">
                            <label for="first_name" class="form-label">First name(s) <span class="text-danger">*</span></label>
                            @if(isset(auth('agent')->user()->id))
                                <input type="text" value="{{ isset($agentApplicant->first_name) ? $agentApplicant->first_name : '' }}" placeholder="First Name" id="first_name" class="form-control capitalize" name="first_name" >
                            @else
                                <input type="text" value="{{ isset($apply->first_name) ? $apply->first_name : '' }}" placeholder="First Name" id="first_name" class="form-control capitalize" name="first_name">
                            @endif
                            <div class="acc__input-error error-first_name text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                            @if(isset(auth('agent')->user()->id))
                                <input type="text" value="{{ isset($agentApplicant->last_name) ? $agentApplicant->last_name : '' }}" placeholder="Last Name" id="last_name" class="form-control capitalize" name="last_name" >
                            
                            @else
                                <input type="text" value="{{ isset($apply->last_name) ? $apply->last_name : '' }}" placeholder="Last Name" id="last_name" class="form-control capitalize" name="last_name">
                            @endif
                            <div class="acc__input-error error-last_name text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="date_of_birth" class="form-label">Date of birth <span class="text-danger">*</span></label>
                            <input type="text" value="{{ isset($apply->date_of_birth) ? $apply->date_of_birth : '' }}" placeholder="DD-MM-YYYY" id="date_of_birth" class="form-control datepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-date_of_birth text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="sex_identifier_id" class="form-label">Sex identifier / gender <span class="text-danger">*</span></label>
                            <select id="sex_identifier_id" class="applicationLccTom lcc-tom-select w-full" name="sex_identifier_id">
                                <option value="" selected>Please Select</option>
                                @if($sexid->count() > 0)
                                    @foreach($sexid as $si)
                                        <option {{ isset($apply->sex_identifier_id) && $apply->sex_identifier_id == $si->id ? 'Selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-sex_identifier_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="nationality_id" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <select id="nationality_id" class="applicationLccTom lcc-tom-select w-full" name="nationality_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($apply->nationality_id) && $apply->nationality_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-nationality_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="country_id" class="form-label">Country of birth <span class="text-danger">*</span></label>
                            <select id="country_id" class="applicationLccTom lcc-tom-select w-full" name="country_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($apply->country_id) && $apply->country_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-country_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="ethnicity_id" class="form-label">Ethnicity <span class="text-danger">*</span></label>
                            <select id="ethnicity_id" class="applicationLccTom lcc-tom-select w-full" name="ethnicity_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($ethnicity))
                                    @foreach($ethnicity as $n)
                                        <option {{ isset($apply->other->ethnicity_id) && $apply->other->ethnicity_id == $n->id ? 'Selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-ethnicity_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="care_leaver_id" class="form-label">Care leaver</label>
                            <select id="care_leaver_id" class="applicationLccTom lcc-tom-select w-full" name="care_leaver_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($careleaver))
                                    @foreach($careleaver as $n)
                                        <option {{ isset($apply->other->care_leaver_id) && $apply->other->care_leaver_id == $n->id ? 'Selected' : ($n->df_code == '09' ? 'Selected' : '') }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <span class="apd-hint">Used for support and HESA reporting only.</span>
                            <div class="acc__input-error error-care_leaver_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            @php
                                $ids = [];
                                if(!empty($apply->disability)):
                                    foreach($apply->disability as $dis): $ids[] = $dis->disabilitiy_id; endforeach;
                                endif;
                                $hasDisability = isset($apply->other->disability_status) && $apply->other->disability_status == 1;
                                $claimsAllowance = isset($apply->other->disabilty_allowance) && $apply->other->disabilty_allowance == 1;
                            @endphp
                            <label class="form-label">Do you have any disabilities? <span class="text-danger">*</span></label>
                            {{-- A switch: on = Yes. `#disability_status` is the box, so the
                                 existing `.prop('checked')` test still reads true only for Yes. --}}
                            <x-applicant.switch name="disability_status" id="disability_status" :checked="$hasDisability" />
                            <div class="acc__input-error error-disability_status text-danger mt-2"></div>
                        </div>

                        <div class="apd-span-all disabilityItems" style="display: {{ $hasDisability ? 'block' : 'none' }};">
                            <div class="apd-sub">
                                <div class="apd-field">
                                    <label for="disability_id" class="form-label">Disabilities <span class="text-danger">*</span></label>
                                    <div class="apd-opt-grid">
                                        @if(!empty($disability))
                                            @foreach($disability as $d)
                                                <label class="apd-opt chk" for="disabilty_id_{{ $d->id }}">
                                                    <input {{ (in_array($d->id, $ids) ? 'checked' : '' ) }} id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="sr disability_ids" type="checkbox" value="{{ $d->id }}">
                                                    <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                                    <span>{{ $d->name }}</span>
                                                </label>
                                            @endforeach
                                        @endif
                                    </div>
                                    <span class="apd-hint">Select all that apply.</span>
                                    <div class="acc__input-error error-disability_id text-danger mt-2"></div>
                                </div>

                                <div class="disabilityAllowance" style="display: {{ !empty($ids) && $hasDisability ? 'block' : 'none' }};">
                                    <label class="form-label">Do you claim Disability Allowance?</label>
                                    <x-applicant.switch name="disabilty_allowance" id="disabilty_allowance" :checked="$claimsAllowance" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="apd-h3">Contact Details</h3>
                    <div class="apd-grid-2">
                        <div class="">
                            <label for="phone" class="form-label">Home phone</label>
                            <input value="{{ isset($apply->contact->home) ? $apply->contact->home : '' }}" type="text" placeholder="Home Phone" id="phone" class="form-control applicationPhoneMask" name="phone">
                            <div class="acc__input-error error-phone text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="mobile" class="form-label">Mobile phone <span class="text-danger">*</span></label>
                            @if(isset(auth('agent')->user()->id))
                                <input value="{{ isset($agentApplicant->mobile) ? $agentApplicant->mobile : '' }}" type="text" placeholder="Mobile Phone" id="mobile" class="form-control applicationPhoneMask" name="mobile" readonly="readonly">
                            
                            @else
                                <input value="{{ isset($apply->contact->mobile) ? $apply->contact->mobile : '' }}" type="text" placeholder="Mobile Phone" id="mobile" class="form-control applicationPhoneMask" name="mobile">
                            @endif
                            <div class="acc__input-error error-mobile text-danger mt-2"></div>
                        </div>
                        @php 
                            $address = $address_line_1 = $address_line_2 = $city = $state = $post_code = $country = '';
                            if(isset($apply->contact->address_line_1) && !empty($apply->contact->address_line_1)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->address_line_1.'</span><br/>';
                                $address_line_1 = $apply->contact->address_line_1;
                            endif;
                            if(isset($apply->contact->address_line_2) && !empty($apply->contact->address_line_2)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->address_line_2.'</span><br/>';
                                $address_line_2 = $apply->contact->address_line_2;
                            endif;
                            if(isset($apply->contact->city) && !empty($apply->contact->city)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->city.'</span>, ';
                                $city = $apply->contact->city;
                            endif;
                            if(isset($apply->contact->state) && !empty($apply->contact->state)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->state.'</span>, <br/>';
                                $state = $apply->contact->state;
                            endif;
                            if(isset($apply->contact->post_code) && !empty($apply->contact->post_code)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->post_code.'</span>,<br/>';
                                $post_code = $apply->contact->post_code;
                            endif;
                            if(isset($apply->contact->country) && !empty($apply->contact->country)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->contact->country.'</span><br/>';
                                $country = $apply->contact->country;
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
                        <div class="apd-span-all">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            {{-- Inline lookup, the project's standard `.theAddressWrap` block.
                                 It was a modal only because the old lookup could not run twice on
                                 one page; `INTAddressLookUps()` loops every wrap, so both the
                                 applicant's and the next of kin's can be inline. --}}
                            <div id="applicanAddress" class="theAddressWrap apd-address">
                                <div class="apd-field apd-span-all">
                                    <label for="applicant_address_lookup" class="form-label">Address lookup</label>
                                    <input type="text" id="applicant_address_lookup" class="form-control theAddressLookup" placeholder="e.g. E15 4BZ" autocomplete="off">
                                    <span class="apd-hint">UK postcode, then pick the address &mdash; or type it below.</span>
                                </div>
                                <div class="apd-grid-2 apd-span-all">
                                    <div class="apd-field">
                                        <label for="applicant_address_line_1" class="form-label">Address line 1 <span class="text-danger">*</span></label>
                                        <input type="text" id="applicant_address_line_1" class="form-control address_line_1 applicant_address_line_1" name="applicant_address_line_1" value="{{ $apply->contact->address_line_1 ?? '' }}">
                                        <div class="acc__input-error error-applicant_address_line_1 text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="applicant_address_line_2" class="form-label">Address line 2</label>
                                        <input type="text" id="applicant_address_line_2" class="form-control address_line_2 applicant_address_line_2" name="applicant_address_line_2" value="{{ $apply->contact->address_line_2 ?? '' }}">
                                    </div>
                                    <div class="apd-field">
                                        <label for="applicant_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                                        <input type="text" id="applicant_address_city" class="form-control city applicant_address_city" name="applicant_address_city" value="{{ $apply->contact->city ?? '' }}">
                                        <div class="acc__input-error error-applicant_address_city text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="applicant_address_state" class="form-label">State / County</label>
                                        <input type="text" id="applicant_address_state" class="form-control state applicant_address_state" name="applicant_address_state" value="{{ $apply->contact->state ?? '' }}">
                                    </div>
                                    <div class="apd-field">
                                        <label for="applicant_address_postal_zip_code" class="form-label">Post code <span class="text-danger">*</span></label>
                                        <input type="text" id="applicant_address_postal_zip_code" class="form-control postal_code applicant_address_postal_zip_code" name="applicant_address_postal_zip_code" value="{{ $apply->contact->post_code ?? '' }}">
                                        <div class="acc__input-error error-applicant_address_postal_zip_code text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="applicant_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                        <input type="text" id="applicant_address_country" class="form-control country applicant_address_country" name="applicant_address_country" value="{{ $apply->contact->country ?? 'United Kingdom' }}">
                                        <div class="acc__input-error error-applicant_address_country text-danger mt-2"></div>
                                    </div>
                                </div>
                                <input type="hidden" class="latitude" name="applicant_address_latitude" value="">
                                <input type="hidden" class="longitude" name="applicant_address_longitude" value="">
                            </div>
                            <div class="acc__input-error error-applicant_address text-danger mt-2"></div>
                        </div>
                    </div>

                    <h3 class="apd-h3">Next of Kin</h3>
                    <div class="apd-grid-2">
                        <div class="">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input value="{{ isset($apply->kin->name) ? $apply->kin->name : '' }}" type="text" placeholder="Name" id="name" class="form-control" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="kins_relation_id" class="form-label">Relation <span class="text-danger">*</span></label>
                            <select id="kins_relation_id" class="applicationLccTom lcc-tom-select w-full" name="kins_relation_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($relations))
                                    @foreach($relations as $r)
                                        <option {{ isset($apply->kin->kins_relation_id) && $apply->kin->kins_relation_id == $r->id ? 'Selected' : '' }} value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-kins_relation_id text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="kins_mobile" class="form-label">Mobile <span class="text-danger">*</span></label>
                            <input value="{{ isset($apply->kin->mobile) ? $apply->kin->mobile : '' }}" type="text" placeholder="Mobile" id="kins_mobile" class="form-control" name="kins_mobile">
                            <div class="acc__input-error error-kins_mobile text-danger mt-2"></div>
                        </div>
                        <div class="">
                            <label for="kins_email" class="form-label">Email</label>
                            <input value="{{ isset($apply->kin->email) ? $apply->kin->email : '' }}" type="email" placeholder="Email" id="kins_email" class="form-control" name="kins_email">
                            <div class="acc__input-error error-kins_email text-danger mt-2"></div>
                        </div>
                        @php 
                            $address = $address_line_1 = $address_line_2 = $city = $state = $post_code = $country = '';
                            if(isset($apply->kin->address_line_1) && !empty($apply->kin->address_line_1)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->address_line_1.'</span><br/>';
                                $address_line_1 = $apply->kin->address_line_1;
                            endif;
                            if(isset($apply->kin->address_line_2) && !empty($apply->kin->address_line_2)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->address_line_2.'</span><br/>';
                                $address_line_2 = $apply->kin->address_line_2;
                            endif;
                            if(isset($apply->kin->city) && !empty($apply->kin->city)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->city.'</span>, ';
                                $city = $apply->kin->city;
                            endif;
                            if(isset($apply->kin->state) && !empty($apply->kin->state)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->state.'</span>, <br/>';
                                $state = $apply->kin->state;
                            endif;
                            if(isset($apply->kin->post_code) && !empty($apply->kin->post_code)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->post_code.'</span>,<br/>';
                                $post_code = $apply->kin->post_code;
                            endif;
                            if(isset($apply->kin->country) && !empty($apply->kin->country)):
                                $address .= '<span class="text-slate-600 font-medium">'.$apply->kin->country.'</span><br/>';
                                $country = $apply->kin->country;
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
                        <div class="apd-span-all">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            {{-- Inline lookup, the project's standard `.theAddressWrap` block.
                                 It was a modal only because the old lookup could not run twice on
                                 one page; `INTAddressLookUps()` loops every wrap, so both the
                                 applicant's and the next of kin's can be inline. --}}
                            <div id="kinAddress" class="theAddressWrap apd-address">
                                <div class="apd-field apd-span-all">
                                    <label for="kin_address_lookup" class="form-label">Address lookup</label>
                                    <input type="text" id="kin_address_lookup" class="form-control theAddressLookup" placeholder="e.g. E15 4BZ" autocomplete="off">
                                    <span class="apd-hint">UK postcode, then pick the address &mdash; or type it below.</span>
                                </div>
                                <div class="apd-grid-2 apd-span-all">
                                    <div class="apd-field">
                                        <label for="kin_address_line_1" class="form-label">Address line 1 <span class="text-danger">*</span></label>
                                        <input type="text" id="kin_address_line_1" class="form-control address_line_1 kin_address_line_1" name="kin_address_line_1" value="{{ $apply->kin->address_line_1 ?? '' }}">
                                        <div class="acc__input-error error-kin_address_line_1 text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="kin_address_line_2" class="form-label">Address line 2</label>
                                        <input type="text" id="kin_address_line_2" class="form-control address_line_2 kin_address_line_2" name="kin_address_line_2" value="{{ $apply->kin->address_line_2 ?? '' }}">
                                    </div>
                                    <div class="apd-field">
                                        <label for="kin_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                                        <input type="text" id="kin_address_city" class="form-control city kin_address_city" name="kin_address_city" value="{{ $apply->kin->city ?? '' }}">
                                        <div class="acc__input-error error-kin_address_city text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="kin_address_state" class="form-label">State / County</label>
                                        <input type="text" id="kin_address_state" class="form-control state kin_address_state" name="kin_address_state" value="{{ $apply->kin->state ?? '' }}">
                                    </div>
                                    <div class="apd-field">
                                        <label for="kin_address_postal_zip_code" class="form-label">Post code <span class="text-danger">*</span></label>
                                        <input type="text" id="kin_address_postal_zip_code" class="form-control postal_code kin_address_postal_zip_code" name="kin_address_postal_zip_code" value="{{ $apply->kin->post_code ?? '' }}">
                                        <div class="acc__input-error error-kin_address_postal_zip_code text-danger mt-2"></div>
                                    </div>
                                    <div class="apd-field">
                                        <label for="kin_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                                        <input type="text" id="kin_address_country" class="form-control country kin_address_country" name="kin_address_country" value="{{ $apply->kin->country ?? 'United Kingdom' }}">
                                        <div class="acc__input-error error-kin_address_country text-danger mt-2"></div>
                                    </div>
                                </div>
                                <input type="hidden" class="latitude" name="kin_address_latitude" value="">
                                <input type="hidden" class="longitude" name="kin_address_longitude" value="">
                            </div>
                            <div class="acc__input-error error-kin_address text-danger mt-2"></div>
                        </div>
                    </div>
                    </div><!-- /.apd-sheet -->
                    
                    <div class="apd-nav-row">
                        <button type="button" class="apd-btn apd-btn--ghost" disabled>&larr; Back</button>
                        <button type="button" class="apd-btn apd-btn--primary form-wizard-next-btn">
                            Save &amp; continue &rarr; 
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                        <input type="hidden" name="applicant_user_id" value="{{ isset($apply->applicant_user_id) && $apply->applicant_user_id > 0 ? $apply->applicant_user_id : $applicant->id }}"/>
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                    </div>
                </form>
            </fieldset>

            <fieldset class="wizard-fieldset">
                <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                    <div class="apd-step-head">
                        <span class="apd-eyebrow">Step 2 of 4</span>
                        <h1 class="apd-step-title">Course &amp; others</h1>
                    </div>
                    <div class="apd-sheet">
                    <h3 class="apd-h3">Course</h3>
                    <div class="apd-grid-2 apd-grid-2--fixed">
                        <div class="apd-field">
                            <label for="course_creation_id" class="form-label">Course &amp; semester <span class="text-danger">*</span> <i data-loading-icon="oval" data-color="black" class="courseLoading w-4 h-4 ml-2 hidden"></i></label>
                            <select id="course_creation_id" class="lcc-tom-select w-full" name="course_creation_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($courseCreationAvailibility))
                                    @foreach($courseCreationAvailibility as $ci)
                                        <option data-ew="{{ $ci->creation->has_evening_and_weekend }}" data-fees="{{ $ci->creation->fees }}" data-start="{{ $ci->course_start_date ? \Carbon\Carbon::createFromFormat('d-m-Y', $ci->course_start_date)->format('d M Y') : '' }}" {{ isset($apply->course->course_creation_id) && $apply->course->course_creation_id == $ci->creation->id ? 'selected' : ''}} value="{{ $ci->creation->id }}">{{ $ci->creation->course->name }} - {{ $ci->creation->semester->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="apd-hint">Only intakes currently open for admission are listed.</span>
                            <div class="acc__input-error error-course_creation_id text-danger mt-2"></div>
                        </div>
                        @php
                            $venues = (isset($apply->course->creation->venues) && !empty($apply->course->creation->venues) ? $apply->course->creation->venues : []);
                        @endphp
                        <div id="selectVenue" class="apd-field {{ isset($apply->course->course_creation_id) && $apply->course->course_creation_id > 0 ? '' : 'hidden' }}">
                            <label for="venue_id" class="form-label">Venue / campus <span class="text-danger">*</span></label>
                            <select id="venue_id" class="lcc-tom-select w-full" name="venue_id">
                                <option value="" selected>Please Select</option>
                                @if(!empty($venues))
                                    @foreach($venues as $vn)
                                        @if($vn->pivot->deleted_at==null)
                                            <option {{ isset($apply->course->venue) && !empty($apply->course->venue) && ($apply->course->venue->id == $vn->id) ? 'selected' : ''}} value="{{ $vn->id }}">{{ $vn->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                        </div>
                    </div>

                    @php
                        $apdFees = $apply->course->creation->fees ?? null;
                        $apdStart = null;
                        if (isset($apply->course->course_creation_id) && !empty($courseCreationAvailibility)):
                            foreach ($courseCreationAvailibility as $ci):
                                if ($ci->creation->id == $apply->course->course_creation_id && $ci->course_start_date):
                                    $apdStart = \Carbon\Carbon::createFromFormat('d-m-Y', $ci->course_start_date)->format('d M Y');
                                endif;
                            endforeach;
                        endif;
                    @endphp
                    <dl class="apd-kv apd-course-meta" style="display: {{ $apdFees || $apdStart ? 'grid' : 'none' }};">
                        <dt>Tuition fee</dt><dd><span class="courseFee">{{ $apdFees ? '£'.number_format($apdFees, 0) : '—' }}</span> per year</dd>
                        <dt>Start date</dt><dd class="courseStart">{{ $apdStart ?: '—' }}</dd>
                    </dl>

                    <div class="apd-sub eveningWeekendWrap" style="display: {{ (isset($apply->creation_venue_status) && $apply->creation_venue_status ? 'grid' : 'none') }};">
                        <label class="apd-opt chk" for="full_time">
                            <input {{ (isset($apply->course->full_time) && $apply->course->full_time == 1) ? 'checked' : '' }} id="full_time" class="sr" name="full_time" value="1" type="checkbox">
                            <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                            <span>Are you applying for evening and weekend classes (Full Time)?
                                <span class="apd-hint ewHint" style="display:block">This campus runs both weekday and evening/weekend timetables.</span></span>
                        </label>
                        <div class="acc__input-error error-full_time text-danger mt-2"></div>
                    </div>

                    <h3 class="apd-h3">Funding</h3>
                    <div class="apd-field">
                        <label for="student_loan" class="form-label">How are you funding your education at London Churchill College? <span class="text-danger">*</span></label>
                        <select id="student_loan" class="lcc-tom-select w-full" name="student_loan">
                            <option value="">Please Select</option>
                            <option {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Independently/Private' ? 'selected' : ''}} value="Independently/Private">Independently/Private</option>
                            <option {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Funding Body' ? 'selected' : ''}} value="Funding Body">Funding Body</option>
                            <option {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Sponsor' ? 'selected' : ''}} value="Sponsor">Sponsor</option>
                            <option {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Student Loan' ? 'selected' : ''}} value="Student Loan">Student Loan</option>
                            <option {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Others' ? 'selected' : ''}} value="Others">Other</option>
                        </select>
                        <div class="acc__input-error error-student_loan text-danger mt-2"></div>
                    </div>

                    @php $apdSL = isset($apply->course->student_loan) && $apply->course->student_loan == 'Student Loan'; @endphp
                    <div class="apd-sub studentLoanPanel" style="display: {{ $apdSL ? 'grid' : 'none' }};">
                        <p style="margin:0;font-size:13px;color:var(--muted)">Because you chose Student Loan, please answer every question below.</p>

                        <div class="apd-field studentLoanEnglandFunding" style="display: {{ $apdSL ? 'grid' : 'none' }};">
                            <label class="form-label">1. Have you applied to Student Finance England for this course? <span class="text-danger">*</span></label>
                                        <x-applicant.switch name="student_finance_england" id="student_finance_england" :checked="isset($apply->course->student_finance_england) && $apply->course->student_finance_england == 1" />
                            <div class="acc__input-error error-student_finance_england text-danger mt-2"></div>
                        </div>

                        <div class="apd-field studentLoanFundReceipt" style="display: {{ $apdSL && isset($apply->course->student_finance_england) && $apply->course->student_finance_england == 1 ? 'grid' : 'none' }};">
                            <label class="form-label">2. Have you already received funds from Student Finance England? <span class="text-danger">*</span></label>
                                        <x-applicant.switch name="fund_receipt" id="fund_receipt" :checked="isset($apply->course->fund_receipt) && $apply->course->fund_receipt == 1" />
                            <div class="acc__input-error error-fund_receipt text-danger mt-2"></div>
                        </div>

                        <div class="apd-field studentLoanApplied" style="display: {{ $apdSL ? 'grid' : 'none' }};">
                            <label class="form-label">3. Have you ever applied for or received a loan from the Student Loans Company (SLC) or any government body for a different course or institution? <span class="text-danger">*</span></label>
                                        <x-applicant.switch name="applied_received_fund" id="applied_received_fund" :checked="isset($apply->course->applied_received_fund) && $apply->course->applied_received_fund == 1" />
                            <div class="acc__input-error error-applied_received_fund text-danger mt-2"></div>
                        </div>
                    </div>

                    <div class="apd-field otherFundings" style="display: {{ isset($apply->course->student_loan) && $apply->course->student_loan == 'Others' ? 'grid' : 'none' }};">
                        <label for="other_funding" class="form-label">Please type other funding <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Other funding" value="{{ isset($apply->course->other_funding) && !empty($apply->course->other_funding) ? $apply->course->other_funding : '' }}" id="other_funding" class="form-control other_funding" name="other_funding">
                        <div class="acc__input-error error-other_funding text-danger mt-2"></div>
                    </div>

                    <h3 class="apd-h3">Academic qualifications</h3>
                    <div class="apd-field">
                        <label for="is_edication_qualification" class="form-label">Do you have any formal academic qualification? <span class="text-danger">*</span></label>
                        @php $apdHasQual = isset($apply->other->is_edication_qualification) && $apply->other->is_edication_qualification == 1; @endphp
                        <x-applicant.switch name="is_edication_qualification" id="is_edication_qualification" :checked="$apdHasQual" off="No (mature entry)" />
                        <div class="acc__input-error error-is_edication_qualification text-danger mt-2"></div>
                    </div>

                    {{-- The table is listed above the button, and only when there is
                         something to list, as the design has it. `educationQualTable`
                         fills it from the same endpoint the grid used. --}}
                    <div class="qualificationAdder" style="display:{{ $apdHasQual ? 'block' : 'none' }};">
                        <div class="educationQualificationTableWrap apd-tablewrap">
                            <div id="educationQualTable" data-applicant="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}" class="{{ $apdHasQual ? 'activeTable' : '' }}"></div>
                        </div>
                        <div class="apd-qual-actions">
                            <button data-tw-toggle="modal" data-tw-target="#addQualificationModal" type="button" class="apd-btn apd-btn--ghost apd-btn--sm">
                                + Add qualification
                            </button>
                        </div>
                    </div>
                    @php 
                        if(!isset($apply->other->employment_status) || ($apply->other->employment_status == 'Unemployed' || $apply->other->employment_status == 'Contractor' || $apply->other->employment_status == 'Consultant' || $apply->other->employment_status == 'Office Holder')):
                            $emptStatus = false;
                        else:
                            $emptStatus = true;
                        endif;
                    @endphp
                    <h3 class="apd-h3">Employment</h3>
                    <div class="apd-field">
                        <label for="employment_status" class="form-label">What is your current employment status? <span class="text-danger">*</span></label>
                        <select id="employment_status" class="lcc-tom-select w-full" name="employment_status">
                            <option value="">Please Select</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Part Time' ? 'Selected' : '' }} value="Part Time">Part Time</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Fixed Term' ? 'Selected' : '' }} value="Fixed Term">Fixed Term</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Contractor' ? 'Selected' : '' }} value="Contractor">Contractor</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Zero Hour' ? 'Selected' : '' }} value="Zero Hour">Zero Hour</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Seasonal' ? 'Selected' : '' }} value="Seasonal">Seasonal</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Agency or Temp' ? 'Selected' : '' }} value="Agency or Temp">Agency or Temp</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Consultant' ? 'Selected' : '' }} value="Consultant">Consultant</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Office Holder' ? 'Selected' : '' }} value="Office Holder">Office Holder</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Volunteer' ? 'Selected' : '' }} value="Volunteer">Volunteer</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Unemployed' ? 'Selected' : '' }} value="Unemployed">Unemployed</option>
                            <option {{ isset($apply->other->employment_status) && $apply->other->employment_status == 'Full Time' ? 'Selected' : '' }} value="Full Time">Full Time</option>
                        </select>
                        <div class="acc__input-error error-employment_status text-danger mt-2"></div>
                    </div>

                    {{-- Table above the button, and only when there is something to list. --}}
                    <div class="employmentHistoryAdder" style="display:{{ $emptStatus ? 'block' : 'none' }};">
                        <div class="educationEmploymentTableWrap apd-tablewrap">
                            <div id="employmentHistoryTable" data-applicant="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}" class="{{ $emptStatus ? 'activeTable' : '' }}"></div>
                        </div>
                        <div class="apd-qual-actions">
                            <button data-tw-toggle="modal" data-tw-target="#addEmployementHistoryModal" type="button" class="apd-btn apd-btn--ghost apd-btn--sm">
                                + Add employment history
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="apd-h3">Referral @if(auth('agent')->user()) <span class="text-danger">*</span> @endif</h3>
                    @php
                        $apdRefCode  = isset($apply->referral_code) ? $apply->referral_code : '';
                        $apdRefOk    = $apdRefCode !== '' && isset($apply->is_referral_varified) && $apply->is_referral_varified == 1;
                        $apdRefOwner = $apdRefOk ? (optional(\App\Models\ReferralCode::where('code', $apdRefCode)->first())->owner_name ?? '') : '';
                    @endphp
                    <div class="apd-field">
                        <label for="referral_code" class="form-label">If you were referred{{ auth('agent')->user() ? ' by an agent or sub-agent' : '' }}, enter the referral code</label>
                        {{-- One button: "Verify code" until the code checks out, "Change" after.
                             The three controls stay siblings in `.validationGroup` for the
                             script. A verified code is painted here on first load, so the
                             state survives a refresh without a round trip. --}}
                        <div class="validationGroup apd-referral">
                            <input value="{{ $apdRefCode }}" data-org="{{ $apdRefCode }}" id="referral_code" name="referral_code" type="text" class="form-control apd-ctl uppercase" placeholder="e.g. LCC-AG-0204" autocomplete="off" {{ $apdRefOk ? 'readonly' : '' }}>
                            <button id="varifiedReferral" type="button" class="apd-btn apd-btn--ghost {{ $apdRefOk ? 'verified' : '' }}"
                                data-applicant-id="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}" {{ !$apdRefOk && $apdRefCode === '' ? 'disabled' : '' }}>{{ $apdRefOk ? 'Change' : 'Verify code' }}</button>
                            <input type="hidden" class="is_referral_varified" name="is_referral_varified" value="{{ $apdRefOk ? 1 : 0 }}" data-org="{{ $apdRefOk ? 1 : 0 }}" />
                        </div>
                        <span class="apd-hint">From an education agent or an existing student. Optional.</span>
                        <div class="apd-referral-notice {{ $apdRefOk ? 'apd-notice apd-notice--ok' : '' }}" role="status" aria-live="polite">@if($apdRefOk)Verified &mdash; {{ $apdRefOwner }}. Your application will be linked to this referrer.@endif</div>
                        <div class="acc__input-error error-referral_code apd-err text-danger"></div>
                    </div>
                    </div><!-- /.apd-sheet -->
                    
                    
                    <div class="apd-nav-row">
                        <button type="button" class="apd-btn apd-btn--ghost form-wizard-previous-btn">&larr; Back</button>
                        <button type="button" class="apd-btn apd-btn--primary form-wizard-next-btn">
                            Save &amp; continue &rarr; 
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                    </div>
                </form>
            </fieldset>

            <fieldset class="wizard-fieldset">
                <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                    <div class="apd-step-head">
                        <span class="apd-eyebrow">Step 3 of 4</span>
                        <h1 class="apd-step-title">Residency &amp; convictions</h1>
                    </div>
                    <div class="apd-sheet">
                    {{-- the component carries its own two section headings --}}
                    @include('components.applicant.residency-status', ['residencyStatus' => isset($apply->residencyStatus) ? $apply->residencyStatus : null, 'applicantId' => isset($apply->id) ? $apply->id : 0,'residencyStatuses' => $residencyStatuses])
                    </div><!-- /.apd-sheet -->
                    
                    <div class="apd-nav-row">
                        <button type="button" class="apd-btn apd-btn--ghost form-wizard-previous-btn">&larr; Back</button>
                        <button type="button" class="apd-btn apd-btn--primary form-wizard-next-btn">
                            Save &amp; continue &rarr; 
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                    </div>
                </form>
            </fieldset>    
            <fieldset class="wizard-fieldset wizard-last-step">
                <form method="post" action="#" id="appicantFormStep_4" class="wizard-step-form">
                    <div class="apd-step-head">
                        <span class="apd-eyebrow">Step 4 of 4</span>
                        <h1 class="apd-step-title">Review &amp; apply</h1>
                    </div>
                    <div class="apd-sheet">
                    {{-- Loader until the review arrives, then the read-back and the declaration. --}}
                    <div class="reviewLoader apd-review__loader">
                        <svg width="36" height="36" viewBox="0 0 45 45" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true">
                            <g fill="none" fill-rule="evenodd" transform="translate(1 1)" stroke-width="3">
                                <circle cx="22" cy="22" r="6" stroke-opacity="0">
                                    <animate attributeName="r" begin="1.5s" dur="3s" values="6;22" calcMode="linear" repeatCount="indefinite"></animate>
                                    <animate attributeName="stroke-opacity" begin="1.5s" dur="3s" values="1;0" calcMode="linear" repeatCount="indefinite"></animate>
                                </circle>
                                <circle cx="22" cy="22" r="8">
                                    <animate attributeName="r" begin="0s" dur="1.5s" values="6;1;2;3;4;5;6" calcMode="linear" repeatCount="indefinite"></animate>
                                </circle>
                            </g>
                        </svg>
                        <span>Preparing your review&hellip;</span>
                    </div>

                    <div class="reviewContentWrap apd-review__wrap" data-review-id="0" style="display: none;">
                        <div class="apd-notice">
                            Check everything below. Use <b style="display:inline">Back</b> or a completed step in the rail to change an answer.
                            Once you apply, the application is locked and goes to the admissions team.
                        </div>

                        <div class="reviewContent"></div>

                        <div class="apd-sub apd-declaration">
                            <h4 class="apd-sub__title">Declaration</h4>
                            <p class="apd-prose">
                                I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my
                                knowledge. It is my responsibility to stay informed about the terms and conditions as well as the policies
                                of the college, and I commit to comply with them. I have thoroughly reviewed the college's terms and
                                conditions and student privacy policy and pledge to adhere to them throughout my entire course of study.
                            </p>
                            <label class="apd-opt chk">
                                <input class="sr" id="is_applicant_agree" type="checkbox" name="is_agree" value="1">
                                <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                <span>I have read and agree to the declaration above.</span>
                            </label>
                            <p class="apd-note">
                                On submission you will receive an SMS to {{ $apply->contact->mobile ?? 'your mobile' }} and an email to
                                {{ $applicant->email ?? 'your email address' }} with your reference number.
                            </p>
                        </div>
                    </div>
                    </div><!-- /.apd-sheet -->

                    <div class="apd-nav-row">
                        <button type="button" class="apd-btn apd-btn--ghost form-wizard-previous-btn">&larr; Back</button>
                        <button type="button" disabled class="apd-btn apd-btn--red form-wizard-next-btn">
                            Apply now
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                        @if(isset(auth('agent')->user()->id))
                            <input type="hidden" name="url" value="{{ route('agent.dashboard') }}"/>
                        @else
                            <input type="hidden" name="url" value="{{ route('applicant.dashboard') }}"/>
                        @endif
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Qualification Modal -->
    @include('components.applicant.modals.qualification-modal')
    <!-- END: Qualification Modal -->
    <!-- BEGIN: Employement History Modal -->
    @include('components.applicant.modals.employment-history-modal')
    <!-- END: Employement History Modal -->
    <!-- BEGIN: Address Modal -->
    <!-- END: Address Modal -->
    <!-- BEGIN: Notification Modal -->
    @include('components.applicant.modals.notification-modal')
    <!-- END: Notification Modal -->
@endsection
@if(isset(\Auth::guard('agent')->user()->id))
    @section('script')
        @vite('resources/js/agent-application.js')
    @endsection
@else
    @section('script')
        @vite('resources/js/application.js')
    @endsection
@endif