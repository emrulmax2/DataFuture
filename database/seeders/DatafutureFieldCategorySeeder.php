<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DatafutureFieldCategory;

class DatafutureFieldCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DatafutureFieldCategory::insert([
            [ 
                'name' => 'Course',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Qualifications',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Venue',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
