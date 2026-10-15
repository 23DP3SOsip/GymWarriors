<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => bcrypt('Password123!'),
        ]);
    }

    public function test_authenticated_user_can_view_plans(): void
    {
        $user = $this->createUser();

        Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $this->actingAs($user)
            ->getJson('/api/plans')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Parastais']);
    }

    public function test_guest_cannot_view_plans(): void
    {
        $this->getJson('/api/plans')->assertUnauthorized();
    }

    public function test_user_can_purchase_a_plan(): void
    {
        $user = $this->createUser();

        $plan = Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $location = Location::create([
            'name' => 'Test Gym',
            'address' => 'Test Street 1',
            'city' => 'Riga',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->postJson('/api/plans/purchase', [
                'plan_id' => $plan->id,
                'location_ids' => [$location->id],
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Plāns veiksmīgi iegādāts!');

        $this->assertDatabaseHas('plan_purchases', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'allocated_tokens' => 30,
            'remaining_tokens' => 30,
            'status' => 'active',
        ]);
    }

    public function test_purchase_requires_authentication(): void
    {
        $this->postJson('/api/plans/purchase', [
            'plan_id' => 1,
            'location_ids' => [1],
        ])->assertUnauthorized();
    }

    public function test_purchase_rejects_nonexistent_plan(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/plans/purchase', [
                'plan_id' => 9999,
                'location_ids' => [1],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['plan_id']);
    }
}
