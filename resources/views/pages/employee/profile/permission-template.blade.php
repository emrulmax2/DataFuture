@php
    // $d keys every field name so a loaded template stays tied to its department;
    // 0 means no template, and the controller stores that as a null department_id.
    $d = (int) $department_id;
    $p = $permissions[$d] ?? [];

    $on = fn($key) => !empty($p[$key]) ? 'checked' : '';
    $off = fn($parentKey) => empty($p[$parentKey]) ? 'disabled' : '';
    $val = fn($key) => $p[$key] ?? '';
@endphp

<!-- Remote Access Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Remote Access Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('remote_access_allowed') }} id="remote-allowed-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][remote_access_allowed]">
                    <label class="form-check-label ml-4" for="remote-allowed-{{ $d }}">Allowed</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('remote_access_temp') }} id="remote-temp-{{ $d }}" class="form-check-input remoteTempToggle" type="checkbox" value="1" name="permissions[{{ $d }}][remote_access_temp]">
                    <label class="form-check-label ml-4" for="remote-temp-{{ $d }}">Allowed Temporary</label>
                </div>
                <div class="remoteDateRangeWrap pt-4 {{ empty($p['remote_access_temp']) ? 'hidden' : '' }}">
                    <input id="remote-date-range-{{ $d }}" type="text" autocomplete="off"
                        class="rangepicker form-control w-full"
                        placeholder="Select date range"
                        aria-label="Temporary access date range"
                        name="permissions[{{ $d }}][remote_access_date_range]"
                        value="{{ $val('remote_access_date_range') }}">
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('remote_access_home') }} id="remote-home-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][remote_access_home]">
                    <label class="form-check-label ml-4" for="remote-home-{{ $d }}">Working From Home</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('remote_access_desktop') }} id="remote-desktop-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][remote_access_desktop]">
                    <label class="form-check-label ml-4" for="remote-desktop-{{ $d }}">Desktop Clock In</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('remote_access_all_services') }} id="remote-all-services-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][remote_access_all_services]">
                    <label class="form-check-label ml-4" for="remote-all-services-{{ $d }}">Allow All Services</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menu Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Menu Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('menu_course_management') }} id="menu-course-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][menu_course_management]">
                    <label class="form-check-label ml-4" for="menu-course-{{ $d }}">Course Management</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_course_semesters') }} {{ $off('menu_course_management') }} id="course-semesters-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_course_semesters]">
                        <label class="form-check-label ml-4" for="course-semesters-{{ $d }}">Course &amp; Semesters</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_terms_modules') }} {{ $off('menu_course_management') }} id="terms-modules-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_terms_modules]">
                        <label class="form-check-label ml-4" for="terms-modules-{{ $d }}">Terms &amp; Modules</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_plans') }} {{ $off('menu_course_management') }} id="menu-plans-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_plans]">
                        <label class="form-check-label ml-4" for="menu-plans-{{ $d }}">Plans</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_plan_list') }} {{ $off('menu_course_management') }} id="plan-list-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_plan_list]">
                        <label class="form-check-label ml-4" for="plan-list-{{ $d }}">Plan List</label>
                    </div>
                    <div class="form-check form-switch">
                        <input {{ $on('menu_plan_tree') }} {{ $off('menu_course_management') }} id="plan-tree-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_plan_tree]">
                        <label class="form-check-label ml-4" for="plan-tree-{{ $d }}">Plan Tree</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('menu_student_management') }} id="menu-student-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][menu_student_management]">
                    <label class="form-check-label ml-4" for="menu-student-{{ $d }}">Student Management</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_generate_letter') }} {{ $off('menu_student_management') }} id="generate-letter-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_generate_letter]">
                        <label class="form-check-label ml-4" for="generate-letter-{{ $d }}">Generate Letter</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('menu_send_email') }} {{ $off('menu_student_management') }} id="send-email-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_send_email]">
                        <label class="form-check-label ml-4" for="send-email-{{ $d }}">Send Email</label>
                    </div>
                    <div class="form-check form-switch">
                        <input {{ $on('menu_send_sms') }} {{ $off('menu_student_management') }} id="send-sms-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_send_sms]">
                        <label class="form-check-label ml-4" for="send-sms-{{ $d }}">Send SMS</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-6">
                <div class="form-check form-switch">
                    <input {{ $on('menu_settings') }} id="menu-settings-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][menu_settings]">
                    <label class="form-check-label ml-4" for="menu-settings-{{ $d }}">Settings</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12 grid grid-cols-12">
                    @php
                        $settingsChildren = [
                            'menu_site_settings' => 'Site Settings',
                            'menu_course_parameters' => 'Course Parameters',
                            'menu_campus_settings' => 'Campus Settings',
                            'menu_applicant_settings' => 'Applicant Settings',
                            'menu_student_option_values' => 'Student Option Values',
                            'menu_student_flags' => 'Student Flags',
                            'menu_communication_settings' => 'Communication Settings',
                            'menu_elearning_activity_setting' => 'E-Learning Activity Setting',
                            'menu_user_privilege' => 'User Privilege',
                            'menu_hr_settings' => 'HR Settings',
                            'menu_datafuture_settings' => 'Datafuture Settings',
                            'menu_internal_site_link' => 'Internal Site Link',
                            'menu_accounts_settings' => 'Accounts Settings',
                            'menu_file_manager_settings' => 'File Manager Settings',
                        ];
                    @endphp
                    @foreach($settingsChildren as $key => $label)
                        <div class="col-span-4">
                            <div class="form-check form-switch mb-4 mr-4">
                                <input {{ $on($key) }} {{ $off('menu_settings') }} id="{{ $key }}-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][{{ $key }}]">
                                <label class="form-check-label ml-4" for="{{ $key }}-{{ $d }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-span-12">
                        <div class="form-check form-switch">
                            <input {{ $on('menu_workplacement') }} {{ $off('menu_settings') }} id="menu-workplacement-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][menu_workplacement]">
                            <label class="form-check-label ml-4" for="menu-workplacement-{{ $d }}">Workplacement</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ $on('menu_workplacement_details') }} {{ $off('menu_workplacement') }} id="workplacement-details-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_workplacement_details]">
                                <label class="form-check-label ml-4" for="workplacement-details-{{ $d }}">Workplacement Details</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ $on('menu_workplacement_companies') }} {{ $off('menu_workplacement') }} id="workplacement-companies-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_workplacement_companies]">
                                <label class="form-check-label ml-4" for="workplacement-companies-{{ $d }}">Workplacement Companies / Supervisor</label>
                            </div>
                            <div class="form-check form-switch">
                                <input {{ $on('menu_workplacement_settings') }} {{ $off('menu_workplacement') }} id="workplacement-settings-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][menu_workplacement_settings]">
                                <label class="form-check-label ml-4" for="workplacement-settings-{{ $d }}">Workplacement Settings</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Dashboard Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_applicant') }} id="dashboard-applicant-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_applicant]">
                    <label class="form-check-label ml-4" for="dashboard-applicant-{{ $d }}">Applicant</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('dashboard_applicant_analysis') }} {{ $off('dashboard_applicant') }} id="applicant-analysis-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_applicant_analysis]">
                        <label class="form-check-label ml-4" for="applicant-analysis-{{ $d }}">Application Analysis</label>
                    </div>
                    <div class="form-check form-switch">
                        <input {{ $on('dashboard_applicant_rejected') }} {{ $off('dashboard_applicant') }} id="applicant-rejected-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_applicant_rejected]">
                        <label class="form-check-label ml-4" for="applicant-rejected-{{ $d }}">Reject / In Progress Application</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_live_student') }} id="dashboard-student-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_live_student]">
                    <label class="form-check-label ml-4" for="dashboard-student-{{ $d }}">Live Student</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_tutor') }} id="dashboard-tutor-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_tutor]">
                    <label class="form-check-label ml-4" for="dashboard-tutor-{{ $d }}">Tutor Dashboard</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_personal_tutor') }} id="dashboard-personal-tutor-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_personal_tutor]">
                    <label class="form-check-label ml-4" for="dashboard-personal-tutor-{{ $d }}">Personal Tutor Dashboard</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_interviews') }} id="dashboard-interviews-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_interviews]">
                    <label class="form-check-label ml-4" for="dashboard-interviews-{{ $d }}">Required Interviews</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_hr_portal') }} id="dashboard-hr-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_hr_portal]">
                    <label class="form-check-label ml-4" for="dashboard-hr-{{ $d }}">HR Portal</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_programme') }} id="dashboard-programme-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_programme]">
                    <label class="form-check-label ml-4" for="dashboard-programme-{{ $d }}">Programme Dashboard</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('dashboard_programme_reports') }} {{ $off('dashboard_programme') }} id="programme-reports-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_programme_reports]">
                        <label class="form-check-label ml-4" for="programme-reports-{{ $d }}">Reports</label>
                    </div>
                    <div class="form-check form-switch">
                        <input {{ $on('dashboard_programme_student_data') }} {{ $off('dashboard_programme') }} id="programme-student-data-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_programme_student_data]">
                        <label class="form-check-label ml-4" for="programme-student-data-{{ $d }}">Student Data Report Other Details Show</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_budget') }} id="dashboard-budget-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_budget]">
                    <label class="form-check-label ml-4" for="dashboard-budget-{{ $d }}">Budget Management</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('dashboard_budget_edit') }} {{ $off('dashboard_budget') }} id="budget-edit-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_budget_edit]">
                        <label class="form-check-label ml-4" for="budget-edit-{{ $d }}">Edit Budget</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('dashboard_budget_delete') }} {{ $off('dashboard_budget') }} id="budget-delete-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_budget_delete]">
                        <label class="form-check-label ml-4" for="budget-delete-{{ $d }}">Delete Settings</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input {{ $on('dashboard_budget_settings') }} {{ $off('dashboard_budget') }} id="budget-settings-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_budget_settings]">
                        <label class="form-check-label ml-4" for="budget-settings-{{ $d }}">Budget Settings</label>
                    </div>
                    <div class="form-check form-switch">
                        <input {{ $on('dashboard_budget_reports') }} {{ $off('dashboard_budget') }} id="budget-reports-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_budget_reports]">
                        <label class="form-check-label ml-4" for="budget-reports-{{ $d }}">Budget Reports</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_news') }} id="dashboard-news-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_news]">
                    <label class="form-check-label ml-4" for="dashboard-news-{{ $d }}">News &amp; Events</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_due_report') }} id="dashboard-due-report-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_due_report]">
                    <label class="form-check-label ml-4" for="dashboard-due-report-{{ $d }}">Student Due Report</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_file_manager') }} id="dashboard-file-manager-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_file_manager]">
                    <label class="form-check-label ml-4" for="dashboard-file-manager-{{ $d }}">File Manager</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_expired_docs') }} id="dashboard-expired-docs-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_expired_docs]">
                    <label class="form-check-label ml-4" for="dashboard-expired-docs-{{ $d }}">Expired Documents</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('dashboard_report_issue') }} id="dashboard-report-issue-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_report_issue]">
                    <label class="form-check-label ml-4" for="dashboard-report-issue-{{ $d }}">Report Issue</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch">
                        <input {{ $on('dashboard_show_all_issue') }} {{ $off('dashboard_report_issue') }} id="dashboard-show-all-issue-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][dashboard_show_all_issue]">
                        <label class="form-check-label ml-4" for="dashboard-show-all-issue-{{ $d }}">Show All Issue</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Staff Profile Privilege -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Staff Profile Privilege</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-center">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('staff_profile_staff_group') }} id="staff-group-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][staff_profile_staff_group]">
                    <label class="form-check-label ml-4" for="staff-group-{{ $d }}">Staff Group</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HR Portal Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">HR Portal Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-center">
            @php
                $hrItems = [
                    'hr_portal_add_attendance' => 'Add Attendance',
                    'hr_portal_delete_attendance' => 'Delete Attendance',
                    'hr_portal_privilege_menu' => 'Privilege Menu',
                    'hr_portal_edit_email' => 'Edit User Email',
                    'hr_portal_login_as' => 'Login As User',
                ];
            @endphp
            @foreach($hrItems as $key => $label)
                <div class="col-span-12 sm:col-span-3">
                    <div class="form-check form-switch">
                        <input {{ $on($key) }} id="{{ $key }}-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][{{ $key }}]">
                        <label class="form-check-label ml-4" for="{{ $key }}-{{ $d }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Applicant Portal Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Applicant Portal Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-center">
            @php
                $applicantItems = [
                    'applicant_portal_login_as' => 'Login as Applicant',
                    'applicant_portal_create' => 'Create Applicant Account',
                    'applicant_portal_e_signature' => 'E-Signature Request',
                ];
            @endphp
            @foreach($applicantItems as $key => $label)
                <div class="col-span-12 sm:col-span-3">
                    <div class="form-check form-switch">
                        <input {{ $on($key) }} id="{{ $key }}-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][{{ $key }}]">
                        <label class="form-check-label ml-4" for="{{ $key }}-{{ $d }}">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Live Student Portal Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Live Student Portal Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('live_student_change_status') }} id="student-change-status-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][live_student_change_status]">
                    <label class="form-check-label ml-4" for="student-change-status-{{ $d }}">Change Status</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('live_student_login_as') }} id="student-login-as-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][live_student_login_as]">
                    <label class="form-check-label ml-4" for="student-login-as-{{ $d }}">Login as Student</label>
                </div>
            </div>

            @php
                // Label-only rows render as group headings; the JS and the print
                // report both key off a .form-check with no checkbox in it.
                $liveStudentGroups = [
                    'Results' => [
                        'live_student_results_view' => 'View',
                        'live_student_results_add' => 'Add',
                        'live_student_results_edit' => 'Edit',
                        'live_student_results_delete' => 'Delete',
                    ],
                    'Attendance' => [
                        'live_student_attendance_view' => 'View',
                        'live_student_attendance_add' => 'Add',
                        'live_student_attendance_edit' => 'Edit',
                        'live_student_attendance_delete' => 'Delete',
                    ],
                    'Accounts' => [
                        'live_student_accounts_view' => 'View',
                        'live_student_accounts_add' => 'Add',
                        'live_student_accounts_edit' => 'Edit',
                        'live_student_accounts_delete' => 'Delete',
                    ],
                    'SLC History' => [
                        'live_student_slc_view' => 'View',
                        'live_student_slc_add' => 'Add',
                        'live_student_slc_edit' => 'Edit',
                        'live_student_slc_delete' => 'Delete',
                    ],
                    'Other Course Relation' => [
                        'live_student_course_relation_view' => 'View',
                    ],
                    'Performance' => [
                        'live_student_performance_view' => 'View',
                    ],
                    'Print Application Form' => [
                        'live_student_print_app_view' => 'View',
                    ],
                    'Student Archives' => [
                        'live_student_archives_view' => 'View',
                    ],
                    'Workplacement' => [
                        'live_student_workplacement_add' => 'Add',
                        'live_student_workplacement_edit' => 'Edit',
                        'live_student_workplacement_delete' => 'Delete',
                    ],
                    'Visit' => [
                        'live_student_visit_view' => 'View',
                        'live_student_visit_add' => 'Add',
                        'live_student_visit_edit' => 'Edit',
                        'live_student_visit_delete' => 'Delete',
                    ],
                    'Documents' => [
                        'live_student_docs_view' => 'View',
                        'live_student_docs_add' => 'Add',
                        'live_student_docs_delete' => 'Delete',
                    ],
                    'Student Other Personal Info' => [
                        'live_student_other_personal_view' => 'View',
                        'live_student_other_personal_edit' => 'Edit',
                    ],
                    'Residency Status and Criminal Convictions' => [
                        'live_student_residency_view' => 'View',
                        'live_student_residency_edit' => 'Edit',
                    ],
                    'Datafuture' => [
                        'live_student_datafuture_view' => 'View',
                        'live_student_datafuture_edit' => 'Add/Edit/Delete',
                    ],
                    'Student Login Logs' => [
                        'live_student_logs_view' => 'View',
                    ],
                ];
            @endphp

            @foreach($liveStudentGroups as $groupLabel => $groupItems)
                <div class="col-span-12 sm:col-span-3">
                    <div class="form-check form-switch">
                        <label class="form-check-label ml-4">{{ $groupLabel }}</label>
                    </div>
                    <div class="childrenPermissionWrap pt-4 pl-12">
                        @foreach($groupItems as $key => $label)
                            <div class="form-check form-switch mb-4">
                                <input {{ $on($key) }} id="{{ $key }}-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][{{ $key }}]">
                                <label class="form-check-label ml-4" for="{{ $key }}-{{ $d }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="col-span-12 sm:col-span-6">
                <div class="form-check form-switch">
                    <label class="form-check-label ml-4">Communications</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    @php
                        $commsItems = [
                            'live_student_comms_view' => 'View Communication',
                            'live_student_comms_send_letter' => 'Send Letter',
                            'live_student_comms_delete_letter' => 'Delete Letter',
                            'live_student_comms_send_email' => 'Send Email',
                            'live_student_comms_delete_email' => 'Delete Email',
                            'live_student_comms_send_sms' => 'Send SMS',
                            'live_student_comms_delete_sms' => 'Delete SMS',
                        ];
                    @endphp
                    @foreach($commsItems as $key => $label)
                        <div class="form-check form-switch mb-4">
                            <input {{ $on($key) }} id="{{ $key }}-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][{{ $key }}]">
                            <label class="form-check-label ml-4" for="{{ $key }}-{{ $d }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Module Content Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Module Content Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('module_content_participants') }} id="module-participants-{{ $d }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permissions[{{ $d }}][module_content_participants]">
                    <label class="form-check-label ml-4" for="module-participants-{{ $d }}">Participants</label>
                </div>
                <div class="childrenPermissionWrap pt-4 pl-12">
                    <div class="form-check form-switch">
                        <input {{ $on('participants_export') }} {{ $off('module_content_participants') }} id="participants-export-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][participants_export]">
                        <label class="form-check-label ml-4" for="participants-export-{{ $d }}">Export</label>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('module_content_assessment') }} id="module-assessment-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][module_content_assessment]">
                    <label class="form-check-label ml-4" for="module-assessment-{{ $d }}">Assessment</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('module_content_analytics') }} id="module-analytics-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][module_content_analytics]">
                    <label class="form-check-label ml-4" for="module-analytics-{{ $d }}">Analytics</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('module_content_edit_attendance') }} id="module-edit-attendance-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][module_content_edit_attendance]">
                    <label class="form-check-label ml-4" for="module-edit-attendance-{{ $d }}">Edit Attendance</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Library Management Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Library Management Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('library_management') }} id="library-management-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][library_management]">
                    <label class="form-check-label ml-4" for="library-management-{{ $d }}">Library Management</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Result Management Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Result Management Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-start">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch mb-3">
                    <input {{ $on('result_staff_upload') }} id="result-staff-upload-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][result_staff_upload]">
                    <label class="form-check-label ml-4" for="result-staff-upload-{{ $d }}">Staff Upload Permission</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input {{ $on('result_staff_delete') }} id="result-staff-delete-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][result_staff_delete]">
                    <label class="form-check-label ml-4" for="result-staff-delete-{{ $d }}">Staff Delete Permission</label>
                </div>
                <div class="form-check form-switch">
                    <input {{ $on('result_pt_upload') }} id="result-pt-upload-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][result_pt_upload]">
                    <label class="form-check-label ml-4" for="result-pt-upload-{{ $d }}">PT Upload Permission</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Internal Links Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Internal Links Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4">
            @foreach($internalLinks as $link)
                @php $hasChildren = $link->children->count() > 0; @endphp
                <div class="col-span-12 {{ $hasChildren ? '' : 'sm:col-span-3' }} mb-2">
                    <div class="form-check form-switch">
                        <input {{ $on('internal_link_'.$link->id) }} id="internal-link-{{ $d }}-{{ $link->id }}" class="form-check-input {{ $hasChildren ? 'parentPermissionItem' : '' }}" type="checkbox" value="1" name="permissions[{{ $d }}][internal_link_{{ $link->id }}]">
                        <label class="form-check-label ml-4" for="internal-link-{{ $d }}-{{ $link->id }}">{{ $link->name }}</label>
                    </div>
                    @if($hasChildren)
                        <div class="grid grid-cols-12 gap-4 pl-12 pt-3 childrenPermissionWrap">
                            @foreach($link->children as $child)
                                <div class="col-span-12 sm:col-span-3">
                                    <div class="form-check form-switch">
                                        <input {{ $on('internal_link_'.$child->id) }} {{ $off('internal_link_'.$link->id) }} id="internal-link-{{ $d }}-{{ $child->id }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][internal_link_{{ $child->id }}]">
                                        <label class="form-check-label ml-4" for="internal-link-{{ $d }}-{{ $child->id }}">{{ $child->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Not rows in internal_links, so they stay as fixed keys (as in the legacy form). --}}
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('internal_link_group_email') }} id="internal-link-group-email-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][internal_link_group_email]">
                    <label class="form-check-label ml-4" for="internal-link-group-email-{{ $d }}">Group Email</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('internal_link_staff_upload_permission') }} id="internal-link-staff-upload-{{ $d }}" class="form-check-input" type="checkbox" value="1" name="permissions[{{ $d }}][internal_link_staff_upload_permission]">
                    <label class="form-check-label ml-4" for="internal-link-staff-upload-{{ $d }}">Staff Upload Permission</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accounts Privileges -->
<div class="intro-y box p-5 mt-5">
    <div class="grid grid-cols-12 gap-0 items-center">
        <div class="col-span-6">
            <div class="font-medium text-base">Accounts Privileges</div>
        </div>
        <div class="col-span-6 text-right relative">
            <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
        </div>
    </div>
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4 items-center">
            <div class="col-span-12 sm:col-span-3">
                <div class="form-check form-switch">
                    <input {{ $on('accounts_privilege') }} id="accounts-privilege-{{ $d }}" class="form-check-input accountsPrivilegeToggle" type="checkbox" value="1" name="permissions[{{ $d }}][accounts_privilege]">
                    <label class="form-check-label ml-4" for="accounts-privilege-{{ $d }}">Account's Privilege</label>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-3 accountsUserTypeWrap {{ empty($p['accounts_privilege']) ? 'hidden' : '' }}">
                <select id="accounts-privilege-type-{{ $d }}" name="permissions[{{ $d }}][accounts_privilege_type]" class="form-control w-auto">
                    <option value="">Please Select</option>
                    <option value="1" {{ $val('accounts_privilege_type') == 1 ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ $val('accounts_privilege_type') == 2 ? 'selected' : '' }}>User</option>
                    <option value="3" {{ $val('accounts_privilege_type') == 3 ? 'selected' : '' }}>Audit</option>
                </select>
            </div>
        </div>
    </div>
</div>
