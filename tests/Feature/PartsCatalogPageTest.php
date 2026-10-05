<?php

namespace Tests\Feature;

use App\Enums\PartCategory;
use App\Enums\UserRole;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PartsCatalogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/parts')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_parts_catalog(): void
    {
        $user = User::factory()->create();
        Part::factory(2)->create();

        $this->actingAs($user)
            ->get('/parts')
            ->assertOk()
            ->assertSeeLivewire('parts.index');
    }

    public function test_a_part_can_be_created_from_the_catalog_page(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        Volt::actingAs($user)
            ->test('parts.index')
            ->set('showCreateForm', true)
            ->set('name', 'Front Wing Assembly')
            ->set('part_number', 'AER-FW-777')
            ->set('category', PartCategory::Aerodynamics->value)
            ->set('manufacturer', 'Apex Composites')
            ->call('createPart')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('parts', ['part_number' => 'AER-FW-777']);
    }
}
