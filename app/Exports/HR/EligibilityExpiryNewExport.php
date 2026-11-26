<?php

namespace App\Exports\HR;

use App\Services\HR\EligibilityExpiry;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Eligibility Expiry, new version: every person the filters keep, soonest expiry first, then expired, then no date. */
class EligibilityExpiryNewExport extends StringValueBinder implements FromArray, WithHeadings, WithColumnWidths, WithStyles, WithTitle, WithCustomValueBinder
{
    public function __construct(private array $rows, private string $kind)
    {
    }

    public function title(): string
    {
        return $this->kind === 'doc' ? 'Passport and ID' : 'Visa and work permit';
    }

    public function headings(): array
    {
        return $this->kind === 'doc'
            ? ['Employee', 'Document', 'Document number', 'Issued by', 'Expiry date', 'Days left', 'Group', 'Status']
            : ['Employee', 'Permit type', 'Permit number', 'Expiry date', 'Days left', 'Group', 'Status'];
    }

    public function array(): array
    {
        $k = $this->kind;
        $order = ['s30', 's90', 's180', 'ok', 'x', 'nd'];
        $rows = $this->rows;
        usort($rows, fn ($a, $b) => [array_search($a[$k]['band'], $order, true), $a[$k]['band'] === 'x' ? -$a[$k]['days'] : ($a[$k]['days'] ?? 0), $a['name']]
            <=> [array_search($b[$k]['band'], $order, true), $b[$k]['band'] === 'x' ? -$b[$k]['days'] : ($b[$k]['days'] ?? 0), $b['name']]);
        $status = fn ($r) => $r['status'] === 1 ? 'Active' : ($r['status'] === 0 ? 'Inactive' : 'Other');

        return array_map(function ($r) use ($k, $status) {
            $x = $r[$k];
            $date = $x['date'] ? date('d/m/Y', strtotime($x['date'])) : '';
            $days = $x['days'] === null ? '' : (string) $x['days'];
            $group = EligibilityExpiry::BANDS[$x['band']][0];

            return $k === 'doc'
                ? [$r['name'], $x['what'], $x['num'], $x['country'], $date, $days, $group, $status($r)]
                : [$r['name'], $x['what'], $x['num'], $date, $days, $group, $status($r)];
        }, $rows);
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 24, 'C' => 20, 'D' => 16, 'E' => 14, 'F' => 16, 'G' => 18, 'H' => 10];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('B2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
