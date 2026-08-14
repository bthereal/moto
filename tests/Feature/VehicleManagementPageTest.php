<?php

namespace Tests\Feature;

use App\Enums\VehiclePartStatus;
use App\Models\Part;
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

    public function test_a_part_requirement_can_be_advanced_through_its_lifecycle(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        $component = Volt::actingAs($user)->test('vehicles.show', ['vehicle' => $vehicle]);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('in-transit', $vehiclePart->fresh()->status->value);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('delivered', $vehiclePart->fresh()->status->value);

        $component->call('advanceStatus', $vehiclePart);
        $this->assertSame('fitted', $vehiclePart->fresh()->status->value);
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
