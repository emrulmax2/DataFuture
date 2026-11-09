{{--
    Add / Edit employment history.

    The design's wide dialog: a three-up field grid, the organisation address
    inline, then the reference block under its own subheading.

    Two things differ from the design on purpose:
      * the address is the structured `.theAddressWrap` lookup, not one free
        text line - the table and `applicant_employments` keep line 1/2, city,
        state, post code and country separately, and a single field would
        throw that away. It is inline here for the same reason it is inline on
        the page: `INTAddressLookUps()` loops every wrap, so the old
        "Add Address" popup is no longer needed.
      * start and end are MM-YYYY, which is what the column holds, so they
        carry `monthYearMask` (00-0000) rather than the design's full date,
        and open a month-only Litepicker (`applicationMonthpicker`). A start
        date cannot be in the future (`data-no-future`); an end date can.

    Hooks kept: modal and form ids, `saveEmpHistory` / `updateEmpHistory` with
    their spinners, every field name, the `error-<field>` holders, the
    `continuing` checkbox the JS enables/disables `end_date` from, and the
    hidden `applicant_id` / `id` / `ref_id`.
--}}
@php
    $apdEmpApplicantId = isset($apply->id) && $apply->id > 0 ? $apply->id : 0;
@endphp

@foreach([
    ['mode' => 'add',  'prefix' => '',      'modal' => 'addEmployementHistoryModal',  'form' => 'addEmployementHistoryForm',  'title' => 'Add employment history', 'btn' => 'saveEmpHistory',   'cta' => 'Save'],
    ['mode' => 'edit', 'prefix' => 'edit_', 'modal' => 'editEmployementHistoryModal', 'form' => 'editEmployementHistoryForm', 'title' => 'Edit employment',        'btn' => 'updateEmpHistory', 'cta' => 'Update'],
] as $apdE)
    <!-- BEGIN: {{ $apdE['title'] }} Modal -->
    <div id="{{ $apdE['modal'] }}" class="modal apd-modal-skin apd-modal-skin--wide" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="{{ $apdE['form'] }}" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="apd-modal__title">{{ $apdE['title'] }}</h3>
                        <button type="button" data-tw-dismiss="modal" class="apd-modal__x" aria-label="Close">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="apd-modal__stack">
                            <div class="apd-grid-3">
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}company_name">Organisation name <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}company_name" name="company_name" class="form-control apd-ctl company_name">
                                    <div class="acc__input-error error-company_name apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}company_phone">Organisation phone <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}company_phone" name="company_phone" class="form-control apd-ctl company_phone">
                                    <div class="acc__input-error error-company_phone apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}position">Position <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}position" name="position" class="form-control apd-ctl position">
                                    <div class="acc__input-error error-position apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}start_date">Start date <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}start_date" name="start_date" class="form-control apd-ctl monthYearMask applicationMonthpicker start_date" placeholder="MM-YYYY" autocomplete="off" data-no-future>
                                    <div class="acc__input-error error-start_date apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}end_date">End date</label>
                                    <input type="text" id="{{ $apdE['prefix'] }}end_date" name="end_date" class="form-control apd-ctl monthYearMask applicationMonthpicker end_date" placeholder="MM-YYYY" autocomplete="off">
                                    <div class="acc__input-error error-end_date apd-err text-danger"></div>
                                </div>
                                <div class="apd-field apd-field--bottom">
                                    <label class="apd-opt chk">
                                        <input class="sr" id="{{ $apdE['prefix'] }}continuing" type="checkbox" name="continuing" value="1">
                                        <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                                        <span>Continuing (current job)</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Inline lookup, the project's standard `.theAddressWrap` block. --}}
                            <div class="apd-field">
                                <label for="{{ $apdE['prefix'] }}employment_address_lookup">Organisation address</label>
                                <div id="{{ $apdE['mode'] }}EmpHistoryAddress" class="theAddressWrap apd-address">
                                    <div class="apd-field">
                                        <input type="text" id="{{ $apdE['prefix'] }}employment_address_lookup" class="form-control apd-ctl theAddressLookup" placeholder="e.g. E15 4BZ" autocomplete="off">
                                        <span class="apd-hint">UK postcode, then pick the address &mdash; or type it below.</span>
                                    </div>
                                    <div class="apd-grid-3">
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_line_1">Address line 1 <span class="text-danger">*</span></label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_line_1" name="employment_address_line_1" class="form-control apd-ctl address_line_1 employment_address_line_1">
                                            <div class="acc__input-error error-employment_address_line_1 apd-err text-danger"></div>
                                        </div>
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_line_2">Address line 2</label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_line_2" name="employment_address_line_2" class="form-control apd-ctl address_line_2 employment_address_line_2">
                                        </div>
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_city">City / Town <span class="text-danger">*</span></label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_city" name="employment_address_city" class="form-control apd-ctl city employment_address_city">
                                            <div class="acc__input-error error-employment_address_city apd-err text-danger"></div>
                                        </div>
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_state">State / County</label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_state" name="employment_address_state" class="form-control apd-ctl state employment_address_state">
                                        </div>
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_postal_zip_code">Post code <span class="text-danger">*</span></label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_postal_zip_code" name="employment_address_postal_zip_code" class="form-control apd-ctl postal_code employment_address_postal_zip_code">
                                            <div class="acc__input-error error-employment_address_postal_zip_code apd-err text-danger"></div>
                                        </div>
                                        <div class="apd-field">
                                            <label for="{{ $apdE['prefix'] }}employment_address_country">Country <span class="text-danger">*</span></label>
                                            <input type="text" id="{{ $apdE['prefix'] }}employment_address_country" name="employment_address_country" class="form-control apd-ctl country employment_address_country" value="United Kingdom">
                                            <div class="acc__input-error error-employment_address_country apd-err text-danger"></div>
                                        </div>
                                    </div>
                                    <input type="hidden" class="latitude" name="employment_address_latitude" value="">
                                    <input type="hidden" class="longitude" name="employment_address_longitude" value="">
                                </div>
                            </div>

                            <h4 class="apd-modal__sub">Reference at this organisation</h4>
                            <div class="apd-grid-3">
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}contact_name">Contact name <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}contact_name" name="contact_name" class="form-control apd-ctl contact_name">
                                    <div class="acc__input-error error-contact_name apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}contact_position">Contact position <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}contact_position" name="contact_position" class="form-control apd-ctl contact_position">
                                    <div class="acc__input-error error-contact_position apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}contact_phone">Contact phone <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdE['prefix'] }}contact_phone" name="contact_phone" class="form-control apd-ctl contact_phone">
                                    <div class="acc__input-error error-contact_phone apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdE['prefix'] }}contact_email">Contact email</label>
                                    <input type="email" id="{{ $apdE['prefix'] }}contact_email" name="contact_email" class="form-control apd-ctl contact_email">
                                    <div class="acc__input-error error-contact_email apd-err text-danger"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Cancel</button>
                        <button type="submit" id="{{ $apdE['btn'] }}" class="apd-btn apd-btn--primary">
                            {{ $apdE['cta'] }}
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-4 h-4 ml-2">
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
                        <input type="hidden" name="applicant_id" value="{{ $apdEmpApplicantId }}"/>
                        @if($apdE['mode'] === 'edit')
                            <input type="hidden" name="id" value="0"/>
                            <input type="hidden" name="ref_id" value="0"/>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: {{ $apdE['title'] }} Modal -->
@endforeach
