<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceInformation;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\TermDeclaration;
use App\Models\User;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index($id){
        $userData = User::find($id);
        $employee = Employee::where("user_id", $userData->id)->get()->first();

        $latestTerm = Plan::where('personal_tutor_id', $id)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $latestTermId = (isset($latestTerm->term_declaration_id) && $latestTerm->term_declaration_id > 0 ? $latestTerm->term_declaration_id : 0);
        $theTermDeclaration = TermDeclaration::find($latestTermId);
        $modules = Plan::where('term_declaration_id', $latestTermId)->where('personal_tutor_id', $id)->orderBy('id', 'ASC')->get();
        $plan_ids = $modules->pluck('id')->unique()->toArray();
        $assigns = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
            $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
        })->distinct()->count('student_id');

        
        
        $today = date('Y-m-d');
        return  view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,

            'current_term' => $theTermDeclaration,
            'modules' => $modules,
            'no_of_assigned' => $assigns,
            'todays_classes' => PlansDateList::where('date', date('Y-m-d'))->whereHas('plan', function($q) use($id){
                                    $q->where('personal_tutor_id', $id);
                                })->orderBy('id', 'ASC')->get(),
        ]);
    }

}
