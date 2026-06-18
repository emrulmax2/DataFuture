<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use Illuminate\Http\Request;

/**
 * Exposes SMS course modules for external synchronisation.
 *
 * Protected by Passport client_credentials with the `sms.course-modules.read` scope.
 */
class CourseModuleSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $modules = CourseModule::query()
            ->select([
                'id',
                'course_id',
                'module_level_id',
                'name',
                'code',
                'status',
                'credit_value',
                'unit_value',
                'class_type',
                'active',
                'updated_at',
            ])
            ->orderBy('course_id')
            ->orderBy('name')
            ->paginate($perPage);

        $modules->getCollection()->transform(function (CourseModule $module) {
            return [
                'id' => $module->id,
                'course_id' => $module->course_id,
                'module_level_id' => $module->module_level_id,
                'name' => $module->name,
                'code' => $module->code,
                'status' => $module->status,
                'credit_value' => $module->credit_value,
                'unit_value' => $module->unit_value,
                'class_type' => $module->class_type,
                'active' => (bool) ($module->active ?? true),
                'updated_at' => optional($module->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $modules->items(),
            'meta' => [
                'current_page' => $modules->currentPage(),
                'last_page' => $modules->lastPage(),
                'per_page' => $modules->perPage(),
                'total' => $modules->total(),
            ],
        ]);
    }
}
