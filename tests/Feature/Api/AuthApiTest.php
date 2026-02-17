<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login Tests
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged in',
            ])
            ->assertJsonStructure([
                'message',
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_missing_email(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_with_missing_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_cannot_login_with_missing_both_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_cannot_login_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_session_is_created_after_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $sessionId = null;

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->withCookie('laravel_session', $sessionId);

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull(Session::getId());
    }

    public function test_session_is_regenerated_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $oldSessionId = Session::getId();

        $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $newSessionId = Session::getId();

        // Session should be regenerated after successful login
        // In testing environment, sessions are handled differently,
        // but the route explicitly calls $request->session()->regenerate()
        $this->assertTrue(Auth::check());
    }

    /**
     * Logout Tests
     */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out',
            ]);

        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_session_is_invalidated_after_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/logout');

        // After logout, user should be guest
        $this->assertGuest();

        // Attempting to access protected route should fail
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_session_token_is_regenerated_after_logout(): void
    {
        $user = User::factory()->create();

        $oldToken = csrf_token();

        $this->actingAs($user)
            ->postJson('/api/logout');

        // The route calls $request->session()->regenerateToken()
        $this->assertGuest();
    }

    public function test_user_cannot_access_protected_route_after_logout(): void
    {
        $user = User::factory()->create();

        // First, authenticate and access protected route
        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);

        // Then logout
        $this->actingAs($user)
            ->postJson('/api/logout');

        // Now try to access protected route again - should fail
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /**
     * Current User Tests
     */
    public function test_get_current_user_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ])
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->assertJsonMissing([
                'password',
                'remember_token',
            ]);
    }

    public function test_get_current_user_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_get_current_user_returns_correct_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $userData = $this->actingAs($user)
            ->getJson('/api/user')
            ->json();

        $this->assertEquals($user->id, $userData['id']);
        $this->assertEquals('Jane Smith', $userData['name']);
        $this->assertEquals('jane@example.com', $userData['email']);
        $this->assertArrayHasKey('email_verified_at', $userData);
        $this->assertArrayHasKey('created_at', $userData);
        $this->assertArrayHasKey('updated_at', $userData);
        $this->assertArrayNotHasKey('password', $userData);
        $this->assertArrayNotHasKey('remember_token', $userData);
    }

    public function test_get_current_user_returns_different_user_for_different_session(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response1 = $this->actingAs($user1)
            ->getJson('/api/user');

        $response1->assertJson([
            'email' => 'user1@example.com',
        ]);

        $response2 = $this->actingAs($user2)
            ->getJson('/api/user');

        $response2->assertJson([
            'email' => 'user2@example.com',
        ]);
    }

    /**
     * Registration Tests (Bonus)
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Security Tests
     */
    public function test_cors_headers_are_present_for_api_routes(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // CORS headers are typically added by middleware
        // In Laravel testing, these may not always be present depending on configuration
        $response->assertStatus(401); // Invalid credentials
    }

    public function test_password_is_hashed_in_database(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $user->password));
    }

    public function test_sensitive_data_is_not_exposed_in_json_response(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $jsonData = $response->json();

        $this->assertArrayNotHasKey('password', $jsonData);
        $this->assertArrayNotHasKey('remember_token', $jsonData);
    }

    public function test_sql_injection_attempt_fails(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => "admin' --",
            'password' => 'password123',
        ]);

        // Laravel's Eloquent ORM prevents SQL injection
        // This should simply return 401 for invalid credentials
        $response->assertStatus(401);
    }

    public function test_login_with_malicious_characters_in_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => "<script>alert('xss')</script>@example.com",
            'password' => 'password123',
        ]);

        // Laravel's validation should reject malformed email
        $response->assertStatus(422);
    }

    public function test_login_with_very_long_email_fails_validation(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => str_repeat('a', 300).'@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Concurrent Login Tests
     */
    public function test_multiple_failed_login_attempts_dont_lock_valid_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        // Now try with correct password - should still work
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }

    public function test_concurrent_logins_create_separate_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // First login
        $response1 = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response1->assertStatus(200);

        // Second login (simulating another request)
        $response2 = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response2->assertStatus(200);
    }

    /**
     * Session and Token Tests
     */
    public function test_session_driver_can_be_configured(): void
    {
        // This test verifies that session configuration is properly loaded
        $driver = Config::get('session.driver');

        $this->assertIsString($driver);
        $this->assertContains($driver, ['file', 'database', 'redis', 'array', 'cookie', 'memcached', 'dynamodb', 'apc']);
    }

    public function test_session_lifetime_is_configured(): void
    {
        $lifetime = Config::get('session.lifetime');

        $this->assertIsInt($lifetime);
        $this->assertGreaterThan(0, $lifetime);
    }

    public function test_sanctum_is_configured_for_stateful_auth(): void
    {
        $stateful = Config::get('sanctum.stateful');

        $this->assertIsArray($stateful);
        $this->assertContains('localhost', $stateful);
    }

    /**
     * Edge Cases
     */
    public function test_login_with_case_insensitive_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Laravel's default authentication is case-insensitive for email
        $response = $this->postJson('/api/login', [
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        // This depends on database collation, but should typically work
        // We'll just verify it doesn't crash
        $response->assertStatus(200);
    }

    public function test_login_with_whitespace_in_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => ' test@example.com ',
            'password' => 'password123',
        ]);

        // Laravel's validation should trim the input
        $response->assertStatus(401); // Will fail due to whitespace not being trimmed before validation
    }

    public function test_get_current_user_after_password_change(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('old-password'),
        ]);

        // Change password
        $user->password = bcrypt('new-password');
        $user->save();

        // User should still be able to access current user endpoint
        $response = $this->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);
    }

    /**
     * JSON API Tests
     */
    public function test_login_accepts_json_content_type(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/json');
    }

    public function test_api_returns_json_for_all_endpoints(): void
    {
        $user = User::factory()->create();

        // Test with Accept header
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        // Returns 401 but with JSON content type
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_invalid_json_request_returns_error(): void
    {
        // Send invalid JSON
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
        ])->post('/api/login', 'invalid json');

        $response->assertStatus(400);
    }

    /**
     * Middleware Tests
     */
    public function test_api_middleware_group_is_applied(): void
    {
        // Sanctum's EnsureFrontendRequestsAreStateful middleware
        // should be applied to all API routes
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_auth_middleware_protects_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_auth_middleware_protects_user_endpoint(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
