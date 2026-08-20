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
    ],

];
