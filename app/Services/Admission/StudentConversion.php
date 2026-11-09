<?php

namespace App\Services\Admission;

use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\ApplicantFeeEligibility;
use App\Models\ApplicantProofOfId;
use App\Models\ApplicantProposedCourse;
use App\Models\StudentConversionLog;
use Closure;
use Illuminate\Bus\Batch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

/**
 * Offer Accepted → student: the work AdmissionController::admissionStudentUpdateStatus
 * did inline for status 7, shared with the DocuSeal signing workflow
 * (App\Services\DocuSeal\OfferAcceptanceWorkflow) so both convert the same way.
 * The caller has already saved status 7 and its archive row.
 */
class StudentConversion
{
    /**
     * The student a conversion step fills: the newest student carrying this applicant's id on
     * the applicant's student login - the one ProcessStudents just created. Every step used to
     * take the login's *first* student, so converting the same applicant again (or anything
     * else already on that login) filled the older record and left the new one empty, and its
     * profile page failed (26 Oct 2026). Falls back to that first student when none carries
     * the applicant id, which is all the old lookup ever returned.
     */
    public static function studentFor(Applicant $applicant, $studentUser): ?\App\Models\Student
    {
        if (!$studentUser) {
            return null;
        }
        $mine = \App\Models\Student::where('student_user_id', $studentUser->id)->where('applicant_id', $applicant->id)->orderByDesc('id')->first();

        return $mine ?: \App\Models\Student::where('student_user_id', $studentUser->id)->first();
    }

    /**
     * @param  array|null  $proof  proof_type, proof_id, proof_expiredate, fee_eligibility_id from the
     *                             Change status dialog (written exactly as before); null keeps what is on record
     * @param  Closure|null  $then   batch callback when every job finished (serialised with the batch)
     * @param  Closure|null  $catch  batch callback on the first failed job
     */
    public function start(Applicant $applicant, int $actorId, ?array $proof = null, ?Closure $then = null, ?Closure $catch = null): Batch
    {
        $applicantId = (int) $applicant->id;

        if ($proof !== null) {
            $existingProofId = (isset($applicant->proof->id) && $applicant->proof->id > 0 ? $applicant->proof->id : 0);
            $existingElegbId = (isset($applicant->feeeligibility->id) && $applicant->feeeligibility->id > 0 ? $applicant->feeeligibility->id : 0);

            ApplicantProofOfId::updateOrCreate(['applicant_id' => $applicantId, 'id' => $existingProofId], [
                'applicant_id' => $applicantId,
                'proof_type' => $proof['proof_type'] ?? null,
                'proof_id' => $proof['proof_id'] ?? null,
                'proof_expiredate' => $proof['proof_expiredate'] ?? null,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            ApplicantFeeEligibility::updateOrCreate(['applicant_id' => $applicantId, 'id' => $existingElegbId], [
                'applicant_id' => $applicantId,
                'fee_eligibility_id' => $proof['fee_eligibility_id'] ?? null,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        }

        $academicYear = AcademicYear::orderBy('id', 'desc')->where('from_date', '<=', Carbon::now())->where('to_date', '>=', Carbon::now())->first();
        if (isset($academicYear->id) && $academicYear->id > 0) {
            ApplicantProposedCourse::where('applicant_id', $applicantId)->update([
                'academic_year_id' => $academicYear->id,
                'updated_by' => $actorId,
            ]);
        }

        /**
         * These jobs have a strict dependency order and MUST run sequentially:
         *  - ProcessNewStudentToUser creates the StudentUser (needed by every job below).
         *  - ProcessStudents creates the Student record (needed by every job below).
         *  - ProcessStudentTaskDocument depends on the StudentTasks created by ProcessStudentTask.
         *
         * Bus::batch() dispatches all jobs concurrently, so on the redis queue (multiple
         * workers) the dependent jobs frequently executed BEFORE their prerequisites,
         * dereferencing null and failing with "Attempt to read property ... on null".
         * (It only appeared to work on the local `sync` queue, which happens to run batch
         * jobs in array order.)
         *
         * A plain Bus::chain() runs them strictly in order, but its dispatch() returns a
         * PendingDispatch (not a Batch), so $chainedBatch->id blew up with
         * "Attempt to read property 'id' on string" and the progress poller
         * (progressForStudentStoreProcess) had no job_batches row to read.
         *
         * Wrapping the jobs as a *chain within a batch* (a nested array inside Bus::batch())
         * keeps the strict sequential ordering AND yields a real Batch, so we still get a
         * batch id to track progress. On failure the chain halts so a retry resumes it
         * instead of leaving a half-migrated student.
         *
         * The chain is StudentConversionLog::JOB_SEQUENCE, so the Conversion log always
         * lists exactly the jobs that run, in the order they run.
         */
        $pending = Bus::batch([
            array_map(fn ($jobClass) => new $jobClass($applicant), StudentConversionLog::JOB_SEQUENCE),
        ]);
        if ($then) {
            $pending->then($then);
        }
        if ($catch) {
            $pending->catch($catch);
        }
        $batch = $pending->dispatch();

        $this->seedLogs($applicantId, $batch->id, $actorId);

        return $batch;
    }

    /**
     * One "queued" row per conversion job, written the moment the batch is
     * dispatched so the Conversion Log always shows the full plan — including
     * the steps a halted chain never reached. Keyed on (batch_id, job_class):
     * on the sync queue the jobs finish BEFORE this runs and the subscriber
     * has already created the rows, so firstOrCreate must not reset them.
     */
    public function seedLogs(int $applicantId, string $batchId, int $actorId): void
    {
        foreach (StudentConversionLog::JOB_SEQUENCE as $jobClass) {
            $log = StudentConversionLog::firstOrCreate([
                'batch_id' => $batchId,
                'job_class' => $jobClass,
            ], [
                'applicant_id' => $applicantId,
                'job_name' => StudentConversionLog::jobLabel($jobClass),
                'status' => StudentConversionLog::STATUS_QUEUED,
                'created_by' => $actorId,
            ]);
            if (empty($log->created_by)) {
                $log->update(['created_by' => $actorId]);
            }
        }

        Log::channel('student_conversion')->info('['.$batchId.'] Conversion batch dispatched for applicant #'.$applicantId.' ('.count(StudentConversionLog::JOB_SEQUENCE).' jobs) by user #'.$actorId.'.');
    }
}
