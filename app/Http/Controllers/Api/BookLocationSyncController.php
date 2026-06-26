<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryLocation;
use Illuminate\Http\Request;

/**
 * Exposes the SMS/DataFuture book location catalogue for external sync.
 *
 * Protected by Passport client_credentials with the `sms.book-locations.read` scope.
 */
class BookLocationSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $locations = LibraryLocation::query()
            ->with('venue:id,name')
            ->select(['id', 'venue_id', 'name', 'description', 'status', 'updated_at'])
            ->orderBy('name')
            ->paginate($perPage);

        $locations->getCollection()->transform(function (LibraryLocation $location) {
            return [
                'id' => $location->id,
                'venue_id' => $location->venue_id,
                'venue_name' => $location->venue?->name,
                'name' => $location->name,
                'description' => $location->description,
                'status' => (bool) $location->status,
                'updated_at' => optional($location->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $locations->items(),
            'meta' => [
                'current_page' => $locations->currentPage(),
                'last_page' => $locations->lastPage(),
                'per_page' => $locations->perPage(),
                'total' => $locations->total(),
            ],
        ]);
    }
}
