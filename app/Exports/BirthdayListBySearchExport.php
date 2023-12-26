<?php

namespace App\Exports;

use App\Models\Employee;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class BirthdayListBySearchExport implements WithColumnFormatting, WithColumnWidths, FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $returnData;
    
    public function __construct($returnData)
    {
        $this->returnData = $returnData;
    }

    public function view(): View
    {
        return view('pages.hr.portal.reports.excel.birthdaylistbysearchexcel', [
            'dataList' => $this->returnData
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'D' => 18,
            'E' => 30,             
        ];
    }
}
