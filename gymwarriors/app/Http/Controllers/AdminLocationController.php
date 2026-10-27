<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class AdminLocationController extends Controller
{
    public function index()
    {
        return response()->json([
            'locations' => Location::query()
                ->select(['id', 'name', 'address', 'city', 'active'])
                ->withCount(['purchaseLocations', 'checkins'])
                ->orderBy('city')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'active' => ['required', 'boolean'],
        ]);

        $location = Location::create($validated);

        return response()->json([
            'location' => $location,
        ], 201);
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'active' => ['required', 'boolean'],
        ]);

        $location->update($validated);

        return response()->json([
            'location' => $location->fresh(),
        ]);
    }

    public function destroy(Location $location)
    {
        $location->loadCount(['purchaseLocations', 'checkins']);

        if ($location->purchase_locations_count > 0 || $location->checkins_count > 0) {
            return response()->json([
                'message' => 'Šo lokāciju nevar dzēst, jo tā ir izmantota pirkumos vai apmeklējumos. Deaktivizē to, lai paslēptu no jaunajiem pirkumiem.',
            ], 409);
        }

        $location->delete();

        return response()->json([
            'message' => 'Lokācija veiksmīgi dzēsta.',
        ]);
    }
}
