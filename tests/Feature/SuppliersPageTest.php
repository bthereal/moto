<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Part;
use App\Models\Supplier;
use App\Models\SupplierPart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SuppliersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/suppliers')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_suppliers_index(): void
    {
        $user = User::factory()->create();
        Supplier::factory(2)->create();

        $this->actingAs($user)
            ->get('/suppliers')
            ->assertOk()
            ->assertSeeLivewire('suppliers.index');
    }

    public function test_a_supplier_can_be_created_from_the_index_page(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        Volt::actingAs($user)
            ->test('suppliers.index')
            ->set('showCreateForm', true)
            ->set('name', 'Trackside Components Ltd')
            ->set('contact_email', 'sales@trackside.example')
            ->call('createSupplier')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'Trackside Components Ltd']);
    }

    public function test_authenticated_users_can_view_a_supplier(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($user)
            ->get("/suppliers/{$supplier->id}")
            ->assertOk()
            ->assertSee($supplier->name);
    }

    public function test_a_part_can_be_added_to_a_suppliers_list(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $part = Part::factory()->create();

        Volt::actingAs($user)
            ->test('suppliers.show', ['supplier' => $supplier])
            ->set('showAddForm', true)
            ->set('partId', $part->id)
            ->set('quantity', '10')
            ->set('price', '199.99')
            ->set('location', 'Birmingham, UK')
            ->set('delivery_cost', '15.00')
            ->call('addSupplierPart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('supplier_parts', [
            'supplier_id' => $supplier->id,
            'part_id' => $part->id,
            'quantity' => 10,
        ]);
    }

    public function test_a_supplier_cannot_list_the_same_part_twice(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $part = Part::factory()->create();
        SupplierPart::factory()->for($supplier)->for($part)->create();

        Volt::actingAs($user)
            ->test('suppliers.show', ['supplier' => $supplier])
            ->set('showAddForm', true)
            ->set('partId', $part->id)
            ->set('quantity', '10')
            ->set('price', '199.99')
            ->set('location', 'Birmingham, UK')
            ->set('delivery_cost', '15.00')
            ->call('addSupplierPart')
            ->assertHasErrors('partId');
    }

    public function test_a_listing_can_be_edited(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $supplierPart = SupplierPart::factory()->for($supplier)->create(['quantity' => 3]);

        Volt::actingAs($user)
            ->test('suppliers.show', ['supplier' => $supplier])
            ->call('editSupplierPart', $supplierPart)
            ->set('quantity', '25')
            ->set('price', '50.00')
            ->set('location', 'New Location')
            ->set('delivery_cost', '10.00')
            ->call('updateSupplierPart', $supplierPart)
            ->assertHasNoErrors();

        $this->assertSame(25, $supplierPart->fresh()->quantity);
    }

    public function test_a_listing_can_be_removed(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();
        $supplier = Supplier::factory()->create();
        $supplierPart = SupplierPart::factory()->for($supplier)->create();

        Volt::actingAs($user)
            ->test('suppliers.show', ['supplier' => $supplier])
            ->call('removeSupplierPart', $supplierPart);

        $this->assertDatabaseMissing('supplier_parts', ['id' => $supplierPart->id]);
    }
}
