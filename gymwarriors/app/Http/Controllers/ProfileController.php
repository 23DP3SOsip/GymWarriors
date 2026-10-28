<?php

namespace App\Http\Controllers;

use App\Models\PlanPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function cancelMembership(Request $request): JsonResponse
    {
        $purchase = PlanPurchase::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest('id')
            ->firstOrFail();

        $purchase->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Membership cancelled. Your access remains active until the end date.',
        ]);
    }
}
