<?php

namespace App\Services\SlcRegistration;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentTask;
use App\Models\TaskList;
use Illuminate\Support\Facades\Cache;

/**
 * SLC REGISTERED (29) → SLC Enrolled (23) when a student's Year 1, Term 1 SLC attendance is recorded by Bulk
 * Attendance & Installment (user, 16 Nov 2026), done as the student page's Change Student Status dialog does it
 * (StudentController::studentUpdateStatus) —
 *   students.status_id, with the change in student_archives;
 *   a status history row (student_attendance_term_statuses) for the attendance's term, with the reason and the
 *     date and time;
 *   that term's class assignments' attendance indicator as the dialog defaults it: on for an active status (23 is);
 *   the tasks of a process attached to the status, as the dialog still creates them (23 has none).
 * The dialog's Task Automation "Student status changed" call is commented out there (and no such rules exist), so
 * none is made here either — the SLC module doesn't run the automation.
 */
class StudentEnrolment
{
    public const SLC_REGISTERED = 29;

    public const SLC_ENROLLED = 23;

    /** Would the student move? (their status is SLC REGISTERED) */
    public static function due(?int $statusId): bool
    {
        return (int) $statusId === self::SLC_REGISTERED;
    }

    public static function enrol(int $studentId, ?int $termDeclarationId, string $reason, int $userId): bool
    {
        $student = Student::find($studentId);
        $status = Status::find(self::SLC_ENROLLED);
        if (!$student || !$status || !self::due($student->status_id)) {
            return false;
        }
        $old = $student->status_id;
        $student->fill(['status_id' => $status->id])->save();
        StudentArchive::create(['student_id' => $student->id, 'table' => 'students', 'field_name' => 'status_id', 'field_value' => $old,
            'field_new_value' => $status->id, 'created_by' => $userId]);
        if ($status->process_list_id) {
            foreach (TaskList::where('process_list_id', $status->process_list_id)->orderBy('id')->get() as $task) {
                StudentTask::create(['student_id' => $student->id, 'task_list_id' => $task->id,
                    'external_link_ref' => $task->external_link_ref ?: null, 'status' => 'Pending', 'created_by' => $userId]);
            }
        }
        StudentAttendanceTermStatus::create([
            'student_id' => $student->id, 'term_declaration_id' => $termDeclarationId, 'status_id' => $status->id,
            'status_change_reason' => $reason, 'status_change_date' => now()->format('Y-m-d H:i:s'), 'created_by' => $userId,
        ]);
        if ($termDeclarationId) {
            $planIds = Plan::where('term_declaration_id', $termDeclarationId)->pluck('id')->unique()->all();
            if ($planIds) {
                Assign::whereIn('plan_id', $planIds)->where('student_id', $student->id)
                    ->update(['attendance' => (int) $status->active === 0 ? 0 : 1, 'updated_by' => $userId]);
                Cache::forget('plan_with_attendance_set_student_'.$student->id);
            }
        }

        return true;
    }
}
