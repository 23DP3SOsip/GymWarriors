<?php

namespace App\Http\Controllers;

use App\Models\PlanPurchase;
use App\Models\QrCode;
use App\Models\TokenTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    private const VALID_HOURS = 24;

    /**
     * Returns the QR code that is still valid (if there is one).
     */
    public function show(Request $request): JsonResponse
    {
        $purchase = PlanPurchase::where('user_id', $request->user()->id)
            ->whereIn('status', ['active', 'cancelled'])
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest('id')
            ->first();

        $qr = $purchase ? $this->validQr($purchase) : null;

        return response()->json([
            'qr_code' => $qr ? $this->format($qr) : null,
            'remaining_tokens' => $purchase?->remaining_tokens,
        ]);
    }

    /**
     * Generates a new QR code and removes one token.
     * If the user still has a valid QR code, that one is returned
     * and no token is removed.
     */
    public function store(Request $request): JsonResponse
    {
        $result = DB::transaction(function () use ($request) {
            $purchase = PlanPurchase::where('user_id', $request->user()->id)
                ->whereIn('status', ['active', 'cancelled'])
                ->whereDate('end_date', '>=', now()->toDateString())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $purchase) {
                return ['error' => 'You have no active plan.', 'status' => 403];
            }

            $existing = $this->validQr($purchase);

            if ($existing) {
                return ['qr' => $existing, 'purchase' => $purchase, 'status' => 200];
            }

            if ($purchase->remaining_tokens < 1) {
                return ['error' => 'You have no tokens left.', 'status' => 422];
            }

            $purchase->decrement('remaining_tokens');

            TokenTransaction::create([
                'purchase_id' => $purchase->id,
                'change' => -1,
                'reason' => 'qr_code',
                'checkin_id' => null,
                'date' => now(),
            ]);

            $qr = QrCode::create([
                'purchase_id' => $purchase->id,
                'code' => Str::random(40),
                'expires_at' => now()->addHours(self::VALID_HOURS),
            ]);

            return ['qr' => $qr, 'purchase' => $purchase, 'status' => 201];
        });

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['status']);
        }

        return response()->json([
            'qr_code' => $this->format($result['qr']),
            'remaining_tokens' => $result['purchase']->remaining_tokens,
        ], $result['status']);
    }

    private function validQr(PlanPurchase $purchase): ?QrCode
    {
        return QrCode::where('purchase_id', $purchase->id)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    private function format(QrCode $qr): array
    {
        return [
            'code' => $qr->code,
            'expires_at' => $qr->expires_at->toIso8601String(),
            'expires_in' => max(0, $qr->expires_at->timestamp - now()->timestamp),
        ];
    }
}