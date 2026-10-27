<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\UserWorkout;
use App\Models\WorkoutTemplate;
use App\Support\PopularExerciseCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkoutController extends Controller
{
    public function templates(Request $request): JsonResponse
    {
        return response()->json([
            'workouts' => WorkoutTemplate::with('exercises')
                ->where(function ($query) use ($request) {
                    $query->whereNull('user_id')
                        ->orWhere('user_id', $request->user()->id);
                })
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function exercises(): JsonResponse
    {
        return response()->json([
            'exercises' => Exercise::query()
                ->whereIn('name', array_column(PopularExerciseCatalog::exercises(), 'name'))
                ->orderBy('name')
                ->get(['id', 'name', 'category', 'muscle_group']),
        ]);
    }

    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:strength,cardio,hiit,flexibility'],
            'description' => ['nullable', 'string', 'max:2000'],
            'exercises' => ['required', 'array', 'min:1', 'max:30'],
            'exercises.*.exercise_id' => ['required', 'integer', 'distinct', 'exists:exercises,id'],
            'exercises.*.sets' => ['required', 'integer', 'min:1', 'max:100'],
            'exercises.*.reps' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'exercises.*.duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
            'exercises.*.rest_seconds' => ['required', 'integer', 'min:0', 'max:3600'],
        ]);

        $template = DB::transaction(function () use ($request, $validated) {
            $template = WorkoutTemplate::create([
                'user_id' => $request->user()->id,
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['exercises'] as $index => $exercise) {
                $template->exercises()->attach($exercise['exercise_id'], [
                    'sets' => $exercise['sets'],
                    'reps' => $exercise['reps'] ?? null,
                    'duration_seconds' => $exercise['duration_seconds'] ?? null,
                    'rest_seconds' => $exercise['rest_seconds'],
                    'sort_order' => $index,
                ]);
            }

            return $template->load('exercises');
        });

        return response()->json(['workout' => $template], 201);
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

        if (!empty($validated['workout_template_id'])) {
            $template = WorkoutTemplate::findOrFail($validated['workout_template_id']);
            abort_unless(
                $template->user_id === null || (int) $template->user_id === (int) $request->user()->id,
                403,
            );
        }

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
