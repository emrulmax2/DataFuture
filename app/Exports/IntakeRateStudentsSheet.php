<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * "Students" sheet: one row per student of the counted courses (intake first), with the
 * outcome that put them in the report's figures (as the old export listed).
 */
class IntakeRateStudentsSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    public function __construct(private array $students, private array $copy)
    {
    }

    public function title(): string
    {
        return 'Students';
    }

    public function headings(): array
    {
        return ['Intake', 'Registration no', 'Student', 'Course', 'Awarding body ref', 'Outcome', 'Dropped out on', 'Current status'];
    }

    public function array(): array
    {
        return array_map(function ($row) {
            return [
                $row['intake'] ?? '',
                $row['registration_no'],
                $row['name'],
                $row['course'],
                $row['reference'],
                $row['outcome'],
                ($row['dropped_on'] ? Date::PHPToExcel(strtotime($row['dropped_on'])) : null),
                $row['status'],
            ];
        }, $this->students);
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $s = $event->sheet->getDelegate();
            $last = count($this->students) + 1;
            foreach (['A' => 17, 'B' => 17, 'C' => 30, 'D' => 58, 'E' => 18, 'F' => 17, 'G' => 15, 'H' => 22] as $col => $width) {
                $s->getColumnDimension($col)->setWidth($width);
            }
            $s->getStyle('A1:H1')->getFont()->setBold(true)->getColor()->setRGB('55616B');
            $s->getStyle('A1:H1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('16242B');
            $s->getStyle('G2:G'.max(2, $last))->getNumberFormat()->setFormatCode('d mmm yyyy');
            for ($row = 2; $row <= $last; $row++) {
                $outcome = $s->getCell('F'.$row)->getValue();
                if ($outcome === 'Dropped out') {
                    $s->getStyle('F'.$row)->getFont()->setBold(true)->getColor()->setRGB('8A3D0C');
                } elseif ($outcome === 'Never registered') {
                    $s->getStyle('F'.$row)->getFont()->getColor()->setRGB('55616B');
                }
            }
            $s->freezePane('A2');
            $s->setAutoFilter('A1:H'.max(1, $last));
        }];
    }
}
