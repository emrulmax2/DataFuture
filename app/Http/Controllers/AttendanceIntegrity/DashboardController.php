<?php

namespace App\Http\Controllers\AttendanceIntegrity;

use App\Models\AttendanceIntegrity\AimScanRun;
use App\Services\AttendanceIntegrity\AttendanceSettings;
use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use App\Services\AttendanceIntegrity\Metrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance Integrity > Dashboard, the drill-down record lists behind it, and
 * Settings (every attendance rule, scans and the history seal).
 */
class DashboardController extends IntegrityBaseController
{
    /** /attendance-integrity: the first area this user may open. */
    public function home()
    {
        $home = IntegrityAccess::home();
        abort_unless($home, 403, 'You do not have access to Attendance Integrity.');

        return redirect($home);
    }

    public function index(Request $request)
    {
        IntegrityAccess::authorize('view_dashboard');
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'dashboard', null, $f->query());

        $lastScan = AimScanRun::where('status', 'completed')->orderByDesc('finished_at')->first();

        return $this->shell($request, 'pages.attendance-integrity.dashboard', 'Dashboard', [
            'filters' => $f,
            'data' => Metrics::dashboard($f),
            'lastScan' => $lastScan,
            'historySince' => Schema::hasTable('attendance_audit_logs') ? DB::table('attendance_audit_logs')->min('changed_at') : null,
        ]);
    }

    /** The attendance records behind a dashboard figure. */
    public function records(Request $request)
    {
        IntegrityAccess::authorize('view_dashboard');
        $metric = array_key_exists($request->input('metric'), Metrics::RECORD_METRICS) ? $request->input('metric') : 'recorded';
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'records', null, ['metric' => $metric] + $f->query());

        return $this->shell($request, 'pages.attendance-integrity.records', Metrics::RECORD_METRICS[$metric], [
            'filters' => $f,
            'metric' => $metric,
            'metrics' => Metrics::RECORD_METRICS,
        ]);
    }

    public function recordsData(Request $request)
    {
        IntegrityAccess::authorize('view_dashboard');
        $metric = array_key_exists($request->input('metric'), Metrics::RECORD_METRICS) ? $request->input('metric') : 'recorded';
        $size = min(max((int) $request->input('size', 50), 10), 200);

        return response()->json(Metrics::records(Filters::fromRequest($request), $metric, max(1, (int) $request->input('page', 1)), $size));
    }

    /**
     * Settings: every attendance rule (correction deadline and reason, who may record
     * attendance, how attendance is counted, the detection rules, risk levels and
     * scan times), plus scans and the history seal. Moved here from Site Settings
     * (user, 3 Nov 2026); the old address redirects. Needs Manage settings.
     */
    public function settings(Request $request)
    {
        IntegrityAccess::authorize('manage_settings');
        $tab = $request->input('tab') === 'scans' ? 'scans' : 'rules';

        $runs = AimScanRun::orderByDesc('id')->limit(40)->get();
        $terms = DB::table('term_declarations')->whereIn('id', $runs->pluck('term_declaration_id')->filter()->unique())->pluck('name', 'id');
        $lastSeal = Schema::hasTable('attendance_audit_seals') ? DB::table('attendance_audit_seals')->orderByDesc('id')->first() : null;
        $history = null;
        if (Schema::hasTable('attendance_audit_logs')) {
            $row = DB::table('attendance_audit_logs')->selectRaw('MIN(changed_at) AS since, COUNT(*) AS n')->first();
            $history = ['since' => $row->since, 'count' => (int) $row->n];
        }
        $related = array_values(array_filter([
            ['label' => 'Venues and campus IP addresses', 'hint' => 'Starting and ending a class checks the campus IP list.', 'route' => 'venues', 'icon' => 'map-pin'],
            ['label' => 'Holiday years and bank holidays', 'hint' => 'Class dates skip bank holidays when they are generated.', 'route' => 'holiday.year', 'icon' => 'calendar-days'],
            ['label' => 'Term declarations', 'hint' => 'The attendance terms that reports and scans group by.', 'route' => 'term-declaration.index', 'icon' => 'calendar-range'],
        ], fn ($r) => \Illuminate\Support\Facades\Route::has($r['route'])));

        return $this->shell($request, 'pages.attendance-integrity.settings', 'Settings', [
            'tab' => $tab,
            'groups' => AttendanceSettings::groups(),
            'values' => AttendanceSettings::all(),
            'statuses' => AttendanceSettings::feedStatuses(),
            'related' => $related,
            'runs' => $runs,
            'runTerms' => $terms,
            'lastSeal' => $lastSeal,
            'history' => $history,
            'filterOptions' => \App\Services\AttendanceIntegrity\Lookup::filterOptions(),
        ], false);
    }

    /** Save the attendance rules. Only the settings that changed are written and logged. */
    public function saveSettings(Request $request)
    {
        IntegrityAccess::authorize('manage_settings');
        $changes = AttendanceSettings::save($request->all(), (int) auth()->id());
        if ($changes) {
            IntegrityAccess::log('settings', null, null, ['changed' => array_map(fn ($c) => ['from' => $c[0], 'to' => $c[1]], $changes)]);
        }

        return redirect()->route('attendance.integrity.settings')
            ->with('aim_success', $changes ? 'Settings saved ('.count($changes).' changed).' : 'Nothing changed.');
    }

    /** Queue scans for the chosen terms; the scheduler runs them within five minutes. */
    public function queueScan(Request $request)
    {
        IntegrityAccess::authorize('manage_settings');
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('scan_terms', []))));
        $ids = DB::table('term_declarations')->whereIn('id', $ids)->pluck('id')->map(fn ($v) => (int) $v)->all();
        if (! $ids) {
            return back()->with('aim_error', 'Choose at least one term to scan.');
        }

        $now = now('Europe/London')->format('Y-m-d H:i:s');
        $queued = 0;
        foreach ($ids as $termId) {
            if (AimScanRun::where('term_declaration_id', $termId)->whereIn('status', ['pending', 'running'])->exists()) {
                continue;
            }
            AimScanRun::create(['scope' => 'term', 'term_declaration_id' => $termId, 'status' => 'pending', 'requested_by' => auth()->id(),
                'requested_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
            $queued++;
        }
        IntegrityAccess::log('scan', null, null, ['terms' => $ids, 'queued' => $queued]);

        return back()->with('aim_success', $queued
            ? $queued.' '.($queued === 1 ? 'scan' : 'scans').' queued. They run within five minutes; this page shows when they finish.'
            : 'Those terms are already queued.');
    }
}
