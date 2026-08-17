<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identity provider
    |--------------------------------------------------------------------------
    |
    | This application is the SSO server. Relying apps send the browser to
    | GET /sso/authorize and redeem the returned ticket at POST /api/sso/token.
    |
    */

    'server_enabled' => (bool) env('SSO_SERVER_ENABLED', false),

    /*
     | Guard whose session decides whether the visitor is already signed in.
     | Only staff (App\Models\User) take part in SSO - students, applicants
     | and agents have their own guards and are never issued tickets.
     */
    'guard' => env('SSO_SERVER_GUARD', 'web'),

    /*
     | Named route the visitor is sent to when they need to sign in first.
     */
    'login_route' => env('SSO_SERVER_LOGIN_ROUTE', 'login.index'),

    /*
     | Seconds a ticket stays redeemable. Tickets are single use; this only
     | bounds the window between the browser redirect and the back-channel
     | redemption, so it should stay small.
     */
    'ticket_ttl' => (int) env('SSO_TICKET_TTL', 60),

    /*
     | Cache store holding in-flight tickets. Kept separate from the default
     | store because sign-out flushes that one wholesale.
     */
    'ticket_store' => env('SSO_TICKET_STORE', 'sso'),

    /*
     | Push a back-channel logout to every client when a staff user signs out
     | of this application.
     */
    'broadcast_logout' => (bool) env('SSO_BROADCAST_LOGOUT', true),

    'logout_timeout' => (int) env('SSO_LOGOUT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Relying applications
    |--------------------------------------------------------------------------
    |
    | The secret must match SSO_CLIENT_SECRET in the client application.
    | Redirect URIs are matched exactly - list every environment.
    | Domains restricts which email addresses this client may receive.
    |
    */

    'clients' => [

        'lcc-operations' => [
            'name'       => 'LCC Operations Management',
            'secret'     => env('SSO_CLIENT_LCC_OPERATIONS_SECRET'),
            'redirects'  => env('SSO_CLIENT_LCC_OPERATIONS_REDIRECTS', ''),
            'domains'    => env('SSO_CLIENT_LCC_OPERATIONS_DOMAINS', ''),
            'logout_url' => env('SSO_CLIENT_LCC_OPERATIONS_LOGOUT_URL'),
        ],

    ],

];
