<?php

namespace App\Listeners;

use App\Models\Student;
use App\Models\StudentConversionLog;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes the applicant-to-student conversion audit trail
 * (student_conversion_logs + the student_conversion file channel).
 *
 * The 21 ProcessStudent* jobs — dispatched as a chain-in-batch from
 * AdmissionController::admissionStudentUpdateStatus when an applicant reaches
 * status 7 (Offer Accepted) — are deliberately left untouched: this subscriber
 * watches the queue's own lifecycle events instead, so every job is tracked
 * the same way and a new job only needs adding to
 * StudentConversionLog::JOB_SEQUENCE to be logged.
 *
 * Rows are keyed on (batch_id, job_class). The controller seeds them as
 * "queued" right after dispatch, but on the sync queue the jobs run BEFORE
 * that seeding, so every write here goes through firstOrCreate on the same
 * key. A logging problem must never break the conversion itself, so each
 * handler swallows its own exceptions and reports them on the file channel.
 */
class StudentConversionLogSubscriber
{
    public function handleJobProcessing(JobProcessing $event){
        $this->safely($event, function ($row) {
            $row->update([
                'status' => StudentConversionLog::STATUS_PROCESSING,
                'attempts' => $row->attempts + 1,
                'started_at' => now(),
                'finished_at' => null,
                'message' => null,
            ]);
            $this->fileLog()->info('['.$row->batch_id.'] '.$row->job_name.' started (attempt '.$row->attempts.') for applicant #'.$row->applicant_id.'.');
        });
    }

    public function handleJobProcessed(JobProcessed $event){
        $this->safely($event, function ($row) {
            $row->update([
                'status' => StudentConversionLog::STATUS_COMPLETED,
                'finished_at' => now(),
                'message' => 'Completed successfully.',
            ]);
            $this->fileLog()->info('['.$row->batch_id.'] '.$row->job_name.' completed for applicant #'.$row->applicant_id.'.');

            $sequence = StudentConversionLog::JOB_SEQUENCE;
            if ($row->job_class === end($sequence)) {
                // The whole chain succeeded — stamp the created student onto
                // every row of this batch and record the happy ending.
                $student = ($row->applicant_id > 0 ? Student::where('applicant_id', $row->applicant_id)->orderBy('id', 'DESC')->first() : null);
                if ($student) {
                    StudentConversionLog::where('batch_id', $row->batch_id)->update(['student_id' => $student->id]);
                }
                $this->fileLog()->info('['.$row->batch_id.'] Conversion COMPLETED for applicant #'.$row->applicant_id.($student ? ' -> student #'.$student->id : '').'.');
            }
        });
    }

    public function handleJobExceptionOccurred(JobExceptionOccurred $event){
        $this->recordFailure($event, $event->exception, false);
    }

    public function handleJobFailed(JobFailed $event){
        $this->recordFailure($event, $event->exception, true);
    }

    public function subscribe($events)
    {
        return [
            JobProcessing::class => 'handleJobProcessing',
            JobProcessed::class => 'handleJobProcessed',
            JobExceptionOccurred::class => 'handleJobExceptionOccurred',
            JobFailed::class => 'handleJobFailed',
        ];
    }

    /**
     * $isFinal distinguishes JobFailed (out of retries — the chain halts) from
     * JobExceptionOccurred (one attempt failed; a retry may still reset the
     * row back to processing).
     */
    protected function recordFailure($event, Throwable $exception, $isFinal)
    {
        $this->safely($event, function ($row) use ($exception, $isFinal) {
            $row->update([
                'status' => StudentConversionLog::STATUS_FAILED,
                'message' => Str::limit('Failed: '.$exception->getMessage(), 500),
                'exception' => (string) $exception,
                'finished_at' => now(),
            ]);
            $this->fileLog()->error('['.$row->batch_id.'] '.$row->job_name.' FAILED for applicant #'.$row->applicant_id.' ('.get_class($exception).'): '.$exception->getMessage());

            if ($isFinal) {
                // The chain halts here, so the steps still queued never run.
                StudentConversionLog::where('batch_id', $row->batch_id)
                    ->where('status', StudentConversionLog::STATUS_QUEUED)
                    ->update([
                        'status' => StudentConversionLog::STATUS_CANCELLED,
                        'message' => 'Skipped: the conversion halted after "'.$row->job_name.'" failed.',
                        'finished_at' => now(),
                    ]);
            }
        });
    }

    /**
     * Filter to conversion jobs, resolve their log row, and run $callback —
     * absorbing every error so the queue worker is never disrupted.
     */
    protected function safely($event, callable $callback)
    {
        try {
            $jobClass = $event->job->payload()['data']['commandName'] ?? null;
            if (!in_array($jobClass, StudentConversionLog::JOB_SEQUENCE, true)) {
                return;
            }

            [$batchId, $applicantId] = $this->identify($event);
            if (empty($batchId)) {
                $this->fileLog()->warning($jobClass.' ran without a resolvable batch id; event not recorded.');
                return;
            }

            $callback($this->resolveRow($jobClass, $batchId, $applicantId));
        } catch (Throwable $e) {
            try {
                $this->fileLog()->error('Conversion log listener failed: '.$e->getMessage());
            } catch (Throwable $ignored) {
            }
        }
    }

    /**
     * Batch id and applicant id both live on the serialized job command
     * (Batchable::$batchId and the job's public $applicant).
     */
    protected function identify($event)
    {
        $batchId = null;
        $applicantId = null;
        try {
            $command = unserialize($event->job->payload()['data']['command']);
            $batchId = $command->batchId ?? null;
            $applicantId = (isset($command->applicant->id) ? $command->applicant->id : null);
        } catch (Throwable $e) {
            // SerializesModels re-queries the Applicant on unserialize; if the
            // row vanished mid-flight we log with whatever we could read.
        }
        return [$batchId, $applicantId];
    }

    protected function resolveRow($jobClass, $batchId, $applicantId)
    {
        try {
            return StudentConversionLog::firstOrCreate([
                'batch_id' => $batchId,
                'job_class' => $jobClass,
            ], [
                'applicant_id' => $applicantId,
                'job_name' => StudentConversionLog::jobLabel($jobClass),
                'status' => StudentConversionLog::STATUS_QUEUED,
            ]);
        } catch (Throwable $e) {
            // Lost the insert race against the controller's seeding — the row
            // exists now (unique on batch_id + job_class), so read it back.
            return StudentConversionLog::where('batch_id', $batchId)->where('job_class', $jobClass)->firstOrFail();
        }
    }

    protected function fileLog()
    {
        return Log::channel('student_conversion');
    }
}
