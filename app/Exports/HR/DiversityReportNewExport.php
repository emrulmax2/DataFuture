<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Diversity Information, new version: the staff list as on screen (filters and any picked group). */
class DiversityReportNewExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(private array $list)
    {
    }

    public function title(): string
    {
        return 'Diversity Information';
    }

    public function headings(): array
    {
        return ['Staff member', 'Works no.', 'Gender', 'Ethnicity', 'Ethnic group', 'Nationality', 'Disability', 'Type', 'Department', 'Status', 'Started on', 'Ended on'];
    }

    public function array(): array
    {
        $d = fn ($v) => $v ? date('d/m/Y', strtotime($v)) : '';

        return array_map(fn ($p) => [
            $p['name'], $p['works'], $p['gender'], $p['ethnicity'], $p['ethGroup'], $p['nationality'], $p['disability'],
            $p['typeName'], $p['departmentName'], $p['status'] === 1 ? 'Active' : 'Inactive', $d($p['started']), $d($p['ended']),
        ], $this->list);
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 11, 'C' => 12, 'D' => 46, 'E' => 13, 'F' => 22, 'G' => 11, 'H' => 13, 'I' => 26, 'J' => 10, 'K' => 12, 'L' => 12];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
