<?php

namespace App\Support;

/**
 * Translates a legacy user_privileges row (category + name) into the flat key
 * used by employee_permissions.
 *
 * The legacy form nests keys under a category; the new one uses a single flat
 * key per permission. Internal links are not listed here because they are keyed
 * by internal_links.id and so are resolved dynamically.
 */
class LegacyPrivilegeMap
{
    /**
     * "category.name" => new flat key.
     */
    public const MAP = [
        // Remote access
        'remote_access.ra_status' => 'remote_access_allowed',
        'remote_access.in_range' => 'remote_access_temp',
        'remote_access.date_range' => 'remote_access_date_range',
        'remote_access.work_home' => 'remote_access_home',
        'remote_access.desktop_login' => 'remote_access_desktop',
        'remote_access.all_services' => 'remote_access_all_services',

        // Top menu + its children
        'top_menue.course_manage' => 'menu_course_management',
        'top_menue.student_manage' => 'menu_student_management',
        'top_menue.settings' => 'menu_settings',
        'course_managements.course_and_semesters' => 'menu_course_semesters',
        'course_managements.terms_and_modules' => 'menu_terms_modules',
        'course_managements.plans' => 'menu_plans',
        'course_managements.plans_list' => 'menu_plan_list',
        'course_managements.plans_tree' => 'menu_plan_tree',
        'live_students.generage_latter' => 'menu_generate_letter',
        'live_students.send_email' => 'menu_send_email',
        'live_students.send_sms' => 'menu_send_sms',

        // Settings
        'settings.site_settings' => 'menu_site_settings',
        'settings.course_parameters' => 'menu_course_parameters',
        'settings.campus_settings' => 'menu_campus_settings',
        'settings.applicant_settings' => 'menu_applicant_settings',
        'settings.student_option_values' => 'menu_student_option_values',
        'settings.student_flags' => 'menu_student_flags',
        'settings.communication_settings' => 'menu_communication_settings',
        'settings.e_learning_activity_setting' => 'menu_elearning_activity_setting',
        'settings.user_privilege' => 'menu_user_privilege',
        'settings.hr_settings' => 'menu_hr_settings',
        'settings.datafuture_settings' => 'menu_datafuture_settings',
        'settings.internal_site_link' => 'menu_internal_site_link',
        'settings.accounts_settings' => 'menu_accounts_settings',
        'settings.file_manager_settings' => 'menu_file_manager_settings',
        'settings.workplacement' => 'menu_workplacement',
        'settings_workplacement.workplacement_details' => 'menu_workplacement_details',
        'settings_workplacement.workplacement_companies' => 'menu_workplacement_companies',
        'settings_workplacement.workplacement_settings' => 'menu_workplacement_settings',

        // Dashboard
        'dashboard.applicant' => 'dashboard_applicant',
        'dashboard.applicant_analysis' => 'dashboard_applicant_analysis',
        'dashboard.applicant_rejected' => 'dashboard_applicant_rejected',
        'dashboard.live' => 'dashboard_live_student',
        'dashboard.tutor_2' => 'dashboard_tutor',
        'dashboard.personal_tutor' => 'dashboard_personal_tutor',
        'dashboard.req_interview' => 'dashboard_interviews',
        'dashboard.hr_porta' => 'dashboard_hr_portal',
        'dashboard.programme_dashboard' => 'dashboard_programme',
        'dashboard.budget_manager' => 'dashboard_budget',
        'dashboard.news_events' => 'dashboard_news',
        'dashboard.student_due_rep' => 'dashboard_due_report',
        'dashboard.file_manager' => 'dashboard_file_manager',
        'dashboard.expired_docs' => 'dashboard_expired_docs',
        'dashboard.report_it_all' => 'dashboard_report_issue',
        'dashboard.show_all_issue' => 'dashboard_show_all_issue',
        'programme_dashboard.reports' => 'dashboard_programme_reports',
        'programme_dashboard.student_other_details_report_show' => 'dashboard_programme_student_data',
        'programme_dashboard.budget_edit' => 'dashboard_budget_edit',
        'programme_dashboard.budget_delete' => 'dashboard_budget_delete',
        'programme_dashboard.budget_settings' => 'dashboard_budget_settings',
        'programme_dashboard.budget_reports' => 'dashboard_budget_reports',

        // Staff profile
        'sfaff_profile.staff_groups' => 'staff_profile_staff_group',

        // HR portal
        'hr_portal.add_attendance' => 'hr_portal_add_attendance',
        'hr_portal.del_attendance' => 'hr_portal_delete_attendance',
        'hr_portal.privilege_menu' => 'hr_portal_privilege_menu',
        'hr_portal.edit_user_email' => 'hr_portal_edit_email',
        'hr_portal.login_as_user' => 'hr_portal_login_as',

        // Applicant portal
        'applicant_live_portal.login_as_applicant' => 'applicant_portal_login_as',
        'applicant_live_portal.create_an_applicant' => 'applicant_portal_create',
        'applicant_live_portal.e_signature_request' => 'applicant_portal_e_signature',

        // Live student portal
        'student_live_portal.edit_student_status' => 'live_student_change_status',
        'student_live_portal.login_as_student' => 'live_student_login_as',
        'student_live_portal.result_view' => 'live_student_results_view',
        'student_live_portal.result_add' => 'live_student_results_add',
        'student_live_portal.result_edit' => 'live_student_results_edit',
        'student_live_portal.result_delete' => 'live_student_results_delete',
        'student_live_portal.attendance_view' => 'live_student_attendance_view',
        'student_live_portal.attendance_add' => 'live_student_attendance_add',
        'student_live_portal.attendance_edit' => 'live_student_attendance_edit',
        'student_live_portal.attendance_delete' => 'live_student_attendance_delete',
        'student_live_portal.student_account_view' => 'live_student_accounts_view',
        'student_live_portal.student_account_add' => 'live_student_accounts_add',
        'student_live_portal.student_account_edit' => 'live_student_accounts_edit',
        'student_live_portal.student_account_delete' => 'live_student_accounts_delete',
        'student_live_portal.slc_history_view' => 'live_student_slc_view',
        'student_live_portal.slc_history_add' => 'live_student_slc_add',
        'student_live_portal.slc_history_edit' => 'live_student_slc_edit',
        'student_live_portal.slc_history_delete' => 'live_student_slc_delete',
        'student_live_portal.student_course_change_view' => 'live_student_course_relation_view',
        'student_live_portal.student_performance_view' => 'live_student_performance_view',
        'student_live_portal.edit_student_print' => 'live_student_print_app_view',
        'student_live_portal.view_student_archives' => 'live_student_archives_view',
        'student_live_portal.visit_view' => 'live_student_visit_view',
        'student_live_portal.visit_add' => 'live_student_visit_add',
        'student_live_portal.visit_edit' => 'live_student_visit_edit',
        'student_live_portal.visit_delete' => 'live_student_visit_delete',
        'student_live_portal.student_other_personal_view' => 'live_student_other_personal_view',
        'student_live_portal.student_other_personal_edit' => 'live_student_other_personal_edit',
        'student_live_portal.student_residency_status_view' => 'live_student_residency_view',
        'student_live_portal.student_residency_status_edit' => 'live_student_residency_edit',
        'student_live_portal.datafuture_view' => 'live_student_datafuture_view',
        'student_live_portal.datafuture_edit' => 'live_student_datafuture_edit',
        'student_live_portal.view_student_logs' => 'live_student_logs_view',

        // Live student - placement, communications, documents
        'student_live_placement.placement_add' => 'live_student_workplacement_add',
        'student_live_placement.placement_edit' => 'live_student_workplacement_edit',
        'student_live_placement.placement_delete' => 'live_student_workplacement_delete',
        'student_live_communication.communication_view' => 'live_student_comms_view',
        'student_live_communication.communication_send_letter' => 'live_student_comms_send_letter',
        'student_live_communication.communication_delete_letter' => 'live_student_comms_delete_letter',
        'student_live_communication.communication_send_email' => 'live_student_comms_send_email',
        'student_live_communication.communication_delete_email' => 'live_student_comms_delete_email',
        'student_live_communication.communication_send_sms' => 'live_student_comms_send_sms',
        'student_live_communication.communication_delete_sms' => 'live_student_comms_delete_sms',
        'student_live_document.document_view' => 'live_student_docs_view',
        'student_live_document.document_add' => 'live_student_docs_add',
        'student_live_document.document_delete' => 'live_student_docs_delete',

        // Module content
        'module_contents.participants' => 'module_content_participants',
        'module_contents.participant_export' => 'participants_export',
        'module_contents.assessment' => 'module_content_assessment',
        'module_contents.analytics' => 'module_content_analytics',
        'module_contents.edit_attendance' => 'module_content_edit_attendance',

        // Library + results
        'library_management.library_management' => 'library_management',
        'result_management.result_management_staff' => 'result_staff_upload',
        'result_management.result_management_staff_delete' => 'result_staff_delete',
        'result_management.result_management_pt' => 'result_pt_upload',

        // Internal links (the id-keyed ones are resolved in resolve())
        'internal_links.group_email' => 'internal_link_group_email',

        // Accounts
        'acc_privilege.access_account' => 'accounts_privilege',
        'acc_privilege.access_account_type' => 'accounts_privilege_type',
    ];

    /**
     * Keys that carry a real value rather than a 1/0 flag, so their value is
     * copied across as-is instead of being normalised to "1".
     */
    public const VALUED_KEYS = [
        'accounts_privilege_type',
        'remote_access_date_range',
    ];

    /**
     * new key => legacy name, built once.
     *
     * User::priv() plucks by `name` and discards the category, and every legacy
     * name is unique across categories (verified against live data), so this
     * reverse map is lossless. It is what lets employee_permissions back the
     * existing priv() checks without touching their 60+ call sites.
     */
    private static ?array $reverse = null;

    public static function toLegacyName(string $newKey): ?string
    {
        if (self::$reverse === null) {
            self::$reverse = [];

            foreach (self::MAP as $categoryAndName => $key) {
                self::$reverse[$key] = explode('.', $categoryAndName, 2)[1];
            }
        }

        // Internal links are stored under the link id on both sides: the legacy
        // row's `name` is the id itself, so priv() exposes it as a numeric key.
        if (preg_match('/^internal_link_(\d+)$/', $newKey, $m)) {
            return $m[1];
        }

        return self::$reverse[$newKey] ?? null;
    }

    /**
     * Returns the new flat key, or null when the legacy row has no counterpart.
     */
    public static function resolve(string $category, string $name): ?string
    {
        // Internal links are keyed by internal_links.id on both sides. The legacy
        // categories are parent_internal_links and parent_child_{parentId}_links,
        // and in both cases `name` is already the link's id.
        if ($category === 'parent_internal_links' || preg_match('/^parent_child_\d+_links$/', $category)) {
            return ctype_digit((string) $name) ? 'internal_link_'.$name : null;
        }

        return self::MAP[$category.'.'.$name] ?? null;
    }
}
