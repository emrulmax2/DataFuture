<?php

namespace App\Console\Commands;

use App\Models\PolicyAssignment;
use App\Services\PolicyAssessmentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Emails each member of staff one reminder listing their policy tests that
 * are due within the next few days or already overdue.
 *
 * A reminder is only sent for a test the person can actually do something
 * about — one they can start or resume — so nobody is chased about a test
 * with no questions yet or no attempts left. Each assignment is reminded at
 * most once every few days (last_reminded_at), so a daily schedule does not
 * turn into a daily email about the same overdue test.
 *
 * Each test is listed with its level (Beginner / Intermediate / Expert) — the
 * email's Level column comes from PolicyAssessmentService — since the same
 * policy can be assigned at more than one level.
 */
class PolicyAssessmentReminderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'policyassessmentreminder:cron
                            {--dry-run : List who would be reminded, without sending anything or marking reminders as sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email staff about policy assessments that are due soon or overdue.';

    /**
     * Execute the console command.
     */
    public function handle(PolicyAssessmentService $service)
    {
        $dryRun = (bool) $this->option('dry-run');
        $window = PolicyAssessmentService::REMINDER_WINDOW_DAYS;
        $dueBy = Carbon::today()->addDays($window)->format('Y-m-d');
        /* By calendar day, not to the second: the run that set last_reminded_at
           stamped it a moment after it started, so comparing timestamps would
           push every "3-day" reminder to day 4. */
        $remindedOnOrBefore = Carbon::today()->subDays($window)->format('Y-m-d');

        /* Timed tests abandoned mid-way are marked first, so a reminder never
           describes a test as "in progress" hours after its clock ran out. */
        if(!$dryRun):
            $service->finaliseExpiredAttempts();
        endif;

        if(!$dryRun && $service->mailConfiguration() === null):
            Log::warning('Policy assessment reminders not sent: no SMTP configuration found.');
            $this->warn('Policy assessment reminders: no SMTP configuration found, nothing sent.');
            return 0;
        endif;

        $assignments = PolicyAssignment::with(['policy.category', 'employee.employment', 'openAttempt'])
            ->where('status', '!=', PolicyAssignment::STATUS_PASSED)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $dueBy)
            /* Active policy, category not archived — the same "live" rule as
               the staff list, so nobody is chased about a test they cannot open. */
            ->live()
            ->whereHas('employee', function ($query) {
                $query->where('status', 1);
            })
            ->where(function ($query) use ($remindedOnOrBefore) {
                $query->whereNull('last_reminded_at')->orWhereDate('last_reminded_at', '<=', $remindedOnOrBefore);
            })
            ->orderBy('employee_id', 'ASC')
            ->orderBy('due_date', 'ASC')
            ->get()
            ->filter(function ($assignment) {
                return $assignment->openAttempt !== null || $assignment->canAttempt();
            });

        $staffCount = 0;
        $assignmentCount = 0;
        $skipped = 0;

        foreach($assignments->groupBy('employee_id') as $employeeId => $group):
            $employee = $group->first()->employee;
            $email = $service->employeeEmail($employee);
            $titles = $group->map(function ($assignment) {
                return $assignment->policy->title.' ('.$assignment->level_label.', due '.$assignment->due_date->format('d M Y').')';
            })->implode('; ');

            if($email === null):
                $skipped += 1;
                $this->warn('No email address for employee #'.$employeeId.' '.$employee->full_name.', skipped.');
                continue;
            endif;

            if($dryRun):
                $this->line('Would remind '.$employee->full_name.' <'.$email.'>: '.$titles);
                $staffCount += 1;
                $assignmentCount += $group->count();
                continue;
            endif;

            if($service->sendReminderEmail($employee, $group->values())):
                PolicyAssignment::whereIn('id', $group->pluck('id')->all())->update(['last_reminded_at' => Carbon::now()]);
                $staffCount += 1;
                $assignmentCount += $group->count();
            else:
                $skipped += 1;
            endif;
        endforeach;

        $this->info(
            'Policy assessment reminders: '.$staffCount.' staff '.($dryRun ? 'would be emailed' : 'emailed')
            .' about '.$assignmentCount.' assignments'
            .($skipped > 0 ? ', '.$skipped.' skipped' : '')
            .($dryRun ? ' (dry run, nothing sent).' : '.')
        );

        return 0;
    }
}
