<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_list_vehicles_with_relations(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $driver = User::factory()->for($team)->role(UserRole::Driver)->create();
        Vehicle::factory()->for($team)->create(['driver_id' => $driver->id]);

        $this->actingAs($user, 'api')
            ->getJson('/api/vehicles')
            ->assertOk()
            ->assertJsonPath('data.0.team.id', $team->id)
            ->assertJsonPath('data.0.driver.id', $driver->id);
    }

    public function test_a_vehicle_requires_a_valid_team(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/vehicles', [
                'team_id' => 999,
                'chassis' => 'AB-01',
                'engine_supplier' => 'Mercedes',
                'car_number' => 44,
                'season_year' => 2026,
            ])
            ->assertJsonValidationErrors('team_id');
    }

    public function test_a_vehicle_can_be_updated(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $vehicle = Vehicle::factory()->create(['status' => 'active']);

        $this->actingAs($user, 'api')
            ->patchJson("/api/vehicles/{$vehicle->id}", ['status' => 'retired'])
            ->assertOk()
            ->assertJsonPath('data.status', 'retired');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'status' => 'retired']);
    }
}
