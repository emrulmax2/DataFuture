<?php

namespace App\Support;

use App\Http\Controllers\AdmissionController;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantTask;
use App\Models\Status;
use Illuminate\Support\Facades\Log;

/**
 * The applicant status the task board drives (user, 23 Oct 2026):
 *  - the first task completed moves New → In Progress;
 *  - once no task is left Pending or In Progress it moves to Awaiting Decision.
 *
 * Only ever moves forward from New / In Progress, so a task completed later
 * (Accepted, Offer Placed, …) leaves the status alone. Archived (soft-deleted)
 * tasks do not count. Used by the admission process board and the staff Task
 * Manager, which complete applicant tasks.
 */
class ApplicantTaskStatus
{
    public const IN_PROGRESS = 3;
    public const AWAITING_DECISION = 4;

    /** Apply the rule after a task changed; returns the new status id, or null when nothing moved. */
    public static function sync(int $applicantId, ?int $userId = null): ?int
    {
        $applicant = Applicant::find($applicantId);
        $current = (int) optional($applicant)->status_id;
        if (!$applicant || $current < 2 || $current >= self::AWAITING_DECISION) {
            return null;
        }

        $open = ApplicantTask::where('applicant_id', $applicantId)->whereIn('status', ['Pending', 'In Progress'])->count();
        $done = ApplicantTask::where('applicant_id', $applicantId)->where('status', 'Completed')->count();
        if ($done === 0) {
            return null;
        }

        // One completion can finish everything: go straight to Awaiting Decision (one change, one notice).
        $to = $open === 0 ? self::AWAITING_DECISION : ($current < self::IN_PROGRESS ? self::IN_PROGRESS : null);
        if ($to === null) {
            return null;
        }

        $userId = $userId ?? (int) auth()->id();
        Applicant::where('id', $applicantId)->update(['status_id' => $to, 'updated_by' => $userId]);
        ApplicantArchive::create([
            'applicant_id' => $applicantId,
            'table' => 'applicants',
            'field_name' => 'status_id',
            'field_value' => $current,
            'field_new_value' => (string) $to,
            'created_by' => $userId,
        ]);
        self::notify($applicantId, $to);

        return $to;
    }

    /** The status's own letter or email, as a manual status change sends it. */
    private static function notify(int $applicantId, int $statusId): void
    {
        $status = Status::find($statusId);
        if (!$status || (empty($status->letter_set_id) && empty($status->email_template_id))) {
            return;
        }
        try {
            $sender = app(AdmissionController::class);
            if (!empty($status->letter_set_id)) {
                $sender->sendLetterOnStatusChanged($applicantId, $statusId);
            } else {
                $sender->sendEmailOnStatusChanged($applicantId, $statusId);
            }
        } catch (\Throwable $e) {
            // the status has moved; a failed notice must not undo the task change
            Log::warning('Applicant '.$applicantId.': status '.$statusId.' notice failed: '.$e->getMessage());
        }
    }

    public static function name(?int $statusId): ?string
    {
        if (!$statusId) {
            return null;
        }
        $name = optional(Status::find($statusId))->name;
        return $name === 'Awating Decision' ? 'Awaiting Decision' : $name;
    }
}
