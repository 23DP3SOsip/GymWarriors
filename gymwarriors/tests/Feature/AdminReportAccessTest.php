<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        $user = User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => uniqid('report-test-', true).'@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_admin_can_view_reports_in_admin_panel(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->getJson('/api/admin/reports')
            ->assertOk()
            ->assertJsonPath('is_admin', true)
            ->assertJsonPath('summary.payments_count', 0)
            ->assertJsonPath('summary.visits_count', 0);
    }

    public function test_regular_user_cannot_view_admin_reports(): void
    {
        $user = $this->createUser('user');

        $this->actingAs($user)
            ->getJson('/api/admin/reports')
            ->assertForbidden();
    }

    public function test_guest_cannot_view_admin_reports(): void
    {
        $this->getJson('/api/admin/reports')
            ->assertUnauthorized();
    }

    public function test_all_time_report_includes_membership_purchases_and_period_filtering(): void
    {
        $admin = $this->createUser('admin');
        $member = $this->createUser('user');
        $plan = Plan::create([
            'name' => 'Standard',
            'price' => 19.99,
            'max_locations' => 2,
        ]);

        foreach ([
            now()->subMonths(3),
            now()->subDays(5),
        ] as $purchaseDate) {
            PlanPurchase::create([
                'user_id' => $member->id,
                'plan_id' => $plan->id,
                'start_date' => $purchaseDate->toDateString(),
                'end_date' => $purchaseDate->copy()->addDays(29)->toDateString(),
                'allocated_tokens' => 30,
                'remaining_tokens' => 30,
                'purchase_date' => $purchaseDate,
                'status' => 'active',
            ]);
        }

        $this->actingAs($admin)
            ->getJson('/api/admin/reports')
            ->assertOk()
            ->assertJsonPath('summary.payments_count', 2)
            ->assertJsonPath('summary.payments_total', 39.98);

        $this->actingAs($admin)
            ->getJson('/api/admin/reports?from='.now()->subDays(30)->toDateString().'&to='.now()->toDateString())
            ->assertOk()
            ->assertJsonPath('summary.payments_count', 1)
            ->assertJsonPath('summary.payments_total', 19.99);
    }
}
