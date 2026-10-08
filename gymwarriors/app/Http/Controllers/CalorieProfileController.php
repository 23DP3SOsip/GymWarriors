<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalorieProfileController extends Controller
{
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
}