<?php

namespace Database\Seeders;

use App\Models\ApplicationRejectedReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationRejectedReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApplicationRejectedReason::insert([
            [ 
                'name' => 'Failed Interview',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Shortage of Document',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Wrong Information',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Lack of Qualification',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Unavailable for Communication',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Failure in English Test',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Previous Bad Records',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
