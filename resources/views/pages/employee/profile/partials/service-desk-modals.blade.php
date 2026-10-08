{{--
    The two dialogs behind the Service Desk tickets card: the ticket itself,
    and the PIN prompt in front of a confidential one.

    Both wear this profile's own modal design (ep-doc-modal), and keep the ids
    resources/js/student-service-desk.js looks for — one script opens a ticket
    on a student's record and on an employee's. `data-sd-base` is what tells it
    which profile it is on, and so where to ask.
--}}

<!-- BEGIN: Service Desk ticket panel -->
<div id="serviceDeskTicketModal" class="modal ep-doc-modal" tabindex="-1" aria-hidden="true"
     data-sd-base="/employee-profile/{{ $employee->id }}/service-desk">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header ep-doc-modal__header">
                <div class="ep-doc-modal__intro">
                    <span class="ep-doc-modal__icon">
                        <i data-lucide="ticket" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h2>Service Desk Ticket</h2>
                        <p>Read only. Tickets are answered in Operations.</p>
                    </div>
                </div>
                <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close" aria-label="Close">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
            <div class="modal-body p-0" id="serviceDeskTicketBody">
                <div class="p-5 text-center text-slate-500">Loading…</div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Service Desk ticket panel -->

<!-- BEGIN: Confidential ticket PIN -->
{{-- The same dialog as an encrypted document on the Documents tab, because it
     is the same PIN and the same promise: counted, locked out, and recorded. --}}
<div id="sdConfidentialModal" class="modal ep-doc-modal ep-doc-pin-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="#" id="sdConfidentialForm" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header ep-doc-modal__header">
                    <div class="ep-doc-modal__intro">
                        <span class="ep-doc-modal__icon">
                            <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2>Confidential Ticket</h2>
                            <p>Enter your document PIN to open this ticket.</p>
                        </div>
                    </div>
                    <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close" aria-label="Close">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="ep-doc-upload-name">
                        <div class="ep-doc-upload-name__prefix">Ticket</div>
                        <div id="sdConfidentialRef" class="ep-doc-upload-name__value"></div>
                    </div>

                    <div class="ep-doc-pin-field">
                        <label for="sdConfidentialPin" class="form-label ep-doc-upload-label">Document PIN</label>
                        <input type="password" id="sdConfidentialPin" name="pin" class="form-control w-full ep-doc-pin-input"
                               inputmode="numeric" pattern="[0-9]*" maxlength="{{ $vault['pin_max_length'] }}" autocomplete="one-time-code"/>
                        <div id="sdConfidentialError" class="ep-doc-pin-error" role="alert"></div>
                    </div>

                    <p class="ep-doc-pin-note">
                        <i data-lucide="scan-eye" class="w-4 h-4"></i>
                        <span>This ticket was marked confidential by the department that holds it. Opening it is recorded against your name.</span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sdConfidentialSubmit">Open</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Confidential ticket PIN -->
