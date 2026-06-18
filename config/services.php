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
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URL'),
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
    ],

    /*
    | External SMS sync — writes made by machine-to-machine (client credentials)
    | API callers, which have no authenticated user. created_by/updated_by on
    | those writes are stamped with this system user id. interview_task_list_id
    | is the task_lists row that represents the admission interview (id 7).
    */
    'sms_sync' => [
        'created_by' => (int) env('SMS_API_SYSTEM_USER_ID', 1),
        'interview_task_list_id' => (int) env('SMS_INTERVIEW_TASK_LIST_ID', 7),
    ],

];
