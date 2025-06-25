<?php

namespace Database\Seeders;

use App\Models\OtherAcademicQualification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

class OtherAcademicQualificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Date::now();
        OtherAcademicQualification::insert([
            [
                'name' => 'L3 Diploma Business Management',
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'GCSE', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 OCR', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'CACHE Child Care', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Health and Social care', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Accounting', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Travel', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 IT', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Cookery', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L2 Skills', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Certificate in Business', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Leadership', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Carpentry', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
            [
                'name' => 'L3 Diploma Children and Young People\'s', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => $now
            ],
        ]);
    }
}
