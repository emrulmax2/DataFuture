<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Exports\IntakeRateExport;
use App\Services\Reports\IntakeRateReport;
use App\Services\Reports\RetentionRate;
use Maatwebsite\Excel\Facades\Excel;

/** Retention Rate, new version (Reports › Intake Performance, "New version" tab). */
class RetentionRateController extends IntakeRateController
{
    protected function report(): IntakeRateReport
    {
        return app(RetentionRate::class);
    }

    public static function copy(): array
    {
        return [
            'key' => 'retention',
            'title' => 'Retention Rate',
            'rate' => 'Retention rate',
            'kept' => 'Retained',
            'kept_lower' => 'retained',
            'kept_note' => 'Still enrolled at the report date',
            'dropped_note' => 'After registering',
            'sentence' => ':kept of :registered registered students are still enrolled. :dropped dropped out.',
            'how' => 'Retained ÷ Registered. A student is retained if they registered and have not dropped out by the report date. Students who never registered are left out.',
            'not_due' => 'No course of this intake has started yet; the first starts on',
            'show_running' => true,
            'file' => 'Retention_Rate',
            'routes' => [
                'generate' => 'reports.intake.performance.retention.rate.generate',
                'pdf' => 'reports.intake.performance.retention.rate.pdf',
                'excel' => 'reports.intake.performance.retention.rate.excel',
            ],
        ];
    }

    /** The Excel version: the report as in the PDF, plus one row per student. */
    public function excel(string $semesters)
    {
        $ids = $this->pathIntakes($semesters);
        $service = $this->report();
        $report = $service->buildMany($ids);

        return Excel::download(
            new IntakeRateExport($report, $service->studentsMany($ids), static::copy(), $this->author(), now()->format('j M Y, H:i')),
            $this->fileName($report, 'xlsx')
        );
    }
}
