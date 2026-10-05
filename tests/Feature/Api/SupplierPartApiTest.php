<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierPartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_list_a_suppliers_parts(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        SupplierPart::factory(2)->for($supplier)->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/suppliers/{$supplier->id}/parts")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_a_part_can_be_added_to_a_suppliers_list(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $part = Part::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/suppliers/{$supplier->id}/parts", [
                'part_id' => $part->id,
                'quantity' => 10,
                'price' => 199.99,
                'location' => 'Birmingham, UK',
                'delivery_cost' => 15.00,
            ])
            ->assertCreated()
            ->assertJsonPath('data.quantity', 10);

        $this->assertDatabaseHas('supplier_parts', [
            'supplier_id' => $supplier->id,
            'part_id' => $part->id,
        ]);
    }

    public function test_a_supplier_cannot_list_the_same_part_twice(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $part = Part::factory()->create();
        SupplierPart::factory()->for($supplier)->for($part)->create();

        $this->actingAs($user, 'api')
            ->postJson("/api/suppliers/{$supplier->id}/parts", [
                'part_id' => $part->id,
                'quantity' => 5,
                'price' => 50,
                'location' => 'Elsewhere',
                'delivery_cost' => 5,
            ])
            ->assertJsonValidationErrors('part_id');
    }

    public function test_a_listing_can_be_updated(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $supplierPart = SupplierPart::factory()->for($supplier)->create(['quantity' => 3]);

        $this->actingAs($user, 'api')
            ->patchJson("/api/suppliers/{$supplier->id}/parts/{$supplierPart->id}", ['quantity' => 20])
            ->assertOk()
            ->assertJsonPath('data.quantity', 20);
    }

    public function test_a_listing_from_another_supplier_is_not_accessible(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $otherSupplier = Supplier::factory()->create();
        $supplierPart = SupplierPart::factory()->for($otherSupplier)->create();

        $this->actingAs($user, 'api')
            ->patchJson("/api/suppliers/{$supplier->id}/parts/{$supplierPart->id}", ['quantity' => 1])
            ->assertNotFound();
    }

    public function test_a_listing_can_be_removed(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $supplierPart = SupplierPart::factory()->for($supplier)->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/suppliers/{$supplier->id}/parts/{$supplierPart->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('supplier_parts', ['id' => $supplierPart->id]);
    }
}
