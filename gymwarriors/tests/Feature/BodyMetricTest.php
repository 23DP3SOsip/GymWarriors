<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BodyMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_only_receive_their_own_body_metrics(): void
    {
        $user = User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => 'password',
        ]);
        $otherUser = User::create([
            'first_name' => 'Sam',
            'last_name' => 'User',
            'email' => 'sam@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)->postJson('/api/body-metrics', [
            'entry_date' => '2026-10-01',
            'weight_kg' => 75.4,
        ])->assertCreated();

        $this->actingAs($user)
            ->getJson('/api/body-metrics')
            ->assertOk()
            ->assertJsonPath('body_metrics.0.weight_kg', '75.40');

        $this->actingAs($otherUser)
            ->getJson('/api/body-metrics')
            ->assertOk()
            ->assertExactJson(['body_metrics' => []]);
    }

    public function test_a_measurement_entry_requires_at_least_one_value(): void
    {
        $user = User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => 'alex@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->postJson('/api/body-metrics', [
                'entry_date' => '2026-10-01',
                'weight_kg' => null,
                'chest_cm' => null,
                'waist_cm' => null,
                'arm_cm' => null,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Enter at least one body measurement.');
    }
}