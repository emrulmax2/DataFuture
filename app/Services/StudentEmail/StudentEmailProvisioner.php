<?php

namespace App\Services\StudentEmail;

use App\Mail\StudentWorkspaceAccount;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\StudentLetter;
use App\Models\StudentLettersDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\StudentUser;
use App\Traits\GenerateStudentLetterTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * A new student's @lcc.ac.uk account, done in one go when the "Student Email Creation
 * and Notification" task is assigned (user, 26 Oct 2026):
 *
 *  1. create the Google Workspace account (lower-case registration no @ domain) with the
 *     date of birth as the password (DDMMYYYY), changed by the student at first sign-in, in
 *     the intake's OU (/Students/2027 January, made when missing) and the student@ group
 *     (user, 13 Nov 2026 — either failing is reported, the account still goes ahead);
 *  2. assign the configured Workspace licence;
 *  3. institutional email = that address (verified); the login email becomes it too,
 *     and the old login email stays as the personal email;
 *  4. email the address and password to the personal email;
 *  5. complete the task.
 * The welcome letter to both addresses comes later (queueWelcomeLetter is kept for it).
 *
 * Google failing changes nothing. Without Workspace credentials, step 3 still runs and
 * the task stays Pending, so staff finish it by hand as before. An address Google
 * already had is left alone and its password is not emailed (it is not ours to know).
 *
 * A "google_workspace / account_created" student_archives row marks accounts this class
 * created; admission:set-status deletes only those.
 */
class StudentEmailProvisioner
{
    use GenerateStudentLetterTrait;

    public const DONE = 'done';
    public const NEEDS_GOOGLE = 'needs_google';
    public const FAILED = 'failed';

    public function __construct(private WorkspaceDirectory $directory)
    {
    }

    /** The address: [prefix] lower-case registration number @ the student email domain. */
    public static function addressFor(Student $student): ?string
    {
        $reg = strtolower(trim((string) $student->registration_no));

        return $reg === '' ? null : config('student_email.address_prefix', '').$reg.'@'.config('student_email.domain', 'lcc.ac.uk');
    }

    /**
     * The first password: the date of birth as DDMMYYYY (26-11-1986 → 26111986). A student
     * with no date of birth gets STUDENT_EMAIL_INITIAL_PASSWORD, or none.
     */
    public static function initialPassword(Student $student): ?string
    {
        $dob = $student->getRawOriginal('date_of_birth');
        if (!empty($dob)) {
            try {
                return Carbon::parse($dob)->format('dmY');
            } catch (\Throwable $e) {
            }
        }
        $fallback = (string) config('student_email.initial_password');

        return $fallback !== '' ? $fallback : null;
    }

    /**
     * The organisational unit for the student's account: the intake parent (GOOGLE_WORKSPACE_INTAKE_OU_PARENT,
     * /Students) + their intake semester, e.g. /Students/2027 January. Without an intake (or with no
     * parent set) it is GOOGLE_WORKSPACE_STUDENT_OU, as before.
     */
    public static function orgUnitFor(Student $student): string
    {
        $default = (string) config('student_email.google.org_unit', '/');
        $parent = trim((string) config('student_email.google.intake_ou_parent', ''));
        $intake = trim(str_replace('/', '-', (string) optional(optional($student->activeCR)->semester)->name));
        if ($parent === '' || $intake === '') {
            return $default;
        }

        return rtrim($parent, '/').'/'.$intake;
    }

    /** @return array{status:string, email:?string, message:string} */
    public function provision(Student $student, ?StudentTask $task, int $actorId): array
    {
        $email = self::addressFor($student);
        if (!$email) {
            return $this->finish($task, self::FAILED, null, 'No registration number, so no address could be made.', $actorId);
        }

        // 1-2. Google
        $google = null;
        $licence = null;
        $placed = [];   // the intake OU and the student group, as done
        $problems = []; // what Google refused there (reported; the account still goes ahead)
        $password = self::initialPassword($student);
        if ($this->directory->configured()) {
            if ($password === null || strlen($password) < 8) {
                return $this->finish($task, self::FAILED, $email, 'No date of birth (or fallback password), so the Google account was not created.', $actorId);
            }
            // the intake's OU (/Students/2027 January), made when missing; if Google refuses, the usual OU
            $defaultOu = (string) config('student_email.google.org_unit', '/');
            $orgUnit = self::orgUnitFor($student);
            $ouReady = $orgUnit === $defaultOu;
            if (!$ouReady) {
                try {
                    $ouNew = $this->directory->ensureOrgUnit($orgUnit) === 'created';
                    $ouReady = true;
                } catch (\Throwable $e) {
                    $problems[] = 'OU '.$orgUnit.' not used ('.$e->getMessage().'); the account went in '.$defaultOu;
                    $orgUnit = $defaultOu;
                }
            }
            try {
                $google = $this->directory->createUser($email, (string) $student->first_name, (string) $student->last_name, $password,
                    (bool) config('student_email.change_at_first_login', true), $orgUnit);
                if ($google === 'exists' && $this->createdBefore($student, $email)) {
                    $google = 'created_before'; // ours from an earlier, unfinished run (student-email:create retries)
                }
                if ($google === 'created') {
                    StudentArchive::create(['student_id' => $student->id, 'table' => 'google_workspace', 'field_name' => 'account_created', 'field_value' => null, 'field_new_value' => $email, 'created_by' => $actorId]);
                }
                $licence = $this->directory->assignLicence($email);
            } catch (\Throwable $e) {
                Log::error('Student email: Google step failed', ['student_id' => $student->id, 'email' => $email, 'error' => $e->getMessage()]);
                // the account may exist without its licence; our records are left alone
                return $this->finish($task, self::FAILED, $email, $e->getMessage(), $actorId);
            }
            if (in_array($google, ['created', 'created_before'], true)) {
                if ($google === 'created_before' && $orgUnit !== $defaultOu) {
                    try {
                        $this->directory->moveUser($email, $orgUnit); // an earlier run may have put it elsewhere
                    } catch (\Throwable $e) {
                        $problems[] = 'not moved to '.$orgUnit.' ('.$e->getMessage().')';
                        $ouReady = false;
                    }
                }
                if ($ouReady && $orgUnit !== $defaultOu) {
                    $placed[] = 'OU '.$orgUnit.(!empty($ouNew) ? ' (new)' : '');
                }
                $this->joinStudentGroup($email, $placed, $problems);
            }
            $this->reportPlacement($student, $task, $email, $problems, $actorId);
        }

        // 3. Our records
        DB::transaction(fn () => $this->updateRecords($student, $email, $actorId));

        if ($google === null) {
            return $this->finish($task, self::NEEDS_GOOGLE, $email,
                $email.' set as institutional + login email. Google not connected: create it by hand, then Complete & Send Email.', $actorId);
        }
        if ($google === 'exists') {
            return $this->finish($task, self::FAILED, $email,
                'Google already had '.$email.'; left unchanged, no password emailed. Check it, then Complete & Send Email.', $actorId);
        }

        // 4. Address + password to the personal email
        $personal = (string) optional(StudentContact::where('student_id', $student->id)->orderByDesc('id')->first())->personal_email;
        if ($personal === '') {
            return $this->finish($task, self::FAILED, $email, 'Created '.$email.', but there is no personal email to send the password to.', $actorId);
        }
        // only ever the student's own address: the new mailbox cannot be opened without this password
        if (strcasecmp($personal, $email) === 0 || str_ends_with(strtolower($personal), '@'.strtolower((string) config('student_email.domain', 'lcc.ac.uk')))) {
            return $this->finish($task, self::FAILED, $email, 'Created '.$email.', but the personal email is a college address ('.$personal.'); password not sent.', $actorId);
        }
        try {
            self::mailer()->to($personal)->send(new StudentWorkspaceAccount($student, $email, $password, (bool) config('student_email.change_at_first_login', true)));
        } catch (\Throwable $e) {
            Log::error('Student email: details not emailed', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            return $this->finish($task, self::FAILED, $email, 'Created '.$email.', but emailing '.$personal.' failed: '.$e->getMessage(), $actorId);
        }

        // The welcome letter to both addresses is sent next by WelcomeLetterSender (CreateStudentEmailJob),
        // the way staff send it from the Communication page; queueWelcomeLetter is no longer used.
        // $queued = $this->queueWelcomeLetter($student->fresh(), $actorId);

        // 5. Done
        return $this->finish($task, self::DONE, $email,
            'Created '.$email.($licence ? ' (licence '.$licence.')' : '').($placed ? ' · '.implode(' · ', $placed) : '').'; details emailed to '.$personal.'.', $actorId, true);
    }

    /** The new account joins the student group (GOOGLE_WORKSPACE_STUDENT_GROUP, student@lcc.ac.uk); test addresses never do. */
    private function joinStudentGroup(string $email, array &$placed, array &$problems): void
    {
        $group = trim((string) config('student_email.google.student_group', ''));
        if ($group === '') {
            return;
        }
        if ((string) config('student_email.address_prefix', '') !== '') {
            $placed[] = 'not added to '.$group.' (test address)';

            return;
        }
        try {
            $placed[] = ($this->directory->addToGroup($group, $email) === 'member' ? 'already in ' : 'added to ').$group;
        } catch (\Throwable $e) {
            $problems[] = 'not added to '.$group.' ('.$e->getMessage().')';
        }
    }

    /** What Google refused about the OU or the group: logged, on the task log, and mailed to CONVERSION_ERROR_REPORT_TO. */
    private function reportPlacement(Student $student, ?StudentTask $task, string $email, array $problems, int $actorId): void
    {
        if (!$problems) {
            return;
        }
        $text = implode(' · ', $problems);
        Log::warning('Student email: OU / group not set', ['student_id' => $student->id, 'email' => $email, 'problems' => $problems]);
        if ($task) {
            \App\Services\Conversion\ConversionErrorReport::report('Action: Create student email (OU / group)', $email.': '.$text, ['student_id' => $task->student_id, 'task_id' => $task->id, 'rule_id' => $task->rule_id]);
            StudentTaskLog::create([
                'student_tasks_id' => $task->id, 'actions' => 'Student email: OU / group', 'field_name' => 'institutional_email',
                'prev_field_value' => '', 'current_field_value' => mb_substr($text, 0, 145), 'created_by' => $actorId, // varchar(145)
            ]);
        }
    }

    /**
     * The mailer for emails to the student: the default one, or — with
     * STUDENT_EMAIL_IGNORE_MAIL_REDIRECT — a copy of it under another name, which
     * MAIL_REDIRECT_TO (Mail::alwaysTo on the default mailer) does not reach.
     */
    public static function mailer(): \Illuminate\Contracts\Mail\Mailer
    {
        if (!config('student_email.ignore_mail_redirect')) {
            return Mail::mailer();
        }
        $default = (string) config('mail.default');
        config(['mail.mailers.student_direct' => config('mail.mailers.'.$default)]);

        return Mail::mailer('student_direct');
    }

    /** Did an earlier run of this class create this address for this student? */
    private function createdBefore(Student $student, string $email): bool
    {
        return StudentArchive::where('student_id', $student->id)->where('table', 'google_workspace')
            ->where('field_name', 'account_created')->where('field_new_value', $email)->exists();
    }

    private function updateRecords(Student $student, string $email, int $actorId): void
    {
        $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
        $login = StudentUser::find($student->student_user_id);
        $loginEmail = (string) optional($login)->email;

        if ($contact) {
            $changes = ['institutional_email' => $email, 'institutional_email_verification' => 1];
            // the old login email is the student's own address: keep it as the personal email
            if (empty($contact->personal_email) && $loginEmail !== '' && strcasecmp($loginEmail, $email) !== 0) {
                $changes['personal_email'] = $loginEmail;
            }
            $this->apply($contact, $changes, 'student_contacts', $student, $actorId);
        }

        if (strcasecmp($loginEmail, $email) === 0) {
            return;
        }
        $owner = StudentUser::where('email', $email)->first();
        if ($owner && optional($login)->id !== $owner->id) {
            // the address already belongs to a login (e.g. a returning student): use that login
            $this->apply($student, ['student_user_id' => $owner->id], 'students', $student, $actorId);
        } elseif ($login) {
            $this->apply($login, ['email' => $email], 'student_users', $student, $actorId);
        }
    }

    /** Save the changed fields and write one student_archives row per field, as the Task Manager export does. */
    private function apply($model, array $changes, string $table, Student $student, int $actorId): void
    {
        foreach ($changes as $field => $value) {
            if ((string) $model->{$field} === (string) $value) {
                continue;
            }
            StudentArchive::create([
                'student_id' => $student->id,
                'table' => $table,
                'field_name' => $field,
                'field_value' => $model->{$field},
                'field_new_value' => $value,
                'created_by' => $actorId,
            ]);
            $model->{$field} = $value;
        }
        if ($model->isDirty()) {
            if (in_array('updated_by', $model->getFillable(), true)) {
                $model->updated_by = $actorId; // student_users has no updated_by
            }
            $model->save();
        }
    }

    /**
     * The welcome email, as Complete & Send Email queues it: a student_letters row with its
     * PDF and a letters document marked mail_sent_status = 1, which
     * studentbulkemailcreationmailsend:cron sends. Not called yet (the letter step comes
     * later). Returns true, or why it could not.
     */
    private function queueWelcomeLetter(Student $student, int $actorId)
    {
        $letterSet = LetterSet::find((int) config('student_email.welcome_letter_set_id', 116));
        if (!$letterSet || empty($letterSet->description)) {
            return 'letter set '.config('student_email.welcome_letter_set_id').' is missing.';
        }
        $signatory = (int) config('student_email.welcome_signatory_id', 23);
        $smtp = ComonSmtp::where('is_default', 1)->first();
        $issued = date('Y-m-d');
        $title = $letterSet->letter_title ?: 'Letter from LCC';
        $pin = time();

        try {
            $letter = StudentLetter::create([
                'student_id' => $student->id, 'letter_set_id' => $letterSet->id, 'pin' => $pin, 'signatory_id' => $signatory,
                'comon_smtp_id' => optional($smtp)->id, 'is_email_or_attachment' => 2, 'issued_by' => $actorId,
                'issued_date' => $issued, 'created_by' => $actorId,
            ]);
            $pdf = $this->generateLetter($student->id, $title, $letterSet->description, $issued, $pin, $signatory);
            StudentLettersDocument::create([
                'student_id' => $student->id, 'student_letter_id' => $letter->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf',
                'path' => Storage::disk('s3')->url('public/students/'.$student->id.'/'.$pdf['filename']),
                'display_file_name' => $title, 'current_file_name' => $pdf['filename'], 'created_by' => $actorId,
                'mail_sent_status' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::error('Student email: welcome letter not queued', ['student_id' => $student->id, 'error' => $e->getMessage()]);
            return $e->getMessage();
        }

        return true;
    }

    /** Record the outcome on the task (log line; Completed when done) and return it. */
    private function finish(?StudentTask $task, string $status, ?string $email, string $message, int $actorId, bool $complete = false): array
    {
        if ($status !== self::DONE && $task) {
            // emailed to CONVERSION_ERROR_REPORT_TO (user, 30 Oct 2026)
            \App\Services\Conversion\ConversionErrorReport::report('Action: Create student email', $message, ['student_id' => $task->student_id, 'task_id' => $task->id, 'rule_id' => $task->rule_id]);
        }
        if ($task) {
            StudentTaskLog::create([
                'student_tasks_id' => $task->id,
                'actions' => $status === self::DONE ? 'Student email created' : ($status === self::NEEDS_GOOGLE ? 'Student email assigned' : 'Student email failed'),
                'field_name' => 'institutional_email',
                'prev_field_value' => '',
                'current_field_value' => mb_substr($message, 0, 145), // varchar(145)
                'created_by' => $actorId,
            ]);
            if ($complete && $task->status !== 'Completed') {
                StudentTaskLog::create([
                    'student_tasks_id' => $task->id, 'actions' => 'Status Changed', 'field_name' => 'status',
                    'prev_field_value' => $task->status, 'current_field_value' => 'Completed', 'created_by' => $actorId,
                ]);
                $task->update(['status' => 'Completed', 'updated_by' => $actorId]);
            }
        }

        return ['status' => $status, 'email' => $email, 'message' => $message];
    }
}
