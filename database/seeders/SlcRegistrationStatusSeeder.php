<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SlcRegistrationStatus;

class SlcRegistrationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SlcRegistrationStatus::insert([
            [ 
                'name' => 'Yes',
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'No',
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'RCNR',
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ],
            [ 
                'name' => 'NA',
                'active' => 1,
                'created_by' => 1,
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
