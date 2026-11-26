<?php

namespace App\Exports\HR;

use App\Services\HR\ContactDetailsReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Employee Contact Details, new version: the staff as on screen (filters, find a person, gap). */
/* Every cell is text: phone numbers keep a leading 0 or + and are never read as numbers. */
class ContactDetailsNewExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle, WithCustomValueBinder
{
    public function __construct(private array $list)
    {
    }

    public function title(): string
    {
        return 'Contact Details';
    }

    public function headings(): array
    {
        return ['Staff member', 'Works no.', 'Mobile', 'Telephone', 'Email', 'Address', 'Emergency contact', 'Relationship', 'Emergency mobile', 'Emergency telephone', 'Emergency email', 'Missing'];
    }

    public function array(): array
    {
        return array_map(fn ($p) => [
            $p['name'], $p['works'], $p['mob'], $p['tel'], $p['email'], $p['address'],
            $p['eName'], $p['eRel'], $p['eMob'], $p['eTel'], $p['eEmail'],
            implode(', ', array_map(fn ($g) => ContactDetailsReport::GAPS[$g], $p['gaps'])),
        ], $this->list);
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 10, 'C' => 15, 'D' => 15, 'E' => 32, 'F' => 46, 'G' => 26, 'H' => 14, 'I' => 16, 'J' => 16, 'K' => 30, 'L' => 30];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
