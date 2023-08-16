<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default credentials
        
        AcademicYear::insert([
            [ 
                'name' => '2021 - 2022',
                'is_hesa' => '0',
                'hesa_code' => 'NULL',
                'is_df' => '0',
                'df_code' => 'NULL',
                'from_date' => '2021-09-01',
                'to_date' => '2022-04-30',
                'target_date_hesa_report' => '2023-05-31',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => '2022 - 2023',
                'is_hesa' => '0',
                'hesa_code' => 'NULL',
                'is_df' => '0',
                'df_code' => 'NULL',
                'from_date' => '2023-05-01',
                'to_date' => '2023-07-31',
                'target_date_hesa_report' => '2022-02-28',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
