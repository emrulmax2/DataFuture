<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Services\Reports\SubmissionPassRate;
use Illuminate\Http\Request;

/**
 * Submission & Pass Rate, new version (Reports › Intake Performance, "New
 * version" tab): one or more intakes, grouped by intake, on screen (submission/screen.blade.php)
 * and as a landscape PDF, summary then detail (submission/pdf.blade.php).
 */
class SubmissionPassRateController extends Controller
{
    use Concerns\ReadsIntakes;

    /** Wording and route names for the shared form (rate/form.blade.php). */
    public static function copy(): array
    {
        return [
            'key' => 'submission',
            'title' => 'Submission and Pass Rate',
            'routes' => [
                'generate' => 'reports.intake.performance.submission.rate.generate',
                'pdf' => 'reports.intake.performance.submission.rate.pdf',
            ],
        ];
    }

    public function generate(Request $request, SubmissionPassRate $submission)
    {
        $html = view('pages.reports.intake-performance.submission.screen', [
            'report' => $submission->buildMany($this->postedIntakes($request)),
        ])->render();

        return response()->json(['htm' => $html]);
    }

    public function pdf(SubmissionPassRate $submission, string $semesters)
    {
        $report = $submission->buildMany($this->pathIntakes($semesters));

        return $this->landscapePdf('pages.reports.intake-performance.submission.pdf', ['report' => $report], 'Submission_Pass_Rate_'.$report['file'].'.pdf');
    }
}
