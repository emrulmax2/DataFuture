<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        if (!is_null(\Auth::guard('agent')->user())) {
            return redirect('/agent/dashboard');
        } else {
            return $next($request);
        }
    }
}
