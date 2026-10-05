<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TeamManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/teams')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_teams_index(): void
    {
        $user = User::factory()->create();
        Team::factory(2)->create();

        $this->actingAs($user)
            ->get('/teams')
            ->assertOk()
            ->assertSeeLivewire('teams.index');
    }

    public function test_a_team_can_be_created_from_the_index_page(): void
    {
        $user = User::factory()->role(UserRole::Admin)->create();

        Volt::actingAs($user)
            ->test('teams.index')
            ->set('showCreateForm', true)
            ->set('name', 'Apex Racing')
            ->set('full_name', 'Apex Racing Formula 1 Team')
            ->set('base', 'Milton Keynes, United Kingdom')
            ->set('principal', 'Jane Doe')
            ->set('founded_year', 2010)
            ->set('color', '#112233')
            ->call('createTeam')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', ['name' => 'Apex Racing']);
    }

    public function test_authenticated_users_can_view_a_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $this->actingAs($user)
            ->get("/teams/{$team->id}")
            ->assertOk()
            ->assertSee($team->name);
    }
}
