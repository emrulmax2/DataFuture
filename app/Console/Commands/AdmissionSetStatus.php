<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocusealSubmission;
use App\Models\Status;
use App\Support\AdmissionStatusFlow;
use App\Services\StudentEmail\WorkspaceDirectory;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Local test helper: put an application back on a status, quietly (no letter,
 * email, DocuSeal form, task rules or conversion), and remove the student
 * records its earlier conversions created so the next conversion starts clean
 * (user, 26 Oct 2026). Writes the status history row so the Time line shows
 * the reset. Refuses outside local / testing and asks before deleting.
 *
 *   php artisan admission:set-status 3562 5
 *
 * Only students a conversion of THIS applicant made are removed: the ids its
 * conversion logs recorded, plus students carrying the applicant id created
 * since its first conversion. Older students that merely share the applicant
 * id (e.g. student 3562, LCC20123562) are never touched. The student login goes
 * too, but only when it was created by those conversions and no other student
 * uses it.
 *
 * Their Google Workspace accounts are deleted as well (user, 26 Oct 2026), but only
 * accounts StudentEmailProvisioner created for those students (its
 * "google_workspace / account_created" mark), that no remaining student uses, and
 * that Google says were created no earlier than that mark. If a Google deletion
 * fails, nothing else is changed.
 */
class AdmissionSetStatus extends Command
{
    protected $signature = 'admission:set-status
        {applicant : applicants.id}
        {status : the status id to set, e.g. 5 for Accepted}
        {--keep-students : only change the status; leave the converted students}
        {--keep-google : remove the students but leave their Google accounts}
        {--force : do not ask before deleting}
        {--dry-run : show what would change and stop}
        {--by=7 : users.id recorded on the history row}';

    protected $description = 'Local only: set an applicant\'s status quietly and remove the students its conversions created';

    public function handle(): int
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->error('admission:set-status only runs when APP_ENV is local or testing.');
            return self::FAILURE;
        }

        $applicant = Applicant::find((int) $this->argument('applicant'));
        if (!$applicant) {
            $this->error('No applicant #'.$this->argument('applicant').'.');
            return self::FAILURE;
        }
        $to = (int) $this->argument('status');
        $names = Status::where('type', 'Applicant')->pluck('name', 'id')->map(fn ($n) => $n === 'Awating Decision' ? 'Awaiting Decision' : $n);
        if (!isset($names[$to])) {
            $this->error('Status '.$to.' is not an applicant status. Use one of: '.$names->map(fn ($n, $id) => $id.' '.$n)->implode(', ').'.');
            return self::FAILURE;
        }
        $label = fn ($id) => $id.' '.($names[$id] ?? '?');
        $from = (int) $applicant->status_id;

        $plan = $this->option('keep-students') ? null : $this->studentPlan($applicant);
        $this->line('#'.$applicant->id.' ('.$applicant->application_no.'): status '.($from === $to ? 'already '.$label($to) : $label($from).' → '.$label($to)));
        if ($plan && $plan['students']) {
            $this->line('Student records from earlier conversions to remove:');
            foreach ($plan['students'] as $s) {
                $this->line('  student #'.$s->id.' '.($s->registration_no ?: '(no reg. no)').' created '.$s->created_at);
            }
            $this->table(['table', 'rows'], collect($plan['rows'])->map(fn ($n, $t) => [$t, $n])->values()->all());
            if ($plan['logins']) {
                $this->line('  student login'.(count($plan['logins']) > 1 ? 's' : '').' '.implode(', ', array_map(fn ($l) => '#'.$l->id.' '.$l->email, $plan['logins'])).' (no other student uses '.(count($plan['logins']) > 1 ? 'them' : 'it').')');
            }
            if ($plan['folders']) {
                $this->line('  files: '.implode(', ', $plan['folders']));
            }
            foreach ($plan['google'] as $g) {
                $this->line('  Google account '.$g['email'].': '.$g['note']);
            }
        } elseif ($plan) {
            $this->line('No student records from earlier conversions.');
        }
        if ($plan && $plan['logs']) {
            $this->line('  '.$plan['logs'].' conversion log row'.($plan['logs'] === 1 ? '' : 's').' of this applicant');
        }

        $deleting = $plan && ($plan['students'] || $plan['logs']);
        $googleDeletes = $plan ? array_values(array_filter($plan['google'], fn ($g) => $g['delete'])) : [];
        if ($this->option('dry-run')) {
            $this->info('Dry run: nothing changed.');
            return self::SUCCESS;
        }
        if ($from === $to && !$deleting) {
            $this->info('Nothing to change.');
            return self::SUCCESS;
        }
        if ($deleting && !$this->option('force') && !$this->confirm('Delete the rows above and set the status?', false)) {
            $this->warn('Nothing changed.');
            return self::SUCCESS;
        }

        // Google first: if an account cannot be deleted, stop before anything else changes.
        foreach ($googleDeletes as $g) {
            try {
                app(WorkspaceDirectory::class)->deleteUser($g['email']);
                $this->line('  Google account '.$g['email'].' deleted.');
            } catch (\Throwable $e) {
                $this->error('Google account '.$g['email'].' could not be deleted: '.$e->getMessage());
                $this->warn('Nothing else changed.');
                return self::FAILURE;
            }
        }

        DB::transaction(function () use ($applicant, $from, $to, $plan, $deleting) {
            if ($from !== $to) {
                Applicant::where('id', $applicant->id)->update(['status_id' => $to, 'updated_by' => (int) $this->option('by')]);
                ApplicantArchive::create([
                    'applicant_id' => $applicant->id,
                    'table' => 'applicants',
                    'field_name' => 'status_id',
                    'field_value' => $from,
                    'field_new_value' => $to,
                    'created_by' => (int) $this->option('by'),
                ]);
            }
            if ($deleting) {
                $ids = array_map(fn ($s) => (int) $s->id, $plan['students']);
                foreach ($plan['tables'] as $table) {
                    DB::table($table)->whereIn('student_id', $ids)->delete();
                }
                DB::table('student_conversion_logs')->where('applicant_id', $applicant->id)->delete();
                DB::table('applicant_users')->whereIn('student_id', $ids)->update(['student_id' => null]);
                DB::table('students')->whereIn('id', $ids)->delete();
                foreach ($plan['logins'] as $login) {
                    foreach ($plan['loginTables'] as $table) {
                        DB::table($table)->where('student_user_id', $login->id)->delete();
                    }
                    DB::table('student_users')->where('id', $login->id)->delete();
                }
            }
        });
        if ($deleting && $plan['folders']) {
            foreach ($plan['folders'] as $folder) {
                Storage::disk('local')->deleteDirectory($folder);
            }
        }

        $this->info('Done: '.($from !== $to ? $label($from).' → '.$label($to).', history row written' : 'status unchanged').($deleting ? '; '.count($plan['students']).' student record'.(count($plan['students']) === 1 ? '' : 's').' removed' : '').'. Nothing was sent.');

        $open = ApplicantDocusealSubmission::where('applicant_id', $applicant->id)->where('state', 'waiting')->count();
        if ($open) {
            $this->line('  '.$open.' offer acceptance form'.($open === 1 ? ' is' : 's are').' still open on DocuSeal; signing one only stores the documents unless the status is Offer Placed.');
        }
        $missing = AdmissionStatusFlow::missingForOffer($applicant);
        if ($missing && in_array($to, [4, 5, 9], true)) {
            $this->line('  Still missing before the offer can be placed: '.implode(', ', $missing).'.');
        }

        return self::SUCCESS;
    }

    /**
     * What a reset would remove: the students this applicant's conversions created, the rows
     * pointing at them (every table with a student_id column), their conversion logs, the
     * student login when those conversions made it and nothing else uses it, and the local
     * student folders.
     */
    private function studentPlan(Applicant $applicant): array
    {
        $logs = DB::table('student_conversion_logs')->where('applicant_id', $applicant->id);
        $since = (clone $logs)->min('created_at');
        $ids = (clone $logs)->whereNotNull('student_id')->pluck('student_id')->map(fn ($id) => (int) $id);
        if ($since) {
            $ids = $ids->merge(DB::table('students')->where('applicant_id', $applicant->id)->where('created_at', '>=', $since)->pluck('id')->map(fn ($id) => (int) $id));
        }
        $students = DB::table('students')->whereIn('id', $ids->unique()->values())->orderBy('id')->get()->all();
        $studentIds = array_map(fn ($s) => (int) $s->id, $students);

        $tables = array_values(array_filter(
            array_map(fn ($r) => $r->t, DB::select('SELECT TABLE_NAME t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = ? ORDER BY TABLE_NAME', ['student_id'])),
            fn ($t) => $t !== 'student_conversion_logs'
        ));
        $rows = [];
        if ($studentIds) {
            foreach ($tables as $table) {
                $n = DB::table($table)->whereIn('student_id', $studentIds)->count();
                if ($n) $rows[$table] = $n;
            }
        }

        // the login, only when these conversions created it and no other student is on it
        $logins = [];
        foreach (collect($students)->pluck('student_user_id')->filter()->unique() as $loginId) {
            $login = DB::table('student_users')->where('id', $loginId)->first();
            $others = DB::table('students')->where('student_user_id', $loginId)->whereNotIn('id', $studentIds)->exists();
            if ($login && !$others && $since && $login->created_at && $login->created_at >= $since) {
                $logins[] = $login;
            }
        }
        $loginTables = array_values(array_filter(
            array_map(fn ($r) => $r->t, DB::select('SELECT TABLE_NAME t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = ?', ['student_user_id'])),
            fn ($t) => $t !== 'students'
        ));
        foreach ($logins as $login) {
            foreach ($loginTables as $table) {
                $n = DB::table($table)->where('student_user_id', $login->id)->count();
                if ($n) $rows[$table.' (login)'] = ($rows[$table.' (login)'] ?? 0) + $n;
            }
        }
        if ($studentIds) {
            $rows['students'] = count($studentIds);
        }

        $folders = ApplicantDocumentDisk::name() === 'local'
            ? array_values(array_filter(array_map(fn ($id) => 'public/students/'.$id, $studentIds), fn ($f) => Storage::disk('local')->exists($f)))
            : [];

        return [
            'students' => $students,
            'tables' => $tables,
            'rows' => $rows,
            'logins' => $logins,
            'loginTables' => $loginTables,
            'folders' => $folders,
            'logs' => (clone $logs)->count(),
            'google' => $this->option('keep-google') ? [] : $this->googlePlan($studentIds),
        ];
    }

    /**
     * The Google accounts StudentEmailProvisioner created for these students, each with
     * whether it will be deleted and why (not).
     *
     * @return array<int, array{email:string, delete:bool, note:string}>
     */
    private function googlePlan(array $studentIds): array
    {
        if (!$studentIds) {
            return [];
        }
        $marks = DB::table('student_archives')->whereIn('student_id', $studentIds)
            ->where('table', 'google_workspace')->where('field_name', 'account_created')->orderBy('id')->get();
        if ($marks->isEmpty()) {
            return [];
        }
        $directory = app(WorkspaceDirectory::class);
        $plan = [];
        foreach ($marks->unique('field_new_value') as $mark) {
            $email = (string) $mark->field_new_value;
            $inUse = DB::table('student_contacts')->where('institutional_email', $email)->whereNotIn('student_id', $studentIds)->exists()
                || DB::table('students')->join('student_users', 'student_users.id', '=', 'students.student_user_id')
                    ->where('student_users.email', $email)->whereNotIn('students.id', $studentIds)->exists();
            if ($inUse) {
                $plan[] = ['email' => $email, 'delete' => false, 'note' => 'kept: another student uses it'];
                continue;
            }
            if (!$directory->configured()) {
                $plan[] = ['email' => $email, 'delete' => false, 'note' => 'kept: Google Workspace is not connected here; delete it in the Admin console'];
                continue;
            }
            try {
                $user = $directory->getUser($email);
            } catch (\Throwable $e) {
                $plan[] = ['email' => $email, 'delete' => false, 'note' => 'kept: could not check it ('.$e->getMessage().')'];
                continue;
            }
            if ($user === null) {
                $plan[] = ['email' => $email, 'delete' => false, 'note' => 'already gone from Google'];
                continue;
            }
            // created by us for this student: Google's creation time is not before our mark (minus clock slack)
            $created = !empty($user['creationTime']) ? strtotime($user['creationTime']) : null;
            if ($created === null || $created < strtotime((string) $mark->created_at) - 600) {
                $plan[] = ['email' => $email, 'delete' => false, 'note' => 'kept: Google says it is older than this student, so it was not made for them'];
                continue;
            }
            $plan[] = ['email' => $email, 'delete' => true, 'note' => 'will be deleted (and its licence released)'];
        }

        return $plan;
    }
}
