<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/register', [
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Reģistrācija veiksmīga!')
            ->assertJsonPath('user.email', 'alex@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'alex@example.com',
        ]);
    }

    public function test_registration_requires_valid_data(): void
    {
        $this->postJson('/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'password',
            ]);
    }

    public function test_user_can_log_in(): void
    {
        User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->postJson('/login', [
            'email' => 'alex@example.com',
            'password' => 'Password123!',
        ])->assertOk()
            ->assertJsonPath('user.email', 'alex@example.com');
    }

    public function test_invalid_password_is_rejected(): void
    {
        User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $this->postJson('/login', [
            'email' => 'alex@example.com',
            'password' => 'WrongPassword123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $this->getJson('/api/profile')
            ->assertUnauthorized();
    }
}