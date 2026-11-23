<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Services\Reports\IntakeRateReport;
use Illuminate\Http\Request;

/**
 * The new Continuation Rate and Retention Rate reports (Reports › Intake
 * Performance, "New version" tabs): one or more intakes, grouped by intake,
 * on screen (rate/screen.blade.php) and as a landscape PDF (rate/pdf.blade.php).
 * Each report supplies its figures (an IntakeRateReport) and its wording.
 */
abstract class IntakeRateController extends Controller
{
    use Concerns\ReadsIntakes;

    abstract protected function report(): IntakeRateReport;

    /**
     * Wording and route names: key, title, rate, kept, kept_lower, kept_note,
     * dropped_note, sentence (:kept / :registered / :dropped), how, not_due,
     * show_running, file, routes[generate, pdf, excel].
     */
    abstract public static function copy(): array;

    public function generate(Request $request)
    {
        $html = view('pages.reports.intake-performance.rate.screen', [
            'report' => $this->report()->buildMany($this->postedIntakes($request)),
            'copy' => static::copy(),
        ])->render();

        return response()->json(['htm' => $html]);
    }

    public function pdf(string $semesters)
    {
        $report = $this->report()->buildMany($this->pathIntakes($semesters));

        return $this->landscapePdf('pages.reports.intake-performance.rate.pdf', [
            'report' => $report,
            'copy' => static::copy(),
        ], $this->fileName($report, 'pdf'));
    }

    protected function fileName(array $report, string $extension): string
    {
        return static::copy()['file'].'_'.$report['file'].'.'.$extension;
    }
}
