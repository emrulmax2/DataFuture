<?php

namespace App\Exports\HR;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Employee Telephone Directory, new version: A–Z as on screen. Every cell is text, so numbers keep their leading 0. */
class TelephoneDirectoryNewExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle, WithCustomValueBinder
{
    public function __construct(private array $list)
    {
    }

    public function title(): string
    {
        return 'Telephone Directory';
    }

    public function headings(): array
    {
        return ['Letter', 'Staff member', 'Mobile', 'Telephone', 'Landline', 'Email', 'Works no.', 'Status'];
    }

    public function array(): array
    {
        return array_map(fn ($p) => [
            $p['letter'], $p['name'], $p['mob'], $p['tel'], $p['land'] ? 'Yes' : '', $p['email'], $p['works'], $p['status'] === 1 ? 'Active' : 'Inactive',
        ], $this->list);
    }

    public function columnWidths(): array
    {
        return ['A' => 7, 'B' => 32, 'C' => 15, 'D' => 15, 'E' => 9, 'F' => 34, 'G' => 10, 'H' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('C2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
