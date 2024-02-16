<?php

namespace Database\Seeders;

use App\Models\AssessmentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssessmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AssessmentType::factory()
                ->count(1)
                ->create();
    }
}
