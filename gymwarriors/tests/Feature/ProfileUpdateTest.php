<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanPurchase;
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

    public function test_authenticated_user_can_cancel_their_active_membership(): void
    {
        $user = $this->createUser('alex@example.com');
        $plan = Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);
        $purchase = PlanPurchase::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(29)->toDateString(),
            'allocated_tokens' => 30,
            'remaining_tokens' => 30,
            'purchase_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson('/api/profile/membership/cancel')
            ->assertOk()
            ->assertJsonPath('message', 'Membership cancelled. Your access remains active until the end date.');

        $this->assertDatabaseHas('plan_purchases', [
            'id' => $purchase->id,
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('membership.status', 'cancelled')
            ->assertJsonPath('membership.end_date', $purchase->end_date->jsonSerialize());

        $this->actingAs($user)
            ->postJson('/api/qr-code')
            ->assertCreated()
            ->assertJsonPath('remaining_tokens', 29);
    }

    public function test_guest_cannot_cancel_a_membership(): void
    {
        $this->postJson('/api/profile/membership/cancel')
            ->assertUnauthorized();
    }

    public function test_user_cannot_cancel_another_users_membership(): void
    {
        $user = $this->createUser('alex@example.com');
        $otherUser = $this->createUser('other@example.com');
        $plan = Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);
        $purchase = PlanPurchase::create([
            'user_id' => $otherUser->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(29)->toDateString(),
            'allocated_tokens' => 30,
            'remaining_tokens' => 30,
            'purchase_date' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->postJson('/api/profile/membership/cancel')
            ->assertNotFound();

        $this->assertDatabaseHas('plan_purchases', [
            'id' => $purchase->id,
            'status' => 'active',
        ]);
    }
}
