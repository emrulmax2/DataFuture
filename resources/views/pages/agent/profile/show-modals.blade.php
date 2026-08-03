<div id="addressModal" class="modal agm-agent-modal agm-profile-address-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add Address</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="agm-agent-form-grid agm-agent-form-grid--single theAddressWrap" id="addressStart">
                        <div class="agm-agent-form-field agm-agent-form-field--wide">
                            <label for="address_lookup">Address Lookup</label>
                            <div class="agm-agent-input-icon">
                                <i data-lucide="search"></i>
                                <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control theAddressLookup" name="address_lookup">
                            </div>
                        </div>
                        <div class="agm-agent-form-field agm-agent-form-field--wide">
                            <label for="student_address_address_line_1">Address Line 1 <span>*</span></label>
                            <input type="text" placeholder="ADDRESS LINE 1" id="student_address_address_line_1" autocomplete="off" class="form-control uppercase inputUppercase address_line_1" name="student_address_address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1"></div>
                        </div>
                        <div class="agm-agent-form-field agm-agent-form-field--wide">
                            <label for="student_address_address_line_2">Address Line 2</label>
                            <input type="text" placeholder="ADDRESS LINE 2 (OPTIONAL)" id="student_address_address_line_2" autocomplete="off" class="form-control uppercase inputUppercase address_line_2" name="student_address_address_line_2">
                        </div>
                        <div class="agm-agent-form-field">
                            <label for="student_address_city">City / Town <span>*</span></label>
                            <input type="text" placeholder="CITY / TOWN" id="student_address_city" class="form-control uppercase inputUppercase city" name="student_address_city">
                            <div class="acc__input-error error-student_address_city"></div>
                        </div>
                        <div class="agm-agent-form-field">
                            <label for="student_address_postal_zip_code">Post Code <span>*</span></label>
                            <input type="text" placeholder="POST CODE" id="student_address_postal_zip_code" class="form-control uppercase inputUppercase postal_code" name="student_address_postal_zip_code">
                            <div class="acc__input-error error-student_address_postal_zip_code"></div>
                        </div>
                        <div class="agm-agent-form-field">
                            <label for="student_address_country">Country <span>*</span></label>
                            <input type="text" placeholder="COUNTRY" id="student_address_country" class="form-control uppercase inputUppercase country" name="student_address_country">
                            <div class="acc__input-error error-student_address_country"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="insertAddress" class="btn btn-primary">
                        <i data-lucide="plus"></i>
                        Add Address
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

                    <input type="hidden" name="address_id" value="{{ (int) ($employee->address_id ?? 0) }}"/>
                    <input type="hidden" name="place" value="#employeeAddress"/>
                    <input type="hidden" id="agentId" name="id" value="{{ $employee->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="editContactModal" class="modal agm-agent-modal agm-profile-contact-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editContactModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Edit Contact</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="agm-agent-form-grid agm-agent-form-grid--single">
                        <div class="agm-agent-form-field agm-agent-form-field--wide">
                            <label for="emailPersonal">Email (use for login) <span>*</span></label>
                            <div class="agm-agent-input-icon">
                                <i data-lucide="mail"></i>
                                <input type="email" id="emailPersonal" name="email" value="{{ $employee->AgentUser->email ?? $employee->email }}" class="form-control email" placeholder="Email">
                            </div>
                            <div id="error-emailPersonal" class="acc__input-error error-email"></div>
                        </div>
                        <div class="agm-agent-form-field agm-agent-form-field--wide">
                            <label for="mobile">Mobile <span>*</span></label>
                            <div class="agm-agent-input-icon">
                                <i data-lucide="phone"></i>
                                <input id="mobile" type="text" class="form-control mobile" value="{{ $employee->mobile }}" name="mobile" aria-label="Mobile">
                            </div>
                            <div class="acc__input-error error-mobile"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="update" class="btn btn-primary">
                        <i data-lucide="check"></i>
                        Update
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
                    <input type="hidden" name="id" value="{{ $employee->id }}" />
                </div>
            </div>
        </form>
    </div>
</div>

@unless($hideAgentProfileSuccessModal ?? false)
    <div id="successModal" class="modal agm-agent-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endunless
