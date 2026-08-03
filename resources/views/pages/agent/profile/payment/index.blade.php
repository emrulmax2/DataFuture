@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="agm-page agm-profile-page">
        @include('pages.agent.profile.show-info')

        <section class="agm-profile-panel agm-payment-panel">
            <div class="agm-profile-panel__header agm-payment-panel__header">
                <div class="agm-section-title">
                    <span aria-hidden="true"></span>
                    <h2>Bank Details</h2>
                </div>

                <button data-tw-toggle="modal" data-tw-target="#addBankDetailsModal" type="button" class="agm-btn agm-btn--primary agm-payment-add-bank">
                    <i data-lucide="plus"></i>
                    Add Bank
                </button>
            </div>

            <div class="agm-payment-toolbar" aria-label="Bank details filters">
                <form id="tabulatorFilterForm" class="agm-payment-toolbar__form">
                    <div class="agm-payment-toolbar__group">
                        <label for="query">Query</label>
                        <input id="query" name="query" type="text" placeholder="Search..." autocomplete="off">
                    </div>

                    <div class="agm-payment-toolbar__group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option selected value="1">Active</option>
                            <option value="0">Inactive</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>

                    <div class="agm-payment-toolbar__actions">
                        <button id="tabulator-html-filter-go" type="button" class="agm-btn agm-btn--primary">Go</button>
                        <button id="tabulator-html-filter-reset" type="button" class="agm-btn agm-btn--muted">Reset</button>
                    </div>
                </form>

                <button id="tabulator-print" type="button" class="agm-btn agm-btn--outline agm-payment-toolbar__print">
                    <i data-lucide="printer"></i>
                    Print
                </button>
            </div>

            <div class="agm-profile-table-wrap agm-bank-table-wrap">
                <div id="agentBankListTable" data-agent="{{ $employee->id }}" class="agm-profile-table agm-agent-table agm-bank-table"></div>
            </div>
        </section>
    </div>

    <div id="addBankDetailsModal" class="modal agm-agent-modal agm-bank-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addBankDetailsForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Add Bank Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>

                    <div class="modal-body">
                        <div class="agm-agent-form-grid agm-agent-form-grid--single">
                            <div class="agm-agent-form-field">
                                <label for="beneficiary">Beneficiary Name <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="user"></i>
                                    <input type="text" value="" id="beneficiary" name="beneficiary" class="beneficiary">
                                </div>
                                <div class="acc__input-error error-beneficiary"></div>
                            </div>

                            <div class="agm-agent-form-field">
                                <label for="sort_code">Sort Code <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="hash"></i>
                                    <input type="text" value="" id="sort_code" name="sort_code" class="sort_code">
                                </div>
                                <div class="acc__input-error error-sort_code"></div>
                            </div>

                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label for="ac_no">Account Number <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="landmark"></i>
                                    <input type="text" value="" id="ac_no" minlength="8" maxlength="8" name="ac_no" class="ac_no">
                                </div>
                                <div class="acc__input-error error-ac_no"></div>
                            </div>

                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label class="agm-agent-toggle-card agm-bank-modal__status" for="active">
                                    <input id="active" class="agm-agent-option-input" name="active" checked value="1" type="checkbox">
                                    <span class="agm-agent-option-card">
                                        <span class="agm-agent-option-mark">
                                            <i data-lucide="x" class="agm-agent-option-mark__off"></i>
                                            <i data-lucide="check" class="agm-agent-option-mark__on"></i>
                                        </span>
                                        <span>
                                            <strong>Active</strong>
                                            <small class="agm-agent-option-hint agm-agent-option-hint--on">Available for use</small>
                                            <small class="agm-agent-option-hint agm-agent-option-hint--off">Currently inactive</small>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer agm-bank-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="saveABNK" class="btn btn-primary">
                            <i data-lucide="plus"></i>
                            Add Bank
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="editBankDetailsModal" class="modal agm-agent-modal agm-bank-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editBankDetailsForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Update Bank Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>

                    <div class="modal-body">
                        <div class="agm-agent-form-grid agm-agent-form-grid--single">
                            <div class="agm-agent-form-field">
                                <label for="edit_beneficiary">Beneficiary Name <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="user"></i>
                                    <input type="text" value="" id="edit_beneficiary" name="beneficiary" class="beneficiary">
                                </div>
                                <div class="acc__input-error error-beneficiary"></div>
                            </div>

                            <div class="agm-agent-form-field">
                                <label for="edit_sort_code">Sort Code <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="hash"></i>
                                    <input type="text" value="" id="edit_sort_code" name="sort_code" class="sort_code">
                                </div>
                                <div class="acc__input-error error-sort_code"></div>
                            </div>

                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label for="edit_ac_no">Account Number <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="landmark"></i>
                                    <input type="text" value="" id="edit_ac_no" minlength="8" maxlength="8" name="ac_no" class="ac_no">
                                </div>
                                <div class="acc__input-error error-ac_no"></div>
                            </div>

                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label class="agm-agent-toggle-card agm-bank-modal__status" for="edit_active">
                                    <input id="edit_active" class="agm-agent-option-input" name="active" value="1" type="checkbox">
                                    <span class="agm-agent-option-card">
                                        <span class="agm-agent-option-mark">
                                            <i data-lucide="x" class="agm-agent-option-mark__off"></i>
                                            <i data-lucide="check" class="agm-agent-option-mark__on"></i>
                                        </span>
                                        <span>
                                            <strong>Active</strong>
                                            <small class="agm-agent-option-hint agm-agent-option-hint--on">Available for use</small>
                                            <small class="agm-agent-option-hint agm-agent-option-hint--off">Currently inactive</small>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer agm-bank-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="updateABNK" class="btn btn-primary">
                            <i data-lucide="save"></i>
                            Update Bank
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal agm-agent-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="check-circle"></i>
                    <h2 class="successModalTitle">Done</h2>
                    <p class="successModalDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" data-action="DISMISS" class="successCloser agm-btn agm-btn--primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal agm-agent-feedback-modal agm-agent-feedback-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="alert-octagon"></i>
                    <h2 class="warningModalTitle">Oops!</h2>
                    <p class="warningModalDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" data-action="DISMISS" class="warningCloser agm-btn agm-btn--primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal agm-agent-feedback-modal agm-agent-feedback-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="x-circle"></i>
                    <h2 class="confModTitle">Are you sure?</h2>
                    <p class="confModDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" class="disAgreeWith agm-btn agm-btn--muted">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-id="0" data-action="none" data-agent="{{ $employee->id }}" class="agreeWith agm-btn agm-btn--danger">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php($hideAgentProfileSuccessModal = true)
    @include('pages.agent.profile.show-modals')
@endsection

@section('script')
    @vite('resources/js/agent-global.js')
    @vite('resources/js/agent-payment-settings.js')
@endsection
