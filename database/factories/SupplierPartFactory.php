<?php

namespace Database\Factories;

use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierPart>
 */
class SupplierPartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'part_id' => Part::factory(),
            'quantity' => fake()->numberBetween(0, 40),
            'price' => fake()->randomFloat(2, 50, 25000),
            'location' => fake()->city().', '.fake()->country(),
            'delivery_cost' => fake()->randomFloat(2, 10, 500),
        ];
    }

    /**
     * Indicate that this listing has stock available to order.
     */
    public function inStock(int $quantity = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Indicate that this listing is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}
