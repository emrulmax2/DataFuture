@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="flex items-center mt-8">
        <h2 class="intro-y text-lg font-medium mr-auto">Student Information</h2>
    </div>
    <div role="alert" class="alert relative border rounded-md px-5 py-4 mt-2 border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 mb-2 flex items-center">
        <i data-lucide="alert-octagon" width="24" height="24" class="stroke-1.5  h-6 w-6 mr-2"></i>
        We need these data for HESA (Higher Education Statistics Agency) submission. Please provide the following data best of your knowledge.
        <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-lucide="x" width="24" height="24" class="stroke-1.5 h-4 w-4 h-4 w-4"></i></button>
    </div>
    <input type="hidden" id="studentId" name="student_id" value="{{ $studentData["student_id"] }}" />
    <!-- BEGIN: Wizard Layout -->
    <div class="form-wizard intro-y box py-10 sm:py-20 mt-5">
        <div class="form-wizard-header">
            <ul class="form-wizard-steps wizard relative before:hidden before:lg:block before:absolute before:w-[69%] before:h-[3px] before:top-0 before:bottom-0 before:mt-4 before:bg-slate-100 before:dark:bg-darkmode-400 flex flex-col lg:flex-row justify-center px-5 sm:px-20">
                <li class="intro-x lg:text-center flex items-center lg:block flex-1 z-10 form-wizard-step-item active">
                    <button class="w-10 h-10 rounded-full btn btn-primary">1</button>
                    <div class="lg:w-32 font-medium text-base lg:mt-3 ml-3 lg:mx-auto">Profile Information</div>
                </li>
                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">2</button>
                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Address</div>
                </li>
                <li class="intro-x lg:text-center flex items-center mt-5 lg:mt-0 lg:block flex-1 z-10 form-wizard-step-item">
                    <button class="w-10 h-10 rounded-full btn text-slate-500 bg-slate-100 dark:bg-darkmode-400 dark:border-darkmode-400">3</button>
                    <div class="lg:w-32 text-base lg:mt-3 ml-3 lg:mx-auto text-slate-600 dark:text-slate-400">Consent and Finish</div>
                </li>
                
            </ul>
        </div>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400 show"> 
            <form method="post" action="#" id="appicantFormStep_1" class="wizard-step-form">
            <div class="font-medium text-base">Profile Information</div>
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-4" class="form-label inline-flex">Nationality <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#nationality-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-4" name="nationality" class="w-full  lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($countries as $country)
                                <option {{ ($studentData["nationality"] == $country->id  ? "selected":"") }} value="{{ $country->id }}">{{ $country->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-nationality text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="nationality-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please specify your nationality or the country of which you are a citizen.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-4" class="form-label inline-flex">Country of Birth <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#country-birth-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-5" name="birth_country" class="w-full  lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($countries as $country)
                                <option  {{ ($studentData["nationality"] == $country->id  ? "selected":"") }}  value="{{ $country->id }}">{{ $country->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-birth_country text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="country-birth-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please specify your nationality or the country of which you are a citizen.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-4" class="form-label inline-flex">Ethnicity <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#ethnicity-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-6" name="ethnicity" class="w-full  lccTom lcc-tom-select">
                                <option value="">Please Select</option>
                            @foreach($ethnicities as $ethnicity)
                                <option {{ ($studentData["ethnicity"] == $ethnicity->id  ? "selected":"") }}  value="{{ $ethnicity->id }}">{{ $ethnicity->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-first_name text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="ethnicity-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please select your ethnicity or ethnic background from the options below. This information is used for statistical purposes and will remain confidential.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-3" class="form-label inline-flex">Religion or Belief /RELIGION <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#religion-belief-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-3" name="religion" class="w-full  lccTom lcc-tom-select">
                                <option value="">Please Select</option>
                            @foreach($religions as $religion)
                                <option {{ ($studentData["religion"] == $religion->id  ? "selected":"") }} value="{{ $religion->id }}">{{ $religion->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-religion text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="religion-belief-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Religious belief based on your own self-assessment.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-6 ">
                        <label for="input-wizard-1" class="form-label inline-flex">Sexual Orientation <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#custom-content-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-1" name="sexual_orientation" class="w-full lccTom lcc-tom-select" >
                            <option value="">Please Select</option>
                            @foreach($sexualOrientations as $sexualOrientation)
                                <option {{ ($studentData["sexualOrientation"] == $sexualOrientation->id  ? "selected":"") }} value="{{ $sexualOrientation->id }}">{{ $sexualOrientation->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-sexual_orientation text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">Sexual orientation based on your own self-assessment.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>
                    
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-2" class="form-label inline-flex">Gender identity <span class="text-danger"> *</span><i data-theme="light" data-tooltip-content="#gender-identity-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-2" name="gender" class="w-full  lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($genderIdentities as $genderIdentity)
                                <option  {{ ($studentData["hesa_gender_id"] == $genderIdentity->id  ? "selected":"") }} value="{{ $genderIdentity->id }}">{{ $genderIdentity->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-gender text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="gender-identity-tooltip" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">Gender Identity based on your own self-assessment, is your gender identity is the same as the gender originally assigned to them at birth.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-6">
                        <label for="input-wizard-4" class="form-label inline-flex">Sex identifier/Gender <span class="text-danger"> *</span> <i data-theme="light" data-tooltip-content="#gender-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                        <select id="data-7" name="sex_identifier_id" class="w-full  lccTom lcc-tom-select">
                            <option value="">Please Select</option>
                            @foreach($sexIdentifiers as $sexIdentifier)
                                <option {{ ($studentData["sex_identifier_id"] == $sexIdentifier->id  ? "selected":"") }}  value="{{ $sexIdentifier->id }}">{{ $sexIdentifier->name }}</option>              
                            @endforeach
                        </select>
                        <div class="acc__input-error error-sex_identifier_id text-danger mt-2"></div>
                        <!-- BEGIN: Custom Tooltip Content -->
                        <div class="tooltip-content">
                            <div id="gender-tooltip" class="relative flex items-center py-1">
                                <div class="text-slate-500 dark:text-slate-400">Please select the option that best represents your gender identity.</div>
                            </div>
                        </div>
                        <!-- END: Custom Tooltip Content -->
                    </div>
                    <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                        <button type="button" class="btn btn-primary w-auto form-wizard-next-btn">
                            Save &amp; Continue
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                </div>
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_2" class="wizard-step-form">
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-5">    
                    <div id="currentAdressQuestion"  class="col-span-12">
                        <div class="col-span-12">
                                <div class="intro-y col-span-12 py-1 text-center">
                                    <label for="input-wizard-4" class="form-label mr-2">When you submitted your application, you supplied us with the address <span class="ml-1 text-xl font-medium">{{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{ !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }}</span>. Could you please confirm if this is the address at which you will be residing during your study term?</label>
                                    <div class="col-span-12  mt-4">
                                    <button id="agreeCurrentAddress" data-addressid="{{ $studentData["current_address"]->id }}"  class="agreeCurrentAddress transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-2 w-32">Yes</button>
                                    <button id="disagreeCurrentAddress"   class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-2 w-32 ">No</button>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div id="currentAddress" class="hidden col-span-12">
                        <div class="font-medium text-base">
                            <label for="input-wizard-4" class="form-label inline-flex">Term Time Address/Correspondence Address<i data-theme="light" data-tooltip-content="#address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>

                            <!-- BEGIN: Custom Tooltip Content -->
                            <div class="tooltip-content">
                                <div id="address-tooltip" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">Please confirm if your Term Time Address/Correspondence Address is still current as provided during the application process.</div>
                                </div>
                            </div>
                            <!-- END: Custom Tooltip Content -->
                        </div>
                        <div id="currenAdress__no" class="hidden">
                                <input type="hidden" name="disagree_current_address" value="0" />

                                <div class="col-span-12">
                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_address_line_1" class="form-label inline-flex">Address Line 1 <span class="text-danger"> *</span></label>
                                            <input id="student_address_address_line_1" autocomplete="off" type="text" name="address_line_1" value="" class="w-full text-sm" />
                                            <div class="acc__input-error error-address_line_1 text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_address_line_2" class="form-label inline-flex">Address Line 2</label>
                                            <input id="student_address_address_line_2" type="text"  autocomplete="off" name="address_line_2" value="" class="w-full text-sm" />
                                            <div class="acc__input-error error-address_line_2 text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_postal_zip_code" class="form-label inline-flex">Post Code <span class="text-danger"> *</span></label>
                                            <input id="student_address_postal_zip_code" type="text"  autocomplete="off" name="post_code" value="" class="w-full text-sm" />
                                            <div class="acc__input-error error-post_code text-danger mt-2"></div>
                                        </div>
                                
                                </div>
                                <div class="col-span-12">
                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_city" class="form-label inline-flex">City <span class="text-danger"> *</span></label>
                                            <input id="student_address_city" type="text"  autocomplete="off" name="city" value="" class="w-full text-sm"  />
                                            <div class="acc__input-error error-city text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_state_province_region" class="form-label inline-flex">State <span class="text-danger"> *</span></label>
                                            <input id="student_address_state_province_region"  autocomplete="off" type="text" name="state" value="" class="w-full text-sm" />
                                            <div class="acc__input-error error-state text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="student_address_country" class="form-label inline-flex">Country <span class="text-danger"> *</span></label>
                                            <input id="student_address_country" type="text"  autocomplete="off" name="country" value="" class="w-full text-sm" />
                                            <div class="acc__input-error error-country text-danger mt-2"></div>
                                        </div>
                                    
                                </div>
                        </div>

                        <div id="currenAddress__yes" class="hidden">
                            <input name="current_address_id" type="hidden" value="" />
                            <div class="font-medium text-base"> {{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{  !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }}</div>
                        </div>
                        
                        <div id="accomodationType__next" class="intro-y col-span-12 sm:col-span-6 hidden my-10" >
                            <label for="input-wizard-4" class="form-label inline-flex">Please Select your current accomodation type <span class="text-danger">*</span> <i data-theme="light" data-tooltip-content="#nationality-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>
                            <select id="data-4" name="term_time_accommodation_type_id" class=" w-full ">
                                
                                @foreach($termTimeAccomadtionTypes as $termTimeAccomadtionType)
                                    <option {{ ($studentData["term_time_accommodation_type_id"] == $termTimeAccomadtionType->id  ? "selected":"") }} value="{{ $termTimeAccomadtionType->id }}">{{ $termTimeAccomadtionType->name }}</option>              
                                @endforeach
                            </select>
                            <!-- BEGIN: Custom Tooltip Content -->
                            <div class="tooltip-content">
                                <div id="nationality-tooltip" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">Please specify your current term accomodation Type.</div>
                                </div>
                            </div>
                            <!-- END: Custom Tooltip Content -->
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400 col-span-12"></div>
                    <div id="askPermanentAdress" class="col-span-12 hidden">
                        <div class="col-span-12">
                                <div class="intro-y col-span-12 py-1">
                                    <label for="input-wizard-4" class="form-label inline-flex mr-2">Is the address mentioned above is your permanent residence address?</label>
                                    <button id="agreePermanentAddress" data-addressid="{{ $studentData["current_address"]->id }}" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-2 w-32 mb-2 mr-2 w-32">Yes</button>
                                    <button id="disagreePermanentAddress" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-2 w-32 ">No</button>
                                    <div class="acc__input-error error-agreePermanentAddress text-danger mt-2"></div>
                                    <input type="hidden" name="disagree_permanent_address" value="0" />
                                </div>
                        </div>
                    </div>
                    <div id="permanentAdressBox" class="col-span-12 hidden">
                        <div class="font-medium text-base">
                            <label for="input-wizard-4" class="form-label inline-flex">Permanent Address<i data-theme="light" data-tooltip-content="#permanent-address-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>

                            <!-- BEGIN: Custom Tooltip Content -->
                            <div class="tooltip-content">
                                <div id="permanent-address-tooltip" class="relative flex items-center py-1">
                                    <div class="text-slate-500 dark:text-slate-400">Please your term time address the same as your permanent address?</div>
                                </div>
                            </div>
                            <!-- END: Custom Tooltip Content -->
                        </div>
                        <div id="permanentAddress__no" class="hidden">
                                <div class="col-span-12">
                                    <div class="grid grid-cols-12 gap-x-4">
                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="permanent_address_line_1" class="form-label inline-flex">Address Line 1</label>
                                            <input id="permanent_address_line_1" type="text" name="permanent_address_line_1" class="w-full text-sm" />
                                            <div class="acc__input-error error-permanent_address_line_1 text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="permanent_address_line_2" class="form-label inline-flex">Address Line 2</label>
                                            <input id="permanent_address_line_2" type="text" name="permanent_address_line_2" class="w-full text-sm" />
                                            <div class="acc__input-error error-permanent_address_line_2 text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label  for="permanent_post_code" class="form-label inline-flex">Post Code</label>
                                            <input id="permanent_post_code" type="text" name="permanent_post_code" class="w-full text-sm" />
                                            <div class="acc__input-error error-permanent_post_code text-danger mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12">
                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label  for="permanent_city" class="form-label inline-flex">City</label>
                                            <input id="permanent_city" type="text" name="permanent_city" class="w-full text-sm"  />
                                            <div class="acc__input-error error-permanent_city text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="permanent_state" class="form-label inline-flex">State</label>
                                            <input id="permanent_state" type="text" name="permanent_state" class="w-full text-sm" />
                                            <div class="acc__input-error error-permanent_state text-danger mt-2"></div>
                                        </div>

                                        <div class="intro-y col-span-12 sm:col-span-4 py-1">
                                            <label for="permanent_country" class="form-label inline-flex">Country</label>
                                            <input id="permanent_country" type="text" name="permanent_country" class="w-full text-sm" />
                                            <div class="acc__input-error error-permanent_country text-danger mt-2"></div>
                                        </div>
                                </div>
                            
                        </div>
                        <div id="permanentAddress__yes" class="hidden" >
                            <input name="permanent_address_id" type="hidden" value="" />
                            <div class="font-medium text-base"><b>{{ $studentData["current_address"]->address_line_1 }},{{ !empty($studentData["current_address"]->address_line_2) ? $studentData["current_address"]->address_line_2."," : '' }} {{ !empty($studentData["current_address"]->post_code) ? $studentData["current_address"]->post_code."," : '' }} {{ !empty($studentData["current_address"]->city) ? $studentData["current_address"]->city."," : '' }} {{ $studentData["current_address"]->country }} </b></div>
                        </div>
                        
                        <div id="" class="intro-y col-span-12 py-2" >
                            <label for="data-5" class="form-label inline-flex">Please select your current permanent country <span class="text-danger">*</span> </label>
                            <select id="data-5" name="permanent_country_id" class=" w-full ">
                                <option value="">Please Select</option>
                                @foreach($pCountries as $pCountry)
                                    <option {{ ((($studentData["permanent_country_id"] == $pCountry->id && $studentData["permanent_country_id"] != 217) || $pCountry->id ==76)  ? "selected":"") }} value="{{ $pCountry->id }}">{{ $pCountry->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-permanent_country_id text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 py-2">
                            <label for="permanent_post_code_new" class="form-label inline-flex">Permanent Post Code <span class="text-danger"> *</span></label>
                            <input id="permanent_post_code_new" type="text"  autocomplete="off" name="permanent_post_code_new" value=" {{ !empty($studentData["current_address"]->post_code && !isset($studentData["permanent_post_code_new"])) ? $studentData["current_address"]->post_code : $studentData["permanent_post_code_new"] }}" class="w-full text-sm" />
                            <div class="acc__input-error error-permanent_post_code_new text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                        <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                            Back
                        </button>
                        <button id="form2SaveButton" type="button" class="btn btn-primary w-auto  form-wizard-next-btn hidden">
                            Save & Continue 
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 svg_2">
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
                </div>
            </form>
        </fieldset>
        <fieldset class="wizard-fieldset px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                <input type="hidden" name="url" value="{{ route('students.dashboard') }}"/>
                
                <div class="font-normal text-base">
                    <label for="input-wizard-4" class="form-label inline-flex">We kindly request your permission for email and SMS communications, with a focus on safeguarding your privacy and tailoring messages to your preferences. To grant permission, please click below.<i data-theme="light" data-tooltip-content="#consent-tooltip" data-trigger="click" data-lucide="help-circle" class="tooltip w-5 h-5 ml-1 cursor-pointer"></i></label>

                    <!-- BEGIN: Custom Tooltip Content -->
                    <div class="tooltip-content">
                        <div id="consent-tooltip" class="relative flex items-center py-1">
                            <div class="text-slate-500 dark:text-slate-400">By accepting the terms and conditions, you have already consented to receiving essential correspondence from the college. Additionally, please confirm your preferences for the following.</div>
                        </div>
                    </div>
                    <!-- END: Custom Tooltip Content -->
                </div>
                {{-- <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div> --}}
                <div class="grid grid-cols-12 gap-4 gap-y-5 mt-2">
                    <div class="col-span-12 sm:col-span-8">
                        
                        @foreach ($consents as $consent)
                                @if($consent->is_required=="Yes")
                                    <div class="mt-2">
                                        <div  id="checkbox-switch-7" class="flex items-center"><input type="checkbox" name="consent_number[]" value="{{ $consent->id }}" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50 w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white w-[38px] h-[24px] p-px rounded-full relative before:w-[20px] before:h-[20px] before:shadow-[1px_1px_3px_rgba(0,0,0,0.25)] before:transition-[margin-left] before:duration-200 before:ease-in-out before:absolute before:inset-y-0 before:my-auto before:rounded-full before:dark:bg-darkmode-600 checked:bg-primary checked:border-primary checked:bg-none before:checked:ml-[14px] before:checked:bg-white" />
                                            <label for="checkbox-switch-7" class="cursor-pointer ml-2 inline-flex">{{ $consent->name }} <i data-theme="light" data-tooltip-content="#consent-tooltip-{{ $consent->id }}" data-trigger="click" data-lucide="help-circle" class="tooltip w-4 h-4 ml-1 cursor-pointer"></i></label>
                                        </div>
                                    </div>
                                    <!-- BEGIN: Custom Tooltip Content -->
                                    <div class="tooltip-content">
                                        <div id="consent-tooltip-{{ $consent->id }}" class="relative flex items-center py-1">
                                            <div class="text-slate-500 dark:text-slate-400">{{ $consent->description }}</div>
                                        </div>
                                    </div>
                                    <!-- END: Custom Tooltip Content -->
                                @endif
                            @endforeach
                        {{-- <div class="form-check form-switch py-1">
                            <label class="form-check-label mr-3 ml-0" for="is_hesa">Other product services?</label>
                            <input id="other_product_services" class="form-check-input" name="other_product_services" value="1" type="checkbox">
                        </div> --}}

                    </div>
                </div>
                
                <div class="intro-y col-span-12 flex items-center justify-center sm:justify-end mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn mr-2">
                        Back
                    </button>
                    <button type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Submit
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 svg_2">
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
        {{-- <fieldset class="wizard-fieldset wizard-last-step px-5 sm:px-20 mt-10 pt-10 border-t border-slate-200/60 dark:border-darkmode-400">
            <form method="post" action="#" id="appicantFormStep_3" class="wizard-step-form">
                <div class="reviewLoader flex pt-5 pb-5 justify-center text-center">
                </div>
                <div class="reviewContentWrap" data-review-id="0" style="display: none;">
                    <div class="font-medium text-base mb-5">Review</div>
                    <div class="reviewContent pt-3 pb-5"></div>
                </div>

                <div class="col-span-12 flex items-center justify-between sm:justify-between mt-5">
                    <button type="button" class="btn btn-secondary w-auto form-wizard-previous-btn">
                        Back
                    </button>
                    <button type="button" class="btn btn-primary w-auto  form-wizard-next-btn">
                        Finished 
                    </button>
                    <input type="hidden" name="url" value="{{ route('students.dashboard') }}"/>
                </div>
            </form>
        </fieldset> --}}
    </div>
    <!-- END: Wizard Layout -->

    <!-- BEGIN: HTML Table Data -->

        <!-- ALL APPLICANT BASE DATA WILL BE HERE -->
  
    <!-- End: HTML Table Data --> 
    
    <link rel="stylesheet" type="text/css" href="https://services.postcodeanywhere.co.uk/css/captureplus-2.30.min.css?key=gy26-rh34-cf82-wd85" />
    <script type="text/javascript" src="https://services.postcodeanywhere.co.uk/js/captureplus-2.30.min.js?key=gy26-rh34-cf82-wd85"></script>

    @include('pages.students.frontend.modals.first-login.index')
@endsection


@section('script')
<script>(function(n,t,i,r){var u,f;n[i]=n[i]||{},n[i].initial={accountCode:"INDIV65018",host:"INDIV65018.pcapredict.com"},n[i].on=n[i].on||function(){(n[i].onq=n[i].onq||[]).push(arguments)},u=t.createElement("script"),u.async=!0,u.src=r,f=t.getElementsByTagName("script")[0],f.parentNode.insertBefore(u,f)})(window,document,"pca","//INDIV65018.pcapredict.com/js/sensor.js")</script>
    @vite('resources/js/student-frontend.js')
 
@endsection
