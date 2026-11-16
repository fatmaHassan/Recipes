<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Registration — POST /api/register
    |--------------------------------------------------------------------------
    */

    public function test_api_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'API User',
            'email' => 'api@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJsonPath('user.email', 'api@example.com')
            ->assertJsonPath('user.name', 'API User');

        $this->assertDatabaseHas('users', [
            'name' => 'API User',
            'email' => 'api@example.com',
        ]);

        $user = User::whereEmail('api@example.com')->first();

        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertCount(1, $user->tokens);
    }

    public function test_api_register_rejects_duplicate_email(): void
    {
        $existing = User::factory()->create();

        $response = $this->postJson('/api/register', [
            'name' => 'Duplicate',
            'email' => $existing->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_api_register_requires_name_email_and_password(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_api_register_rejects_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Short Password',
            'email' => 'short@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_api_register_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Mismatch',
            'email' => 'mismatch@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /*
    |--------------------------------------------------------------------------
    | Login — POST /api/login
    |--------------------------------------------------------------------------
    */

    public function test_api_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJsonPath('user.email', $user->email);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    public function test_api_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('message', 'The provided credentials are incorrect.');

        $this->assertGuest();
    }

    public function test_api_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /*
    |--------------------------------------------------------------------------
    | Authenticated user — GET /api/user
    |--------------------------------------------------------------------------
    */

    public function test_api_user_returns_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_api_user_requires_a_token(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }

    /*
    |--------------------------------------------------------------------------
    | Logout — POST /api/logout
    |--------------------------------------------------------------------------
    */

    public function test_api_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertCount(0, $user->fresh()->tokens);
    }

    /**
     * A revoked token must not authenticate anymore.
     *
     * Gotcha: this is a separate test method on purpose. Within ONE test
     * method the app container is shared across requests, so Sanctum's
     * web-guard fallback can still resolve the user after an earlier
     * authenticated request — even with the token deleted. Each test
     * method boots a fresh app, so this check is reliable in isolation.
     */
    public function test_api_revoked_tokens_can_no_longer_authenticate(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');
        $plain = $token->plainTextToken;

        // Revoke the token BEFORE the request (same net effect as logout)
        $token->accessToken->delete();

        $this->withToken($plain)->getJson('/api/user')->assertUnauthorized();
    }
}
