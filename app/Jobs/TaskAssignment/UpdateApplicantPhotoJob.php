<?php

namespace App\Jobs\TaskAssignment;

use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskLog;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * "Update student photo" (Task Automation › Applicant, config('tasks.completion_jobs')): when
 * staff mark the rule's task Completed, the latest image uploaded to that task becomes the
 * applicant photo, which the conversion carries to the student (ProcessStudents).
 *
 * The upload lives with the other applicant documents (S3 on the servers, the local disk on a
 * local install); the photo is copied to where applicant photos always are, the local disk at
 * public/applicants/{id}/ (Applicant::photo_url). The previous photo file is kept.
 */
class UpdateApplicantPhotoJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Images a browser and the PDFs can show. */
    public const IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(public int $applicantId, public int $applicantTaskId, public ?int $ruleId = null)
    {
    }

    /** @return array{ok:bool,message:string} */
    public function handle(): array
    {
        $actorId = (int) (auth()->id() ?: config('tasks.system_user_id', 1));
        $task = ApplicantTask::find($this->applicantTaskId);
        $applicant = Applicant::find($this->applicantId);
        if (!$task || !$applicant || (int) $task->applicant_id !== (int) $applicant->id) {
            return ['ok' => false, 'message' => 'The task was not found, so the applicant photo was not changed.'];
        }

        $document = ApplicantDocument::query()
            ->join('applicant_task_documents as atd', 'atd.applicant_document_id', '=', 'applicant_documents.id')
            ->where('atd.applicant_task_id', $task->id)
            ->whereNull('atd.deleted_at')
            ->where('applicant_documents.applicant_id', $applicant->id)
            ->whereIn(DB::raw('LOWER(applicant_documents.doc_type)'), self::IMAGE_TYPES)
            ->orderByDesc('atd.id')
            ->select('applicant_documents.*')
            ->first();
        if (!$document) {
            return $this->result($task, false, 'No image (JPG, PNG, GIF or WEBP) is uploaded to this task, so the applicant photo was not changed.', $actorId);
        }

        try {
            $bytes = Storage::disk(ApplicantDocumentDisk::of($document))->get(ApplicantDocumentDisk::path($applicant->id, $document->current_file_name));
        } catch (Throwable $e) {
            $bytes = null;
        }
        if (empty($bytes) || @getimagesizefromstring($bytes) === false) {
            return $this->result($task, false, 'The uploaded image "'.$document->display_file_name.'" could not be read, so the applicant photo was not changed.', $actorId);
        }

        $extension = strtolower((string) $document->doc_type) === 'jpeg' ? 'jpg' : strtolower((string) $document->doc_type);
        $fileName = time().'_photo_'.$document->id.'.'.$extension;
        if (!Storage::disk('local')->put('public/applicants/'.$applicant->id.'/'.$fileName, $bytes)) {
            return $this->result($task, false, 'The photo could not be saved, so the applicant photo was not changed.', $actorId);
        }

        $old = $applicant->photo;
        $applicant->photo = $fileName;
        $applicant->save();
        ApplicantArchive::create([
            'applicant_id' => $applicant->id,
            'table' => 'applicants',
            'field_name' => 'photo',
            'field_value' => $old,
            'field_new_value' => $fileName,
            'created_by' => $actorId,
        ]);

        return $this->result($task, true, 'The applicant photo was updated from "'.$document->display_file_name.'".', $actorId, $old, $fileName);
    }

    private function result(ApplicantTask $task, bool $ok, string $message, int $actorId, ?string $old = null, ?string $new = null): array
    {
        try {
            ApplicantTaskLog::create([
                'applicant_tasks_id' => $task->id,
                'actions' => $ok ? 'Applicant photo updated' : 'Applicant photo not updated',
                'field_name' => 'photo',
                'prev_field_value' => $ok ? mb_substr((string) $old, 0, 145) : null,
                'current_field_value' => mb_substr($ok ? (string) $new : $message, 0, 145),
                'created_by' => $actorId,
            ]);
        } catch (Throwable $e) {
            Log::warning('[Task Automation] Photo action log not written for applicant task #'.$task->id.': '.$e->getMessage());
        }

        return ['ok' => $ok, 'message' => $message];
    }
}
