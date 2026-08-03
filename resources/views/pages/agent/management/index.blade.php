@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="agm-page">
        <section class="agm-hero">
            <div class="agm-hero__copy">
                <span class="agm-hero__icon">
                    <i data-lucide="heart-handshake"></i>
                </span>
                <div>
                    <span class="agm-eyebrow">Referrals &amp; Commission</span>
                    <h1>Agent Management</h1>
                    <p>Referral performance and commission rules by intake</p>
                </div>
            </div>

            <div class="agm-hero__actions">
                <a href="{{ route('agent.management.remittance') }}" class="agm-btn agm-btn--soft-gold">
                    <i data-lucide="badge-pound-sterling"></i>
                    <span>Remittance</span>
                </a>
                <a href="{{ route('agent-user.index') }}" class="agm-btn agm-btn--soft-teal">
                    <i data-lucide="user-cog"></i>
                    <span>Agents</span>
                </a>
                <a href="{{ route('agent.management') }}" class="agm-btn agm-btn--dark">
                    <i data-lucide="arrow-left"></i>
                    <span>Back to Management</span>
                </a>
            </div>
        </section>

        <section class="agm-filter">
            <div class="agm-step">
                <span class="agm-step__icon">
                    <i data-lucide="calendar"></i>
                </span>
                <div>
                    <small>Step 1</small>
                    <strong>Choose intake</strong>
                </div>
            </div>

            <form id="tabulatorFilterForm" class="agm-filter__form">
                <div class="agm-control">
                    <label for="semister_id">Intek Semester <span class="agm-required">*</span></label>
                    <select id="semister_id" name="semister_id" class="tom-selects">
                        <option value="">Please Select</option>
                        @if($semesters->count() > 0)
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <button id="tabulator-html-filter-go" type="button" class="agm-btn agm-btn--primary">
                    <i data-lucide="arrow-right"></i>
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
        </section>

        <section class="agm-results">
            <div class="agentRefListWrap">
                <div class="agm-empty">
                    <span class="agm-empty__icon">
                        <i data-lucide="calendar-days"></i>
                    </span>
                    <h2>No intake loaded yet</h2>
                    <p>Pick an <strong>Intake Semester</strong> above and press <strong>Go</strong> to load agent referrals, student counts and commission figures.</p>
                    <span class="agm-empty__chip">
                        <i data-lucide="info"></i>
                        Results are intake-specific
                    </span>
                </div>
            </div>
        </section>
    </div>

    <div id="agentRulesModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="agentRulesForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Agent Rule</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="agm-rule-grid">
                            <div class="agm-rule-field">
                                <label for="comission_mode">Commission <span class="agm-required">*</span></label>
                                <select id="comission_mode" name="comission_mode" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="1">Percentage</option>
                                    <option value="2">Fixed Amount</option>
                                </select>
                                <div class="acc__input-error error-comission_mode"></div>
                            </div>

                            <div class="agm-rule-field">
                                <label for="period">Period <span class="agm-required">*</span></label>
                                <select id="period" name="period" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    <option value="1">Every Year</option>
                                    <option value="2">Year 1</option>
                                </select>
                                <div class="acc__input-error error-period"></div>
                            </div>

                            <div class="agm-rule-field percentageWrap" style="display: none;">
                                <label for="percentage">Percentage <span class="agm-required">*</span></label>
                                <input id="percentage" type="text" name="percentage" class="form-control w-full" placeholder="Percentage">
                                <div class="acc__input-error error-percentage"></div>
                            </div>

                            <div class="agm-rule-field fixedAmountWrap" style="display: none;">
                                <label for="amount">Amount <span class="agm-required">*</span></label>
                                <input id="amount" step="any" type="number" name="amount" class="form-control w-full" placeholder="Amount">
                                <div class="acc__input-error error-amount"></div>
                            </div>

                            <div class="agm-rule-field--wide">
                                <span class="agm-rule-label">Payment <span class="agm-required">*</span></span>
                                <div class="agm-payment-options">
                                    <label class="agm-payment-option" for="payment_type_1">
                                        <input id="payment_type_1" type="radio" name="payment_type" value="1">
                                        <span class="agm-payment-card">
                                            <span class="agm-payment-card__mark">
                                                <i data-lucide="check"></i>
                                            </span>
                                            <span>
                                                <strong>Single Payment</strong>
                                                <small>Pay once per rule</small>
                                            </span>
                                        </span>
                                    </label>
                                    <label class="agm-payment-option" for="payment_type_2">
                                        <input id="payment_type_2" type="radio" name="payment_type" value="2">
                                        <span class="agm-payment-card">
                                            <span class="agm-payment-card__mark">
                                                <i data-lucide="check"></i>
                                            </span>
                                            <span>
                                                <strong>On Receipt</strong>
                                                <small>Release after receipt</small>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <div class="acc__input-error error-payment_type"></div>
                            </div>
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
                        <input type="hidden" name="agent_user_id" value="0">
                        <input type="hidden" name="code" value="">
                        <input type="hidden" name="semester_id" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
@endsection

@section('script')
    @vite('resources/js/agent-management.js')
@endsection
