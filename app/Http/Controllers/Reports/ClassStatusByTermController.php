<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EmployeeAttendanceLive;
use App\Models\Group;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\TermDeclaration;
use App\Models\User;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ClassStatusByTermController extends Controller
{
    public function index(){

        return view('pages.reports.class-status.index', [
            'title' => 'Status Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Status Reports', 'href' => 'javascript:void(0);']
            ],
         
            'terms' => TermDeclaration::all()->sortByDesc('id'),
          

        ]);
    }

    public function list(Request $request){
        
        $termDeclarationId = (isset($request->attendance_semester) && $request->attendance_semester > 0 ? $request->attendance_semester : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        

        // Fetch the plans related to the term_declaration_id
        //data
        $plans = Plan::where('term_declaration_id', $termDeclarationId[0])->get();
        $planList = $plans->pluck('id')->toArray();

        if($plans->isEmpty()):
            return response()->json(['last_page' => 0, 'data' => []]);
        endif;
        
        $Query = PlansDateList::with(['plan','plan.course','plan.attenTerm'])->whereIn('plan_id', $planList)->orderBy('date', 'DESC')->get();
       
        $planDatelistToArray = $Query->pluck('id')->toArray();
        $data = array();
        
        if(!empty($Query)):
            $i = 1;
            $held = [];
            $cancelled = [];
            $unknown = [];
            $proxy = [];
            $futureScheduleCount = [];
            $totalSchedule = [];
            $groupSet   =   [];
            $groupSet = [];
            $groupInfo = [];
            foreach($Query as $list):

                
                $startDate = Carbon::parse($list->date)->format('d-m-Y') . ' ' . Carbon::parse($list->plan->start_time)->format('H:i');
                $classScheduleStartTime = Carbon::parse($startDate);

                if ($classScheduleStartTime->isFuture()) {
                    if(!isset($futureScheduleCount[$list->plan->course->name]))
                        $futureScheduleCount[$list->plan->course->name] = 0;

                    $futureScheduleCount[$list->plan->course->name] +=  1 ;
                } 
                    
                if(!isset($totalSchedule[$list->plan->course->name]))
                        $totalSchedule[$list->plan->course->name] = 0;
                    

                    $totalSchedule[$list->plan->course->name] +=  1;
                

                if($list->status == 'Completed'):
                    if(!isset($held[$list->plan->course->name]))
                        $held[$list->plan->course->name] = 0;
                    
                    $held[$list->plan->course->name] +=  1;

                elseif($list->status == 'Canceled'):
                    if(!isset($cancelled[$list->plan->course->name]))
                        $cancelled[$list->plan->course->name] = 0;

                    $cancelled[$list->plan->course->name] += 1;
                elseif($list->status == 'Unknown'):
                    if(!isset($unknown[$list->plan->course->name]))
                        $unknown[$list->plan->course->name] = 0;
                    $unknown[$list->plan->course->name] +=  1;
                
                endif;
                if($list->proxy_tutor_id  != null):
                    if(!isset($proxy[$list->plan->course->name]))
                        $proxy[$list->plan->course->name] = 0;
                    $proxy[$list->plan->course->name] += 1;
                endif;
                
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['name'] = $list->plan->group->name;
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['schedule'] = isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['schedule']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['schedule'] + 1 : 1;
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['future_schedule'] = $classScheduleStartTime->isFuture() ? (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['future_schedule']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['future_schedule'] + 1 : 1) : (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['future_schedule']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['future_schedule'] : 0);
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['held'] = $list->status == 'Completed' ? (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['held']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['held'] + 1 : 1) : (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['held']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['held'] : 0);
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['cancelled'] = $list->status == 'Canceled' ? (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['cancelled']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['cancelled'] + 1 : 1) : (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['cancelled']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['cancelled'] : 0);
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['unknown'] = $list->status == 'Unknown' ? (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['unknown']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['unknown'] + 1 : 1) : (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['unknown']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['unknown'] : 0);
                $groupInfo[$list->plan->course->name][$list->plan->group_id]['proxy'] = $list->proxy_tutor_id != null ? (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['proxy']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['proxy'] + 1 : 1) : (isset($groupInfo[$list->plan->course->name][$list->plan->group_id]['proxy']) ? $groupInfo[$list->plan->course->name][$list->plan->group_id]['proxy'] : 0);

                $data[$list->plan->course->name] = [
                    'id'=> 0,    
                    'plans'=>$planList,
                    'course_name' => $list->plan->course->name,
                    'term_name' => $list->plan->attenTerm->name,
                    'schedule'=> isset($totalSchedule[$list->plan->course->name]) ? $totalSchedule[$list->plan->course->name] :'',
                    'future_schedule'=> isset($futureScheduleCount[$list->plan->course->name]) ? $futureScheduleCount[$list->plan->course->name] : '',
                    'held'  => isset($held[$list->plan->course->name]) ? $held[$list->plan->course->name] : '',
                    'cancelled' => isset($cancelled[$list->plan->course->name]) ? $cancelled[$list->plan->course->name] : '',
                    'unknown' => isset($unknown[$list->plan->course->name]) ? $unknown[$list->plan->course->name] : '',
                    'proxy' => isset($proxy[$list->plan->course->name]) ? $proxy[$list->plan->course->name] : '',
                    '_children' => [],
                    
                ];

                
                
                $groupSet[$list->plan->course->name][] = $list->plan->group_id;  

                // Step 1: Convert associative arrays to serialized strings
                $serializedGroupSet = array_map('serialize', $groupSet);

                // Step 2: Use array_unique to remove duplicates
                $uniqueSerializedGroupSet = array_unique($serializedGroupSet);

                // Step 3: Convert serialized strings back to associative arrays
                $uniqueGroupSet = array_map('unserialize', $uniqueSerializedGroupSet);

                $groupSet = $uniqueGroupSet;
                

            endforeach;
            
            //$total_rows = count($data);
            
            $groupSetTabulator = [];
            foreach($data as $key => $value):
                $data[$key]['id'] = $i++;
                $plans = $value['plans'];
                $i = 1;
                $groupsList = array_unique($groupSet[$key]);
                sort($groupsList);
                
                 foreach ($groupsList as $group_id):
                    
                    $data[$key]['_children'][] = [
                                    'id' => $i++,
                                    'group_id' => $group_id,
                                    'term_id' => $termDeclarationId[0],
                                    'course_name' => $groupInfo[$key][$group_id]['name'] ? $groupInfo[$key][$group_id]['name'] : '',
                                    'course_id' => $key,
                                    'schedule' => $groupInfo[$key][$group_id]['schedule'] ? $groupInfo[$key][$group_id]['schedule'] : '',
                                    'future_schedule' => $groupInfo[$key][$group_id]['future_schedule'] ? $groupInfo[$key][$group_id]['future_schedule'] : '',
                                    'held' => $groupInfo[$key][$group_id]['held'] ? $groupInfo[$key][$group_id]['held'] : '',
                                    'cancelled' => $groupInfo[$key][$group_id]['cancelled'] ? $groupInfo[$key][$group_id]['cancelled'] : '',
                                    'unknown' => $groupInfo[$key][$group_id]['unknown'] ? $groupInfo[$key][$group_id]['unknown'] : '',
                                    'proxy' => $groupInfo[$key][$group_id]['proxy'] ? $groupInfo[$key][$group_id]['proxy'] : '',
                                    
                                ];
                    $groupSetTabulator[$key][$group_id]['planDateList'] = $planDatelistToArray;
                    $groupSetTabulator[$key][$group_id]['schedule'] = $groupInfo[$key][$group_id]['schedule'];
                    $groupSetTabulator[$key][$group_id]['future_schedule'] = $groupInfo[$key][$group_id]['future_schedule'];
                    $groupSetTabulator[$key][$group_id]['held'] = $groupInfo[$key][$group_id]['held'];
                    $groupSetTabulator[$key][$group_id]['cancelled'] = $groupInfo[$key][$group_id]['cancelled'];
                    $groupSetTabulator[$key][$group_id]['unknown'] = $groupInfo[$key][$group_id]['unknown'];
                    $groupSetTabulator[$key][$group_id]['proxy'] = $groupInfo[$key][$group_id]['proxy'];

                  endforeach;
                  usort($data[$key]['_children'], function($a, $b) {
                    return strcmp($a['course_name'], $b['course_name']);
                });

            endforeach;
            Session::put('groupSetTabulator', $groupSetTabulator);
            $data = array_values($data);
        endif;
        
        return response()->json($data);
    }
    
    public function scheduleList($group, $course, $term){ 

        // $held = $request->held;
        // $cancelled = $request->cancelled;
        // $unknown = $request->unknown;
        // $proxy = $request->proxy;
        // $futureScheduleCount = $request->futureScheduleCount;
        // $totalSchedule = $request->totalSchedule;
       
        $theTerm   =   TermDeclaration::find($term);

        $tabulatorData = Session::get('groupSetTabulator');

        
        $groupSetTabulator = $tabulatorData[$course][$group];
        
        $planDateList = $groupSetTabulator['planDateList'];
        $futureScheduleCount = $groupSetTabulator['future_schedule'];
        $totalSchedule = $groupSetTabulator['schedule'];
        $held = $groupSetTabulator['held'];
        $cancelled = $groupSetTabulator['cancelled'];
        $unknown = $groupSetTabulator['unknown'];
        $proxy = $groupSetTabulator['proxy'];

        $planClassStatus =  'All';


        return view('pages.reports.class-status.details', [
            'title' => 'Programme Dashboard - London Churchill College',
            'breadcrumbs' => [],

            'theDate' => '',
            'theTerm' => $theTerm,
            'courses' => Course::where('name', $course)->get(),
            'course' => $course,
            'classInformation' => $this->getClassInfoHtml($planDateList, $group, $planClassStatus),
            'futureScheduleCount' => $futureScheduleCount,
            'totalSchedule' => $totalSchedule,
            'held' => $held,
            'cancelled' => $cancelled,
            'unknown' => $unknown,
            'proxy' => $proxy,
            'tutors' => User::with('employee')->whereHas('employee', function($q){
                $q->where('status', 1);
            })->orderBy('name', 'ASC')->get()
        ]);
    }

    protected function getClassInfoHtml($planDateList = null, $group_id = 0, $planClassStatus = 'All'){
        
        $html = '';
        /*$classPlanIds = PlansDateList::where('date', $theDate)->pluck('plan_id')->unique()->toArray();
        $query = Plan::with('tutor')->whereIn('id', $classPlanIds); 
        if($course_id > 0):
            $query->where('course_id', $course_id);
        endif;
        $query = $query->orderBy('start_time', 'ASC')->get();*/

        $query = PlansDateList::with('plan', 'attendanceInformation', 'attendances')->whereIn('id', $planDateList)->whereHas('plan', function($q) use($group_id){
                    if($group_id > 0):
                        $q->where('group_id', $group_id);
                    endif;
                });
        if($planClassStatus != 'All'):
            $query->where('status', $planClassStatus);
        endif;
        $plans = $query->orderBy('date','ASC')->get();

        if(!empty($plans) && $plans->count() > 0):
            $currentTime = date('Y-m-d H:i:s');
            $i = 1;
            foreach($plans as $pln):
                $tutorEmployeeId = (isset($pln->plan->tutor->employee->id) && $pln->plan->tutor->employee->id > 0 ? $pln->plan->tutor->employee->id : 0);
                $PerTutorEmployeeId = (isset($pln->plan->personalTutor->employee->id) && $pln->plan->personalTutor->employee->id > 0 ? $pln->plan->personalTutor->employee->id : 0);
                $classTutor = ($tutorEmployeeId > 0 ? $tutorEmployeeId : ($PerTutorEmployeeId > 0 ? $PerTutorEmployeeId : 0));
                $empAttendanceLive = EmployeeAttendanceLive::where('employee_id', $classTutor)->where('date', $pln->date)->where('attendance_type', 1)->get();

                $proxyEmployeeId = (isset($pln->proxy->employee->id) && $pln->proxy->employee->id > 0 ? $pln->proxy->employee->id : 0);
                $proxyAttendanceLive = EmployeeAttendanceLive::where('employee_id', $proxyEmployeeId)->where('date', $pln->date)->where('attendance_type', 1)->get();

                $classStatus = 0;
                $classLabel = '';
                $orgStart = date('Y-m-d H:i:s', strtotime( $pln->date.' '.$pln->plan->start_time));
                $orgEnd = date('Y-m-d H:i:s', strtotime( $pln->date.' '.$pln->plan->end_time));

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
                        $completed =1;
                    endif;
                elseif($currentTime > $orgEnd && !isset($pln->attendanceInformation->id)):
                    $classLabel .= '<span class="text-danger font-medium">Not Started</span>';
                endif;

                $html .= '<tr class="intro-x" data-planListId="'.$pln->id.'">';
                    $html .= '<td>';
                        $html .= '<span class="font-fedium">'.$i++.'</span><br/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<span class="font-fedium">'.date('jS F, Y', strtotime( $pln->date.' '.$pln->plan->start_time)).'</span><br/>';
                        $html .= '<span class="font-fedium">'.date('h:i a', strtotime( $pln->date.' '.$pln->plan->start_time)).'</span>';
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
                    if($pln->status=='Completed'){ 
                        $html .= '<td class="text-left">';
                            $html .= '<span class="flex justify-start items-center">';
                            if($pln->proxy_tutor_id > 0):
                                $html .= '<span class=" text-emerald-700 font-medium">Completed</span><span class=" text-danger font-medium ml-2"> [ Proxy ]</span';
                            else:
                                $html .= '<span class=" text-emerald-700 font-medium">Completed</span>';
                            endif;
                            $html .= '</span>';
                        $html .= '</td>';
                    } else if($pln->status == 'Canceled') {
                        $html .= '<td class="text-left">';
                            $html .= '<span class="flex justify-start items-center">';
                                $html .= '<span class="text-danger font-medium">Cancelled</span>';
                            $html .= '</span>';
                        $html .= '</td>';
                    } else if($pln->status == 'Unknown') {
                        $html .= '<td class="text-left">';
                            $html .= '<span class="flex justify-start items-center">';
                                $html .= '<span class="text-warning font-medium">Unknown</span>';
                            $html .= '</span>';
                        $html .= '</td>';
                    } else {
                        $html .= '<td class="text-left">';
                            $html .= '<span class="flex justify-start items-center">';
                                $html .= '<span class="text-info font-medium">Scheduled</span>';
                            $html .= '</span>';
                        $html .= '</td>';
                    }
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
}
