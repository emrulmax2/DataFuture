<?php

/*
 * Automatic task assignment (Site settings > Task Automation).
 */
return [

    /* Who automated rows are recorded as created by. The project's other
       automated services use user 1 for this. */
    'system_user_id' => (int) env('TASK_SYSTEM_USER_ID', 1),

    /* live: create tasks on submission. shadow: evaluate and log a dry run,
       create nothing - for comparing the rules against staff decisions. */
    'mode' => env('TASK_ASSIGNMENT_MODE', 'live'),

    /* A manual assignment also moves the applicant to 3 "In Progress". The
       engine leaves them at 2 "New" by default so the admissions queue still
       shows fresh applications; turn this on to mirror the manual behaviour. */
    'advance_status' => (bool) env('TASK_ASSIGNMENT_ADVANCE_STATUS', false),

    /* Jobs a rule may run after it assigns its task (Site settings > Task
       Automation > "After assigning, run"). Class => label. Only classes
       listed here can be chosen or dispatched. */
    'assignment_jobs' => [
        \App\Jobs\TaskAssignment\SendESignatureRequestJob::class => 'Send e-signature request (email + SMS)',
    ],

    /* Applicant rule actions that run when staff mark the rule's task Completed (process
       board or task manager), not when it is assigned (user, 27 Oct 2026). Same column
       (after_assign_job), same constructor: new Job(applicant id, applicant task id, rule id).
       They run inline, so the result shows in the completion message. */
    'completion_jobs' => [
        \App\Jobs\TaskAssignment\UpdateApplicantPhotoJob::class => 'Update student photo: the latest image uploaded to the task becomes the applicant photo (carried to the student)',
    ],

    /* Student rules (triggers "Student created" / "Student status changed"):
       live creates the tasks, shadow only evaluates and logs the run. */
    'student_mode' => env('TASK_STUDENT_ASSIGNMENT_MODE', 'live'),

    /* Jobs a student rule may run after it assigns its task. Constructed as
       new Job(student id, student task id, rule id). */
    'student_assignment_jobs' => [
        \App\Jobs\TaskAssignment\CreateStudentEmailJob::class => 'Create the student @lcc.ac.uk email and complete the task',
        \App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob::class => 'Send the welcome letter to the institutional and personal email (after the student email is created) and complete the task',
        \App\Jobs\TaskAssignment\UpdateStudentStatusToSlcNewJob::class => 'Change student status to SLC New and complete the task, once every other task in its process is done',
        \App\Jobs\TaskAssignment\SendInductionInvitationJob::class => 'Send the induction invitation email with the course\'s booking link and complete the task',
    ],

    /* Student actions that complete their task while the applicant is moved to students: a
       "Student created" rule with one of these shows Auto Complete = Yes on Task Automation ›
       Student (user, 30 Oct 2026). Checked by hand against the jobs and the dev conversions:
       the email account is created, then the welcome letter and the induction invitation go
       out straight after it (CreateStudentEmailJob runs both). Not listed: the SLC New status
       change, which waits until every other task of the process is done, and rules with no
       action, whose tasks staff complete. An action added later goes here only if it too
       completes its task during the conversion. */
    'conversion_auto_complete_jobs' => [
        \App\Jobs\TaskAssignment\CreateStudentEmailJob::class,
        \App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob::class,
        \App\Jobs\TaskAssignment\SendInductionInvitationJob::class,
    ],

    /* The induction invitation (SendInductionInvitationJob). The booking link itself is
       per course — Course Creation > Induction Booking Link — and reaches the wording
       through [DATA=course_creations]induction_booking_button[/DATA]. */
    'induction' => [
        'letter_set_id' => (int) env('INDUCTION_LETTER_SET_ID', 55), // the first, letter-set version (no longer used)
        'signatory_id' => (int) env('INDUCTION_SIGNATORY_ID', 23),   // the first, letter-set version (no longer used)
        // the email template's "Registration: 5th floor" (user, 30 Oct 2026); empty leaves the floor out
        'registration_floor' => env('INDUCTION_REGISTRATION_FLOOR', '5th floor'),
    ],

    /* The status UpdateStudentStatusToSlcNewJob sets, by name (ids differ between installs). */
    'slc_new_status' => env('TASK_SLC_NEW_STATUS', 'SLC New'),

    /* sync: run those jobs inline when the task is created. queue: dispatch
       them to the queue (needs a worker on the database driver). */
    'jobs_run' => env('TASK_ASSIGNMENT_JOBS', 'sync'),

    /* Who an automated e-signature request is sent as. The manual button
       keeps auth()->user(); this is the fallback when nobody is signed in. */
    'esignature_user_id' => (int) env('ESIGNATURE_RESPONSIBLE_USER_ID', 1),
];
