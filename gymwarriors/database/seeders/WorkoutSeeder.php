<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\WorkoutTemplate;
use Illuminate\Database\Seeder;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            ['name' => 'Barbell Squat', 'category' => 'strength', 'muscle_group' => 'legs', 'description' => 'Compound lower-body movement.', 'equipment' => 'barbell'],
            ['name' => 'Bench Press', 'category' => 'strength', 'muscle_group' => 'chest', 'description' => 'Horizontal pressing movement.', 'equipment' => 'barbell'],
            ['name' => 'Lat Pulldown', 'category' => 'strength', 'muscle_group' => 'back', 'description' => 'Vertical pulling movement.', 'equipment' => 'machine'],
            ['name' => 'Romanian Deadlift', 'category' => 'strength', 'muscle_group' => 'hamstrings', 'description' => 'Hip-hinge movement for the posterior chain.', 'equipment' => 'barbell'],
            ['name' => 'Push-ups', 'category' => 'strength', 'muscle_group' => 'chest', 'description' => 'Bodyweight horizontal press.', 'equipment' => 'bodyweight'],
            ['name' => 'Plank', 'category' => 'strength', 'muscle_group' => 'core', 'description' => 'Isometric core exercise.', 'equipment' => 'bodyweight'],
            ['name' => 'Treadmill Run', 'category' => 'cardio', 'muscle_group' => 'full body', 'description' => 'Steady-state treadmill running.', 'equipment' => 'treadmill'],
            ['name' => 'Cycling', 'category' => 'cardio', 'muscle_group' => 'legs', 'description' => 'Low-impact cardiovascular exercise.', 'equipment' => 'bike'],
        ];

        foreach ($exercises as $data) {
            Exercise::updateOrCreate(['name' => $data['name']], $data);
        }

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
                ['name' => $templateData['name']],
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
