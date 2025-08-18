<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LoggedIn
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        if (!is_null(request()->user())) {
            return redirect('/');
        }else if (!is_null(Auth::guard('student')->user())) {
            
            return redirect()->route('students.login');

        }else if (!is_null(Auth::guard('applicant')->user())) {
            
            return redirect()->route('applicant.login');

        }else if (!is_null(Auth::guard('agent')->user())) {
            
            return redirect()->route('agent.login');

        }  else {
            return $next($request);
        }
    }
}
