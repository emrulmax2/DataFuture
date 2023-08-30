<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Address::insert([
            [
               'address_line_1'=>'London Churchill College',
               'address_line_2'=>'116 Cavell Street',
               'state'=>'',
               'post_code'=>'E1 2JA',
               'city'=>'London',
               'country'=>'United Kingdom',
               'created_by'=> 1,
            ],
        ]);
    
    }
}
