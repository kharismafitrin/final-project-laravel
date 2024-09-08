<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_registers_a_new_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'Success',
                'message' => 'Register Successfully',
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_register_with_invalid_data()
    {
        // Missing email and password confirmation
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'wrongconfirmation',
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function it_fails_to_register_with_missing_required_fields()
    {
        $response = $this->postJson('/api/register', [
            'name' => '', // Missing required field
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_logs_in_a_user()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'access_token',
                'expires_in',
            ]);
    }

    /** @test */
    public function it_fails_to_login_with_incorrect_credentials()
    {
        // Register user first
        $user = User::factory()->create([
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login with incorrect password
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'User not Registered',
            ]);
    }

    /** @test */
    public function it_returns_user_profile()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'Success',
                'message' => 'User profile retrieved successfully',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /** @test */
    public function it_fails_to_return_profile_without_authentication()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function it_logs_out_a_user()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'address' => '123 Main Street',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'massage' => 'User logged out successfully',
            ]);
    }

    /** @test */
    public function it_fails_to_log_out_without_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }



}
