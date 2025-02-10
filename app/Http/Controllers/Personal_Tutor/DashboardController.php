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
use App\Models\SmsTemplate;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;



class DashboardController extends Controller
{
    use SendSmsTrait, GenerateEmailPdfTrait;
    public function index(){

        $id = auth()->user()->id; //304; 
        $userData = User::find($id);
        $employee = Employee::where("user_id", $userData->id)->get()->first();

        $ptTerms = Plan::where('personal_tutor_id', $id)->orderBy('term_declaration_id', 'DESC')->get();
        $ptTermIds = $ptTerms->pluck('term_declaration_id')->unique()->toArray();
        $latestTermId = (isset($ptTerms[0]->term_declaration_id) && $ptTerms[0]->term_declaration_id > 0 ? $ptTerms[0]->term_declaration_id : 0);
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
        
        
        
        $undecidedUploads = $this->totalUndecidedCount();
        
        $today = date('Y-m-d');
        $yesterday = Carbon::yesterday()->format('d-m-Y');
        return  view('pages.personal-tutor.dashboard.index', [
            'title' => 'Personal Tutor Dashboard - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theTerm' => $theTerm,
            'undecidedUploads' => $undecidedUploads,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'classInformation' => $this->getClassInfoHtml($theDate),
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
            'myModules' => DB::table('plans')->select('class_type', DB::raw('COUNT(DISTINCT id) as TOTAL_MODULE'))
                            ->where('term_declaration_id', $latestTermId)->where('personal_tutor_id', $id)
                            ->groupBy('class_type')->orderBy('class_type', 'ASC')->get(),
            'attendance_avg' => $this->myModulesAttendanceAverage($id, $latestTermId),
            'bellow_60' => $this->myModulesAttendanceBellow($id, $latestTermId),
            'yesterday' => $yesterday,
            'no_of_assignment' => $this->getAssignmentCount($id, $latestTermId),
            'termdeclarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
            'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            'otherTerms' => (!empty($ptTermIds) ? TermDeclaration::whereIn('id', $ptTermIds)->orderBy('id', 'DESC')->get() : [])
        ]);

    }

    public function getAssignmentCount($id, $term_declaration_id){
        $plan_ids = Plan::where('personal_tutor_id', $id)->where('term_declaration_id', $term_declaration_id)
                   ->where('class_type', 'Theory')->pluck('id')->unique()->toArray();
        if(!empty($plan_ids)):
            $assigns = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
                    $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
                })->count('student_id');
            return $assigns;
        else:
            return 0;
        endif;
    }

    public function myModulesAttendanceAverage($id = 0, $term_declaration_id){
        $id = ($id > 0 ? $id : auth()->user()->id);
        $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where('personal_tutor_id', $id)
                    ->whereIn('class_type', ['Tutorial', 'Seminar'])->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();

        if(!empty($plan_ids) && count($plan_ids) > 0):
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
                        ->whereIn('atn.plan_id', $plan_ids);
            if(!empty($student_ids)):
                $query->whereIn('atn.student_id', $student_ids);
            endif;
            $attendance = $query->get()->first();

            return (isset($attendance->percentage_withexcuse) && $attendance->percentage_withexcuse > 0 ? number_format($attendance->percentage_withexcuse, 2).'%' : '0%');
        else:
            return '0%';
        endif;
    }

    public function myModulesAttendanceBellow($id = 0, $term_declaration_id, $percentage = 60){
        $id = ($id > 0 ? $id : auth()->user()->id);
        $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where('personal_tutor_id', $id)
                    ->whereIn('class_type', ['Tutorial', 'Seminar'])->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();

        if(!empty($plan_ids) && count($plan_ids) > 0):
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
                    ->whereIn('atn.plan_id', $plan_ids);
            if(!empty($student_ids)):
                $query->whereIn('atn.student_id', $student_ids);
            endif;
            $query->groupBy('atn.student_id')->havingRaw('percentage_withexcuse < '.$percentage.' OR round(percentage_withexcuse) = 0');
            $attendance = $query->get()->count();

            return ($attendance > 0 ? $attendance : 0);
        else:
            return 0;
        endif;
    }

    public function totalUndecidedCount() {
        $id = auth()->user()->id; //304; 
        $theDate = Date('Y-m-d'); //'2023-11-24';

        $dateTerm = PlansDateList::with('plan')->where('date',$theDate)->get()->first();
        if(isset($dateTerm->plan)):
        $planDates = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('class_file_upload_found',"Undecided")->where('status','Completed')->whereHas('plan', function($q) use($dateTerm, $id){
            
            
                $q->where('personal_tutor_id', $id);
                $q->where('class_type', "Theory");
                $q->where('term_declaration_id',$dateTerm->plan->term_declaration_id);


        })->get();

        $undecidedUploads =  $planDates->count();
    else:
        $undecidedUploads = 0;
    endif;  
        return $undecidedUploads;

    }

    public function getClassess(Request $request) {
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

    public function getClassInfoHtml($theDate = null, $course_id = 0, $uploadedType="Undecided"){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        
        $personalTutorId = auth()->user()->id; //304; 
        
        $html = '';

        $dateTerm = PlansDateList::with('plan')->where('date',$theDate)->get()->first();
        if(isset($dateTerm->plan)):
            $planDates = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('class_file_upload_found',$uploadedType)->where('status','Completed')->whereHas('plan', function($q) use($dateTerm,$course_id, $personalTutorId){
                
                if($course_id > 0):
                    $q->where('course_id', $course_id);
                endif;
                    $q->where('personal_tutor_id', $personalTutorId);
                    $q->where('class_type', "Theory");
                    $q->where('term_declaration_id',$dateTerm->plan->term_declaration_id);


            })->get()->sortBy(function($planDates, $key) {

                return date("Y-m-d H:i", strtotime($planDates->date." ".$planDates->plan->start_time));

            });
        else:
            $planDates = [];
        endif;

        if(!empty($planDates) && $planDates->count() > 0):
            foreach($planDates as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $PerTutorEmployeeId = (isset($pln->plan->personalTutor->employee->id) && $pln->plan->personalTutor->employee->id > 0 ? $pln->plan->personalTutor->employee->id : 0);
                $classTutor = ($tutorEmployeeId > 0 ? $tutorEmployeeId : ($PerTutorEmployeeId > 0 ? $PerTutorEmployeeId : 0));
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $classTutor)->where('date', date("Y-m-d",strtotime($pln->date)))->where('attendance_type', 1)->get();

                $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
                
                $proxyAttendanceLive = EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', date("Y-m-d",strtotime($pln->date)))->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';
                
                if(isset($pln->attendanceInformation->id)):
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
                else:
                    $classLabel .= '<span class="text-danger font-medium">Completed But No Attendance Found</span>';
                endif;

                    $html .= '<tr class="intro-x">';
                        $html .= '<td>';
                            $html .= '<div class="font-fedium">'.date('jS M, Y', strtotime($pln->date.' '.$pln->plan->start_time)).'</div>';
                            $html .= '<div class="text-slate-500">'.date('H:i ', strtotime($pln->date.' '.$pln->plan->start_time)).' - '.date('H:i ', strtotime($pln->date.' '.$pln->plan->end_time)).'</div>';
                        $html .= '</td>';
                        $html .= '<td>';
                            $html .= '<div class="flex items-center">';
                                $html .= '<div>';
                                    $html .= '<a href="'.route('tutor-dashboard.plan.module.show', $pln->plan_id).'" class="font-medium whitespace-nowrap">'.(isset($pln->plan->creations->module->name) && !empty($pln->plan->creations->module->name) ? $pln->plan->creations->module->name : 'Unknown').(isset($pln->plan->class_type) && !empty($pln->plan->class_type) ? ' - '.$pln->plan->class_type : '').'</a>';
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->plan->course->name) && !empty($pln->plan->course->name) ? $pln->plan->course->name : 'Unknown'). ' <span class="rounded bg-primary text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto ml-1 px-3 py-0.5"> '.$pln->plan->group->name .' </spane></div>';
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
                        $html .= '<td class="text-left">';
                            $html .= '<span class="flex justify-start items-center">';
                            $html .= '<div class="mt-2 flex flex-col sm:flex-row">';
                            $html .=   '<div data-tw-merge class="flex items-center mr-2 "><input id="radio-switch-'.$pln->id.'" data-tw-merge data-id="'.$pln->id.'"  name="class_file_upload_found'.$pln->id.'" value="Yes"  type="radio" '.(isset($pln->class_file_upload_found) && !empty($pln->class_file_upload_found) && $pln->class_file_upload_found=="Yes" ? 'checked' : '').' class="class-fileupload transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type=\'radio\']]:checked:bg-primary [&[type=\'radio\']]:checked:border-primary [&[type=\'radio\']]:checked:border-opacity-10 [&[type=\'checkbox\']]:checked:bg-primary [&[type=\'checkbox\']]:checked:border-primary [&[type=\'checkbox\']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />';
                            $html .=      '<label data-tw-merge for="radio-switch-'.$pln->id.'" class="cursor-pointer ml-2">Yes</label>';
                            $html .=   '</div>';
                            $html .=   '<div data-tw-merge class="flex items-center mr-2 mt-2 sm:mt-0 "><input id="radio-switch2-'.$pln->id.'" data-tw-merge data-id="'.$pln->id.'"   name="class_file_upload_found'.$pln->id.'" value="No" type="radio" '.(isset($pln->class_file_upload_found) && !empty($pln->class_file_upload_found) && $pln->class_file_upload_found=="No" ? 'checked' : '').' class="class-fileupload transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type=\'radio\']]:checked:bg-primary [&[type=\'radio\']]:checked:border-primary [&[type=\'radio\']]:checked:border-opacity-10 [&[type=\'checkbox\']]:checked:bg-primary [&[type=\'checkbox\']]:checked:border-primary [&[type=\'checkbox\']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"   />';
                            $html .=       '<label data-tw-merge for="radio-switch2-'.$pln->id.'" class="cursor-pointer ml-2">No</label>';
                            $html .=   '</div>';
                            $html .= '</div>';
                            $html .= '</span>';
                        $html .= '</td>';
                        $html .= '<td class="text-right">';
                        $html .= '</td>';
                    $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="7">';
                    $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan found for the selected date.</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return $html;
    }
    public function getClassInformations(Request $request){
        $uploadedType = $request->planClassStatus;
        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
        
        $res = [];
        $res['planTable'] = $this->getClassInfoHtml(date("Y-m-d"), $planCourseId, $uploadedType);;
        $res['totalUndecidedCount'] = $this->totalUndecidedCount();
        
        return response()->json(['res' => $res], 200);
    }
    public function UpdateClassStatus(Request $request) {
        $uploadedType = $request->planClassStatus;
        $plans_date_list_id = $request->plansDateListId;
        $class_file_upload_found = $request->classFileUploadFound;

        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
  
        $pdl = PlansDateList::find($plans_date_list_id);
        $pdl->class_file_upload_found = $class_file_upload_found;
        $pdl->save();
        
        $res['planTable'] = $this->getClassInfoHtml(date("Y-m-d"), $planCourseId, $uploadedType);
        $res['totalUndecidedCount'] = $this->totalUndecidedCount();
        if($pdl->wasChanged()){
        return response()->json(['res' => $res], 200);
        } else
            return response()->json(['res' => $res], 422);
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

    public function getStudentAttenTrackingHtml(Request $request){
        $user_id = auth()->user()->id;
        $theDate = (isset($request->theDate) && !empty($request->theDate) ? date('Y-m-d', strtotime($request->theDate)) : Carbon::yesterday()->format('Y-m-d'));
        $res = [];
        
        $tutor_plans = PlansDateList::where('date', $theDate)->whereHas('plan', function($q) use($user_id){
            $q->where('tutor_id', $user_id)->orWhere('personal_tutor_id', $user_id)->orWhereHas('tutorial', function($sq) use($user_id){
                $sq->where('personal_tutor_id', $user_id);
            });
        })->get();
        $date_list_ids = $tutor_plans->pluck('id')->unique()->toArray();
        $plan_ids = $tutor_plans->pluck('plan_id')->unique()->toArray();

        $assigns = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
            $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
        })->pluck('student_id')->unique()->toArray();

        if(!empty($assigns)):
            foreach($assigns as $student_id):
                $student = Student::find($student_id);
                $student_modules = $this->getStudentModules($student->id, $plan_ids, $theDate);
                if($student_modules):
                    $res[$student_id] = [
                        'student_id' => $student->id,
                        'registration_no' => $student->registration_no,
                        'mobile' => (isset($student->contact->mobile) && !empty($student->contact->mobile) ? $student->contact->mobile : ''),
                        'photo_url' => $student->photo_url,
                        'name' => $student->full_name,
                        'course' => (isset($student->activeCR->creation->course->name) && !empty($student->activeCR->creation->course->name) ? $student->activeCR->creation->course->name : ''),
                        'attendance' => $this->getStudentAttendanceRate($student->id, $plan_ids),
                        'modules' => $this->getStudentModules($student->id, $plan_ids, $theDate)
                    ];
                endif;
            endforeach;
        endif;
        usort($res, fn($a, $b) => $a['attendance'] <=> $b['attendance']);

        $html = '';
        if(!empty($res)):
            foreach($res as $row):
                $html .= '<tr class="intro-x">';
                    $html .= '<td class="w-40">';
                        $html .= '<div class="flex items-center">';
                            $html .= '<div class="w-10 h-10 image-fit zoom-in">';
                                $html .= '<img alt="'.$row['name'].'" class="tooltip rounded-full" src="'.$row['photo_url'].'">';
                            $html .= '</div>';
                            $html .= '<a href="'.route('student.show', $row['student_id']).'" class="font-medium whitespace-nowrap ml-3">'.$row['registration_no'].'</a>';
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<a href="'.route('student.show', $row['student_id']).'" class="font-medium whitespace-nowrap">'.$row['name'].'</a>';
                        $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.$row['course'].'</div>';
                        if(!empty($row['mobile'])):
                            $html .= '<div class="text-slate-500 text-xs font-medium whitespace-nowrap mt-0.5">'.$row['mobile'].'</div>';
                        endif;
                    $html .= '</td>';
                    $html .= '<td class="text-center">'.number_format($row['attendance'], 2).'%</td>';
                    $html .= '<td class="text-left">';
                        if(isset($row['modules']) && !empty($row['modules'])):
                            $m = 1;
                            foreach($row['modules'] as $mod):
                                $html .= '<div class="'.($m != count($mod) ? 'mb-1' : '').' flex items-start justify-start text-'.($mod['status'] == 1 ? 'success' : 'danger').'">';
                                    $html .= '<i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> '.$mod['module'];
                                    $html .= (isset($mod['type']) && !empty($mod['type']) ? ' ('.$mod['type'].')' : '');
                                    $html .= (isset($mod['group']) && !empty($mod['group']) ? ' ('.$mod['group'].')' : '');
                                    $html .= (!empty($mod['start']) ? ' ('.$mod['start'].(!empty($mod['end']) ? ' - '.$mod['end'] : '').')' : '');
                                $html .= '</div>';

                                $m++;
                            endforeach;
                        endif;
                    $html .= '</td>';
                    $html .= '<td class="table-report__action w-56">';
                        $html .= '<div class="flex justify-center items-center">';
                            $html .= '<a class="addNoteBtn flex items-center mr-3" data-tw-toggle="modal" data-tw-target="#addNoteModal" href="javascript:void(0);" data-student="'.$row['student_id'].'">';
                                $html .= '<i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Note';
                            $html .= '</a>';
                            $html .= '<a class="addSmsBtn flex items-center text-danger" href="javascript:void(0);" data-student="'.$row['student_id'].'" data-tw-toggle="modal" data-tw-target="#smsSMSModal">';
                                $html .= '<i data-lucide="tablet-smartphone" class="w-4 h-4 mr-1"></i> Send SMS';
                            $html .= '</a>';
                        $html .= '</div>';
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-pending-soft show flex items-center" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="alert-circle" class="lucide lucide-alert-circle w-6 h-6 mr-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg>
                                Assiged student not foud for the day.
                            </div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getStudentAttendanceRate($student_id, $plan_ids){
        $query = DB::table('attendances as atn')
                ->select(
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                )->whereIn('atn.plan_id', $plan_ids)->where('atn.student_id', $student_id)->get()->first();
        return (isset($query->percentage_withexcuse) && $query->percentage_withexcuse > 0 ? $query->percentage_withexcuse : 0);
    }

    public function getStudentModules($student_id, $plan_ids, $theDate){
        $student_plans = Assign::where('student_id', $student_id)->whereIn('plan_id', $plan_ids)->pluck('plan_id')->unique()->toArray();
        $res = [];
        $absentCount = 0;
        if(!empty($student_plans)):
            foreach($student_plans as $plan_id):
                $attendance = Attendance::where('student_id', $student_id)->where('attendance_date', $theDate)->where('plan_id', $plan_id)->get()->first();
                if(isset($attendance->id) && $attendance->id > 0):
                    $plan = Plan::find($plan_id);
                    $res[$plan_id] = [
                        'module' => $plan->creations->module_name,
                        'group' => (isset($plan->group->name) && !empty($plan->group->name) ? $plan->group->name : ''),
                        'type' => $plan->class_type,
                        'start' => (!empty($plan->start_time) ? date('H:i', strtotime($plan->start_time)) : ''),
                        'end' => (!empty($plan->end_time) ? date('H:i', strtotime($plan->end_time)) : ''),
                        'status' => (isset($attendance->attendance_feed_status_id) && $attendance->attendance_feed_status_id != 4 ? 1 : 0)
                    ];
                    $absentCount = (isset($attendance->attendance_feed_status_id) && $attendance->attendance_feed_status_id == 4 ? 1 : 0);
                endif;
            endforeach;
        endif;
        return (!empty($res) && $absentCount > 0 ? $res : false);
    }

    public function getTermStatistics(Request $request){
        $id = auth()->user()->id;
        $term_id = (isset($request->term_id) && $request->term_id > 0 ? $request->term_id : 0);
        
        $html = '';
        if($term_id > 0):
            $plans = Plan::where('personal_tutor_id', $id)->where('term_declaration_id', $term_id)->orderBy('term_declaration_id', 'DESC')->get();
            $plan_ids = $plans->pluck('id')->unique()->toArray();
            $myModules = DB::table('plans')->select('class_type', DB::raw('COUNT(DISTINCT id) as TOTAL_MODULE'))
                        ->where('term_declaration_id', $term_id)->where('personal_tutor_id', $id)
                        ->groupBy('class_type')->orderBy('class_type', 'ASC')->get();
            $no_of_assigned = Assign::whereIn('plan_id', $plan_ids)->where(function($q){
                                $q->whereNull('attendance')->orWhere('attendance', 1)->orWhere('attendance', '');
                            })->distinct()->count('student_id');
            $no_of_assignment = $this->getAssignmentCount($id, $term_id);
            $attendance_avg = $this->myModulesAttendanceAverage($id, $term_id);
            $bellow_60 = $this->myModulesAttendanceBellow($id, $term_id);

            $html .= '<div class="grid grid-cols-12 gap-y-8 gap-x-10">';
                $html .= '<div class="col-span-6 sm:col-span-6">';
                    $html .= '<div class="text-slate-500">No of Module</div>';
                    $html .= '<div class="mt-1.5 flex items-center">';
                        $html .= '<div id="totalModule" class="text-base">';
                            if($myModules->count() > 0):
                                foreach($myModules as $mm):
                                    if($mm->TOTAL_MODULE > 0):
                                        $html .= '<span class="bg-slate-200 px-2 py-1 mr-1 text-xs rounded font-medium text-primary">'.$mm->class_type.': '.$mm->TOTAL_MODULE.'</span>';
                                    endif;
                                endforeach;
                            else:
                                $html .= '<span class="bg-slate-200 px-2 py-1 mr-1 text-xs rounded font-medium">0 Modules</span>';
                            endif;
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-12 sm:col-span-6">';
                    $html .= '<div class="text-slate-500">No of Student</div>';
                    $html .= '<div class="mt-1.5 flex items-center">';
                        $html .= '<div class="text-base">'.$no_of_assigned.'</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-12 sm:col-span-6">';
                    $html .= '<div class="text-slate-500">Expected Assignments</div>';
                    $html .= '<div class="mt-1.5 flex items-center">';
                        $html .= '<div class="text-base">'.($no_of_assignment).'</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="col-span-12 sm:col-span-6">';
                    $html .= '<div class="text-slate-500">Average Attendance</div>';
                    $html .= '<div class="mt-1.5 flex items-center">';
                        $html .= '<div class="text-base">'.$attendance_avg.'</div>';
                    $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="col-span-12 sm:col-span-6"></div>';

                $html .= '<div class="col-span-12 sm:col-span-6">';
                    $html .= '<div class="text-slate-500">Attendance Bellow 60%</div>';
                    $html .= '<div class="mt-1.5 flex items-center">';
                        $html .= '<a target="_blank" href="'.route('attendance.percentage', [auth()->user()->id, $term_id]).'" class="text-base font-medium underline">'.$bellow_60.'</a>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        else:
            $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert">';
                $html .= '<i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> No data found for the selected term.';
            $html .= '</div>';
        endif;

        return response()->json(['html' => $html], 200);
    }

}
