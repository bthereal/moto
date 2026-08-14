<?php

namespace Tests\Feature;

use App\Enums\VehiclePartStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_it_only_lists_vehicles_in_testing(): void
    {
        $user = User::factory()->create();
        $testing = Vehicle::factory()->create(['status' => 'testing']);
        $active = Vehicle::factory()->create(['status' => 'active']);
        $retired = Vehicle::factory()->create(['status' => 'retired']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText($testing->chassis);
        $response->assertDontSeeText($active->chassis);
        $response->assertDontSeeText($retired->chassis);
    }

    public function test_it_shows_each_required_parts_status(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);
        $vehiclePart = VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::InTransit)->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText($vehiclePart->part->name);
        $response->assertSeeText('in-transit');
    }

    public function test_it_handles_a_testing_vehicle_with_no_required_parts(): void
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['status' => 'testing']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText($vehicle->chassis);
        $response->assertSeeText('No parts required.');
    }

    public function test_it_shows_a_message_when_no_vehicles_are_in_testing(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('No vehicles are currently in testing.');
    }
}
