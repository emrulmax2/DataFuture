<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $users = User::query()
            ->select(['id', 'name', 'email', 'active', 'social_id','social_type', 'photo'])
            ->with([
                'roles',
                'employee.employment.employeeJobTitle',
                'employee.employment.department',
                'employee.lineManagers.employee.employment.department',
            ])
            ->orderBy('id')
            ->paginate($perPage);

        $users->getCollection()->transform(function (User $user) {
            $employee = $user->employee;
            $employment = $employee?->employment;
            $departmentId = $employment?->department_id;
            $managedDepartmentKeys = collect($employee?->lineManagers ?? [])
                ->map(function ($lineManager) {
                    return $lineManager->employee?->employment?->department_id;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            return [
                'email' => $user->email,
                'name' => $user->full_name,
                'position' => $employment?->employeeJobTitle?->name,
                'active' => (bool) $user->active,
                'social_id' => $user->social_id ?? null,
                'social_type' => $user->social_type ?? null,
                'avatar_url' => $user->photo_url,
                'department_key' => $departmentId,
                'department_keys' => !empty($departmentId) ? [$departmentId] : [],
                'is_manager' => !empty($managedDepartmentKeys),
                'manager_department_keys' => $managedDepartmentKeys,
                'department_roles' => $user->roles->pluck('display_name')->filter()->values()->all(),
            ];
        });

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }
}