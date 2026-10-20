<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email): User
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('Password123!'),
        ]);
    }

    public function test_authenticated_user_can_view_workout_templates(): void
    {
        $user = $this->createUser('templates@example.com');
        $template = WorkoutTemplate::create([
            'name' => 'Test Strength',
            'type' => 'strength',
            'description' => 'Test workout',
        ]);
        $exercise = Exercise::create([
            'name' => 'Test Squat',
            'category' => 'strength',
            'muscle_group' => 'legs',
        ]);
        $template->exercises()->attach($exercise, ['sets' => 3, 'reps' => 8, 'sort_order' => 1]);

        $this->actingAs($user)
            ->getJson('/api/workout-templates')
            ->assertOk()
            ->assertJsonPath('workouts.0.exercises.0.name', 'Test Squat');
    }

    public function test_workout_is_saved_for_the_authenticated_user_with_exercises(): void
    {
        $user = $this->createUser('save@example.com');
        $exercise = Exercise::create([
            'name' => 'Test Push-up',
            'category' => 'strength',
            'muscle_group' => 'chest',
        ]);

        $this->actingAs($user)
            ->postJson('/api/workouts', [
                'name' => 'My Workout',
                'type' => 'strength',
                'started_at' => '2026-10-20 18:00:00',
                'completed_at' => '2026-10-20 19:00:00',
                'exercises' => [[
                    'exercise_id' => $exercise->id,
                    'sets_completed' => 3,
                    'reps_completed' => 12,
                    'weight_kg' => 0,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('workout.name', 'My Workout');

        $this->assertDatabaseHas('user_workouts', ['user_id' => $user->id, 'name' => 'My Workout']);
        $this->assertDatabaseHas('user_workout_exercises', ['exercise_id' => $exercise->id, 'sets_completed' => 3]);
    }

    public function test_user_only_sees_own_workout_history(): void
    {
        $user = $this->createUser('mine@example.com');
        $otherUser = $this->createUser('other@example.com');
        $user->workouts()->create([
            'name' => 'Mine',
            'type' => 'cardio',
            'started_at' => now(),
        ]);
        $otherUser->workouts()->create([
            'name' => 'Not mine',
            'type' => 'cardio',
            'started_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/api/workouts')
            ->assertOk()
            ->assertJsonCount(1, 'workouts')
            ->assertJsonPath('workouts.0.name', 'Mine');
    }
}
