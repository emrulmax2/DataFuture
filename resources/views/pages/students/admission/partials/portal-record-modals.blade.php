{{--
    Record-level modals of the admission portal: the status change flow
    (#changeStatusModal → admission-show.js; #statusConfirmModal and
    #rejectedConfirmModal → admission-global.js), the student conversion
    progress (#progressBarModal, Vue root #app → admission-vue.js), the
    profile photo (#addApplicantPhotoModal, Dropzone) and the e-signature
    request (#sendOfferAcceptanceModal). Included by every page that renders
    partials/portal-record; needs $adpNext, $adpStatusHelp, $reasons and
    $feeelegibility from the page.
--}}
@php
    $adpSpin = $adpSpin ?? '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
    $adpClose = $adpClose ?? '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
    $adpCheck = $adpCheck ?? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $adpStatusId = (int) ($applicant->status_id ?? 0);
    $adpStatusName = $applicant->status->name ?? '—';
    $adpStatusName = $adpStatusName === 'Awating Decision' ? 'Awaiting Decision' : $adpStatusName;
    // Accepted / Offer Placed send the offer form: proof of ID + fee eligibility must be on record first.
    $adpNeedsProof = collect($adpNext ?? [])->contains(fn ($n) => in_array((int) $n['id'], \App\Support\AdmissionStatusFlow::NEEDS_PROOF, true));
    $adpOfferMissing = $adpNeedsProof ? \App\Support\AdmissionStatusFlow::missingForOffer($applicant) : [];
@endphp

{{-- ============================ Change status ============================ --}}
<div id="changeStatusModal" class="modal adp-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h3>Change application status</h3>{!! $adpClose !!}</div>
            <div class="modal-body">
                <div class="adp-vrow"><span>Current status</span><span class="adp-pill adp-pill--{{ [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'][$adpStatusId] ?? 'muted' }}"><i></i>{{ $adpStatusName }}</span></div>
                <div class="adp-field">
                    <label for="adp_new_status">New status <span class="adp-req">*</span></label>
                    <select id="adp_new_status" class="adp-ctl adp-select">
                        <option value="">Please select</option>
                        @foreach($adpNext as $adpN)
                            <option value="{{ $adpN['id'] }}" data-help="{{ $adpStatusHelp[$adpN['id']] ?? '' }}" @if(in_array((int) $adpN['id'], \App\Support\AdmissionStatusFlow::NEEDS_PROOF, true)) data-needs-proof="1" @endif>{{ $adpN['name'] }}</option>
                        @endforeach
                    </select>
                    <div class="adp-err" data-adp-status-error></div>
                </div>
                <div class="adp-notice" data-adp-status-help hidden></div>
                @if(!empty($adpOfferMissing))
                    {{-- shown for Accepted / Offer Placed while something is missing; Continue stays disabled --}}
                    <div class="adp-notice adp-notice--bad" data-adp-offer-block hidden>
                        <b>Proof of ID and fee eligibility needed</b>
                        <div>The offer acceptance form can't be sent until these are on the record. Missing: <b>{{ implode(', ', $adpOfferMissing) }}</b>.</div>
                        <div class="adp-notice__acts">
                            <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-offer-fix data-url="{{ route('admission.show', $applicant->id) }}?proof=1#adp-proof">Add proof of ID &amp; fee eligibility</button>
                        </div>
                    </div>
                @endif
                <div class="adp-field" data-adp-reason hidden>
                    <label for="rejected_reason">Rejection reason <span class="adp-req">*</span></label>
                    <select id="rejected_reason" name="rejected_reason" class="adp-ctl adp-select">
                        <option value="">Please select a reason</option>
                        @if(isset($reasons) && $reasons->count() > 0)
                            @foreach($reasons as $resn)
                                <option value="{{ $resn->id }}">{{ $resn->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="adp-err" data-adp-reason-error></div>
                </div>
                {{-- Offer Accepted: proof of ID and fee eligibility are saved with the
                     conversion (admission.student.update.status reads proof_type, proof_id,
                     proof_expiredate and fee_eligibility_id). admission-global.js's agree
                     handler reads these four controls by id, so they keep the ids they had
                     on the legacy confirm modal; admission-show.js shows the block for
                     status 7, requires every field and summarises them on the confirm step. --}}
                <div class="adp-sub adp-status-proof" data-adp-proof hidden>
                    <div class="adp-subhead">Proof of ID &amp; fee eligibility</div>
                    <p class="adp-copy adp-status-proof__hint">Saved with the conversion. Check the details before you continue.</p>
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="sts_proof_type">Proof of ID type <span class="adp-req">*</span></label>
                            <select id="sts_proof_type" class="adp-ctl adp-select" name="proof_type" data-adp-proof-field="type">
                                <option value="">Please select</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'passport' ? 'selected' : '' }} value="passport">Passport</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'birth' ? 'selected' : '' }} value="birth">Birth Certificate</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'driving' ? 'selected' : '' }} value="driving">Driving Licence</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'nid' ? 'selected' : '' }} value="nid">National ID Card</option>
                                <option {{ isset($applicant->proof->proof_type) && $applicant->proof->proof_type == 'respermit' ? 'selected' : '' }} value="respermit">Residence Permit No</option>
                            </select>
                            <div class="adp-err" data-adp-proof-error="type"></div>
                        </div>
                        <div class="adp-field">
                            <label for="sts_proof_id">ID number <span class="adp-req">*</span></label>
                            <input type="text" value="{{ $applicant->proof->proof_id ?? '' }}" placeholder="e.g. 512345678" id="sts_proof_id" class="adp-ctl" name="proof_id" data-adp-proof-field="id">
                            <div class="adp-err" data-adp-proof-error="id"></div>
                        </div>
                        <div class="adp-field">
                            <label for="sts_proof_expiredate">Expiry date <span class="adp-req">*</span></label>
                            <input type="text" value="{{ $applicant->proof->proof_expiredate ?? '' }}" placeholder="DD-MM-YYYY" id="sts_proof_expiredate" class="adp-ctl datepicker" data-format="DD-MM-YYYY" data-single-mode="true" data-allow-future name="proof_expiredate" autocomplete="off" data-adp-proof-field="expiry">
                            <div class="adp-err" data-adp-proof-error="expiry"></div>
                        </div>
                        <div class="adp-field">
                            <label for="sts_fee_eligibility_id">Fee eligibility <span class="adp-req">*</span></label>
                            <select id="sts_fee_eligibility_id" class="adp-ctl adp-select" name="fee_eligibility_id" data-adp-proof-field="fee">
                                <option value="">Please select</option>
                                @if($feeelegibility->count() > 0)
                                    @foreach($feeelegibility as $fl)
                                        <option {{ isset($applicant->feeeligibility->fee_eligibility_id) && $applicant->feeeligibility->fee_eligibility_id == $fl->id ? 'selected' : (!isset($applicant->feeeligibility->fee_eligibility_id) && $fl->id == 3 ? 'selected' : '') }} value="{{ $fl->id }}">{{ $fl->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="adp-err" data-adp-proof-error="fee"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="adp-btn adp-btn--ghost" data-tw-dismiss="modal">Cancel</button>
                <button type="button" class="adp-btn adp-btn--primary" data-adp-status-go>Continue</button>
            </div>
        </div>
    </div>
</div>

{{-- Confirm step (admission-global.js): "Are you sure?"; for Offer Accepted it
     also lists the proof of ID / fee eligibility details collected in the dialog. --}}
<div id="statusConfirmModal" class="modal adp-modal adp-modal--dialog" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 2h8.2L22 7.9v8.2L16.1 22H7.9L2 16.1V7.9z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg></span>
            <h3 class="confModTitle">Are you sure?</h3>
            <p class="confModDesc"></p>
            <div class="adp-dlg-item" data-adp-status-item hidden></div>
            {{-- Offer Accepted: what the conversion will save, copied by admission-show.js
                 from the Change status dialog's proof of ID / fee eligibility fields. --}}
            <div class="adp-dlg-form adp-status-sum" data-adp-proof-summary hidden>
                <dl class="adp-kv adp-kv--sum">
                    <dt>Proof of ID</dt><dd data-adp-sum="type"></dd>
                    <dt>ID number</dt><dd data-adp-sum="id"></dd>
                    <dt>Expiry date</dt><dd data-adp-sum="expiry"></dd>
                    <dt>Fee eligibility</dt><dd data-adp-sum="fee"></dd>
                </dl>
            </div>
            <div class="adp-dialog__actions">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">No, cancel</button>
                <button id="statusAgreement" type="button" data-statusid="0" data-applicant="{{ $applicant->id }}" class="agreeWith adp-btn adp-btn--primary">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

<div id="rejectedConfirmModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 2h8.2L22 7.9v8.2L16.1 22H7.9L2 16.1V7.9z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg></span>
            <h3 class="rejectedConfModTitle">Are you sure?</h3>
            <p class="rejectedConfModDesc"></p>
            <div class="adp-dialog__actions">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">No, cancel</button>
                <button type="button" data-statusid="0" data-applicant="{{ $applicant->id }}" class="agreeWith adp-btn adp-btn--danger">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

{{-- Student conversion progress (Vue, admission-vue.js mounts on #app). --}}
<div id="progressBarModal" class="modal adp-modal adp-modal--dialog" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div id="app" class="modal-dialog adp-modal-dialog--progress">
        <form method="POST" action="#" id="" enctype="multipart/form-data">
            <div class="modal-content adp-dialog adp-dialog--success adp-dialog--left">
                <div class="adp-dialog__row">
                    <h3 v-if="failedJobs.length" class="adp-dialog__title--danger">Conversion stopped</h3>
                    <h3 v-else-if="progressPercentage < 100">Converting {{ $applicant->first_name }} to a student</h3>
                    <h3 v-else>{{ $applicant->first_name }} is now a student</h3>
                    <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-tw-dismiss="modal" aria-label="Close">✕</button>
                </div>
                <p v-if="!failedJobs.length">Running the student process batch in order. Do not close this window.</p>
                <input id="batchId" type="hidden" value="" />
                <input id="progress" type="hidden" :value="progressPercentage" />
                <input id="conversionFailed" type="hidden" :value="failedJobs.length" />
                <div class="adp-track"><div id="progress-bar" :style="{width: `${progressPercentage}%`}" :class="failedJobs.length ? 'bg-danger' : ''" role="progressbar" :aria-valuenow="progressPercentage" aria-valuemin="0" aria-valuemax="100"></div></div>
                <div class="adp-track__meta"><span class="adp-mono">@{{ progress }}</span><b>@{{ progressPercentage }}%</b></div>
                <div v-if="failedJobs.length" class="adp-notice adp-notice--bad" role="alert">
                    <b>The student conversion stopped because a step failed. The remaining steps were skipped.</b>
                    <ul class="adp-list">
                        <li v-for="job in failedJobs">@{{ job.job_name }}: @{{ job.message }}</li>
                    </ul>
                    <a v-if="conversionLogUrl" :href="conversionLogUrl" class="adp-btn adp-btn--danger adp-btn--sm" style="margin-top:10px;">View conversion log</a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============================ Profile photo (Dropzone) ============================ --}}
<div id="addApplicantPhotoModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h3>Upload profile photo</h3>{!! $adpClose !!}</div>
            <div class="modal-body">
                <div class="adp-photo-grid">
                    <div class="adp-photo-prev">
                        <span class="adp-avatar" style="width:120px;height:120px;font-size:40px;">
                            @if(!\App\Support\Avatar::isGenerated((string) $applicant->photo_url))
                                <img src="{{ $applicant->photo_url }}" alt="">
                            @else
                                {{ \App\Support\Avatar::initialsOnly($applicant->first_name.' '.$applicant->last_name) }}
                            @endif
                        </span>
                        <small>Current</small>
                    </div>
                    <form method="post" action="{{ route('admission.upload.photo') }}" class="dropzone adp-dropzone" id="addApplicantPhotoForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback"><input name="documents" type="file" /></div>
                        <div class="dz-message" data-dz-message>
                            <span class="adp-dz-ico"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></span>
                            <b>Drop a photo here or click to browse</b>
                            <small>JPG, PNG or GIF · up to 5 MB · a clear, front-facing head and shoulders shot</small>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                <button type="button" id="uploadPhotoBtn" class="adp-btn adp-btn--primary">Upload {!! $adpSpin !!}</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================ Send e-signature request ============================ --}}
<div id="sendOfferAcceptanceModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="sendOfferAcceptanceForm">
            <div class="modal-content">
                <div class="modal-header"><h3>Send e-signature request</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <p class="adp-copy">The applicant gets a secure link to sign the {{ (int) ($applicant->status_id ?? 0) >= 6 ? 'offer acceptance form' : 'application form' }}. Tick where to send it.</p>
                    <label class="adp-opt adp-opt--chk adp-esign-ch" for="esign_contact_email">
                        <input id="esign_contact_email" class="adp-opt__input" name="contact_email" value="1" type="checkbox">
                        <span class="adp-opt__box">{!! $adpCheck !!}</span>
                        <span class="adp-esign-ch__t"><b>Email</b><span class="adp-mono">{{ $applicant->users->email ?? '' }}</span></span>
                        @if(isset($applicant->users) && $applicant->users->email_verified_at != null)
                            <span class="adp-flag adp-flag--ok">Verified</span>
                        @endif
                    </label>
                    @if(!empty($applicant->contact->mobile))
                        <label class="adp-opt adp-opt--chk adp-esign-ch" for="esign_contact_phone">
                            <input id="esign_contact_phone" class="adp-opt__input" name="contact_phone" value="1" type="checkbox">
                            <span class="adp-opt__box">{!! $adpCheck !!}</span>
                            <span class="adp-esign-ch__t"><b>SMS</b><span class="adp-mono">{{ $applicant->contact->mobile }}</span></span>
                            @if($applicant->contact->mobile_verification == 1)
                                <span class="adp-flag adp-flag--ok">Verified</span>
                            @else
                                <span class="adp-flag adp-flag--no">Unverified</span>
                            @endif
                        </label>
                    @endif
                    <div class="acc__input-error adp-err"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="sendOfferBtn" class="adp-btn adp-btn--primary">Send request {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="LocationPermissionModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <h3 class="warningModalTitle">Location permission required</h3>
            <p class="warningModalDesc">We need your location to proceed. Please allow access.</p>
            <div class="adp-dialog__actions">
                <button type="button" id="denyLocationBtn" class="adp-btn adp-btn--ghost" data-tw-dismiss="modal">Deny</button>
                <button type="button" id="allowLocationBtn" class="adp-btn adp-btn--primary">Allow</button>
            </div>
        </div>
    </div>
</div>
