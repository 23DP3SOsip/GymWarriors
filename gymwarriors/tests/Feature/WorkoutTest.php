<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutTemplate;
use App\Support\PopularExerciseCatalog;
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

    public function test_exercise_picker_returns_one_hundred_popular_exercises(): void
    {
        $this->seed(\Database\Seeders\WorkoutSeeder::class);

        $this->actingAs($this->createUser('exercise-picker@example.com'))
            ->getJson('/api/exercises')
            ->assertOk()
            ->assertJsonCount(100, 'exercises')
            ->assertJsonFragment(['name' => 'Barbell Squat'])
            ->assertJsonFragment(['name' => 'Treadmill Run']);

        $this->assertCount(100, PopularExerciseCatalog::exercises());
    }

    public function test_user_can_create_a_private_workout_template(): void
    {
        $user = $this->createUser('custom-template@example.com');
        $otherUser = $this->createUser('other-template@example.com');
        $exercise = Exercise::create([
            'name' => 'Custom Bench Press',
            'category' => 'strength',
            'muscle_group' => 'chest',
        ]);

        $this->actingAs($user)
            ->postJson('/api/workout-templates', [
                'name' => 'My Chest Day',
                'type' => 'strength',
                'description' => 'Personal workout',
                'exercises' => [[
                    'exercise_id' => $exercise->id,
                    'sets' => 4,
                    'reps' => 10,
                    'rest_seconds' => 90,
                ]],
            ])
            ->assertCreated()
            ->assertJsonPath('workout.name', 'My Chest Day')
            ->assertJsonPath('workout.user_id', $user->id)
            ->assertJsonPath('workout.exercises.0.pivot.sets', 4);

        $this->actingAs($otherUser)
            ->getJson('/api/workout-templates')
            ->assertOk()
            ->assertJsonMissing(['name' => 'My Chest Day']);
    }

    public function test_user_cannot_log_another_users_private_workout_template(): void
    {
        $user = $this->createUser('workout-owner@example.com');
        $otherUser = $this->createUser('workout-visitor@example.com');
        $exercise = Exercise::create([
            'name' => 'Private Workout Exercise',
            'category' => 'strength',
            'muscle_group' => 'chest',
        ]);
        $template = WorkoutTemplate::create([
            'user_id' => $user->id,
            'name' => 'Private Workout',
            'type' => 'strength',
        ]);
        $template->exercises()->attach($exercise, ['sets' => 3, 'reps' => 8]);

        $this->actingAs($otherUser)
            ->postJson('/api/workouts', [
                'workout_template_id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
                'started_at' => '2026-10-20 18:00:00',
                'exercises' => [[
                    'exercise_id' => $exercise->id,
                    'sets_completed' => 3,
                    'reps_completed' => 8,
                ]],
            ])
            ->assertForbidden();
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
