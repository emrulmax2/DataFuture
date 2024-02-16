<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentType>
 */
class AssessmentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => "Assessment",
            'code' => "ASM",
            'active' => 1,
            'active' => 1,
            'created_by' => 1,
            'created_at' => now()
        ];
    }
}
