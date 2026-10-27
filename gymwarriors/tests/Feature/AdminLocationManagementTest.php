<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\PurchaseLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_locations_with_usage_counts(): void
    {
        $admin = $this->createUser('admin');
        $location = $this->createLocation();
        $purchase = $this->createPurchase($this->createUser());

        PurchaseLocation::create([
            'purchase_id' => $purchase->id,
            'location_id' => $location->id,
        ]);
        DB::table('checkins')->insert([
            'purchase_id' => $purchase->id,
            'location_id' => $location->id,
            'time' => now(),
            'token_removed' => false,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/locations')
            ->assertOk()
            ->assertJsonPath('locations.0.name', $location->name)
            ->assertJsonPath('locations.0.purchase_locations_count', 1)
            ->assertJsonPath('locations.0.checkins_count', 1);
    }

    public function test_admin_can_create_and_update_locations(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)
            ->postJson('/api/admin/locations', [
                'name' => 'Central Gym',
                'address' => 'Main Street 1',
                'city' => 'Riga',
                'active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('location.name', 'Central Gym')
            ->assertJsonPath('location.active', true);

        $locationId = $response->json('location.id');

        $this->putJson("/api/admin/locations/{$locationId}", [
            'name' => 'Central Gym Updated',
            'address' => 'Main Street 2',
            'city' => 'Jurmala',
            'active' => false,
        ])
            ->assertOk()
            ->assertJsonPath('location.name', 'Central Gym Updated')
            ->assertJsonPath('location.address', 'Main Street 2')
            ->assertJsonPath('location.city', 'Jurmala')
            ->assertJsonPath('location.active', false);

        $this->assertDatabaseHas('locations', [
            'id' => $locationId,
            'name' => 'Central Gym Updated',
            'active' => false,
        ]);
    }

    public function test_admin_can_delete_unused_location(): void
    {
        $admin = $this->createUser('admin');
        $location = $this->createLocation();

        $this->actingAs($admin)
            ->deleteJson("/api/admin/locations/{$location->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Lokācija veiksmīgi dzēsta.');

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_admin_cannot_delete_location_used_in_purchase_or_checkin(): void
    {
        $admin = $this->createUser('admin');
        $location = $this->createLocation();
        $purchase = $this->createPurchase($this->createUser());

        PurchaseLocation::create([
            'purchase_id' => $purchase->id,
            'location_id' => $location->id,
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/locations/{$location->id}")
            ->assertStatus(409)
            ->assertJsonPath(
                'message',
                'Šo lokāciju nevar dzēst, jo tā ir izmantota pirkumos vai apmeklējumos. Deaktivizē to, lai paslēptu no jaunajiem pirkumiem.'
            );

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_only_admins_can_manage_locations(): void
    {
        $user = $this->createUser();
        $location = $this->createLocation();

        $this->getJson('/api/admin/locations')->assertUnauthorized();

        $this->actingAs($user)
            ->getJson('/api/admin/locations')
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson('/api/admin/locations', [
                'name' => 'Central Gym',
                'address' => 'Main Street 1',
                'city' => 'Riga',
                'active' => true,
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->deleteJson("/api/admin/locations/{$location->id}")
            ->assertForbidden();
    }

    public function test_location_data_is_validated(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->postJson('/api/admin/locations', [
                'name' => '',
                'address' => str_repeat('x', 256),
                'city' => '',
                'active' => 'not-a-boolean',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address', 'city', 'active']);
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

    private function createLocation(): Location
    {
        return Location::create([
            'name' => 'Central Gym',
            'address' => 'Main Street 1',
            'city' => 'Riga',
            'active' => true,
        ]);
    }

    private function createPurchase(User $user): PlanPurchase
    {
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        return PlanPurchase::create([
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
