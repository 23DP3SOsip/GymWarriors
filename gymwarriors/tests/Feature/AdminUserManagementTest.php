<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_search_and_view_users_without_exposing_passwords(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser();

        $this->actingAs($admin)
            ->getJson('/api/admin/users?search='.$user->email)
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.email', $user->email);

        $this->getJson("/api/admin/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('user.first_name', $user->first_name)
            ->assertJsonPath('user.is_banned', false)
            ->assertJsonMissingPath('user.password');
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->getJson('/api/admin/users')
            ->assertForbidden();

        $this->actingAs($user)
            ->patchJson("/api/admin/users/{$user->id}/ban", ['is_banned' => true])
            ->assertForbidden();
    }

    public function test_admin_can_ban_unban_and_banned_users_are_denied_access(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser();

        $this->actingAs($admin)
            ->patchJson("/api/admin/users/{$user->id}/ban", ['is_banned' => true])
            ->assertOk()
            ->assertJsonPath('user.is_banned', true);

        $this->assertNotNull($user->fresh()->banned_at);

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->actingAs($user->fresh())
            ->getJson('/api/profile')
            ->assertForbidden();

        $this->actingAs($admin)
            ->patchJson("/api/admin/users/{$user->id}/ban", ['is_banned' => false])
            ->assertOk()
            ->assertJsonPath('user.is_banned', false);

        $this->assertNull($user->fresh()->banned_at);
        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertOk();
    }

    public function test_admin_accounts_cannot_be_banned_and_status_is_required(): void
    {
        $admin = $this->createUser('admin');
        $otherAdmin = $this->createUser('admin');

        $this->actingAs($admin)
            ->patchJson("/api/admin/users/{$otherAdmin->id}/ban", ['is_banned' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_banned']);

        $this->patchJson("/api/admin/users/{$otherAdmin->id}/ban", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['is_banned']);
    }

    private function createUser(string $role = 'user'): User
    {
        $user = User::create([
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Password123!'),
        ]);

        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
