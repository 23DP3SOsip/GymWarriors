<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\WorkoutTemplate;
use App\Support\PopularExerciseCatalog;
use Illuminate\Database\Seeder;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        PopularExerciseCatalog::seed();

        $templates = [
            [
                'name' => 'Full Body Strength',
                'type' => 'strength',
                'description' => 'A balanced full-body strength session.',
                'exercises' => [
                    ['name' => 'Barbell Squat', 'sets' => 3, 'reps' => 8, 'rest_seconds' => 120, 'sort_order' => 1],
                    ['name' => 'Bench Press', 'sets' => 3, 'reps' => 8, 'rest_seconds' => 120, 'sort_order' => 2],
                    ['name' => 'Lat Pulldown', 'sets' => 3, 'reps' => 10, 'rest_seconds' => 90, 'sort_order' => 3],
                    ['name' => 'Plank', 'sets' => 3, 'duration_seconds' => 45, 'rest_seconds' => 60, 'sort_order' => 4],
                ],
            ],
            [
                'name' => 'Cardio Base',
                'type' => 'cardio',
                'description' => 'A moderate steady-state cardio session.',
                'exercises' => [
                    ['name' => 'Treadmill Run', 'sets' => 1, 'duration_seconds' => 1800, 'rest_seconds' => 0, 'sort_order' => 1],
                    ['name' => 'Cycling', 'sets' => 1, 'duration_seconds' => 900, 'rest_seconds' => 0, 'sort_order' => 2],
                ],
            ],
        ];

        foreach ($templates as $templateData) {
            $template = WorkoutTemplate::updateOrCreate(
                ['user_id' => null, 'name' => $templateData['name']],
                ['type' => $templateData['type'], 'description' => $templateData['description']],
            );
            $template->exercises()->detach();

            foreach ($templateData['exercises'] as $exerciseData) {
                $exercise = Exercise::where('name', $exerciseData['name'])->firstOrFail();
                $template->exercises()->attach($exercise, collect($exerciseData)->except('name')->all());
            }
        }
    }
}
