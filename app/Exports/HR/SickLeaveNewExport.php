<?php

namespace App\Exports\HR;

use App\Services\HR\SickLeave;
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
 * Employee Sick Leave, new version: the old export's columns (Leave Days as YYYY-MM-DD, as before) plus the
 * absences (consecutive days joined) and how many sick days fell on a weekend.
 */
class SickLeaveNewExport extends StringValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private array $rows)
    {
        $this->setNumericConversion(false);
    }

    public function title(): string
    {
        return 'Sick leave';
    }

    public function headings(): array
    {
        return ['Employee', 'Contracted Hour', 'No Of Days', 'Leave Days', 'Absences', 'Separate absences', 'Weekend days', 'Status'];
    }

    public function array(): array
    {
        return array_map(function ($r) {
            $spells = SickLeave::spells($r['dates']);
            $label = fn ($g) => count($g) > 1 ? date('j M Y', strtotime($g[0])).' to '.date('j M Y', strtotime(end($g))).' ('.count($g).' days)' : date('j M Y', strtotime($g[0]));

            return [
                $r['name'], $r['hours'], count($r['dates']), implode(', ', $r['dates']),
                implode('; ', array_map($label, $spells)), count($spells),
                count(array_filter($r['dates'], fn ($d) => (int) date('N', strtotime($d)) >= 6)),
                $r['active'] ? 'Active' : 'Inactive',
            ];
        }, $this->rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
