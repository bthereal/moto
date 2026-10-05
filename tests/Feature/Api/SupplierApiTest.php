<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_suppliers_api(): void
    {
        $this->getJson('/api/suppliers')->assertUnauthorized();
    }

    public function test_authenticated_users_can_list_suppliers(): void
    {
        $user = User::factory()->create();
        Supplier::factory(3)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/suppliers')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_a_supplier_can_be_created(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/suppliers', [
                'name' => 'Trackside Components Ltd',
                'contact_email' => 'sales@trackside.example',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Trackside Components Ltd');

        $this->assertDatabaseHas('suppliers', ['name' => 'Trackside Components Ltd']);
    }

    public function test_a_supplier_requires_a_valid_email(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/suppliers', [
                'name' => 'Trackside Components Ltd',
                'contact_email' => 'not-an-email',
            ])
            ->assertJsonValidationErrors('contact_email');
    }

    public function test_a_supplier_can_be_deleted(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/suppliers/{$supplier->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
