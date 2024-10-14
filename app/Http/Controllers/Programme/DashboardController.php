<?php

namespace App\Http\Controllers\Programme;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelClassRequest;
use App\Http\Requests\ReAssignClassRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Assign;
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
        $theDate = Date('Y-m-d'); //'2023-11-24';
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $theTerm = Plan::with('attenTerm')->whereIn('id', $classPlanIds)->orderBy('term_declaration_id', 'DESC')->get()->first();
        $theTermId = (isset($theTerm->attenTerm->id) && $theTerm->attenTerm->id > 0 ? $theTerm->attenTerm->id : 0);
        $usedCourses = Plan::where('term_declaration_id', $theTermId)->pluck('course_id')->unique()->toArray();

        return view('pages.programme.dashboard.index', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'theDate' => date('d-m-Y', strtotime($theDate)),
            'theTerm' => $theTerm,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'classInformation' => $this->getClassInfoHtml($theDate),
            'classTutor' => $this->getClassTutorsHtml($theDate),
            'classPTutor' => $this->getClassPersonalTutorsHtml($theDate),
            'absentToday' => $this->getAbsentEmployees(date('Y-m-d')),
            'termAttendanceRates' => $this->getTermAttendanceRateFull($theTermId),
            'tutors' => User::with('employee')->whereHas('employee', function($q){
                $q->where('status', 1);
            })->orderBy('name', 'ASC')->get()
        ]);
    }

    public function getClassInformations(Request $request){
        $planClassStatus = $request->planClassStatus;
        $planCourseId = (isset($request->planCourseId) && $request->planCourseId > 0 ? $request->planCourseId : 0);
        $theClassDate = (isset($request->theClassDate) && !empty($request->theClassDate) ? date('Y-m-d', strtotime($request->theClassDate)) : date('Y-m-d'));

        $res = [];
        $res['planTable'] = $this->getClassInfoHtml($theClassDate, $planCourseId, $planClassStatus);
        $res['tutors'] = $this->getClassTutorsHtml($theClassDate, $planCourseId);
        $res['ptutors'] = $this->getClassPersonalTutorsHtml($theClassDate, $planCourseId);

        return response()->json(['res' => $res], 200);
    }

    public function getClassInfoHtml($theDate = null, $course_id = 0, $planClassStatus = 'All'){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');

        $html = '';
        /*$classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $query = Plan::with('tutor')->whereIn('id', $classPlanIds); 
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $query = $query->orderBy('start_time', 'ASC')->get();*/

        $query = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('date', $theDate)->whereHas('plan', function($q) use($course_id){
                    if($course_id > 0):
                        $q->where('course_id', $course_id);
                    endif;
                });
        if($planClassStatus != 'All'):
            $query->where('status', $planClassStatus);
        endif;
        $plans = $query->get()->sortBy(function($planDates, $key) {
                    return $planDates->plan->start_time;
                });

        if(!empty($plans) && $plans->count() > 0):
            $currentTime = date('Y-m-d H:i:s');
            foreach($plans as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $PerTutorEmployeeId = (isset($pln->plan->personalTutor->employee->id) && $pln->plan->personalTutor->employee->id > 0 ? $pln->plan->personalTutor->employee->id : 0);
                $classTutor = ($tutorEmployeeId > 0 ? $tutorEmployeeId : ($PerTutorEmployeeId > 0 ? $PerTutorEmployeeId : 0));
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $classTutor)->where('date', $theDate)->where('attendance_type', 1)->get();

                $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
                $proxyAttendanceLive = EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', $theDate)->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';
                $orgStart = date('Y-m-d H:i:s', strtotime($theDate.' '.$pln->plan->start_time));
                $orgEnd = date('Y-m-d H:i:s', strtotime($theDate.' '.$pln->plan->end_time));

                if(date('Y-m-d', strtotime($currentTime)) < date('Y-m-d', strtotime($orgStart))):
                    $classLabel = '<span class="text-info font-medium">Scheduled</span>';
                elseif($currentTime < $orgStart && !isset($pln->attendanceInformation->id)):
                    $classLabel = '<span class="text-info font-medium">Scheduled</span>';
                elseif($currentTime > $orgStart && $currentTime < $orgEnd && !isset($pln->attendanceInformation->id)):
                    $classLabel = '<span class="text-pending font-medium flashingText">Starting Shortly</span>';
                elseif(isset($pln->attendanceInformation->id)):
                    if($pln->feed_given == 1 && $pln->attendances->count() > 0):
                        $classLabel .= '<span class="btn-rounded btn font-medium btn-success text-white p-0 w-9 h-9 mr-1" style="flex: 0 0 36px;">A</span>';
                    endif;
                    if(!empty($pln->attendanceInformation->start_time) && empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">Started '.date('H:i', strtotime($pln->attendanceInformation->start_time)).'</span>';
                    elseif(!empty($pln->attendanceInformation->start_time) && !empty($pln->attendanceInformation->end_time)):
                        $classLabel .= '<span class="text-success font-medium">';
                            $classLabel .= 'Started '.date('H:i', strtotime($pln->attendanceInformation->start_time)).'<br/>'; 
                            $classLabel .= 'Finished '.date('H:i', strtotime($pln->attendanceInformation->end_time)); 
                        $classLabel .= '</span>';
                    endif;
                elseif($currentTime > $orgEnd && !isset($pln->attendanceInformation->id)):
                    $classLabel .= '<span class="text-danger font-medium">Not Started</span>';
                endif;

                $html .= '<tr class="intro-x" data-planListId="'.$pln->id.'">';
                    $html .= '<td>';
                        $html .= '<span class="font-fedium">'.date('H:i', strtotime($theDate.' '.$pln->plan->start_time)).'</span>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<div class="flex items-center">';
                            $html .= '<div>';
                                $html .= '<a href="'.route('tutor-dashboard.plan.module.show', $pln->plan_id).'" class="font-medium whitespace-nowrap">'.(isset($pln->plan->creations->module->name) && !empty($pln->plan->creations->module->name) ? $pln->plan->creations->module->name : 'Unknown').(isset($pln->plan->class_type) && !empty($pln->plan->class_type) ? ' - '.$pln->plan->class_type : '').'</a>';
                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'.(isset($pln->plan->course->name) && !empty($pln->plan->course->name) ? $pln->plan->course->name : 'Unknown').'</div>';
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
                            if(isset($pln->plan->group->name) && !empty($pln->plan->group->name)):
                                if(strlen($pln->plan->group->name) > 2):
                                    $html .= '<div class="ml-auto mr-4 rounded text-lg bg-success whitespace-nowrap text-white cursor-pointer font-medium w-auto px-2 py-1 h-auto inline-flex justify-center items-center">'.$pln->plan->group->name.'</div>';
                                else:
                                    $html .= '<div class="ml-auto mr-4 rounded-full text-lg bg-success text-white cursor-pointer font-medium w-10 h-10 inline-flex justify-center items-center">'.$pln->plan->group->name.'</div>';
                                endif;
                            endif;
                        $html .= '</div>';
                    $html .= '</td>';
                    $html .= '<td class="text-left">';
                        if($pln->plan->tutor_id > 0): 
                            $html .= '<div class="flex justify-start items-center '.(!empty($pln->proxy_reason) && $pln->proxy_tutor_id > 0 ? 'tooltip' : '').'" '.(!empty($pln->proxy_reason) && $pln->proxy_tutor_id > 0 ? ' title="'.$pln->proxy_reason.'" ' : '').'>';
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
                            $html .= '<div class="flex justify-start items-center '.(!empty($pln->proxy_reason) && $pln->proxy_tutor_id > 0 ? 'tooltip' : '').'" '.(!empty($pln->proxy_reason) && $pln->proxy_tutor_id > 0 ? ' title="'.$pln->proxy_reason.'" ' : '').'>';
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
                        $btnHtml = '';
                        if($pln->status == 'Scheduled' && $pln->feed_given != 1 && $orgEnd < $currentTime):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a href="'.route('attendance.create', $pln->id).'" class="cancelClass dropdown-item text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="view" class="lucide lucide-view w-4 h-4 mr-3"><path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z"></path><path d="M12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path><path d="M21 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2"></path><path d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"></path></svg> Feed Attendance</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($pln->status == 'Completed'):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a href="'.route('tutor-dashboard.attendance', [($pln->plan->tutor_id > 0 ? $pln->plan->tutor_id : $pln->plan->personal_tutor_id), $pln->id, 2]).'" class="cancelClass dropdown-item text-primary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="view" class="lucide lucide-view w-4 h-4 mr-3"><path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z"></path><path d="M12 13a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"></path><path d="M21 17v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2"></path><path d="M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"></path></svg> '.($pln->feed_given == 1 && $pln->attendances->count() > 0 ? 'View Feed' : 'Feed Attendance').'</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($pln->status == 'Scheduled' && ($orgStart > $currentTime || ($orgStart < $currentTime && $orgEnd > $currentTime)) && ($pln->proxy_tutor_id == null || $pln->proxy_tutor_id == 0)):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#proxyClassModal" href="javascript:void(0);" class="proxyClass text-success dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4 mr-3"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg> Swap Class</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($pln->status == 'Scheduled' || $pln->status == 'Unknown'):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#cancelClassModal" href="javascript:void(0);" class="cancelClass text-danger dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x-circle" class="lucide lucide-x-circle w-4 h-4 mr-3"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg> Cancel Class</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($pln->status == 'Ongoing' && $pln->feed_given != 1):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a href="'.route('tutor-dashboard.attendance', [($pln->plan->tutor_id > 0 ? $pln->plan->tutor_id : $pln->plan->personal_tutor_id), $pln->id, 2]).'" class="cancelClass text-success dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x-circle" class="lucide lucide-x-circle w-4 h-4 mr-3"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg> Feed Attendance</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($pln->status == 'Ongoing' && $pln->feed_given == 1 && $orgEnd < $currentTime):
                            $btnHtml .= '<li>';
                                $btnHtml .= '<a data-attendanceinfo="'.$pln->attendanceInformation->id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#endClassModal" href="javascript:void(0);" class="endClassBtn text-danger dropdown-item"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="clock" class="lucide lucide-clock stroke-1.5 mr-2 h-4 w-4"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> End Class</a>';
                            $btnHtml .= '</li>';
                        endif;
                        if($btnHtml != ''):
                            $html .= '<div class="dropdown inline-block" data-tw-placement="bottom-end">';
                                $html .= '<a class="dropdown-toggle w-5 h-5" href="javascript:void(0);" aria-expanded="false" data-tw-toggle="dropdown"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="more-vertical" class="lucide lucide-more-vertical w-5 h-5 text-slate-500"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg></a>';
                                $html .= '<div class="dropdown-menu w-48">';
                                    $html .= '<ul class="dropdown-content">';
                                        $html .= $btnHtml;
                                    $html .= '</ul>';
                            $html .= '</div>';
                        endif;
                        /*if($pln->status == 'Scheduled' && ($orgStart > $currentTime || ($orgStart < $currentTime && $orgEnd > $currentTime))):
                            $html .= '<button data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#proxyClassModal" type="button" class="proxyClass btn-rounded btn btn-success text-white p-0 w-9 h-9"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg></button>';
                        endif;
                        if($pln->status == 'Scheduled' || $pln->status == 'Unknown'):
                            $html .= '<button data-planid="'.$pln->plan_id.'" data-plandateid="'.$pln->id.'" data-tw-toggle="modal" data-tw-target="#cancelClassModal" type="button" class="cancelClass ml-1 btn-rounded btn btn-danger text-white p-0 w-9 h-9"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="x-circle" class="lucide lucide-x-circle w-4 h-4"><circle cx="12" cy="12" r="10"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg></button>';
                        endif;*/
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

    public function getClassTutorsHtml($theDate = null, $course_id = 0){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $termDecs = Plan::whereIn('id', $classPlanIds);
        if($course_id > 0):
            $termDecs->where('course_id', $course_id);
        endif;
        $termDecs = $termDecs->orderBy('term_declaration_id', 'DESC')->get()->first();
        $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);
        
        $query = Plan::where('term_declaration_id', $termDecId);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
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

    public function getClassPersonalTutorsHtml($theDate = null, $course_id = 0){
        $theDate = !empty($theDate) ? $theDate : date('Y-m-d');
        $classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $termDecs = Plan::whereIn('id', $classPlanIds);
        if($course_id > 0):
            $termDecs->where('course_id', $course_id);
        endif;
        $termDecs = $termDecs->orderBy('term_declaration_id', 'DESC')->get()->first();
        $termDecId = (isset($termDecs->term_declaration_id) && $termDecs->term_declaration_id > 0 ? $termDecs->term_declaration_id : 0);

        $query = Plan::where('term_declaration_id', $termDecId);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $classTutors = $query->pluck('personal_tutor_id')->unique()->toArray();

        $html = '';
        $uttors = User::whereIn('id', $classTutors)->skip(0)->take(5)->get();
        if(!empty($uttors) && $uttors->count() > 0):
            foreach($uttors as $tut):
                $moduleCreations = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $termDecId)->where('class_type', 'Tutorial')->pluck('module_creation_id')->toArray();
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

    public function tutors($term_declaration_id, $course_id = 0){
        $usedCourses = Plan::where('term_declaration_id', $term_declaration_id)->pluck('course_id')->unique()->toArray();
        //$tutorIds = Plan::where('term_declaration_id', $term_declaration_id)->pluck('tutor_id')->unique()->toArray();
        $query = Plan::where('term_declaration_id', $term_declaration_id);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $tutorIds = $query->pluck('tutor_id')->unique()->toArray();

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
            'tutors' => $res,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'selected_course' => $course_id
        ]);
    }

    public function tutorsDetails($term_declaration_id, $tutorid){
        $plans = [];
        $tutorPlans = Plan::where('term_declaration_id', $term_declaration_id)->where('tutor_id', $tutorid)->whereNotIn('class_type', ['Tutorial', 'Seminar'])->get();
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


    public function personalTutors($term_declaration_id, $course_id = 0){
        $usedCourses = Plan::where('term_declaration_id', $term_declaration_id)->pluck('course_id')->unique()->toArray();
        $query = Plan::where('term_declaration_id', $term_declaration_id);
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $tutorIds = $query->pluck('personal_tutor_id')->unique()->toArray();
        
        $theDate = Date('Y-m-d'); //'2023-11-24';

        
        $res = [];
        $tutors = User::whereIn('id', $tutorIds)->orderBy('id', 'ASC')->get();
        if(!empty($tutors)):
            foreach($tutors as $tut):

                $dateTerm = PlansDateList::with('plan')->where('date',$theDate)->get()->first();
                $planDates = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('class_file_upload_found', "Undecided")->where('status','Completed')->whereHas('plan', function($q) use($dateTerm, $tut){
                    
                    
                        $q->where('personal_tutor_id', $tut->id);
                        $q->where('class_type', "Theory");
                        $q->where('term_declaration_id',$dateTerm->plan->term_declaration_id);


                })->get();

                $undecidedUploads =  $planDates->count();
                $employee = Employee::with('workingPattern')->where('user_id', $tut->id)->get()->first();
                $activePlans = Plan::where('personal_tutor_id', $tut->id)->where('term_declaration_id', $term_declaration_id)->where('class_type', 'Tutorial')->get();
                $plan_ids = $activePlans->pluck('id')->unique()->toArray();
                $assigns = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
                $moduleCreations = $activePlans->pluck('module_creation_id')->toArray();
                $groups = $activePlans->pluck('group_id')->unique()->toArray();
                $tut['no_of_module'] = (!empty($moduleCreations) ? count($moduleCreations) : 0);
                $tut['no_of_assigned'] = (!empty($assigns) ? count($assigns) : 0);
                $tut['no_of_group'] = (!empty($groups) ? count($groups) : 0);
                $res[$tut->id] = $tut;
                $res[$tut->id]['attendances'] = $this->getTermAttendanceRate($term_declaration_id, $tut->id, 2);
                $res[$tut->id]['undecidedUploads'] = $undecidedUploads;
                $res[$tut->id]['contracted_hour'] = (isset($employee->workingPattern->contracted_hour) && !empty($employee->workingPattern->contracted_hour) ? $employee->workingPattern->contracted_hour : '00:00');
            endforeach;
        endif;

        return view('pages.programme.dashboard.personal-tutors', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'termDeclaration' => TermDeclaration::find($term_declaration_id),
            'tutors' => $res,
            'courses' => Course::whereIn('id', $usedCourses)->orderBy('name')->get(),
            'selected_course' => $course_id
        ]);
    }


    public function personalTutorDetails($term_declaration_id, $tutorid){
        $plans = [];//->where('class_type', 'Tutorial')
        $tutorPlans = Plan::where('term_declaration_id', $term_declaration_id)->where('personal_tutor_id', $tutorid)->get();
        if($tutorPlans->count() > 0):
            foreach($tutorPlans as $tp):
                $planDates = PlansDateList::where('plan_id', $tp->id)->where('class_file_upload_found', "Undecided")->where('status','Completed')
                    ->whereHas('plan', function($q) use($term_declaration_id, $tutorid){  
                            $q->where('personal_tutor_id', $tutorid);
                            $q->where('class_type', "Theory");
                            $q->where('term_declaration_id', $term_declaration_id);
                    })->get();

                $plans[$tp->id] = $tp;
                $plans[$tp->id]['attendances'] = $this->getPlanAttendanceRate($tp->id);
                $plans[$tp->id]['undecidedUploads'] = $planDates->count();
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
        /*$planDateLists = PlansDateList::whereHas('plan', function($q) use($term_declaration_id, $tutor_field, $tutor_id){
            $q->where('term_declaration_id', $term_declaration_id)->where($tutor_field, $tutor_id);
            if($tutor_field == 'personal_tutor_id'):
                $q->where('class_type', 'Tutorial');
            else:
                $q->whereNotIn('class_type', ['Tutorial', 'Seminar']);
            endif;
        })->get();
        $plan_ids = $planDateLists->pluck('plan_id')->unique()->toArray();
        $date_ids = $planDateLists->pluck('id')->unique()->toArray();*/

        $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where($tutor_field, $tutor_id)->where(function($q) use($type){
            if($type == 2):
                $q->whereIn('class_type', ['Tutorial', 'Seminar']);
            else:
                $q->whereNotIn('class_type', ['Tutorial', 'Seminar']);
            endif;
        })->pluck('id')->unique()->toArray();
        
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

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $company_name,
        ];
        $MAILHTML = 'This is a class cancellation notice:<br/>';
        $MAILHTML .= 'Course Name: '.$courseName.'<br/>';
        $MAILHTML .= 'Module Name: '.$moduleName.'<br/>';
        $MAILHTML .= 'Group Name: '.$groupName.'<br/>';
        $MAILHTML .= 'Time: '.$classTime.'<br/>';
        $MAILHTML .= 'Tutor Name: '.$tutorName.'<br/><br/>';
        $MAILHTML .= 'Thanks & Regards <br/>'.$company_name;

        if($notify_student):
            if(isset($plan->assign) && $plan->assign->count() > 0):
                $sms_subject = 'Class Cancellation Notice';
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

                        $sms = $this->sendSms($mobile, $sms_body, $company_name);
                    endif;
                    
                    $NEWMAILHTML = 'Dear '.$student->full_name.',<br/><br/>'.$MAILHTML;
                    $studentEmail = StudentEmail::create([
                        'student_id' => $student->id,
                        'common_smtp_id' => (isset($commonSmtp->id) && $commonSmtp->id > 0 ? $commonSmtp->id : null),
                        'email_template_id' => null,
                        'subject' => $sms_subject,
                        'created_by' => auth()->user()->id,
                    ]);
                    if($studentEmail->id):
                        $emailPdf = $this->generateEmailPdf($studentEmail->id, $student->id, $sms_subject, $NEWMAILHTML);
                        $studentEmail = StudentEmail::where('id', $studentEmail->id)->update([
                            'mail_pdf_file' => $emailPdf
                        ]);

                        UserMailerJob::dispatch($configuration, $emails, new CommunicationSendMail($sms_subject, $NEWMAILHTML, []));
                    endif;
                endforeach;
            endif;
        endif;

        if($notify_tutors):
            $SUBJECT = 'Class Cancellation Notice From '.$company_name.' Account.';
            if(isset($plan->tutor_id) && $plan->tutor_id > 0):
                $NEWMAILHTML = 'Dear '.$plan->tutor->employee->full_name.',<br/><br/>'.$MAILHTML;
                $TEMAILS = [];
                if(isset($plan->tutor->employee->email) && !empty($plan->tutor->employee->email)):
                    $TEMAILS[] = $plan->tutor->employee->email;
                endif;
                if(isset($plan->tutor->employee->employment->email) && !empty($plan->tutor->employee->employment->email)):
                    $TEMAILS[] = $plan->tutor->employee->employment->email;
                endif;

                UserMailerJob::dispatch($configuration, $TEMAILS, new CommunicationSendMail($SUBJECT, $NEWMAILHTML, []));
            endif;
            if(isset($plan->personal_tutor_id) && $plan->personal_tutor_id > 0):
                $NEWMAILHTML = 'Dear '.$plan->personalTutor->employee->full_name.',<br/><br/>'.$MAILHTML;
                $TEMAILS = [];
                if(isset($plan->personalTutor->employee->email) && !empty($plan->personalTutor->employee->email)):
                    $TEMAILS[] = $plan->personalTutor->employee->email;
                endif;
                if(isset($plan->personalTutor->employee->employment->email) && !empty($plan->personalTutor->employee->employment->email)):
                    $TEMAILS[] = $plan->personalTutor->employee->employment->email;
                endif;

                UserMailerJob::dispatch($configuration, $TEMAILS, new CommunicationSendMail($SUBJECT, $NEWMAILHTML, []));
            endif;
        endif;

        return response()->json(['message' => 'Class status updated to canceled.'], 200);
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
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                        DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                    ->leftJoin('students as std', 'atn.student_id', 'std.id')
                    ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45])
                    ->whereIn('atn.plans_date_list_id', $date_ids);
        if(!empty($plan_ids)):
            $query->whereIn('atn.plan_id', $plan_ids);
        endif;
        if(!empty($student_ids)):
            $query->whereIn('atn.student_id', $student_ids);
        endif;
        $attendances = $query->get()->first();

        if(isset($attendances) && !empty($attendances)):
            $attendance = (isset($attendances->percentage_withexcuse) && $attendances->percentage_withexcuse > 0 ? $attendances->percentage_withexcuse : 0);
            if($attendance):
                return number_format($attendance, 2);
            else:
                return 0;
            endif;
        else:
            return 0;
        endif;
    }

    public function endClass(Request $request){
        $plan_date_list_id = $request->plan_date_list_id;
        $attendance_information_id = $request->attendance_information_id;

        $planDate = PlansDateList::with('plan')->find($plan_date_list_id);
        $endTime = (isset($planDate->plan->end_time) && !empty($planDate->plan->end_time) ? $planDate->plan->end_time : date('H:i:s'));

        $attendanceInformation = AttendanceInformation::find($attendance_information_id);
        $attendanceInformation->end_time = $endTime;
        $attendanceInformation->updated_by = Auth::user()->id;
        if($attendanceInformation->isDirty()):
            $attendanceInformation->save();
            PlansDateList::where('id', $plan_date_list_id)->update(['status' => 'Completed']);
            return response()->json(['data' => 'Class Ended' ], 200);
        else:
            return response()->json(['data' => 'error found' ], 422);
        endif;
    }

    public function reAssignClass(ReAssignClassRequest $request){
        $proxy_tutor_id = $request->proxy_tutor_id;
        $plan_id = $request->plan_id;
        $plans_date_list_id = $request->plans_date_list_id;

        $data = [];
        $data['proxy_tutor_id'] = $proxy_tutor_id;
        $data['proxy_reason'] = (isset($request->proxy_reason) && !empty($request->proxy_reason) ? $request->proxy_reason : null);
        $data['proxy_assigned_by'] = auth()->user()->id;
        $data['proxy_assigned_at'] = date('Y-m-d H:i:s');

        PlansDateList::where('id', $plans_date_list_id)->where('plan_id', $plan_id)->update($data);

        return response()->json(['message' => 'Class successfully re-assigned to new tutor.'], 200);
    }

    public function getUndecidedClass(Request $request){
        $tutor_id = $request->tutor_id;
        $term_id = $request->term_id;
        $plan_id = (isset($request->plan_id) && $request->plan_id > 0 ? $request->plan_id : 0);
        
        $html = '';
        $query = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->where('class_file_upload_found', 'Undecided')->where('status','Completed')
                    ->whereHas('plan', function($q) use($term_id, $tutor_id){
                        $q->where('personal_tutor_id', $tutor_id);
                        $q->where('class_type', "Theory");
                        $q->where('term_declaration_id', $term_id);
                    });
        if($plan_id > 0):
            $query->where('plan_id', $plan_id);
        endif;
        $planDates = $query->get()->sortBy(function($planDates, $key) {
            return date("Y-m-d H:i", strtotime($planDates->date." ".$planDates->plan->start_time));
        });

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
                    /*$html .= '<td class="text-left">';
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
                    $html .= '<td class="text-right"></td>';*/
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="intro-x">';
                $html .= '<td colspan="5">';
                    $html .= '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> No calss plan found for the selected date.</div>';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return response()->json(['htm' => $html], 200);
    }
}
