<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApplicantViewUnlock;
class EnsureExpiredDateIsValid
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
        $applicantToken = ApplicantViewUnlock::where(['token'=>$request->route('token'),'applicant_id'=>$request->route('id')])->orderBy('expired_at','desc')->get()->first();

        if ($applicantToken && (strtotime($applicantToken->expired_at) > strtotime(now()))) {
            
            return $next($request);
        }
        return redirect(route('dashboard',\Auth::id()));
    }
}
