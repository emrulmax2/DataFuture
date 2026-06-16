<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

/**
 * Exposes the SMS course catalogue for external synchronisation (e.g. the
 * Operations app mirrors these into its interview "programmes" list).
 *
 * Protected by Passport client_credentials with the `sms.courses.read` scope.
 */
class CourseSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $courses = Course::query()
            ->select(['id', 'name', 'active'])
            ->orderBy('name')
            ->paginate($perPage);

        $courses->getCollection()->transform(function (Course $course) {
            return [
                'id'     => $course->id,
                'name'   => $course->name,
                'active' => (bool) ($course->active ?? true),
            ];
        });

        return response()->json([
            'data' => $courses->items(),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page'    => $courses->lastPage(),
                'per_page'     => $courses->perPage(),
                'total'        => $courses->total(),
            ],
        ]);
    }
}
