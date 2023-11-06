<?php

namespace App\Imports;

use App\Models\HrBankHoliday;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class HrBankHolidayImport implements ToModel, WithHeadingRow
{
    protected $hr_holiday_year_id;

    public function __construct($hr_holiday_year_id){
        //array works
        $this->hr_holiday_year_id = $hr_holiday_year_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $start_date = (isset($row['start_date']) && !empty($row['start_date']) ? date('Y-m-d', strtotime($row['start_date'])) : null);
        $end_date = (isset($row['end_date']) && !empty($row['end_date']) ? date('Y-m-d', strtotime($row['end_date'])) : null);

        return new HrBankHoliday([
            'hr_holiday_year_id' => (isset($row['hr_holiday_year_id']) && $row['hr_holiday_year_id'] == $this->hr_holiday_year_id ? $row['hr_holiday_year_id'] : $this->hr_holiday_year_id),
            'name' => $row['name'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'duration' => $row['duration'],
            'description' => (isset($row['description']) && !empty($row['description']) ? $row['description'] : null),
            'created_by' => auth()->user()->id
        ]);
    }
}
