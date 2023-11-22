<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceFeedStatus>
 */
class AttendanceFeedStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            
                'name' => 'Preasent', 
                'code' => 'P', 
                'active' => 1, 
                'created_by' => 1, 
                'created_at' => now()
            
        ];
    }
}
