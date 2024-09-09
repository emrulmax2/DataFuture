<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;

use Closure;

class Authenticate
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
            return $next($request);
        }else if (!is_null(Auth::guard('student')->user())) {
            
            return redirect()->route('students.login');

        }  else {
            return redirect('login');
        }
    }
}
