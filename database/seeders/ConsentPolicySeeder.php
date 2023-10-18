<?php

namespace Database\Seeders;

use App\Models\ConsentPolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConsentPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ConsentPolicy::insert([
            [
                'name' => 'Alumni', 
                'description' => 'LCC Alumni service will contact you for following events and services,CV and cover letter writing drop in sessions and career workshops, Employability advice, including work experiences and interview techniques. You will be invited to career events, graduate fairs, employer forum and networking assistance.', 
                'department_id' => 4, 
                'is_required' => 'Yes', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Marketing', 
                'description' => 'We may contact you with up to date Information about courses offered by the college including changes to our course offer.', 
                'department_id' => 10, 
                'is_required' => 'Yes',
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Employment', 
                'description' => 'Career advice, Career Events, Drop in Session, Seminar', 
                'is_required' => 'No', 
                'department_id' => NULL, 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'LCC Facilities', 
                'description' => 'LCC facilities will contact you with Up to date news regarding college campus facilities and upcoming events',
                'department_id' => 6, 
                'is_required' => 'No',
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ],
            [
                'name' => 'Student engagement', 
                'description' => 'LCC student engagement team will contact you about opportunities to network with other LCC alumni/students including fun trips, Winter Extravaganza, food fair, graduate reunions and many more.', 
                'department_id' => 5, 
                'is_required' => 'No', 
                'created_by' => 1, 
                'created_at' => date("Y-m-d", time())
            ]
        ]);
    }
}
