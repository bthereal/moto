<?php

namespace Database\Factories;

use App\Enums\PartCategory;
use App\Models\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'part_number' => strtoupper(fake()->unique()->bothify('??-####')),
            'category' => fake()->randomElement(PartCategory::cases()),
            'manufacturer' => fake()->company(),
            'description' => fake()->sentence(),
        ];
    }
}
