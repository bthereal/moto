<?php

namespace Tests\Feature\Api;

use App\Enums\PartCategory;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_parts_api(): void
    {
        $this->getJson('/api/parts')->assertUnauthorized();
    }

    public function test_authenticated_users_can_list_parts(): void
    {
        $user = User::factory()->create();
        Part::factory(3)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/parts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_a_part_can_be_created(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name' => 'Front Wing Assembly',
            'part_number' => 'AER-FW-999',
            'category' => PartCategory::Aerodynamics->value,
            'manufacturer' => 'Apex Composites',
            'description' => 'Test part.',
        ];

        $this->actingAs($user, 'api')
            ->postJson('/api/parts', $payload)
            ->assertCreated()
            ->assertJsonPath('data.part_number', 'AER-FW-999');

        $this->assertDatabaseHas('parts', ['part_number' => 'AER-FW-999']);
    }

    public function test_a_part_number_must_be_unique(): void
    {
        $user = User::factory()->create();
        Part::factory()->create(['part_number' => 'AER-FW-001']);

        $this->actingAs($user, 'api')
            ->postJson('/api/parts', [
                'name' => 'Duplicate Wing',
                'part_number' => 'AER-FW-001',
                'category' => PartCategory::Aerodynamics->value,
                'manufacturer' => 'Apex Composites',
            ])
            ->assertJsonValidationErrors('part_number');
    }
}
