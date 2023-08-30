<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default credentials
        
        Status::insert([
            [ 
                'name' => 'Submitting',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'New',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'In Progress',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Rejected',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Offer Placed',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'Offer Accepted',
                'type' => 'Applicant',
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]

        ]);
    }
}
