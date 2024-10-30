<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibraryLocation>
 */
class LibraryLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => "L06S03E7",
            "description" => "Library Location 6 Shelf 3 Elevation 7",
            "created_by" => 1,
            "created_at" => now()
        ];
    }
}
