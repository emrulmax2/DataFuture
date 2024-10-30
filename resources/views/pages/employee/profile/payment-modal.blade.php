<!-- BEGIN: Add Modal -->
<div id="addEmployeePaymentSettingModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="addEmployeePaymentSettingForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Payment Basic Settings</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6 sm:col-span-4">
                            <label for="pay_frequency" class="form-label">Pay Frequency <span class="text-danger">*</span></label>
                            <select id="pay_frequency" name="pay_frequency" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Weekly">Weekly</option>
                            </select>
                            <div class="acc__input-error error-pay_frequency text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <label for="tax_code" class="form-label">Tax Code <span class="text-danger">*</span></label>
                            <input type="text" id="tax_code" name="tax_code" class="form-control w-full">
                            <div class="acc__input-error error-tax_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select id="payment_method" name="payment_method" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option {{ (isset($employee->bank->id) && $employee->bank->id > 0 ? 'Selected' : '') }} value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                            <div class="acc__input-error error-payment_method text-danger mt-2"></div>
                        </div>
                        {{-- Bank Details --}}
                        <div class="col-span-12 bankDetailsArea" style="display: {{ (isset($employee->bank->id) && $employee->bank->id > 0 ? 'block' : 'none') }};">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ (isset($employee->bank->beneficiary) && !empty($employee->bank->beneficiary) ? $employee->bank->beneficiary : '') }}" id="beneficiary" name="beneficiary" class="form-control w-full">
                                    <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ (isset($employee->bank->sort_code) && !empty($employee->bank->sort_code) ? $employee->bank->sort_code : '') }}" id="sort_code" name="sort_code" class="form-control w-full">
                                    <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ (isset($employee->bank->ac_no) && !empty($employee->bank->ac_no) ? $employee->bank->ac_no : '') }}" id="ac_no" name="ac_no" maxlength="8" minlength="8" class="form-control w-full">
                                    <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                                </div>
                                <input type="hidden" name="employee_bank_detail_id" value="{{ (isset($employee->bank->id) && $employee->bank->id > 0 ? $employee->bank->id : '0') }}"/>
                            </div>
                        </div>
                        {{-- Bank Details --}}
                        
                        <div class="col-span-12">
                            <label for="subject_to_clockin" class="form-label">Subject To Clockin</label>
                            <div class="form-check form-switch">
                                <input id="subject_to_clockin" name="subject_to_clockin" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        {{-- Hour Authorised By --}}
                        <div class="col-span-12 hourAuthorisedByArea" style="display: none;">
                            <label for="hour_authorised_by" class="form-label">Hour Authorised By <span class="text-danger">*</span></label>
                            <select id="hour_authorised_by" name="hour_authorised_by[]" multiple class=" tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(!empty($users) && $users->count() > 0)
                                    @foreach($users as $usr)
                                        <option value="{{ $usr->id }}">{{ $usr->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-hour_authorised_by text-danger mt-2"></div>
                        </div>
                        {{-- Hour Authorised By --}}

                        <div class="col-span-12">
                            <label for="holiday_entitled" class="form-label">Holiday Entitlement</label>
                            <div class="form-check form-switch">
                                <input id="holiday_entitled" name="holiday_entitled" value="1" class="form-check-input" type="checkbox">
                            </div>
                            <div class="acc__input-error error-holiday_entitled text-danger mt-2"></div>
                        </div>
                        {{-- Holiday Entitled --}}
                        <div class="col-span-12 holidayEntitlementArea" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="holiday_base" class="form-label">Holiday Base <span class="text-danger">*</span></label>
                                    <input type="number" step="any" value="" id="holiday_base" name="holiday_base" class="form-control w-full">
                                    <div class="acc__input-error error-holiday_base text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="bank_holiday_auto_book" class="form-label">Bank Holiday Auto Book <span class="text-danger">*</span></label>
                                    <div class="form-check form-switch">
                                        <input id="bank_holiday_auto_book" name="bank_holiday_auto_book" value="1" class="form-check-input" type="checkbox">
                                    </div>
                                    <div class="acc__input-error error-bank_holiday_auto_book text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="holiday_authorised_by" class="form-label">Holiday Authorised By <span class="text-danger">*</span></label>
                                    <select id="holiday_authorised_by" name="holiday_authorised_by[]" multiple class=" tom-selects w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($users) && $users->count() > 0)
                                            @foreach($users as $usr)
                                                <option value="{{ $usr->id }}">{{ $usr->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-holiday_authorised_by text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Holiday Entitled --}}

                        <div class="col-span-12">
                            <label for="pension_enrolled" class="form-label">Pension Enrolled</label>
                            <div class="form-check form-switch">
                                <input id="pension_enrolled" name="pension_enrolled" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        {{-- Penssion Enrolled --}}
                        <div class="col-span-12 penssionEnrolledArea" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="scheme_name" class="form-label">Scheme Name <span class="text-danger">*</span></label>
                                    <select id="employee_info_penssion_scheme_id" name="employee_info_penssion_scheme_id" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($schemes) && $schemes->count() > 0)
                                            @foreach($schemes as $scm)
                                                <option value="{{ $scm->id }}">{{ $scm->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-employee_info_penssion_scheme_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="joining_date" class="form-label">Date Joined <span class="text-danger">*</span></label>
                                    <input type="text" id="joining_date" name="joining_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-joining_date text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="date_left" class="form-label">Date Left</label>
                                    <input type="text" id="date_left" name="date_left" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-date_left text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Penssion Enrolled --}}

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePBS" class="btn btn-primary w-auto">     
                        Update Settings                      
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="employee_payment_setting_id" value="{{ (isset($employee->payment->id) && $employee->payment->id > 0 ? $employee->payment->id : 0) }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->

<!-- BEGIN: Edit Modal -->
<div id="editEmployeePaymentSettingModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editEmployeePaymentSettingForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Payment Basic Settings</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6 sm:col-span-4">
                            <label for="edit_pay_frequency" class="form-label">Pay Frequency <span class="text-danger">*</span></label>
                            <select id="edit_pay_frequency" name="pay_frequency" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option {{ (isset($employee->payment->pay_frequency) && $employee->payment->pay_frequency == 'Monthly' ? 'selected' : '') }} value="Monthly">Monthly</option>
                                <option {{ (isset($employee->payment->pay_frequency) && $employee->payment->pay_frequency == 'Weekly' ? 'selected' : '') }} value="Weekly">Weekly</option>
                            </select>
                            <div class="acc__input-error error-pay_frequency text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <label for="edit_tax_code" class="form-label">Tax Code <span class="text-danger">*</span></label>
                            <input type="text" value="{{ (isset($employee->payment->tax_code) ? $employee->payment->tax_code : '') }}" id="edit_tax_code" name="tax_code" class="form-control w-full">
                            <div class="acc__input-error error-tax_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <label for="edit_payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select id="edit_payment_method" name="payment_method" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option {{ (isset($employee->payment->payment_method) && $employee->payment->payment_method == 'Bank Transfer' ? 'selected' : '') }} value="Bank Transfer">Bank Transfer</option>
                                <option {{ (isset($employee->payment->payment_method) && $employee->payment->payment_method == 'Cash' ? 'selected' : '') }} value="Cash">Cash</option>
                                <option {{ (isset($employee->payment->payment_method) && $employee->payment->payment_method == 'Cheque' ? 'selected' : '') }} value="Cheque">Cheque</option>
                            </select>
                            <div class="acc__input-error error-payment_method text-danger mt-2"></div>
                        </div>
                        
                        <div class="col-span-12">
                            <label for="edit_subject_to_clockin" class="form-label">Subject To Clockin</label>
                            <div class="form-check form-switch">
                                <input id="edit_subject_to_clockin" {{ (isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes' ? 'checked' : '') }} name="subject_to_clockin" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        {{-- Hour Authorised By --}}
                        <div class="col-span-12 hourAuthorisedByArea" style="display: {{ (isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes' ? 'block' : 'none') }};">
                            <label for="edit_hour_authorised_by" class="form-label">Hour Authorised By <span class="text-danger">*</span></label>
                            <select id="edit_hour_authorised_by" name="hour_authorised_by[]" multiple class=" tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(!empty($users) && $users->count() > 0)
                                    @foreach($users as $usr)
                                        <option {{ (in_array($usr->id, $hourAuthIds) ? 'Selected' : '') }} value="{{ $usr->id }}">{{ $usr->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-hour_authorised_by text-danger mt-2"></div>
                        </div>
                        {{-- Hour Authorised By --}}

                        <div class="col-span-12">
                            <label for="edit_holiday_entitled" class="form-label">Holiday Entitlement</label>
                            <div class="form-check form-switch">
                                <input id="edit_holiday_entitled" name="holiday_entitled" {{ (isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes' ? 'checked' : '') }} value="1" class="form-check-input" type="checkbox">
                            </div>
                            <div class="acc__input-error error-holiday_entitled text-danger mt-2"></div>
                        </div>
                        {{-- Holiday Entitled --}}
                        <div class="col-span-12 holidayEntitlementArea" style="display: {{ (isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes' ? 'block' : 'none') }};">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="edit_holiday_base" class="form-label">Holiday Base <span class="text-danger">*</span></label>
                                    <input type="number" step="any" value="{{ (isset($employee->payment->holiday_base) ? $employee->payment->holiday_base : '') }}" id="edit_holiday_base" name="holiday_base" class="form-control w-full">
                                    <div class="acc__input-error error-holiday_base text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="edit_bank_holiday_auto_book" class="form-label">Bank Holiday Auto Book <span class="text-danger">*</span></label>
                                    <div class="form-check form-switch">
                                        <input id="edit_bank_holiday_auto_book" {{ (isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' ? 'checked' : '') }} name="bank_holiday_auto_book" value="1" class="form-check-input" type="checkbox">
                                    </div>
                                    <div class="acc__input-error error-bank_holiday_auto_book text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="edit_holiday_authorised_by" class="form-label">Holiday Authorised By <span class="text-danger">*</span></label>
                                    <select id="edit_holiday_authorised_by" name="holiday_authorised_by[]" multiple class=" tom-selects w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($users) && $users->count() > 0)
                                            @foreach($users as $usr)
                                                <option {{ (in_array($usr->id, $holidayAuthIds) ? 'Selected' : '') }} value="{{ $usr->id }}">{{ $usr->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-holiday_authorised_by text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                        {{-- Holiday Entitled --}}

                        <div class="col-span-12">
                            <label for="edit_pension_enrolled" class="form-label">Pension Enrolled</label>
                            <div class="form-check form-switch">
                                <input {{ (isset($employee->payment->pension_enrolled) && $employee->payment->pension_enrolled == 'Yes' ? 'checked' : '') }} id="edit_pension_enrolled" name="pension_enrolled" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updatePBS" class="btn btn-primary w-auto">     
                        Update Settings                      
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="employee_payment_setting_id" value="{{ (isset($employee->payment->id) && $employee->payment->id > 0 ? $employee->payment->id : 0) }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->

<!-- BEGIN: Add Bank Modal -->
<div id="addBankModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addBankForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Bank</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                        <input type="text" value="" id="beneficiary" name="beneficiary" class="form-control w-full">
                        <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                        <input type="text" value="" id="sort_code" name="sort_code" class="form-control w-full">
                        <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" value="" id="ac_no" minlength="8" maxlength="8" name="ac_no" class="form-control w-full">
                        <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                        <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEBNK" class="btn btn-primary w-auto">     
                        Add Bank                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Bank Modal -->

<!-- BEGIN: Edit Bank Modal -->
<div id="editBankModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editBankForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Bank</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="edit_beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                        <input type="text" value="" id="edit_beneficiary" name="beneficiary" class="form-control w-full">
                        <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="edit_sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                        <input type="text" value="" id="edit_sort_code" name="sort_code" class="form-control w-full">
                        <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="edit_ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" value="" id="edit_ac_no" minlength="8" maxlength="8" name="ac_no" class="form-control w-full">
                        <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                        <input id="edit_active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEBNK" class="btn btn-primary w-auto">     
                        Add Bank                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Bank Modal -->

<!-- BEGIN: Add Penssion Modal -->
<div id="addEmpPenssionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addEmpPenssionForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Penssion</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="add_employee_info_penssion_scheme_id" class="form-label">Scheme Name <span class="text-danger">*</span></label>
                        <select id="add_employee_info_penssion_scheme_id" name="employee_info_penssion_scheme_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($schemes) && $schemes->count() > 0)
                                @foreach($schemes as $scm)
                                    <option value="{{ $scm->id }}">{{ $scm->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-employee_info_penssion_scheme_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <label for="add_joining_date" class="form-label">Date Joined <span class="text-danger">*</span></label>
                        <input type="text" id="add_joining_date" name="joining_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-joining_date text-danger mt-2"></div>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <label for="add_date_left" class="form-label">Date Left</label>
                        <input type="text" id="add_date_left" name="date_left" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-date_left text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEPS" class="btn btn-primary w-auto">     
                        Save                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Pension Modal -->

<!-- BEGIN: Edit Penssion Modal -->
<div id="editEmpPenssionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editEmpPenssionForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Penssion</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="edit_employee_info_penssion_scheme_id" class="form-label">Scheme Name <span class="text-danger">*</span></label>
                        <select id="edit_employee_info_penssion_scheme_id" name="employee_info_penssion_scheme_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($schemes) && $schemes->count() > 0)
                                @foreach($schemes as $scm)
                                    <option value="{{ $scm->id }}">{{ $scm->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-employee_info_penssion_scheme_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <label for="edit_joining_date" class="form-label">Date Joined <span class="text-danger">*</span></label>
                        <input type="text" id="edit_joining_date" name="joining_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-joining_date text-danger mt-2"></div>
                    </div>
                    <div class="col-span-6 sm:col-span-4">
                        <label for="edit_date_left" class="form-label">Date Left</label>
                        <input type="text" id="edit_date_left" name="date_left" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                        <div class="acc__input-error error-date_left text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEPS" class="btn btn-primary w-auto">     
                        Update                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Pension Modal -->

<!-- BEGIN: Working Pattern Modal -->
<div id="addEmployeeWorkingPatternModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addEmployeeWorkingPatternForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Working Pattern</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="text" id="effective_from" name="effective_from" class="form-control w-full">
                            <div class="acc__input-error error-effective_from text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="end_to" class="form-label">End Date</label>
                            <input type="text" id="end_to" name="end_to" class="form-control w-full end_to_date">
                            <div class="acc__input-error error-end_to text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contracted_hour" class="form-label">Contracted Hour <span class="text-danger">*</span></label>
                            <input type="text" id="contracted_hour" name="contracted_hour" placeholder="00:00" class="form-control w-full">
                            <div class="acc__input-error error-contracted_hour text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
                            <input type="number" step="any" id="salary" name="salary" class="form-control w-full">
                            <div class="acc__input-error error-salary text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <label for="hourly_rate" class="form-label">Hourly Rate <span class="text-danger">*</span></label>
                            <input type="number" step="any" readonly id="hourly_rate" name="hourly_rate" class="form-control w-full">
                            <div class="acc__input-error error-hourly_rate text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                        <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEWP" class="btn btn-primary w-auto">     
                        Save                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Working Pattern Modal -->

<!-- BEGIN: Edit Working Pattern Modal -->
<div id="editEmployeeWorkingPatternModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editEmployeeWorkingPatternForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Working Pattern</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="text" id="edit_effective_from" name="effective_from" class="form-control w-full">
                            <div class="acc__input-error error-effective_from text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_end_to" class="form-label">End Date</label>
                            <input type="text" id="edit_end_to" name="end_to" class="form-control w-full">
                            <div class="acc__input-error error-end_to text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="contracted_hour" class="form-label">Contracted Hour <span class="text-danger">*</span></label>
                            <input type="text" id="contracted_hour" name="contracted_hour" placeholder="00:00" class="form-control w-full">
                            <div class="acc__input-error error-contracted_hour text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                        <input id="active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEWP" class="btn btn-primary w-auto">     
                        Update                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Working Pattern Modal -->

<!-- BEGIN: Add Calendar Modal -->
<div id="addCalendarModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="addCalendarForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Working Calendar</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <div class="weekDays">
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="1" id="weekDays_1"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_1">
                                        Monday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="2" id="weekDays_2"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_2">
                                        Tuesday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="3" id="weekDays_3"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_3">
                                        Wednesday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="4" id="weekDays_4"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_4">
                                        Thursday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="5" id="weekDays_5"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_5">
                                        Friday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="6" id="weekDays_6"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_6">
                                        Saturday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="7" id="weekDays_7"/>
                                    <label class="btn btn-outline-secondary" for="weekDays_7">
                                        Sunday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <table class="table table-bordered table-hover" id="staff_pay_info_table">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">Day</th>
                                        <th class="whitespace-nowrap">Start</th>
                                        <th class="whitespace-nowrap">End</th>
                                        <th class="whitespace-nowrap">Paid Break</th>
                                        <th class="whitespace-nowrap">Unpaid Break</th>
                                        <th class="whitespace-nowrap">Total Paid Hour</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="errorRow">
                                        <td colspan="6">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Selected days not found!
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5">Total</th>
                                        <th>
                                            <input type="text" class="timeMask form-control w-full weekTotal" name="weekTotal" placeholder="00:00" readonly/>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveEWPD" class="btn btn-primary w-auto">     
                        Save Pattern                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="employee_working_pattern_id" value="0"/>
                    <input type="hidden" name="contracted_hour" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Calendar Modal -->

<!-- BEGIN: Edit Calendar Modal -->
<div id="editCalendarModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editCalendarForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Working Calendar</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <div class="weekDays">
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="1" id="edit_weekDays_1"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_1">
                                        Monday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="2" id="edit_weekDays_2"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_2">
                                        Tuesday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="3" id="edit_weekDays_3"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_3">
                                        Wednesday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="4" id="edit_weekDays_4"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_4">
                                        Thursday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="5" id="edit_weekDays_5"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_5">
                                        Friday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="6" id="edit_weekDays_6"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_6">
                                        Saturday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                                <div class="weekDay">
                                    <input type="checkbox" class="weekDayStat" name="weekDays[]" value="7" id="edit_weekDays_7"/>
                                    <label class="btn btn-outline-secondary" for="edit_weekDays_7">
                                        Sunday 
                                        <i data-lucide="x" class="w-3 h-3 ml-2 closeIcon"></i>
                                        <i data-lucide="check" class="w-3 h-3 ml-2 checkIcon"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12">
                            <table class="table table-bordered table-hover" id="edit_staff_pay_info_table">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">Day</th>
                                        <th class="whitespace-nowrap">Start</th>
                                        <th class="whitespace-nowrap">End</th>
                                        <th class="whitespace-nowrap">Paid Break</th>
                                        <th class="whitespace-nowrap">Unpaid Break</th>
                                        <th class="whitespace-nowrap">Total Paid Hour</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="errorRow" style="display: none;">
                                        <td colspan="6">
                                            <div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span>Selected days not found!</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5">Total</th>
                                        <th>
                                            <input type="text" class="timeMask form-control w-full weekTotal" name="weekTotal" placeholder="00:00" readonly/>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEWPD" class="btn btn-primary w-auto">     
                        Update Pattern                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="employee_working_pattern_id" value="0"/>
                    <input type="hidden" name="contracted_hour" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Calendar Modal -->

<!-- BEGIN: add Working Pattern Pay Modal -->
<div id="addEmployeePatternPayModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addEmployeePatternPayForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Working Pattern Pay</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_add_effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="text" id="pay_add_effective_from" name="effective_from" class="form-control w-full">
                            <div class="acc__input-error error-effective_from text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_add_end_to" class="form-label">End Date</label>
                            <input type="text" id="pay_add_end_to" name="end_to" class="form-control w-full">
                            <div class="acc__input-error error-end_to text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_add_contracted_hour" class="form-label">Contracted Hour <span class="text-danger">*</span></label>
                            <input type="text" id="pay_add_contracted_hour" readonly name="contracted_hour" placeholder="00:00" class="form-control w-full">
                            <div class="acc__input-error error-contracted_hour text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_add_salary" class="form-label">Salary <span class="text-danger">*</span></label>
                            <input type="number" step="any" id="pay_add_salary" name="salary" class="form-control w-full">
                            <div class="acc__input-error error-salary text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <label for="pay_add_hourly_rate" class="form-label">Hourly Rate <span class="text-danger">*</span></label>
                            <input type="number" step="any" readonly id="pay_add_hourly_rate" name="hourly_rate" class="form-control w-full">
                            <div class="acc__input-error error-hourly_rate text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="pay_add_active">Active</label>
                        <input id="pay_add_active" class="form-check-input m-0" checked name="active" value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="addEWPPAY" class="btn btn-primary w-auto">     
                        Save                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="employee_working_pattern_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: add Working Pattern Pay Modal -->

<!-- BEGIN: Edit Working Pattern Pay Modal -->
<div id="editEmployeePatternPayModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editEmployeePatternPayForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Working Pattern Pay</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_edit_effective_from" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="text" id="pay_edit_effective_from" name="effective_from" class="form-control w-full">
                            <div class="acc__input-error error-effective_from text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_edit_end_to" class="form-label">End Date</label>
                            <input type="text" id="pay_edit_end_to" name="end_to" class="form-control w-full">
                            <div class="acc__input-error error-end_to text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_contracted_hour" class="form-label">Contracted Hour <span class="text-danger">*</span></label>
                            <input type="text" id="pay_contracted_hour" readonly name="contracted_hour" placeholder="00:00" class="form-control w-full">
                            <div class="acc__input-error error-contracted_hour text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="pay_salary" class="form-label">Salary <span class="text-danger">*</span></label>
                            <input type="number" step="any" id="pay_salary" name="salary" class="form-control w-full">
                            <div class="acc__input-error error-salary text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-12">
                            <label for="pay_hourly_rate" class="form-label">Hourly Rate <span class="text-danger">*</span></label>
                            <input type="number" step="any" readonly id="pay_hourly_rate" name="hourly_rate" class="form-control w-full">
                            <div class="acc__input-error error-hourly_rate text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="pay_active">Active</label>
                        <input id="pay_active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateEWPPAY" class="btn btn-primary w-auto">     
                        Update                   
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="id" value="0"/>
                    <input type="hidden" name="employee_working_pattern_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Working Pattern Pay Modal -->


<!-- BEGIN: Success Modal Content -->
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
                    <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Success Modal Content -->
<div id="successPayModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successPayModalTitle"></div>
                    <div class="text-slate-500 mt-2 successPayModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-auto">No, I Don't</button>
                    <button type="button" data-pattern="0" class="btn btn-danger successPayAdder w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Success Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 warningModalTitle"></div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Delete Confirm Modal Content -->
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
<!-- END: Delete Confirm Modal Content -->