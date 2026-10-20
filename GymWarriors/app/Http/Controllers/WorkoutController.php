<?php

namespace App\Http\Controllers;

use App\Models\UserWorkout;
use App\Models\WorkoutTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkoutController extends Controller
{
    public function templates(): JsonResponse
    {
        return response()->json([
            'workouts' => WorkoutTemplate::with('exercises')->orderBy('id')->get(),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        return response()->json([
            'workouts' => $request->user()->workouts()
                ->with('exercises.exercise')
                ->latest('started_at')
                ->limit(30)
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'workout_template_id' => ['nullable', 'integer', 'exists:workout_templates,id'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:strength,cardio,hiit,flexibility'],
            'started_at' => ['required', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'exercises' => ['required', 'array', 'min:1'],
            'exercises.*.exercise_id' => ['required', 'integer', 'exists:exercises,id'],
            'exercises.*.sets_completed' => ['required', 'integer', 'min:0', 'max:100'],
            'exercises.*.reps_completed' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'exercises.*.weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'exercises.*.duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'exercises.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        $workout = DB::transaction(function () use ($request, $validated) {
            $workout = $request->user()->workouts()->create(collect($validated)
                ->except('exercises')
                ->all());
            $workout->exercises()->createMany($validated['exercises']);

            return $workout->load('exercises.exercise');
        });

        return response()->json(['workout' => $workout], 201);
    }
}
