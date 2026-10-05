<?php

namespace Database\Factories;

use App\Models\SupplierPart;
use App\Models\VehiclePart;
use App\Models\VehiclePartOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehiclePartOrder>
 */
class VehiclePartOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_part_id' => VehiclePart::factory(),
            'supplier_part_id' => SupplierPart::factory(),
            'price' => fake()->randomFloat(2, 50, 25000),
            'delivery_cost' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
