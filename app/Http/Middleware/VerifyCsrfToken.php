<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        /* Server-to-server callback from PayPal: no session, no CSRF token.
           Authenticated by verifying the transmission signature instead
           (PayPalClient::verifyWebhook). */
        'paypal/webhook',
    ];
}
