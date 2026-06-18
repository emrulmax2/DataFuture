<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

/**
 * Exposes SMS HR departments for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.departments.read` scope.
 */
class HrDepartmentSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $departments = Department::query()
            ->select(['id', 'name', 'available_for_all', 'updated_at'])
            ->orderBy('name')
            ->paginate($perPage);

        $departments->getCollection()->transform(function (Department $department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'available_for_all' => (bool) ($department->available_for_all ?? false),
                'active' => true,
                'updated_at' => optional($department->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $departments->items(),
            'meta' => [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ],
        ]);
    }
}
