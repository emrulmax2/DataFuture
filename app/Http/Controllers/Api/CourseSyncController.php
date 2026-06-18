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
            ->select([
                'id',
                'name',
                'degree_offered',
                'pre_qualification',
                'awarding_body_id',
                'source_tuition_fee_id',
                'franchise_course',
                'active',
                'updated_at',
            ])
            ->orderBy('name')
            ->paginate($perPage);

        $courses->getCollection()->transform(function (Course $course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'degree_offered' => $course->degree_offered,
                'pre_qualification' => $course->pre_qualification,
                'awarding_body_id' => $course->awarding_body_id,
                'source_tuition_fee_id' => $course->source_tuition_fee_id,
                'franchise_course' => $course->franchise_course,
                'active' => (bool) ($course->active ?? true),
                'updated_at' => optional($course->updated_at)->toISOString(),
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
