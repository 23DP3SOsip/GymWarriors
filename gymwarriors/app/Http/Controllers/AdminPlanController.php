<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::query()
            ->withCount('purchases')
            ->orderBy('id')
            ->get();

        $totalPurchases = $plans->sum('purchases_count');

        return response()->json([
            'total_purchases' => $totalPurchases,
            'plans' => $plans->map(fn (Plan $plan) => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'max_locations' => $plan->max_locations,
                'features' => $plan->features ?? [],
                'purchases_count' => $plan->purchases_count,
                'purchase_percentage' => $totalPurchases > 0
                    ? round(($plan->purchases_count / $totalPurchases) * 100, 1)
                    : 0,
            ])->values(),
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'max_locations' => ['nullable', 'integer', 'min:1', 'max:100'],
            'features' => ['required', 'array', 'min:1', 'max:10'],
            'features.*' => ['required', 'string', 'max:150'],
        ]);

        $plan->update($validated);

        return response()->json([
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => $plan->price,
                'max_locations' => $plan->max_locations,
                'features' => $plan->features ?? [],
            ],
        ]);
    }
}
