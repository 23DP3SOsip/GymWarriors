<?php

namespace Tests\Feature;

use App\Models\CalorieProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalorieProfileTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email = 'alex@example.com'): User
    {
        return User::create([
            'first_name' => 'Alex',
            'last_name' => 'User',
            'email' => $email,
            'password' => bcrypt('Password123!'),
        ]);
    }

    public function test_authenticated_user_can_save_calorie_profile(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/calorie-profile', [
                'height_cm' => 180,
                'weight_kg' => 75.5,
                'age' => 25,
                'sex' => 'male',
                'activity_level' => 'moderate',
                'goal' => 'maintain',
            ])
            ->assertOk()
            ->assertJsonPath('calorie_profile.height_cm', 180)
            ->assertJsonPath('calorie_profile.sex', 'male');

        $this->assertDatabaseHas('calorie_profiles', [
            'user_id' => $user->id,
            'height_cm' => 180,
        ]);
    }

    public function test_guest_cannot_save_calorie_profile(): void
    {
        $this->postJson('/api/calorie-profile', [])
            ->assertUnauthorized();
    }

    public function test_calorie_profile_requires_valid_data(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/calorie-profile', [
                'height_cm' => 50,
                'weight_kg' => -5,
                'age' => 200,
                'sex' => 'unknown',
                'activity_level' => 'unknown',
                'goal' => 'unknown',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'height_cm',
                'weight_kg',
                'age',
                'sex',
                'activity_level',
                'goal',
            ]);
    }

    public function test_user_can_view_own_calorie_profile(): void
    {
        $user = $this->createUser();

        $user->calorieProfile()->create([
            'height_cm' => 180,
            'weight_kg' => 75.5,
            'age' => 25,
            'sex' => 'male',
            'activity_level' => 'moderate',
            'goal' => 'maintain',
        ]);

        $this->actingAs($user)
            ->getJson('/api/calorie-profile')
            ->assertOk()
            ->assertJsonPath('calorie_profile.height_cm', 180);
    }

    public function test_user_without_profile_receives_null(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->getJson('/api/calorie-profile')
            ->assertOk()
            ->assertJsonPath('calorie_profile', null);
    }

    public function test_authenticated_user_receives_workout_specific_recommendations(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/workout-recommendations', [
                'workout_type' => 'strength',
                'height_cm' => 180,
                'weight_kg' => 75.5,
                'age' => 25,
                'sex' => 'male',
                'activity_level' => 'moderate',
                'goal' => 'maintain',
            ])
            ->assertOk()
            ->assertJsonPath('workout_type', 'strength')
            ->assertJsonPath('workout_name', 'Strength training')
            ->assertJsonStructure([
                'calories' => ['daily_target', 'before', 'after'],
                'protein_target',
                'before',
                'after',
                'hydration',
                'tips',
            ]);
    }

    public function test_workout_recommendations_require_a_supported_workout_type(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->postJson('/api/workout-recommendations', [
                'workout_type' => 'unknown',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'workout_type',
                'height_cm',
                'weight_kg',
                'age',
                'sex',
                'activity_level',
                'goal',
            ]);
    }
}
