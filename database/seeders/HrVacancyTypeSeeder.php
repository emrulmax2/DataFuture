<?php

namespace Database\Seeders;

use App\Models\HrVacancyType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HrVacancyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HrVacancyType::insert([
            [ 
                'name' => 'Internal',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'External',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
