<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibraryPolicyElement>
 */
class LibraryPolicyElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => "Available Book Limit",
            'code' => "available_book_limit",
            'active' => 1,
            'created_by' => 1,
            'created_at' => now()
        ];
    }
}
