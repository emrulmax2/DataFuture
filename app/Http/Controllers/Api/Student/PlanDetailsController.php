<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanDetailsResource;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\PlanTask;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlanDetailsController extends Controller
{
    public function show(Request $request, Plan $plan)
    {
        $theUser = $request->user();
        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }

        //api call parameter for selected student id
        $selectedStudentId = $request->query('selected_student_id', null);

        $studentQuery = Student::where('student_user_id', $theUser->id);
        if (!empty($selectedStudentId)) {
            $studentId = (clone $studentQuery)->where('id', $selectedStudentId)->value('id');
        } else {
            $studentId = $studentQuery->orderBy('id', 'DESC')->value('id');
        }

        if (!$studentId) {
            return response()->json(['success' => false, 'error' => 'No student record found for this user.'], 404);
        }

        $isAssigned = Assign::where('plan_id', $plan->id)->where('student_id', $studentId)->exists();
        if (!$isAssigned) {
            return response()->json(['success' => false, 'error' => 'This module plan is not assigned to the student.'], 403);
        }

        // Cached below the S3 temporary URL lifetime (120 min) so links never expire while cached
        $cacheKey = 'plan_details_' . $plan->id . '_student_' . $studentId;
        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($plan, $studentId) {
            return new PlanDetailsResource($this->planDetails($plan, $studentId));
        });

        return response()->json([
            'success' => true,
            'message' => 'Plan details data retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    protected function planDetails(Plan $plan, $studentId)
    {
        $plan->load(['course', 'creations', 'group', 'room', 'venu', 'tutor', 'personalTutor', 'attenTerm']);

        $moduleCreation = $plan->creations;

        $tutor = isset($plan->tutor) ? Employee::where('user_id', $plan->tutor->id)->first() : null;
        $personalTutor = isset($plan->personalTutor) ? Employee::where('user_id', $plan->personalTutor->id)->first() : null;

        $moduleDetails = [
            'id' => $plan->id,
            'module' => isset($moduleCreation->module_name) ? $moduleCreation->module_name : '',
            'course' => isset($plan->course->name) ? $plan->course->name : '',
            'term_name' => (isset($moduleCreation->term->name) && !empty($moduleCreation->term->name)) ? $moduleCreation->term->name : (isset($plan->attenTerm->name) ? $plan->attenTerm->name : ''),
            'class_type' => ($plan->class_type != '') ? $plan->class_type : (isset($moduleCreation->class_type) ? $moduleCreation->class_type : ''),
            'group' => isset($plan->group->name) ? $plan->group->name : '',
            'venue' => isset($plan->venu->name) ? $plan->venu->name : '',
            'room' => isset($plan->room->name) ? $plan->room->name : '',
            'virtual_room' => $plan->virtual_room,
            'start_time' => (!empty($plan->start_time) ? date('h:i A', strtotime(date('Y-m-d ' . $plan->start_time))) : ''),
            'end_time' => (!empty($plan->end_time) ? date('h:i A', strtotime(date('Y-m-d ' . $plan->end_time))) : ''),
            'tutor' => ($tutor ? ['full_name' => $tutor->full_name, 'photo_url' => $tutor->photo_url] : null),
            'personal_tutor' => ($personalTutor ? ['full_name' => $personalTutor->full_name, 'photo_url' => $personalTutor->photo_url] : null),
        ];

        //Assignment Brief and Important Documents cards
        $courseContent = [];
        $planTasks = PlanTask::with(['uploads.createdBy.employee'])->where('plan_id', $plan->id)->get();
        foreach ($planTasks as $task) {

            $uploads = [];
            foreach ($task->uploads as $upload) {
                $uploads[] = [
                    'id' => $upload->id,
                    'file_name' => $upload->display_file_name,
                    'doc_type' => $upload->doc_type,
                    'uploaded_by' => (isset($upload->createdBy->employee) ? $upload->createdBy->employee->full_name : (isset($upload->createdBy->name) ? $upload->createdBy->name : '')),
                    'uploaded_at' => (!empty($upload->created_at) ? date('jS F, Y', strtotime($upload->created_at)) : ''),
                    'download_url' => $this->s3TemporaryUrl('public/plans/plan_task/' . $task->id . '/' . $upload->current_file_name),
                ];
            }

            $courseContent[] = [
                'id' => $task->id,
                'name' => $task->name,
                'category' => $task->category,
                'logo_url' => $this->taskLogoUrl($task),
                'upload_required_by' => (!empty($task->last_date) ? date('jS F, Y', strtotime($task->last_date)) : ''),
                'uploads' => $uploads,
            ];
        }

        //Class Dates tab
        $classDates = [];
        $planDates = PlansDateList::with('attendanceInformation')->where('plan_id', $plan->id)->orderBy('date', 'ASC')->get();
        foreach ($planDates as $dateList) {

            $theDay = date('Y-m-d', strtotime($dateList->date));

            $start_time = date($theDay . ' ' . $plan->start_time);
            $end_day = date($theDay . ' ' . $plan->end_time);

            if (strtotime(now()) > strtotime($end_day)) {
                $upcommingStatus = 'Unknown';
            } else {
                $upcommingStatus = 'Upcomming';
            }

            $myAttendance = Attendance::with('feed')
                ->where('plans_date_list_id', $dateList->id)
                ->where('student_id', $studentId)
                ->first();

            $classDates[] = [
                'id' => $dateList->id,
                'name' => (isset($plan->virtual_room) && !empty($plan->virtual_room) ? 'Virtual - ' : 'Physical - ') . $dateList->name,
                'date' => date('l jS M, Y', strtotime($dateList->date)),
                'raw_date' => $theDay,
                'time' => (!empty($plan->start_time) ? date('H:i', strtotime($plan->start_time)) : 'Unknown') . ' - ' . (!empty($plan->end_time) ? date('H:i', strtotime($plan->end_time)) : 'Unknown'),
                'start_time' => date('h:i A', strtotime($start_time)),
                'end_time' => date('h:i A', strtotime($end_day)),
                'end_date_time' => $end_day,
                'venue' => isset($plan->venu->name) ? $plan->venu->name : '',
                'room' => isset($plan->room->name) ? $plan->room->name : '',
                'virtual_room' => $plan->virtual_room,
                'upcomming_status' => $upcommingStatus,
                'attendance_information' => ($dateList->attendanceInformation) ?? null,
                'attendance_status' => (isset($myAttendance->feed->name) ? $myAttendance->feed->name : null),
            ];
        }

        return [
            'module_details' => $moduleDetails,
            'microsoft_teams_url' => 'https://teams.microsoft.com/v2/',
            'course_content' => $courseContent,
            'class_dates' => $classDates,
        ];
    }

    protected function taskLogoUrl($task)
    {
        // Signing is local — no exists() round trip to S3, it blocks the request when S3 is slow
        if ($task->logo !== null) {
            $url = $this->s3TemporaryUrl('public/activity/' . $task->logo);
            if ($url) {
                return $url;
            }
        }
        return asset('build/assets/images/placeholders/200x200.jpg');
    }

    protected function s3TemporaryUrl($path)
    {
        try {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(120));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
