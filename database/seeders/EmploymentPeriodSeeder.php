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

        EmploymentPeriod::factory()
                ->count(3)
                ->sequence(
                    ['name' => 'Fixed Term'],
                    ['name' => 'Permanent'],
                    ['name' => 'Temporary'],
                )
                ->create();
    }
}
