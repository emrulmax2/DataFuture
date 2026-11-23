<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Services\Reports\ContinuationRate;
use App\Services\Reports\IntakeRateReport;

/** Continuation Rate, new version (Reports › Intake Performance, "New version" tab). */
class ContinuationRateController extends IntakeRateController
{
    protected function report(): IntakeRateReport
    {
        return app(ContinuationRate::class);
    }

    public static function copy(): array
    {
        return [
            'key' => 'continuation',
            'title' => 'Continuation Rate',
            'rate' => 'Continuation rate',
            'kept' => 'Continued',
            'kept_lower' => 'continued',
            'kept_note' => 'Still enrolled at day 380',
            'dropped_note' => 'Within 380 days of starting',
            'sentence' => ':kept of :registered registered students were still enrolled 380 days after starting. :dropped dropped out.',
            'how' => 'Continued ÷ Registered. A student has continued if they are still enrolled 380 days after starting. Students who never registered are left out.',
            'not_due' => 'The 380-day window for this intake ends on',
            'show_running' => false,
            'file' => 'Continuation_Rate',
            'routes' => [
                'generate' => 'reports.intake.performance.continuation.rate.generate',
                'pdf' => 'reports.intake.performance.continuation.rate.pdf',
            ],
        ];
    }
}
