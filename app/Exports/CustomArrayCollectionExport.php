<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomArrayCollectionExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $styles["{$columnLetter}{$rowIndex}"] = [
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '22d3ee']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
                ];
                // Set row height
                //$sheet->getRowDimension($rowIndex)->setRowHeight(20); // Adjust height as needed
                // Apply text rotation for the second header row
                if ($rowIndex == 2) {
                    $styles["{$columnLetter}{$rowIndex}"] = [
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '164e63']],
                        'alignment' => ['horizontal' => 'center', 'vertical' => 'center' , 'textRotation' => 90],
                        'font' => ['color' => ['rgb' => 'FFFFFF']] // Change text color to red
                    ];
                    $sheet->getRowDimension($rowIndex)->setRowHeight(250); // Auto height
                }
            }
        }

        // Merge cells for the first header row
        // first 7 row is reserved for header then merge next
        $sheet->mergeCells('H1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->moduleList) + 7) . '1');
        
        //Apply background color based on module values
        foreach ($this->data as $rowIndex => $row) {
            foreach ($this->moduleList as $colIndex => $module) {
                $cellValue = $row[$colIndex + 7]; // Adjust index based on your data structure
                if (isset($cellValue) && $cellValue!="") {
                    $rgbColor = $cellValue == 'P' ? '60a5fa' : ($cellValue == 'M' ? '4ade80' : ($cellValue == 'D' ? '22d3ee' : 'fed7aa')); // Change color as needed
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 8) . ($rowIndex + 2); // Adjust index based on your data structure
                    $styles[$cellCoordinate] = ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $rgbColor]]]; // Change color as needed
                }
            }
        }


        // Center align all text from the 3rd row onwards for columns after column B
        $highestRow = count($this->data) + count($this->headers);
        for ($row = 3; $row <= $highestRow; $row++) {
            for ($col = 3; $col <= \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()); $col++) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $styles["{$columnLetter}{$row}"]['alignment'] = ['horizontal' => 'center', 'vertical' => 'center'];
            }
        }


        return $styles;
    }

    
    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 30,
            'C' => 30,
            'D' => 30,
            'E' => 30,
            'F' => 30,
            'G' => 30,
            'H' => 30,
            'I' => 30,
            'J' => 30,
            'K' => 30,
            'L' => 30,
            'M' => 30,
            'N' => 30,
            'O' => 30,
            'P' => 30,
            'Q' => 30,
            'R' => 30,
            'S' => 30,
            'T' => 30,
            'U' => 30,
            'V' => 30,
            'W' => 30,
            'X' => 30,
            'Y' => 30,
            'Z' => 30,
            // Add more columns as needed
        ];
    }
}