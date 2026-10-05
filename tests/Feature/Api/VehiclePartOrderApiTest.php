<?php

namespace Tests\Feature\Api;

use App\Enums\VehiclePartStatus;
use App\Enums\VehicleStatus;
use App\Models\SupplierPart;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePartOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_in_stock_suppliers_for_the_required_part(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        $inStock = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();
        SupplierPart::factory()->for($vehiclePart->part)->outOfStock()->create();
        SupplierPart::factory()->inStock(5)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}/suppliers")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $inStock->id);
    }

    public function test_a_required_part_can_be_ordered_from_a_supplier(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}/order", [
                'supplier_part_id' => $supplierPart->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'ordered')
            ->assertJsonPath('data.order.supplier_part.id', $supplierPart->id);

        $this->assertSame(4, $supplierPart->fresh()->quantity);
    }

    public function test_ordering_rejects_a_listing_for_the_wrong_part(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();
        $wrongSupplierPart = SupplierPart::factory()->inStock(5)->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}/order", [
                'supplier_part_id' => $wrongSupplierPart->id,
            ])
            ->assertJsonValidationErrors('supplier_part_id');
    }

    public function test_ordering_rejects_an_out_of_stock_listing(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->outOfStock()->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}/order", [
                'supplier_part_id' => $supplierPart->id,
            ])
            ->assertJsonValidationErrors('supplier_part_id');
    }

    public function test_ordering_rejects_a_part_that_is_not_required(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::InTransit)->create();
        $supplierPart = SupplierPart::factory()->for($vehiclePart->part)->inStock(5)->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}/order", [
                'supplier_part_id' => $supplierPart->id,
            ])
            ->assertJsonValidationErrors('supplier_part_id');
    }

    public function test_the_generic_update_endpoint_rejects_setting_status_to_ordered(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}", ['status' => 'ordered'])
            ->assertJsonValidationErrors('status');
    }

    public function test_the_generic_update_endpoint_rejects_setting_status_to_required(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Ordered)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}", ['status' => 'required'])
            ->assertJsonValidationErrors('status');
    }
}
