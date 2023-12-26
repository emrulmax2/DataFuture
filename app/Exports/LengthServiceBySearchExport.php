<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LengthServiceBySearchExport implements WithColumnWidths, FromView
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
        return view('pages.hr.portal.reports.excel.lengthservicebysearchexcel', [
            'dataList' => $this->returnData
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'D' => 20,
            'E' => 40,             
        ];
    }
}
