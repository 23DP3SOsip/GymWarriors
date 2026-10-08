<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Models\Plan;
use App\Models\PlanPurchase;
use App\Models\Location;
use App\Models\PurchaseLocation;
use Carbon\Carbon;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/api/user', function (Request $request) {

    return response()->json([
        'id' => $request->user()->id,
        'first_name' => $request->user()->first_name,
        'last_name' => $request->user()->last_name,
        'email' => $request->user()->email,
        'phone' => $request->user()->phone,
    ]);

});


/*
|--------------------------------------------------------------------------
| PLANS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/api/plans', function () {

    return response()->json(
        Plan::orderBy('id')->get()
    );

});


/*
|--------------------------------------------------------------------------
| LOCATIONS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/api/locations', function () {

    return response()->json(
        Location::where('active', true)
            ->orderBy('id')
            ->get()
    );

});


/*
|--------------------------------------------------------------------------
| PLAN PURCHASE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->post('/api/plans/purchase', function (Request $request) {

    /*
    |--------------------------------------------------------------------------
    | VALIDĀCIJA
    |--------------------------------------------------------------------------
    */

    $validated = $request->validate([
        'plan_id' => 'required|integer|exists:plans,id',
        'location_ids' => 'nullable|array',
        'location_ids.*' => 'integer|exists:locations,id',
    ]);


    /*
    |--------------------------------------------------------------------------
    | IEGŪST PLĀNU
    |--------------------------------------------------------------------------
    */

    $plan = Plan::findOrFail($validated['plan_id']);


    /*
    |--------------------------------------------------------------------------
    | IEGŪST IZVĒLĒTĀS LOKĀCIJAS
    |--------------------------------------------------------------------------
    */

    $locationIds = $validated['location_ids'] ?? [];


    /*
    |--------------------------------------------------------------------------
    | PARASTAIS PLĀNS
    |--------------------------------------------------------------------------
    |
    | Parastajam plānam jāizvēlas 1 vai 2 lokācijas.
    |
    */

    if ($plan->max_locations !== null) {

        if (
            count($locationIds) < 1 ||
            count($locationIds) > $plan->max_locations
        ) {

            return response()->json([
                'message' => 'Parastajam plānam jāizvēlas 1 vai 2 lokācijas.',
            ], 422);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | PRO PLĀNS
    |--------------------------------------------------------------------------
    |
    | Pro plānam lokācijas nav jāizvēlas,
    | jo tas dod pieeju visām lokācijām.
    |
    */

    if ($plan->max_locations === null) {

        $locationIds = [];

    }


    /*
    |--------------------------------------------------------------------------
    | PĀRBAUDA LOKĀCIJAS
    |--------------------------------------------------------------------------
    */

    if (!empty($locationIds)) {

        $locationIds = array_unique($locationIds);


        $activeLocationsCount = Location::whereIn(
            'id',
            $locationIds
        )
            ->where('active', true)
            ->count();


        if ($activeLocationsCount !== count($locationIds)) {

            return response()->json([
                'message' => 'Viena vai vairākas izvēlētās lokācijas nav aktīvas.',
            ], 422);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | DATUMI
    |--------------------------------------------------------------------------
    |
    | Dalība ir 30 dienas:
    | sākuma datums + 29 dienas.
    |
    */

    $startDate = Carbon::today();

    $endDate = Carbon::today()->addDays(29);

/*
|--------------------------------------------------------------------------
| DEAKTIVIZĒ IEPRIEKŠĒJO AKTĪVO PLĀNU
|--------------------------------------------------------------------------
*/

PlanPurchase::where('user_id', $request->user()->id)
    ->where('status', 'active')
    ->update([
        'status' => 'deactivate',
    ]);

    /*
    |--------------------------------------------------------------------------
    | IZVEIDO PIRKUMU
    |--------------------------------------------------------------------------
    */

    $purchase = PlanPurchase::create([

        'user_id' => $request->user()->id,

        'plan_id' => $plan->id,

        'start_date' => $startDate,

        'end_date' => $endDate,

        'allocated_tokens' => 30,

        'remaining_tokens' => 30,

        'purchase_date' => now(),

        'status' => 'active',

    ]);


    /*
    |--------------------------------------------------------------------------
    | SAGLABĀ LOKĀCIJAS
    |--------------------------------------------------------------------------
    */

    foreach ($locationIds as $locationId) {

        PurchaseLocation::create([

            'purchase_id' => $purchase->id,

            'location_id' => $locationId,

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | ATBILDE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'message' => 'Plāns veiksmīgi iegādāts!',

        'purchase' => $purchase,

        'plan' => $plan,

    ], 201);

});


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/api/profile', function (Request $request) {

    /*
    |--------------------------------------------------------------------------
    | ATROD AKTĪVO PIRKUMU
    |--------------------------------------------------------------------------
    */

    $purchase = PlanPurchase::with('plan')
        ->where('user_id', $request->user()->id)
        ->where('status', 'active')
        ->whereDate(
            'end_date',
            '>=',
            now()->toDateString()
        )
        ->latest('id')
        ->first();


    /*
    |--------------------------------------------------------------------------
    | LOKĀCIJAS
    |--------------------------------------------------------------------------
    */

    $purchaseLocations = [];


    if ($purchase) {

        $purchaseLocations = PurchaseLocation::with('location')
            ->where('purchase_id', $purchase->id)
            ->get()
            ->map(function ($purchaseLocation) {

                return [

                    'id' => $purchaseLocation->location->id,

                    'name' => $purchaseLocation->location->name,

                    'address' => $purchaseLocation->location->address,

                    'city' => $purchaseLocation->location->city,

                ];

            })
            ->values()
            ->toArray();

    }


    /*
    |--------------------------------------------------------------------------
    | ATBILDE
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'user' => [

            'id' => $request->user()->id,

            'first_name' => $request->user()->first_name,

            'last_name' => $request->user()->last_name,

            'email' => $request->user()->email,

            'phone' => $request->user()->phone,

        ],


        'membership' => $purchase ? [

            'plan' => $purchase->plan->name,

            'price' => $purchase->plan->price,

            'start_date' => $purchase->start_date,

            'end_date' => $purchase->end_date,

            'remaining_tokens' => $purchase->remaining_tokens,

            'allocated_tokens' => $purchase->allocated_tokens,

            'status' => $purchase->status,

            'locations' => $purchaseLocations,

        ] : null,

    ]);

});


/*
|--------------------------------------------------------------------------
| VUE FALLBACK
|--------------------------------------------------------------------------
*/

Route::get('/{any?}', function () {

    return view('welcome');

})->where('any', '.*');