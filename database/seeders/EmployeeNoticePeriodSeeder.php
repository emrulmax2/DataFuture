<?php

namespace Database\Seeders;

use App\Models\EmployeeNoticePeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeNoticePeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeNoticePeriod::insert([
            
            [
                "name" => "1 Week",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                "name" => "2 Weeks",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                "name" => "3 Weeks",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                "name" => "4 Weeks",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                "name" => "1 Calendar Month",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                "name" => "2 Months",
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
