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
        EmploymentSspTerm::insert([
            [
                'name' => 'Company Sick Pay', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Occupetional Sick Pay', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
