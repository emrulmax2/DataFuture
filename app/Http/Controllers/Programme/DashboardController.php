<?php

namespace App\Http\Controllers\Programme;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(){
        $theDate = Date('Y-m-d'); //'2023-11-24';
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $usedCourses = Plan::pluck('course_id')->unique()->toArray();

        return view('pages.programme.dashboard.index', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theTerm' => Plan::whereIn('id', $classPlanIds)->orderBy('id', 'DESC')->get()->first(),
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'classInformation' => $this->getClassInfoHtml($theDate),
            'classTutor' => $this->getClassTutorsHtml($theDate),
            'classPTutor' => $this->getClassPersonalTutorsHtml($theDate),
            'absentToday' => $this->getAbsentEmployees(date('Y-m-d')),
        ]);
    }

    public function getClassInformations(Request $request){
        $planClassStatus = $request->planClassStatus;
        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
        $theClassDate = (isset($request->theClassDate) && !empty($request->theClassDate) ? date('Y-m-d', strtotime($request->theClassDate)) : date('Y-m-d'));

        $res = [];
        $res['planTable'] = $this->getClassInfoHtml($theClassDate, $planCourseId);

        return response()->json(['res' => $res], 200);
    }

    public function getClassInfoHtml($theDate = null, $course_id = 0){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();

        $html = '';
        $query = Plan::with('tutor')->whereIn('id', $classPlanIds); 
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $query = $query->orderBy('start_time', 'ASC')->get();

        if(!empty($query) && $query->count() > 0):
            foreach($query as $pln):
                $tutorEmployeeId = (isset($pln->tutor->employee->id) && $pln->tutor->employee->id > 0 ? $pln->tutor->employee->id : 0);
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $tutorEmployeeId)->where('date', $theDate)->where('attendance_type', 1)->get();
                $html .= '<tr class="intro-x">';
                    $html .= '<td>';
                        $html .= '<span class="font-fedium">'.date('H:i', strtotime($theDate.' '.$pln->start_time)).'</span>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<div class="flex items-center">';
                            if(isset($pln->group->name) && !empty($pln->group->name)):
                                if(strlen($pln->group->name) > 2):
                                    $html .= '<div class="mr-4 rounded text-lg bg-success whitespace-nowrap text-white cursor-pointer font-medium w-auto px-2 py-1 h-auto inline-flex justify-center items-center">'.$pln->group->name.'</div>';
                                else:
                                    $html .= '<div class="mr-4 rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.$pln->group->name.'</div>';
                                endif;
                            endif;
                            $html .= '<div>';
                                $html .= '<a href="" class="font-medium whitespace-nowrap">'.(isset($pln->creations->module->name) && !empty($pln->creations->module->name) ? $pln->creations->module->name : 'Unknown').(isset($plan->class_type) && !empty($plan->class_type) ? ' - '.$plan->class_type : '').'</a>';
                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->course->name) && !empty($pln->course->name) ? $pln->course->name : 'Unknown').'</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                        $html .= '<div class="flex justify-start items-center">';
                            $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                $html .= '<img src="'.(isset($pln->tutor->employee->photo_url) ? $pln->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->tutor->employee->full_name) ? $pln->tutor->employee->full_name : 'LCC').'">';
                            $html .= '</div>';
                            $html .= '<div class="inline-block font-medium relative">';
                                $html .= (isset($pln->tutor->employee->full_name) ? $pln->tutor->employee->full_name : $pln->tutor->name);
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= (isset($pln->room->name) && !empty($pln->room->name) ? $pln->room->name : '');
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= '<span class="font-medium text-danger">Starting Shortly</span>';
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan found for the selected date.</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return $html;
    }

    public function getClassTutorsHtml($theDate = null){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $termDecs = Plan::whereIn('id', $classPlanIds)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);
        
        $query = Plan::where('term_declaration_id', $termDecId);
        $classTutors = $query->pluck('tutor_id')->unique()->toArray();
        
        $html = '';
        $uttors = User::whereIn('id', $classTutors)->skip(0)->take(5)->get();
        if(!empty($uttors) && $uttors->count() > 0):
            foreach($uttors as $tut):
                $moduleCreations = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->pluck('module_creation_id')->unique()->toArray();
                $html .= '<div class="intro-x">';
                    $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
                        $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                            $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                        $html .= '</div>';
                        $html .= '<div class="ml-4 mr-auto">';
                            $html .= '<div class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</div>';
                        $html .= '</div>';
                        $html .= '<div class="text-white rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.(!empty($moduleCreations) ? count($moduleCreations) : 0).'</div>';
                    $html .= '</div>';
                $html .= '</div>';
            endforeach;
        else:
            $html .= '<div class="intro-x">';
                $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan tutor found for the selected date.</div>';
            $html .= '</div>';
        endif;

        return array('count' => (!empty($classTutors) ? count($classTutors) : 0), 'html' => $html);
    }

    public function getClassPersonalTutorsHtml($theDate = null){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $termDecs = Plan::whereIn('id', $classPlanIds)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);

        $query = Plan::where('term_declaration_id', $termDecId);
        $classTutors = $query->pluck('personal_tutor_id')->unique()->toArray();

        $html = '';
        $uttors = User::whereIn('id', $classTutors)->skip(0)->take(5)->get();
        if(!empty($uttors) && $uttors->count() > 0):
            foreach($uttors as $tut):
                $moduleCreations = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->pluck('module_creation_id')->unique()->toArray();
                $html .= '<div class="intro-x">';
                    $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
                        $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                            $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                        $html .= '</div>';
                        $html .= '<div class="ml-4 mr-auto">';
                            $html .= '<div class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</div>';
                        $html .= '</div>';
                        $html .= '<div class="text-white rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.(!empty($moduleCreations) ? count($moduleCreations) : 0).'</div>';
                    $html .= '</div>';
                $html .= '</div>';
            endforeach;
        else:
            $html .= '<div class="intro-x">';
                $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan tutor found for the selected date.</div>';
            $html .= '</div>';
        endif;

        return array('count' => (!empty($classTutors) ? count($classTutors) : 0), 'html' => $html);
    }

    public function getAbsentEmployees($date = ''){
        $theDate = (empty($date) ? date('Y-m-d') : $date);
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');
        $employees = Employee::where('status', 1)->orderBy('first_name', 'ASC')->get();

        $row = 0;
        $res = [];
        foreach($employees as $employee):
            if($row > 5): 
                break; 
            endif;

            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employeeLeaveDay = EmployeeLeaveDay::where('status', 'Active')
                                    ->where('leave_date', $theDate)
                                    ->whereHas('leave', function($q) use($employee_id){
                                        $q->where('employee_id', $employee_id)->where('status', 'Approved');
                                    })
                                    ->get()->first();
                $leave_status = (isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0 && isset($employeeLeaveDay->leave->status) && $employeeLeaveDay->leave->status == 'Approved' ? true : false);

                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $theDate)
                                         ->where(function($query) use($theDate){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                                         })->get()->first();
                $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $theDayNum)->get()->first();
                $day_status = (isset($patternDay->id) && $patternDay->id > 0 ? true : false);
                if($day_status && !$leave_status):
                    $todayAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
                    if($todayAttendance->count() == 0 && $patternDay->start <= $time):
                        $res[$employee_id]['photo_url'] = $employee->photo_url;
                        $res[$employee_id]['full_name'] = $employee->full_name;
                        $res[$employee_id]['date'] =  date('jS M, Y', strtotime($theDate));
                        $res[$employee_id]['hourMinute'] =  $patternDay->total;
                        $res[$employee_id]['minute'] =  $this->convertStringToMinute($patternDay->total);

                        $row += 1;
                    endif;
                endif;
            endif;
        endforeach;

        return $res;
    }


    public function convertStringToMinute($string){
        $min = 0;
        $str = explode(':', $string);

        $min += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $min += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        return $min;
    }

    function calculateHourMinute($minutes){
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
        
        return $hourMins;
    }


    public function tutors($term_declaration_id){
        $tutorIds = Plan::where('term_declaration_id', $term_declaration_id)->pluck('tutor_id')->unique()->toArray();

        $res = [];
        $tutors = User::whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        if(!empty($tutors)):
            foreach($tutors as $tut):
                $moduleCreations = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->pluck('module_creation_id')->unique()->toArray();
                $tut['no_of_module'] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $res[$tut->id] = $tut;
                $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 1);
            endforeach;
        endif;
        
        return view('pages.programme.dashboard.tutors', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'tutors' => $res
        ]);
    }

    public function tutorsDetails($term_declaration_id, $tutorid){
        $plans = [];
        $tutorPlans = Plan::where('term_declaration_id', $term_declaration_id)->where('tutor_id', $tutorid)->get();
        if($tutorPlans->count() > 0):
            foreach($tutorPlans as $tp):
                $plans[$tp->id] = $tp;
                $plans[$tp->id]['attendances'] = $this->getPlanAttendanceRate($tp->id);
            endforeach;
        endif;
        
        return view('pages.programme.dashboard.tutors-details', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'p_tutor_id' => $tutorid,
            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'tutor' => User::find($tutorid),
            'plans' => $plans
        ]);
    }


    public function personalTutors($term_declaration_id){
        $tutorIds = Plan::where('term_declaration_id', $term_declaration_id)->pluck('personal_tutor_id')->unique()->toArray();

        $res = [];
        $tutors = User::whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        if(!empty($tutors)):
            foreach($tutors as $tut):
                $moduleCreations = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->pluck('module_creation_id')->unique()->toArray();
                $tut['no_of_module'] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $res[$tut->id] = $tut;
                $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 2);
            endforeach;
        endif;

        return view('pages.programme.dashboard.personal-tutors', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'tutors' => $res
        ]);
    }


    public function personalTutorDetails($term_declaration_id, $tutorid){
        $plans = [];
        $tutorPlans = Plan::where('term_declaration_id', $term_declaration_id)->where('personal_tutor_id', $tutorid)->get();
        if($tutorPlans->count() > 0):
            foreach($tutorPlans as $tp):
                $plans[$tp->id] = $tp;
                $plans[$tp->id]['attendances'] = $this->getPlanAttendanceRate($tp->id);
            endforeach;
        endif;

        return view('pages.programme.dashboard.personal-tutors-details', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'p_tutor_id' => $tutorid,
            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'termDeclarations' => TermDeclaration::orderBy('id', 'desc')->get(),
            'tutor' => User::find($tutorid),
            'plans' => $plans
        ]);
    }

    public function getTermAttendanceRate($term_declaration_id, $tutor_id, $type = 1){
        $tutor_field = ($type == 2 ? 'personal_tutor_id' : 'tutor_id');
        $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where($tutor_field, $tutor_id)->pluck('id')->unique()->toArray();
        $student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : []);
        $query = DB::table('attendances as atn')
                    ->select(
                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->whereIn('plan_id', $plan_ids);
        if(!empty($student_ids)):
            $query->whereIn('student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }

    public function getPlanAttendanceRate($plan_id){
        $student_ids = Assign::where('plan_id', $plan_id)->pluck('student_id')->unique()->toArray();
        $query = DB::table('attendances as atn')
                    ->select(
                        DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->where('plan_id', $plan_id);
        if(!empty($student_ids)):
            $query->whereIn('student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }
}
