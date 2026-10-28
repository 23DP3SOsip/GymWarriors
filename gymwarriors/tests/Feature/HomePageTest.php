<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_load_current_plans_and_public_homepage_statistics(): void
    {
        $user = User::create([
            'first_name' => 'Alex',
            'last_name' => 'Warrior',
            'email' => 'homepage@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $user->workouts()->create([
            'name' => 'Strength workout',
            'type' => 'strength',
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        Location::create([
            'name' => 'Active gym',
            'address' => 'Main Street 1',
            'city' => 'Riga',
            'active' => true,
        ]);
        Location::create([
            'name' => 'Inactive gym',
            'address' => 'Main Street 2',
            'city' => 'Riga',
            'active' => false,
        ]);

        Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
            'features' => ['Gym access', 'Workout tracking'],
        ]);
        Plan::create([
            'name' => 'Pro',
            'price' => 29.99,
            'max_locations' => null,
        ]);

        $this->getJson('/api/homepage')
            ->assertOk()
            ->assertJsonPath('stats.warriors', 1)
            ->assertJsonPath('stats.workouts', 1)
            ->assertJsonPath('stats.locations', 1)
            ->assertJsonPath('plans.0.name', 'Standard')
            ->assertJsonPath('plans.0.price', '19.99')
            ->assertJsonPath('plans.0.features.0', 'Gym access')
            ->assertJsonPath('plans.1.features.3', 'Unlimited locations');
    }

    public function test_authenticated_user_data_includes_role_for_homepage_navigation(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'homepage-admin@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('role', 'admin');
    }

    public function test_user_membership_indicator_includes_cancelled_membership_until_expiry(): void
    {
        $user = User::create([
            'first_name' => 'Member',
            'last_name' => 'User',
            'email' => 'homepage-member@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);
        PlanPurchase::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(24)->toDateString(),
            'allocated_tokens' => 30,
            'remaining_tokens' => 30,
            'purchase_date' => now()->subDays(5),
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('has_active_membership', true);
    }

    public function test_user_with_expired_membership_can_see_membership_button(): void
    {
        $user = User::create([
            'first_name' => 'Former',
            'last_name' => 'Member',
            'email' => 'homepage-former-member@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);
        PlanPurchase::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => now()->subDays(40)->toDateString(),
            'end_date' => now()->subDays(10)->toDateString(),
            'allocated_tokens' => 30,
            'remaining_tokens' => 0,
            'purchase_date' => now()->subDays(40),
            'status' => 'deactivate',
        ]);

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('has_active_membership', false);
    }
}
