<?php

namespace Database\Seeders;

use App\Models\AttendanceCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendanceCode::insert([
            ['code' => 'A', 'coc_required' => 0, 'active' => 1, 'created_by' => 1],
            ['code' => 'C', 'coc_required' => 1, 'active' => 1, 'created_by' => 1],
            ['code' => 'F', 'coc_required' => 1, 'active' => 1, 'created_by' => 1],
            ['code' => 'L', 'coc_required' => 1, 'active' => 1, 'created_by' => 1],
            ['code' => 'N', 'coc_required' => 1, 'active' => 1, 'created_by' => 1],
            ['code' => 'S', 'coc_required' => 0, 'active' => 1, 'created_by' => 1],
            ['code' => 'X', 'coc_required' => 1, 'active' => 1, 'created_by' => 1],
            ['code' => 'D', 'coc_required' => 1, 'active' => 1, 'created_by' => 1]
        ]);
    }
}
