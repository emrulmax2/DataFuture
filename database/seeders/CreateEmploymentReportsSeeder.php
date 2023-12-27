<?php

namespace Database\Seeders;

use App\Models\EmploymentReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateEmploymentReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmploymentReport::insert([
            [
                'report_description'  => 'Birthday List',
                'file_name'  => 'Birthday List Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Diversity Information',
                'file_name'  => 'Diversity Information Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Contact Detail',
                'file_name'  => 'Employee Contact Detail Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Length of Service',
                'file_name'  => 'Employee Length of Service Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Record Card',
                'file_name'  => 'Employee Record Card Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Starter',
                'file_name'  => 'Employee Starter Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Telephone Directory',
                'file_name'  => 'Employee Telephone Directory Reports',
                'created_by' => 1
            ],
            [
                'report_description'  => 'Employee Eligibility Entry',
                'file_name'  => 'Employee Eligibility Entry Reports',
                'created_by' => 1
            ]
        ]);
    }
}
