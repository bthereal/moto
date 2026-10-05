<?php

namespace Tests\Feature;

use App\Enums\VehiclePartStatus;
use App\Models\Part;
use App\Models\SupplierPart;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class VehicleManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/vehicles')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_vehicles_index(): void
    {
        $user = User::factory()->create();
        Vehicle::factory(2)->create();

        $this->actingAs($user)
            ->get('/vehicles')
            ->assertOk()
            ->assertSeeLivewire('vehicles.index');
    }

    public function test_a_vehicles_status_can_be_updated_from_the_show_page(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'active']);

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->set('status', 'retired')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'retired']);
    }

    public function test_a_vehicle_cannot_be_activated_from_the_show_page_while_parts_are_outstanding(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->set('status', 'active')
            ->call('updateStatus')
            ->assertHasErrors('status');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'testing']);
    }

    public function test_a_part_can_be_required_from_the_show_page_while_testing(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $part = Part::factory()->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->set('partId', $part->id)
            ->call('requirePart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vehicle_parts', [
            'vehicle_id' => $vehicle->id,
            'part_id' => $part->id,
            'status' => 'required',
        ]);
    }

    public function test_a_part_requirement_can_be_advanced_through_its_lifecycle_once_ordered(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Ordered)->create();

        $component = Volt::actingAs($user)->test('vehicles.show', ['vehicle' => $vehicle]);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('in-transit', $vehiclePart->fresh()->status->value);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('delivered', $vehiclePart->fresh()->status->value);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('fitted', $vehiclePart->fresh()->status->value);
    }

    public function test_advancing_a_required_part_directly_is_a_no_op(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->call('advanceStatus', $vehiclePart);

        $this->assertSame('required', $vehiclePart->fresh()->status->value);
    }

    public function test_ordering_a_required_part_opens_the_modal_with_in_stock_suppliers(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $part = Part::factory()->create();
        $vehiclePart = VehiclePart::factory()->for($vehicle)->for($part)->status(VehiclePartStatus::Required)->create();

        $inStock = SupplierPart::factory()->for($part)->inStock(5)->create();
        SupplierPart::factory()->for($part)->outOfStock()->create();
        SupplierPart::factory()->outOfStock()->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->call('openOrderModal', $vehiclePart)
            ->assertSet('orderingVehiclePartId', $vehiclePart->id)
            ->assertDispatched('open-modal', 'order-part')
            ->assertViewHas('availableSupplierParts', function ($listings) use ($inStock) {
                return $listings->count() === 1 && $listings->first()->id === $inStock->id;
            });
    }

    public function test_confirming_an_order_places_it_and_advances_the_part(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $part = Part::factory()->create();
        $vehiclePart = VehiclePart::factory()->for($vehicle)->for($part)->status(VehiclePartStatus::Required)->create();
        $supplierPart = SupplierPart::factory()->for($part)->inStock(5)->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->call('openOrderModal', $vehiclePart)
            ->set('selectedSupplierPartId', $supplierPart->id)
            ->call('confirmOrder')
            ->assertHasNoErrors()
            ->assertSet('orderingVehiclePartId', null)
            ->assertDispatched('close-modal', 'order-part');

        $this->assertSame('ordered', $vehiclePart->fresh()->status->value);
        $this->assertSame(4, $supplierPart->fresh()->quantity);
        $this->assertDatabaseHas('vehicle_part_orders', [
            'vehicle_part_id' => $vehiclePart->id,
            'supplier_part_id' => $supplierPart->id,
        ]);
    }

    public function test_confirming_an_order_without_a_selection_fails_validation(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->call('openOrderModal', $vehiclePart)
            ->call('confirmOrder')
            ->assertHasErrors('selectedSupplierPartId');

        $this->assertSame('required', $vehiclePart->fresh()->status->value);
    }

    public function test_a_vehicle_can_be_activated_from_the_show_page_once_parts_are_fitted(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Fitted)->create();

        Volt::actingAs($user)
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->set('status', 'active')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'active']);
    }
}
