{{--
    One sub department's permission set: the full tree of permission keys for a
    single department + permission category pair.

    Extracted from the Permissions page so it can render once per category
    instead of once per department. Expects:
      $department  the department being configured
      $category    the permission category (sub department) inside it
      $values      stored key => value pairs for exactly this pair
      $internalLinks

    Field names post as permissions[department][category][key]. Element ids are
    suffixed with $uid (department-category) so two categories in the same
    department never share an id — labels, toggles and the remote-access date
    range all resolve against their own category.
--}}
@php
    $uid = $department->id.'-'.$category->id;
    $values = $values ?? [];
@endphp
<div id="nestedAccordion-{{ $uid }}" class="accordion mt-3">
    {{-- Always posted, so this pair reaches store() even when every box is
         unticked — unticked checkboxes send nothing, and a category that sent
         nothing would keep its old permissions instead of being cleared. Empty,
         so it is never written as a permission itself. --}}
    <input type="hidden" name="permissions[{{ $department->id }}][{{ $category->id }}][__present]" value="">
    <!-- Remote Access Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-remote" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-remote"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-remote">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Remote Access Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-remote" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-remote">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="flex items-center">
                        <input id="remote-allowed-{{ $uid }}" class="form-check-input" name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_allowed]" type="checkbox" value="1" {{ isset($values['remote_access_allowed']) && $values['remote_access_allowed'] ? 'checked' : '' }}>
                        <label for="remote-allowed-{{ $uid }}" class="ml-2 font-medium">Allowed</label>
                    </div>
                    <div class="flex items-center">
                        <input id="remote-temp-{{ $uid }}" class="form-check-input remoteTempToggle" name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_temp]" type="checkbox" value="1" {{ isset($values['remote_access_temp']) && $values['remote_access_temp'] ? 'checked' : '' }}>
                        <label for="remote-temp-{{ $uid }}" class="ml-2 font-medium">Allowed Temporary</label>
                    </div>

                    <div class="remoteDateRangeWrap flex items-center {{ empty($values['remote_access_temp']) ? 'hidden' : '' }}">
                        <input id="remote-date-range-{{ $uid }}" type="text" autocomplete="off"
                            class="rangepicker form-control"
                            placeholder="Select date range"
                            aria-label="Temporary access date range"
                            name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_date_range]"
                            value="{{ $values['remote_access_date_range'] ?? '' }}">
                    </div>

                    <div class="flex items-center">
                        <input id="remote-home-{{ $uid }}" class="form-check-input"
                            name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_home]" type="checkbox" value="1"
                            {{ isset($values['remote_access_home']) && $values['remote_access_home'] ? 'checked' : '' }}>
                        <label for="remote-home-{{ $uid }}" class="ml-2 font-medium">Working From Home</label>
                    </div>

                    <div class="flex items-center">
                        <input id="remote-desktop-{{ $uid }}" class="form-check-input"
                            name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_desktop]" type="checkbox" value="1"
                            {{ isset($values['remote_access_desktop']) && $values['remote_access_desktop'] ? 'checked' : '' }}>
                        <label for="remote-desktop-{{ $uid }}" class="ml-2 font-medium">Desktop Clock In</label>
                    </div>

                    <div class="flex items-center">
                        <input id="remote-all-services-{{ $uid }}" class="form-check-input"
                            name="permissions[{{ $department->id }}][{{ $category->id }}][remote_access_all_services]" type="checkbox" value="1"
                            {{ isset($values['remote_access_all_services']) && $values['remote_access_all_services'] ? 'checked' : '' }}>
                        <label for="remote-all-services-{{ $uid }}" class="ml-2 font-medium">Allow All Services</label>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Menu Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-menu" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-menu"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-menu">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Menu Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-menu" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-menu">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-2">
                            <div class="flex items-center">
                                <input id="menu-course-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_course_management]" {{ isset($values['menu_course_management']) && $values['menu_course_management'] ? 'checked' : '' }}>
                                <label for="menu-course-{{ $uid }}" class="ml-2 font-medium">Course Management</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="course-semesters-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_course_semesters]" {{ isset($values['menu_course_semesters']) && $values['menu_course_semesters'] ? 'checked' : '' }}>
                                    <label for="course-semesters-{{ $uid }}" class="ml-2">Course & Semesters</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="terms-modules-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_terms_modules]" {{ isset($values['menu_terms_modules']) && $values['menu_terms_modules'] ? 'checked' : '' }}>
                                    <label for="terms-modules-{{ $uid }}" class="ml-2">Terms & Modules</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="menu_plans-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_plans]" {{ isset($values['menu_plans']) && $values['menu_plans'] ? 'checked' : '' }}>
                                    <label for="menu_plans-{{ $uid }}" class="ml-2">Plans</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="plan-list-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_plan_list]" {{ isset($values['menu_plan_list']) && $values['menu_plan_list'] ? 'checked' : '' }}>
                                    <label for="plan-list-{{ $uid }}" class="ml-2">Plan List</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="plan-tree-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_plan_tree]" {{ isset($values['menu_plan_tree']) && $values['menu_plan_tree'] ? 'checked' : '' }}>
                                    <label for="plan-tree-{{ $uid }}" class="ml-2">Plan Tree</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-2">
                            <div class="flex items-center">
                                <input id="menu-student-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_student_management]" {{ isset($values['menu_student_management']) && $values['menu_student_management'] ? 'checked' : '' }}>
                                <label for="menu-student-{{ $uid }}" class="ml-2 font-medium">Student Management</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="generate-letter-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_generate_letter]" {{ isset($values['menu_generate_letter']) && $values['menu_generate_letter'] ? 'checked' : '' }}>
                                    <label for="generate-letter-{{ $uid }}" class="ml-2">Generate Letter</label>
                                </div>
                                <div class="flex items-center mt-2">
                                        <input id="send-email-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_send_email]" {{ isset($values['menu_send_email']) && $values['menu_send_email'] ? 'checked' : '' }}>
                                        <label for="send-email-{{ $uid }}" class="ml-2">Send Email</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="send-sms-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_send_sms]" {{ isset($values['menu_send_sms']) && $values['menu_send_sms'] ? 'checked' : '' }}>
                                    <label for="send-sms-{{ $uid }}" class="ml-2">Send SMS</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-8">
                            <div class="flex items-center">
                                <input id="menu-settings-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_settings]" {{ isset($values['menu_settings']) && $values['menu_settings'] ? 'checked' : '' }}>
                                <label for="menu-settings-{{ $uid }}" class="ml-2 font-medium">Settings</label>
                            </div>
                            <div class="pl-8 mt-2 grid grid-cols-12 gap-2">
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-site-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_site_settings]" {{ isset($values['menu_site_settings']) && $values['menu_site_settings'] ? 'checked' : '' }}>
                                    <label for="settings-site-{{ $uid }}" class="ml-2">Site Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-course-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_course_parameters]" {{ isset($values['menu_course_parameters']) && $values['menu_course_parameters'] ? 'checked' : '' }}>
                                    <label for="settings-course-{{ $uid }}" class="ml-2">Course Parameters</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-campus-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_campus_settings]" {{ isset($values['menu_campus_settings']) && $values['menu_campus_settings'] ? 'checked' : '' }}>
                                    <label for="settings-campus-{{ $uid }}" class="ml-2">Campus Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-applicant-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_applicant_settings]" {{ isset($values['menu_applicant_settings']) && $values['menu_applicant_settings'] ? 'checked' : '' }}>
                                    <label for="settings-applicant-{{ $uid }}" class="ml-2">Applicant Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-student-options-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_student_option_values]" {{ isset($values['menu_student_option_values']) && $values['menu_student_option_values'] ? 'checked' : '' }}>
                                    <label for="settings-student-options-{{ $uid }}" class="ml-2">Student Option Values</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-student-flags-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_student_flags]" {{ isset($values['menu_student_flags']) && $values['menu_student_flags'] ? 'checked' : '' }}>
                                    <label for="settings-student-flags-{{ $uid }}" class="ml-2">Student Flags</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-communication-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_communication_settings]" {{ isset($values['menu_communication_settings']) && $values['menu_communication_settings'] ? 'checked' : '' }}>
                                    <label for="settings-communication-{{ $uid }}" class="ml-2">Communication Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-elearning-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_elearning_activity_setting]" {{ isset($values['menu_elearning_activity_setting']) && $values['menu_elearning_activity_setting'] ? 'checked' : '' }}>
                                    <label for="settings-elearning-{{ $uid }}" class="ml-2">E-Learning Activity Setting</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-user-privilege-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_user_privilege]" {{ isset($values['menu_user_privilege']) && $values['menu_user_privilege'] ? 'checked' : '' }}>
                                    <label for="settings-user-privilege-{{ $uid }}" class="ml-2">User Privilege</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-hr-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_hr_settings]" {{ isset($values['menu_hr_settings']) && $values['menu_hr_settings'] ? 'checked' : '' }}>
                                    <label for="settings-hr-{{ $uid }}" class="ml-2">HR Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-datafuture-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_datafuture_settings]" {{ isset($values['menu_datafuture_settings']) && $values['menu_datafuture_settings'] ? 'checked' : '' }}>
                                    <label for="settings-datafuture-{{ $uid }}" class="ml-2">Datafuture Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex items-center">
                                    <input id="settings-internal-link-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_internal_site_link]" {{ isset($values['menu_internal_site_link']) && $values['menu_internal_site_link'] ? 'checked' : '' }}>
                                    <label for="settings-internal-link-{{ $uid }}" class="ml-2">Internal Site Link</label>
                                </div>
                                <div class="col-span-12 sm:col-span-4 flex">
                                    <input id="settings-accounts-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_accounts_settings]" {{ isset($values['menu_accounts_settings']) && $values['menu_accounts_settings'] ? 'checked' : '' }}>
                                    <label for="settings-accounts-{{ $uid }}" class="ml-2">Accounts Settings</label>
                                </div>
                                <div class="col-span-12 sm:col-span-8 flex">
                                    <input id="settings-file-manager-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_file_manager_settings]" {{ isset($values['menu_file_manager_settings']) && $values['menu_file_manager_settings'] ? 'checked' : '' }}>
                                    <label for="settings-file-manager-{{ $uid }}" class="ml-2">File Manager Settings</label>
                                </div>
                                <div class="col-span-12">
                                    <div class="flex items-center">
                                        <input id="settings-workplacement-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_workplacement]" {{ isset($values['menu_workplacement']) && $values['menu_workplacement'] ? 'checked' : '' }}>
                                        <label for="settings-workplacement-{{ $uid }}" class="ml-2">Workplacement</label>
                                    </div>
                                    <div class="pl-8 mt-2">
                                        <div class="flex items-center mt-2">
                                            <input id="workplacement-details-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_workplacement_details]" {{ isset($values['menu_workplacement_details']) && $values['menu_workplacement_details'] ? 'checked' : '' }}>
                                            <label for="workplacement-details-{{ $uid }}" class="ml-2">Workplacement Details</label>
                                        </div>
                                        <div class="flex items-center mt-2">
                                            <input id="workplacement-companies-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_workplacement_companies]" {{ isset($values['menu_workplacement_companies']) && $values['menu_workplacement_companies'] ? 'checked' : '' }}>
                                            <label for="workplacement-companies-{{ $uid }}" class="ml-2">Workplacement Companies / Supervisor</label>
                                        </div>
                                        <div class="flex items-center mt-2">
                                            <input id="workplacement-settings-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][menu_workplacement_settings]" {{ isset($values['menu_workplacement_settings']) && $values['menu_workplacement_settings'] ? 'checked' : '' }}>
                                            <label for="workplacement-settings-{{ $uid }}" class="ml-2">Workplacement Settings</label>
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
        <div id="nestedAccordionHeader-{{ $uid }}-dashboard" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-dashboard"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-dashboard">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Dashboard Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-dashboard" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-dashboard">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-applicant-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_applicant]" {{ isset($values['dashboard_applicant']) && $values['dashboard_applicant'] ? 'checked' : '' }}>
                                <label for="dashboard-applicant-{{ $uid }}" class="ml-2 font-medium">Applicant</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="applicant-analysis-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_applicant_analysis]" {{ isset($values['dashboard_applicant_analysis']) && $values['dashboard_applicant_analysis'] ? 'checked' : '' }}>
                                    <label for="applicant-analysis-{{ $uid }}" class="ml-2">Application Analysis</label>
                                </div>
                                <div class="flex items-center mt-2">
                                     <input id="applicant-rejected-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_applicant_rejected]" {{ isset($values['dashboard_applicant_rejected']) && $values['dashboard_applicant_rejected'] ? 'checked' : '' }}>
                                    <label for="applicant-rejected-{{ $uid }}" class="ml-2">Reject / In Progress Application</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                 <input id="dashboard-interviews-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_interviews]" {{ isset($values['dashboard_interviews']) && $values['dashboard_interviews'] ? 'checked' : '' }}>
                                <label for="dashboard-interviews-{{ $uid }}" class="ml-2 font-medium">Required Interviews</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                               <input id="dashboard-news-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_news]" {{ isset($values['dashboard_news']) && $values['dashboard_news'] ? 'checked' : '' }}>
                                <label for="dashboard-news-{{ $uid }}" class="ml-2 font-medium">News & Events</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                 <input id="dashboard-student-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_live_student]" {{ isset($values['dashboard_live_student']) && $values['dashboard_live_student'] ? 'checked' : '' }}>
                                <label for="dashboard-student-{{ $uid }}" class="ml-2 font-medium">Live Student</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                 <input id="dashboard-hr-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_hr_portal]" {{ isset($values['dashboard_hr_portal']) && $values['dashboard_hr_portal'] ? 'checked' : '' }}>
                                <label for="dashboard-hr-{{ $uid }}" class="ml-2 font-medium">HR Portal</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-due-report-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_due_report]" {{ isset($values['dashboard_due_report']) && $values['dashboard_due_report'] ? 'checked' : '' }}>
                                <label for="dashboard-due-report-{{ $uid }}" class="ml-2 font-medium">Student Due Report</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-tutor-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_tutor]" {{ isset($values['dashboard_tutor']) && $values['dashboard_tutor'] ? 'checked' : '' }}>
                                <label for="dashboard-tutor-{{ $uid }}" class="ml-2 font-medium">Tutor Dashboard</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-personal-tutor-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_personal_tutor]" {{ isset($values['dashboard_personal_tutor']) && $values['dashboard_personal_tutor'] ? 'checked' : '' }}>
                                <label for="dashboard-personal-tutor-{{ $uid }}" class="ml-2 font-medium">Personal Tutor Dashboard</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-group-leader-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_group_leader]" {{ isset($values['dashboard_group_leader']) && $values['dashboard_group_leader'] ? 'checked' : '' }}>
                                <label for="dashboard-group-leader-{{ $uid }}" class="ml-2 font-medium">Group Leader</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="group-leader-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_group_leader_view]" {{ isset($values['dashboard_group_leader_view']) && $values['dashboard_group_leader_view'] ? 'checked' : '' }}>
                                    <label for="group-leader-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="group-leader-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_group_leader_add]" {{ isset($values['dashboard_group_leader_add']) && $values['dashboard_group_leader_add'] ? 'checked' : '' }}>
                                    <label for="group-leader-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="group-leader-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_group_leader_edit]" {{ isset($values['dashboard_group_leader_edit']) && $values['dashboard_group_leader_edit'] ? 'checked' : '' }}>
                                    <label for="group-leader-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="group-leader-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_group_leader_delete]" {{ isset($values['dashboard_group_leader_delete']) && $values['dashboard_group_leader_delete'] ? 'checked' : '' }}>
                                    <label for="group-leader-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                 <input id="dashboard-programme-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_programme]" {{ isset($values['dashboard_programme']) && $values['dashboard_programme'] ? 'checked' : '' }}>
                                <label for="dashboard-programme-{{ $uid }}" class="ml-2 font-medium">Programme Dashboard</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="programme-reports-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_programme_reports]" {{ isset($values['dashboard_programme_reports']) && $values['dashboard_programme_reports'] ? 'checked' : '' }}>
                                    <label for="programme-reports-{{ $uid }}" class="ml-2">Reports</label>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input id="programme-student-data-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_programme_student_data]" {{ isset($values['dashboard_programme_student_data']) && $values['dashboard_programme_student_data'] ? 'checked' : '' }}>
                                    <label for="programme-student-data-{{ $uid }}" class="ml-2">Student Data Report Other Details Show</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-file-manager-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_file_manager]" {{ isset($values['dashboard_file_manager']) && $values['dashboard_file_manager'] ? 'checked' : '' }}>
                                <label for="dashboard-file-manager-{{ $uid }}" class="ml-2 font-medium">File Manager</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-expired-docs-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_expired_docs]" {{ isset($values['dashboard_expired_docs']) && $values['dashboard_expired_docs'] ? 'checked' : '' }}>
                                <label for="dashboard-expired-docs-{{ $uid }}" class="ml-2 font-medium">Expired Documents</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="dashboard-report-issue-{{ $uid }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_report_issue]" {{ isset($values['dashboard_report_issue']) && $values['dashboard_report_issue'] ? 'checked' : '' }}>
                                <label for="dashboard-report-issue-{{ $uid }}" class="ml-2 font-medium">Report Issue</label>
                            </div>
                            <div class="pl-4 mt-2 space-y-2 childrenPermissionWrap">
                                <div class="flex items-center">
                                    <input id="dashboard-show-all-issue-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][dashboard_show_all_issue]" {{ isset($values['dashboard_show_all_issue']) && $values['dashboard_show_all_issue'] ? 'checked' : '' }} {{ empty($values['dashboard_report_issue']) ? 'disabled' : '' }}>
                                    <label for="dashboard-show-all-issue-{{ $uid }}" class="ml-2">Show All Issue</label>
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
        <div id="nestedAccordionHeader-{{ $uid }}-staff" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-staff"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-staff">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Staff Profile Privilege
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-staff" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-staff">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="flex items-center">
                        <input id="staff-group-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][staff_profile_staff_group]" {{ isset($values['staff_profile_staff_group']) && $values['staff_profile_staff_group'] ? 'checked' : '' }}>
                        <label for="staff-group-{{ $uid }}" class="ml-2 font-medium">Staff Group</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HR Portal Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-hr" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-hr"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-hr">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    HR Portal Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-hr" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-hr">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="flex items-center">
                        <input id="hr-add-attendance-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][hr_portal_add_attendance]" {{ isset($values['hr_portal_add_attendance']) && $values['hr_portal_add_attendance'] ? 'checked' : '' }}>
                        <label for="hr-add-attendance-{{ $uid }}" class="ml-2 font-medium">Add Attendance</label>
                    </div>
                    <div class="flex items-center">
                        <input id="hr-delete-attendance-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][hr_portal_delete_attendance]" {{ isset($values['hr_portal_delete_attendance']) && $values['hr_portal_delete_attendance'] ? 'checked' : '' }}>
                        <label for="hr-delete-attendance-{{ $uid }}" class="ml-2 font-medium">Delete Attendance</label>
                    </div>
                    <div class="flex items-center">
                      <input id="hr-privilege-menu-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][hr_portal_privilege_menu]" {{ isset($values['hr_portal_privilege_menu']) && $values['hr_portal_privilege_menu'] ? 'checked' : '' }}>
                        <label for="hr-privilege-menu-{{ $uid }}" class="ml-2 font-medium">Privilege Menu</label>
                    </div>
                    <div class="flex items-center">
                         <input id="hr-edit-email-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][hr_portal_edit_email]" {{ isset($values['hr_portal_edit_email']) && $values['hr_portal_edit_email'] ? 'checked' : '' }}>
                        <label for="hr-edit-email-{{ $uid }}" class="ml-2 font-medium">Edit User Email</label>
                    </div>
                    <div class="flex items-center">
                         <input id="hr-login-as-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][hr_portal_login_as]" {{ isset($values['hr_portal_login_as']) && $values['hr_portal_login_as'] ? 'checked' : '' }}>
                        <label for="hr-login-as-{{ $uid }}" class="ml-2 font-medium">Login As User</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applicant Portal Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-applicant" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-applicant"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-applicant">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Applicant Portal Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-applicant" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-applicant">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="flex items-center">
                       <input id="applicant-login-as-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][applicant_portal_login_as]" {{ isset($values['applicant_portal_login_as']) && $values['applicant_portal_login_as'] ? 'checked' : '' }}>
                        <label for="applicant-login-as-{{ $uid }}" class="ml-2 font-medium">Login as Applicant</label>
                    </div>
                    <div class="flex items-center">
                        <input id="applicant-create-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][applicant_portal_create]" {{ isset($values['applicant_portal_create']) && $values['applicant_portal_create'] ? 'checked' : '' }}>
                        <label for="applicant-create-{{ $uid }}" class="ml-2 font-medium">Create Applicant Account</label>
                    </div>
                    <div class="flex items-center">
                        <input id="applicant-e-signature-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][applicant_portal_e_signature]" {{ isset($values['applicant_portal_e_signature']) && $values['applicant_portal_e_signature'] ? 'checked' : '' }}>
                        <label for="applicant-e-signature-{{ $uid }}" class="ml-2 font-medium">E-Signature Request</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Student Portal Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-student" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-student"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-student">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Live Student Portal Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-student" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-student">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4">
                      
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Results</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="results-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_results_view]" {{ isset($values['live_student_results_view']) && $values['live_student_results_view'] ? 'checked' : '' }}>
                                    <label for="results-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="results-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_results_add]" {{ isset($values['live_student_results_add']) && $values['live_student_results_add'] ? 'checked' : '' }}>
                                    <label for="results-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="results-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_results_edit]" {{ isset($values['live_student_results_edit']) && $values['live_student_results_edit'] ? 'checked' : '' }}>
                                    <label for="results-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="results-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_results_delete]" {{ isset($values['live_student_results_delete']) && $values['live_student_results_delete'] ? 'checked' : '' }}>
                                    <label for="results-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Attendance</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="attendance-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_attendance_view]" {{ isset($values['live_student_attendance_view']) && $values['live_student_attendance_view'] ? 'checked' : '' }}>
                                    <label for="attendance-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="attendance-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_attendance_add]" {{ isset($values['live_student_attendance_add']) && $values['live_student_attendance_add'] ? 'checked' : '' }}>
                                    <label for="attendance-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="attendance-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_attendance_edit]" {{ isset($values['live_student_attendance_edit']) && $values['live_student_attendance_edit'] ? 'checked' : '' }}>
                                    <label for="attendance-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="attendance-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_attendance_delete]" {{ isset($values['live_student_attendance_delete']) && $values['live_student_attendance_delete'] ? 'checked' : '' }}>
                                    <label for="attendance-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Accounts</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="accounts-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_accounts_view]" {{ isset($values['live_student_accounts_view']) && $values['live_student_accounts_view'] ? 'checked' : '' }}>
                                    <label for="accounts-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                   <input id="accounts-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_accounts_add]" {{ isset($values['live_student_accounts_add']) && $values['live_student_accounts_add'] ? 'checked' : '' }}>
                                    <label for="accounts-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="accounts-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_accounts_edit]" {{ isset($values['live_student_accounts_edit']) && $values['live_student_accounts_edit'] ? 'checked' : '' }}>
                                    <label for="accounts-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="accounts-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_accounts_delete]" {{ isset($values['live_student_accounts_delete']) && $values['live_student_accounts_delete'] ? 'checked' : '' }}>
                                    <label for="accounts-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">SLC History</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="slc-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_slc_view]" {{ isset($values['live_student_slc_view']) && $values['live_student_slc_view'] ? 'checked' : '' }}>
                                    <label for="slc-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="slc-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_slc_add]" {{ isset($values['live_student_slc_add']) && $values['live_student_slc_add'] ? 'checked' : '' }}>
                                    <label for="slc-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="slc-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_slc_edit]" {{ isset($values['live_student_slc_edit']) && $values['live_student_slc_edit'] ? 'checked' : '' }}>
                                    <label for="slc-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="slc-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_slc_delete]" {{ isset($values['live_student_slc_delete']) && $values['live_student_slc_delete'] ? 'checked' : '' }}>
                                    <label for="slc-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Other Course Relation</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                      <input id="course-relation-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_course_relation_view]" {{ isset($values['live_student_course_relation_view']) && $values['live_student_course_relation_view'] ? 'checked' : '' }}>
                                    <label for="course-relation-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Performance</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="performance-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_performance_view]" {{ isset($values['live_student_performance_view']) && $values['live_student_performance_view'] ? 'checked' : '' }}>
                                    <label for="performance-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Print Application Form</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                   <input id="print-app-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_print_app_view]" {{ isset($values['live_student_print_app_view']) && $values['live_student_print_app_view'] ? 'checked' : '' }}>
                                    <label for="print-app-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Student Archives</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="archives-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_archives_view]" {{ isset($values['live_student_archives_view']) && $values['live_student_archives_view'] ? 'checked' : '' }}>
                                    <label for="archives-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Workplacement</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="workplacement-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_workplacement_add]" {{ isset($values['live_student_workplacement_add']) && $values['live_student_workplacement_add'] ? 'checked' : '' }}>
                                    <label for="workplacement-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                      <input id="workplacement-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_workplacement_edit]" {{ isset($values['live_student_workplacement_edit']) && $values['live_student_workplacement_edit'] ? 'checked' : '' }}>
                                    <label for="workplacement-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="workplacement-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_workplacement_delete]" {{ isset($values['live_student_workplacement_delete']) && $values['live_student_workplacement_delete'] ? 'checked' : '' }}>
                                    <label for="workplacement-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Visit</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                     <input id="visit-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_visit_view]" {{ isset($values['live_student_visit_view']) && $values['live_student_visit_view'] ? 'checked' : '' }}>
                                    <label for="visit-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="visit-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_visit_add]" {{ isset($values['live_student_visit_add']) && $values['live_student_visit_add'] ? 'checked' : '' }}>
                                    <label for="visit-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="visit-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_visit_edit]" {{ isset($values['live_student_visit_edit']) && $values['live_student_visit_edit'] ? 'checked' : '' }}>
                                    <label for="visit-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="visit-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_visit_delete]" {{ isset($values['live_student_visit_delete']) && $values['live_student_visit_delete'] ? 'checked' : '' }}>
                                    <label for="visit-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label class="font-medium">Communications</label>
                            <div class="pl-4 mt-2 grid grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <input id="comms-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_view]" {{ isset($values['live_student_comms_view']) && $values['live_student_comms_view'] ? 'checked' : '' }}>
                                    <label for="comms-view-{{ $uid }}" class="ml-2">View Communication</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="comms-send-letter-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_send_letter]" {{ isset($values['live_student_comms_send_letter']) && $values['live_student_comms_send_letter'] ? 'checked' : '' }}>
                                    <label for="comms-send-letter-{{ $uid }}" class="ml-2">Send Letter</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="comms-delete-letter-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_delete_letter]" {{ isset($values['live_student_comms_delete_letter']) && $values['live_student_comms_delete_letter'] ? 'checked' : '' }}>
                                    <label for="comms-delete-letter-{{ $uid }}" class="ml-2">Delete Letter</label>
                                </div>
                                <div class="flex items-center">
                                     <input id="comms-send-email-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_send_email]" {{ isset($values['live_student_comms_send_email']) && $values['live_student_comms_send_email'] ? 'checked' : '' }}>
                                    <label for="comms-send-email-{{ $uid }}" class="ml-2">Send Email</label>
                                </div>
                                <div class="flex items-center">
                                   <input id="comms-delete-email-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_delete_email]" {{ isset($values['live_student_comms_delete_email']) && $values['live_student_comms_delete_email'] ? 'checked' : '' }}>
                                    <label for="comms-delete-email-{{ $uid }}" class="ml-2">Delete Email</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="comms-send-sms-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_send_sms]" {{ isset($values['live_student_comms_send_sms']) && $values['live_student_comms_send_sms'] ? 'checked' : '' }}>
                                    <label for="comms-send-sms-{{ $uid }}" class="ml-2">Send SMS</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="comms-delete-sms-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_comms_delete_sms]" {{ isset($values['live_student_comms_delete_sms']) && $values['live_student_comms_delete_sms'] ? 'checked' : '' }}>
                                    <label for="comms-delete-sms-{{ $uid }}" class="ml-2">Delete SMS</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Documents</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="docs-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_docs_view]" {{ isset($values['live_student_docs_view']) && $values['live_student_docs_view'] ? 'checked' : '' }}>
                                    <label for="docs-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="docs-add-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_docs_add]" {{ isset($values['live_student_docs_add']) && $values['live_student_docs_add'] ? 'checked' : '' }}>
                                    <label for="docs-add-{{ $uid }}" class="ml-2">Add</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="docs-delete-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_docs_delete]" {{ isset($values['live_student_docs_delete']) && $values['live_student_docs_delete'] ? 'checked' : '' }}>
                                    <label for="docs-delete-{{ $uid }}" class="ml-2">Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <input id="student-change-status-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_change_status]" {{ isset($values['live_student_change_status']) && $values['live_student_change_status'] ? 'checked' : '' }}>
                            <label for="student-change-status-{{ $uid }}" class="ml-2 font-medium">Change Status</label>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <input id="student-login-as-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_login_as]" {{ isset($values['live_student_login_as']) && $values['live_student_login_as'] ? 'checked' : '' }}>
                            <label for="student-login-as-{{ $uid }}" class="ml-2 font-medium">Login as Student</label>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Student Other Personal Info</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="other-personal-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_other_personal_view]" {{ isset($values['live_student_other_personal_view']) && $values['live_student_other_personal_view'] ? 'checked' : '' }}>
                                    <label for="other-personal-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="other-personal-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_other_personal_edit]" {{ isset($values['live_student_other_personal_edit']) && $values['live_student_other_personal_edit'] ? 'checked' : '' }}>
                                    <label for="other-personal-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Residency Status and Criminal Convictions</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="residency-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_residency_view]" {{ isset($values['live_student_residency_view']) && $values['live_student_residency_view'] ? 'checked' : '' }}>
                                    <label for="residency-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="residency-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_residency_edit]" {{ isset($values['live_student_residency_edit']) && $values['live_student_residency_edit'] ? 'checked' : '' }}>
                                    <label for="residency-edit-{{ $uid }}" class="ml-2">Edit</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Datafuture</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="datafuture-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_datafuture_view]" {{ isset($values['live_student_datafuture_view']) && $values['live_student_datafuture_view'] ? 'checked' : '' }}>
                                    <label for="datafuture-view-{{ $uid }}" class="ml-2">View</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="datafuture-edit-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_datafuture_edit]" {{ isset($values['live_student_datafuture_edit']) && $values['live_student_datafuture_edit'] ? 'checked' : '' }}>
                                    <label for="datafuture-edit-{{ $uid }}" class="ml-2">Add/Edit/Delete</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="font-medium">Student Login Logs</label>
                            <div class="pl-4 mt-2 space-y-2">
                                <div class="flex items-center">
                                    <input id="student-logs-view-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][live_student_logs_view]" {{ isset($values['live_student_logs_view']) && $values['live_student_logs_view'] ? 'checked' : '' }}>
                                    <label for="student-logs-view-{{ $uid }}" class="ml-2">View</label>
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
        <div id="nestedAccordionHeader-{{ $uid }}-module" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-module"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-module">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Module Content Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-module" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-module">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4 items-start">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="module-participants-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][module_content_participants]" {{ isset($values['module_content_participants']) && $values['module_content_participants'] ? 'checked' : '' }}>
                                <label for="module-participants-{{ $uid }}" class="ml-2 font-medium">Participants</label>
                            </div>
                            <div class="pl-8 mt-2">
                                <div class="flex items-center mt-2">
                                    <input id="participants-export-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][participants_export]" {{ isset($values['participants_export']) && $values['participants_export'] ? 'checked' : '' }}>
                                    <label for="participants-export-{{ $uid }}" class="ml-2">Export</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="module-assessment-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][module_content_assessment]" {{ isset($values['module_content_assessment']) && $values['module_content_assessment'] ? 'checked' : '' }}>
                                <label for="module-assessment-{{ $uid }}" class="ml-2 font-medium">Assessment</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                              <input id="module-analytics-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][module_content_analytics]" {{ isset($values['module_content_analytics']) && $values['module_content_analytics'] ? 'checked' : '' }}>
                                <label for="module-analytics-{{ $uid }}" class="ml-2 font-medium">Analytics</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="module-edit-attendance-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][module_content_edit_attendance]" {{ isset($values['module_content_edit_attendance']) && $values['module_content_edit_attendance'] ? 'checked' : '' }}>
                                <label for="module-edit-attendance-{{ $uid }}" class="ml-2 font-medium">Edit Attendance</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Library Management Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-library" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-library"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-library">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Library Management Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-library" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-library">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4 items-start">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="library-settings-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][library_management]" {{ isset($values['library_management']) && $values['library_management'] ? 'checked' : '' }}>
                                <label for="library-settings-{{ $uid }}" class="ml-2 font-medium">Library Management</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Result Management Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-result-management" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-result-management"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-result-management">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Result Management Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-result-management" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-result-management">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4 items-start">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="result-management-staff-upload-permission-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][result_staff_upload]" {{ isset($values['result_staff_upload']) && $values['result_staff_upload'] ? 'checked' : '' }}>
                                <label for="result-management-staff-upload-permission-{{ $uid }}" class="ml-2 font-medium">Staff Upload Permission</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                            <input id="result-management-staff-delete-permission-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][result_staff_delete]" {{ isset($values['result_staff_delete']) && $values['result_staff_delete'] ? 'checked' : '' }}>
                                <label for="result-management-staff-delete-permission-{{ $uid }}" class="ml-2 font-medium">Staff Delete Permission</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                            <input id="result-management-PT-upload-permission-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][result_pt_upload]" {{ isset($values['result_pt_upload']) && $values['result_pt_upload'] ? 'checked' : '' }}>
                                <label for="result-management-PT-upload-permission-{{ $uid }}" class="ml-2 font-medium">PT Upload Permission</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Internal Links Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-internal-links-management" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-internal-links-management"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-internal-links-management">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Internal Links Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-internal-links-management" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-internal-links-management">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4">
                        @foreach($internalLinks as $link)
                            @php $hasChildren = $link->children->count() > 0; @endphp
                            <div class="col-span-12 {{ $hasChildren ? '' : 'sm:col-span-3' }}">
                                <div class="flex items-center font-medium">
                                    <input id="internal-link-{{ $uid }}-{{ $link->id }}" class="form-check-input {{ $hasChildren ? 'parentPermissionItem' : '' }}" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][internal_link_{{ $link->id }}]" {{ !empty($values['internal_link_'.$link->id]) ? 'checked' : '' }}>
                                    <label for="internal-link-{{ $uid }}-{{ $link->id }}" class="ml-2">{{ $link->name }}</label>
                                </div>
                                @if($hasChildren)
                                    <div class="pl-8 mt-2 grid grid-cols-12 gap-2 childrenPermissionWrap">
                                        @foreach($link->children as $child)
                                            <div class="col-span-12 sm:col-span-4 flex items-center">
                                                <input id="internal-link-{{ $uid }}-{{ $child->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][internal_link_{{ $child->id }}]" {{ !empty($values['internal_link_'.$child->id]) ? 'checked' : '' }} {{ empty($values['internal_link_'.$link->id]) ? 'disabled' : '' }}>
                                                <label for="internal-link-{{ $uid }}-{{ $child->id }}" class="ml-2">{{ $child->name }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Not rows in internal_links, so they stay as fixed keys (as in the legacy form). --}}
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center font-medium">
                                <input id="internal-link-group-email-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][internal_link_group_email]" {{ !empty($values['internal_link_group_email']) ? 'checked' : '' }}>
                                <label for="internal-link-group-email-{{ $uid }}" class="ml-2">Group Email</label>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center font-medium">
                                <input id="internal-link-staff-upload-{{ $uid }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][internal_link_staff_upload_permission]" {{ !empty($values['internal_link_staff_upload_permission']) ? 'checked' : '' }}>
                                <label for="internal-link-staff-upload-{{ $uid }}" class="ml-2">Staff Upload Permission</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Accounts Privileges -->
    <div class="accordion-item bg-gray-100 rounded-lg my-2 p-4">
        <div id="nestedAccordionHeader-{{ $uid }}-accounts-rivileges-management" class="accordion-header flex justify-between">
            <button class="accordion-button collapsed relative w-full font-semibold"
                type="button"
                data-target="#nestedAccordionCollapse-{{ $uid }}-accounts-rivileges-management"
                aria-expanded="false"
                aria-controls="nestedAccordionCollapse-{{ $uid }}-accounts-rivileges-management">
                <div class="flex items-center">
                    <i data-lucide="plus" class="w-6 h-6 mr-2 accordion-icon-plus"></i>
                    <i data-lucide="minus" class="w-6 h-6 mr-2 accordion-icon-minus hidden"></i>
                    Accounts Privileges
                </div>
            </button>
        </div>
        <div id="nestedAccordionCollapse-{{ $uid }}-accounts-rivileges-management" class="accordion-collapse collapse"
            aria-labelledby="nestedAccordionHeader-{{ $uid }}-accounts-rivileges-management">
            <div class="accordion-body pl-5 text-slate-600 leading-relaxed">
                <div class="p-3 rounded-lg my-2 mx-4 bg-white">
                    <div class="grid grid-cols-12 gap-4 items-start">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="flex items-center">
                                <input id="accounts-privilege-{{ $uid }}" class="form-check-input accountsPrivilegeToggle" type="checkbox" value="1" name="permissions[{{ $department->id }}][{{ $category->id }}][accounts_privilege]" {{ isset($values['accounts_privilege']) && $values['accounts_privilege'] ? 'checked' : '' }}>
                                <label for="accounts-privilege-{{ $uid }}" class="ml-2 font-medium">Account's Privilege</label>
                            </div>
                        </div>
                        @php $accountsType = $values['accounts_privilege_type'] ?? ''; @endphp
                        <div class="col-span-12 sm:col-span-3 accountsUserTypeWrap {{ empty($values['accounts_privilege']) ? 'hidden' : '' }}">
                            <select id="accounts-privilege-type-{{ $uid }}" name="permissions[{{ $department->id }}][{{ $category->id }}][accounts_privilege_type]" class="form-control">
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
