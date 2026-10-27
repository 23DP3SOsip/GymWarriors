<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_plans_and_purchase_percentages(): void
    {
        $admin = $this->createUser('admin');
        $user = $this->createUser();
        $popularPlan = Plan::create([
            'name' => 'Popular',
            'price' => 19.99,
            'max_locations' => 2,
        ]);
        $otherPlan = Plan::create([
            'name' => 'Other',
            'price' => 29.99,
            'max_locations' => null,
        ]);

        $this->createPurchase($user, $popularPlan);
        $this->createPurchase($user, $popularPlan);
        $this->createPurchase($user, $otherPlan);

        $this->actingAs($admin)
            ->getJson('/api/admin/plans')
            ->assertOk()
            ->assertJsonPath('total_purchases', 3)
            ->assertJsonPath('plans.0.purchases_count', 2)
            ->assertJsonPath('plans.0.purchase_percentage', 66.7)
            ->assertJsonPath('plans.1.purchase_percentage', 33.3);
    }

    public function test_admin_can_edit_plan_name_description_price_and_location_limit(): void
    {
        $admin = $this->createUser('admin');
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/plans/{$plan->id}", [
                'name' => 'Warrior Plus',
                'description' => 'Access to all clubs with premium support.',
                'price' => 1234.56,
                'max_locations' => null,
                'features' => [
                    '60 DAYS MEMBERSHIP',
                    '50 TOKENS',
                    'ACCESS TO EVERY LOCATION',
                    'FREE GYM CHECK-IN',
                    'PRIORITY SUPPORT',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('plan.name', 'Warrior Plus')
            ->assertJsonPath('plan.description', 'Access to all clubs with premium support.')
            ->assertJsonPath('plan.price', '1234.56')
            ->assertJsonPath('plan.max_locations', null)
            ->assertJsonPath('plan.features.0', '60 DAYS MEMBERSHIP')
            ->assertJsonPath('plan.features.4', 'PRIORITY SUPPORT');

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Warrior Plus',
            'price' => 1234.56,
            'max_locations' => null,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/plans')
            ->assertOk()
            ->assertJsonPath('0.features.2', 'ACCESS TO EVERY LOCATION')
            ->assertJsonPath('0.features.4', 'PRIORITY SUPPORT');
    }

    public function test_only_admins_can_manage_plans(): void
    {
        $user = $this->createUser();
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $this->getJson('/api/admin/plans')->assertUnauthorized();

        $this->actingAs($user)
            ->getJson('/api/admin/plans')
            ->assertForbidden();

        $this->actingAs($user)
            ->putJson("/api/admin/plans/{$plan->id}", [
                'name' => 'Changed',
                'price' => 10,
                'max_locations' => 1,
            ])
            ->assertForbidden();
    }

    public function test_plan_updates_validate_all_public_plan_fields(): void
    {
        $admin = $this->createUser('admin');
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/admin/plans/{$plan->id}", [
                'name' => '',
                'price' => 0,
                'max_locations' => 0,
                'features' => ['', str_repeat('x', 151)],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'max_locations', 'features.0', 'features.1']);
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

    private function createPurchase(User $user, Plan $plan): void
    {
        PlanPurchase::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(29)->toDateString(),
            'allocated_tokens' => 30,
            'remaining_tokens' => 30,
            'purchase_date' => now(),
            'status' => 'active',
        ]);
    }
}
