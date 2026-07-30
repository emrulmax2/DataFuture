@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="agm-page agm-remittance-page agm-payments-page">
        <section class="agm-remittance-hero agm-payments-hero">
            <div class="agm-remittance-hero__copy">
                <span class="agm-remittance-hero__icon">
                    <i data-lucide="credit-card"></i>
                </span>
                <div>
                    <span class="agm-eyebrow">Settlements</span>
                    <h1>Remittance Payments</h1>
                    <p>Scheduled payments and their linked bank transactions</p>
                </div>
            </div>

            <div class="agm-remittance-hero__actions">
                <a href="{{ route('agent.management.remittance') }}" class="agm-btn agm-btn--cream agm-remittance-action-btn">
                    <span class="agm-btn__icon-chip agm-btn__icon-chip--blue">
                        <i data-lucide="badge-pound-sterling"></i>
                    </span>
                    Remittance
                </a>
                <a href="{{ route('agent.management') }}" class="agm-btn agm-btn--dark">
                    <i data-lucide="user-cog"></i>
                    Agent Management
                </a>
            </div>
        </section>

        <section class="agm-remittance-card agm-payments-card">
            <div class="agm-remittance-toolbar">
                <form id="tabulatorFilterForm" class="agm-remittance-filter">
                    <div class="agm-remittance-field">
                        <label for="query">Query</label>
                        <div class="agm-remittance-search">
                            <i data-lucide="search"></i>
                            <input id="query" name="query" type="search" placeholder="Search..." autocomplete="off">
                        </div>
                    </div>

                    <div class="agm-remittance-field">
                        <label for="status">Status</label>
                        <div class="agm-remittance-select">
                            <select id="status" name="status">
                                <option value="1">Scheduled</option>
                                <option value="2">Paid</option>
                                <option value="3">Canceled</option>
                            </select>
                            <i data-lucide="chevron-down"></i>
                        </div>
                    </div>

                    <button id="tabulator-html-filter-go" type="submit" class="agm-btn agm-btn--primary">
                        <i data-lucide="search" class="theIcon"></i>
                        Go
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="theLoader">
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

                    <button id="tabulator-html-filter-reset" type="button" class="agm-btn agm-btn--muted">
                        <i data-lucide="rotate-ccw"></i>
                        Reset
                    </button>
                </form>
            </div>

            <div class="agm-remittance-table-wrap">
                <div id="agentRemittPaymentsListTable" class="agm-remittance-table agm-payments-table"></div>
            </div>
        </section>
    </div>

    <div id="linkTransactionModal" class="modal agm-commission-modal agm-payment-link-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="linkTransactionForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Linked Transaction</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="agm-payment-link-field">
                            <label class="form-label">Search Transaction</label>
                            <div class="autoCompleteField agm-payment-link-search" data-table="acc_transactions">
                                <i data-lucide="search"></i>
                                <input type="text" autocomplete="off" id="transaction_code" name="transaction_code" class="form-control transaction_code" value="" placeholder="Type a transaction ref, e.g. TC0">
                                <input type="hidden" id="transaction_id" name="transaction_id" class="form-control transaction_id" value="">
                                <ul class="autoFillDropdown"></ul>
                            </div>
                            <div class="acc__input-error error-transaction_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="linkTransBtn" class="btn btn-primary">
                            <i data-lucide="check" class="theIcon"></i>
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                        <input type="hidden" name="agent_comission_payment_id" value="0"/>
                        <input type="hidden" name="agent_comission_total" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="successModal" class="modal agm-commission-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="check-circle"></i>
                    <h2 class="successModalTitle"></h2>
                    <p class="successModalDesc"></p>
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--primary">
                        <i data-lucide="check"></i>
                        Ok
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal agm-commission-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="x-circle"></i>
                    <h2 class="confModTitle">Are you sure?</h2>
                    <p class="confModDesc"></p>
                    <div class="agm-commission-feedback-modal__actions">
                        <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--muted">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith agm-btn agm-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal agm-commission-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="alert-octagon"></i>
                    <h2 class="warningModalTitle"></h2>
                    <p class="warningModalDesc"></p>
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--danger">
                        <i data-lucide="check"></i>
                        Ok
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/agent-management-payments.js')
@endsection
