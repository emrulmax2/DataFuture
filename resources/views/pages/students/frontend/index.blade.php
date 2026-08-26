@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Welcome &middot; First sign-in</div>
            <h1 class="spf-h1">Student information</h1>
            <div class="spf-page-head__sub">A few details before you reach your dashboard.</div>
        </div>
    </div>

    <div role="alert" class="alert spf-notice">
        <span class="spf-notice__icon"><i data-lucide="alert-octagon" class="w-[18px] h-[18px]"></i></span>
        <div class="spf-notice__text">We need these data for HESA (Higher Education Statistics Agency) submission. Please provide the following data best of your knowledge.</div>
        <button data-tw-dismiss="alert" type="button" aria-label="Close" class="spf-notice__close btn-close"><i data-lucide="x" class="w-3.5 h-3.5"></i></button>
    </div>

    <input type="hidden" id="studentId" name="student_id" value="{{ $studentData["student_id"] }}" />

    <!-- BEGIN: Wizard Layout -->
    {{-- The JS steps by sibling index: the header wrapper must stay the first
         child of .form-wizard and the three fieldsets its only other children. --}}
    <div class="form-wizard spf-wizard">
        <div class="form-wizard-header spf-wizard__head">
            <ul class="form-wizard-steps wizard spf-wsteps">
                <li class="form-wizard-step-item spf-wstep active">
                    <span class="spf-wstep__dot">1</span>
                    <span class="spf-wstep__tick"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    <span class="spf-wstep__label">Profile information</span>
                </li>
                <li class="form-wizard-step-item spf-wstep">
                    <span class="spf-wstep__dot">2</span>
                    <span class="spf-wstep__tick"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    <span class="spf-wstep__label">Address</span>
                </li>
                <li class="form-wizard-step-item spf-wstep">
                    <span class="spf-wstep__dot">3</span>
                    <span class="spf-wstep__tick"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                    <span class="spf-wstep__label">Consent &amp; finish</span>
                </li>
            </ul>
        </div>

        {{-- ── Step 1 · Profile information ─────────────────────────────── --}}
        <fieldset class="wizard-fieldset spf-wizard__body show">
            <form method="post" action="#" id="appicantFormStep_1" class="wizard-step-form">
                <h2 class="spf-h3 spf-wizard__body-title">Profile information</h2>
                <div class="spf-wgrid">
                    <div class="spf-wfield">
                        <label for="wiz_nationality" class="form-label spf-wlabel">Nationality <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#nationality-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_nationality" name="nationality" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($countries as $country)
                                <option {{ ($studentData["nationality"] == $country->id  ? "selected":"") }} value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-nationality text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="nationality-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please specify your nationality or the country of which you are a citizen.</div>
                            </div>
                        </div>
                    </div>
                    <div class="spf-wfield">
                        <label for="wiz_birth_country" class="form-label spf-wlabel">Country of birth <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#country-birth-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_birth_country" name="birth_country" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($countries as $country)
                                <option  {{ ($studentData["nationality"] == $country->id  ? "selected":"") }}  value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-birth_country text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="country-birth-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please specify your nationality or the country of which you are a citizen.</div>
                            </div>
                        </div>
                    </div>

                    <div class="spf-wfield">
                        <label for="wiz_ethnicity" class="form-label spf-wlabel">Ethnicity <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#ethnicity-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_ethnicity" name="ethnicity" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($ethnicities as $ethnicity)
                                <option {{ ($studentData["ethnicity"] == $ethnicity->id  ? "selected":"") }}  value="{{ $ethnicity->id }}">{{ $ethnicity->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-ethnicity text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="ethnicity-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please select your ethnicity or ethnic background from the options below. This information is used for statistical purposes and will remain confidential.</div>
                            </div>
                        </div>
                    </div>
                    <div class="spf-wfield">
                        <label for="wiz_religion" class="form-label spf-wlabel">Religion or belief <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#religion-belief-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_religion" name="religion" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($religions as $religion)
                                <option {{ ($studentData["religion"] == $religion->id  ? "selected":"") }} value="{{ $religion->id }}">{{ $religion->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-religion text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="religion-belief-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Religious belief based on your own self-assessment.</div>
                            </div>
                        </div>
                    </div>

                    <div class="spf-wfield">
                        <label for="wiz_sexual_orientation" class="form-label spf-wlabel">Sexual orientation <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#custom-content-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_sexual_orientation" name="sexual_orientation" class="w-full lccTom lcc-tom-select" >
                            <option value="">Please Select</option>
                            @foreach($sexualOrientations as $sexualOrientation)
                                <option {{ ($studentData["sexualOrientation"] == $sexualOrientation->id  ? "selected":"") }} value="{{ $sexualOrientation->id }}">{{ $sexualOrientation->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-sexual_orientation text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Sexual orientation based on your own self-assessment.</div>
                            </div>
                        </div>
                    </div>

                    <div class="spf-wfield">
                        <label for="wiz_gender" class="form-label spf-wlabel">Gender identity <span class="spf-req">*</span><i data-theme="light" data-tooltip-content="#gender-identity-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_gender" name="gender" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($genderIdentities as $genderIdentity)
                                <option  {{ ($studentData["hesa_gender_id"] == $genderIdentity->id  ? "selected":"") }} value="{{ $genderIdentity->id }}">{{ $genderIdentity->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-gender text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="gender-identity-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Gender Identity based on your own self-assessment, is your gender identity is the same as the gender originally assigned to them at birth.</div>
                            </div>
                        </div>
                    </div>
                    <div class="spf-wfield">
                        <label for="wiz_sex_identifier" class="form-label spf-wlabel">Sex identifier / gender <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#gender-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_sex_identifier" name="sex_identifier_id" class="w-full lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($sexIdentifiers as $sexIdentifier)
                                <option {{ ($studentData["sex_identifier_id"] == $sexIdentifier->id  ? "selected":"") }}  value="{{ $sexIdentifier->id }}">{{ $sexIdentifier->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-sex_identifier_id text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="gender-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please select the option that best represents your gender identity.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="spf-wizard__foot">
                    {{-- The JS fades every svg inside this button in and out as the
                         request runs, so the spinner must stay its only svg. --}}
                    <button type="button" class="spf-btn spf-btn--dark form-wizard-next-btn">
                        Save &amp; continue
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 svg_2">
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
                </div>
            </form>
        </fieldset>

        {{-- ── Step 2 · Address ─────────────────────────────────────────── --}}
        <fieldset class="wizard-fieldset spf-wizard__body">
            <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                {{-- The ids below are what the address flow in student-frontend.js
                     drives — spelling and nesting preserved exactly. --}}
                <div id="currentAdressQuestion">
                    <div class="spf-wq">
                        <p class="spf-wq__text">When you submitted your application, you supplied us with the address</p>
                        <p class="spf-wq__address">{{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{ !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }}</p>
                        <p class="spf-wq__text">Could you please confirm if this is the address at which you will be residing during your study term?</p>
                        <div class="spf-wq__actions">
                            <button id="agreeCurrentAddress" data-addressid="{{ $studentData["current_address"]->id }}" class="agreeCurrentAddress spf-btn spf-btn--dark spf-btn--choice">Yes, that's correct</button>
                            <button id="disagreeCurrentAddress" class="spf-btn spf-btn--choice">No, it has changed</button>
                        </div>
                    </div>
                </div>

                <div id="currentAddress" class="hidden">
                    <div class="spf-wsection">
                        <span class="spf-wsection__title">Term time address / correspondence address</span>
                        <i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i>
                        <div class="tooltip-content">
                            <div id="address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please confirm if your Term Time Address/Correspondence Address is still current as provided during the application process.</div>
                            </div>
                        </div>
                    </div>
                    <div id="currenAdress__no" class="hidden">
                        <input type="hidden" name="disagree_current_address" value="0" />
                        <div class="spf-wgrid spf-wgrid--3">
                            <div class="spf-wfield">
                                <label for="student_address_address_line_1" class="form-label spf-wlabel">Address line 1 <span class="spf-req">*</span></label>
                                <input id="student_address_address_line_1" autocomplete="off" type="text" name="address_line_1" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="student_address_address_line_2" class="form-label spf-wlabel">Address line 2</label>
                                <input id="student_address_address_line_2" type="text" autocomplete="off" name="address_line_2" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-address_line_2 text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="student_address_postal_zip_code" class="form-label spf-wlabel">Post code <span class="spf-req">*</span></label>
                                <input id="student_address_postal_zip_code" type="text" autocomplete="off" name="post_code" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-post_code text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="student_address_city" class="form-label spf-wlabel">City <span class="spf-req">*</span></label>
                                <input id="student_address_city" type="text" autocomplete="off" name="city" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-city text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="student_address_state_province_region" class="form-label spf-wlabel">State <span class="spf-req">*</span></label>
                                <input id="student_address_state_province_region" autocomplete="off" type="text" name="state" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-state text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="student_address_country" class="form-label spf-wlabel">Country <span class="spf-req">*</span></label>
                                <input id="student_address_country" type="text" autocomplete="off" name="country" value="" class="w-full spf-winput" />
                                <div class="acc__input-error error-country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div id="currenAddress__yes" class="hidden">
                        <input name="current_address_id" type="hidden" value="" />
                        <div class="spf-waddress">{{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{  !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }}</div>
                    </div>

                    <div id="accomodationType__next" class="spf-wfield spf-wfield--gap hidden">
                        <label for="wiz_accommodation_type" class="form-label spf-wlabel">Please select your current accommodation type <span class="spf-req">*</span> <i data-theme="light" data-tooltip-content="#accommodation-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></label>
                        <select id="wiz_accommodation_type" name="term_time_accommodation_type_id" class="w-full spf-winput">
                            @foreach($termTimeAccomadtionTypes as $termTimeAccomadtionType)
                                <option {{ ($studentData["term_time_accommodation_type_id"] == $termTimeAccomadtionType->id  ? "selected":"") }} value="{{ $termTimeAccomadtionType->id }}">{{ $termTimeAccomadtionType->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-term_time_accommodation_type_id text-danger mt-2"></div>
                        <div class="tooltip-content">
                            <div id="accommodation-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please specify your current term accomodation Type.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="askPermanentAdress" class="hidden">
                    <div class="spf-wq spf-wq--inline">
                        <p class="spf-wq__text">Is the address mentioned above your permanent residence address?</p>
                        <div class="spf-wq__actions">
                            <button id="agreePermanentAddress" data-addressid="{{ $studentData["current_address"]->id }}" class="spf-btn spf-btn--dark spf-btn--choice">Yes</button>
                            <button id="disagreePermanentAddress" class="spf-btn spf-btn--choice">No</button>
                        </div>
                        <div class="acc__input-error error-agreePermanentAddress text-danger mt-2"></div>
                        <input type="hidden" name="disagree_permanent_address" value="0" />
                    </div>
                </div>

                <div id="permanentAdressBox" class="hidden">
                    <div class="spf-wsection">
                        <span class="spf-wsection__title">Permanent address</span>
                        <i data-theme="light" data-tooltip-content="#permanent-address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i>
                        <div class="tooltip-content">
                            <div id="permanent-address-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please your term time address the same as your permanent address?</div>
                            </div>
                        </div>
                    </div>
                    <div id="permanentAddress__no" class="theAddressWrap hidden">
                        <div class="spf-wfield spf-wfield--full">
                            <label for="address_lookup" class="form-label spf-wlabel">Address lookup</label>
                            <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full spf-winput theAddressLookup" name="address_lookup">
                        </div>
                        <div class="spf-wgrid spf-wgrid--3">
                            <div class="spf-wfield">
                                <label for="permanent_address_line_1" class="form-label spf-wlabel">Address line 1</label>
                                <input id="permanent_address_line_1" type="text" name="permanent_address_line_1" class="w-full spf-winput address_line_1" />
                                <div class="acc__input-error error-permanent_address_line_1 text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="permanent_address_line_2" class="form-label spf-wlabel">Address line 2</label>
                                <input id="permanent_address_line_2" type="text" name="permanent_address_line_2" class="w-full spf-winput address_line_2" />
                                <div class="acc__input-error error-permanent_address_line_2 text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="permanent_post_code" class="form-label spf-wlabel">Post code</label>
                                <input id="permanent_post_code" type="text" name="permanent_post_code" class="w-full spf-winput postal_code" />
                                <div class="acc__input-error error-permanent_post_code text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="permanent_city" class="form-label spf-wlabel">City</label>
                                <input id="permanent_city" type="text" name="permanent_city" class="w-full spf-winput city" />
                                <div class="acc__input-error error-permanent_city text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="permanent_state" class="form-label spf-wlabel">State</label>
                                <input id="permanent_state" type="text" name="permanent_state" class="w-full spf-winput state" />
                                <div class="acc__input-error error-permanent_state text-danger mt-2"></div>
                            </div>
                            <div class="spf-wfield">
                                <label for="permanent_country" class="form-label spf-wlabel">Country</label>
                                <input id="permanent_country" type="text" name="permanent_country" class="w-full spf-winput country" />
                                <div class="acc__input-error error-permanent_country text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    {{-- The JS writes the confirmed address into the div below. --}}
                    <div id="permanentAddress__yes" class="hidden">
                        <input name="permanent_address_id" type="hidden" value="" />
                        <div class="spf-waddress">{{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{ !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }}</div>
                    </div>

                    <div class="spf-wgrid spf-wfield--gap">
                        <div class="spf-wfield">
                            <label for="wiz_permanent_country" class="form-label spf-wlabel">Please select your current permanent country <span class="spf-req">*</span></label>
                            <select id="wiz_permanent_country" name="permanent_country_id" class="w-full spf-winput">
                                <option value="">Please Select</option>
                                @foreach($pCountries as $pCountry)
                                    <option {{ ((($studentData["permanent_country_id"] == $pCountry->id && $studentData["permanent_country_id"] != 217) || $pCountry->id ==76)  ? "selected":"") }} value="{{ $pCountry->id }}">{{ $pCountry->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-permanent_country_id text-danger mt-2"></div>
                        </div>
                        <div class="spf-wfield">
                            <label for="permanent_post_code_new" class="form-label spf-wlabel">Permanent post code <span class="spf-req">*</span></label>
                            <input id="permanent_post_code_new" type="text" autocomplete="off" name="permanent_post_code_new" value=" {{ !empty($studentData["current_address"]->post_code && !isset($studentData["permanent_post_code_new"])) ? $studentData["current_address"]->post_code : $studentData["permanent_post_code_new"] }}" class="w-full spf-winput" />
                            <div class="acc__input-error error-permanent_post_code_new text-danger mt-2"></div>
                        </div>
                    </div>
                </div>

                <div class="spf-wizard__foot">
                    <button type="button" class="spf-btn form-wizard-previous-btn">&larr; Back</button>
                    <button id="form2SaveButton" type="button" class="spf-btn spf-btn--dark form-wizard-next-btn hidden">
                        Save &amp; continue
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 svg_2">
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
                </div>
            </form>
        </fieldset>

        {{-- ── Step 3 · Consent & finish ────────────────────────────────── --}}
        <fieldset class="wizard-fieldset spf-wizard__body">
            <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                <input type="hidden" name="url" value="{{ route('students.dashboard') }}"/>

                <h2 class="spf-h3 spf-wizard__body-title">Consent &amp; finish</h2>
                <p class="spf-wintro">
                    We kindly request your permission for email and SMS communications, with a focus on safeguarding your privacy and tailoring messages to your preferences. To grant permission, please click below.
                    <i data-theme="light" data-tooltip-content="#consent-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i>
                </p>
                <div class="tooltip-content">
                    <div id="consent-tooltip" class="relative flex items-center py-1">
                        <div class="text-slate-500 dark:text-slate-400">By accepting the terms and conditions, you have already consented to receiving essential correspondence from the college. Additionally, please confirm your preferences for the following.</div>
                    </div>
                </div>

                <div class="spf-wconsents">
                    @foreach ($consents as $consent)
                        @if($consent->is_required=="Yes")
                            <label class="spf-switchrow">
                                <input type="checkbox" name="consent_number[]" value="{{ $consent->id }}" class="spf-switch" />
                                <span class="spf-switchrow__text">{{ $consent->name }} <i data-theme="light" data-tooltip-content="#consent-tooltip-{{ $consent->id }}" data-trigger="click" data-lucide="help-circle" class="tooltip spf-whelp"></i></span>
                            </label>
                            <div class="tooltip-content">
                                <div id="consent-tooltip-{{ $consent->id }}" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">{{ $consent->description }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="spf-wizard__foot">
                    <button type="button" class="spf-btn form-wizard-previous-btn">&larr; Back</button>
                    <button type="button" class="spf-btn spf-btn--dark form-wizard-next-btn">
                        Submit
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 svg_2">
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
                </div>
            </form>
        </fieldset>
    </div>
    <!-- END: Wizard Layout -->

    @include('pages.students.frontend.modals.first-login.index')
@endsection


@section('script')
<script>(function(n,t,i,r){var u,f;n[i]=n[i]||{},n[i].initial={accountCode:"INDIV65018",host:"INDIV65018.pcapredict.com"},n[i].on=n[i].on||function(){(n[i].onq=n[i].onq||[]).push(arguments)},u=t.createElement("script"),u.async=!0,u.src=r,f=t.getElementsByTagName("script")[0],f.parentNode.insertBefore(u,f)})(window,document,"pca","//INDIV65018.pcapredict.com/js/sensor.js")</script>
    @vite('resources/js/student-frontend.js')

@endsection
