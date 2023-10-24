<?php

namespace Database\Seeders;

use App\Models\EmployeeJobTitle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeJobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeJobTitle::factory()
                ->count(6)
                ->sequence(
                    ['name' => 'Senior Staff'],
                    ['name' => 'Chief Accountant'],
                    ['name' => 'CEO'],
                    ['name' => 'Director'],
                    ['name' => 'Administrator'],
                    ['name' => 'Hr Admin'],
                )
                ->create();
    }
}
