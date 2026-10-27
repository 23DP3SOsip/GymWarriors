<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,banned'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $users = User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'role',
                'is_banned',
                'created_at',
            ])
            ->withCount(['planPurchases', 'bodyMetrics', 'workouts'])
            ->when(isset($validated['search']), function ($query) use ($validated) {
                $search = $validated['search'];

                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(($validated['status'] ?? 'all') === 'active', fn ($query) => $query->where('is_banned', false))
            ->when(($validated['status'] ?? 'all') === 'banned', fn ($query) => $query->where('is_banned', true))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->json($users);
    }

    public function show(User $user)
    {
        $user->load([
            'calorieProfile',
            'planPurchases' => fn ($query) => $query
                ->with('plan:id,name,price')
                ->withCount('checkins')
                ->orderByDesc('purchase_date')
                ->limit(10),
            'bodyMetrics' => fn ($query) => $query
                ->orderByDesc('entry_date')
                ->limit(20),
            'workouts' => fn ($query) => $query
                ->with('exercises.exercise:id,name')
                ->orderByDesc('started_at')
                ->limit(20),
        ]);

        $payments = DB::table('payments')
            ->join('plan_purchases', 'payments.purchase_id', '=', 'plan_purchases.id')
            ->where('plan_purchases.user_id', $user->id)
            ->orderByDesc('payments.payment_date')
            ->limit(20)
            ->get([
                'payments.id',
                'payments.amount',
                'payments.payment_date',
                'payments.payment_method',
                'payments.status',
            ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'is_banned' => $user->is_banned,
                'banned_at' => $user->banned_at,
                'created_at' => $user->created_at,
            ],
            'calorie_profile' => $user->calorieProfile,
            'body_metrics' => $user->bodyMetrics,
            'workouts' => $user->workouts,
            'plan_purchases' => $user->planPurchases,
            'payments' => $payments,
        ]);
    }

    public function updateBan(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_banned' => ['required', 'boolean'],
        ]);

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'is_banned' => ['Administrator accounts cannot be banned here.'],
            ]);
        }

        $isBanned = (bool) $validated['is_banned'];

        $user->forceFill([
            'is_banned' => $isBanned,
            'banned_at' => $isBanned ? now() : null,
            'banned_by' => $isBanned ? $request->user()->id : null,
        ])->save();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'is_banned' => $user->is_banned,
                'banned_at' => $user->banned_at,
            ],
        ]);
    }
}
