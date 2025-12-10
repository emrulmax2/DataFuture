<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::insert([
            [ 
                'name' => 'Extended Study',
                'type' => 'Student',
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Ghost',
                'type' => 'Student',
                'active' => 0,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Discarded(non-progression)',
                'type' => 'Student',
                'active' => 0,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
        ]);
    }
}
