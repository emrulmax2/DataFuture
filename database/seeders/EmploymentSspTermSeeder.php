<?php

namespace Database\Seeders;

use App\Models\EmploymentSspTerm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmploymentSspTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        EmploymentSspTerm::factory()
                ->count(2)
                ->sequence(
                    ['name' => 'Company Sick Pay'],
                    ['name' => 'Occupetional Sick Pay'],
                )
                ->create();
    }
}
