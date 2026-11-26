<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Employee Starter Information, new version: the starters as on screen (newest first), then staff with no start date. */
class StarterReportNewExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(private array $shown, private array $noStart)
    {
    }

    public function title(): string
    {
        return 'Starters';
    }

    public function headings(): array
    {
        return ['Staff member', 'Works no.', 'Started on', 'Year', 'Month', 'Started', 'Status'];
    }

    public function array(): array
    {
        $rows = array_map(fn ($p) => [
            $p['name'], $p['works'], date('d/m/Y', strtotime($p['started'])), (int) substr($p['started'], 0, 4),
            date('F', strtotime($p['started'])), $p['ago'], $p['status'] === 1 ? 'Active' : 'Inactive',
        ], $this->shown);
        foreach ($this->noStart as $p) {
            $rows[] = [$p['name'], $p['works'], 'No start date', '', '', '', $p['status'] === 1 ? 'Active' : 'Inactive'];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 11, 'C' => 14, 'D' => 7, 'E' => 12, 'F' => 22, 'G' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
