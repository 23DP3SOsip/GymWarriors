<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email): User
    {
        return User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('Password123!'),
        ]);
    }

    public function test_authenticated_user_can_update_personal_information(): void
    {
        $user = $this->createUser('alex@example.com');

        $this->actingAs($user)
            ->putJson('/api/profile', [
                'first_name' => 'Alekss',
                'last_name' => 'Bērziņš',
                'email' => 'aleks@example.com',
                'phone' => '+37120000000',
            ])
            ->assertOk()
            ->assertJsonPath('user.first_name', 'Alekss')
            ->assertJsonPath('user.last_name', 'Bērziņš')
            ->assertJsonPath('user.email', 'aleks@example.com')
            ->assertJsonPath('user.phone', '+37120000000');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Alekss',
            'last_name' => 'Bērziņš',
            'email' => 'aleks@example.com',
            'phone' => '+37120000000',
        ]);
    }

    public function test_profile_update_rejects_an_email_used_by_another_user(): void
    {
        $user = $this->createUser('alex@example.com');
        $this->createUser('taken@example.com');

        $this->actingAs($user)
            ->putJson('/api/profile', [
                'first_name' => 'Alex',
                'last_name' => 'User',
                'email' => 'taken@example.com',
                'phone' => null,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->putJson('/api/profile', [
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'phone' => null,
        ])->assertUnauthorized();
    }
}
