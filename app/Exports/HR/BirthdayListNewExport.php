<?php

namespace App\Exports\HR;

use App\Services\HR\BirthdayList;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Birthday List, new version: the list as on screen (filters and order), then staff with no date of birth. */
class BirthdayListNewExport implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function title(): string
    {
        return 'Birthday List';
    }

    public function headings(): array
    {
        return ['Month', 'Day', 'Staff member', 'Works no.', 'Gender', 'Type', 'Department', 'Status', 'Date of birth', 'Age', 'Next birthday', 'Days to go', 'Turns', 'Milestone'];
    }

    public function array(): array
    {
        $status = fn ($r) => $r['status'] === 1 ? 'Active' : 'Inactive';
        $rows = array_map(fn ($p) => [
            BirthdayList::MONTHS[$p['m'] - 1],
            $p['d'],
            $p['name'],
            $p['works'],
            $p['gender'],
            $p['typeName'],
            $p['departmentName'],
            $status($p),
            date('d/m/Y', strtotime($p['dob'])),
            $p['age'],
            $p['next']->format('D d/m/Y'),
            $p['days'],
            $p['turns'],
            $p['milestone'] ? 'Yes' : '',
        ], $this->data['list']);

        foreach ($this->data['noDob'] as $r) {
            $rows[] = ['', '', $r['name'], $r['works'], $r['gender'], $r['typeName'], $r['departmentName'], $status($r), 'No date of birth', '', '', '', '', ''];
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return ['A' => 12, 'B' => 6, 'C' => 30, 'D' => 12, 'E' => 10, 'F' => 13, 'G' => 26, 'H' => 10, 'I' => 16, 'J' => 6, 'K' => 16, 'L' => 11, 'M' => 7, 'N' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
