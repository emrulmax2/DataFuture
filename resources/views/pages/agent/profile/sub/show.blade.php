@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
    <div class="agm-page agm-profile-page">
        @include('pages.agent.profile.show-info')

        <section class="agm-profile-panel agm-sub-agent-panel">
            <div class="agm-profile-panel__header">
                <div class="agm-section-title">
                    <span aria-hidden="true"></span>
                    <h2>Sub Agent List</h2>
                </div>

                <button data-tw-toggle="modal" data-tw-target="#addAgentModal" type="button" class="agm-btn agm-btn--primary">
                    <i data-lucide="plus"></i>
                    <span>Add Sub Agent</span>
                </button>
            </div>

            <div class="agm-agent-toolbar agm-agent-toolbar--profile">
                <form id="tabulatorFilterForm-Agent" class="agm-agent-toolbar__filters">
                    <div class="agm-agent-field agm-agent-field--query">
                        <label for="query-Agent">Query</label>
                        <div class="agm-agent-search-field">
                            <i data-lucide="search"></i>
                            <input id="query-Agent" name="query" type="text" placeholder="Search...">
                        </div>
                    </div>

                    <div class="agm-agent-field">
                        <label for="status-Agent">Status</label>
                        <div class="agm-agent-select">
                            <select id="status-Agent" name="status">
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                            <i data-lucide="chevron-down"></i>
                        </div>
                    </div>

                    <button id="tabulator-html-filter-go-Agent" type="button" class="agm-btn agm-btn--primary">
                        <i data-lucide="search"></i>
                        <span>Go</span>
                    </button>
                    <button id="tabulator-html-filter-reset-Agent" type="button" class="agm-btn agm-btn--muted">
                        <i data-lucide="rotate-ccw"></i>
                        <span>Reset</span>
                    </button>
                </form>

                <div class="agm-agent-toolbar__actions">
                    <button id="tabulator-print" type="button" class="agm-btn agm-btn--cream">
                        <i data-lucide="printer"></i>
                        <span>Print</span>
                    </button>
                    <div class="dropdown">
                        <button class="dropdown-toggle agm-btn agm-btn--export" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                            <i data-lucide="download"></i>
                            <span>Export</span>
                            <i data-lucide="chevron-down"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="agm-profile-table-wrap">
                <div id="agentTableId" data-id="{{ $employee->agent_user_id }}" class="agm-profile-table agm-agent-table agm-sub-agent-table"></div>
            </div>
        </section>
    </div>

    <div id="addAgentModal" class="modal agm-agent-modal agm-sub-agent-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addAgentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Add Sub Agent</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="agm-agent-form-grid">
                            <div class="agm-agent-form-field">
                                <label for="first_name">First name <span>*</span></label>
                                <input id="first_name" type="text" name="first_name" placeholder="First name">
                                <div class="acc__input-error error-first_name"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="last_name">Last name <span>*</span></label>
                                <input id="last_name" type="text" name="last_name" placeholder="Last name">
                                <div class="acc__input-error error-last_name"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="code">Referral Code <span>*</span></label>
                                <input id="code" type="text" value="{{ $unique }}" name="code" placeholder="Referral code">
                                <div class="acc__input-error error-code"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="organization">Organization <span>*</span></label>
                                <input id="organization" type="text" name="organization" placeholder="Organization">
                                <div class="acc__input-error error-organization"></div>
                            </div>
                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label for="email">Email <span>*</span></label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="mail"></i>
                                    <input id="email" type="email" name="email" placeholder="sub.agent@example.com">
                                </div>
                                <div class="acc__input-error error-email"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="password">Password <span>*</span></label>
                                <input id="password" type="password" autocomplete="off" class="password" name="password" placeholder="Password">
                                <div class="agm-agent-strength" data-strength-for="password">
                                    <span id="strength-1"></span>
                                    <span id="strength-2"></span>
                                    <span id="strength-3"></span>
                                    <span id="strength-4"></span>
                                </div>
                                <a href="javascript:;" data-theme="light" data-tooltip="custom-content-tooltip" data-trigger="click" class="tooltip agm-agent-password-help" title="What is a secure password?">
                                    <i data-lucide="info"></i>
                                    <span>What is a secure password?</span>
                                </a>
                                <div class="tooltip-content">
                                    <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                        <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                            <li><span class="low-upper-case">Lowercase and Uppercase</span></li>
                                            <li><span class="one-number">Number (0-9)</span></li>
                                            <li><span class="one-special-char">Special Character (!@#$%^&*)</span></li>
                                            <li><span class="eight-character">At least 8 characters</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="acc__input-error error-password"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="password_confirmation">Password Confirmation <span>*</span></label>
                                <input id="password_confirmation" type="password" autocomplete="off" name="password_confirmation" placeholder="Password Confirmation">
                                <div class="acc__input-error error-password_confirmation"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" id="saveAgent" class="btn btn-primary">
                            <i data-lucide="check"></i>
                            <span>Save</span>
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
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
                        <input name="parent_id" value="{{ $employee->AgentUser->id }}" type="hidden">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="editAgentModal" class="modal agm-agent-modal agm-sub-agent-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editAgentForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Edit Sub Agent</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="agm-agent-form-grid">
                            <div class="agm-agent-form-field">
                                <label for="first_name1">First name <span>*</span></label>
                                <input id="first_name1" type="text" name="first_name" placeholder="First name">
                                <div class="acc__input-error error-first_name"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="last_name1">Last name <span>*</span></label>
                                <input id="last_name1" type="text" name="last_name" placeholder="Last name">
                                <div class="acc__input-error error-last_name"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="code1">Referral Code <span>*</span></label>
                                <input id="code1" type="text" value="{{ $unique }}" name="code" placeholder="Referral code">
                                <div class="acc__input-error error-code"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="organization1">Organization <span>*</span></label>
                                <input id="organization1" type="text" name="organization" placeholder="Organization">
                                <div class="acc__input-error error-organization"></div>
                            </div>
                            <div class="agm-agent-form-field agm-agent-form-field--wide">
                                <label for="email1">
                                    Email <span>*</span>
                                    <strong id="verificationEmail" class="agm-agent-email-status"></strong>
                                </label>
                                <div class="agm-agent-input-icon">
                                    <i data-lucide="mail"></i>
                                    <input id="email1" type="email" name="email" placeholder="sub.agent@example.com">
                                </div>
                                <div class="acc__input-error error-email"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="password1">Password</label>
                                <input id="password1" type="password" autocomplete="off" class="password" name="password" placeholder="Password">
                                <div class="agm-agent-strength" data-strength-for="password1">
                                    <span id="strength-5"></span>
                                    <span id="strength-6"></span>
                                    <span id="strength-7"></span>
                                    <span id="strength-8"></span>
                                </div>
                                <a href="javascript:;" data-theme="light" data-tooltip="custom-content-tooltip-edit" data-trigger="click" class="tooltip agm-agent-password-help" title="What is a secure password?">
                                    <i data-lucide="info"></i>
                                    <span>What is a secure password?</span>
                                </a>
                                <div class="tooltip-content">
                                    <div id="custom-content-tooltip-edit" class="relative flex items-center py-1">
                                        <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                            <li><span class="low-upper-case">Lowercase and Uppercase</span></li>
                                            <li><span class="one-number">Number (0-9)</span></li>
                                            <li><span class="one-special-char">Special Character (!@#$%^&*)</span></li>
                                            <li><span class="eight-character">At least 8 characters</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="acc__input-error error-password"></div>
                            </div>
                            <div class="agm-agent-form-field">
                                <label for="password_confirmation1">Password Confirmation</label>
                                <input id="password_confirmation1" type="password" autocomplete="off" name="password_confirmation" placeholder="Password Confirmation">
                                <div class="acc__input-error error-password_confirmation"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                            <i data-lucide="x"></i>
                            <span>Cancel</span>
                        </button>
                        <button type="submit" id="updateAgent" class="btn btn-primary">
                            <i data-lucide="check"></i>
                            <span>Update</span>
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
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
                        <input type="hidden" name="id" value="0">
                        <input name="parent_id" value="" type="hidden">
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
                    <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal agm-agent-feedback-modal agm-agent-feedback-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="circle-alert"></i>
                    <h2 class="confModTitle">Are you sure?</h2>
                    <p class="confModDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" data-tw-dismiss="modal" class="agm-btn agm-btn--muted">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith agm-btn agm-btn--danger">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.agent.profile.show-modals')
@endsection

@section('script')
    @vite('resources/js/agent-global.js')
    @vite('resources/js/agent-profile.js')
    @vite('resources/js/sub-agent-crud.js')
@endsection
