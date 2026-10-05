<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The token endpoints handle credentials, so they carry a tighter limiter
 * ('login') than the group-wide 'api' limiter. Without these, password
 * brute-forcing against the API is unbounded.
 */
class ApiThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_repeated_failed_logins_are_throttled(): void
    {
        User::factory()->create(['email' => 'driver@example.com']);

        $attempt = fn () => $this->postJson('/api/tokens', [
            'email' => 'driver@example.com',
            'password' => 'wrong-password',
        ]);

        // The limiter allows 5 attempts per minute for an email/IP pair.
        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(422);
        }

        $attempt()->assertStatus(429);
    }

    public function test_the_throttle_does_not_block_a_different_account(): void
    {
        User::factory()->create(['email' => 'first@example.com']);
        User::factory()->create(['email' => 'second@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/tokens', [
                'email' => 'first@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422);
        }

        // Keyed per email, so a second account still gets its own budget.
        $this->postJson('/api/tokens', [
            'email' => 'second@example.com',
            'password' => 'password',
        ])->assertOk();
    }

    public function test_authenticated_api_routes_are_throttled(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull(
            RateLimiter::limiter('api'),
            "The 'api' named limiter must be defined, or throttleApi() silently fails to limit."
        );

        $this->actingAs($user, 'api')->getJson('/api/parts')->assertOk();
    }
}
