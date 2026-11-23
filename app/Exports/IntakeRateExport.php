<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Excel version of a new intake rate report (Retention Rate; works for
 * Continuation Rate too): a "Report" sheet laid out like the PDF and a
 * "Students" sheet with one row per student of the counted courses.
 */
class IntakeRateExport implements WithMultipleSheets
{
    public function __construct(
        private array $report,
        private array $students,
        private array $copy,
        private string $author,
        private string $created
    ) {
    }

    public function sheets(): array
    {
        return [
            new IntakeRateSummarySheet($this->report, $this->copy, $this->author, $this->created),
            new IntakeRateStudentsSheet($this->students, $this->copy),
        ];
    }
}
