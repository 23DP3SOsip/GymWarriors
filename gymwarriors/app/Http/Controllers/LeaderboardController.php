<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rankedUsers = User::query()
            ->withCount('workouts')
            ->orderByDesc('workouts_count')
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name']);

        $currentUserIndex = $rankedUsers->search(
            fn (User $user): bool => $user->id === $request->user()->id,
        );

        abort_if($currentUserIndex === false, 404);

        return response()->json([
            'rank' => $currentUserIndex + 1,
            'workouts_count' => $rankedUsers[$currentUserIndex]->workouts_count,
            'total_users' => $rankedUsers->count(),
            'top_users' => $rankedUsers->take(3)
                ->values()
                ->map(fn (User $user, int $index): array => [
                    'rank' => $index + 1,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'workouts_count' => $user->workouts_count,
                ]),
        ]);
    }
}
