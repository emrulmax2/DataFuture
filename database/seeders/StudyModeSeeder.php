<?php

namespace Database\Seeders;

use App\Models\StudyMode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudyModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudyMode::insert([
            [ 
                'name' => 'Full-time according to funding council definitions',
                'is_hesa' => 1,
                'hesa_code' => '01',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Other full-time',
                'is_hesa' => 1,
                'hesa_code' => '02',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Sandwich (thick) according to funding council definitions',
                'is_hesa' => 1,
                'hesa_code' => '03',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Sandwich (thin) according to funding council definitions',
                'is_hesa' => 1,
                'hesa_code' => '04',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Other sandwich course/programme',
                'is_hesa' => 1,
                'hesa_code' => '05',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Part-time',
                'is_hesa' => 1,
                'hesa_code' => '06',
                'is_df' => 0,
                'df_code' => NULL,
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
