<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $from = isset($validated['from']) ? Carbon::parse($validated['from'])->startOfDay() : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : null;

        $payments = Payment::query()
            ->join('plan_purchases', 'payments.purchase_id', '=', 'plan_purchases.id')
            ->join('plans', 'plan_purchases.plan_id', '=', 'plans.id')
            ->join('users', 'plan_purchases.user_id', '=', 'users.id')
            ->select([
                'payments.id',
                'payments.amount',
                'payments.payment_date',
                'payments.payment_method',
                'payments.status',
                'plans.name as plan_name',
                DB::raw("users.first_name || ' ' || users.last_name as user_name"),
            ]);

        $unrecordedPurchases = DB::table('plan_purchases')
            ->join('plans', 'plan_purchases.plan_id', '=', 'plans.id')
            ->join('users', 'plan_purchases.user_id', '=', 'users.id')
            ->leftJoin('payments', 'payments.purchase_id', '=', 'plan_purchases.id')
            ->whereNull('payments.id')
            ->select([
                'plan_purchases.id',
                'plans.price as amount',
                'plan_purchases.purchase_date as payment_date',
                DB::raw("'membership' as payment_method"),
                'plan_purchases.status',
                'plans.name as plan_name',
                DB::raw("users.first_name || ' ' || users.last_name as user_name"),
            ]);

        $checkins = Checkin::query()
            ->join('plan_purchases', 'checkins.purchase_id', '=', 'plan_purchases.id')
            ->join('users', 'plan_purchases.user_id', '=', 'users.id')
            ->join('locations', 'checkins.location_id', '=', 'locations.id')
            ->select([
                'checkins.id',
                'checkins.time',
                'checkins.token_removed',
                'locations.name as location_name',
                DB::raw("users.first_name || ' ' || users.last_name as user_name"),
            ]);

        if (!$isAdmin) {
            $payments->where('plan_purchases.user_id', $user->id);
            $unrecordedPurchases->where('plan_purchases.user_id', $user->id);
            $checkins->where('plan_purchases.user_id', $user->id);
        } elseif (isset($validated['user_id'])) {
            $payments->where('plan_purchases.user_id', $validated['user_id']);
            $unrecordedPurchases->where('plan_purchases.user_id', $validated['user_id']);
            $checkins->where('plan_purchases.user_id', $validated['user_id']);
        }

        if ($from) {
            $payments->where('payments.payment_date', '>=', $from);
            $unrecordedPurchases->where('plan_purchases.purchase_date', '>=', $from);
            $checkins->where('checkins.time', '>=', $from);
        }

        if ($to) {
            $payments->where('payments.payment_date', '<=', $to);
            $unrecordedPurchases->where('plan_purchases.purchase_date', '<=', $to);
            $checkins->where('checkins.time', '<=', $to);
        }

        if (isset($validated['location_id'])) {
            $checkins->where('checkins.location_id', $validated['location_id']);
            $payments->whereExists(function ($query) use ($validated) {
                $query->selectRaw('1')
                    ->from('purchase_locations')
                    ->whereColumn('purchase_locations.purchase_id', 'plan_purchases.id')
                    ->where('purchase_locations.location_id', $validated['location_id']);
            });
            $unrecordedPurchases->whereExists(function ($query) use ($validated) {
                $query->selectRaw('1')
                    ->from('purchase_locations')
                    ->whereColumn('purchase_locations.purchase_id', 'plan_purchases.id')
                    ->where('purchase_locations.location_id', $validated['location_id']);
            });
        }

        $paymentRows = $payments->orderByDesc('payments.payment_date')->get()
            ->concat($unrecordedPurchases->orderByDesc('plan_purchases.purchase_date')->get())
            ->sortByDesc('payment_date')
            ->values();
        $checkinRows = $checkins->orderByDesc('checkins.time')->get();

        return response()->json([
            'is_admin' => $isAdmin,
            'summary' => [
                'payments_count' => $paymentRows->count(),
                'payments_total' => (float) $paymentRows->sum('amount'),
                'visits_count' => $checkinRows->count(),
                'tokens_used' => $checkinRows->where('token_removed', true)->count(),
            ],
            'payments' => $paymentRows,
            'visits' => $checkinRows,
            'payment_trend' => $paymentRows
                ->groupBy(fn ($payment) => Carbon::parse($payment->payment_date)->toDateString())
                ->map(fn ($items, $date) => [
                    'date' => $date,
                    'value' => (float) $items->sum('amount'),
                ])
                ->values(),
            'visit_trend' => $checkinRows
                ->groupBy(fn ($checkin) => Carbon::parse($checkin->time)->toDateString())
                ->map(fn ($items, $date) => [
                    'date' => $date,
                    'value' => $items->count(),
                ])
                ->values(),
        ]);
    }
}
