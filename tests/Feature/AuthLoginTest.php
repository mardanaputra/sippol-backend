<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a test user
        User::create([
            'name' => 'Test Admin',
            'username' => 'admin_test',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function login_with_valid_credentials_returns_token()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin_test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'token',
                     'user' => [
                         'id', 'name', 'username', 'role'
                     ]
                 ]);
        
        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function login_with_invalid_credentials_returns_unauthorized()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin_test',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Username atau Password yang Anda masukkan salah.'
                 ]);
    }

    /** @test */
    public function login_validation_requires_username_and_password()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['username', 'password']);
    }

    /** @test */
    public function login_rate_limiting_blocks_after_three_failed_attempts()
    {
        $payload = [
            'username' => 'admin_test',
            'password' => 'wrongpassword',
        ];

        // 1st failed attempt
        $this->postJson('/api/login', $payload)->assertStatus(401);
        
        // 2nd failed attempt
        $this->postJson('/api/login', $payload)->assertStatus(401);
        
        // 3rd failed attempt
        $this->postJson('/api/login', $payload)->assertStatus(401);

        // 4th attempt should be blocked by rate limiter
        $response = $this->postJson('/api/login', $payload);
        
        $response->assertStatus(429)
                 ->assertJsonPath('success', false);
                 
        $this->assertStringContainsString('Terlalu banyak percobaan login', $response->json('message'));
    }
}
