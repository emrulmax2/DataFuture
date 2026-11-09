{{--
    Modals for the applicant details page on the admission portal shell.

    Markup follows the approved design (modal / dialog / field / switch /
    check-card / radio-pill pieces). Every id, name and hook class that
    admission.js, admission-global.js and admission-vue.js bind is kept:
    form ids, field names, .acc__input-error / .error-<field>, .adm-btn-loader,
    .adm-check-card(__meta), .disabilityItems / .disabilityAllowance,
    .studentLoan* / .otherFundings / .eveningWeekendWrap / .courseLoading,
    .validationGroup / #sendMobileVerifiCode / .verifyCodeGroup / #verifyMobile,
    .theAddressWrap / .theAddressLookup / data-address-source,
    .criminalConvictionDetailsWrap, confirm dialogs' .confModTitle/.confModDesc/
    .agreeWith/.disAgreeWith, #statusConfirmModal's .offerAcceptedErrorArea
    fields, #rejected_reason, #statusAgreement, #progressBarModal (Vue root
    #app), #addApplicantPhotoForm (Dropzone), #sendOfferAcceptanceForm.

    Buttons deliberately do not carry Midone's .btn class, so
    admissionDecorateModalButtons() leaves them alone.
--}}
@php
    $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
    $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
    $adpCheck = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $adpX = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    $adpSwitch = function ($id, $name, $checked, $extraClass = '') use ($adpCheck, $adpX) {
        return '<label class="adp-sw" for="'.$id.'"><input id="'.$id.'" class="adp-sw__input '.$extraClass.'" name="'.$name.'" value="1" type="checkbox" '.($checked ? 'checked' : '').'><span class="adp-sw__dot"><span class="adp-sw__on">'.$adpCheck.'</span><span class="adp-sw__off">'.$adpX.'</span></span><span class="adp-sw__yes">Yes</span><span class="adp-sw__no">No</span></label>';
    };
    $adpStatusId = (int) ($applicant->status_id ?? 0);
    $adpStatusName = $applicant->status->name ?? '—';
    $adpStatusName = $adpStatusName === 'Awating Decision' ? 'Awaiting Decision' : $adpStatusName;
@endphp

@include('pages.students.admission.partials.portal-record-modals')

{{-- ============================ Edit personal details ============================ --}}
<div id="editAdmissionPersonalDetailsModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editAdmissionPersonalDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Edit personal details</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="title_id">Title <span class="adp-req">*</span></label>
                            <select id="title_id" class="addmissionLccTom lcc-tom-select" name="title_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($titles))
                                    @foreach($titles as $t)
                                        <option {{ isset($applicant->title_id) && $applicant->title_id == $t->id ? 'selected' : '' }} value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-title_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="first_name">First name(s) <span class="adp-req">*</span></label>
                            <input type="text" value="{{ $applicant->first_name ?? '' }}" placeholder="First name" id="first_name" class="adp-ctl" name="first_name">
                            <div class="acc__input-error error-first_name adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="last_name">Last name <span class="adp-req">*</span></label>
                            <input type="text" value="{{ $applicant->last_name ?? '' }}" placeholder="Last name" id="last_name" class="adp-ctl" name="last_name">
                            <div class="acc__input-error error-last_name adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="date_of_birth">Date of birth <span class="adp-req">*</span></label>
                            <input type="text" value="{{ $applicant->date_of_birth ?? '' }}" placeholder="DD-MM-YYYY" id="date_of_birth" class="adp-ctl admissionDatepicker" name="date_of_birth" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                            <div class="acc__input-error error-date_of_birth adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="sex_identifier_id">Sex identifier / gender <span class="adp-req">*</span></label>
                            <select id="sex_identifier_id" class="addmissionLccTom lcc-tom-select" name="sex_identifier_id">
                                <option value="" selected>Please select</option>
                                @if($sexid->count() > 0)
                                    @foreach($sexid as $si)
                                        <option {{ isset($applicant->sex_identifier_id) && $applicant->sex_identifier_id == $si->id ? 'selected' : '' }} value="{{ $si->id }}">{{ $si->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-sex_identifier_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="nationality_id">Nationality <span class="adp-req">*</span></label>
                            <select id="nationality_id" class="addmissionLccTom lcc-tom-select" name="nationality_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($applicant->nationality_id) && $applicant->nationality_id == $n->id ? 'selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-nationality_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="country_id">Country of birth <span class="adp-req">*</span></label>
                            <select id="country_id" class="addmissionLccTom lcc-tom-select" name="country_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($country))
                                    @foreach($country as $n)
                                        <option {{ isset($applicant->country_id) && $applicant->country_id == $n->id ? 'selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-country_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="ethnicity_id">Ethnicity <span class="adp-req">*</span></label>
                            <select id="ethnicity_id" class="addmissionLccTom lcc-tom-select" name="ethnicity_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($ethnicity))
                                    @foreach($ethnicity as $n)
                                        @if($n->active == 1)
                                            <option {{ isset($applicant->other->ethnicity_id) && $applicant->other->ethnicity_id == $n->id ? 'selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-ethnicity_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="care_leaver_id">Care leaver <span class="adp-req">*</span></label>
                            <select id="care_leaver_id" class="addmissionLccTom lcc-tom-select" name="care_leaver_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($careleaver))
                                    @foreach($careleaver as $n)
                                        @if($n->active == 1)
                                            <option {{ isset($applicant->other->care_leaver_id) && $applicant->other->care_leaver_id == $n->id ? 'selected' : '' }} value="{{ $n->id }}">{{ $n->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <span class="adp-note">Used for support and HESA reporting only.</span>
                            <div class="acc__input-error error-care_leaver_id adp-err"></div>
                        </div>
                        @php
                            $ids = [];
                            if (!empty($applicant->disability)) { foreach ($applicant->disability as $dis) { $ids[] = $dis->disabilitiy_id; } }
                            $adpHasDisability = isset($applicant->other->disability_status) && $applicant->other->disability_status == 1;
                        @endphp
                        <div class="adp-field">
                            <label for="disability_status">Do you have any disabilities? <span class="adp-req">*</span></label>
                            <div>{!! $adpSwitch('disability_status', 'disability_status', $adpHasDisability) !!}</div>
                        </div>
                        {{-- admission.js fades .disabilityItems in/out on #disability_status and shows
                             .disabilityAllowance once one of the list's checkboxes is ticked; the
                             allowance switch is a sibling so it is not counted as one of them. --}}
                        <div class="adp-span-2 adp-sub adp-disability-panel">
                            <div class="adp-field disabilityItems" style="display: {{ $adpHasDisability ? 'grid' : 'none' }};">
                                <label for="disability_id">Disabilities <span class="adp-req">*</span></label>
                                <div class="adp-check-grid">
                                    @if(!empty($disability))
                                        @foreach($disability as $d)
                                            <label class="adp-opt adp-opt--chk" for="disabilty_id_{{ $d->id }}">
                                                <input {{ (in_array($d->id, $ids) ? 'checked' : '') }} id="disabilty_id_{{ $d->id }}" name="disability_id[]" class="adp-opt__input disability_ids" type="checkbox" value="{{ $d->id }}">
                                                <span class="adp-opt__box">{!! $adpCheck !!}</span>
                                                <span>{{ $d->name }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <span class="adp-note">Select all that apply.</span>
                                <div class="acc__input-error error-disability_id adp-err"></div>
                            </div>
                            <div class="adp-field disabilityAllowance" style="display: {{ !empty($ids) && $adpHasDisability ? 'grid' : 'none' }};">
                                <label for="disabilty_allowance">Do you claim Disability Allowance?</label>
                                <div>{!! $adpSwitch('disabilty_allowance', 'disabilty_allowance', isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1) !!}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="savePD" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" value="{{ $applicant->id }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Proof of ID ============================ --}}
<div id="editAdmissionProofModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionProofForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Proof of ID &amp; fee eligibility</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field adp-span-2">
                            <label for="proof_type">Proof of ID type</label>
                            <select id="proof_type" class="addmissionLccTom lcc-tom-select" name="proof_type">
                                <option value="">Please select</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'passport' ? 'selected' : '' }} value="passport">Passport</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'birth' ? 'selected' : '' }} value="birth">Birth Certificate</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'driving' ? 'selected' : '' }} value="driving">Driving Licence</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'nid' ? 'selected' : '' }} value="nid">National ID Card</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'respermit' ? 'selected' : '' }} value="respermit">Residence Permit No</option>
                            </select>
                            <div class="acc__input-error error-proof_type adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="proof_id">Document number</label>
                            <input type="text" value="{{ $applicant->proof->proof_id ?? '' }}" placeholder="e.g. 512345678" id="proof_id" class="adp-ctl" name="proof_id">
                            <div class="acc__input-error error-proof_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="proof_expiredate">Expiry date</label>
                            <input type="text" value="{{ $applicant->proof->proof_expiredate ?? '' }}" placeholder="DD-MM-YYYY" id="proof_expiredate" class="adp-ctl datepicker" data-format="DD-MM-YYYY" data-single-mode="true" data-allow-future name="proof_expiredate" autocomplete="off">
                            <div class="acc__input-error error-proof_expiredate adp-err"></div>
                        </div>
                        {{-- Fee eligibility: also needed before Accepted / Offer Placed (AdmissionStatusFlow::missingForOffer). --}}
                        <div class="adp-field adp-span-2">
                            <label for="proof_fee_eligibility_id">Fee eligibility</label>
                            <select id="proof_fee_eligibility_id" class="addmissionLccTom lcc-tom-select" name="fee_eligibility_id">
                                <option value="">Please select</option>
                                @foreach(($feeelegibility ?? []) as $adpFee)
                                    <option value="{{ $adpFee->id }}" {{ (int) ($applicant->feeeligibility->fee_eligibility_id ?? 0) === (int) $adpFee->id ? 'selected' : '' }}>{{ $adpFee->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-fee_eligibility_id adp-err"></div>
                        </div>
                    </div>
                    <p class="adp-copy">Required before the application can be accepted (accepting places the offer and sends the offer acceptance form).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="saveProof" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" value="{{ $applicant->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ isset($applicant->proof->id) && $applicant->proof->id > 0 ? $applicant->proof->id : 0 }}" name="applicant_proof_of_id"/>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Edit contact details ============================ --}}
@php
    $address_line_1 = $applicant->contact->address_line_1 ?? '';
    $address_line_2 = $applicant->contact->address_line_2 ?? '';
    $city = $applicant->contact->city ?? '';
    $state = $applicant->contact->state ?? '';
    $post_code = $applicant->contact->post_code ?? '';
    $country = $applicant->contact->country ?? '';
    $adpMobileVerifiedNow = isset($applicant->contact->mobile_verification) && $applicant->contact->mobile_verification == 1;
@endphp
<div id="editAdmissionContactDetailsModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionContactDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Edit contact details</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="email">Email address <span class="adp-req">*</span></label>
                            <input value="{{ $applicant->users->email ?? '' }}" type="text" placeholder="Email" id="email" class="adp-ctl" name="email">
                            <div class="acc__input-error error-email adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="phone">Home phone</label>
                            <input value="{{ $applicant->contact->home ?? '' }}" type="text" placeholder="Home phone" id="phone" class="adp-ctl phoneMask" name="phone">
                            <div class="acc__input-error error-phone adp-err"></div>
                        </div>
                        {{-- Mobile + SMS verification. admission.js drives the state through the
                             hidden #sendMobileVerifiCode button (its classes/text), the hidden
                             .mobile_verification input and .verifyCodeGroup; admission-show.js
                             paints the design's Send code / Change / Verified states from that. --}}
                        <div class="adp-field adp-span-2 adp-mobile" data-adp-mobile>
                            <label for="mobile">Mobile phone <span class="adp-req">*</span></label>
                            <div class="validationGroup adp-vgroup adp-vgroup--mobile">
                                <input value="{{ $applicant->contact->mobile ?? '' }}" data-org="{{ $applicant->contact->mobile ?? '' }}" id="mobile" name="mobile" type="text" class="adp-ctl phoneMask" placeholder="e.g. 07700 900123" autocomplete="off" {{ $adpMobileVerifiedNow && !empty($applicant->contact->mobile) ? 'readonly' : '' }}>
                                <button type="button" id="sendMobileVerifiCode" data-applicant-id="{{ isset($applicant->id) && $applicant->id > 0 ? $applicant->id : 0 }}" class="{{ $adpMobileVerifiedNow ? 'btn-primary verified' : 'btn-danger' }}" hidden aria-hidden="true" tabindex="-1">
                                    @if($adpMobileVerifiedNow)
                                        <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> Verified
                                    @else
                                        <i data-lucide="link" class="w-4 h-4 mr-1"></i> Send Code
                                    @endif
                                </button>
                                <button type="button" class="adp-btn adp-btn--ghost" data-adp-mobile-action>Send code</button>
                                <input type="hidden" class="mobile_verification" name="mobile_verification" value="{{ $adpMobileVerifiedNow ? 1 : 0 }}" data-org="{{ $adpMobileVerifiedNow ? 1 : 0 }}" />
                            </div>
                            <span class="adp-note">Verification codes are sent here by SMS. A changed number needs verifying again. <button type="button" class="adp-btn-link" data-adp-mobile-change hidden>Change number</button></span>
                            <div class="adp-notice adp-notice--ok" data-adp-mobile-ok hidden></div>
                            <div class="acc__input-error error-mobile adp-err"></div>
                            <div class="acc__input-error error-mobile_verification adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2 verifyCodeGroup" style="display: none;">
                            <label for="verification_code">Verification code <span class="adp-req">*</span></label>
                            <div class="validationGroup adp-vgroup adp-vgroup--code">
                                <input value="" id="verification_code" name="verification_code" type="text" class="adp-ctl" placeholder="XXXXXX" autocomplete="one-time-code" inputmode="numeric">
                                <button type="button" id="verifyMobile" data-applicant-id="{{ isset($applicant->id) && $applicant->id > 0 ? $applicant->id : 0 }}" class="adp-btn adp-btn--primary">Verify</button>
                            </div>
                            <span class="adp-note">Enter the 6-digit code from the SMS.</span>
                            <div class="acc__input-error error-mobile_verification_error adp-notice-slot"></div>
                        </div>
                        <div class="adp-span-2 adp-subhead">Address</div>
                        <div class="adp-span-2">
                            <div id="applicantAddressInline" class="adp-grid-2 theAddressWrap">
                                <div class="adp-field adp-span-2">
                                    <label for="applicant_address_lookup">Address lookup</label>
                                    <input type="text" placeholder="Start typing the address…" id="applicant_address_lookup" class="adp-ctl theAddressLookup" name="applicant_address_lookup" autocomplete="off">
                                    <span class="adp-note">Pick a suggestion to fill the fields below, or type them in.</span>
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="applicant_address_line_1">Address line 1 <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $address_line_1 }}" placeholder="Address line 1" id="applicant_address_line_1" class="address_line_1 adp-ctl required" name="applicant_address_line_1">
                                    <input type="hidden" value="{{ $address_line_1 }}" name="applicant_address" data-address-source="applicant_address_line_1">
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="applicant_address_line_2">Address line 2</label>
                                    <input type="text" value="{{ $address_line_2 }}" placeholder="Address line 2 (optional)" id="applicant_address_line_2" class="address_line_2 adp-ctl" name="applicant_address_line_2">
                                </div>
                                <div class="adp-field">
                                    <label for="applicant_address_city">City / Town <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $city }}" placeholder="City / Town" id="applicant_address_city" class="city adp-ctl required" name="applicant_address_city">
                                </div>
                                <div class="adp-field">
                                    <label for="applicant_address_state">State / County</label>
                                    <input type="text" value="{{ $state }}" placeholder="State / County" id="applicant_address_state" class="state adp-ctl" name="applicant_address_state">
                                </div>
                                <div class="adp-field">
                                    <label for="applicant_address_postal_zip_code">Post code <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $post_code }}" placeholder="Post code" id="applicant_address_postal_zip_code" class="postal_code adp-ctl required" name="applicant_address_postal_zip_code">
                                </div>
                                <div class="adp-field">
                                    <label for="applicant_address_country">Country <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $country }}" placeholder="Country" id="applicant_address_country" class="country adp-ctl required" name="applicant_address_country">
                                </div>
                            </div>
                            <div class="acc__input-error error-applicant_address adp-err"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="saveCD" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" value="{{ $applicant->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ $applicant->contact->id ?? 0 }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Edit next of kin ============================ --}}
@php
    $address_line_1 = $applicant->kin->address_line_1 ?? '';
    $address_line_2 = $applicant->kin->address_line_2 ?? '';
    $city = $applicant->kin->city ?? '';
    $state = $applicant->kin->state ?? '';
    $post_code = $applicant->kin->post_code ?? '';
    $country = $applicant->kin->country ?? '';
@endphp
<div id="editAdmissionKinDetailsModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionKinDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Edit next of kin</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="name">Name <span class="adp-req">*</span></label>
                            <input value="{{ $applicant->kin->name ?? '' }}" type="text" placeholder="Name" id="name" class="adp-ctl" name="name">
                            <div class="acc__input-error error-name adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="kins_relation_id">Relation <span class="adp-req">*</span></label>
                            <select id="kins_relation_id" class="addmissionLccTom lcc-tom-select" name="kins_relation_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($relations))
                                    @foreach($relations as $r)
                                        <option {{ isset($applicant->kin->kins_relation_id) && $applicant->kin->kins_relation_id == $r->id ? 'selected' : '' }} value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-kins_relation_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="kins_mobile">Mobile <span class="adp-req">*</span></label>
                            <input value="{{ $applicant->kin->mobile ?? '' }}" type="text" placeholder="Mobile" id="kins_mobile" class="adp-ctl phoneMask" name="kins_mobile">
                            <div class="acc__input-error error-kins_mobile adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="kins_email">Email</label>
                            <input value="{{ $applicant->kin->email ?? '' }}" type="email" placeholder="Email" id="kins_email" class="adp-ctl" name="kins_email">
                            <div class="acc__input-error error-kins_email adp-err"></div>
                        </div>
                        <div class="adp-span-2 adp-subhead">Address</div>
                        <div class="adp-span-2">
                            <div id="kinAddressInline" class="adp-grid-2 theAddressWrap">
                                <div class="adp-field adp-span-2">
                                    <label for="kin_address_lookup">Address lookup</label>
                                    <input type="text" placeholder="Start typing the address…" id="kin_address_lookup" class="adp-ctl theAddressLookup" name="kin_address_lookup" autocomplete="off">
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="kin_address_line_1">Address line 1 <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $address_line_1 }}" placeholder="Address line 1" id="kin_address_line_1" class="address_line_1 adp-ctl required" name="kin_address_line_1">
                                    <input type="hidden" value="{{ $address_line_1 }}" name="kin_address" data-address-source="kin_address_line_1">
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="kin_address_line_2">Address line 2</label>
                                    <input type="text" value="{{ $address_line_2 }}" placeholder="Address line 2 (optional)" id="kin_address_line_2" class="address_line_2 adp-ctl" name="kin_address_line_2">
                                </div>
                                <div class="adp-field">
                                    <label for="kin_address_city">City / Town <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $city }}" placeholder="City / Town" id="kin_address_city" class="city adp-ctl required" name="kin_address_city">
                                </div>
                                <div class="adp-field">
                                    <label for="kin_address_state">State / County</label>
                                    <input type="text" value="{{ $state }}" placeholder="State / County" id="kin_address_state" class="state adp-ctl" name="kin_address_state">
                                </div>
                                <div class="adp-field">
                                    <label for="kin_address_postal_zip_code">Post code <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $post_code }}" placeholder="Post code" id="kin_address_postal_zip_code" class="postal_code adp-ctl required" name="kin_address_postal_zip_code">
                                </div>
                                <div class="adp-field">
                                    <label for="kin_address_country">Country <span class="adp-req">*</span></label>
                                    <input type="text" value="{{ $country }}" placeholder="Country" id="kin_address_country" class="country adp-ctl required" name="kin_address_country">
                                </div>
                            </div>
                            <div class="acc__input-error error-kin_address adp-err"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="saveNOK" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" value="{{ $applicant->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ $applicant->kin->id ?? 0 }}" name="id"/>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Edit proposed course ============================ --}}
@php
    $venues = (isset($applicant->course->creation->venues) && !empty($applicant->course->creation->venues) ? $applicant->course->creation->venues : []);
    $adpLoan = $applicant->course->student_loan ?? '';
@endphp
<div id="editAdmissionCourseDetailsModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editAdmissionCourseDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Edit proposed course</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    @php
                        $adpCurCreation = $applicant->course->creation ?? null;
                        $adpCurFees = $adpCurCreation && $adpCurCreation->fees !== null ? (float) $adpCurCreation->fees : null;
                        $adpCurStart = null;
                        if (!empty($courseCreationAvailibility) && $adpCurCreation) {
                            foreach ($courseCreationAvailibility as $ci) { if ($ci->creation && $ci->creation->id == $adpCurCreation->id && !empty($ci->course_start_date)) { $adpCurStart = $ci->course_start_date; break; } }
                        }
                        $adpSfe = isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1;
                    @endphp
                    <div class="adp-subhead">Course</div>
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="course_creation_id">Course &amp; semester <span class="adp-req">*</span> <i data-loading-icon="oval" data-color="black" class="courseLoading adp-inline-loader hidden"></i></label>
                            <select id="course_creation_id" class="lcc-tom-select" name="course_creation_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($courseCreationAvailibility))
                                    @foreach($courseCreationAvailibility as $ci)
                                        <option data-ew="{{ $ci->creation->has_evening_and_weekend }}" data-fees="{{ $ci->creation->fees }}" data-start="{{ $ci->course_start_date }}" {{ isset($applicant->course->course_creation_id) && $applicant->course->course_creation_id == $ci->creation->id ? 'selected' : '' }} value="{{ $ci->creation->id }}">{{ $ci->creation->course->name }} - {{ $ci->creation->semester->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="adp-note">Only intakes currently open for admission are listed.</span>
                            <div class="acc__input-error error-course_creation_id adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="venue_id">Venue / campus <span class="adp-req">*</span></label>
                            <select id="venue_id" class="lcc-tom-select" name="venue_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($venues))
                                    @foreach($venues as $vn)
                                        @if($vn->pivot->deleted_at == null)
                                            <option {{ isset($applicant->course->venue) && !empty($applicant->course->venue) && ($applicant->course->venue->id == $vn->id) ? 'selected' : '' }} value="{{ $vn->id }}">{{ $vn->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-venue_id adp-err"></div>
                        </div>
                    </div>
                    <dl class="adp-kv adp-kv--course" data-adp-course-info {{ $adpCurFees === null && !$adpCurStart ? 'hidden' : '' }}>
                        <dt>Tuition fee</dt><dd data-adp-fee>{{ $adpCurFees !== null ? '£'.number_format($adpCurFees, 0).' per year' : '—' }}</dd>
                        <dt>Start date</dt><dd data-adp-start>{{ $adpCurStart ? \Carbon\Carbon::parse($adpCurStart)->format('d M Y') : '—' }}</dd>
                    </dl>
                    {{-- admission.js shows this on venue change (weekends 1 = optional, 2 = this campus only, .onlyWeekends) --}}
                    <div class="adp-sub eveningWeekendWrap" style="display: {{ $applicant->creation_venue_status ? 'block' : 'none' }};">
                        <label class="adp-opt adp-opt--chk" for="full_time">
                            <input {{ (isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? 'checked' : '') }} id="full_time" class="adp-opt__input" name="full_time" value="1" type="checkbox">
                            <span class="adp-opt__box">{!! $adpCheck !!}</span>
                            <span>Are you applying for evening and weekend classes (Full Time)?<span class="adp-opt__hint" data-adp-ew-hint>This campus runs both weekday and evening/weekend timetables.</span></span>
                        </label>
                        <div class="acc__input-error error-full_time adp-err"></div>
                    </div>

                    <hr class="adp-rule">

                    <div class="adp-subhead">Funding</div>
                    <div class="adp-field">
                        <label for="student_loan">How are you funding your education at London Churchill College? <span class="adp-req">*</span></label>
                        <select id="student_loan" class="lcc-tom-select" name="student_loan">
                            <option value="">Please select</option>
                            <option {{ $adpLoan == 'Independently/Private' ? 'selected' : '' }} value="Independently/Private">Independently/Private</option>
                            <option {{ $adpLoan == 'Funding Body' ? 'selected' : '' }} value="Funding Body">Funding Body</option>
                            <option {{ $adpLoan == 'Sponsor' ? 'selected' : '' }} value="Sponsor">Sponsor</option>
                            <option {{ $adpLoan == 'Student Loan' ? 'selected' : '' }} value="Student Loan">Student Loan</option>
                            <option {{ $adpLoan == 'Others' ? 'selected' : '' }} value="Others">Other</option>
                        </select>
                        <div class="acc__input-error error-student_loan adp-err"></div>
                    </div>
                    {{-- admission.js fades the three question wrappers in/out on #student_loan /
                         #student_finance_england; the panel follows .studentLoanApplied --}}
                    <div class="adp-sub adp-loan-panel">
                        <p class="adp-note">Because you chose Student Loan, please answer every question below.</p>
                        <div class="adp-field studentLoanEnglandFunding" style="display: {{ $adpLoan == 'Student Loan' ? 'grid' : 'none' }};">
                            <label for="student_finance_england">1. Have you applied to Student Finance England for this course? <span class="adp-req">*</span></label>
                            <div>{!! $adpSwitch('student_finance_england', 'student_finance_england', $adpSfe) !!}</div>
                            <div class="acc__input-error error-student_finance_england adp-err"></div>
                        </div>
                        <div class="adp-field studentLoanFundReceipt" style="display: {{ $adpLoan == 'Student Loan' && $adpSfe ? 'grid' : 'none' }};">
                            <label for="fund_receipt">2. Have you already received funds from Student Finance England? <span class="adp-req">*</span></label>
                            <div>{!! $adpSwitch('fund_receipt', 'fund_receipt', isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1) !!}</div>
                            <div class="acc__input-error error-fund_receipt adp-err"></div>
                        </div>
                        <div class="adp-field studentLoanApplied" style="display: {{ $adpLoan == 'Student Loan' ? 'grid' : 'none' }};">
                            <label for="applied_received_fund"><span data-adp-q3>{{ $adpSfe ? '3' : '2' }}</span>. Have you ever applied for or received a loan from the Student Loans Company (SLC) or any government body for a different course or institution? <span class="adp-req">*</span></label>
                            <div>{!! $adpSwitch('applied_received_fund', 'applied_received_fund', isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1) !!}</div>
                            <div class="acc__input-error error-applied_received_fund adp-err"></div>
                        </div>
                    </div>
                    <div class="adp-field otherFundings" style="display: {{ $adpLoan == 'Others' ? 'grid' : 'none' }};">
                        <label for="other_funding">Please type other funding <span class="adp-req">*</span></label>
                        <input type="text" placeholder="Other funding" value="{{ $applicant->course->other_funding ?? '' }}" id="other_funding" class="adp-ctl" name="other_funding">
                        <div class="acc__input-error error-other_funding adp-err"></div>
                    </div>
                    <div class="adp-field">
                        <label for="fee_eligibility_id">Fee eligibility</label>
                        <select id="fee_eligibility_id" class="adp-ctl adp-select" name="fee_eligibility_id">
                            <option value="">Please select</option>
                            @if($feeelegibility->count() > 0)
                                @foreach($feeelegibility as $fl)
                                    <option {{ isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id == $fl->id ? 'selected' : '' }} value="{{ $fl->id }}">{{ $fl->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="savePCP" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" value="{{ $applicant->id }}" name="applicant_id"/>
                    <input type="hidden" value="{{ $applicant->course->id ?? 0 }}" name="id"/>
                    <input type="hidden" value="{{ (isset($applicant->feeeligibility->id) && $applicant->feeeligibility->id > 0 ? $applicant->feeeligibility->id : 0) }}" name="applicant_proof_of_id"/>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Qualifications ============================ --}}
@foreach([['id' => 'addQualificationModal', 'form' => 'addQualificationForm', 'title' => 'Add qualification', 'prefix' => '', 'btn' => 'saveEducationQualification', 'label' => 'Save'], ['id' => 'editQualificationModal', 'form' => 'editQualificationForm', 'title' => 'Edit qualification', 'prefix' => 'edit_', 'btn' => 'updateEducationQualification', 'label' => 'Save changes']] as $adpQ)
<div id="{{ $adpQ['id'] }}" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="{{ $adpQ['form'] }}" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>{{ $adpQ['title'] }}</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field adp-span-2">
                            <label for="{{ $adpQ['prefix'] }}highest_academic">Highest academic qualification <span class="adp-req">*</span></label>
                            <input type="text" placeholder="e.g. L3 Diploma in Business" id="{{ $adpQ['prefix'] }}highest_academic" class="adp-ctl" name="highest_academic">
                            <div class="acc__input-error error-highest_academic adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2">
                            <label for="{{ $adpQ['prefix'] }}awarding_body">Awarding body</label>
                            <input type="text" placeholder="e.g. PEARSON, NCFE, a university" id="{{ $adpQ['prefix'] }}awarding_body" class="adp-ctl" name="awarding_body">
                            <div class="acc__input-error error-awarding_body adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2">
                            <label for="{{ $adpQ['prefix'] }}subjects">Subjects</label>
                            <input type="text" placeholder="Subjects" id="{{ $adpQ['prefix'] }}subjects" class="adp-ctl" name="subjects">
                            <div class="acc__input-error error-subjects adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpQ['prefix'] }}result">Result</label>
                            <input type="text" placeholder="e.g. Merit, 5 GCSEs A–C" id="{{ $adpQ['prefix'] }}result" class="adp-ctl" name="result">
                            <div class="acc__input-error error-result adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpQ['prefix'] }}degree_award_date">Date of award</label>
                            <input type="text" placeholder="DD-MM-YYYY" id="{{ $adpQ['prefix'] }}degree_award_date" class="adp-ctl admissionDatepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                            <div class="acc__input-error error-degree_award_date adp-err"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="{{ $adpQ['btn'] }}" class="adp-btn adp-btn--primary">{{ $adpQ['label'] }} {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    @if($adpQ['prefix'] !== '')<input type="hidden" name="id" value="0"/>@endif
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ============================ Employment ============================ --}}
@foreach([['id' => 'addEmployementHistoryModal', 'form' => 'addEmployementHistoryForm', 'title' => 'Add employment history', 'prefix' => '', 'btn' => 'saveEmpHistory', 'label' => 'Save', 'wrap' => 'empHistoryAddressInline'], ['id' => 'editEmployementHistoryModal', 'form' => 'editEmployementHistoryForm', 'title' => 'Edit employment', 'prefix' => 'edit_', 'btn' => 'updateEmpHistory', 'label' => 'Save changes', 'wrap' => 'editEmpHistoryAddressInline']] as $adpE)
<div id="{{ $adpE['id'] }}" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="{{ $adpE['form'] }}" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>{{ $adpE['title'] }}</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field adp-span-2">
                            <label for="{{ $adpE['prefix'] }}company_name">Organisation name <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Organisation name" id="{{ $adpE['prefix'] }}company_name" class="adp-ctl" name="company_name">
                            <div class="acc__input-error error-company_name adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}company_phone">Organisation phone <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Organisation phone" id="{{ $adpE['prefix'] }}company_phone" class="adp-ctl phoneMask" name="company_phone">
                            <div class="acc__input-error error-company_phone adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}position">Position <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Position" id="{{ $adpE['prefix'] }}position" class="adp-ctl" name="position">
                            <div class="acc__input-error error-position adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}start_date">Start date <span class="adp-req">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="{{ $adpE['prefix'] }}start_date" class="adp-ctl datepicker" name="start_date" data-format="MM-YYYY" data-single-mode="true" autocomplete="off">
                            <div class="acc__input-error error-start_date adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}end_date">End date <span class="adp-req">*</span></label>
                            <input type="text" placeholder="MM-YYYY" id="{{ $adpE['prefix'] }}end_date" class="adp-ctl datepicker" name="end_date" data-format="MM-YYYY" data-single-mode="true" autocomplete="off">
                            <div class="acc__input-error error-end_date adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2">
                            <label class="adp-opt adp-opt--chk" for="{{ $adpE['prefix'] }}continuing">
                                <input id="{{ $adpE['prefix'] }}continuing" class="adp-opt__input" type="checkbox" name="continuing" value="1">
                                <span class="adp-opt__box">{!! $adpCheck !!}</span>
                                <span>Continuing (current job)</span>
                            </label>
                        </div>
                        <div class="adp-span-2 adp-subhead">Organisation address</div>
                        <div class="adp-span-2">
                            <div id="{{ $adpE['wrap'] }}" class="adp-grid-2 theAddressWrap">
                                <div class="adp-field adp-span-2">
                                    <label for="{{ $adpE['prefix'] }}employment_address_lookup">Address lookup</label>
                                    <input type="text" placeholder="Start typing the address…" id="{{ $adpE['prefix'] }}employment_address_lookup" class="adp-ctl theAddressLookup" name="employment_address_lookup" autocomplete="off">
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="{{ $adpE['prefix'] }}employment_address_line_1">Address line 1 <span class="adp-req">*</span></label>
                                    <input type="text" placeholder="Address line 1" id="{{ $adpE['prefix'] }}employment_address_line_1" class="address_line_1 adp-ctl required" name="employment_address_line_1">
                                    <input type="hidden" value="" name="employment_address" data-address-source="employment_address_line_1">
                                </div>
                                <div class="adp-field adp-span-2">
                                    <label for="{{ $adpE['prefix'] }}employment_address_line_2">Address line 2</label>
                                    <input type="text" placeholder="Address line 2 (optional)" id="{{ $adpE['prefix'] }}employment_address_line_2" class="address_line_2 adp-ctl" name="employment_address_line_2">
                                </div>
                                <div class="adp-field">
                                    <label for="{{ $adpE['prefix'] }}employment_address_city">City / Town <span class="adp-req">*</span></label>
                                    <input type="text" placeholder="City / Town" id="{{ $adpE['prefix'] }}employment_address_city" class="city adp-ctl required" name="employment_address_city">
                                </div>
                                <div class="adp-field">
                                    <label for="{{ $adpE['prefix'] }}employment_address_state">State / County</label>
                                    <input type="text" placeholder="State / County" id="{{ $adpE['prefix'] }}employment_address_state" class="state adp-ctl" name="employment_address_state">
                                </div>
                                <div class="adp-field">
                                    <label for="{{ $adpE['prefix'] }}employment_address_postal_zip_code">Post code <span class="adp-req">*</span></label>
                                    <input type="text" placeholder="Post code" id="{{ $adpE['prefix'] }}employment_address_postal_zip_code" class="postal_code adp-ctl required" name="employment_address_postal_zip_code">
                                </div>
                                <div class="adp-field">
                                    <label for="{{ $adpE['prefix'] }}employment_address_country">Country <span class="adp-req">*</span></label>
                                    <input type="text" placeholder="Country" id="{{ $adpE['prefix'] }}employment_address_country" class="country adp-ctl required" name="employment_address_country">
                                </div>
                            </div>
                            <div class="acc__input-error error-employment_address adp-err"></div>
                        </div>
                        <div class="adp-span-2 adp-subhead">Reference at this organisation</div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}contact_name">Contact name <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Contact name" id="{{ $adpE['prefix'] }}contact_name" class="adp-ctl" name="contact_name">
                            <div class="acc__input-error error-contact_name adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}contact_position">Contact position <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Contact position" id="{{ $adpE['prefix'] }}contact_position" class="adp-ctl" name="contact_position">
                            <div class="acc__input-error error-contact_position adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}contact_phone">Contact phone <span class="adp-req">*</span></label>
                            <input type="text" placeholder="Contact phone" id="{{ $adpE['prefix'] }}contact_phone" class="adp-ctl phoneMask" name="contact_phone">
                            <div class="acc__input-error error-contact_phone adp-err"></div>
                        </div>
                        <div class="adp-field">
                            <label for="{{ $adpE['prefix'] }}contact_email">Contact email</label>
                            <input type="email" placeholder="Contact email" id="{{ $adpE['prefix'] }}contact_email" class="adp-ctl" name="contact_email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="{{ $adpE['btn'] }}" class="adp-btn adp-btn--primary">{{ $adpE['label'] }} {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    @if($adpE['prefix'] !== '')
                        <input type="hidden" name="id" value="0"/>
                        <input type="hidden" name="ref_id" value="0"/>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ============================ Residency & convictions ============================ --}}
<div id="editAdmissionResidencyCriminalModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editAdmissionResidencyCriminalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header"><h3>Edit residency &amp; convictions</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-grid-2">
                        <div class="adp-field adp-span-2">
                            <label for="residency_status_id">Residency / immigration status in the UK <span class="adp-req">*</span></label>
                            <select id="residency_status_id" class="addmissionLccTom lcc-tom-select" name="residency_status_id">
                                <option value="" selected>Please select</option>
                                @if(!empty($residencyStatuses))
                                    @foreach($residencyStatuses as $residency_status)
                                        <option data-ew="{{ $residency_status->id }}" {{ isset($applicant->residency->residency_status_id) && $applicant->residency->residency_status_id == $residency_status->id ? 'selected' : '' }} value="{{ $residency_status->id }}">{{ $residency_status->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-residency_status_id adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2">
                            <label>Have you been convicted of any criminal offence in the UK or any other country? <span class="adp-req">*</span></label>
                            <div class="adp-opts">
                                <label class="adp-opt adp-opt--rad" for="admission_criminal_conviction_yes">
                                    <input id="admission_criminal_conviction_yes" class="adp-opt__input" type="radio" name="have_you_been_convicted" value="1" {{ isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1 ? 'checked' : '' }}>
                                    <span class="adp-opt__dot">{!! $adpCheck !!}</span>Yes
                                </label>
                                <label class="adp-opt adp-opt--rad adp-opt--neg" for="admission_criminal_conviction_no">
                                    <input id="admission_criminal_conviction_no" class="adp-opt__input" type="radio" name="have_you_been_convicted" value="0" {{ isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 0 ? 'checked' : '' }}>
                                    <span class="adp-opt__dot">{!! $adpX !!}</span>No
                                </label>
                            </div>
                            <div class="acc__input-error error-have_you_been_convicted adp-err"></div>
                        </div>
                        <div class="adp-field adp-span-2 criminalConvictionDetailsWrap" style="{{ isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1 ? '' : 'display: none;' }}">
                            <label for="criminal_conviction_details">Please give details <span class="adp-req">*</span></label>
                            <textarea id="criminal_conviction_details" name="criminal_conviction_details" class="adp-ctl" rows="4" placeholder="Provide details of the conviction(s)">{{ $applicant->criminalConviction->criminal_conviction_details ?? '' }}</textarea>
                            <div class="acc__input-error error-criminal_conviction_details adp-err"></div>
                        </div>
                        <div class="adp-span-2 adp-sub">
                            <div class="adp-subhead">Declaration</div>
                            <p class="adp-copy">Please ensure that all information provided is complete and accurate. Failure to disclose relevant information, or the provision of false or misleading information, may result in:</p>
                            <ul class="adp-list adp-copy">
                                <li>Withdrawal of an offer</li>
                                <li>Termination of enrolment</li>
                                <li>Further action in line with College policies</li>
                            </ul>
                            <label class="adp-opt adp-opt--chk" for="admission_criminal_declaration">
                                <input id="admission_criminal_declaration" class="adp-opt__input" type="checkbox" name="criminal_declaration" value="1" {{ isset($applicant->criminalConviction->criminal_declaration) && (int) $applicant->criminalConviction->criminal_declaration === 1 ? 'checked' : '' }}>
                                <span class="adp-opt__box">{!! $adpCheck !!}</span>
                                <span>I confirm I have read and understood the above declaration.</span>
                            </label>
                            <div class="acc__input-error error-criminal_declaration adp-err"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="saveResidencyCriminal" class="adp-btn adp-btn--primary">Save changes {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="confirmEmploymentModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
            <h3 class="confModTitle">Are you sure?</h3>
            <p class="confModDesc"></p>
            <div class="adp-dialog__actions">
                <button type="button" class="disAgreeWith adp-btn adp-btn--ghost">No, cancel</button>
                <button type="button" data-status="none" data-applicant="{{ $applicant->id }}" class="agreeWith adp-btn adp-btn--primary">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

<div id="confirmEducationModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
            <h3 class="confModTitle">Are you sure?</h3>
            <p class="confModDesc"></p>
            <div class="adp-dialog__actions">
                <button type="button" class="disAgreeWith adp-btn adp-btn--ghost">No, cancel</button>
                <button type="button" data-applicant="{{ $applicant->id }}" data-action="none" class="agreeWith adp-btn adp-btn--primary">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

@include('pages.students.admission.partials.portal-dialogs')
