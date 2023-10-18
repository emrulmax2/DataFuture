<?php

namespace Database\Seeders;

use App\Models\EmployeeWorkType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkType;

class EmployeeWorkTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeWorkType::insert([
            [
                'name' => 'Volunteer', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Contractor', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Employee', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
