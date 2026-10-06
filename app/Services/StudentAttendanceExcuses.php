<?php

namespace App\Services;

use App\Models\Assign;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceExcuseDay;
use App\Models\AttendanceExcuseDocument;
use App\Models\PlansDateList;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

/**
 * Attendance excuses, for the student app.
 *
 * The rules are the web portal's (Student\Frontend\DashboardController and
 * AttendanceExcuseController): which class dates a student may excuse, and
 * what submitting an excuse writes. They are repeated here because the web
 * versions read the signed-in web session and build their lists for a blade.
 *
 * A "session" is one class date - a plans_date_lists row.
 */
class StudentAttendanceExcuses
{
    /* attendance_excuses.status as it is stored => as the app reads it. */
    const STATUSES = [
        0 => 'pending',
        1 => 'rejected',
        2 => 'approved',
    ];

    const ABSENT_FEED_STATUS = 4;

    /**
     * The class dates the student can excuse right now, as PlansDateList rows.
     *
     *   missed    marked absent, on a date before today
     *   upcoming  still scheduled, on a date after today
     *
     * A date already covered by an excuse that is waiting for a decision, or
     * that was approved, is left out: the web lists the first as locked and
     * hides the second. A rejected one comes back, so it can be sent again.
     *
     * @return array{missed:\Illuminate\Support\Collection,upcoming:\Illuminate\Support\Collection}
     */
    public function excusable($studentId): array
    {
        $planIds = Assign::where('student_id', $studentId)->where(function($q){
            $q->where('attendance', 1)->orWhereNull('attendance');
        })->pluck('plan_id')->filter()->unique()->values()->toArray();

        if(empty($planIds)):
            return ['missed' => collect(), 'upcoming' => collect()];
        endif;

        $today = date('Y-m-d');
        $with = ['plan.creations', 'plan.room', 'plan.venu'];

        $missed = PlansDateList::with($with)->whereIn('plan_id', $planIds)->whereHas('plan')
            ->where('feed_given', 1)->where('date', '<', $today)
            ->whereHas('attendances', function($q) use($studentId){
                $q->where('attendance_feed_status_id', self::ABSENT_FEED_STATUS)->where('student_id', $studentId)
                  ->whereColumn('attendances.plan_id', 'plans_date_lists.plan_id');
            })->orderBy('date', 'DESC')->orderBy('id', 'DESC')->get();

        $upcoming = PlansDateList::with($with)->whereIn('plan_id', $planIds)->whereHas('plan')
            ->where('status', 'Scheduled')->where('date', '>', $today)
            ->orderBy('date', 'ASC')->orderBy('id', 'ASC')->get();

        $taken = $this->takenDates($studentId, $missed->pluck('id')->merge($upcoming->pluck('id'))->toArray());
        $open = function($dateList) use($taken){
            return !in_array($dateList->plan_id.'_'.$dateList->id, $taken);
        };

        return [
            'missed' => $missed->filter($open)->values(),
            'upcoming' => $upcoming->filter($open)->values(),
        ];
    }

    /** Dates whose newest live excuse is pending or approved, as "plan_date" keys. */
    private function takenDates($studentId, array $dateListIds): array
    {
        if(empty($dateListIds)):
            return [];
        endif;

        /* Reached through the student's own excuses, which are indexed by
           student. attendance_excuse_days has no index on the class date, so
           asking it by date alone reads the whole table. */
        $statuses = AttendanceExcuse::where('student_id', $studentId)->pluck('status', 'id');
        if($statuses->isEmpty()):
            return [];
        endif;

        $days = AttendanceExcuseDay::whereIn('attendance_excuse_id', $statuses->keys()->toArray())->whereIn('plans_date_list_id', $dateListIds)
            ->where('active', 1)->orderBy('attendance_excuse_id', 'DESC')->get();

        $newest = [];
        foreach($days as $day):
            $key = $day->plan_id.'_'.$day->plans_date_list_id;
            if(!array_key_exists($key, $newest)):
                $status = $statuses->get($day->attendance_excuse_id);
                $newest[$key] = ($status !== null ? (int) $status : null);
            endif;
        endforeach;

        return array_keys(array_filter($newest, function($status){
            return $status === 0 || $status === 2;
        }));
    }

    /**
     * Save an excuse for the given class dates and send it to staff for review.
     *
     * @param \Illuminate\Support\Collection $dateLists PlansDateList rows, already checked against excusable()
     * @param \Illuminate\Http\UploadedFile[] $files
     * @return AttendanceExcuse|null null when the evidence could not be stored; nothing is saved then
     */
    public function submit(Student $student, $studentUserId, $dateLists, $reason, array $files): ?AttendanceExcuse
    {
        $stored = [];

        try {
            return DB::transaction(function () use ($student, $studentUserId, $dateLists, $reason, $files, &$stored) {
                $excuse = AttendanceExcuse::create([
                    'student_id' => $student->id,
                    'reason' => $reason,
                    'status' => 0,
                    'created_by' => $studentUserId,
                ]);

                foreach($dateLists as $dateList):
                    AttendanceExcuseDay::create([
                        'attendance_excuse_id' => $excuse->id,
                        'plan_id' => $dateList->plan_id,
                        'plans_date_list_id' => $dateList->id,
                        'created_by' => $studentUserId,
                    ]);
                endforeach;

                foreach($files as $file):
                    /* The web names these by the second alone, so two files
                       sent together land on one name. The suffix keeps each. */
                    $documentName = 'EXC_'.$student->id.'_'.time().'_'.Str::lower(Str::random(6)).'.'.$file->extension();
                    $path = $file->storeAs('public/students/'.$student->id, $documentName, 's3');
                    if(!$path):
                        throw new \RuntimeException('The file could not be written to storage.');
                    endif;
                    $stored[] = $path;

                    $document = AttendanceExcuseDocument::create([
                        'attendance_excuse_id' => $excuse->id,
                        'hard_copy_check' => 0,
                        'doc_type' => $file->getClientOriginalExtension(),
                        'path' => Storage::disk('s3')->url($path),
                        'display_file_name' => str_replace('.'.$file->extension(), '', $file->getClientOriginalName()),
                        'current_file_name' => $documentName,
                        'created_by' => $studentUserId,
                    ]);
                    Cache::forever($this->sizeKey($document->id), (int) $file->getSize());
                endforeach;

                $excuseTask = TaskList::where('attendance_excuses', 'Yes')->orderBy('id', 'DESC')->first();
                if(isset($excuseTask->id) && $excuseTask->id > 0):
                    $studentTask = StudentTask::create([
                        'student_id' => $student->id,
                        'task_list_id' => $excuseTask->id,
                        'status' => 'Pending',
                        'created_by' => 1,
                    ]);
                    $excuse->student_task_id = $studentTask->id;
                    $excuse->save();
                endif;

                return $excuse;
            });
        } catch (\Throwable $e) {
            /* The rows are rolled back; do not leave their files behind. */
            foreach($stored as $path):
                try {
                    Storage::disk('s3')->delete($path);
                } catch (\Throwable $ignored) {
                }
            endforeach;

            Log::error('[Attendance excuse] Could not save an excuse sent from the student app.', ['student' => $student->id, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /* What the app is shown                                               */
    /* ------------------------------------------------------------------ */

    /** One class date. $status is "absent" or "upcoming"; left out, it is read off the date. */
    public function session(PlansDateList $dateList, $status = null): array
    {
        $plan = $dateList->plan;
        $date = date('Y-m-d', strtotime($dateList->getRawOriginal('date')));
        $classType = (isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : (isset($plan->creations->class_type) ? $plan->creations->class_type : null));

        return [
            'id' => $dateList->id,
            'date' => $date,
            'start_time' => (isset($plan->start_time) && !empty($plan->start_time) ? date('H:i', strtotime($plan->start_time)) : null),
            'end_time' => (isset($plan->end_time) && !empty($plan->end_time) ? date('H:i', strtotime($plan->end_time)) : null),
            'status' => (!empty($status) ? $status : ($date > date('Y-m-d') ? 'upcoming' : 'absent')),
            'module' => [
                'id' => (isset($plan->module_creation_id) ? (int) $plan->module_creation_id : 0),
                'code' => (isset($plan->creations->code) && !empty($plan->creations->code) ? $plan->creations->code : null),
                'name' => (isset($plan->creations->module_name) ? $plan->creations->module_name : 'Unknown Module'),
            ],
            'class_type' => $classType,
            'room' => (isset($plan->room->name) ? $plan->room->name : null),
            'venue' => (isset($plan->venu->name) ? $plan->venu->name : null),
        ];
    }

    /** Eager-load for present(). */
    public function withDetails(): array
    {
        return ['days.plandate.plan.creations', 'days.plandate.plan.room', 'days.plandate.plan.venu', 'documents'];
    }

    public function present(AttendanceExcuse $excuse): array
    {
        $excuse->loadMissing($this->withDetails());

        $sessions = [];
        foreach($excuse->days as $day):
            if(isset($day->plandate->id)):
                $sessions[] = $this->session($day->plandate);
            endif;
        endforeach;

        $documents = [];
        foreach($excuse->documents as $document):
            $documents[] = $this->document($excuse, $document);
        endforeach;

        $status = (int) $excuse->status;

        return [
            'id' => $excuse->id,
            'status' => (isset(self::STATUSES[$status]) ? self::STATUSES[$status] : 'pending'),
            'reason' => (string) $excuse->reason,
            'submitted_at' => (!empty($excuse->created_at) ? $excuse->created_at->toIso8601String() : null),
            'reviewed_at' => (!empty($excuse->actioned_at) ? Carbon::parse($excuse->actioned_at)->toIso8601String() : null),
            'reviewer_note' => (!empty($excuse->remarks) ? $excuse->remarks : null),
            'sessions' => $sessions,
            'documents' => $documents,
        ];
    }

    private function document(AttendanceExcuse $excuse, AttendanceExcuseDocument $document): array
    {
        $path = 'public/students/'.$excuse->student_id.'/'.$document->current_file_name;
        $extension = strtolower((string) (!empty($document->doc_type) ? $document->doc_type : pathinfo((string) $document->current_file_name, PATHINFO_EXTENSION)));
        $mimeTypes = (new MimeTypes())->getMimeTypes($extension);

        /* Only the file name and type are kept on the row. The size is asked
           of storage once and remembered, since a stored file never changes. */
        $size = Cache::get($this->sizeKey($document->id));
        if($size === null):
            try {
                $size = (int) Storage::disk('s3')->size($path);
                Cache::forever($this->sizeKey($document->id), $size);
            } catch (\Throwable $e) {
                $size = 0;
            }
        endif;

        try {
            $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
        } catch (\Throwable $e) {
            $url = null;
        }

        return [
            'id' => $document->id,
            'file_name' => $document->display_file_name.($extension != '' ? '.'.$extension : ''),
            'size' => $size,
            'mime_type' => (!empty($mimeTypes) ? $mimeTypes[0] : 'application/octet-stream'),
            'url' => $url,
        ];
    }

    private function sizeKey($documentId): string
    {
        return 'attendance_excuse_document_size_'.$documentId;
    }
}
