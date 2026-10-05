<?php

namespace Tests\Feature\Api;

use App\Enums\PartCategory;
use App\Enums\UserRole;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guards the admin-only write rules enforced by App\Policies. Reads stay open
 * to every authenticated user; anything that mutates state is admin-only.
 */
class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{UserRole}>
     */
    public static function nonAdminRoles(): array
    {
        return [
            'principal' => [UserRole::Principal],
            'engineer' => [UserRole::Engineer],
            'driver' => [UserRole::Driver],
            'staff' => [UserRole::Staff],
        ];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_a_non_admin_cannot_create_a_user(UserRole $role): void
    {
        $actor = User::factory()->role($role)->create();

        $this->actingAs($actor, 'api')
            ->postJson('/api/users', [
                'name' => 'Intruder',
                'email' => 'intruder@example.com',
                'password' => 'password-long-enough',
                'role' => UserRole::Admin->value,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    #[DataProvider('nonAdminRoles')]
    public function test_a_non_admin_cannot_escalate_their_own_role(UserRole $role): void
    {
        $actor = User::factory()->role($role)->create();

        $this->actingAs($actor, 'api')
            ->patchJson("/api/users/{$actor->id}", ['role' => UserRole::Admin->value])
            ->assertForbidden();

        $this->assertSame($role, $actor->fresh()->role);
    }

    public function test_a_non_admin_cannot_delete_a_user(): void
    {
        $actor = User::factory()->role(UserRole::Engineer)->create();
        $victim = User::factory()->create();

        $this->actingAs($actor, 'api')
            ->deleteJson("/api/users/{$victim->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $victim->id]);
    }

    public function test_a_non_admin_cannot_create_teams_vehicles_parts_or_suppliers(): void
    {
        $actor = User::factory()->role(UserRole::Driver)->create();
        $this->actingAs($actor, 'api');

        $this->postJson('/api/teams', [])->assertForbidden();
        $this->postJson('/api/vehicles', [])->assertForbidden();
        $this->postJson('/api/parts', [])->assertForbidden();
        $this->postJson('/api/suppliers', [])->assertForbidden();
    }

    public function test_a_non_admin_cannot_delete_records(): void
    {
        $actor = User::factory()->role(UserRole::Staff)->create();
        $team = Team::factory()->create();
        $vehicle = Vehicle::factory()->create();
        $part = Part::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($actor, 'api');

        $this->deleteJson("/api/teams/{$team->id}")->assertForbidden();
        $this->deleteJson("/api/vehicles/{$vehicle->id}")->assertForbidden();
        $this->deleteJson("/api/parts/{$part->id}")->assertForbidden();
        $this->deleteJson("/api/suppliers/{$supplier->id}")->assertForbidden();

        $this->assertDatabaseHas('teams', ['id' => $team->id]);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id]);
        $this->assertDatabaseHas('parts', ['id' => $part->id]);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }

    #[DataProvider('nonAdminRoles')]
    public function test_a_non_admin_may_still_read(UserRole $role): void
    {
        $actor = User::factory()->role($role)->create();
        Team::factory()->create();
        Vehicle::factory()->create();

        $this->actingAs($actor, 'api');

        $this->getJson('/api/teams')->assertOk();
        $this->getJson('/api/vehicles')->assertOk();
        $this->getJson('/api/parts')->assertOk();
        $this->getJson('/api/suppliers')->assertOk();
        $this->getJson('/api/users')->assertOk();
    }

    public function test_an_admin_can_create_a_user_with_a_role(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin, 'api')
            ->postJson('/api/users', [
                'name' => 'New Engineer',
                'email' => 'engineer@example.com',
                'password' => 'password-long-enough',
                'role' => UserRole::Engineer->value,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'engineer@example.com',
            'role' => UserRole::Engineer->value,
        ]);
    }

    public function test_an_admin_can_create_a_part(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin, 'api')
            ->postJson('/api/parts', [
                'name' => 'Rear Wing',
                'part_number' => 'AER-RW-001',
                'category' => PartCategory::Aerodynamics->value,
                'manufacturer' => 'Apex Composites',
                'description' => 'Authorized create.',
            ])
            ->assertCreated();
    }
}
