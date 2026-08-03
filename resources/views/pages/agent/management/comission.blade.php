@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $agent = $rule?->agentuser?->agent;
        $agentName = trim(($agent?->full_name ?? '').(!empty($agent?->organization) ? ' ('.$agent->organization.')' : ''));
        $agentName = $agentName !== '' ? $agentName : ($rule?->agentuser?->email ?? 'Agent');

        $commissionMode = ($rule?->comission_mode ?? 0) == 1 ? 'Percentage' : (($rule?->comission_mode ?? 0) == 2 ? 'Fixed Amount' : '');
        $commissionValueLabel = ($rule?->comission_mode ?? 0) == 1 ? 'Percentage' : 'Amount';
        $commissionValue = ($rule?->comission_mode ?? 0) == 1
            ? (!empty($rule?->percentage) ? $rule->percentage.'%' : '')
            : (!empty($rule?->amount) ? \Illuminate\Support\Number::currency($rule->amount, in: 'GBP') : '');
        $period = ($rule?->period ?? 0) == 1 ? 'Full Course' : (($rule?->period ?? 0) == 2 ? 'Year 1' : '');
        $payment = ($rule?->payment_type ?? 0) == 1 ? 'Single Payment' : (($rule?->payment_type ?? 0) == 2 ? 'On Receipt' : '');
        $commissionDetails = [
            ['label' => 'Semester', 'value' => $rule?->semester?->name ?? ''],
            ['label' => 'Agent', 'value' => $agentName],
            ['label' => 'Agent Email', 'value' => $rule?->agentuser?->email ?? ''],
            ['label' => 'Referral Code', 'value' => $rule?->code ?? ''],
            ['label' => 'Comission Mode', 'value' => $commissionMode],
            ['label' => $commissionValueLabel, 'value' => $commissionValue],
            ['label' => 'Period', 'value' => $period],
            ['label' => 'Payment', 'value' => $payment],
        ];
    @endphp

    <div class="agm-page agm-commission-page">
        <section class="agm-commission-hero">
            <div class="agm-commission-hero__copy">
                <span class="agm-commission-hero__icon">
                    <i data-lucide="pound-sterling"></i>
                </span>
                <div>
                    <span class="agm-eyebrow">Commission</span>
                    <h1>Agent Comission Details</h1>
                    <p>Claimed and received amounts per referred student</p>
                </div>
            </div>

            <a href="{{ route('agent.management') }}" class="agm-btn agm-btn--primary">
                <i data-lucide="arrow-left"></i>
                <span>Agent Management</span>
            </a>
        </section>

        <section class="agm-commission-details">
            <div class="agm-commission-card__head">
                <span></span>
                <strong>Details</strong>
                <b>{{ $rule?->code ?? '' }}</b>
            </div>

            <div class="agm-commission-details__grid">
                @foreach($commissionDetails as $detail)
                    <div class="agm-commission-detail">
                        <small>{{ $detail['label'] }}</small>
                        <strong>{{ $detail['value'] !== '' ? $detail['value'] : 'Not set' }}</strong>
                    </div>
                @endforeach

                <div class="agm-commission-detail agm-commission-detail--students">
                    <small>No of Students</small>
                    <strong id="noOfStdCount">0</strong>
                </div>
            </div>
        </section>

        <section class="agm-commission-card">
            <div class="agm-commission-toolbar">
                <form id="tabulatorFilterForm" class="agm-commission-filter">
                    <label for="query">Query</label>
                    <div class="agm-commission-search">
                        <i data-lucide="search"></i>
                        <input id="query" name="query" type="search" placeholder="Search...">
                    </div>

                    <button id="tabulator-html-filter-go" type="submit" class="agm-btn agm-btn--primary">
                        <i data-lucide="search"></i>
                        <span>Go</span>
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                             stroke="white" class="theLoader">
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

                    <button id="tabulator-html-filter-reset" type="button" class="agm-btn agm-btn--muted">
                        <i data-lucide="rotate-ccw"></i>
                        <span>Reset</span>
                    </button>
                </form>

                <div class="agm-commission-actions">
                    <button data-comissionruleid="{{ $rule?->id ?? 0 }}" style="display: none;" id="generateComissionBtn" class="agm-btn agm-btn--primary" type="button">
                        <i data-lucide="pound-sterling"></i>
                        <span>Generate Comission</span>
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                             stroke="white" class="theLoader">
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

                    <a href="{{ route('agent.management.comission.list.export', [$rule?->semester_id ?? 0, $rule?->agent_user_id ?? 0, $rule?->code ?? '']) }}" class="agm-btn agm-btn--export">
                        <i data-lucide="download"></i>
                        <span>Export</span>
                    </a>
                </div>
            </div>

            <div class="agm-commission-table-wrap">
                <div id="agentComissionListTable"
                     data-semester="{{ $rule?->semester_id ?? 0 }}"
                     data-agent="{{ $rule?->agent_user_id ?? 0 }}"
                     data-code="{{ $rule?->code ?? '' }}"
                     class="agm-commission-table"></div>
            </div>
        </section>
    </div>

    <div id="comissionGenerateModal" class="modal agm-commission-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="agentRulesForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Agent Comission</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close"><i data-lucide="x"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="agm-commission-modal-table">
                            <table id="comissionsPaymentTable">
                                <thead>
                                    <tr>
                                        <th>Receipt ID</th>
                                        <th>Date</th>
                                        <th>Year</th>
                                        <th>Receipt Amount</th>
                                        <th>Comission Payable</th>
                                        <th>Paid Date</th>
                                        <th>Paid Amount</th>
                                        <th>Remittance Ref.</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" id="saveRuleBtn" class="btn btn-primary">
                            <i data-lucide="check"></i>
                            <span>Save</span>
                        </button>
                        <input type="hidden" name="agent_comission_rule_id" value="{{ $rule?->id ?? 0 }}">
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
@endsection

@section('script')
    @vite('resources/js/agent-management-comission.js')
@endsection
