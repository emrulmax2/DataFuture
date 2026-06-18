<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Receives a finalised interview-outcome PDF from the LCC Operations app
 * (admission interviews module) and attaches it to the applicant in DataFuture:
 *
 *   1. records the uploaded PDF in applicant_documents,
 *   2. links it to the applicant's interview task via applicant_task_documents,
 *   3. marks that applicant_tasks row (task_list_id = 7) Completed,
 *   4. writes an applicant_task_logs audit entry.
 *
 * The PDF itself is already uploaded to the smschurchill S3 bucket by the
 * caller; this endpoint stores the provided URL. Authenticated via Passport
 * client-credentials (scope sms.applicants.write), so there is no logged-in
 * user — created_by/updated_by use the configured system user
 * (services.sms_sync.created_by).
 */
class ApplicantInterviewDocumentSyncController extends Controller
{
    public function store(Request $request, int $applicant)
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:1024'],
            'current_file_name' => ['required', 'string', 'max:255'],
            'display_file_name' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'max:32'],
            'outcome' => ['required', 'string', 'in:Pass,Fail'],
        ]);

        $systemUserId = (int) config('services.sms_sync.created_by', 1);
        if ($systemUserId <= 0) {
            return response()->json([
                'message' => 'SMS sync system user is not configured (set SMS_API_SYSTEM_USER_ID).',
            ], 500);
        }

        $taskListId = (int) config('services.sms_sync.interview_task_list_id', 7);

        $task = ApplicantTask::where('applicant_id', $applicant)
            ->where('task_list_id', $taskListId)
            ->first();

        if (! $task) {
            return response()->json([
                'message' => "No interview task (task_list_id {$taskListId}) found for applicant {$applicant}.",
            ], 404);
        }

        // Idempotency: if this exact file is already attached to the task (e.g. a
        // retried push), return the existing record instead of duplicating it.
        $linkedDocumentIds = ApplicantTaskDocument::where('applicant_task_id', $task->id)
            ->pluck('applicant_document_id');

        $existingDoc = ApplicantDocument::where('applicant_id', $applicant)
            ->where('current_file_name', $data['current_file_name'])
            ->whereIn('id', $linkedDocumentIds)
            ->first();

        if ($existingDoc) {
            return response()->json([
                'message' => 'Already attached.',
                'document_id' => $existingDoc->id,
                'applicant_task_id' => $task->id,
                'status' => $task->status,
                'idempotent' => true,
            ], 200);
        }

        $taskStatusId = $data['outcome'] === 'Pass' ? 1 : 2;

        $document = DB::transaction(function () use ($data, $applicant, $task, $systemUserId, $taskStatusId) {
            $document = ApplicantDocument::create([
                'applicant_id' => $applicant,
                'doc_type' => $data['doc_type'] ?: 'pdf',
                'path' => $data['path'],
                'display_file_name' => $data['display_file_name'] ?: $data['current_file_name'],
                'current_file_name' => $data['current_file_name'],
                'created_by' => $systemUserId,
            ]);

            ApplicantTaskDocument::create([
                'applicant_task_id' => $task->id,
                'applicant_document_id' => $document->id,
                'created_by' => $systemUserId,
            ]);

            $task->status = 'Completed';
            $task->task_status_id = $taskStatusId;
            $task->updated_by = $systemUserId;
            $task->save();

            ApplicantTaskLog::create([
                'applicant_tasks_id' => $task->id,
                'actions' => 'Document',
                'field_name' => '',
                'prev_field_value' => '',
                'current_field_value' => $document->id,
                'created_by' => $systemUserId,
            ]);

            return $document;
        });

        return response()->json([
            'message' => 'Interview outcome attached and task completed.',
            'document_id' => $document->id,
            'applicant_task_id' => $task->id,
            'status' => 'Completed',
            'task_status_id' => $taskStatusId,
        ], 201);
    }
}
