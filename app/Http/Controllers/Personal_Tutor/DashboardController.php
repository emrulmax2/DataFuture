<?php

namespace App\Http\Controllers\Personal_Tutor;


use App\Http\Controllers\Controller;
use App\Http\Requests\CancelClassRequest;
use App\Http\Requests\ReAssignClassRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceInformation;
use App\Models\ComonSmtp;
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
use App\Models\StudentEmail;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use App\Models\TermDeclaration;
use App\Models\User;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SendSmsTrait;
use DateTime;
use App\Traits\GenerateEmailPdfTrait;
use Illuminate\Support\Facades\Auth;



class DashboardController extends Controller
{
    use SendSmsTrait, GenerateEmailPdfTrait;
    public function index(){

        $id = auth()->user()->id; //304; 
        $userData = User::find($id);
        $employee = Employee::where("user_id", $userData->id)->get()->first();

        $latestTerm = Plan::where('personal_tutor_id', $id)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $latestTermId = (isset($latestTerm->term_declaration_id) && $latestTerm->term_declaration_id > 0 ? $latestTerm->term_declaration_id : 0);
        $theTermDeclaration = TermDeclaration::find($latestTermId);
        $modules = Plan::with('activeAssign', 'tutor', 'personalTutor')->where('term_declaration_id', $latestTermId)->where('personal_tutor_id', $id)->orderBy('id', 'ASC')->get();
        $plan_ids = $modules->pluck('id')->unique()->toArray();
        $assigns = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
            $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
        })->distinct()->count('student_id');

        $theDate = Date('Y-m-d'); //'2023-11-24';
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $usedCourses = Plan::pluck('course_id')->unique()->toArray();
        $theTerm = Plan::with('attenTerm')->whereIn('id', $classPlanIds)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $theTermId = (isset($theTerm->attenTerm->id) && $theTerm->attenTerm->id > 0 ? $theTerm->attenTerm->id : 0);

        
        $today = date('Y-m-d');
        return  view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theTerm' => $theTerm,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'classInformation' => $this->getClassInfoHtml($theDate),
            //'classTutor' => $this->getClassTutorsHtml($theDate),
            //'classPTutor' => $this->getClassPersonalTutorsHtml($theDate),
            'absentToday' => $this->getAbsentEmployees(date('Y-m-d')),
            'termAttendanceRates' => $this->getTermAttendanceRateFull($theTermId),
            'tutors' => User::with('employee')->whereHas('employee', function($q){
                $q->where('status', 1);
            })->orderBy('name', 'ASC')->get(),
            'current_term' => $theTermDeclaration,
            'modules' => $modules,
            'no_of_assigned' => $assigns,
            'venue_ips' => VenueIpAddress::whereNotNull('venue_id')->pluck('ip')->toArray(),
            'todays_classes' => PlansDateList::with('attendanceInformation', 'attendances')->where('date', date('Y-m-d'))->whereHas('plan', function($q) use($id){
                                    $q->where('personal_tutor_id', $id);
                                })->get()->sortBy(function($classes, $key) {
                                    return $classes->plan->start_time;
                                }),
        ]);

    }
    public function getClassess(Request $request){
        $personalTutorId = (isset($request->personalTutorId) && $request->personalTutorId > 0 ? $request->personalTutorId : 0);
        $plan_date = (isset($request->plan_date) && !empty($request->plan_date) ? date('Y-m-d', strtotime($request->plan_date)) : '');
        $venue_ips = VenueIpAddress::whereNotNull('venue_id')->pluck('ip')->toArray();

        $html = '';
        if(!empty($plan_date) && $personalTutorId > 0):
            $classes = PlansDateList::with('attendanceInformation', 'attendances')->where('date', $plan_date)->whereHas('plan', function($q) use($personalTutorId){
                        $q->where('personal_tutor_id', $personalTutorId);
                    })->get()->sortBy(function($myClasses, $key) {
                        return $myClasses->plan->start_time;
                    });
            if($classes->count() > 0):
                foreach($classes as $class):
                    $showClass = 0;
                    if(in_array(auth()->user()->last_login_ip, $venue_ips)):
                        $listStart = $plan_date.' '.$class->plan->start_time;
                        $listEnd = $plan_date.' '.$class->plan->end_time;
                        $classStart = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($listStart)));
                        $classEnd = date('Y-m-d H:i:s', strtotime($listEnd));
                        $currentTime = date('Y-m-d H:i:s');
                        if($currentTime >= $classStart && $currentTime <= $classEnd):
                            $showClass = 1;
                        elseif($currentTime < $classStart):
                            $showClass = 2;
                        endif;
                    endif;

                    $html .= '<div class="intro-x relative flex items-center mb-3">';
                        $html .= '<div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">';
                            $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
                                $html .= '<img alt="'.(isset($class->plan->tutor->employee->full_name) && !empty($class->plan->tutor->employee->full_name) ? $class->plan->tutor->employee->full_name : 'London Churchill College').'" src="'.(isset($class->plan->tutor->employee->photo_url) && !empty($class->plan->tutor->employee->photo_url) ? $class->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
                            $html .= '</div>';
                        $html .= '</div>';
                        $html .= '<div class="box px-5 py-3 ml-4 flex-1 bg-warning-soft zoom-in">';
                            $html .= '<div class="flex items-center mb-3">';
                                $html .= '<div class="font-medium">'.$class->plan->creations->module_name.' ('. $class->plan->group->name.')'.(isset($class->plan->class_type) && !empty($class->plan->class_type) ? ' - '.$class->plan->class_type : '').'</div>';
                                $html .= '<div class="text-xs text-slate-500 ml-auto">'.(isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '').'</div>';
                            $html .= '</div>';
                            //$html .= '<div class="text-slate-500 mt-1">'.(isset($class->plan->course->name) ? $class->plan->course->name : '').'</div>';
                            if($class->plan->class_type == 'Tutorial'):
                                if(isset($class->attendanceInformation->id) && $class->attendanceInformation->id > 0):
                                    if($class->feed_given == 1):
                                        $html .= '<a data-attendanceinfo="'.$class->attendanceInformation->id.'" data-id="'.$class->id.'" href="'.route('tutor-dashboard.attendance', [$class->plan->tutor_id, $class->id, 1]).'" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Feed Attendance</a>';
                                    else:
                                        $html .= '<a href="'.route('tutor-dashboard.attendance', [$class->plan->tutor_id, $class->id, 1]).'"  data-attendanceinfo="'.$class->attendanceInformation->id.'" data-id="'.$class->id.'" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-2 px-3 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>';
                                        if($class->feed_given == 1 && $class->attendanceInformation->end_time == null):
                                            $html .= '<a data-attendanceinfo="'.$class->attendanceInformation->id.'" data-id="'.$class->id.'" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch transition duration-200 btn btn-sm btn-danger text-white py-2 px-3 ml-1"><i data-lucide="x-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</a>';
                                        endif;
                                    endif;
                                else:
                                    if($showClass == 1):
                                        $html .= '<a data-tw-toggle="modal" data-id="'.$class['id'].'" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>';
                                    elseif($showClass == 2):
                                        $html .= '<div class="alert alert-danger-soft show flex items-start" role="alert">
                                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Class Start Button appears 15 minutes before the scheduled time.
                                                </div>';
                                    endif;
                                endif;
                            endif;
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
        $Query = Student::with('title')->orderBy('registration_no', 'ASC')->where('registration_no', 'LIKE', '%'.$SearchVal.'%')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="'.route('student.show', $qr->id).'" data-label="'.$qr->registration_no.' - '.' '.$qr->title->name.$qr->first_name.' '.$qr->last_name.'" class="dropdown-item">'.$qr->registration_no.' - '.$qr->full_name.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" data-lable="Nothing found!" class="dropdown-item disable">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getClassInfoHtml($theDate = null, $course_id = 0, ){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        
        $personalTutorId = auth()->user()->id; //304; 
        
        $html = '';

        $term = PlansDateList::with('plan')->where('date',$theDate)->get()->first();

        $plans = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('status','Completed')->whereHas('plan', function($q) use($term,$course_id, $personalTutorId){
            
            if($course_id > 0):
                $q->where('course_id', $course_id);
            endif;
                $q->where('personal_tutor_id', $personalTutorId);
                $q->where('class_type', "Theory");
                $q->where('term_declaration_id',$term->term_declaration_id);


        })->get()->sortBy(function($planDates, $key) {

            return $planDates->plan->start_time;

        });


        if(!empty($plans) && $plans->count() > 0):
            //$currentTime = date('Y-m-d H:i:s');
            foreach($plans as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $PerTutorEmployeeId = (isset($pln->plan->personalTutor->employee->id) && $pln->plan->personalTutor->employee->id > 0 ? $pln->plan->personalTutor->employee->id : 0);
                $classTutor = ($tutorEmployeeId > 0 ? $tutorEmployeeId : ($PerTutorEmployeeId > 0 ? $PerTutorEmployeeId : 0));
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $classTutor)->where('date', $pln->date)->where('attendance_type', 1)->get();

                $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
                $proxyAttendanceLive = EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', $pln->date)->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';

                $classLabel .= '<span class="text-danger font-medium">Completed</span>';
                    $html .= '<tr class="intro-x">';
                        $html .= '<td>';
                            $html .= '<span class="font-fedium">'.date('d/m/Y H:i', strtotime($pln->date.' '.$pln->plan->start_time)).'</span>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<div class="flex items-center">';
                                $html .= '<div>';
                                    $html .= '<a href="'.route('tutor-dashboard.plan.module.show', $pln->plan_id).'" class="font-medium whitespace-nowrap">'.(isset($pln->plan->creations->module->name) && !empty($pln->plan->creations->module->name) ? $pln->plan->creations->module->name : 'Unknown').(isset($pln->plan->class_type) && !empty($pln->plan->class_type) ? ' - '.$pln->plan->class_type : '').'</a>';
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->plan->course->name) && !empty($pln->plan->course->name) ? $pln->plan->course->name : 'Unknown'). ' <b>[ '.$pln->plan->group->name .' ]</b></div>';
                                    if(isset($pln->plan->tasks) && $pln->plan->tasks->count() > 0):
                                        $html .= '<div class="flex flex-start pt-1">';
                                        foreach($pln->plan->tasks as $tsk):
                                            $sc_class = 'btn-success';
                                            if($tsk->uploads->count() == 0):
                                                if($tsk->last_date && $tsk->last_date > date('Y-m-d')):
                                                    $sc_class = 'btn-warning';
                                                elseif($tsk->last_date && $tsk->last_date <= date('Y-m-d')):
                                                    $sc_class = 'btn-danger';
                                                endif;
                                            endif;
                                            $html .= '<span class="btn btn-sm px-2 py-0.5 text-white '.$sc_class.' mr-1">'.$tsk->eLearn->short_code.'</span>';
                                        endforeach;
                                        $html .= '</div>';
                                    endif;
                                $html .= '</div>';
                                
                            $html .= '</div>';
                        $html .= '</td>';
                        $html .= '<td class="text-left">';
                            if($pln->plan->tutor_id > 0):
                                $html .= '<div class="flex justify-start items-center">';
                                    $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                        if($pln->proxy_tutor_id > 0):
                                            $html .= '<img src="'.(isset($pln->plan->proxy->employee->photo_url) ? $pln->plan->proxy->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->proxy->employee->full_name) ? $pln->plan->proxy->employee->full_name : 'LCC').'">';
                                        else:
                                            $html .= '<img src="'.(isset($pln->plan->tutor->employee->photo_url) ? $pln->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : 'LCC').'">';
                                        endif;
                                    $html .= '</div>';
                                    $html .= '<div class="inline-block font-medium relative text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                                        $html .= ($pln->proxy_tutor_id > 0 ? '<span class="line-through">' : '').(isset($pln->plan->tutor->employee->full_name) && !empty($pln->plan->tutor->employee->full_name) ? $pln->plan->tutor->employee->full_name : (isset($pln->plan->tutor->name) ? $pln->plan->tutor->name : 'LCC')).($pln->proxy_tutor_id > 0 ? '</span>' : '');
                                        if($pln->proxy_tutor_id > 0):
                                            $html .= '<br/><span class="'.($proxyAttendanceLive->count() > 0 ? 'text-success' : 'text-danger').'">'.(isset($pln->proxy->employee->full_name) && !empty($pln->proxy->employee->full_name) ? $pln->proxy->employee->full_name : 'Unknown Proxy').'</span>';
                                            $html .= ($proxyAttendanceLive->count() == 0 && isset($pln->proxy->employee->mobile) && !empty($pln->proxy->employee->mobile) ? '<br/><span class="text-danger">'.$pln->proxy->employee->mobile.'</span>' : '');
                                        else:
                                            $html .= ($empAttendanceLive->count() == 0 && isset($pln->plan->tutor->employee->mobile) && !empty($pln->plan->tutor->employee->mobile) ? '<br/>'.$pln->plan->tutor->employee->mobile : '');
                                        endif;
                                    $html .= '</div>';
                                $html .= '</div>';
                            elseif($pln->plan->personal_tutor_id > 0):
                                $html .= '<div class="flex justify-start items-center">';
                                    $html .= '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block" style="0 0 2.5rem">';
                                        if($pln->proxy_tutor_id > 0):
                                            $html .= '<img src="'.(isset($pln->plan->proxy->employee->photo_url) ? $pln->plan->proxy->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->proxy->employee->full_name) ? $pln->plan->proxy->employee->full_name : 'LCC').'">';
                                        else:
                                            $html .= '<img src="'.(isset($pln->plan->personalTutor->employee->photo_url) ? $pln->plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'" class="rounded-full shadow" alt="'.(isset($pln->plan->personalTutor->employee->full_name) ? $pln->plan->personalTutor->employee->full_name : 'LCC').'">';
                                        endif;
                                    $html .= '</div>';
                                    $html .= '<div class="inline-block font-medium relative text-'.($empAttendanceLive->count() > 0 ? 'success' : 'danger').'">';
                                        $html .= ($pln->proxy_tutor_id > 0 ? '<span class="line-through">' : '').(isset($pln->plan->personalTutor->employee->full_name) && !empty($pln->plan->personalTutor->employee->full_name) ? $pln->plan->personalTutor->employee->full_name : (isset($pln->plan->personalTutor->name) ? $pln->plan->personalTutor->name : 'LCC')).($pln->proxy_tutor_id > 0 ? '</span>' : '');
                                        if($pln->proxy_tutor_id > 0):
                                            $html .= '<br/><span class="'.($proxyAttendanceLive->count() > 0 ? 'text-success' : 'text-danger').'">'.(isset($pln->proxy->employee->full_name) && !empty($pln->proxy->employee->full_name) ? $pln->proxy->employee->full_name : 'Unknown Proxy').'</span>';
                                            $html .= ($proxyAttendanceLive->count() == 0 && isset($pln->proxy->employee->mobile) && !empty($pln->proxy->employee->mobile) ? '<br/><span class="text-danger">'.$pln->proxy->employee->mobile.'</span>' : '');
                                        else:
                                            $html .= ($empAttendanceLive->count() == 0 && isset($pln->plan->personalTutor->employee->mobile) && !empty($pln->plan->personalTutor->employee->mobile) ? '<br/>'.$pln->plan->personalTutor->employee->mobile : '');
                                        endif;
                                        $html .= '</div>';
                                $html .= '</div>';
                            else:
                                $html .= '<span>N/A</span>';
                            endif;
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
    public function getClassInformations(Request $request){
        $planClassStatus = $request->planClassStatus;
        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
        $theClassDate = (isset($request->theClassDate) && !empty($request->theClassDate) ? date('Y-m-d', strtotime($request->theClassDate)) : date('Y-m-d'));

        $res = [];
        $res['planTable'] = $this->getClassInfoHtml($theClassDate, $planCourseId);
        //$res['tutors'] = $this->getClassTutorsHtml($theClassDate, $planCourseId);
        //$res['ptutors'] = $this->getClassPersonalTutorsHtml($theClassDate, $planCourseId);

        return response()->json(['res' => $res], 200);
    }
    // public function getClassTutorsHtml($theDate = null, $course_id = 0){
    //     $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
    //     $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
    //     $termDecs = Plan::whereIn('id', $classPlanIds);
    //     if($course_id > 0):
    //         $termDecs->where('course_id', $course_id);
    //     endif;
    //     $termDecs = $termDecs->orderBy('term_declaration_id', 'DESC')->get()->first();
    //     $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);
        
    //     $query = Plan::where('term_declaration_id', $termDecId);
    //     if($course_id > 0):
    //         $query->where('course_id', $course_id);
    //     endif;
    //     $classTutors = $query->pluck('tutor_id')->unique()->toArray();
        
    //     $html = '';
    //     $uttors = User::whereIn('id', $classTutors)->skip(0)->take(5)->get();
    //     if(!empty($uttors) && $uttors->count() > 0):
    //         foreach($uttors as $tut):
    //             $moduleCreations = Plan::where('tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->pluck('module_creation_id')->unique()->toArray();
    //             $html .= '<div class="intro-x">';
    //                 $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
    //                     $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
    //                         $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
    //                     $html .= '</div>';
    //                     $html .= '<div class="ml-4 mr-auto">';
    //                         $html .= '<a href="'.route('programme.dashboard.personal.tutors.details', [$termDecId, $tut->id]).'" class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</a>';
    //                     $html .= '</div>';
    //                     $html .= '<div class="text-white rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.(!empty($moduleCreations) ? count($moduleCreations) : 0).'</div>';
    //                 $html .= '</div>';
    //             $html .= '</div>';
    //         endforeach;
    //     else:
    //         $html .= '<div class="intro-x">';
    //             $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan tutor found for the selected date.</div>';
    //         $html .= '</div>';
    //     endif;

    //     return array('count' => (!empty($classTutors) ? count($classTutors) : 0), 'html' => $html);
    // }

    // public function getClassPersonalTutorsHtml($theDate = null, $course_id = 0) {
    //     $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
    //     $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
    //     $termDecs = Plan::whereIn('id', $classPlanIds);
    //     if($course_id > 0):
    //         $termDecs->where('course_id', $course_id);
    //     endif;
    //     $termDecs = $termDecs->orderBy('term_declaration_id', 'DESC')->get()->first();
    //     $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);

    //     $query = Plan::where('term_declaration_id', $termDecId);
    //     if($course_id > 0):
    //         $query->where('course_id', $course_id);
    //     endif;
    //     $classTutors = $query->pluck('personal_tutor_id')->unique()->toArray();

    //     $html = '';
    //     $uttors = User::whereIn('id', $classTutors)->skip(0)->take(5)->get();
    //     if(!empty($uttors) && $uttors->count() > 0):
    //         foreach($uttors as $tut):
    //             $moduleCreations = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->where('class_type', 'Tutorial')->pluck('module_creation_id')->toArray();
    //             $html .= '<div class="intro-x">';
    //                 $html .= '<div class="box px-5 py-3 mb-3 flex items-center zoom-in">';
    //                     $html .= '<div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">';
    //                         $html .= '<img alt="'.(isset($tut->employee->full_name) ? $tut->employee->full_name : '').'" src="'.(isset($tut->employee->photo_url) ? $tut->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')).'">';
    //                     $html .= '</div>';
    //                     $html .= '<div class="ml-4 mr-auto">';
    //                         $html .= '<a href="'.route('programme.dashboard.personal.tutors.details', [$termDecId, $tut->id]).'" class="font-medium uppercase">'.(isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee').'</a>';
    //                     $html .= '</div>';
    //                     $html .= '<div class="text-white rounded-full text-lg bg-warning text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.(!empty($moduleCreations) ? count($moduleCreations) : 0).'</div>';
    //                 $html .= '</div>';
    //             $html .= '</div>';
    //         endforeach;
    //     else:
    //         $html .= '<div class="intro-x">';
    //             $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan tutor found for the selected date.</div>';
    //         $html .= '</div>';
    //     endif;

    //     return array('count' => (!empty($classTutors) ? count($classTutors) : 0), 'html' => $html);
    // }
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
    public function getTermAttendanceRateFull($term_declaration_id){
        $planDateLists = PlansDateList::whereHas('plan', function($q) use($term_declaration_id){
            $q->where('term_declaration_id', $term_declaration_id);
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
        $attendances = $query->get()->first();

        if(isset($attendances) && !empty($attendances)):
            $attendance = 0;
            $attendance += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
            $attendance += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
            $attendance += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
            $attendance += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
            $attendance += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
            $attendance += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

            $attendanceTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
            if($attendance > 0 && $attendanceTotal > 0):
                return number_format($attendance / $attendanceTotal * 100, 2);
            else:
                return 0;
            endif;
        else:
            return 0;
        endif;
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
                $startTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' '.$pln->start_time));
                $endTime = date('Y-m-d H:i:s', strtotime(date('Y-m-d').' '.$pln->end_time));

                $start = new DateTime($startTime);
                $end = new DateTime($endTime);
                $diff_in_seconds = $end->getTimestamp() - $start->getTimestamp();
                $minute = floor($diff_in_seconds / 60);

                $minutes += $minute;
            endforeach;
        endif;

        return $minutes;
    }

}
