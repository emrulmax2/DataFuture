<?php

namespace App\Imports;

use App\Models\BankHoliday;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class HolidayImport implements ToModel, WithHeadingRow
{
    protected $academicYearId;
    
    public function __construct($academicId)
    {
        //array works
        $this->academicYearId = $academicId;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $unixStartDate = ($row['start_date'] - 25569) * 86400;
        $unixEndtDate = ($row['end_date'] - 25569) * 86400;

        $start_date =  gmdate("Y-m-d", $unixStartDate);
        $end_date =  gmdate("Y-m-d", $unixEndtDate);
        
        return new BankHoliday([
            'academic_year_id' => $this->academicYearId,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'duration' => $row['duration'],
            'title' => $row['title'],
            'type' => $row['type'],
            'created_by' => Auth::id()
        ]);
    }
}
