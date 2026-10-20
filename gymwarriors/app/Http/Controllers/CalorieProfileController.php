<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalorieProfileController extends Controller
{
    private const WORKOUTS = [
        'strength' => [
            'name' => 'Strength training',
            'carb_factor' => 0.10,
            'protein_factor' => 0.35,
            'duration' => '45–75 minutes',
            'hydration' => 'Drink 400–600 ml of water before training and 150–250 ml every 15–20 minutes.',
            'before' => '1–2 hours before: 25–35 g of protein and 30–60 g of carbohydrates, such as rice with chicken or oatmeal with yogurt.',
            'after' => 'Within 2 hours: 25–40 g of protein and 40–80 g of carbohydrates to support muscle recovery and glycogen replenishment.',
            'tips' => ['Warm up for 5–10 minutes.', 'Keep 1–3 repetitions in reserve for most sets.', 'Aim for 7–9 hours of sleep.'],
        ],
        'cardio' => [
            'name' => 'Cardio',
            'carb_factor' => 0.08,
            'protein_factor' => 0.25,
            'duration' => '30–60 minutes',
            'hydration' => 'Drink 400–600 ml of water before training; add electrolytes if the session lasts longer than one hour.',
            'before' => '1–2 hours before: 20–30 g of protein and 25–50 g of easy-to-digest carbohydrates, such as a banana with cottage cheese.',
            'after' => 'Within 1–2 hours: 20–30 g of protein, carbohydrates according to hunger, and a regular meal with vegetables.',
            'tips' => ['Start at a moderate pace.', 'Use an intensity that allows you to speak in short sentences.', 'Increase volume gradually, by no more than 10% per week.'],
        ],
        'hiit' => [
            'name' => 'HIIT / interval training',
            'carb_factor' => 0.12,
            'protein_factor' => 0.30,
            'duration' => '20–35 minutes',
            'hydration' => 'Drink 400–600 ml of water before training and take small sips during the session.',
            'before' => '60–120 minutes before: 25–35 g of protein and 30–60 g of carbohydrates. Avoid heavy and very fatty foods.',
            'after' => 'Within 2 hours: 25–35 g of protein and 40–70 g of carbohydrates, followed by a complete meal.',
            'tips' => ['Do an 8–10 minute dynamic warm-up.', 'Stop an interval if you feel dizzy or experience pain.', 'Allow at least 24 hours of recovery between hard HIIT sessions.'],
        ],
        'flexibility' => [
            'name' => 'Mobility / stretching',
            'carb_factor' => 0.04,
            'protein_factor' => 0.20,
            'duration' => '20–45 minutes',
            'hydration' => 'Drink water normally; no special carbohydrate loading is needed.',
            'before' => 'A regular meal 1–3 hours before is enough. If you are hungry, choose fruit or yogurt.',
            'after' => 'No special nutrition is required: follow your daily protein target and regular diet.',
            'tips' => ['Do not bounce while stretching.', 'Move until you feel tension, not pain.', 'Breathe calmly and hold each position for 20–40 seconds.'],
        ],
    ];

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'calorie_profile' => $request->user()->calorieProfile,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'height_cm' => ['required', 'integer', 'between:100,250'],
            'weight_kg' => ['required', 'numeric', 'between:30,300'],
            'age' => ['required', 'integer', 'between:13,100'],
            'sex' => ['required', 'in:male,female'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,active,very_active'],
            'goal' => ['required', 'in:lose,maintain,gain'],
        ]);

        $profile = $request->user()->calorieProfile()->updateOrCreate([], $validated);

        return response()->json(['calorie_profile' => $profile]);
    }

    public function workoutRecommendation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workout_type' => ['required', 'in:strength,cardio,hiit,flexibility'],
            'height_cm' => ['required', 'integer', 'between:100,250'],
            'weight_kg' => ['required', 'numeric', 'between:30,300'],
            'age' => ['required', 'integer', 'between:13,100'],
            'sex' => ['required', 'in:male,female'],
            'activity_level' => ['required', 'in:sedentary,light,moderate,active,very_active'],
            'goal' => ['required', 'in:lose,maintain,gain'],
        ]);

        $workout = self::WORKOUTS[$validated['workout_type']];
        $bmr = 10 * $validated['weight_kg']
            + 6.25 * $validated['height_cm']
            - 5 * $validated['age']
            + ($validated['sex'] === 'male' ? 5 : -161);
        $goalAdjustment = match ($validated['goal']) {
            'lose' => -500,
            'gain' => 300,
            default => 0,
        };
        $activityFactors = [
            'sedentary' => 1.2,
            'light' => 1.375,
            'moderate' => 1.55,
            'active' => 1.725,
            'very_active' => 1.9,
        ];
        $dailyCalories = max(
            $bmr * $activityFactors[$validated['activity_level']] + $goalAdjustment,
            $validated['sex'] === 'male' ? 1500 : 1200,
        );
        $protein = round($validated['weight_kg'] * ($validated['goal'] === 'lose' ? 2 : 1.8));

        return response()->json([
            'workout_type' => $validated['workout_type'],
            'workout_name' => $workout['name'],
            'duration' => $workout['duration'],
            'calories' => [
                'daily_target' => round($dailyCalories),
                'before' => round($dailyCalories * $workout['carb_factor']),
                'after' => round($dailyCalories * $workout['protein_factor']),
            ],
            'protein_target' => $protein,
            'hydration' => $workout['hydration'],
            'before' => $workout['before'],
            'after' => $workout['after'],
            'tips' => $workout['tips'],
            'notice' => 'These estimates are for guidance only and do not replace advice from a doctor or sports dietitian.',
        ]);
    }
}