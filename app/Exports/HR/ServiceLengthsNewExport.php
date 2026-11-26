<?php

namespace App\Exports\HR;

use App\Services\HR\ServiceLengthsReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Employee Service Lengths, new version: the list as on screen (band and order), then staff with no start date. */
class ServiceLengthsNewExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(private array $shown, private array $noStart)
    {
    }

    public function title(): string
    {
        return 'Service Lengths';
    }

    public function headings(): array
    {
        return ['Staff member', 'Works no.', 'Started', 'Ended', 'Band', 'Years', 'Months', 'Days', 'Service', 'Service in years', 'Status'];
    }

    public function array(): array
    {
        $d = fn ($v) => $v ? date('d/m/Y', strtotime($v)) : '';
        $rows = array_map(fn ($p) => [
            $p['name'], $p['works'], $d($p['started']), $p['end'] ? $d($p['end']).($p['future'] ? ' (future)' : '') : '',
            ServiceLengthsReport::BANDS[$p['band']][0], $p['y'], $p['m'], $p['d'], $p['words'], round($p['years'], 2),
            $p['status'] === 1 ? 'Active' : 'Inactive',
        ], $this->shown);
        foreach ($this->noStart as $p) {
            $rows[] = [$p['name'], $p['works'], 'No start date', '', '', '', '', '', '', '', $p['status'] === 1 ? 'Active' : 'Inactive'];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 10, 'C' => 13, 'D' => 20, 'E' => 18, 'F' => 7, 'G' => 8, 'H' => 6, 'I' => 20, 'J' => 15, 'K' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
