<?php

namespace App\Support;

use App\Models\Exercise;

class PopularExerciseCatalog
{
    public static function exercises(): array
    {
        $groups = [
            'chest' => [
                'Barbell Bench Press', 'Bench Press', 'Incline Barbell Bench Press',
                'Decline Barbell Bench Press', 'Dumbbell Bench Press', 'Incline Dumbbell Press',
                'Decline Dumbbell Press', 'Dumbbell Fly', 'Cable Crossover', 'Pec Deck Fly',
                'Push-ups', 'Chest Dip', 'Machine Chest Press', 'Smith Machine Bench Press',
                'Dumbbell Pullover',
            ],
            'back' => [
                'Deadlift', 'Romanian Deadlift', 'Barbell Row', 'Pendlay Row',
                'One-Arm Dumbbell Row', 'Seated Cable Row', 'Lat Pulldown', 'Pull-ups',
                'Chin-ups', 'T-Bar Row', 'Chest-Supported Row', 'Straight-Arm Pulldown',
                'Face Pull', 'Back Extension', 'Rack Pull',
            ],
            'legs' => [
                'Barbell Squat', 'Front Squat', 'Goblet Squat', 'Bulgarian Split Squat',
                'Walking Lunges', 'Reverse Lunges', 'Leg Press', 'Hack Squat',
                'Leg Extension', 'Lying Leg Curl', 'Seated Leg Curl', 'Hip Thrust',
                'Glute Bridge', 'Standing Calf Raise', 'Seated Calf Raise', 'Step-ups',
                'Sumo Deadlift', 'Good Morning', 'Nordic Hamstring Curl', 'Cable Pull-Through',
            ],
            'shoulders' => [
                'Overhead Barbell Press', 'Dumbbell Shoulder Press', 'Arnold Press',
                'Lateral Raise', 'Cable Lateral Raise', 'Front Raise', 'Rear Delt Fly',
                'Upright Row', 'Dumbbell Shrugs', 'Machine Shoulder Press',
            ],
            'arms' => [
                'Barbell Curl', 'EZ-Bar Curl', 'Dumbbell Curl', 'Hammer Curl',
                'Preacher Curl', 'Cable Curl', 'Triceps Pushdown', 'Overhead Triceps Extension',
                'Skull Crushers', 'Close-Grip Bench Press', 'Triceps Dip',
                'Cable Overhead Triceps Extension', 'Reverse Curl', 'Wrist Curl', 'Zottman Curl',
            ],
            'core' => [
                'Plank', 'Side Plank', 'Crunch', 'Bicycle Crunch', 'Hanging Leg Raise',
                "Captain's Chair Leg Raise", 'Cable Crunch', 'Ab Wheel Rollout',
                'Russian Twist', 'Dead Bug',
            ],
            'full body' => [
                'Treadmill Run', 'Cycling', 'Rowing Machine', 'Elliptical',
                'Stair Climber', 'Jump Rope', 'Mountain Climbers', 'Burpees',
                'Battle Ropes', 'High Knees', 'Box Jump', 'Sled Push', 'Walking',
                'Swimming', 'Assault Bike',
            ],
        ];

        $exercises = [];
        foreach ($groups as $muscleGroup => $names) {
            foreach ($names as $name) {
                $exercises[] = [
                    'name' => $name,
                    'category' => $muscleGroup === 'full body' ? 'cardio' : 'strength',
                    'muscle_group' => $muscleGroup,
                ];
            }
        }

        return $exercises;
    }

    public static function seed(): void
    {
        foreach (self::exercises() as $exercise) {
            Exercise::updateOrCreate(['name' => $exercise['name']], $exercise);
        }
    }
}
