<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RecordCardBySearchExport implements FromView
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
        return view('pages.hr.portal.reports.excel.recordcardbysearchexcel', [
            'dataList' => $this->returnData
        ]);
    }
}
