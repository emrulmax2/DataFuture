<?php

namespace Database\Seeders;

use App\Models\EmploymentPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploymentPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmploymentPeriod::insert([
            [
                'name' => 'Fixed Term', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Permanent', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Temporary', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
