@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="agm-page agm-remittance-page">
        <section class="agm-remittance-hero">
            <div class="agm-remittance-hero__copy">
                <span class="agm-remittance-hero__icon">
                    <i data-lucide="pound-sterling"></i>
                </span>
                <div>
                    <span class="agm-eyebrow">Payouts</span>
                    <h1>Agent Remittance</h1>
                    <p>Scheduled and settled commission payments to agents</p>
                </div>
            </div>

            <div class="agm-remittance-hero__actions">
                <a href="{{ route('agent.management.remittances.payment') }}" class="agm-btn agm-btn--cream agm-remittance-action-btn">
                    <span class="agm-btn__icon-chip agm-btn__icon-chip--blue">
                        <i data-lucide="badge-pound-sterling"></i>
                    </span>
                    Payments
                </a>
                <a href="{{ route('agent.management') }}" class="agm-btn agm-btn--dark">
                    <i data-lucide="users"></i>
                    Agent Management
                </a>
            </div>
        </section>

        <section class="agm-remittance-kpis" aria-label="Remittance summary">
            @foreach(($remittanceKpis ?? []) as $kpi)
                <article class="agm-remittance-kpi agm-remittance-kpi--{{ $kpi['tone'] ?? 'teal' }}">
                    <div class="agm-remittance-kpi__head">
                        <span>{{ $kpi['label'] ?? '' }}</span>
                        <b>
                            <i data-lucide="{{ $kpi['icon'] ?? 'circle' }}"></i>
                        </b>
                    </div>
                    <strong>{{ $kpi['value'] ?? '0' }}</strong>
                    <div class="agm-remittance-kpi__bar">
                        <span style="width: {{ $kpi['width'] ?? '50%' }}"></span>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="agm-remittance-card">
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
                                <option value="0">All</option>
                                <option value="2">Paid</option>
                                <option value="1">Scheduled</option>
                                <option value="4">Pending</option>
                                <option value="3">Canceled</option>
                            </select>
                            <i data-lucide="chevron-down"></i>
                        </div>
                    </div>

                    <button id="tabulator-html-filter-go" type="submit" class="agm-btn agm-btn--primary">
                        <i data-lucide="search"></i>
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

                <button type="button" id="scheduleRemitPaymentBtn" style="display: none;" class="scheduleRemitPaymentBtn agm-btn agm-remittance-schedule-btn">
                    <i data-lucide="calendar-days"></i>
                    Schedule Payment
                </button>
            </div>

            <div class="agm-remittance-table-wrap">
                <div id="agentRemittanceListTable" class="agm-remittance-table"></div>
            </div>
        </section>
    </div>

    <div id="scheduleRemitPaymentModal" class="modal agm-commission-modal agm-remittance-schedule-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="scheduleRemitPaymentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Schedule Payment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="theScheduleLoader agm-remittance-loader">
                            <i data-loading-icon="oval" class="w-8 h-8"></i>
                            <span>Loading selected remittances...</span>
                        </div>
                        <div class="theScheduleContent agm-remittance-schedule-content" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="schedulePayBtn" class="btn btn-primary">
                            <i data-lucide="calendar-plus"></i>
                            Add Schedule
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
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--primary">Ok</button>
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
                        <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--muted">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith agm-btn agm-btn--danger">Yes, I agree</button>
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
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--danger">Ok</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/agent-management-remittance.js')
@endsection
