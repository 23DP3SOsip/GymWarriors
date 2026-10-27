<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email): User
    {
        return User::create([
            'first_name' => ucfirst(strstr($email, '@', true)),
            'last_name' => 'Warrior',
            'email' => $email,
            'password' => bcrypt('Password123!'),
        ]);
    }

    private function logWorkouts(User $user, int $count): void
    {
        for ($index = 0; $index < $count; $index++) {
            $user->workouts()->create([
                'name' => "Workout {$index}",
                'type' => 'strength',
                'started_at' => now()->subMinutes($index),
                'completed_at' => now()->subMinutes($index),
            ]);
        }
    }

    public function test_authenticated_user_gets_their_rank_and_top_three_by_workouts(): void
    {
        $leader = $this->createUser('leader@example.com');
        $second = $this->createUser('second@example.com');
        $currentUser = $this->createUser('current@example.com');
        $fourth = $this->createUser('fourth@example.com');

        $this->logWorkouts($leader, 5);
        $this->logWorkouts($second, 3);
        $this->logWorkouts($currentUser, 2);
        $this->logWorkouts($fourth, 1);

        $this->actingAs($currentUser)
            ->getJson('/api/leaderboard')
            ->assertOk()
            ->assertJsonPath('rank', 3)
            ->assertJsonPath('workouts_count', 2)
            ->assertJsonPath('total_users', 4)
            ->assertJsonPath('top_users.0.first_name', 'Leader')
            ->assertJsonPath('top_users.0.workouts_count', 5)
            ->assertJsonPath('top_users.1.first_name', 'Second')
            ->assertJsonPath('top_users.2.first_name', 'Current');
    }

    public function test_guest_cannot_access_leaderboard(): void
    {
        $this->getJson('/api/leaderboard')->assertUnauthorized();
    }
}
