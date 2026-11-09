<?php

/*
 * Student @lcc.ac.uk accounts, created right after a new student's
 * "Student Email Creation and Notification" task is assigned
 * (App\Jobs\TaskAssignment\CreateStudentEmailJob).
 */
return [

    /* The address: lower-case registration number at this domain,
       e.g. lcc20270002@lcc.ac.uk (the same rule as the Task Manager export). */
    'domain' => env('STUDENT_EMAIL_DOMAIN', 'lcc.ac.uk'),

    /* Put in front of the address, e.g. "test." on a local install: local
       registration numbers are the ones production hands out next, so a local
       test must never create (or later delete) a real student's account. */
    'address_prefix' => env('STUDENT_EMAIL_ADDRESS_PREFIX', ''),

    /* The first Google password is the student's date of birth as DDMMYYYY
       (user, 26 Oct 2026: DOB 26-11-1986 → 26111986), see
       StudentEmailProvisioner::initialPassword(). This value is used only for
       a student with no date of birth. Google needs 8+ characters. */
    'initial_password' => env('STUDENT_EMAIL_INITIAL_PASSWORD'),

    /* Google makes the student choose their own password at first sign-in.
       Keep this on while the first password is shared or guessable. */
    'change_at_first_login' => (bool) env('STUDENT_EMAIL_CHANGE_AT_FIRST_LOGIN', true),

    /* The account details email and the welcome letter go to the student's own
       addresses even where MAIL_REDIRECT_TO diverts other mail (a local install).
       Google cannot send the sign-in details itself: its Admin console option has
       no API equivalent, so this app sends them (user, 26 Oct 2026). */
    'ignore_mail_redirect' => (bool) env('STUDENT_EMAIL_IGNORE_MAIL_REDIRECT', false),

    /* Google Workspace Admin SDK (Directory API): a service account with
       domain-wide delegation for the admin.directory.user scope, acting as a
       Workspace super admin. Until both are set, the account is not created
       in Google and the task stays Pending for staff to finish by hand. */
    'google' => [
        'credentials' => env('GOOGLE_WORKSPACE_CREDENTIALS'),   // path to the service-account JSON key
        'admin_email' => env('GOOGLE_WORKSPACE_ADMIN_EMAIL'),   // the super admin it acts as
        'org_unit' => env('GOOGLE_WORKSPACE_STUDENT_OU', '/'), // e.g. /Students
        /* The licence to assign (Enterprise License Manager API, scope
           apps.licensing). Leave empty when Google assigns licences itself. */
        'licence_product' => env('GOOGLE_WORKSPACE_LICENCE_PRODUCT', ''),
        'licence_sku' => env('GOOGLE_WORKSPACE_LICENCE_SKU', ''),
    ],

    /* The two tasks are found by their rule's action (Task Automation > "After
       assigning, run"), not by task list id, which differs between installs.
    'email_task_list_id' => (int) env('STUDENT_EMAIL_TASK', 10),
    'welcome_task_list_id' => (int) env('STUDENT_WELCOME_TASK', 13), */

    /* The welcome letter ("General communication - Welcome Message to Students")
       and its signatory, sent once the email account exists, as staff send it
       from the student's Communication page. */
    'welcome_letter_set_id' => (int) env('STUDENT_EMAIL_WELCOME_LETTER', 116),
    'welcome_signatory_id' => (int) env('STUDENT_EMAIL_WELCOME_SIGNATORY', 23),
];
