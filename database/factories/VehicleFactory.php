<?php

namespace Database\Factories;

use App\Enums\VehicleStatus;
use App\Models\Team;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'driver_id' => null,
            'chassis' => strtoupper(fake()->bothify('??-##')),
            'engine_supplier' => fake()->randomElement(['Mercedes', 'Ferrari', 'Honda RBPT', 'Renault']),
            'car_number' => fake()->unique()->numberBetween(1, 99),
            'season_year' => now()->year,
            'status' => VehicleStatus::Active,
        ];
    }
}
