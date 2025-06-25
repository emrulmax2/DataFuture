<?php

namespace App\Imports;

use App\Models\OtherAcademicQualification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OtherAcademicQualificationImport implements ToModel, WithHeadingRow
{
     /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new OtherAcademicQualification([
            'name' => $row['name'],
            'active' => (isset($row['status']) && $row['status'] > 0 ? $row['status'] : 0),
            'created_by' => Auth::id()
        ]);
    }
}
