<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

/**
 * Exposes SMS venue rooms for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.rooms.read` scope.
 */
class RoomSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $rooms = Room::query()
            ->select(['id', 'venue_id', 'name', 'room_capacity', 'updated_at'])
            ->orderBy('venue_id')
            ->orderBy('name')
            ->paginate($perPage);

        $rooms->getCollection()->transform(function (Room $room) {
            return [
                'id' => $room->id,
                'venue_id' => $room->venue_id,
                'name' => $room->name,
                'room_capacity' => (int) $room->room_capacity,
                'active' => true,
                'updated_at' => optional($room->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $rooms->items(),
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ],
        ]);
    }
}
