<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'full_name' => "{$name} Formula 1 Team",
            'base' => fake()->city().', '.fake()->country(),
            'principal' => fake()->name(),
            'founded_year' => fake()->numberBetween(1950, 2020),
            'color' => fake()->hexColor(),
        ];
    }
}
