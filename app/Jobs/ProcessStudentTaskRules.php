<?php

namespace App\Jobs;

use App\Models\Applicant;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentUser;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * The last step of the student conversion: Task Automation's "Student created"
 * rules (Site settings > Task Automation > Student). It runs after every other
 * job, so the course, funding and other details the rules test have already
 * been copied to the student. Replaces the old "Auto feed" process in
 * ProcessStudentTask (that process became rules).
 *
 * Fail-safe like the applicant listener: a rule problem is logged and never
 * stops a conversion that has otherwise finished.
 */
class ProcessStudentTaskRules implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $applicant;

    public function __construct(Applicant $applicant)
    {
        $this->applicant = $applicant;
    }

    public function handle(TaskAssigner $assigner)
    {
        // The same lookup every other conversion step uses (StudentConversion::studentFor: the
        // newest student of this applicant on its login), so the tasks land on the record the
        // rest of the data went to.
        $applicantUser = ApplicantUser::find($this->applicant->applicant_user_id);
        $user = $applicantUser ? StudentUser::where('email', $applicantUser->email)->first() : null;
        // $student = $user ? Student::where('student_user_id', $user->id)->first() : null;
        $student = \App\Services\Admission\StudentConversion::studentFor($this->applicant, $user);
        if (!$student) {
            $student = Student::where('applicant_id', $this->applicant->id)->orderByDesc('id')->first();
        }
        if (!$student) {
            Log::warning('Student task rules skipped: no student found for applicant #'.$this->applicant->id.'.');
            \App\Services\Conversion\ConversionErrorReport::report('Conversion: Assign Student Tasks', 'No student record was found for this applicant, so no tasks were assigned.', ['applicant_id' => $this->applicant->id]);

            return;
        }

        try {
            $assigner->applyStudent($student, TaskAssigner::TRIGGER_STUDENT_CREATED);
        } catch (\Throwable $e) {
            Log::error('Student task rules failed after conversion', ['applicant_id' => $this->applicant->id, 'student_id' => $student->id, 'error' => $e->getMessage()]);
            \App\Services\Conversion\ConversionErrorReport::report('Conversion: Assign Student Tasks', 'Task Automation rules could not run: '.$e->getMessage(), [
                'applicant_id' => $this->applicant->id, 'student_id' => $student->id, 'detail' => (string) $e,
            ]);
        }
    }
}
