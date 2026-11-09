<?php

namespace App\Http\Controllers\AttendanceIntegrity;

use App\Http\Controllers\Controller;
use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use App\Services\AttendanceIntegrity\Lookup;
use Illuminate\Http\Request;

/**
 * Shared shell for the Attendance Integrity screens (/attendance-integrity):
 * navigation (only the areas the user may open), the filter bar, page header.
 */
abstract class IntegrityBaseController extends Controller
{
    /** Sidebar: group => [[route, label, ability, lucide icon], ...] in the order of the specification. */
    public const NAV = [
        'Overview' => [
            ['attendance.integrity.dashboard', 'Dashboard', 'view_dashboard', 'layout-dashboard'],
        ],
        'Analysis' => [
            ['attendance.integrity.staff', 'Staff Analysis', 'view_staff', 'users'],
            ['attendance.integrity.students', 'Student Analysis', 'view_students', 'graduation-cap'],
        ],
        'Detection' => [
            ['attendance.integrity.retrospective', 'Retrospective Attendance', 'view_dashboard', 'alarm-clock'],
            ['attendance.integrity.amendments', 'Attendance Amendments', 'view_audit_logs', 'history'],
            ['attendance.integrity.bulk', 'Bulk Entry Detection', 'view_dashboard', 'layers'],
            ['attendance.integrity.patterns', 'Staff–Student Patterns', 'view_staff', 'network'],
            ['attendance.integrity.timetable', 'Timetable Conflicts', 'view_dashboard', 'calendar-x'],
        ],
        'Casework' => [
            ['attendance.integrity.cases', 'Investigations', 'view_investigations', 'folder-search'],
            ['attendance.integrity.reports', 'Reports', 'export', 'file-bar-chart'],
        ],
        'Help & admin' => [
            ['attendance.integrity.guide', 'Guide', null, 'book-open'],
            ['attendance.integrity.settings', 'Settings', 'manage_settings', 'sliders-horizontal'],
        ],
    ];

    protected function shell(Request $request, string $view, string $title, array $data = [], bool $filters = true)
    {
        $f = $data['filters'] ?? Filters::fromRequest($request);
        $query = $f->query();
        $navQuery = array_intersect_key($query, array_flip(['terms', 'academic_year', 'date_from', 'date_to', 'venue', 'course', 'group', 'module']));

        $nav = [];
        foreach (self::NAV as $group => $links) {
            foreach ($links as [$route, $label, $ability, $icon]) {
                if ($ability === null ? IntegrityAccess::any() : IntegrityAccess::can($ability)) {
                    $nav[$group][] = ['route' => $route, 'label' => $label, 'icon' => $icon, 'url' => route($route, $route === 'attendance.integrity.guide' ? [] : $navQuery)];
                }
            }
        }

        return view($view, array_merge([
            'layout' => 'attendance-integrity',
            'title' => $title.' - Attendance Integrity - London Churchill College',
            'pageTitle' => $title,
            'nav' => $nav,
            'showFilters' => $filters,
            'filters' => $f,
            'filterOptions' => $filters ? Lookup::filterOptions() : [],
            'termOptions' => $filters ? Lookup::termOptions($f->terms) : [],
            'filterText' => $f->describe(),
            'query' => $query,
            'can' => array_combine(array_keys(IntegrityAccess::KEYS), array_map(fn ($a) => IntegrityAccess::can($a), array_keys(IntegrityAccess::KEYS))),
        ], $data));
    }

    /** Guide: how the module works, in plain words. Anyone with access to any area. */
    public function guide(Request $request)
    {
        abort_unless(IntegrityAccess::any(), 403, 'You do not have access to Attendance Integrity.');
        IntegrityAccess::log('view', 'guide');

        return $this->shell($request, 'pages.attendance-integrity.guide', 'Guide', [
            'settings' => \App\Services\AttendanceIntegrity\AttendanceSettings::all(),
        ], false);
    }

    /** Staff / student search for the filter bar. */
    public function lookup(Request $request, string $kind)
    {
        abort_unless(IntegrityAccess::any(), 403);
        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        return response()->json($kind === 'students' ? Lookup::searchStudents($q) : Lookup::searchStaff($q));
    }

    /** Groups and modules of the chosen terms (the filter bar reloads them). */
    public function termOptions(Request $request)
    {
        abort_unless(IntegrityAccess::any(), 403);
        $ids = array_values(array_filter(array_map('intval', explode(',', (string) $request->input('terms', '')))));

        return response()->json(Lookup::termOptions($ids));
    }
}
