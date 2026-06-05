<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeAuthorizationHeader
{
    /**
     * Normalize Authorization header from common proxy/server variables.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->headers->has('authorization')) {
            $fallback = $request->server('HTTP_AUTHORIZATION')
                ?? $request->server('REDIRECT_HTTP_AUTHORIZATION')
                ?? $request->server('X_FORWARDED_AUTHORIZATION');

            if (!empty($fallback)) {
                $request->headers->set('authorization', $fallback);
            }
        }

        return $next($request);
    }
}
