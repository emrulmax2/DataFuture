<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Grade::factory()
                ->count(11)
                ->sequence(
                    ['name' => 'SUBMITTED',"code"=>"S"],
                    ['name' => 'ABSENT OR NON SUBMISSION',"code"=>"A"],
                    ['name' => 'PASS',"code"=>"P"],
                    ['name' => 'MERIT',"code"=>"M"],
                    ['name' => 'DISTINCTION',"code"=>"D"],
                    ['name' => 'REFERRED',"code"=>"R"],
                    ['name' => 'MALPRACTICE/UNFAIR PRACTICE',"code"=>"C"],
                    ['name' => 'UNCLASSIFIED/COMPENSATED',"code"=>"U"],
                    ['name' => 'WITHHOLD',"code"=>"W"],
                    ['name' => 'CORE',"code"=>"C"],
                    ['name' => 'SPECIALIST',"code"=>"S"],

                )
                ->create();
    }
}
