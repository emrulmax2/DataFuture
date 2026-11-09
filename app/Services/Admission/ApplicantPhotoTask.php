<?php

namespace App\Services\Admission;

use App\Jobs\TaskAssignment\UpdateApplicantPhotoJob;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use App\Models\TaskAssignmentRule;
use App\Support\ApplicantDocumentDisk;
use App\Support\ApplicantTaskStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * A photo uploaded from the applicant record (#addApplicantPhotoModal) also goes into the
 * applicant's photo task (user, 28 Oct 2026): it is added to the task's uploads and, when the
 * task is still open, the task is completed. The photo task is found by its action, never by
 * a task id: the task whose rule runs "Update student photo" (UpdateApplicantPhotoJob), or the
 * same task list on an active rule with that action (a task staff added by hand).
 */
class ApplicantPhotoTask
{
    public function find(int $applicantId): ?ApplicantTask
    {
        $rules = TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_APPLICANT)->where('after_assign_job', UpdateApplicantPhotoJob::class);
        $ruleIds = (clone $rules)->pluck('id')->all();
        $listIds = (clone $rules)->where('active', 1)->pluck('task_list_id')->all();
        if (!$ruleIds) {
            return null;
        }

        return ApplicantTask::with('task')
            ->where('applicant_id', $applicantId)
            ->where('status', '!=', 'Canceled')
            ->where(fn ($q) => $q->whereIn('rule_id', $ruleIds)->orWhereIn('task_list_id', $listIds ?: [0]))
            ->orderByRaw("status = 'Completed'") // an open one first
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Add the applicant's current photo (already saved on the local disk) to the photo task's uploads,
     * and complete the task if it is still open. The "Update student photo" completion action is not
     * run: the photo is already the one just uploaded.
     *
     * @return array{task:string,completed_now:bool}|null null when the applicant has no photo task
     */
    public function attach(Applicant $applicant, string $originalName, int $userId): ?array
    {
        $task = $this->find((int) $applicant->id);
        if (!$task || empty($applicant->photo)) {
            return null;
        }
        $bytes = Storage::disk('local')->get('public/applicants/'.$applicant->id.'/'.$applicant->photo);
        if ($bytes === null || $bytes === '') {
            return null;
        }

        // with the other applicant documents (S3 on the servers, local here), under its own name so
        // replacing the photo later never removes the task's copy
        $disk = ApplicantDocumentDisk::name();
        $extension = strtolower(pathinfo($applicant->photo, PATHINFO_EXTENSION)) ?: 'jpg';
        $fileName = time().'_task_'.preg_replace('/[\\\\\/:*?"<>|]+/', '-', $originalName ?: ('photo.'.$extension));
        $path = ApplicantDocumentDisk::path($applicant->id, $fileName);
        if (!Storage::disk($disk)->put($path, $bytes)) {
            return null;
        }
        $taskName = $task->task->name ?? 'Applicant Photo Upload';

        try {
            $completedNow = DB::transaction(function () use ($applicant, $task, $disk, $path, $fileName, $extension, $taskName, $userId) {
                $document = ApplicantDocument::create([
                    'applicant_id' => $applicant->id,
                    'hard_copy_check' => 0,
                    'doc_type' => $extension,
                    'disk_type' => $disk,
                    'path' => Storage::disk($disk)->url($path),
                    'display_file_name' => $taskName,
                    'current_file_name' => $fileName,
                    'created_by' => $userId,
                ]);
                ApplicantTaskDocument::create(['applicant_task_id' => $task->id, 'applicant_document_id' => $document->id, 'created_by' => $userId]);
                // as a task upload from the process board logs it
                ApplicantTaskLog::create(['applicant_tasks_id' => $task->id, 'actions' => 'Document', 'field_name' => '', 'prev_field_value' => '', 'current_field_value' => $document->id, 'created_by' => $userId]);

                if ($task->status === 'Completed') {
                    return false;
                }
                $previous = $task->status ?: 'Pending';
                $task->update(['status' => 'Completed', 'updated_by' => $userId]);
                ApplicantTaskLog::create(['applicant_tasks_id' => $task->id, 'actions' => 'Status Changed', 'field_name' => 'status', 'prev_field_value' => $previous, 'current_field_value' => 'Completed', 'created_by' => $userId]);

                return true;
            });
        } catch (Throwable $e) {
            Storage::disk($disk)->delete($path);
            throw $e;
        }

        if ($completedNow) {
            // first completed task → In Progress, nothing left open → Awaiting Decision, as on the process board
            ApplicantTaskStatus::sync((int) $applicant->id, $userId);
        }

        return ['task' => $taskName, 'completed_now' => $completedNow];
    }
}
