@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    
    @include('pages.employee.profile.title-info')


    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->
    
    <form method="post" action="#" id="employeePrivilegeForm">
        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
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
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'checked' : '') }} id="permission_remote_access_1" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][ra_status]">
                            <label class="form-check-label ml-4 ra_status_label" for="permission_remote_access_1">
                                {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'Allowed' : 'Not Allowed') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3" id="inRangeSwitch" style="display: {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'block' : 'none') }};">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['in_range']) && $priv['remote_access']['in_range'] == 1 ? 'checked' : '') }} id="permission_remote_access_2" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][in_range]">
                            <label class="form-check-label ml-4" for="permission_remote_access_2">Allowed Termporary</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3" id="dateRangeWrap" style="display: {{ (isset($priv['remote_access']['in_range']) && $priv['remote_access']['in_range'] == 1 ? 'block' : 'none') }};">
                        <div class="flex justify-between items-center">
                            <input type="text" name="permission[remote_access][date_range]" value="{{ (isset($priv['remote_access']['date_range']) && !empty($priv['remote_access']['date_range']) ? $priv['remote_access']['date_range'] : '') }}" data-daterange="true" id="rangepicker" class="rangepicker form-control w-56 block mx-auto">
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['work_home']) && $priv['remote_access']['work_home'] == 1 ? 'checked' : '') }} id="permission_remote_access_4" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][work_home]">
                            <label class="form-check-label ml-4" for="permission_remote_access_4">Working From Home</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['desktop_login']) && $priv['remote_access']['desktop_login'] == 1 ? 'checked' : '') }} id="permission_remote_access_5" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][desktop_login]">
                            <label class="form-check-label ml-4" for="permission_remote_access_5">Desktop Clock In</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            <input {{ (isset($priv['top_menue']['course_manage']) && $priv['top_menue']['course_manage'] == 1 ? 'checked' : '') }} id="permission_menue_1" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[top_menue][course_manage]">
                            <label class="form-check-label ml-4" for="permission_menue_1">Course Management</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['course_managements']['course_and_semesters']) && $priv['course_managements']['course_and_semesters'] == 1 ? 'checked' : '') }} id="permission_course_management_1" class="form-check-input" type="checkbox" value="1" name="permission[course_managements][course_and_semesters]">
                                <label class="form-check-label ml-4" for="permission_course_management_1">Course & Semesters</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['course_managements']['terms_and_modules']) && $priv['course_managements']['terms_and_modules'] == 1 ? 'checked' : '') }} id="permission_course_management_2" class="form-check-input" type="checkbox" value="1" name="permission[course_managements][terms_and_modules]">
                                <label class="form-check-label ml-4" for="permission_course_management_2">Terms & Modules</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['course_managements']['plans']) && $priv['course_managements']['plans'] == 1 ? 'checked' : '') }} id="permission_course_management_3" class="form-check-input" type="checkbox" value="1" name="permission[course_managements][plans]">
                                <label class="form-check-label ml-4" for="permission_course_management_3">Plans</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['course_managements']['plans_list']) && $priv['course_managements']['plans_list'] == 1 ? 'checked' : '') }} id="permission_course_management_4" class="form-check-input" type="checkbox" value="1" name="permission[course_managements][plans_list]">
                                <label class="form-check-label ml-4" for="permission_course_management_4">Plan List</label>
                            </div>
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['course_managements']['plans_tree']) && $priv['course_managements']['plans_tree'] == 1 ? 'checked' : '') }} id="permission_course_management_5" class="form-check-input" type="checkbox" value="1" name="permission[course_managements][plans_tree]">
                                <label class="form-check-label ml-4" for="permission_course_management_5">Plan Tree</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['top_menue']['student_manage']) && $priv['top_menue']['student_manage'] == 1 ? 'checked' : '') }} id="permission_menue_2" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[top_menue][student_manage]">
                            <label class="form-check-label ml-4" for="permission_menue_2">Student Management</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['live_students']['generage_latter']) && $priv['live_students']['generage_latter'] == 1 ? 'checked' : '') }} id="permission_live_student_com_1" class="form-check-input" type="checkbox" value="1" name="permission[live_students][generage_latter]">
                                <label class="form-check-label ml-4" for="permission_live_student_com_1">Generate Latter</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['live_students']['send_email']) && $priv['live_students']['send_email'] == 1 ? 'checked' : '') }} id="permission_live_student_com_2" class="form-check-input" type="checkbox" value="1" name="permission[live_students][send_email]">
                                <label class="form-check-label ml-4" for="permission_live_student_com_2">Send Email</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['live_students']['send_sms']) && $priv['live_students']['send_sms'] == 1 ? 'checked' : '') }} id="permission_live_student_com_3" class="form-check-input" type="checkbox" value="1" name="permission[live_students][send_sms]">
                                <label class="form-check-label ml-4" for="permission_live_student_com_3">Send SMS</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['top_menue']['settings']) && $priv['top_menue']['settings'] == 1 ? 'checked' : '') }} id="permission_menue_3" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[top_menue][settings]">
                            <label class="form-check-label ml-4" for="permission_menue_3">Settings</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 grid grid-cols-12">
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['site_settings']) && $priv['settings']['site_settings'] == 1 ? 'checked' : '') }} id="permission_settings_1" class="form-check-input" type="checkbox" value="1" name="permission[settings][site_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_1">Site Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['course_parameters']) && $priv['settings']['course_parameters'] == 1 ? 'checked' : '') }} id="permission_settings_2" class="form-check-input" type="checkbox" value="1" name="permission[settings][course_parameters]">
                                    <label class="form-check-label ml-4" for="permission_settings_2">Course Parameters</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['campus_settings']) && $priv['settings']['campus_settings'] == 1 ? 'checked' : '') }} id="permission_settings_3" class="form-check-input" type="checkbox" value="1" name="permission[settings][campus_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_3">Campus Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['applicant_settings']) && $priv['settings']['applicant_settings'] == 1 ? 'checked' : '') }} id="permission_settings_4" class="form-check-input" type="checkbox" value="1" name="permission[settings][applicant_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_4">Applicant Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['student_option_values']) && $priv['settings']['student_option_values'] == 1 ? 'checked' : '') }} id="permission_settings_5" class="form-check-input" type="checkbox" value="1" name="permission[settings][student_option_values]">
                                    <label class="form-check-label ml-4" for="permission_settings_5">Student Option Values</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['student_flags']) && $priv['settings']['student_flags'] == 1 ? 'checked' : '') }} id="permission_settings_6" class="form-check-input" type="checkbox" value="1" name="permission[settings][student_flags]">
                                    <label class="form-check-label ml-4" for="permission_settings_6">Student Flags</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['communication_settings']) && $priv['settings']['communication_settings'] == 1 ? 'checked' : '') }} id="permission_settings_7" class="form-check-input" type="checkbox" value="1" name="permission[settings][communication_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_7">Communication Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['e_learning_activity_setting']) && $priv['settings']['e_learning_activity_setting'] == 1 ? 'checked' : '') }} id="permission_settings_9" class="form-check-input" type="checkbox" value="1" name="permission[settings][e_learning_activity_setting]">
                                    <label class="form-check-label ml-4" for="permission_settings_9">E-Learning Activity Setting</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['user_privilege']) && $priv['settings']['user_privilege'] == 1 ? 'checked' : '') }} id="permission_settings_10" class="form-check-input" type="checkbox" value="1" name="permission[settings][user_privilege]">
                                    <label class="form-check-label ml-4" for="permission_settings_10">User Privilege</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['hr_settings']) && $priv['settings']['hr_settings'] == 1 ? 'checked' : '') }} id="permission_settings_11" class="form-check-input" type="checkbox" value="1" name="permission[settings][hr_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_11">HR Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['datafuture_settings']) && $priv['settings']['datafuture_settings'] == 1 ? 'checked' : '') }} id="permission_settings_12" class="form-check-input" type="checkbox" value="1" name="permission[settings][datafuture_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_12">Datafuture Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['internal_site_link']) && $priv['settings']['internal_site_link'] == 1 ? 'checked' : '') }} id="permission_settings_13" class="form-check-input" type="checkbox" value="1" name="permission[settings][internal_site_link]">
                                    <label class="form-check-label ml-4" for="permission_settings_13">Internal Site Link</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['accounts_settings']) && $priv['settings']['accounts_settings'] == 1 ? 'checked' : '') }} id="permission_settings_14" class="form-check-input" type="checkbox" value="1" name="permission[settings][accounts_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_14">Accounts Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                <div class="form-check form-switch mb-4 mr-4">
                                    <input {{ (isset($priv['settings']['file_manager_settings']) && $priv['settings']['file_manager_settings'] == 1 ? 'checked' : '') }} id="permission_settings_15" class="form-check-input" type="checkbox" value="1" name="permission[settings][file_manager_settings]">
                                    <label class="form-check-label ml-4" for="permission_settings_15">File Manager Settings</label>
                                </div>
                            </div>
                            <div class="col-span-4">
                                
                            </div>
                            <div class="col-span-6">
                                <div class="form-check form-switch">
                                      <input {{ (isset($priv['settings']['workplacement']) && $priv['settings']['workplacement'] == 1 ? 'checked' : '') }} id="permission_settings_8" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[settings][workplacement]">
                                <label class="form-check-label ml-4" for="permission_settings_8">Workplacement</label>
                                </div>
                                <div class="childrenPermissionWrap pt-4 pl-12">
                                    <div class="form-check form-switch mb-4">
                                        <input {{ (isset($priv['settings_workplacement']['workplacement_details']) && $priv['settings_workplacement']['workplacement_details'] == 1 ? 'checked' : '') }} id="permission_settings_16" class="form-check-input" type="checkbox" value="1" name="permission[settings_workplacement][workplacement_details]">
                                        <label class="form-check-label ml-4" for="permission_settings_16">Workplacement Details</label>
                                    </div>
                                    <div class="form-check form-switch mb-4">
                                        <input {{ (isset($priv['settings_workplacement']['workplacement_companies']) && $priv['settings_workplacement']['workplacement_companies'] == 1 ? 'checked' : '') }} id="permission_settings_17" class="form-check-input" type="checkbox" value="1" name="permission[settings_workplacement][workplacement_companies]">
                                        <label class="form-check-label ml-4" for="permission_settings_17">Workplacement Companies / Supervisor</label>
                                    </div>
                                    <div class="form-check form-switch mb-4">
                                        <input {{ (isset($priv['settings_workplacement']['workplacement_settings']) && $priv['settings_workplacement']['workplacement_settings'] == 1 ? 'checked' : '') }} id="permission_settings_18" class="form-check-input" type="checkbox" value="1" name="permission[settings_workplacement][workplacement_settings]">
                                        <label class="form-check-label ml-4" for="permission_settings_18">Workplacement Settings</label>
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
                            <input {{ (isset($priv['dashboard']['applicant']) && $priv['dashboard']['applicant'] == 1 ? 'checked' : '') }} id="permission_dashboard_1" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[dashboard][applicant]">
                            <label class="form-check-label ml-4" for="permission_dashboard_1">Applicant</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['dashboard']['applicant_analysis']) && $priv['dashboard']['applicant_analysis'] == 1 ? 'checked' : '') }} id="permission_application_analysis_1" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][applicant_analysis]">
                                <label class="form-check-label ml-4" for="permission_application_analysis_1">Application Analysis</label>
                            </div>
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['dashboard']['applicant_rejected']) && $priv['dashboard']['applicant_rejected'] == 1 ? 'checked' : '') }} id="permission_applicant_rejected_1" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][applicant_rejected]">
                                <label class="form-check-label ml-4" for="permission_applicant_rejected_1">Reject / In Progress Application</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['live']) && $priv['dashboard']['live'] == 1 ? 'checked' : '') }} id="permission_dashboard_2" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][live]">
                            <label class="form-check-label ml-4" for="permission_dashboard_2">Live Student</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['tutor_2']) && $priv['dashboard']['tutor_2'] == 1 ? 'checked' : '') }} id="permission_dashboard_4" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][tutor_2]">
                            <label class="form-check-label ml-4" for="permission_dashboard_4">Tutor Dashboard</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['personal_tutor']) && $priv['dashboard']['personal_tutor'] == 1 ? 'checked' : '') }} id="permission_dashboard_5" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][personal_tutor]">
                            <label class="form-check-label ml-4" for="permission_dashboard_5">Personal Tutor Dashboard</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['req_interview']) && $priv['dashboard']['req_interview'] == 1 ? 'checked' : '') }} id="permission_dashboard_6" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][req_interview]">
                            <label class="form-check-label ml-4" for="permission_dashboard_6">Required Interviews</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['hr_porta']) && $priv['dashboard']['hr_porta'] == 1 ? 'checked' : '') }} id="permission_dashboard_7" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][hr_porta]">
                            <label class="form-check-label ml-4" for="permission_dashboard_7">HR Portal</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['programme_dashboard']) && $priv['dashboard']['programme_dashboard'] == 1 ? 'checked' : '') }} id="permission_dashboard_8" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[dashboard][programme_dashboard]">
                            <label class="form-check-label ml-4" for="permission_dashboard_8">Programme Dashboard</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['programme_dashboard']['reports']) && $priv['programme_dashboard']['reports'] == 1 ? 'checked' : '') }} id="permission_programme_dashboard_1" class="form-check-input" type="checkbox" value="1" name="permission[programme_dashboard][reports]">
                                <label class="form-check-label ml-4" for="permission_programme_dashboard_1">Reports</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['budget_manager']) && $priv['dashboard']['budget_manager'] == 1 ? 'checked' : '') }} id="permission_dashboard_9" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[dashboard][budget_manager]">
                            <label class="form-check-label ml-4" for="permission_dashboard_9">Budget Management</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['programme_dashboard']['budget_settings']) && $priv['programme_dashboard']['budget_settings'] == 1 ? 'checked' : '') }} id="permission_dashboard_10" class="form-check-input" type="checkbox" value="1" name="permission[programme_dashboard][budget_settings]">
                                <label class="form-check-label ml-4" for="permission_dashboard_10">Budget Settings</label>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input {{ (isset($priv['programme_dashboard']['budget_reports']) && $priv['programme_dashboard']['budget_reports'] == 1 ? 'checked' : '') }} id="permission_dashboard_11" class="form-check-input" type="checkbox" value="1" name="permission[programme_dashboard][budget_reports]">
                                <label class="form-check-label ml-4" for="permission_dashboard_11">Budget Reports</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Staff Privileges -->
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
                            <input {{ (isset($priv['sfaff_profile']['staff_groups']) && $priv['sfaff_profile']['staff_groups'] == 1 ? 'checked' : '') }} id="permission_sfaff_profile_1" class="form-check-input" type="checkbox" value="1" name="permission[sfaff_profile][staff_groups]">
                            <label class="form-check-label ml-4" for="permission_sfaff_profile_1">Staff Group</label>
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
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['add_attendance']) && $priv['hr_portal']['add_attendance'] == 1 ? 'checked' : '') }} id="permission_hr_portal_1" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][add_attendance]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_1">Add Attendance</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['del_attendance']) && $priv['hr_portal']['del_attendance'] == 1 ? 'checked' : '') }} id="permission_hr_portal_2" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][del_attendance]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_2">Delete Attendance</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['privilege_menu']) && $priv['hr_portal']['privilege_menu'] == 1 ? 'checked' : '') }} id="permission_hr_portal_3" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][privilege_menu]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_3">Privilege Menu</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['edit_user_email']) && $priv['hr_portal']['edit_user_email'] == 1 ? 'checked' : '') }} id="permission_hr_portal_4" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][edit_user_email]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_4">Edit User Email</label>
                        </div>
                    </div>
                    
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['login_as_user']) && $priv['hr_portal']['login_as_user'] == 1 ? 'checked' : '') }} id="permission_hr_portal_5" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][login_as_user]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_5">Login As User</label>
                        </div>
                    </div>
                    {{-- <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['login_as_student']) && $priv['hr_portal']['login_as_student'] == 1 ? 'checked' : '') }} id="permission_hr_portal_6" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][login_as_student]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_6">Login as Student</label>
                        </div>
                    </div> --}}
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
                <div class="grid grid-cols-12 gap-4 items-center">
                    
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['student_live_portal']['edit_student_status']) && $priv['student_live_portal']['edit_student_status'] == 1 ? 'checked' : '') }} id="permission_student_portal_1" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][edit_student_status]">
                            <label class="form-check-label ml-4" for="permission_student_portal_1">Change Status </label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['student_live_portal']['login_as_student']) && $priv['student_live_portal']['login_as_student'] == 1 ? 'checked' : '') }} id="permission_student_portal_2" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][login_as_student]">
                            <label class="form-check-label ml-4" for="permission_student_portal_2">Login as Student</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Results</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['result_view']) && $priv['student_live_portal']['result_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_3" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][result_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_3">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['result_add']) && $priv['student_live_portal']['result_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_4" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][result_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_4">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['result_edit']) && $priv['student_live_portal']['result_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_5" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][result_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_5">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['result_delete']) && $priv['student_live_portal']['result_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_6" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][result_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_6">Delete</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Attendance</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['attendance_view']) && $priv['student_live_portal']['attendance_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_7" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][attendance_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_7">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['attendance_add']) && $priv['student_live_portal']['attendance_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_8" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][attendance_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_8">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['attendance_edit']) && $priv['student_live_portal']['attendance_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_9" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][attendance_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_9">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['attendance_delete']) && $priv['student_live_portal']['attendance_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_10" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][attendance_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_10">Delete</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Accounts</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_account_view']) && $priv['student_live_portal']['student_account_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_11" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_account_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_11">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_account_add']) && $priv['student_live_portal']['student_account_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_12" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_account_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_12">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_account_edit']) && $priv['student_live_portal']['student_account_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_13" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_account_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_13">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_account_delete']) && $priv['student_live_portal']['student_account_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_14" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_account_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_14">Delete</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">SLC History</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['slc_history_view']) && $priv['student_live_portal']['slc_history_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_15" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][slc_history_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_15">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['slc_history_add']) && $priv['student_live_portal']['slc_history_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_16" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][slc_history_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_16">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['slc_history_edit']) && $priv['student_live_portal']['slc_history_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_17" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][slc_history_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_17">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['slc_history_delete']) && $priv['student_live_portal']['slc_history_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_18" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][slc_history_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_18">Delete</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Other Course Relation</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_course_change_view']) && $priv['student_live_portal']['student_course_change_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_19" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_course_change_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_19">View</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Performance</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['student_performance_view']) && $priv['student_live_portal']['student_performance_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_24" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][student_performance_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_24">View</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Print Application Form</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['edit_student_print']) && $priv['student_live_portal']['edit_student_print'] == 1 ? 'checked' : '') }} id="pdfprint_student_portal_24" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][edit_student_print]">
                                <label class="form-check-label ml-4" for="pdfprint_student_portal_24">View</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Student Archives</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['view_student_archives']) && $priv['student_live_portal']['view_student_archives'] == 1 ? 'checked' : '') }} id="student_portal_archives" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][view_student_archives]">
                                <label class="form-check-label ml-4" for="student_portal_archives">View</label>
                            </div>  
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Workplacement</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_placement']['placement_add']) && $priv['student_live_placement']['placement_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_placement_1" class="form-check-input" type="checkbox" value="1" name="permission[student_live_placement][placement_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_placement_1">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_placement']['placement_edit']) && $priv['student_live_placement']['placement_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_placement_2" class="form-check-input" type="checkbox" value="1" name="permission[student_live_placement][placement_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_placement_2">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_placement']['placement_delete']) && $priv['student_live_placement']['placement_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_placement_3" class="form-check-input" type="checkbox" value="1" name="permission[student_live_placement][placement_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_placement_3">Delete</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Visit</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['visit_view']) && $priv['student_live_portal']['visit_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_33" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][visit_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_33">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['visit_add']) && $priv['student_live_portal']['visit_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_34" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][visit_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_34">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['visit_edit']) && $priv['student_live_portal']['visit_edit'] == 1 ? 'checked' : '') }} id="permission_student_portal_35" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][visit_edit]">
                                <label class="form-check-label ml-4" for="permission_student_portal_35">Edit</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_portal']['visit_delete']) && $priv['student_live_portal']['visit_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_36" class="form-check-input" type="checkbox" value="1" name="permission[student_live_portal][visit_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_36">Delete</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-6">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Communications</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_view']) && $priv['student_live_communication']['communication_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_1" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_1">View Communication</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_send_letter']) && $priv['student_live_communication']['communication_send_letter'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_2" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_send_letter]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_2">Send Letter</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_delete_letter']) && $priv['student_live_communication']['communication_delete_letter'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_3" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_delete_letter]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_3">Delete Letter</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_send_email']) && $priv['student_live_communication']['communication_send_email'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_4" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_send_email]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_4">Send Email</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_delete_email']) && $priv['student_live_communication']['communication_delete_email'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_5" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_delete_email]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_5">Delete Email</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_send_sms']) && $priv['student_live_communication']['communication_send_sms'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_6" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_send_sms]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_6">Send SMS</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_communication']['communication_delete_sms']) && $priv['student_live_communication']['communication_delete_sms'] == 1 ? 'checked' : '') }} id="permission_student_portal_communication_7" class="form-check-input" type="checkbox" value="1" name="permission[student_live_communication][communication_delete_sms]">
                                <label class="form-check-label ml-4" for="permission_student_portal_communication_7">Delete SMS</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label ml-4" for="">Documents</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_document']['document_view']) && $priv['student_live_document']['document_view'] == 1 ? 'checked' : '') }} id="permission_student_portal_documents_1" class="form-check-input" type="checkbox" value="1" name="permission[student_live_document][document_view]">
                                <label class="form-check-label ml-4" for="permission_student_portal_documents_1">View</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_document']['document_add']) && $priv['student_live_document']['document_add'] == 1 ? 'checked' : '') }} id="permission_student_portal_documents_2" class="form-check-input" type="checkbox" value="1" name="permission[student_live_document][document_add]">
                                <label class="form-check-label ml-4" for="permission_student_portal_documents_2">Add</label>
                            </div>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12 inline-flex">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['student_live_document']['document_delete']) && $priv['student_live_document']['document_delete'] == 1 ? 'checked' : '') }} id="permission_student_portal_documents_3" class="form-check-input" type="checkbox" value="1" name="permission[student_live_document][document_delete]">
                                <label class="form-check-label ml-4" for="permission_student_portal_documents_3">Delete</label>
                            </div>
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
                            <input {{ (!isset($priv['module_contents']['participants']) || (isset($priv['module_contents']['participants']) && $priv['module_contents']['participants'] == 1) ? 'checked' : '') }} id="permission_module_contents_1" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[module_contents][participants]">
                            <label class="form-check-label ml-4" for="permission_module_contents_1">Participants</label>
                        </div>
                        <div class="childrenPermissionWrap pt-4 pl-12">
                            <div class="form-check form-switch">
                                <input {{ (isset($priv['module_contents']['participant_export']) && $priv['module_contents']['participant_export'] == 1 ? 'checked' : '') }} id="permission_participant_export_1" class="form-check-input" type="checkbox" value="1" name="permission[module_contents][participant_export]">
                                <label class="form-check-label ml-4" for="permission_participant_export_1">Export</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['module_contents']['assessment']) && $priv['module_contents']['assessment'] == 1 ? 'checked' : '') }} id="permission_module_contents_2" class="form-check-input" type="checkbox" value="1" name="permission[module_contents][assessment]">
                            <label class="form-check-label ml-4" for="permission_module_contents_2">Assessment</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['module_contents']['analytics']) && $priv['module_contents']['analytics'] == 1 ? 'checked' : '') }} id="permission_module_contents_3" class="form-check-input" type="checkbox" value="1" name="permission[module_contents][analytics]">
                            <label class="form-check-label ml-4" for="permission_module_contents_3">Analytics</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['module_contents']['edit_attendance']) && $priv['module_contents']['edit_attendance'] == 1 ? 'checked' : '') }} id="permission_module_contents_4" class="form-check-input" type="checkbox" value="1" name="permission[module_contents][edit_attendance]">
                            <label class="form-check-label ml-4" for="permission_module_contents_4">Edit Attendance</label>
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
                            <input {{ (isset($priv['library_management']['library_management']) && $priv['library_management']['library_management'] == 1 ? 'checked' : '') }} id="permission_library_management_1" class="form-check-input" type="checkbox" value="1" name="permission[library_management][library_management]">
                            <label class="form-check-label ml-4" for="permission_library_management_1">Library Management</label>
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
                            <input {{ (isset($priv['result_management']['result_management_staff']) && $priv['result_management']['result_management_staff'] == 1 ? 'checked' : '') }} id="permission_result_management_1" class="form-check-input" type="checkbox" value="1" name="permission[result_management][result_management_staff]">
                            <label class="form-check-label ml-4" for="permission_result_management_1">Staff Upload Permission</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input {{ (isset($priv['result_management']['result_management_staff_delete']) && $priv['result_management']['result_management_staff_delete'] == 1 ? 'checked' : '') }} id="permission_result_management_2" class="form-check-input" type="checkbox" value="1" name="permission[result_management][result_management_staff_delete]">
                            <label class="form-check-label ml-4" for="permission_result_management_2">Staff Delete Permission</label>
                        </div>
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['result_management']['result_management_pt']) && $priv['result_management']['result_management_pt'] == 1 ? 'checked' : '') }} id="permission_result_management_3" class="form-check-input" type="checkbox" value="1" name="permission[result_management][result_management_pt]">
                            <label class="form-check-label ml-4" for="permission_library_management_3">PT Upload Permission</label>
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
                    @if($links->count() > 0)
                        @foreach($links as $lnk)
                            <div class="col-span-12 mb-2">
                                <div class="form-check form-switch">
                                    <input {{ (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1 ? 'checked' : '') }} id="permission_parent_internal_links_{{ $lnk->id }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[parent_internal_links][{{ $lnk->id }}]">
                                    <label class="form-check-label ml-4" for="permission_parent_internal_links_{{ $lnk->id }}">{{ $lnk->name }}</label>
                                </div>
                                @if(isset($lnk->children) && $lnk->children->count() > 0)
                                <div class="grid grid-cols-12 gap-4 pl-12 pt-3 childrenPermissionWrap">
                                    @foreach($lnk->children as $clnk)
                                        @php 
                                            $childAttr = (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1 ? '' : ' disabled ');
                                            $childAttr .= (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1) && (isset($priv['parent_child_'.$lnk->id.'_links'][$clnk->id]) && $priv['parent_child_'.$lnk->id.'_links'][$clnk->id] == 1) ? ' checked ' : '';
                                        @endphp
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="form-check form-switch">
                                                <input {{ $childAttr }} id="permission_child_internal_links_{{ $clnk->id }}" class="form-check-input" type="checkbox" value="1" name="permission[parent_child_{{$lnk->id}}_links][{{ $clnk->id }}]">
                                                <label class="form-check-label ml-4" for="permission_child_internal_links_{{ $clnk->id }}">{{ $clnk->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['internal_links']['group_email']) && $priv['internal_links']['group_email'] == 1 ? 'checked' : '') }} id="permission_internal_links_1" class="form-check-input" type="checkbox" value="1" name="permission[internal_links][group_email]">
                            <label class="form-check-label ml-4" for="permission_internal_links_1">Group Email</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="intro-y box p-5 mt-5 {{ (in_array(auth()->user()->id, [1, 7, 8]) ? '' : 'magicBox') }}">
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
                            <input {{ (isset($priv['acc_privilege']['access_account']) && $priv['acc_privilege']['access_account'] == 1 ? 'checked' : '') }} id="permission_acc_privilege_1" class="form-check-input" type="checkbox" value="1" name="permission[acc_privilege][access_account]">
                            <label class="form-check-label ml-4" for="permission_acc_privilege_1">Account's Privilege</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3 accountsUserTypeWrap" style="display: {{ (isset($priv['acc_privilege']['access_account']) && $priv['acc_privilege']['access_account'] == 1 ? 'block' : 'none') }};">
                        <select id="permission_acc_privilege_2" name="permission[acc_privilege][access_account_type]" class="form-control w-auto">
                            <option value="">Please Select</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 1 ? 'selected' : '') }} value="1">Admin</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 2 ? 'selected' : '') }} value="2">User</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 3 ? 'selected' : '') }} value="3">Audit</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>


    </form>

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

    <!-- BEGIN: Warning Modal Content -->
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
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/employee-privilege.js')
@endsection