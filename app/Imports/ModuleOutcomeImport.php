<?php

namespace App\Imports;

use App\Models\ModuleOutcome;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class ModuleOutcomeImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ModuleOutcome([
            'name' => $row['name'],
            'is_hesa' => isset($row['is_hesa']) ? $row['is_hesa'] : 0,
            'hesa_code' => (isset($row['is_hesa']) && $row['is_hesa'] == 1 && !empty($row['hesa_code']) ? $row['hesa_code'] : null),
            'is_df' => isset($row['is_df']) ? $row['is_df'] : 0,
            'df_code' => (isset($row['is_df']) && $row['is_df'] == 1 && !empty($row['df_code']) ? $row['df_code'] : null),
            'active' => (isset($row['status']) && $row['status'] > 0 ? $row['status'] : 0),
            'created_by' => Auth::id()
        ]);
    }
}
