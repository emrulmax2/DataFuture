<?php

namespace Database\Seeders;

use App\Models\AttendanceFeedStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceFeedStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AttendanceFeedStatus::factory()
                ->count(8)
                ->sequence(
                    ['name' => 'Present', 'code' => 'P'],
                    ['name' => 'Online Present', 'code' => 'O'],
                    ['name' => 'Left Early', 'code' => 'LE'],
                    ['name' => 'Absence', 'code' => 'A'],
                    ['name' => 'Late', 'code' => 'L'],
                    ['name' => 'Excuse', 'code' => 'E'],
                    ['name' => 'Medical', 'code' => 'M'],
                    ['name' => 'Exceptional', 'code' => 'E'],
                )
                ->create();
    }
}
