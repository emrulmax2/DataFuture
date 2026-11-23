<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Services\Reports\AwardRate;
use Illuminate\Http\Request;

/**
 * Award Rate, new version (Reports › Intake Performance, "New version" tab):
 * one or more intakes, grouped by intake, on screen (award/screen.blade.php) and as a landscape
 * PDF (award/pdf.blade.php). The old report (AwardRateReportController) is
 * left as it was, in the "Old version" tab.
 */
class AwardRateController extends Controller
{
    use Concerns\ReadsIntakes;

    /** Wording and route names for the shared form (rate/form.blade.php). */
    public static function copy(): array
    {
        return [
            'key' => 'award',
            'title' => 'Award Rate',
            'routes' => [
                'generate' => 'reports.intake.performance.award.rate.generate',
                'pdf' => 'reports.intake.performance.award.rate.pdf',
            ],
        ];
    }

    public function generate(Request $request, AwardRate $award)
    {
        $html = view('pages.reports.intake-performance.award.screen', [
            'report' => $award->buildMany($this->postedIntakes($request)),
        ])->render();

        return response()->json(['htm' => $html]);
    }

    public function pdf(AwardRate $award, string $semesters)
    {
        $report = $award->buildMany($this->pathIntakes($semesters));

        return $this->landscapePdf('pages.reports.intake-performance.award.pdf', ['report' => $report], 'Award_Rate_'.$report['file'].'.pdf');
    }
}
