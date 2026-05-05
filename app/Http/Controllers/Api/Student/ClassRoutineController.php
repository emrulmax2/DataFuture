<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClassRoutineResource;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClassRoutineController extends Controller
{
    
    public function index(Request $request) 
    {

        $theUser = $request->user();
        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }

        $selectedStudentId = $request->query('selected_student_id', null);
        //return $selectedStudentId;
        try {
            $fromDate = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid date format. Expected a valid date string.',
            ], 422);
        }
        // Cache student_id separately — user→student mapping rarely changes
        $studentCacheKey = 'class_routine_student_id_' . $theUser->id . '_' . ($selectedStudentId ?: 'latest');
        $studentId = Cache::remember($studentCacheKey, now()->addHours(24), function () use ($theUser, $selectedStudentId) {
            if (!empty($selectedStudentId)) {
                return Student::where('id', $selectedStudentId)->value('id');
            }
            return Student::where('student_user_id', $theUser->id)
                ->orderBy('id', 'DESC')
                ->value('id');
        });

        if (!$studentId) {
            return response()->json([
                'success' => true,
                'message' => 'Class routine data retrieved successfully.',
                'data'    => (new ClassRoutineResource(['student_id' => null, 'from_date' => $fromDate, 'rows' => []]))->toArray($request),
            ], 200);
        }

        //Cache::flush(); // Clear cache to ensure fresh data for testing
        $cacheKey = 'class_routine_' . $studentId . '_' . $fromDate;
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($studentId, $fromDate) {
            $rows = DB::table('plans_date_lists as datelist')
                ->select([
                    'term.name as term_name',
                    'datelist.id as plan_date_list_id',
                    'datelist.date as plan_date',
                    'datelist.plan_id',
                    'plan.start_time',
                    'plan.end_time',
                    'plan.class_type',
                    'plan.virtual_room',
                    'course.name as course_name',
                    'module.module_name',
                    'module.class_type as module_class_type',
                    'group.name as group_name',
                    'venue.name as venue_name',
                    'room.name as room_name',
                    'emp.id as tutor_id',
                    'emp.photo as tutor_photo',
                    DB::raw("CONCAT(emp.first_name, ' ', emp.last_name) as tutor_name"),
                ])
                ->join('assigns', function ($join) use ($studentId) {
                    $join->on('assigns.plan_id', '=', 'datelist.plan_id')
                         ->where('assigns.student_id', '=', $studentId);
                })
                ->leftJoin('plans as plan', 'datelist.plan_id', '=', 'plan.id')
                ->leftJoin('term_declarations as term', 'plan.term_declaration_id', '=', 'term.id')
                ->leftJoin('courses as course', 'plan.course_id', '=', 'course.id')
                ->leftJoin('module_creations as module', 'plan.module_creation_id', '=', 'module.id')
                ->leftJoin('groups as group', 'plan.group_id', '=', 'group.id')
                ->leftJoin('venues as venue', 'plan.venue_id', '=', 'venue.id')
                ->leftJoin('rooms as room', 'plan.rooms_id', '=', 'room.id')
                ->leftJoin('users as user', 'plan.tutor_id', '=', 'user.id')
                ->leftJoin('employees as emp', 'user.id', '=', 'emp.user_id')
                ->whereDate('datelist.date', '=', $fromDate)
                ->orderBy('datelist.date')
                ->orderBy('plan.start_time')
                ->get();

            // Store as plain arrays — file cache serializes/deserializes these ~10x faster than Collection+stdClass
            return [
                'student_id' => $studentId,
                'from_date'  => $fromDate,
                'rows'       => array_map(fn ($r) => (array) $r, $rows->all()),
            ];
        });
            
        return response()->json([
            'success' => true,
            'message' => 'Class routine data retrieved successfully.',
            'data'    => (new ClassRoutineResource($data))->toArray($request),
        ], 200);
    }
    
}
