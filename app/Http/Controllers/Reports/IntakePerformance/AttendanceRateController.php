<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Services\Reports\AttendanceRate;
use Illuminate\Http\Request;

/**
 * Attendance Rate, new version (Reports › Intake Performance, "New version"
 * tab): one or more intakes, grouped by intake, on screen (attendance/screen.blade.php) and as
 * a landscape PDF, summary then detail (attendance/pdf.blade.php).
 */
class AttendanceRateController extends Controller
{
    use Concerns\ReadsIntakes;

    /** Wording and route names for the shared form (rate/form.blade.php). */
    public static function copy(): array
    {
        return [
            'key' => 'attendance',
            'title' => 'Attendance Rate',
            'routes' => [
                'generate' => 'reports.intake.performance.attendance.rate.generate',
                'pdf' => 'reports.intake.performance.attendance.rate.pdf',
            ],
        ];
    }

    public function generate(Request $request, AttendanceRate $attendance)
    {
        $html = view('pages.reports.intake-performance.attendance.screen', [
            'report' => $attendance->buildMany($this->postedIntakes($request)),
        ])->render();

        return response()->json(['htm' => $html]);
    }

    public function pdf(AttendanceRate $attendance, string $semesters)
    {
        $report = $attendance->buildMany($this->pathIntakes($semesters));

        return $this->landscapePdf('pages.reports.intake-performance.attendance.pdf', ['report' => $report], 'Attendance_Rate_'.$report['file'].'.pdf');
    }
}
