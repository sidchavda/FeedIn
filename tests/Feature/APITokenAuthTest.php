<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class APITokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_token_returns_401_error()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Authorization token is required',
                    'error' => 'MISSING_TOKEN'
                ]);
    }

    public function test_invalid_token_returns_401_error()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here'
        ])->getJson('/api/profile');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                    'error' => 'INVALID_TOKEN'
                ]);
    }

    public function test_valid_token_returns_success()
    {
        // Create a user with a token
        $user = User::factory()->create([
            'api_token' => hash('sha256', 'valid_test_token')
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid_test_token'
        ])->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'mobile' => $user->mobile,
                            'is_admin' => $user->is_admin,
                        ]
                    ]
                ]);
    }

    public function test_token_without_bearer_prefix_works()
    {
        // Create a user with a token
        $user = User::factory()->create([
            'api_token' => hash('sha256', 'test_token_123')
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'test_token_123'
        ])->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_x_api_token_header_works()
    {
        // Create a user with a token
        $user = User::factory()->create([
            'api_token' => hash('sha256', 'x_api_test_token')
        ]);

        $response = $this->withHeaders([
            'X-API-Token' => 'x_api_test_token'
        ])->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }
} 