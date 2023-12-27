<?php

namespace Database\Seeders;

use App\Models\AgentUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class AgentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Default credentials
        AgentUser::insert([
            [ 
                'email' => 'midone@left4code.com',
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'active' => 1,
                'remember_token' => Str::random(10)
            ]
        ]);

        // Fake users
        AgentUser::factory()->times(9)->create();
    }
}
