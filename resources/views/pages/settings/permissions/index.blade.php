@extends('../layout/' . $layout)

@section('subhead')
<title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
    </div>
</div>

<!-- BEGIN: Settings Page Content -->
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
        <!-- BEGIN: Profile Info -->
        @include('pages.settings.sidebar')
        <!-- END: Profile Info -->
    </div>
    <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
        <form id="permissionUpdateForm">
            <div class="intro-y box p-5 mt-5">
                <div class="flex justify-between items-center">
                    <div class="font-medium text-base">Department Permissions</div>
                    <button id="savePermissionsBtn" class="btn btn-primary w-48">
                        Save Permissions
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
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
            <div class="intro-y box p-5 mt-5">
                @if($departments->count() > 0)
                @foreach($departments as $department)
                <div id="department-{{ $department->id }}" class="accordion">
                    <div class="accordion-item {{ $loop->last ? '' : 'border-b' }}">
                        <div id="department-{{ $department->id }}" class="accordion-header flex justify-between {{ $loop->first ? '' : 'pt-4' }}">
                            <button class="accordion-button collapsed relative w-full text-lg font-semibold"
                                type="button"
                                data-target="#department-collapse-{{ $department->id }}"
                                aria-expanded="false"
                                aria-controls="department-collapse-{{ $department->id }}">
                                <div class="flex items-center font-medium text-base">
                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                    {{ $department->name }}
                                </div>
                            </button>
                        </div>
                        <div id="department-collapse-{{ $department->id }}" class="accordion-collapse collapse ml-4"
                            aria-labelledby="department-{{ $department->id }}">
                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed my-8">
                                <div id="nestedAccordion-{{ $department->id }}" class="accordion mt-3">
                                    <!-- Remote Access Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-remote" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-remote"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-remote">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Remote Access Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-remote" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-remote">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="flex items-center">
                                                        <input id="remote-allowed-{{ $department->id }}" class="form-check-input" name="permissions[{{ $department->id }}][remote_access_allowed]" type="checkbox" value="1" {{ isset($permissions[$department->id]['remote_access_allowed']) && $permissions[$department->id]['remote_access_allowed'] ? 'checked' : '' }}>
                                                        <label for="remote-allowed-{{ $department->id }}" class="ml-2 font-medium">Allowed</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input id="remote-temp-{{ $department->id }}" class="form-check-input remoteTempToggle" name="permissions[{{ $department->id }}][remote_access_temp]" type="checkbox" value="1" {{ isset($permissions[$department->id]['remote_access_temp']) && $permissions[$department->id]['remote_access_temp'] ? 'checked' : '' }}>
                                                        <label for="remote-temp-{{ $department->id }}" class="ml-2 font-medium">Allowed Temporary</label>
                                                    </div>

                                                    <div class="remoteDateRangeWrap flex items-center {{ empty($permissions[$department->id]['remote_access_temp']) ? 'hidden' : '' }}">
                                                        <input id="remote-date-range-{{ $department->id }}" type="text" autocomplete="off"
                                                            class="rangepicker form-control"
                                                            placeholder="Select date range"
                                                            aria-label="Temporary access date range"
                                                            name="permissions[{{ $department->id }}][remote_access_date_range]"
                                                            value="{{ $permissions[$department->id]['remote_access_date_range'] ?? '' }}">
                                                    </div>

                                                    <div class="flex items-center">
                                                        <input id="remote-home-{{ $department->id }}" class="form-check-input"
                                                            name="permissions[{{ $department->id }}][remote_access_home]" type="checkbox" value="1"
                                                            {{ isset($permissions[$department->id]['remote_access_home']) && $permissions[$department->id]['remote_access_home'] ? 'checked' : '' }}>
                                                        <label for="remote-home-{{ $department->id }}" class="ml-2 font-medium">Working From Home</label>
                                                    </div>

                                                    <div class="flex items-center">
                                                        <input id="remote-desktop-{{ $department->id }}" class="form-check-input"
                                                            name="permissions[{{ $department->id }}][remote_access_desktop]" type="checkbox" value="1"
                                                            {{ isset($permissions[$department->id]['remote_access_desktop']) && $permissions[$department->id]['remote_access_desktop'] ? 'checked' : '' }}>
                                                        <label for="remote-desktop-{{ $department->id }}" class="ml-2 font-medium">Desktop Clock In</label>
                                                    </div>

                                                    <div class="flex items-center">
                                                        <input id="remote-all-services-{{ $department->id }}" class="form-check-input"
                                                            name="permissions[{{ $department->id }}][remote_access_all_services]" type="checkbox" value="1"
                                                            {{ isset($permissions[$department->id]['remote_access_all_services']) && $permissions[$department->id]['remote_access_all_services'] ? 'checked' : '' }}>
                                                        <label for="remote-all-services-{{ $department->id }}" class="ml-2 font-medium">Allow All Services</label>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Menu Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-menu" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-menu"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-menu">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Menu Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-menu" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-menu">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="col-span-12 sm:col-span-2">
                                                            <div class="flex items-center">
                                                                <input id="menu-course-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_course_management]" {{ isset($permissions[$department->id]['menu_course_management']) && $permissions[$department->id]['menu_course_management'] ? 'checked' : '' }}>
                                                                <label for="menu-course-{{ $department->id }}" class="ml-2 font-medium">Course Management</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="course-semesters-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_course_semesters]" {{ isset($permissions[$department->id]['menu_course_semesters']) && $permissions[$department->id]['menu_course_semesters'] ? 'checked' : '' }}>
                                                                    <label for="course-semesters-{{ $department->id }}" class="ml-2">Course & Semesters</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="terms-modules-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_terms_modules]" {{ isset($permissions[$department->id]['menu_terms_modules']) && $permissions[$department->id]['menu_terms_modules'] ? 'checked' : '' }}>
                                                                    <label for="terms-modules-{{ $department->id }}" class="ml-2">Terms & Modules</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="menu_plans-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_plans]" {{ isset($permissions[$department->id]['menu_plans']) && $permissions[$department->id]['menu_plans'] ? 'checked' : '' }}>
                                                                    <label for="menu_plans-{{ $department->id }}" class="ml-2">Plans</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="plan-list-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_plan_list]" {{ isset($permissions[$department->id]['menu_plan_list']) && $permissions[$department->id]['menu_plan_list'] ? 'checked' : '' }}>
                                                                    <label for="plan-list-{{ $department->id }}" class="ml-2">Plan List</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="plan-tree-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_plan_tree]" {{ isset($permissions[$department->id]['menu_plan_tree']) && $permissions[$department->id]['menu_plan_tree'] ? 'checked' : '' }}>
                                                                    <label for="plan-tree-{{ $department->id }}" class="ml-2">Plan Tree</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-2">
                                                            <div class="flex items-center">
                                                                <input id="menu-student-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_student_management]" {{ isset($permissions[$department->id]['menu_student_management']) && $permissions[$department->id]['menu_student_management'] ? 'checked' : '' }}>
                                                                <label for="menu-student-{{ $department->id }}" class="ml-2 font-medium">Student Management</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="generate-letter-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_generate_letter]" {{ isset($permissions[$department->id]['menu_generate_letter']) && $permissions[$department->id]['menu_generate_letter'] ? 'checked' : '' }}>
                                                                    <label for="generate-letter-{{ $department->id }}" class="ml-2">Generate Letter</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                        <input id="send-email-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_send_email]" {{ isset($permissions[$department->id]['menu_send_email']) && $permissions[$department->id]['menu_send_email'] ? 'checked' : '' }}>
                                                                        <label for="send-email-{{ $department->id }}" class="ml-2">Send Email</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="send-sms-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_send_sms]" {{ isset($permissions[$department->id]['menu_send_sms']) && $permissions[$department->id]['menu_send_sms'] ? 'checked' : '' }}>
                                                                    <label for="send-sms-{{ $department->id }}" class="ml-2">Send SMS</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-8">
                                                            <div class="flex items-center">
                                                                <input id="menu-settings-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_settings]" {{ isset($permissions[$department->id]['menu_settings']) && $permissions[$department->id]['menu_settings'] ? 'checked' : '' }}>
                                                                <label for="menu-settings-{{ $department->id }}" class="ml-2 font-medium">Settings</label>
                                                            </div>
                                                            <div class="pl-8 mt-2 grid grid-cols-12 gap-2">
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-site-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_site_settings]" {{ isset($permissions[$department->id]['menu_site_settings']) && $permissions[$department->id]['menu_site_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-site-{{ $department->id }}" class="ml-2">Site Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-course-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_course_parameters]" {{ isset($permissions[$department->id]['menu_course_parameters']) && $permissions[$department->id]['menu_course_parameters'] ? 'checked' : '' }}>
                                                                    <label for="settings-course-{{ $department->id }}" class="ml-2">Course Parameters</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-campus-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_campus_settings]" {{ isset($permissions[$department->id]['menu_campus_settings']) && $permissions[$department->id]['menu_campus_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-campus-{{ $department->id }}" class="ml-2">Campus Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-applicant-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_applicant_settings]" {{ isset($permissions[$department->id]['menu_applicant_settings']) && $permissions[$department->id]['menu_applicant_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-applicant-{{ $department->id }}" class="ml-2">Applicant Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-student-options-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_student_option_values]" {{ isset($permissions[$department->id]['menu_student_option_values']) && $permissions[$department->id]['menu_student_option_values'] ? 'checked' : '' }}>
                                                                    <label for="settings-student-options-{{ $department->id }}" class="ml-2">Student Option Values</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-student-flags-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_student_flags]" {{ isset($permissions[$department->id]['menu_student_flags']) && $permissions[$department->id]['menu_student_flags'] ? 'checked' : '' }}>
                                                                    <label for="settings-student-flags-{{ $department->id }}" class="ml-2">Student Flags</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-communication-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_communication_settings]" {{ isset($permissions[$department->id]['menu_communication_settings']) && $permissions[$department->id]['menu_communication_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-communication-{{ $department->id }}" class="ml-2">Communication Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-elearning-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_elearning_activity_setting]" {{ isset($permissions[$department->id]['menu_elearning_activity_setting']) && $permissions[$department->id]['menu_elearning_activity_setting'] ? 'checked' : '' }}>
                                                                    <label for="settings-elearning-{{ $department->id }}" class="ml-2">E-Learning Activity Setting</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-user-privilege-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_user_privilege]" {{ isset($permissions[$department->id]['menu_user_privilege']) && $permissions[$department->id]['menu_user_privilege'] ? 'checked' : '' }}>
                                                                    <label for="settings-user-privilege-{{ $department->id }}" class="ml-2">User Privilege</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-hr-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_hr_settings]" {{ isset($permissions[$department->id]['menu_hr_settings']) && $permissions[$department->id]['menu_hr_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-hr-{{ $department->id }}" class="ml-2">HR Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-datafuture-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_datafuture_settings]" {{ isset($permissions[$department->id]['menu_datafuture_settings']) && $permissions[$department->id]['menu_datafuture_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-datafuture-{{ $department->id }}" class="ml-2">Datafuture Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                    <input id="settings-internal-link-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_internal_site_link]" {{ isset($permissions[$department->id]['menu_internal_site_link']) && $permissions[$department->id]['menu_internal_site_link'] ? 'checked' : '' }}>
                                                                    <label for="settings-internal-link-{{ $department->id }}" class="ml-2">Internal Site Link</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-4 flex">
                                                                    <input id="settings-accounts-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_accounts_settings]" {{ isset($permissions[$department->id]['menu_accounts_settings']) && $permissions[$department->id]['menu_accounts_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-accounts-{{ $department->id }}" class="ml-2">Accounts Settings</label>
                                                                </div>
                                                                <div class="col-span-12 sm:col-span-8 flex">
                                                                    <input id="settings-file-manager-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_file_manager_settings]" {{ isset($permissions[$department->id]['menu_file_manager_settings']) && $permissions[$department->id]['menu_file_manager_settings'] ? 'checked' : '' }}>
                                                                    <label for="settings-file-manager-{{ $department->id }}" class="ml-2">File Manager Settings</label>
                                                                </div>
                                                                <div class="col-span-12">
                                                                    <div class="flex items-center">
                                                                        <input id="settings-workplacement-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_workplacement]" {{ isset($permissions[$department->id]['menu_workplacement']) && $permissions[$department->id]['menu_workplacement'] ? 'checked' : '' }}>
                                                                        <label for="settings-workplacement-{{ $department->id }}" class="ml-2">Workplacement</label>
                                                                    </div>
                                                                    <div class="pl-8 mt-2">
                                                                        <div class="flex items-center mt-2">
                                                                            <input id="workplacement-details-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_workplacement_details]" {{ isset($permissions[$department->id]['menu_workplacement_details']) && $permissions[$department->id]['menu_workplacement_details'] ? 'checked' : '' }}>
                                                                            <label for="workplacement-details-{{ $department->id }}" class="ml-2">Workplacement Details</label>
                                                                        </div>
                                                                        <div class="flex items-center mt-2">
                                                                            <input id="workplacement-companies-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_workplacement_companies]" {{ isset($permissions[$department->id]['menu_workplacement_companies']) && $permissions[$department->id]['menu_workplacement_companies'] ? 'checked' : '' }}>
                                                                            <label for="workplacement-companies-{{ $department->id }}" class="ml-2">Workplacement Companies / Supervisor</label>
                                                                        </div>
                                                                        <div class="flex items-center mt-2">
                                                                            <input id="workplacement-settings-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][menu_workplacement_settings]" {{ isset($permissions[$department->id]['menu_workplacement_settings']) && $permissions[$department->id]['menu_workplacement_settings'] ? 'checked' : '' }}>
                                                                            <label for="workplacement-settings-{{ $department->id }}" class="ml-2">Workplacement Settings</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dashboard Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-dashboard" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-dashboard"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-dashboard">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Dashboard Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-dashboard" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-dashboard">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-applicant-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_applicant]" {{ isset($permissions[$department->id]['dashboard_applicant']) && $permissions[$department->id]['dashboard_applicant'] ? 'checked' : '' }}>
                                                                <label for="dashboard-applicant-{{ $department->id }}" class="ml-2 font-medium">Applicant</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="applicant-analysis-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_applicant_analysis]" {{ isset($permissions[$department->id]['dashboard_applicant_analysis']) && $permissions[$department->id]['dashboard_applicant_analysis'] ? 'checked' : '' }}>
                                                                    <label for="applicant-analysis-{{ $department->id }}" class="ml-2">Application Analysis</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                     <input id="applicant-rejected-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_applicant_rejected]" {{ isset($permissions[$department->id]['dashboard_applicant_rejected']) && $permissions[$department->id]['dashboard_applicant_rejected'] ? 'checked' : '' }}>
                                                                    <label for="applicant-rejected-{{ $department->id }}" class="ml-2">Reject / In Progress Application</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                 <input id="dashboard-interviews-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_interviews]" {{ isset($permissions[$department->id]['dashboard_interviews']) && $permissions[$department->id]['dashboard_interviews'] ? 'checked' : '' }}>
                                                                <label for="dashboard-interviews-{{ $department->id }}" class="ml-2 font-medium">Required Interviews</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                               <input id="dashboard-news-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_news]" {{ isset($permissions[$department->id]['dashboard_news']) && $permissions[$department->id]['dashboard_news'] ? 'checked' : '' }}>
                                                                <label for="dashboard-news-{{ $department->id }}" class="ml-2 font-medium">News & Events</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                 <input id="dashboard-student-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_live_student]" {{ isset($permissions[$department->id]['dashboard_live_student']) && $permissions[$department->id]['dashboard_live_student'] ? 'checked' : '' }}>
                                                                <label for="dashboard-student-{{ $department->id }}" class="ml-2 font-medium">Live Student</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                 <input id="dashboard-hr-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_hr_portal]" {{ isset($permissions[$department->id]['dashboard_hr_portal']) && $permissions[$department->id]['dashboard_hr_portal'] ? 'checked' : '' }}>
                                                                <label for="dashboard-hr-{{ $department->id }}" class="ml-2 font-medium">HR Portal</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-due-report-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_due_report]" {{ isset($permissions[$department->id]['dashboard_due_report']) && $permissions[$department->id]['dashboard_due_report'] ? 'checked' : '' }}>
                                                                <label for="dashboard-due-report-{{ $department->id }}" class="ml-2 font-medium">Student Due Report</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-tutor-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_tutor]" {{ isset($permissions[$department->id]['dashboard_tutor']) && $permissions[$department->id]['dashboard_tutor'] ? 'checked' : '' }}>
                                                                <label for="dashboard-tutor-{{ $department->id }}" class="ml-2 font-medium">Tutor Dashboard</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-personal-tutor-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_personal_tutor]" {{ isset($permissions[$department->id]['dashboard_personal_tutor']) && $permissions[$department->id]['dashboard_personal_tutor'] ? 'checked' : '' }}>
                                                                <label for="dashboard-personal-tutor-{{ $department->id }}" class="ml-2 font-medium">Personal Tutor Dashboard</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                 <input id="dashboard-programme-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_programme]" {{ isset($permissions[$department->id]['dashboard_programme']) && $permissions[$department->id]['dashboard_programme'] ? 'checked' : '' }}>
                                                                <label for="dashboard-programme-{{ $department->id }}" class="ml-2 font-medium">Programme Dashboard</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="programme-reports-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_programme_reports]" {{ isset($permissions[$department->id]['dashboard_programme_reports']) && $permissions[$department->id]['dashboard_programme_reports'] ? 'checked' : '' }}>
                                                                    <label for="programme-reports-{{ $department->id }}" class="ml-2">Reports</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="programme-student-data-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_programme_student_data]" {{ isset($permissions[$department->id]['dashboard_programme_student_data']) && $permissions[$department->id]['dashboard_programme_student_data'] ? 'checked' : '' }}>
                                                                    <label for="programme-student-data-{{ $department->id }}" class="ml-2">Student Data Report Other Details Show</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-budget-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_budget]" {{ isset($permissions[$department->id]['dashboard_budget']) && $permissions[$department->id]['dashboard_budget'] ? 'checked' : '' }}>
                                                                <label for="dashboard-budget-{{ $department->id }}" class="ml-2 font-medium">Budget Management</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="budget-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_budget_edit]" {{ isset($permissions[$department->id]['dashboard_budget_edit']) && $permissions[$department->id]['dashboard_budget_edit'] ? 'checked' : '' }}>
                                                                    <label for="budget-edit-{{ $department->id }}" class="ml-2">Edit Budget</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="budget-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_budget_delete]" {{ isset($permissions[$department->id]['dashboard_budget_delete']) && $permissions[$department->id]['dashboard_budget_delete'] ? 'checked' : '' }}>
                                                                    <label for="budget-delete-{{ $department->id }}" class="ml-2">Delete Settings</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="budget-settings-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_budget_settings]" {{ isset($permissions[$department->id]['dashboard_budget_settings']) && $permissions[$department->id]['dashboard_budget_settings'] ? 'checked' : '' }}>
                                                                    <label for="budget-settings-{{ $department->id }}" class="ml-2">Budget Settings</label>
                                                                </div>
                                                                <div class="flex items-center mt-2">
                                                                    <input id="budget-reports-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_budget_reports]" {{ isset($permissions[$department->id]['dashboard_budget_reports']) && $permissions[$department->id]['dashboard_budget_reports'] ? 'checked' : '' }}>
                                                                    <label for="budget-reports-{{ $department->id }}" class="ml-2">Budget Reports</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-file-manager-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_file_manager]" {{ isset($permissions[$department->id]['dashboard_file_manager']) && $permissions[$department->id]['dashboard_file_manager'] ? 'checked' : '' }}>
                                                                <label for="dashboard-file-manager-{{ $department->id }}" class="ml-2 font-medium">File Manager</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-expired-docs-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_expired_docs]" {{ isset($permissions[$department->id]['dashboard_expired_docs']) && $permissions[$department->id]['dashboard_expired_docs'] ? 'checked' : '' }}>
                                                                <label for="dashboard-expired-docs-{{ $department->id }}" class="ml-2 font-medium">Expired Documents</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="dashboard-report-issue-{{ $department->id }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_report_issue]" {{ isset($permissions[$department->id]['dashboard_report_issue']) && $permissions[$department->id]['dashboard_report_issue'] ? 'checked' : '' }}>
                                                                <label for="dashboard-report-issue-{{ $department->id }}" class="ml-2 font-medium">Report Issue</label>
                                                            </div>
                                                            <div class="pl-4 mt-2 space-y-2 childrenPermissionWrap">
                                                                <div class="flex items-center">
                                                                    <input id="dashboard-show-all-issue-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][dashboard_show_all_issue]" {{ isset($permissions[$department->id]['dashboard_show_all_issue']) && $permissions[$department->id]['dashboard_show_all_issue'] ? 'checked' : '' }} {{ empty($permissions[$department->id]['dashboard_report_issue']) ? 'disabled' : '' }}>
                                                                    <label for="dashboard-show-all-issue-{{ $department->id }}" class="ml-2">Show All Issue</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Staff Profile Privilege -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-staff" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-staff"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-staff">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Staff Profile Privilege
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-staff" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-staff">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="flex items-center">
                                                        <input id="staff-group-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][staff_profile_staff_group]" {{ isset($permissions[$department->id]['staff_profile_staff_group']) && $permissions[$department->id]['staff_profile_staff_group'] ? 'checked' : '' }}>
                                                        <label for="staff-group-{{ $department->id }}" class="ml-2 font-medium">Staff Group</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- HR Portal Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-hr" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-hr"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-hr">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    HR Portal Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-hr" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-hr">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="flex items-center">
                                                        <input id="hr-add-attendance-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][hr_portal_add_attendance]" {{ isset($permissions[$department->id]['hr_portal_add_attendance']) && $permissions[$department->id]['hr_portal_add_attendance'] ? 'checked' : '' }}>
                                                        <label for="hr-add-attendance-{{ $department->id }}" class="ml-2 font-medium">Add Attendance</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input id="hr-delete-attendance-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][hr_portal_delete_attendance]" {{ isset($permissions[$department->id]['hr_portal_delete_attendance']) && $permissions[$department->id]['hr_portal_delete_attendance'] ? 'checked' : '' }}>
                                                        <label for="hr-delete-attendance-{{ $department->id }}" class="ml-2 font-medium">Delete Attendance</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                      <input id="hr-privilege-menu-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][hr_portal_privilege_menu]" {{ isset($permissions[$department->id]['hr_portal_privilege_menu']) && $permissions[$department->id]['hr_portal_privilege_menu'] ? 'checked' : '' }}>
                                                        <label for="hr-privilege-menu-{{ $department->id }}" class="ml-2 font-medium">Privilege Menu</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                         <input id="hr-edit-email-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][hr_portal_edit_email]" {{ isset($permissions[$department->id]['hr_portal_edit_email']) && $permissions[$department->id]['hr_portal_edit_email'] ? 'checked' : '' }}>
                                                        <label for="hr-edit-email-{{ $department->id }}" class="ml-2 font-medium">Edit User Email</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                         <input id="hr-login-as-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][hr_portal_login_as]" {{ isset($permissions[$department->id]['hr_portal_login_as']) && $permissions[$department->id]['hr_portal_login_as'] ? 'checked' : '' }}>
                                                        <label for="hr-login-as-{{ $department->id }}" class="ml-2 font-medium">Login As User</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Applicant Portal Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-applicant" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-applicant"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-applicant">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Applicant Portal Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-applicant" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-applicant">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="flex items-center">
                                                       <input id="applicant-login-as-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][applicant_portal_login_as]" {{ isset($permissions[$department->id]['applicant_portal_login_as']) && $permissions[$department->id]['applicant_portal_login_as'] ? 'checked' : '' }}>
                                                        <label for="applicant-login-as-{{ $department->id }}" class="ml-2 font-medium">Login as Applicant</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input id="applicant-create-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][applicant_portal_create]" {{ isset($permissions[$department->id]['applicant_portal_create']) && $permissions[$department->id]['applicant_portal_create'] ? 'checked' : '' }}>
                                                        <label for="applicant-create-{{ $department->id }}" class="ml-2 font-medium">Create Applicant Account</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input id="applicant-e-signature-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][applicant_portal_e_signature]" {{ isset($permissions[$department->id]['applicant_portal_e_signature']) && $permissions[$department->id]['applicant_portal_e_signature'] ? 'checked' : '' }}>
                                                        <label for="applicant-e-signature-{{ $department->id }}" class="ml-2 font-medium">E-Signature Request</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Live Student Portal Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-student" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-student"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-student">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Live Student Portal Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-student" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-student">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4">
                                                      
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Results</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="results-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_results_view]" {{ isset($permissions[$department->id]['live_student_results_view']) && $permissions[$department->id]['live_student_results_view'] ? 'checked' : '' }}>
                                                                    <label for="results-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="results-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_results_add]" {{ isset($permissions[$department->id]['live_student_results_add']) && $permissions[$department->id]['live_student_results_add'] ? 'checked' : '' }}>
                                                                    <label for="results-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="results-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_results_edit]" {{ isset($permissions[$department->id]['live_student_results_edit']) && $permissions[$department->id]['live_student_results_edit'] ? 'checked' : '' }}>
                                                                    <label for="results-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="results-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_results_delete]" {{ isset($permissions[$department->id]['live_student_results_delete']) && $permissions[$department->id]['live_student_results_delete'] ? 'checked' : '' }}>
                                                                    <label for="results-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Attendance</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="attendance-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_attendance_view]" {{ isset($permissions[$department->id]['live_student_attendance_view']) && $permissions[$department->id]['live_student_attendance_view'] ? 'checked' : '' }}>
                                                                    <label for="attendance-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="attendance-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_attendance_add]" {{ isset($permissions[$department->id]['live_student_attendance_add']) && $permissions[$department->id]['live_student_attendance_add'] ? 'checked' : '' }}>
                                                                    <label for="attendance-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="attendance-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_attendance_edit]" {{ isset($permissions[$department->id]['live_student_attendance_edit']) && $permissions[$department->id]['live_student_attendance_edit'] ? 'checked' : '' }}>
                                                                    <label for="attendance-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="attendance-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_attendance_delete]" {{ isset($permissions[$department->id]['live_student_attendance_delete']) && $permissions[$department->id]['live_student_attendance_delete'] ? 'checked' : '' }}>
                                                                    <label for="attendance-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Accounts</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="accounts-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_accounts_view]" {{ isset($permissions[$department->id]['live_student_accounts_view']) && $permissions[$department->id]['live_student_accounts_view'] ? 'checked' : '' }}>
                                                                    <label for="accounts-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                   <input id="accounts-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_accounts_add]" {{ isset($permissions[$department->id]['live_student_accounts_add']) && $permissions[$department->id]['live_student_accounts_add'] ? 'checked' : '' }}>
                                                                    <label for="accounts-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="accounts-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_accounts_edit]" {{ isset($permissions[$department->id]['live_student_accounts_edit']) && $permissions[$department->id]['live_student_accounts_edit'] ? 'checked' : '' }}>
                                                                    <label for="accounts-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="accounts-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_accounts_delete]" {{ isset($permissions[$department->id]['live_student_accounts_delete']) && $permissions[$department->id]['live_student_accounts_delete'] ? 'checked' : '' }}>
                                                                    <label for="accounts-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">SLC History</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="slc-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_slc_view]" {{ isset($permissions[$department->id]['live_student_slc_view']) && $permissions[$department->id]['live_student_slc_view'] ? 'checked' : '' }}>
                                                                    <label for="slc-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="slc-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_slc_add]" {{ isset($permissions[$department->id]['live_student_slc_add']) && $permissions[$department->id]['live_student_slc_add'] ? 'checked' : '' }}>
                                                                    <label for="slc-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="slc-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_slc_edit]" {{ isset($permissions[$department->id]['live_student_slc_edit']) && $permissions[$department->id]['live_student_slc_edit'] ? 'checked' : '' }}>
                                                                    <label for="slc-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="slc-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_slc_delete]" {{ isset($permissions[$department->id]['live_student_slc_delete']) && $permissions[$department->id]['live_student_slc_delete'] ? 'checked' : '' }}>
                                                                    <label for="slc-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Other Course Relation</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                      <input id="course-relation-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_course_relation_view]" {{ isset($permissions[$department->id]['live_student_course_relation_view']) && $permissions[$department->id]['live_student_course_relation_view'] ? 'checked' : '' }}>
                                                                    <label for="course-relation-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Performance</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="performance-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_performance_view]" {{ isset($permissions[$department->id]['live_student_performance_view']) && $permissions[$department->id]['live_student_performance_view'] ? 'checked' : '' }}>
                                                                    <label for="performance-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Print Application Form</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                   <input id="print-app-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_print_app_view]" {{ isset($permissions[$department->id]['live_student_print_app_view']) && $permissions[$department->id]['live_student_print_app_view'] ? 'checked' : '' }}>
                                                                    <label for="print-app-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Student Archives</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="archives-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_archives_view]" {{ isset($permissions[$department->id]['live_student_archives_view']) && $permissions[$department->id]['live_student_archives_view'] ? 'checked' : '' }}>
                                                                    <label for="archives-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Workplacement</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="workplacement-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_workplacement_add]" {{ isset($permissions[$department->id]['live_student_workplacement_add']) && $permissions[$department->id]['live_student_workplacement_add'] ? 'checked' : '' }}>
                                                                    <label for="workplacement-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                      <input id="workplacement-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_workplacement_edit]" {{ isset($permissions[$department->id]['live_student_workplacement_edit']) && $permissions[$department->id]['live_student_workplacement_edit'] ? 'checked' : '' }}>
                                                                    <label for="workplacement-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="workplacement-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_workplacement_delete]" {{ isset($permissions[$department->id]['live_student_workplacement_delete']) && $permissions[$department->id]['live_student_workplacement_delete'] ? 'checked' : '' }}>
                                                                    <label for="workplacement-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Visit</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                     <input id="visit-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_visit_view]" {{ isset($permissions[$department->id]['live_student_visit_view']) && $permissions[$department->id]['live_student_visit_view'] ? 'checked' : '' }}>
                                                                    <label for="visit-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="visit-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_visit_add]" {{ isset($permissions[$department->id]['live_student_visit_add']) && $permissions[$department->id]['live_student_visit_add'] ? 'checked' : '' }}>
                                                                    <label for="visit-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="visit-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_visit_edit]" {{ isset($permissions[$department->id]['live_student_visit_edit']) && $permissions[$department->id]['live_student_visit_edit'] ? 'checked' : '' }}>
                                                                    <label for="visit-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="visit-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_visit_delete]" {{ isset($permissions[$department->id]['live_student_visit_delete']) && $permissions[$department->id]['live_student_visit_delete'] ? 'checked' : '' }}>
                                                                    <label for="visit-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-6">
                                                            <label class="font-medium">Communications</label>
                                                            <div class="pl-4 mt-2 grid grid-cols-2 gap-2">
                                                                <div class="flex items-center">
                                                                    <input id="comms-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_view]" {{ isset($permissions[$department->id]['live_student_comms_view']) && $permissions[$department->id]['live_student_comms_view'] ? 'checked' : '' }}>
                                                                    <label for="comms-view-{{ $department->id }}" class="ml-2">View Communication</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="comms-send-letter-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_send_letter]" {{ isset($permissions[$department->id]['live_student_comms_send_letter']) && $permissions[$department->id]['live_student_comms_send_letter'] ? 'checked' : '' }}>
                                                                    <label for="comms-send-letter-{{ $department->id }}" class="ml-2">Send Letter</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="comms-delete-letter-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_delete_letter]" {{ isset($permissions[$department->id]['live_student_comms_delete_letter']) && $permissions[$department->id]['live_student_comms_delete_letter'] ? 'checked' : '' }}>
                                                                    <label for="comms-delete-letter-{{ $department->id }}" class="ml-2">Delete Letter</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                     <input id="comms-send-email-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_send_email]" {{ isset($permissions[$department->id]['live_student_comms_send_email']) && $permissions[$department->id]['live_student_comms_send_email'] ? 'checked' : '' }}>
                                                                    <label for="comms-send-email-{{ $department->id }}" class="ml-2">Send Email</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                   <input id="comms-delete-email-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_delete_email]" {{ isset($permissions[$department->id]['live_student_comms_delete_email']) && $permissions[$department->id]['live_student_comms_delete_email'] ? 'checked' : '' }}>
                                                                    <label for="comms-delete-email-{{ $department->id }}" class="ml-2">Delete Email</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="comms-send-sms-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_send_sms]" {{ isset($permissions[$department->id]['live_student_comms_send_sms']) && $permissions[$department->id]['live_student_comms_send_sms'] ? 'checked' : '' }}>
                                                                    <label for="comms-send-sms-{{ $department->id }}" class="ml-2">Send SMS</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="comms-delete-sms-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_comms_delete_sms]" {{ isset($permissions[$department->id]['live_student_comms_delete_sms']) && $permissions[$department->id]['live_student_comms_delete_sms'] ? 'checked' : '' }}>
                                                                    <label for="comms-delete-sms-{{ $department->id }}" class="ml-2">Delete SMS</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Documents</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="docs-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_docs_view]" {{ isset($permissions[$department->id]['live_student_docs_view']) && $permissions[$department->id]['live_student_docs_view'] ? 'checked' : '' }}>
                                                                    <label for="docs-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="docs-add-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_docs_add]" {{ isset($permissions[$department->id]['live_student_docs_add']) && $permissions[$department->id]['live_student_docs_add'] ? 'checked' : '' }}>
                                                                    <label for="docs-add-{{ $department->id }}" class="ml-2">Add</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="docs-delete-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_docs_delete]" {{ isset($permissions[$department->id]['live_student_docs_delete']) && $permissions[$department->id]['live_student_docs_delete'] ? 'checked' : '' }}>
                                                                    <label for="docs-delete-{{ $department->id }}" class="ml-2">Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <input id="student-change-status-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_change_status]" {{ isset($permissions[$department->id]['live_student_change_status']) && $permissions[$department->id]['live_student_change_status'] ? 'checked' : '' }}>
                                                            <label for="student-change-status-{{ $department->id }}" class="ml-2 font-medium">Change Status</label>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <input id="student-login-as-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_login_as]" {{ isset($permissions[$department->id]['live_student_login_as']) && $permissions[$department->id]['live_student_login_as'] ? 'checked' : '' }}>
                                                            <label for="student-login-as-{{ $department->id }}" class="ml-2 font-medium">Login as Student</label>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Student Other Personal Info</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="other-personal-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_other_personal_view]" {{ isset($permissions[$department->id]['live_student_other_personal_view']) && $permissions[$department->id]['live_student_other_personal_view'] ? 'checked' : '' }}>
                                                                    <label for="other-personal-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="other-personal-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_other_personal_edit]" {{ isset($permissions[$department->id]['live_student_other_personal_edit']) && $permissions[$department->id]['live_student_other_personal_edit'] ? 'checked' : '' }}>
                                                                    <label for="other-personal-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Residency Status and Criminal Convictions</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="residency-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_residency_view]" {{ isset($permissions[$department->id]['live_student_residency_view']) && $permissions[$department->id]['live_student_residency_view'] ? 'checked' : '' }}>
                                                                    <label for="residency-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="residency-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_residency_edit]" {{ isset($permissions[$department->id]['live_student_residency_edit']) && $permissions[$department->id]['live_student_residency_edit'] ? 'checked' : '' }}>
                                                                    <label for="residency-edit-{{ $department->id }}" class="ml-2">Edit</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Datafuture</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="datafuture-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_datafuture_view]" {{ isset($permissions[$department->id]['live_student_datafuture_view']) && $permissions[$department->id]['live_student_datafuture_view'] ? 'checked' : '' }}>
                                                                    <label for="datafuture-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    <input id="datafuture-edit-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_datafuture_edit]" {{ isset($permissions[$department->id]['live_student_datafuture_edit']) && $permissions[$department->id]['live_student_datafuture_edit'] ? 'checked' : '' }}>
                                                                    <label for="datafuture-edit-{{ $department->id }}" class="ml-2">Add/Edit/Delete</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <label class="font-medium">Student Login Logs</label>
                                                            <div class="pl-4 mt-2 space-y-2">
                                                                <div class="flex items-center">
                                                                    <input id="student-logs-view-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][live_student_logs_view]" {{ isset($permissions[$department->id]['live_student_logs_view']) && $permissions[$department->id]['live_student_logs_view'] ? 'checked' : '' }}>
                                                                    <label for="student-logs-view-{{ $department->id }}" class="ml-2">View</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Module Content Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-module" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-module"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-module">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Module Content Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-module" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-module">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4 items-start">
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="module-participants-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][module_content_participants]" {{ isset($permissions[$department->id]['module_content_participants']) && $permissions[$department->id]['module_content_participants'] ? 'checked' : '' }}>
                                                                <label for="module-participants-{{ $department->id }}" class="ml-2 font-medium">Participants</label>
                                                            </div>
                                                            <div class="pl-8 mt-2">
                                                                <div class="flex items-center mt-2">
                                                                    <input id="participants-export-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][participants_export]" {{ isset($permissions[$department->id]['participants_export']) && $permissions[$department->id]['participants_export'] ? 'checked' : '' }}>
                                                                    <label for="participants-export-{{ $department->id }}" class="ml-2">Export</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="module-assessment-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][module_content_assessment]" {{ isset($permissions[$department->id]['module_content_assessment']) && $permissions[$department->id]['module_content_assessment'] ? 'checked' : '' }}>
                                                                <label for="module-assessment-{{ $department->id }}" class="ml-2 font-medium">Assessment</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                              <input id="module-analytics-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][module_content_analytics]" {{ isset($permissions[$department->id]['module_content_analytics']) && $permissions[$department->id]['module_content_analytics'] ? 'checked' : '' }}>
                                                                <label for="module-analytics-{{ $department->id }}" class="ml-2 font-medium">Analytics</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="module-edit-attendance-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][module_content_edit_attendance]" {{ isset($permissions[$department->id]['module_content_edit_attendance']) && $permissions[$department->id]['module_content_edit_attendance'] ? 'checked' : '' }}>
                                                                <label for="module-edit-attendance-{{ $department->id }}" class="ml-2 font-medium">Edit Attendance</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Library Management Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-library" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-library"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-library">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Library Management Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-library" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-library">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4 items-start">
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="library-settings-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][library_management]" {{ isset($permissions[$department->id]['library_management']) && $permissions[$department->id]['library_management'] ? 'checked' : '' }}>
                                                                <label for="library-settings-{{ $department->id }}" class="ml-2 font-medium">Library Management</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Result Management Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-result-management" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-result-management"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-result-management">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Result Management Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-result-management" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-result-management">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4 items-start">
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="result-management-staff-upload-permission-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][result_staff_upload]" {{ isset($permissions[$department->id]['result_staff_upload']) && $permissions[$department->id]['result_staff_upload'] ? 'checked' : '' }}>
                                                                <label for="result-management-staff-upload-permission-{{ $department->id }}" class="ml-2 font-medium">Staff Upload Permission</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                            <input id="result-management-staff-delete-permission-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][result_staff_delete]" {{ isset($permissions[$department->id]['result_staff_delete']) && $permissions[$department->id]['result_staff_delete'] ? 'checked' : '' }}>
                                                                <label for="result-management-staff-delete-permission-{{ $department->id }}" class="ml-2 font-medium">Staff Delete Permission</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                            <input id="result-management-PT-upload-permission-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][result_pt_upload]" {{ isset($permissions[$department->id]['result_pt_upload']) && $permissions[$department->id]['result_pt_upload'] ? 'checked' : '' }}>
                                                                <label for="result-management-PT-upload-permission-{{ $department->id }}" class="ml-2 font-medium">PT Upload Permission</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Internal Links Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-internal-links-management" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-internal-links-management"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-internal-links-management">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Internal Links Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-internal-links-management" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-internal-links-management">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4">
                                                        @foreach($internalLinks as $link)
                                                            @php $hasChildren = $link->children->count() > 0; @endphp
                                                            <div class="col-span-12 {{ $hasChildren ? '' : 'sm:col-span-3' }}">
                                                                <div class="flex items-center font-medium">
                                                                    <input id="internal-link-{{ $department->id }}-{{ $link->id }}" class="form-check-input {{ $hasChildren ? 'parentPermissionItem' : '' }}" type="checkbox" value="1" name="permissions[{{ $department->id }}][internal_link_{{ $link->id }}]" {{ !empty($permissions[$department->id]['internal_link_'.$link->id]) ? 'checked' : '' }}>
                                                                    <label for="internal-link-{{ $department->id }}-{{ $link->id }}" class="ml-2">{{ $link->name }}</label>
                                                                </div>
                                                                @if($hasChildren)
                                                                    <div class="pl-8 mt-2 grid grid-cols-12 gap-2 childrenPermissionWrap">
                                                                        @foreach($link->children as $child)
                                                                            <div class="col-span-12 sm:col-span-4 flex items-center">
                                                                                <input id="internal-link-{{ $department->id }}-{{ $child->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][internal_link_{{ $child->id }}]" {{ !empty($permissions[$department->id]['internal_link_'.$child->id]) ? 'checked' : '' }} {{ empty($permissions[$department->id]['internal_link_'.$link->id]) ? 'disabled' : '' }}>
                                                                                <label for="internal-link-{{ $department->id }}-{{ $child->id }}" class="ml-2">{{ $child->name }}</label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach

                                                        {{-- Not rows in internal_links, so they stay as fixed keys (as in the legacy form). --}}
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center font-medium">
                                                                <input id="internal-link-group-email-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][internal_link_group_email]" {{ !empty($permissions[$department->id]['internal_link_group_email']) ? 'checked' : '' }}>
                                                                <label for="internal-link-group-email-{{ $department->id }}" class="ml-2">Group Email</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center font-medium">
                                                                <input id="internal-link-staff-upload-{{ $department->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][internal_link_staff_upload_permission]" {{ !empty($permissions[$department->id]['internal_link_staff_upload_permission']) ? 'checked' : '' }}>
                                                                <label for="internal-link-staff-upload-{{ $department->id }}" class="ml-2">Staff Upload Permission</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Accounts Privileges -->
                                    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
                                        <div id="nestedAccordionHeader-{{ $department->id }}-accounts-rivileges-management" class="accordion-header flex justify-between">
                                            <button class="accordion-button collapsed relative w-full font-semibold"
                                                type="button"
                                                data-target="#nestedAccordionCollapse-{{ $department->id }}-accounts-rivileges-management"
                                                aria-expanded="false"
                                                aria-controls="nestedAccordionCollapse-{{ $department->id }}-accounts-rivileges-management">
                                                <div class="flex items-center">
                                                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                                                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                                                    Accounts Privileges
                                                </div>
                                            </button>
                                        </div>
                                        <div id="nestedAccordionCollapse-{{ $department->id }}-accounts-rivileges-management" class="accordion-collapse collapse"
                                            aria-labelledby="nestedAccordionHeader-{{ $department->id }}-accounts-rivileges-management">
                                            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                                                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                                                    <div class="grid grid-cols-12 gap-4 items-start">
                                                        <div class="col-span-12 sm:col-span-3">
                                                            <div class="flex items-center">
                                                                <input id="accounts-privilege-{{ $department->id }}" class="form-check-input accountsPrivilegeToggle" type="checkbox" value="1" name="permissions[{{ $department->id }}][accounts_privilege]" {{ isset($permissions[$department->id]['accounts_privilege']) && $permissions[$department->id]['accounts_privilege'] ? 'checked' : '' }}>
                                                                <label for="accounts-privilege-{{ $department->id }}" class="ml-2 font-medium">Account's Privilege</label>
                                                            </div>
                                                        </div>
                                                        @php $accountsType = $permissions[$department->id]['accounts_privilege_type'] ?? ''; @endphp
                                                        <div class="col-span-12 sm:col-span-3 accountsUserTypeWrap {{ empty($permissions[$department->id]['accounts_privilege']) ? 'hidden' : '' }}">
                                                            <select id="accounts-privilege-type-{{ $department->id }}" name="permissions[{{ $department->id }}][accounts_privilege_type]" class="form-control">
                                                                <option value="">Please Select</option>
                                                                <option value="1" {{ $accountsType == 1 ? 'selected' : '' }}>Admin</option>
                                                                <option value="2" {{ $accountsType == 2 ? 'selected' : '' }}>User</option>
                                                                <option value="3" {{ $accountsType == 3 ? 'selected' : '' }}>Audit</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-span-12">
                    <div class="intro-y box p-5 ">
                        <div class="text-center">No Departments Found</div>
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>
<!-- END: Settings Page Content -->
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
@endsection

@section('script')
@vite('resources/js/settings.js')
@vite('resources/js/department-permissions.js')
<script>
    (function () {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('collapse');
            });

            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = button.getAttribute('data-target');
                    const targetContent = document.querySelector(targetId);
                    const plusIcon = button.querySelector('.accordion-icon-plus');
                    const minusIcon = button.querySelector('.accordion-icon-minus');
                    
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';

                    if (isExpanded) {
                        plusIcon.classList.remove('hidden');
                        minusIcon.classList.add('hidden');
                    } else {
                        plusIcon.classList.add('hidden');
                        minusIcon.classList.remove('hidden');
                    }
                    
                    if (!isExpanded) {
                        targetContent.classList.remove('collapse');
                        targetContent.classList.add('show');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        targetContent.classList.remove('show');
                        targetContent.classList.add('collapse');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });
    })();
</script>

@endsection