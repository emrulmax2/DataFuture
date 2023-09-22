<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Department::insert([
            [
                'name'  => 'Academic',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Academic Admin',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Admission',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Alumni',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Employability and Student engagement',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Facilities',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Finance',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'HR',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'IT and Monitoring',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Marketing',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Registry',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name'  => 'Quality Assurance',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
