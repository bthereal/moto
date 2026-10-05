<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\VehicleStatus;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * The Livewire pages mutate state directly, so they need the same admin-only
 * policy checks as the API. These cover both halves: the action is refused
 * server-side, and the control is not rendered for non-admins.
 */
class WebAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function nonAdmin(): User
    {
        return User::factory()->role(UserRole::Engineer)->create();
    }

    public function test_a_non_admin_cannot_create_a_team_from_the_index_page(): void
    {
        Volt::actingAs($this->nonAdmin())
            ->test('teams.index')
            ->set('showCreateForm', true)
            ->set('name', 'Rogue Racing')
            ->set('full_name', 'Rogue Racing Team')
            ->set('base', 'Nowhere')
            ->set('principal', 'Nobody')
            ->set('founded_year', 2020)
            ->set('color', '#000000')
            ->call('createTeam')
            ->assertForbidden();

        $this->assertDatabaseMissing('teams', ['name' => 'Rogue Racing']);
    }

    public function test_a_non_admin_cannot_delete_a_team(): void
    {
        $team = Team::factory()->create();

        Volt::actingAs($this->nonAdmin())
            ->test('teams.index')
            ->call('deleteTeam', $team->id)
            ->assertForbidden();

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    public function test_a_non_admin_cannot_delete_a_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create();

        Volt::actingAs($this->nonAdmin())
            ->test('vehicles.index')
            ->call('deleteVehicle', $vehicle->id)
            ->assertForbidden();

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
    }

    public function test_a_non_admin_cannot_change_a_vehicle_status(): void
    {
        $vehicle = Vehicle::factory()->create(['status' => VehicleStatus::Testing]);

        Volt::actingAs($this->nonAdmin())
            ->test('vehicles.show', ['vehicle' => $vehicle])
            ->set('status', VehicleStatus::Retired->value)
            ->call('updateStatus')
            ->assertForbidden();

        $this->assertSame(VehicleStatus::Testing, $vehicle->fresh()->status);
    }

    public function test_a_non_admin_cannot_delete_a_part(): void
    {
        $part = Part::factory()->create();

        Volt::actingAs($this->nonAdmin())
            ->test('parts.index')
            ->call('deletePart', $part->id)
            ->assertForbidden();

        $this->assertDatabaseHas('parts', ['id' => $part->id]);
    }

    public function test_a_non_admin_cannot_delete_a_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        Volt::actingAs($this->nonAdmin())
            ->test('suppliers.index')
            ->call('deleteSupplier', $supplier->id)
            ->assertForbidden();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    public function test_a_non_admin_cannot_remove_a_supplier_listing(): void
    {
        $supplier = Supplier::factory()->create();
        $listing = SupplierPart::factory()->create(['supplier_id' => $supplier->id]);

        Volt::actingAs($this->nonAdmin())
            ->test('suppliers.show', ['supplier' => $supplier])
            ->call('removeSupplierPart', $listing->id)
            ->assertForbidden();

        $this->assertDatabaseHas('supplier_parts', ['id' => $listing->id]);
    }

    public function test_admin_only_controls_are_hidden_from_non_admins(): void
    {
        Team::factory()->create();
        Part::factory()->create();
        Supplier::factory()->create();

        $this->actingAs($this->nonAdmin());

        $this->get('/teams')->assertOk()->assertDontSee('New team');
        $this->get('/parts')->assertOk()->assertDontSee('New part');
        $this->get('/suppliers')->assertOk()->assertDontSee('New supplier');
    }

    public function test_admin_only_controls_are_visible_to_admins(): void
    {
        Team::factory()->create();

        $this->actingAs(User::factory()->role(UserRole::Admin)->create());

        $this->get('/teams')->assertOk()->assertSee('New team');
    }
}
