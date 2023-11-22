<?php

namespace Database\Seeders;

use App\Models\EmployeeWorkDocumentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeWorkDocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeWorkDocumentType::factory()
                ->count(1)
                ->sequence(
                    ['name' => 'Passport'],
                )
                ->create();
    }
}
