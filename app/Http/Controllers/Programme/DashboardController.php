<?php

namespace App\Http\Controllers\Programme;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelClassRequest;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Option;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Student;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SendSmsTrait;
use DateTime;

class DashboardController extends Controller
{
    use SendSmsTrait;

    public function index(){
        $theDate = Date('Y-m-d'); //'2023-11-24';
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $usedCourses = Plan::pluck('course_id')->unique()->toArray();

        return view('pages.programme.dashboard.index', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theTerm' => Plan::whereIn('id', $classPlanIds)->orderBy('term_declaration_id', 'DESC')->get()->first(),
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

        $html = '';
        /*$classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $query = Plan::with('tutor')->whereIn('id', $classPlanIds); 
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $query = $query->orderBy('start_time', 'ASC')->get();*/

        $plans = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('date', $theDate)->whereHas('plan', function($q) use($course_id){
            if($course_id > 0):
                $q->where('course_id', $course_id);
            endif;
        })->get()->sortBy(function($planDates, $key) {
            return $planDates->plan->start_time;
        });

        if(!empty($plans) && $plans->count() > 0):
            $currentTime = date('Y-m-d H:i:s', strtotime($theDate.' '.date('H:i:s')));
            foreach($plans as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $tutorEmployeeId)->where('date', $theDate)->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';
                $orgStart = date('Y-m-d H:i:s', strtotime($theDate.' '.$pln->plan->start_time));
                $orgEnd = date('Y-m-d H:i:s', strtotime($theDate.' '.$pln->plan->end_time));
                if($currentTime < $orgStart && !isset($pln->attendanceInformation->id)):
                    $classLabel = '<span class="text-danger font-medium">Starting Shortly</span>';
                elseif($currentTime > $orgStart && $currentTime < $orgEnd && !isset($pln->attendanceInformation->id)):
                    $classLabel = '<span class="text-pending font-medium flashingText">Starting Shortly</span>';
                elseif(isset($pln->attendanceInformation->id)):
                    if($pln->feed_given == 1 && $pln->attendances->count() > 0):
                        $classLabel .= '<span class="btn-rounded btn font-medium btn-success text-white p-0 w-9 h-9 mr-1" style="flex: 0 0 36px;">A</span>';
                    endif;
                    if(!empty($pln->attendanceInformation->start_time) && empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">Started '.date('h:i A', strtotime($pln->attendanceInformation->start_time)).'</span>';
                    elseif(!empty($pln->attendanceInformation->start_time) && !empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">';
                            $classLabel .= 'Started '.date('h:i A', strtotime($pln->attendanceInformation->start_time)).'<br/>'; 
                            $classLabel .= 'Finished '.date('h:i A', strtotime($pln->attendanceInformation->end_time)); 
                        $classLabel .= '</span>';
                    endif;
                elseif($currentTime > $orgEnd && !isset($pln->attendanceInformation->id)):
                    $classLabel .= '<span class="text-danger font-medium">Not Started</span>';
                endif;

                $html .= '<tr class="intro-x">';
                    $html .= '<td>';
                        $html .= '<span class="font-fedium">'.date('H:i', strtotime($theDate.' '.$pln->plan->start_time)).'</span>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<div class="flex items-center">';
                            if(isset($pln->plan->group->name) && !empty($pln->plan->group->name)):
                                if(strlen($pln->plan->group->name) > 2):
                                    $html .= '<div class="mr-4 rounded text-lg bg-success whitespace-nowrap text-white cursor-pointer font-medium w-auto px-2 py-1 h-auto inline-flex justify-center items-center">'.$pln->plan->group->name.'</div>';
                                else:
                                    $html .= '<div class="mr-4 rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.$pln->plan->group->name.'</div>';
                                endif;
                            endif;
                            $html .= '<div>';
                                $html .= '<a href="'.route('tutor-dashboard.plan.module.show', $pln->plan_id).'" class="font-medium whitespace-nowrap">'.(isset($pln->plan->creations->module->name) && !empty($pln->plan->creations->module->name) ? $pln->plan->creations->module->name : 'Unknown').(isset($pln->plan->class_type) && !empty($pln->plan->class_type) ? ' - '.$pln->plan->class_type : '').'</a>';
                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->plan->course->name) && !empty($pln->plan->course->name) ? $pln->plan->course->name : 'Unknown').'</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                        $html .= '<div class="flex justify-start items-center">';
                            $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                $html .= '<img src="'.(isset($pln->plan->tutor->employee->photo_url) ? $pln->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : 'LCC').'">';
                            $html .= '</div>';
                            $html .= '<div class="inline-block font-medium relative">';
                                $html .= (isset($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : $pln->plan->tutor->name);
                                $html .= ($empAttendanceLive->count() == 0 && isset($pln->plan->tutor->employee->mobile) && !empty($pln->plan->tutor->employee->mobile) ? '<br/>'.$pln->plan->tutor->employee->mobile : '');
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= (isset($pln->plan->room->name) && !empty($pln->plan->room->name) ? $pln->plan->room->name : '');
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        $html .= '<span class="flex justify-start items-center">';
                            $html .= $classLabel;
                        $html .= '</span>';
                    $html .= '</td>';
                    $html .= '<td class="text-right">';
                        if($pln->status == 'Schedule'):
                            $html .= '<button data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#proxyClassModal" type="button" class="proxyClass btn-rounded btn btn-success text-white p-0 w-9 h-9"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg></button>';
                        endif;
                        if($pln->status == 'Schedule' || $pln->status == 'Unknown'):
                            $html .= '<button data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#cancelClassModal" type="button" class="cancelClass ml-1 btn-rounded btn btn-danger text-white p-0 w-9 h-9"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x-circle" class="lucide lucide-x-circle w-4 h-4"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg></button>';
                        endif;
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="6">';
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
                $moduleCreations = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->pluck('module_creation_id')->unique()->toArray();
                $html .= '<div class="intro-x">';
                    $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
                        $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                            $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                        $html .= '</div>';
                        $html .= '<div class="ml-4 mr-auto">';
                            $html .= '<a href="'.route('programme.dashboard.personal.tutors.details', [$termDecId, $tut->id]).'" class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</a>';
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
                $moduleCreations = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->pluck('module_creation_id')->unique()->toArray();
                $html .= '<div class="intro-x">';
                    $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
                        $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                            $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                        $html .= '</div>';
                        $html .= '<div class="ml-4 mr-auto">';
                            $html .= '<a href="'.route('programme.dashboard.personal.tutors.details', [$termDecId, $tut->id]).'" class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</a>';
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


    public function calculateTutorHours($tutor, $term_declaration_id){
        $minutes = 0;
        $activePlans = Plan::where('tutor_id', $tutor)->where('term_declaration_id', $term_declaration_id)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
        if(!empty($activePlans)):
            foreach($activePlans as $pln):
                $startTime = date('Y-m-d').' '.$pln->start_time;
                $endTime = date('Y-m-d').' '.$pln->end_time;
                $start = new DateTime($startTime);
                $end = new DateTime($endTime);
                $diff_in_seconds = $end->getTimestamp() - $start->getTimestamp();
                $minute = floor($diff_in_seconds / 60);

                $minutes += $minute;
            endforeach;
        endif;

        return $minutes;
    }

    public function tutors($term_declaration_id){
        $tutorIds = Plan::where('term_declaration_id', $term_declaration_id)->pluck('tutor_id')->unique()->toArray();

        $res = [];
        $tutors = User::with('employee')->whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        if(!empty($tutors)):
            foreach($tutors as $tut):
                $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
                $classMinutes = $this->calculateTutorHours($tut->id, $term_declaration_id);

                $activePlans = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
                $plan_ids = $activePlans->pluck('id')->unique()->toArray();
                $assigns = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
                $moduleCreations = $activePlans->pluck('module_creation_id')->toArray();
                $groups = $activePlans->pluck('group_id')->unique()->toArray();

                $tut['no_of_module'] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $res[$tut->id] = $tut;
                $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 1);
                $res[$tut->id]['contracted_hour'] = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');
                $res[$tut->id]['class_minutes'] = $classMinutes;
                $res[$tut->id]['class_hours'] = $this->calculateHourMinute($classMinutes);
            endforeach;
        endif;
        
        return view('pages.programme.dashboard.tutors', [
            'title' => 'Programme Dashboard - Welcome to London churchill college',
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
                $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
                $activePlans = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
                $plan_ids = $activePlans->pluck('id')->unique()->toArray();
                $assigns = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
                $moduleCreations = $activePlans->pluck('module_creation_id')->toArray();
                $groups = $activePlans->pluck('group_id')->unique()->toArray();
                $tut['no_of_module'] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $tut['no_of_assigned'] = (!empty($assigns) ? count($assigns) : 0);
                $tut['no_of_group'] = (!empty($groups) ? count($groups) : 0);
                $res[$tut->id] = $tut;
                $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 2);
                $res[$tut->id]['contracted_hour'] = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');
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
        $planDateLists = PlansDateList::whereHas('plan', function($q) use($term_declaration_id, $tutor_field, $tutor_id){
            $q->where('term_declaration_id', $term_declaration_id)->where($tutor_field, $tutor_id);
        })->get();
        $plan_ids = $planDateLists->pluck('plan_id')->unique()->toArray();
        $date_ids = $planDateLists->pluck('id')->unique()->toArray();
        
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
                    ->whereIn('atn.plans_date_list_id', $date_ids);
        if(!empty($student_ids)):
            $query->whereIn('atn.student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }

    public function getPlanAttendanceRate($plan_id){
        $planDateLists = PlansDateList::where('plan_id', $plan_id)->pluck('id')->unique()->toArray();
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
                    ->whereIn('atn.plans_date_list_id', $planDateLists);
        if(!empty($student_ids)):
            $query->whereIn('atn.student_id', $student_ids);
        endif;
        $attendance = $query->get()->first();

        return $attendance;
    }

    public function cancelClass(CancelClassRequest $request){
        $plan_id = $request->plan_id;
        $plan = Plan::find($plan_id);
        $plans_date_list_id = $request->plans_date_list_id;
        $canceled_reason = $request->canceled_reason;
        $siteSettings = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->get()->first();
        $company_name = (isset($siteSettings->value) && !empty($siteSettings->value) ? $siteSettings->value : 'London Churchill College');
        $courseName = (isset($plan->course->name) ? $plan->course->name : '');
        $moduleName = (isset($plan->creations->module_name) ? $plan->creations->module_name : '');
        $groupName = (isset($plan->group->name) ? $plan->group->name : '');
        $classTime = date('h:i A', strtotime($plan->start_time)).' - '.date('h:i A', strtotime($plan->end_time));
        $tutorName = (isset($plan->tutor->employee->full_name) && !empty($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : (isset($plan->personalTutor->employee->full_name) && !empty($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : ''));

        $notify_student = (isset($request->notify_student) && $request->notify_student > 0 ? true : false);
        $notify_tutors = (isset($request->notify_tutors) && $request->notify_tutors > 0 ? true : false);

        $data = [];
        $data['status'] = 'Canceled';
        $data['canceled_reason'] = $canceled_reason;
        $data['canceled_by'] = auth()->user()->id;
        $data['canceled_at'] = date('Y-m-d H:i:s');

        PlansDateList::where('id', $plans_date_list_id)->update($data);

        if($notify_student):
            if(isset($plan->assign) && $plan->assign->count() > 0):
                $sms_subject = 'Class cancellation notice';
                foreach($plan->assign as $assign):
                    $student = Student::with('title', 'contact')->where('id', $assign->student_id)->get()->first();
                    $mobile = (isset($student->contact->mobile) && !empty($student->contact->mobile) ? $student->contact->mobile : '');
                    $emails = [];
                    if(isset($student->contact->personal_email) && !empty($student->contact->personal_email)): 
                        $emails[] = $student->contact->personal_email; 
                    endif;
                    if(isset($student->contact->institutional_email) && !empty($student->contact->institutional_email)): 
                        $emails[] = $student->contact->institutional_email; 
                    endif;

                    $sms_body = 'Dear '.$student->full_name.', this is a class cancellation notice: Course name: '.$courseName.', Module name: '.$moduleName.', Group: '.$groupName.', Time: '.$classTime.', Tutor name: '.$tutorName;
                    $studentSmsContent = StudentSmsContent::create([
                        'sms_template_id' => null,
                        'subject' => $sms_subject,
                        'sms' => $sms_body
                    ]);
                    if($studentSmsContent):
                        $studentSms = StudentSms::create([
                            'student_id' => $student->id,
                            'student_sms_content_id' => $studentSmsContent->id,
                            'phone' => $mobile,
                            'created_by' => auth()->user()->id
                        ]);

                        //$sms = $this->sendSms($mobile, $sms_body, $company_name);
                    endif;
                    
                    $email_body = 'Dear '.$student->full_name.',<br/><br/>';
                    $email_body .= 'This is a class cancellation notice:<br/>';
                    $email_body .= 'Course Name: '.$courseName.'<br/>';
                    $email_body .= 'Module Name: '.$moduleName.'<br/>';
                    $email_body .= 'Group Name: '.$groupName.'<br/>';
                    $email_body .= 'Time: '.$classTime.'<br/>';
                    $email_body .= 'Tutor Name: '.$tutorName.'<br/><br/>';
                    $email_body .= 'Thanks & Regards <br/>'.$company_name;

                    
                endforeach;
            endif;
        endif;

        if($notify_tutors):
            //$sub = "Class cancellation notice from ".$settings['company_name']." account.";
            //$from = $settings['company_name']." Staff <".$settings['smtp_user'].">";
            //$msg = 'Dear '.$staff_name.', <br/> You got a class cancellation notice: <br/><br/> '.$email_text.'<br/> Course Name: '.$course_name.'<br/> Module Name: '.$module_name.'<br/> Group: '.$group_name.'<br/> Time: '.$class_time.'<br/> Tutor Name: '.$tutor_name.'<br/><br/> Regards<br/>'.$settings['company_name'];
        endif;

        return response()->json(['message' => 'Class status updated to canceled.'], 200);
    }
}
