<?php

namespace Database\Factories;

use App\Enums\VehiclePartStatus;
use App\Models\Part;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehiclePart>
 */
class VehiclePartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'part_id' => Part::factory(),
            'status' => fake()->randomElement(VehiclePartStatus::cases()),
        ];
    }

    /**
     * Indicate that the requirement carries a specific status.
     */
    public function status(VehiclePartStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
