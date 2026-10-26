<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 90);
        $days = in_array($days, [30, 90, 365], true) ? $days : 90;
        $from = now()->subDays($days - 1)->startOfDay();
        $user = $request->user();

        $metrics = $user->bodyMetrics()
            ->where('entry_date', '>=', $from->toDateString())
            ->orderBy('entry_date')
            ->get(['entry_date', 'weight_kg', 'chest_cm', 'waist_cm', 'arm_cm']);

        $workouts = $user->workouts()
            ->where('started_at', '>=', $from)
            ->selectRaw("date(started_at) as date, count(*) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $checkins = DB::table('checkins')
            ->join('plan_purchases', 'checkins.purchase_id', '=', 'plan_purchases.id')
            ->where('plan_purchases.user_id', $user->id)
            ->where('checkins.time', '>=', $from)
            ->selectRaw("date(checkins.time) as date, count(*) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $firstMetric = $metrics->first();
        $lastMetric = $metrics->last();

        return response()->json([
            'days' => $days,
            'metrics' => $metrics,
            'summary' => [
                'weight_change' => $this->difference($firstMetric?->weight_kg, $lastMetric?->weight_kg),
                'waist_change' => $this->difference($firstMetric?->waist_cm, $lastMetric?->waist_cm),
                'workouts_count' => $workouts->sum(),
                'checkins_count' => $checkins->sum(),
            ],
            'activity' => collect(range(0, $days - 1))
                ->map(function (int $offset) use ($from, $workouts, $checkins) {
                    $date = $from->copy()->addDays($offset)->toDateString();

                    return [
                        'date' => $date,
                        'workouts' => (int) ($workouts[$date] ?? 0),
                        'checkins' => (int) ($checkins[$date] ?? 0),
                    ];
                })
                ->values(),
        ]);
    }

    private function difference($first, $last): ?float
    {
        if ($first === null || $last === null) {
            return null;
        }

        return round((float) $last - (float) $first, 1);
    }
}
