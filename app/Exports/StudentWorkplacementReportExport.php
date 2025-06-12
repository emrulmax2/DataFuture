<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentWorkplacementReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $data;
    protected $headers;
    protected $moduleList;

    public function __construct(array $data, array $headers,array $moduleList)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->moduleList = $moduleList;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        foreach ($this->headers as $index => $header) {
            $rowIndex = $index;
            $styles[$rowIndex] = ['font' => ['bold' => true]];

            foreach ($header as $colIndex => $colValue) {
                $columnLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $styles["{$columnLetter}{$rowIndex}"] = [
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '22d3ee']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
                ];
                if ($rowIndex == 2) {
                    $styles["{$columnLetter}{$rowIndex}"] = [
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '164e63']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center' , 'textRotation' => 90],
                        'font' => ['color' => ['rgb' => 'FFFFFF']]
                    ];
                    $sheet->getRowDimension($rowIndex)->setRowHeight(250);
                }
            }
        }

        $sheet->mergeCells('F1:' . Coordinate::stringFromColumnIndex(count($this->moduleList) + 5) . '1');
        
        $highestRow = count($this->data) + count($this->headers);
        for ($row = 3; $row <= $highestRow; $row++) {
            for ($col = 3; $col <= Coordinate::columnIndexFromString($sheet->getHighestColumn()); $col++) {
                $columnLetter = Coordinate::stringFromColumnIndex($col);
                $styles["{$columnLetter}{$row}"]['alignment'] = ['horizontal' => 'center', 'vertical' => 'center'];
            }
        }


        return $styles;
    }

    
    public function columnWidths(): array
    {
        $widths = [
            'A' => 20,
            'B' => 40,
            'C' => 25,
            'D' => 25,
            'E' => 65,
        ];

        $moduleStartColumnIndex = 6;
        foreach ($this->moduleList as $index => $moduleName) {
            $widths[Coordinate::stringFromColumnIndex($moduleStartColumnIndex + $index)] = 20;
        }

        $totalHoursColumnLetter = Coordinate::stringFromColumnIndex($moduleStartColumnIndex + count($this->moduleList));
        $widths[$totalHoursColumnLetter] = 30;

        return $widths;
    }
}