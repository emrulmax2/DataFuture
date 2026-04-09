<?php

namespace App\Imports;

use App\Models\Grade;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class ResultGradeImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Grade([
            'name' => $row['name'],
            'code' => isset($row['code']) ? $row['code'] : null,
            'turnitin_grade' => (isset($row['turnitin_grade']) && !empty($row['turnitin_grade']) ? $row['turnitin_grade'] : null),
            'active' => (isset($row['status']) && $row['status'] > 0 ? $row['status'] : 0),
            'created_by' => Auth::id()
        ]);
    }
}
