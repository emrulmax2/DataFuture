<?php

namespace App\Exports\HR;

use App\Services\HR\OutstandingHoliday;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Outstanding Holiday, new version: the old export's columns (hours as h:mm) with the rate and balance as
 * numbers you can add up, plus the parts of Taken and decimal hours.
 */
class OutstandingHolidayNewExport extends StringValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle, WithColumnFormatting, WithCustomValueBinder, WithStrictNullComparison
{
    public function __construct(private array $rows)
    {
        $this->setNumericConversion(false);
    }

    public function title(): string
    {
        return 'Outstanding holiday';
    }

    public function headings(): array
    {
        return ['Employee', 'Department', 'Allocation', 'Taken', 'Balance Hour', 'Hourly Rate', 'Balance', 'Status',
            'Allocation (hours)', 'Taken (hours)', 'Balance (hours)', 'Holiday taken', 'Holiday booked', 'Bank holidays'];
    }

    public function array(): array
    {
        $h = fn ($m) => round($m / 60, 2);

        return array_map(fn ($r) => [
            $r['full'], $r['dept'], OutstandingHoliday::hm($r['a']), OutstandingHoliday::hm($r['t']), OutstandingHoliday::hm($r['b']),
            $r['rate'], $r['v'], $r['active'] ? 'Active' : 'Inactive',
            $h($r['a']), $h($r['t']), $h($r['b']), OutstandingHoliday::hm($r['taken']), OutstandingHoliday::hm($r['booked']), OutstandingHoliday::hm($r['bank']),
        ], $this->rows);
    }

    public function columnFormats(): array
    {
        return ['F' => '£#,##0.00', 'G' => '£#,##0.00;-£#,##0.00'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('C2');

        return [1 => ['font' => ['bold' => true]]];
    }
}
