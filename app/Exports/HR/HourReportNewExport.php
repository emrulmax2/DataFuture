<?php

namespace App\Exports\HR;

use App\Services\HR\HourReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Employee Hour Report, new version: the old export's columns (hours as h:mm, as before) plus the same
 * hours as decimal numbers and worked hours a week, for the people the page lists.
 */
class HourReportNewExport extends StringValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private array $rows, private float $weeks)
    {
        $this->setNumericConversion(false);
    }

    public function title(): string
    {
        return 'Hours';
    }

    public function headings(): array
    {
        return ['Work Number', 'NI Number', 'Name', 'Position', 'Status', 'Employee/Contractor', 'Worked Hour', 'Holiday Hour', 'Total',
            'Worked (hours)', 'Holiday (hours)', 'Total (hours)', 'Worked a week (hours)'];
    }

    public function array(): array
    {
        return array_map(fn ($r) => [
            $r['works'], $r['ni'], $r['full'], $r['job'], $r['status'], $r['type'],
            HourReport::hm($r['w']), HourReport::hm($r['h']), HourReport::hm($r['t']),
            round($r['w'] / 60, 2), round($r['h'] / 60, 2), round($r['t'] / 60, 2), round($r['w'] / 60 / $this->weeks, 1),
        ], $this->rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('D2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
