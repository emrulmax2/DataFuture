<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Assign;
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
        try {
            $fromDate = Carbon::parse($request->query('date', now()->toDateString()))->toDateString();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid date format. Expected a valid date string.',
            ], 422);
        }

        $cacheKey = 'class_routine_student_' . $theUser->id . '_' . ($selectedStudentId ?: 'latest') . '_' . $fromDate;
        $data = Cache::remember($cacheKey, now()->addHours(1), function () use ($theUser, $selectedStudentId, $fromDate) {
            $studentQuery = Student::query()
                ->where('student_user_id', $theUser->id)
                ->orderBy('id', 'DESC');

            if (!empty($selectedStudentId)) {
                $studentQuery->where('id', $selectedStudentId);
            }

            $student = $studentQuery->first();
            if (!$student) {
                return [
                    'student_id' => null,
                    'from_date' => $fromDate,
                    'total_classes' => 0,
                    'classes' => [],
                    'date_wise' => (object)[],
                ];
            }

            $planIds = Assign::where('student_id', $student->id)
                ->pluck('plan_id')
                ->unique()
                ->values()
                ->toArray();

            if (empty($planIds)) {
                return [
                    'student_id' => $student->id,
                    'from_date' => $fromDate,
                    'total_classes' => 0,
                    'classes' => [],
                    'date_wise' => (object)[],
                ];
            }

            $rows = DB::table('plans_date_lists as datelist')
                ->select([
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
                    'user.name as tutor_name',
                ])
                ->leftJoin('plans as plan', 'datelist.plan_id', 'plan.id')
                ->leftJoin('courses as course', 'plan.course_id', 'course.id')
                ->leftJoin('module_creations as module', 'plan.module_creation_id', 'module.id')
                ->leftJoin('groups as group', 'plan.group_id', 'group.id')
                ->leftJoin('venues as venue', 'plan.venue_id', 'venue.id')
                ->leftJoin('rooms as room', 'plan.rooms_id', 'room.id')
                ->leftJoin('users as user', 'plan.tutor_id', 'user.id')
                ->whereIn('datelist.plan_id', $planIds)
                ->whereDate('datelist.date', '>=', $fromDate)
                ->orderBy('datelist.date')
                ->orderBy('plan.start_time')
                ->get();

            $formatted = $rows->map(function ($row) {
                $startTime = (!empty($row->start_time) ? date('h:i A', strtotime('1970-01-01 '.$row->start_time)) : null);
                $endTime = (!empty($row->end_time) ? date('h:i A', strtotime('1970-01-01 '.$row->end_time)) : null);
                $venue = trim(($row->venue_name ?? '') . (!empty($row->room_name) ? ', ' . $row->room_name : ''), ', ');

                return [
                    'plan_date_list_id' => $row->plan_date_list_id,
                    'plan_id' => $row->plan_id,
                    'plan_date' => $row->plan_date,
                    'hr_date' => date('F jS, Y', strtotime($row->plan_date)),
                    'course' => $row->course_name,
                    'module' => $row->module_name,
                    'classType' => (!empty($row->class_type) ? $row->class_type : $row->module_class_type),
                    'group' => $row->group_name,
                    'tutor' => $row->tutor_name,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'hr_time' => trim(($startTime ?? '') . (!empty($endTime) ? ' - ' . $endTime : '')),
                    'venue' => $row->venue_name,
                    'room' => $row->room_name,
                    'venue_room' => $venue,
                    'virtual_room' => $row->virtual_room,
                ];
            })->values();

            return [
                'student_id' => $student->id,
                'from_date' => $fromDate,
                'total_classes' => $formatted->count(),
                'classes' => $formatted,
                'date_wise' => $formatted->groupBy('plan_date')->toArray(),
            ];
        });
            
        return response()->json([
            'success' => true,
            'message' => 'Class routine data retrieved successfully.',
            'data' => $data,
        ], 200);
    }
    
}
