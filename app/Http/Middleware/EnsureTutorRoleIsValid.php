<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
class EnsureTutorRoleIsValid
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
        $User = User::find(\Auth::id());
        foreach ($User->roles as $role) {
            # code...
            if($role->type=="Tutor" || $role->type =="Admin") {
                return $next($request);    
            }
        }
        
        return redirect(route('useraccess',\Auth::id()));
    }
}
