<?php

namespace Tests\Feature\Api;

use App\Enums\VehiclePartStatus;
use App\Enums\VehicleStatus;
use App\Models\Part;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_part_cannot_be_required_unless_the_vehicle_is_in_testing(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Active]);
        $part = Part::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts", ['part_id' => $part->id])
            ->assertJsonValidationErrors('part_id');

        $this->assertDatabaseCount('vehicle_parts', 0);
    }

    public function test_a_part_can_be_required_while_the_vehicle_is_in_testing(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $part = Part::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/vehicles/{$vehicle->id}/parts", ['part_id' => $part->id])
            ->assertCreated()
            ->assertJsonPath('data.status', 'required')
            ->assertJsonPath('data.part.id', $part->id);

        $this->assertDatabaseHas('vehicle_parts', [
            'vehicle_id' => $vehicle->id,
            'part_id' => $part->id,
            'status' => 'required',
        ]);
    }

    public function test_a_part_requirements_status_can_be_advanced(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Required)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}", ['status' => 'in-transit'])
            ->assertOk()
            ->assertJsonPath('data.status', 'in-transit');
    }

    public function test_a_vehicle_cannot_activate_while_parts_are_outstanding(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Delivered)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}", ['status' => 'active'])
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'testing']);
    }

    public function test_a_vehicle_can_activate_once_all_parts_are_fitted(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Fitted)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}", ['status' => 'active'])
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_a_part_requirement_can_be_removed(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('vehicle_parts', ['id' => $vehiclePart->id]);
    }

    public function test_a_part_requirement_from_another_vehicle_is_not_accessible(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $otherVehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);
        $vehiclePart = VehiclePart::factory()->for($otherVehicle)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}/parts/{$vehiclePart->id}", ['status' => 'fitted'])
            ->assertNotFound();
    }
}
