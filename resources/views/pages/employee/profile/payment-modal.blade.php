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
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                            <div class="acc__input-error error-payment_method text-danger mt-2"></div>
                        </div>
                        {{-- Bank Details --}}
                        <div class="col-span-12 bankDetailsArea" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                                    <input type="text" value="" id="beneficiary" name="beneficiary" class="form-control w-full">
                                    <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                                    <input type="text" value="" id="sort_code" name="sort_code" class="form-control w-full">
                                    <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6 sm:col-span-4">
                                    <label for="ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" value="" id="ac_no" name="ac_no" maxlength="8" minlength="8" class="form-control w-full">
                                    <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                                </div>
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
                                        <option value="{{ $usr->id }}">{{ $usr->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-hour_authorised_by text-danger mt-2"></div>
                        </div>
                        {{-- Hour Authorised By --}}

                        <div class="col-span-12">
                            <label for="holiday_entitled" class="form-label">Holiday Entitled</label>
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
                                                <option value="{{ $usr->id }}">{{ $usr->name }}</option>
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
                        <input type="text" value="" id="ac_no" name="ac_no" class="form-control w-full">
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
                        <input type="text" value="" id="edit_ac_no" name="ac_no" class="form-control w-full">
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