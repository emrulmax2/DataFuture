<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Employee Data Report sheet (EmployeeDataReport::sheet): text stays text, so phone numbers, sort codes and
 * account numbers keep their leading zeros; numbers (serial, salary, rate, hours) stay numbers.
 */
class EmployeeDataReportExport extends StringValueBinder implements FromArray, ShouldAutoSize, WithStyles, WithTitle, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private array $rows)
    {
        $this->setNumericConversion(false);
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Employee data';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
