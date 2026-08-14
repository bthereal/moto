<?php

namespace Tests\Unit;

use App\Enums\VehiclePartStatus;
use App\Models\Vehicle;
use App\Models\VehiclePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePartsBusinessRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_vehicle_with_no_parts_can_activate(): void
    {
        $vehicle = Vehicle::factory()->create();

        $this->assertFalse($vehicle->hasOutstandingParts());
        $this->assertTrue($vehicle->canActivate());
    }

    public function test_a_vehicle_with_an_outstanding_part_cannot_activate(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::InTransit)->create();

        $this->assertTrue($vehicle->hasOutstandingParts());
        $this->assertFalse($vehicle->canActivate());
    }

    public function test_a_vehicle_with_only_fitted_parts_can_activate(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Fitted)->create();
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Fitted)->create();

        $this->assertFalse($vehicle->hasOutstandingParts());
        $this->assertTrue($vehicle->canActivate());
    }

    public function test_a_vehicle_with_a_mix_of_fitted_and_pending_parts_cannot_activate(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Fitted)->create();
        VehiclePart::factory()->for($vehicle)->status(VehiclePartStatus::Delivered)->create();

        $this->assertFalse($vehicle->canActivate());
    }
}
