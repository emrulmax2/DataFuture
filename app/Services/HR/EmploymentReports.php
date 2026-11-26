<?php

namespace App\Services\HR;

use App\Models\EmploymentReport;
use Illuminate\Support\Facades\Route;

/**
 * Employment Reports index (user's employment-reports design, 26 Nov 2026): the rows of employment_reports,
 * each with the category, description, formats, Sensitive flag, icon and search words from the design.
 * Reports are matched by report_description, as the old list endpoint does.
 */
class EmploymentReports
{
    public const CATEGORIES = [
        'people' => 'People and contacts',
        'workforce' => 'Workforce',
        'time' => 'Time and leave',
        'compliance' => 'Right to work',
        'export' => 'Custom export',
        'other' => 'Other reports',
    ];

    /** report_description => [category, description, formats, sensitive, icon, search words, route name] */
    public const CATALOGUE = [
        'Birthday List' => ['people', 'Whose birthday is coming up, and a calendar of the year ahead.', ['PDF', 'XLSX'], false, 'cake', 'date of birth age anniversary', 'hr.portal.reports.birthdaylist'],
        'Diversity Information' => ['workforce', 'Staff by gender, ethnic group and nationality.', ['PDF', 'XLSX'], true, 'people', 'ethnicity equality disability', 'hr.portal.reports.diversityreport'],
        'Employee Contact Detail' => ['people', 'Address, phone, email and emergency contact for each employee.', ['PDF', 'XLSX'], true, 'card', 'address emergency next of kin', 'hr.portal.reports.contactdetail'],
        'Employee Length of Service' => ['workforce', 'How long each person has worked here, and work anniversaries.', ['PDF', 'XLSX'], false, 'clock', 'tenure years started service lengths', 'hr.portal.reports.lengthservice'],
        'Employee Record Card' => ['people', 'One page per employee with all their personal and job details.', ['PDF', 'XLSX'], true, 'file', 'profile personal ni number', 'hr.portal.reports.recordcard'],
        'Employee Starter' => ['workforce', 'Who started when, with the newest starters first.', ['PDF', 'XLSX'], false, 'flag', 'new joiners start date onboarding', 'hr.portal.reports.starterreport'],
        'Employee Telephone Directory' => ['people', 'An A to Z phone book of mobile numbers and emails.', ['PDF', 'XLSX'], false, 'phone', 'mobile numbers phone book email', 'hr.portal.reports.telephonedirectory'],
        'Employee Eligibility Entry' => ['compliance', 'Visa and work permit expiry dates, with what needs action first.', ['PDF', 'XLSX'], true, 'shield', 'visa permit passport expiry right to work', 'hr.portal.reports.eligibilityreport'],
        'Employee Data Report' => ['export', 'Build your own spreadsheet: pick the employees and the fields.', ['XLSX'], true, 'sheet', 'custom export fields columns payroll', 'hr.portal.reports.datareport'],
        'Attendance Report' => ['time', 'Clock-in and attendance for each employee.', [], false, 'hours', 'attendance clock in timesheet', 'hr.portal.reports.attendance'],
        'Employment Hour Report' => ['time', 'Worked and holiday hours for each employee in a date range.', ['XLSX'], false, 'hours', 'timesheet attendance worked hours', 'hr.portal.reports.holiday.hour'],
        'Outstanding Holiday Report' => ['time', 'Holiday allocated, taken and still owed, in hours and pounds.', ['XLSX'], false, 'sun', 'annual leave balance allocation liability', 'hr.portal.reports.outstanding.holiday'],
        'Sick Leave' => ['time', 'Who was off sick and on which days.', ['XLSX'], true, 'plus', 'absence illness sickness', 'hr.portal.reports.sick.leave'],
    ];

    /** The reports in SL No order, ready for the page. */
    public static function all(): array
    {
        return EmploymentReport::orderBy('id')->get()->map(function ($r) {
            $c = self::CATALOGUE[trim((string) $r->report_description)] ?? null;
            $route = $c[6] ?? null;

            return [
                'no' => (int) $r->id,
                'name' => (string) $r->report_description,
                'cat' => $c[0] ?? 'other',
                'desc' => $c[1] ?? (string) $r->file_name,
                'fmt' => $c[2] ?? [],
                'sens' => (bool) ($c[3] ?? false),
                'ic' => $c[4] ?? 'file',
                'words' => trim(($c[5] ?? '').' '.$r->file_name),
                'url' => $route && Route::has($route) ? route($route) : null,
                'last' => $r->last_run ? date('j M Y, H:i', strtotime($r->last_run)) : null,
            ];
        })->all();
    }
}
