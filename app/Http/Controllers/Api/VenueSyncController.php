<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;

/**
 * Exposes the SMS venue catalogue for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.venues.read` scope.
 */
class VenueSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $venues = Venue::query()
            ->select(['id', 'name', 'active', 'idnumber', 'ukprn', 'address', 'postcode', 'updated_at'])
            ->orderBy('name')
            ->paginate($perPage);

        $venues->getCollection()->transform(function (Venue $venue) {
            return [
                'id' => $venue->id,
                'name' => $venue->name,
                'active' => (bool) ($venue->active ?? true),
                'idnumber' => $venue->idnumber,
                'ukprn' => $venue->ukprn,
                'address' => $venue->address,
                'postcode' => $venue->postcode,
                'updated_at' => optional($venue->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $venues->items(),
            'meta' => [
                'current_page' => $venues->currentPage(),
                'last_page' => $venues->lastPage(),
                'per_page' => $venues->perPage(),
                'total' => $venues->total(),
            ],
        ]);
    }
}
