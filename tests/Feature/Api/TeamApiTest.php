<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_teams_api(): void
    {
        $this->getJson('/api/teams')->assertUnauthorized();
    }

    public function test_authenticated_users_can_list_teams(): void
    {
        $user = User::factory()->create();
        Team::factory(3)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/teams')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_a_team_can_be_created(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        $payload = [
            'name' => 'Apex Racing',
            'full_name' => 'Apex Racing Formula 1 Team',
            'base' => 'Milton Keynes, United Kingdom',
            'principal' => 'Jane Doe',
            'founded_year' => 2010,
            'color' => '#112233',
        ];

        $this->actingAs($user, 'api')
            ->postJson('/api/teams', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Apex Racing');

        $this->assertDatabaseHas('teams', ['name' => 'Apex Racing']);
    }

    public function test_a_team_requires_a_valid_color(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/teams', [
                'name' => 'Apex Racing',
                'full_name' => 'Apex Racing Formula 1 Team',
                'base' => 'Milton Keynes, United Kingdom',
                'principal' => 'Jane Doe',
                'founded_year' => 2010,
                'color' => 'not-a-color',
            ])
            ->assertJsonValidationErrors('color');
    }
}
