<?php

namespace App\Console\Commands;

use App\Jobs\TaskAssignment\CreateStudentEmailJob;
use App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\StudentUser;
use App\Services\StudentEmail\StudentEmailProvisioner;
use App\Services\StudentEmail\StudentTaskActions;
use App\Services\StudentEmail\WorkspaceDirectory;
use Illuminate\Console\Command;

/**
 * Read-only: does a student's @lcc.ac.uk account exist, in our records and in Google?
 *
 *   php artisan student-email:status 20167                 (student id)
 *   php artisan student-email:status LCC20270002           (registration no)
 *   php artisan student-email:status lcc20270002@lcc.ac.uk (any address)
 */
class StudentEmailStatus extends Command
{
    protected $signature = 'student-email:status {who : student id, registration no or email address}';

    protected $description = 'Show whether a student\'s @lcc.ac.uk email exists, in our records and in Google Workspace (read-only)';

    public function handle(WorkspaceDirectory $google): int
    {
        $who = trim((string) $this->argument('who'));
        $student = null;
        $address = null;
        if (str_contains($who, '@')) {
            $address = strtolower($who);
            $id = StudentContact::where('institutional_email', $address)->orderByDesc('id')->value('student_id')
                ?? optional(StudentUser::where('email', $address)->first(), fn ($u) => Student::where('student_user_id', $u->id)->orderByDesc('id')->value('id'));
            $student = $id ? Student::find($id) : null;
        } elseif (ctype_digit($who)) {
            $student = Student::find((int) $who);
        } else {
            $student = Student::where('registration_no', $who)->orderByDesc('id')->first();
        }

        if ($student) {
            $contact = StudentContact::where('student_id', $student->id)->orderByDesc('id')->first();
            $address = $address ?: (optional($contact)->institutional_email ?: StudentEmailProvisioner::addressFor($student));
            $emailTask = StudentTaskActions::task($student->id, CreateStudentEmailJob::class);
            $welcomeTask = StudentTaskActions::task($student->id, SendStudentWelcomeLetterJob::class);
            $ours = StudentArchive::where('student_id', $student->id)->where('table', 'google_workspace')->where('field_name', 'account_created')->where('field_new_value', $address)->first();

            $this->line('<options=bold>Student #'.$student->id.'</> '.$student->registration_no.' · '.trim($student->first_name.' '.$student->last_name));
            $this->table(['In our records', ''], [
                ['Institutional email', optional($contact)->institutional_email ?: '—'],
                ['Login email', optional(StudentUser::find($student->student_user_id))->email ?: '—'],
                ['Personal email', optional($contact)->personal_email ?: '—'],
                ['Email task', $emailTask ? $emailTask->status : 'no task with the create-email action'],
                ['Welcome letter task', $welcomeTask ? $welcomeTask->status : 'no task with the welcome-letter action'],
                ['Created by this system', $ours ? 'yes, '.$ours->created_at : 'no'],
            ]);
        } elseif (!$address) {
            $this->error('No student found for "'.$who.'".');
            return self::FAILURE;
        } else {
            $this->line('No student in our records uses '.$address.'.');
        }

        if (!$address) {
            $this->line('No address to look up in Google (no registration number).');
            return self::SUCCESS;
        }
        if (!$google->configured()) {
            $this->warn('Google Workspace is not connected here, so '.$address.' was not looked up (php artisan student-email:check).');
            return self::SUCCESS;
        }
        try {
            $user = $google->getUser($address);
        } catch (\Throwable $e) {
            $this->error('Google: '.$e->getMessage());
            return self::FAILURE;
        }
        if (!$user) {
            $this->warn('Google: '.$address.' does NOT exist.');
            return self::SUCCESS;
        }
        $when = fn ($t) => $t ? date('d M Y H:i', strtotime($t)) : '—';
        $this->info('Google: '.$address.' exists.');
        $this->table(['In Google Workspace', ''], [
            ['Name', $user['name'] ?? '—'],
            ['Created', $when($user['creationTime'] ?? null)],
            ['Last sign-in', !empty($user['lastLoginTime']) && !str_starts_with((string) $user['lastLoginTime'], '1970') ? $when($user['lastLoginTime']) : 'never'],
            ['Must change password', !empty($user['changePasswordAtNextLogin']) ? 'yes (not signed in / not changed yet)' : 'no'],
            ['Suspended', !empty($user['suspended']) ? 'YES' : 'no'],
            ['Organisational unit', $user['orgUnitPath'] ?? '—'],
        ]);

        return self::SUCCESS;
    }
}
