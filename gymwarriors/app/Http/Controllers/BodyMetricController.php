<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BodyMetricController extends Controller
{
    private const METRIC_FIELDS = [
        'weight_kg',
        'chest_cm',
        'waist_cm',
        'arm_cm',
    ];

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'body_metrics' => $request->user()
                ->bodyMetrics()
                ->orderBy('entry_date')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entry_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'weight_kg' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'chest_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'waist_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'arm_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
        ]);

        if (! collect(self::METRIC_FIELDS)->contains(fn ($field) => filled($validated[$field] ?? null))) {
            return response()->json([
                'message' => 'Enter at least one body measurement.',
            ], 422);
        }

        $metric = $request->user()->bodyMetrics()->updateOrCreate(
            ['entry_date' => $validated['entry_date']],
            collect(self::METRIC_FIELDS)
                ->mapWithKeys(fn ($field) => [$field => $validated[$field] ?? null])
                ->all(),
        );

        return response()->json(['body_metric' => $metric], 201);
    }
}