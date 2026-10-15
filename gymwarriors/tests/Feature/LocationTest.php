<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
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

    public function test_authenticated_user_can_view_active_locations(): void
    {
        $user = $this->createUser();

        Location::create([
            'name' => 'Active Gym',
            'address' => 'Street 1',
            'city' => 'Riga',
            'active' => true,
        ]);

        Location::create([
            'name' => 'Inactive Gym',
            'address' => 'Street 2',
            'city' => 'Riga',
            'active' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/api/locations')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Active Gym'])
            ->assertJsonMissing(['name' => 'Inactive Gym']);
    }

    public function test_guest_cannot_view_locations(): void
    {
        $this->getJson('/api/locations')->assertUnauthorized();
    }

    public function test_purchase_rejects_inactive_location(): void
    {
        $user = $this->createUser();

        $plan = \App\Models\Plan::create([
            'name' => 'Parastais',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        $location = Location::create([
            'name' => 'Inactive Gym',
            'address' => 'Street 2',
            'city' => 'Riga',
            'active' => false,
        ]);

        $this->actingAs($user)
            ->postJson('/api/plans/purchase', [
                'plan_id' => $plan->id,
                'location_ids' => [$location->id],
            ])
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                'Viena vai vairākas izvēlētās lokācijas nav aktīvas.'
            );
    }
}
