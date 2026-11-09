{{--
    Add applicant, verify codes, remove, and the outcome dialogs. The skin is
    the applicant portal's (`apd-modal-skin`, which carries its own tokens
    because the theme moves a modal to <body>). Hooks the script relies on are
    kept: the form ids, field names, hidden `url` / `user_id` / `id` / `type`,
    `#modal-emailverified` / `#modal-mobileverified`, `.resend-email`,
    `.resend-mobile`, `.save`, `.agreeWith`, and the success/error targets.
--}}
<!-- BEGIN: Add applicant -->
<div id="addDeteilsModal" class="modal apd-modal-skin" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addStudentDetailsForm" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="apd-modal__title">Add new applicant</h3>
                    <button type="button" data-tw-dismiss="modal" class="apd-modal__x" aria-label="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>
                <div class="modal-body">
                    <div class="apd-modal__stack">
                        <p class="apd-modal__copy" style="color:var(--ink-2)">We text a 4-digit code to the applicant's mobile and email a 4-digit code to their inbox. You enter both codes to confirm the contact details are real. Codes are valid for 24 hours.</p>
                        <div class="apd-grid-2">
                            <div class="apd-field">
                                <label for="first_name">First name <span class="text-danger">*</span></label>
                                <input id="first_name" type="text" name="first_name" class="form-control apd-ctl first_name">
                                <div id="error-first_name" class="acc__input-error error-first_name apd-err text-danger"></div>
                            </div>
                            <div class="apd-field">
                                <label for="last_name">Last name <span class="text-danger">*</span></label>
                                <input id="last_name" type="text" name="last_name" class="form-control apd-ctl last_name">
                                <div id="error-last_name" class="acc__input-error error-last_name apd-err text-danger"></div>
                            </div>
                            <div class="apd-field">
                                <label for="mobile">Mobile <span class="text-danger">*</span></label>
                                <input id="mobile" type="text" name="mobile" class="form-control apd-ctl mobile">
                                <div id="error-mobile" class="acc__input-error error-mobile apd-err text-danger"></div>
                            </div>
                            <div class="apd-field">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input id="email" type="email" name="email" class="form-control apd-ctl email">
                                <div id="error-email" class="acc__input-error error-email apd-err text-danger"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Cancel</button>
                    <button type="submit" id="savePD" class="apd-btn apd-btn--primary save">
                        Send verification codes
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-4 h-4 ml-2"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>
                    </button>
                    <input type="hidden" value="{{ $user->id }}" name="user_id"/>
                    <input type="hidden" name="url" value="{{ route('agent.apply.check') }}" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add applicant -->

<!-- BEGIN: Verify codes -->
<div id="confirmModal" class="modal apd-modal-skin" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="apd-modal__title" id="verifyTitle">Verify applicant</h3>
                <button type="button" data-tw-dismiss="modal" class="apd-modal__x" aria-label="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></button>
            </div>
            <div class="modal-body">
                <div class="apd-modal__stack">
                    <div class="apd-notice apd-notice--warn">Both the email and the mobile must be verified before you can start the application.</div>

                    <form method="POST" action="#" id="verifyEmailForm" class="agp-vrow" autocomplete="off">
                        <div style="min-width:0"><b style="font-size:13px">Email</b><div id="horizontal-email" style="font-size:13.5px;overflow-wrap:anywhere"></div></div>
                        <span class="agp-flag ok agp-vrow__done" hidden><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>Verified</span>
                        <div id="modal-emailverified" class="agp-vrow__entry">
                            <div style="display:flex;gap:6px">
                                <input id="horizontal-form-1" name="email_verify_code" type="text" class="form-control apd-ctl agp-code email_verify_code" maxlength="4" inputmode="numeric" placeholder="XXXX" aria-label="Email code">
                                <button type="button" class="resend-email apd-btn apd-btn--ghost apd-btn--sm" data-id="0"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/></svg>Resend</button>
                                <button type="submit" class="save apd-btn apd-btn--primary apd-btn--sm" data-id="0">Verify</button>
                            </div>
                            <div class="acc__input-error error-email_verify_code apd-err text-danger"></div>
                        </div>
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('agent.apply.email.verify') }}" />
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <input name="type" value="email" type="hidden">
                    </form>

                    <form method="POST" action="#" id="verifyMobileForm" class="agp-vrow" autocomplete="off">
                        <div style="min-width:0"><b style="font-size:13px">Mobile</b><div id="horizontal-mobile" style="font-size:13.5px;overflow-wrap:anywhere"></div></div>
                        <span class="agp-flag ok agp-vrow__done" hidden><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>Verified</span>
                        <div id="modal-mobileverified" class="agp-vrow__entry">
                            <div style="display:flex;gap:6px">
                                <input id="horizontal-form-2" name="verify_code" type="text" class="form-control apd-ctl agp-code verify_code" maxlength="4" inputmode="numeric" placeholder="XXXX" aria-label="Mobile code">
                                <button type="button" class="resend-mobile apd-btn apd-btn--ghost apd-btn--sm" data-id="0"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/></svg>Resend</button>
                                <button type="submit" class="save apd-btn apd-btn--primary apd-btn--sm" data-id="0">Verify</button>
                            </div>
                            <div class="acc__input-error error-verify_code apd-err text-danger"></div>
                        </div>
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('agent.apply.verify') }}" />
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <input name="type" value="mobile" type="hidden">
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Close</button>
                <a id="verifyApplyNow" href="#" class="apd-btn apd-btn--primary" hidden>Apply now</a>
            </div>
        </div>
    </div>
</div>
<!-- END: Verify codes -->

<!-- BEGIN: Remove applicant -->
<div id="confirmDeleteModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body apd-dlg">
                <span class="apd-dlg__icon apd-dlg__icon--red"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg></span>
                <h3 class="apd-dlg__title">Remove this applicant?</h3>
                <p class="apd-dlg__copy">The pending applicant and any unsubmitted draft for them will be deleted. This cannot be undone.</p>
                <div class="apd-dlg__actions">
                    <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Cancel</button>
                    <button type="button" data-id="0" class="agreeWith apd-btn apd-btn--red">Yes, remove</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Remove applicant -->

<div id="successModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-body apd-dlg">
        <span class="apd-dlg__icon apd-dlg__icon--green"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.4 2.4 4.6-4.9"/></svg></span>
        <h3 class="apd-dlg__title successModalTitle"></h3>
        <p class="apd-dlg__copy successModalDesc"></p>
        <div class="apd-dlg__actions apd-dlg__actions--one"><button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--green">Done</button></div>
    </div></div></div>
</div>
<div id="errorModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-body apd-dlg">
        <span class="apd-dlg__icon apd-dlg__icon--red"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.6v5M12 16.1h.01"/></svg></span>
        <h3 class="apd-dlg__title errorModalTitle"></h3>
        <p class="apd-dlg__copy errorModalDesc"></p>
        <div class="apd-dlg__actions apd-dlg__actions--one"><button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Close</button></div>
    </div></div></div>
</div>
