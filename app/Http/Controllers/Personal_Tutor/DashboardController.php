<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceInformation;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Student;
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


    public function getClassess(Request $request){
        $personalTutorId = (isset($request->personalTutorId) && $request->personalTutorId > 0 ? $request->personalTutorId : 0);
        $plan_date = (isset($request->plan_date) && !empty($request->plan_date) ? date('Y-m-d', strtotime($request->plan_date)) : '');

        $html = '';
        if(!empty($plan_date) && $personalTutorId > 0):
            $classes = PlansDateList::where('date', $plan_date)->whereHas('plan', function($q) use($personalTutorId){
                        $q->where('personal_tutor_id', $personalTutorId);
                    })->orderBy('id', 'ASC')->get();
            if($classes->count() > 0):
                foreach($classes as $class):
                    $html .= '<div class="intro-x relative flex items-center mb-3">';
                        $html .= '<div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">';
                            $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                                $html .= '<img alt="'.(isset($class->plan->tutor->employee->full_name) && !empty($class->plan->tutor->employee->full_name) ? $class->plan->tutor->employee->full_name : 'London Churchill College').'" src="'.(isset($class->plan->tutor->employee->photo_url) && !empty($class->plan->tutor->employee->photo_url) ? $class->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                            $html .= '</div>';
                        $html .= '</div>';
                        $html .= '<div class="box px-5 py-3 ml-4 flex-1 bg-warning-soft zoom-in">';
                            $html .= '<div class="flex items-center">';
                                $html .= '<div class="font-medium">'.$class->plan->creations->module_name.' ('. $class->plan->group->name.')</div>';
                                $html .= '<div class="text-xs text-slate-500 ml-auto">'.(isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '').'</div>';
                            $html .= '</div>';
                            $html .= '<div class="text-slate-500 mt-1">'.(isset($class->plan->course->name) ? $class->plan->course->name : '').'</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                endforeach;
            else:
                $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No Class found for the day.
                      </div>';
            endif;
        else:
            $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No Class found for the day.
                      </div>';
        endif;

        return response()->json(['res' => $html], 200);
    }


    public function searchStudent(Request $request){
        $SearchVal = $request->SearchVal;

        $html = '';
        $Query = Student::orderBy('registration_no', 'ASC')->where('registration_no', 'LIKE', '%'.$SearchVal.'%')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="'.route('student.show', $qr->id).'" data-label="'.$qr->registration_no.' - '.$qr->full_name.'" class="dropdown-item">'.$qr->registration_no.' - '.$qr->full_name.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" data-lable="Nothing found!" class="dropdown-item disable">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

}
