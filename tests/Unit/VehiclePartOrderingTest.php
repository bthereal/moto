<?php

namespace Tests\Unit;

use App\Enums\VehiclePartStatus;
use App\Models\SupplierPart;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class VehiclePartOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_placing_an_order_snapshots_price_and_delivery_cost(): void
    {
        $vehiclePart = VehiclePart::factory()
            ->for(Vehicle::factory())
            ->status(VehiclePartStatus::Required)
            ->create();

        $supplierPart = SupplierPart::factory()
            ->for($vehiclePart->part)
            ->inStock(10)
            ->create(['price' => 199.99, 'delivery_cost' => 24.50]);

        $order = $vehiclePart->placeOrder($supplierPart);

        $this->assertSame('199.99', $order->price);
        $this->assertSame('24.50', $order->delivery_cost);
        $this->assertSame($supplierPart->id, $order->supplier_part_id);
    }

    public function test_placing_an_order_advances_status_and_decrements_stock(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $vehiclePart->placeOrder($supplierPart);

        $this->assertSame(VehiclePartStatus::Ordered, $vehiclePart->fresh()->status);
        $this->assertSame(4, $supplierPart->fresh()->quantity);
    }

    public function test_a_vehicle_part_cannot_be_ordered_twice(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $vehiclePart->placeOrder($supplierPart);

        $this->expectException(LogicException::class);

        $vehiclePart->placeOrder($supplierPart);
    }

    public function test_a_vehicle_part_that_is_not_required_cannot_be_ordered(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::InTransit)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $this->expectException(LogicException::class);

        $vehiclePart->placeOrder($supplierPart);
    }

    public function test_a_listing_for_a_different_part_cannot_be_ordered(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->inStock(5)->create();

        $this->expectException(LogicException::class);

        $vehiclePart->placeOrder($supplierPart);
    }

    public function test_an_out_of_stock_listing_cannot_be_ordered(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->outOfStock()->create();

        $this->expectException(LogicException::class);

        $vehiclePart->placeOrder($supplierPart);
    }

    public function test_a_vehicle_parts_order_relation_resolves(): void
    {
        $vehiclePart = VehiclePart::factory()->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $order = $vehiclePart->placeOrder($supplierPart);

        $this->assertTrue($vehiclePart->order()->exists());
        $this->assertSame($order->id, $vehiclePart->order->id);
    }
}
