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
    protected $allModuleNames;
    protected $allLevelHourNames;
    protected $columnMergeData;

    public function __construct(array $data, array $headers, array $allModuleNames, array $allLevelHourNames, array $columnMergeData = [])
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->allModuleNames = $allModuleNames;
        $this->allLevelHourNames = $allLevelHourNames;
        $this->columnMergeData = $columnMergeData;
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
    $centerAlignment = ['horizontal' => 'center', 'vertical' => 'center'];

    $styles[1] = [
        'font' => ['bold' => true],
        'alignment' => $centerAlignment,
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '22d3ee']
        ]
    ];

    $styles[2] = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'alignment' => $centerAlignment,
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '164e63']
        ]
    ];

    $sheet->getRowDimension(2)->setRowHeight(250);
    
    $staticColumnCount = 5;
    $headerColumnCount = count($this->headers[0]);

    $totalHoursRequiredColIndex1Based = -1;
    $totalHoursCompletedColIndex1Based = -1;
    $headerRow1 = $this->headers[0];
    for ($k = 0; $k < $headerColumnCount; $k++) {
        if ($headerRow1[$k] === "Total Hours Required") {
            $totalHoursRequiredColIndex1Based = $k + 1;
        } elseif ($headerRow1[$k] === "Total Hours Completed") {
            $totalHoursCompletedColIndex1Based = $k + 1;
        }
    }

    for ($col = 1; $col <= $headerColumnCount; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        if ($col > $staticColumnCount &&
            $col !== $totalHoursRequiredColIndex1Based && 
            $col !== $totalHoursCompletedColIndex1Based) {

            $styles[$colLetter.'2']['alignment'] = [
                'horizontal' => 'center',
                'vertical' => 'center',
                'textRotation' => 90
            ];
            $styles[$colLetter.'2']['fill'] = [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '164e63']
            ];
        } else {
            $styles[$colLetter.'2'] = [
                'alignment' => $centerAlignment,
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '164e63']
                ]
            ];
        }
    }

    if (isset($this->columnMergeData['row1'])) {
        foreach ($this->columnMergeData['row1'] as $mergeInfo) {
            if ($mergeInfo['span'] > 1) {
                $startColChar = Coordinate::stringFromColumnIndex($mergeInfo['start_col_abs'] + 1);
                $endColChar = Coordinate::stringFromColumnIndex($mergeInfo['start_col_abs'] + $mergeInfo['span']);
                $sheet->mergeCells($startColChar.'1:'.$endColChar.'1');
            }
        }
    }

    $highestRow = count($this->data) + count($this->headers);
    $highestColumnLetter = $sheet->getHighestDataColumn();

    if ($highestRow > 2) {
        $styles['A3:'.$highestColumnLetter.$highestRow] = [
            'alignment' => $centerAlignment
        ];
    }

    if ($highestRow > 0 && !empty($highestColumnLetter)) {
        $cellRange = 'A1:' . $highestColumnLetter . $highestRow;
        $styles[$cellRange]['borders'] = [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ];
    }

    $sheet->getRowDimension(1)->setRowHeight(25);

    return $styles;
}

    public function columnWidths(): array
    {
        $widths = [
            'A' => 15, // Student ID
            'B' => 30, // Student Name
            'C' => 15, // Status
            'D' => 20, // Intake Semester
            'E' => 30, // Course
        ];

        $columnIndex = 6;

        $headerRowForWpdAndModules = $this->headers[1];
        $headerRowForTotals = $this->headers[0];
        $wpdColumnsCount = 0;
        $moduleColumnsCount = 0;
        $totalColumnsCount = 0;

        $staticCols = 5;
        $inWpdSection = true;
        $inModuleSection = false;

        $totalHeaderColumns = count($this->headers[0]);

        for ($i = $staticCols; $i < $totalHeaderColumns; $i++) {
            $headerTextRow1 = $headerRowForTotals[$i];
            $headerTextRow2 = isset($headerRowForWpdAndModules[$i]) ? $headerRowForWpdAndModules[$i] : '';

            if ($headerTextRow1 === "Total Hours Required" || $headerTextRow1 === "Total Hours Completed") {
                if ($inWpdSection) $inWpdSection = false;
                if ($inModuleSection) $inModuleSection = false;
                $totalColumnsCount++;
            }
            elseif ($headerTextRow2 === "Unassigned" || in_array($headerTextRow2, $this->allModuleNames) || ($headerTextRow1 === "Module List" && $headerTextRow2 === "")) {
                if ($inWpdSection) $inWpdSection = false;
                $inModuleSection = true;
                $moduleColumnsCount++;
            }
            else {
                if (!$inModuleSection)
                    $wpdColumnsCount++;
            }
        }

        for ($i = 0; $i < $wpdColumnsCount; $i++) {
            $widths[Coordinate::stringFromColumnIndex($columnIndex++)] = 12;
        }

        for ($i = 0; $i < $moduleColumnsCount; $i++) {
            $widths[Coordinate::stringFromColumnIndex($columnIndex++)] = 18;
        }

        if ($totalColumnsCount > 0) $widths[Coordinate::stringFromColumnIndex($columnIndex++)] = 22;
        if ($totalColumnsCount > 1) $widths[Coordinate::stringFromColumnIndex($columnIndex++)] = 25;

        return $widths;
    }
}
