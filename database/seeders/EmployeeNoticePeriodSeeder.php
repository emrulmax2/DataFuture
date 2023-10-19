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

        EmployeeNoticePeriod::factory()
                ->count(6)
                ->sequence(
                    ['name' => '1 Week'],
                    ['name' => '2 Weeks'],
                    ['name' => '3 Weeks'],
                    ['name' => '4 Weeks'],
                    ['name' => '1 Calendar Month'],
                    ['name' => '2 Months'],
                )
                ->create();
    }
}
