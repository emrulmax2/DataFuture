<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'ios_client_id' => env('GOOGLE_STUDENT_IOS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'google_student' => [
        'client_id' => env('GOOGLE_STUDENT_CLIENT_ID'),
        'client_secret' => env('GOOGLE_STUDENT_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_STUDENT_REDIRECT_URL'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URL'),
        // Swapped in by the student login controller. Read here, not with
        // env() in the controller: once config is cached, env() is null.
        'student_redirect' => env('MICROSOFT_STUDENT_REDIRECT_URL'),
        'tenant' => env('MICROSOFT_TENANT', 'organizations'),
    ],

    // Other configurations...

    'google_books' => [
        'api_key' => env('GOOGLE_BOOKS_API_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'sandbox' => env('PAYPAL_SANDBOX', true),

        // From the PayPal dashboard's webhook you point at /paypal/webhook.
        // Without it the webhook cannot be verified and is refused.
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    /*
     * Google Maps / Places, used by the address lookup on the student and
     * applicant forms.
     *
     * Read through config, never with env() in a view: once `php artisan
     * config:cache` has run — which every deploy does — Laravel stops loading
     * .env and env() returns its default. The key then rendered as the literal
     * "YOUR_API_KEY" and Google answered InvalidKeyMapError, which is why the
     * lookup worked locally (no cache) and failed on the server.
     */
    'google_maps' => [
        'key' => env('GOOGLE_MAP_API'),
    ],

    'library' => [
        /*
         * Where every library notification goes. The desk is a room, not a user
         * in this system, so it is addressed by configuration rather than
         * looked up.
         *
         * The fallback is deliberate and TEMPORARY: while the library is being
         * tested on live, production sends here too rather than depending on an
         * env var nobody has set yet — an unset address means no mail at all,
         * and a reservation nobody is told about is worse than one landing in
         * the wrong inbox. Replace the default the moment the real library
         * mailbox is known; `LIBRARY_MANAGER_EMAIL` already overrides it
         * without a code change.
         */
        'manager_email' => env('LIBRARY_MANAGER_EMAIL', 'limon@lcc.ac.uk'),
        'manager_name' => env('LIBRARY_MANAGER_NAME', 'Library desk'),
    ],




    /*
    |--------------------------------------------------------------------------
    | LCC Operations
    |--------------------------------------------------------------------------
    | Budget Management now lives in the Operations system. Transactions settled
    | against a requisition raised there link back to it from the accounts
    | screens, so this is the base URL those links are built from.
    */
    'operations' => [
        'url' => env('OPERATIONS_BASE_URL', 'https://operations.lcc.ac.uk'),

        // Shared secret presented as X-Operations-Key when reading a requisition.
        'api_key' => env('OPERATIONS_API_KEY'),
        'timeout' => (int) env('OPERATIONS_API_TIMEOUT', 10),
        'verify_tls' => env('OPERATIONS_API_VERIFY_TLS', true),

        /*
         * Service Desk tickets raised from the student portal.
         *
         * A student submitting an in-portal form opens a ticket in Operations;
         * these say where it lands. Registry (19) answers course changes under
         * its "Student Course Change Request" issue type (55). Both are ids in
         * the Operations database, so they are configurable rather than
         * hard-coded — and both must be switched on for students there before
         * a ticket can be raised at all.
         */
        'service_desk' => [
            'course_change' => [
                'department_id' => (int) env('OPERATIONS_SD_COURSE_CHANGE_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_COURSE_CHANGE_ISSUE_TYPE', 55),
            ],

            /* Discontinuations go to the same queue as course changes for now;
               they get their own issue type the moment Registry adds one, and
               only this value changes. */
            'discontinuation' => [
                'department_id' => (int) env('OPERATIONS_SD_DISCONTINUATION_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_DISCONTINUATION_ISSUE_TYPE', 55),
            ],

            /* Refunds will belong to Finance rather than Registry once they
               have an issue type of their own; until then they follow the
               others, and only these two values change. */
            'refund' => [
                'department_id' => (int) env('OPERATIONS_SD_REFUND_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_REFUND_ISSUE_TYPE', 55),
            ],

            'academic_appeal' => [
                'department_id' => (int) env('OPERATIONS_SD_APPEAL_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_APPEAL_ISSUE_TYPE', 55),
            ],

            'mitigating_circumstances' => [
                'department_id' => (int) env('OPERATIONS_SD_MITIGATING_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_MITIGATING_ISSUE_TYPE', 55),
            ],

            /* The attendance claim is routed separately from the assignment
               one, even though both go to the same place today. */
            /* IT & Monitoring answers for many kinds of request, so this form
               has no fixed issue type: the student picks one from the types
               that department has opened to students, and the id travels with
               the request. Only the department is configured here. */
            'it_support' => [
                'department_id' => (int) env('OPERATIONS_SD_IT_DEPARTMENT', 14),
                'issue_type_id' => null,
            ],

            'complaint' => [
                'department_id' => (int) env('OPERATIONS_SD_COMPLAINT_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_COMPLAINT_ISSUE_TYPE', 55),
            ],

            'mitigating_attendance' => [
                'department_id' => (int) env('OPERATIONS_SD_MITIGATING_ATTENDANCE_DEPARTMENT', 19),
                'issue_type_id' => (int) env('OPERATIONS_SD_MITIGATING_ATTENDANCE_ISSUE_TYPE', 55),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | College Registry
    |--------------------------------------------------------------------------
    | Who a student is told to contact about a request they did not make. The
    | phone number is the college's own switchboard, as held in Site Settings.
    */
    'registry' => [
        'email' => env('REGISTRY_EMAIL', 'registry@lcc.ac.uk'),
        'phone' => env('REGISTRY_PHONE', '020 7377 1077'),
    ],

];
