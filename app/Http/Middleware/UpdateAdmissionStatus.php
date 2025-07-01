<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Applicant;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;

class UpdateAdmissionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
                // Your logic to update admission status
        $completedApplicantIds = [];
        $applicants = Applicant::where('status_id', 3)->get();
        
        foreach ($applicants as $applicant) {
            
            $completedTasks = $applicant->allTasks->where('status', 'Completed');

            if (isset($completedTasks) && ($completedTasks->count() == $applicant->allTasks->count())) {
                
                    $completedApplicantIds[] = $applicant->id;
                
            }
        }
        if (!empty($completedApplicantIds)) {
            //FacadesDebugbar::info('Updating Admission Status for Applicants: ' . implode(', ', $completedApplicantIds));
            Applicant::whereIn('id', $completedApplicantIds)->update(['status_id' => 4]);
        }
        return $next($request);
    }
}
