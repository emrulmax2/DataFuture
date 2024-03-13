<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\ModuleCreation;
use App\Models\InstanceTerm;
use App\Models\CourseModule;
use App\Models\ModuleLevel;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseModuleBaseAssesment;
use App\Models\Venue;
use App\Models\Room;
use App\Models\Semester;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PlansUpdateRequest;
use App\Models\AcademicYear;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\PlansDateList;
use App\Models\TermDeclaration;
use App\Models\TermType;

class PlanController extends Controller
{
    public function index()
    {
        return view('pages.course-management.plan.index', [
            'title' => 'Plans - LCC Data Future Managment',
            'subtitle' => 'Class Plans',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Plans', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::orderBy('name', 'ASC')->get(),
            'terms' => TermDeclaration::with('termType')->orderBy('id','DESC')->get(),
            'room' => Room::with('venue')->get(),
            'group' => Group::orderBy('id', 'ASC')->get(),
            'tutor' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'ptutor' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : 0);
        $term_declarations = (isset($request->term_declarations) && !empty($request->term_declarations) ? $request->term_declarations : 0);
        $group = (isset($request->group) && !empty($request->group) ? $request->group : 0);
        $room = (isset($request->room) && !empty($request->room) ? $request->room : []);
        $tutor = (isset($request->tutor) && !empty($request->tutor) ? $request->tutor : []);
        $ptutor = (isset($request->ptutor) && !empty($request->ptutor) ? $request->ptutor : []);
        $days = (isset($request->days) && !empty($request->days) ? $request->days : []);
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sameNameGroupIds = [];
        if($group > 0):
            $groups = Group::find($group);
            $sameNameGroupIds = Group::where('term_declaration_id', $term_declarations)->where('course_id', $courses)
                                ->where('name', $groups->name)->pluck('id')->unique()->toArray();
        endif;

        $datesCPIds = [];
        if(isset($request->dates) && !empty($request->dates)):
            $datesCPIds = PlansDateList::where('date', date('Y-m-d', strtotime($request->dates)))->pluck('plan_id')->unique()->toArray();
        endif;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Plan::orderByRaw(implode(',', $sorts));
        if(!empty($courses) && $courses > 0): $query->where('course_id', $courses); endif;
        if(!empty($term_declarations) && $term_declarations > 0): $query->where('term_declaration_id', $term_declarations); endif;
        if(!empty($sameNameGroupIds)): $query->whereIn('group_id', $sameNameGroupIds); endif;
        
        if(!empty($room)): $query->whereIn('rooms_id', $room); endif;
        if(!empty($tutor)): $query->whereIn('tutor_id', $tutor); endif;
        if(!empty($ptutor)): $query->whereIn('personal_tutor_id', $ptutor); endif;
        if(!empty($days)):
            $query->where(function($q) use ($days){
                foreach($days as $day):
                    $q->orWhere($day, 1);
                endforeach;
            });
        endif;
        if(!empty($datesCPIds)): $query->whereIn('id', $datesCPIds); endif;
        if($status == 2): $query->onlyTrashed(); endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();
        $lastQuery = DB::getQueryLog();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $day = '';
                if($list->sat == 1){
                    $day = 'Sat';
                }elseif($list->sun == 1){
                    $day = 'Sun';
                }elseif($list->mon == 1){
                    $day = 'Mon';
                }elseif($list->tue == 1){
                    $day = 'Tue';
                }elseif($list->wed == 1){
                    $day = 'Wed';
                }elseif($list->thu == 1){
                    $day = 'Thu';
                }elseif($list->fri == 1){
                    $day = 'Fri';
                }
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'course_id' => $list->course_id ,
                    'module_creation_id'=> $list->module_creation_id,
                    'course'=> isset($list->course->name) ? $list->course->name : '',
                    'module'=> isset($list->creations->module_name) ? $list->creations->module_name : '',
                    'room'=> (isset($list->venu->name) ? $list->venu->name : '').' - '.(isset($list->room->name) ? $list->room->name : ''),
                    'time'=> $list->start_time.' - '.$list->end_time,
                    'module_enrollment_key'=> $list->module_enrollment_key,
                    'submission_date'=> $list->submission_date,
                    'tutor'=> (isset($list->tutor->name) ? $list->tutor->name : ''),
                    'personalTutor'=> (isset($list->personalTutor->name) ? $list->personalTutor->name : ''),
                    'virtual_room'=> $list->virtual_room,
                    'group'=> (isset($list->group->name) ? $list->group->name : ''),
                    'day'=> $day,
                    'deleted_at' => $list->deleted_at,
                    'dates' => $list->dates->count() > 0 ? 1 : 0
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function update(PlansUpdateRequest $request){
        $planID = $request->id;
        $classDay = $request->class_day;
        $start_time = !empty($request->start_time) ? $request->start_time.':00' : '';
        $end_time = !empty($request->end_time) ? $request->end_time.':00' : '';
        $submission_date = !empty($request->submission_date) ? date('Y-m-d', strtotime($request->submission_date)) : '';
        $room = ($request->rooms_id > 0 ? Room::find($request->rooms_id) : []);
        $day = [ 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $data = [];
        $data['venue_id'] = (isset($room->venue->id) ? $room->venue->id : null);
        $data['rooms_id'] = (isset($room->id) ? $room->id : null);
        //$data['group_id'] = $request->group_id;
        $data['module_creation_id'] = $request->module_creation_id;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        foreach($day as $d):
            $data[$d] = ($d == $classDay ? 1 : 0);
        endforeach;
        $data['tutor_id'] = (isset($request->tutor_id) ? $request->tutor_id : null);
        $data['personal_tutor_id'] = (isset($request->personal_tutor_id) ? $request->personal_tutor_id : null);
        //$data['module_enrollment_key'] = (isset($request->module_enrollment_key) ? $request->module_enrollment_key : null);
        $data['virtual_room'] = (isset($request->virtual_room) ? $request->virtual_room : null);
        $data['note'] = (isset($request->note) ? $request->note : null);
        $data['submission_date'] = (isset($request->submission_date) && !empty($request->submission_date) ? date('Y-m-d', strtotime($request->submission_date)) : null);
        $data['updated_by'] = auth()->user()->id;

        $plan = Plan::where('id', $planID)->update($data);
        if($plan):
            return response()->json(['msg' => 'Successfully updated!'], 200);
        else:
            return response()->json(['msg' => 'Error Found'], 422);
        endif;
    }

    public function grid(Request $request){
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $term_declaration = (isset($request->term_declaration) && !empty($request->term_declaration) ? $request->term_declaration : []);
        
        $room = (isset($request->room) && !empty($request->room) ? $request->room : []);
        $group = (isset($request->group) && !empty($request->group) ? $request->group : []);
        $tutor = (isset($request->tutor) && !empty($request->tutor) ? $request->tutor : []);
        $ptutor = (isset($request->ptutor) && !empty($request->ptutor) ? $request->ptutor : []);
        $days = (isset($request->days) && !empty($request->days) ? $request->days : ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $datesCPIds = [];
        if(isset($request->dates) && !empty($request->dates)):
            $datesCPIds = PlansDateList::where('date', date('Y-m-d', strtotime($request->dates)))->pluck('plan_id')->unique()->toArray();
        endif;

        $allRooms = (!empty($room) ? Room::whereIn('id', $room)->get() : Room::all());
        $weekDays = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'];
        $groups = Group::all();
        $users = User::where('active', 1)->orderBy('name', 'ASC')->get();
        
        $html = '';
        //$html .= implode(',', $days);
        $html .= '<table class="table table-striped table-bordered routineBuilderTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="whitespace-nowrap">Day</th>';
                    if(!empty($allRooms)):
                        foreach($allRooms as $rm):
                            $html .= '<th class="whitespace-nowrap">'.$rm->name.' - '.$rm->venue->name.'</th>';
                        endforeach;
                    endif;
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                foreach($days as $dayName):
                    $html .= '<tr data-day="'.$dayName.'" class="routineRow">';
                        $html .= '<td class="text-center font-bold">'.ucfirst($dayName).'</td>';
                        if(!empty($allRooms)):
                            foreach($allRooms as $rm):
                                $query = Plan::where('rooms_id', $rm->id);
                                if(!empty($courses)): $query->whereIn('course_id', $courses); endif;
                                if(!empty($term_declaration)): $query->whereIn('term_declaration_id', $term_declaration); endif;
                                if(!empty($group)): $query->whereIn('group_id', $group); endif;
                                if(!empty($tutor)): $query->whereIn('tutor_id', $tutor); endif;
                                if(!empty($ptutor)): $query->whereIn('personal_tutor_id', $ptutor); endif;
                                if(!empty($datesCPIds)): $query->whereIn('id', $datesCPIds); endif;
                                if($status == 2): $query->onlyTrashed(); endif;
                                
                                $query->where(strtolower($dayName), 1);
                                $Query = $query->get();
                                $html .= '<td class="routineDay onlyViewRoutineDay relative" data-venuRoom="'.$rm->venue_id.'_'.$rm->id.'">';
                                    $html .= '<div class="routineDayBoxes">';
                                        if(!empty($Query)):
                                            foreach($Query as $cp):
                                                $times = (isset($cp->start_time) && !empty($cp->start_time) ? substr($cp->start_time, 0, 5) : '');
                                                $times .= (isset($cp->end_time) && !empty($cp->end_time) ? ' - '.substr($cp->end_time, 0, 5) : '');
                                                $html .= '<div class="routineDayBox" data-day="'.$dayName.'" data-venue="'.$cp->venue_id.'" data-room="'.$cp->rooms_id.'">';
                                                    $html .= '<div class="rdbItem course" data-id="'.$cp->course_id.'" data-label="Course">';
                                                        $html .= '<button type="button" class="btn btn-course inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="book" class="w-4 h-4 mr-1"></i> '.$cp->course->name.'</button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem module" data-id="'.$cp->module_creation_id.'" data-label="Module">';
                                                        $html .= '<button type="button" class="btn btn-module inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> '.$cp->creations->module_name.'</button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem group dropdownMenus" data-id="'.$cp->group_id.'" data-label="Group">';
                                                        $html .= '<button type="button" class=" btn btn-group inline-flex items-start justify-start w-full px-3 py-2 text-left text-white" ><i data-lucide="tag" class="w-4 h-4 mr-1"></i> <span>'.$cp->group->name.'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem tutor dropdownMenus" data-id="'.$cp->tutor_id.'" data-label="Tutor">';
                                                        $html .= '<button type="button" class=" btn btn-tutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user" class="w-4 h-4 mr-1"></i> <span>'.$cp->tutor->name.'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem personalTutor dropdownMenus" data-id="'.(isset($cp->personal_tutor_id) ? $cp->personal_tutor_id : '').'" data-label="Personal Tutor">';
                                                        $html .= '<button type="button" class=" btn btn-ptutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user-check" class="w-4 h-4 mr-1"></i> <span>'.(isset($cp->personalTutor->name) ? $cp->personalTutor->name : '').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem rdItemHalf odds classType dropdownMenus" data-id="'.(!empty($cp->creations->class_type) ? $cp->creations->class_type : 0).'" data-label="Class Type">';
                                                        $html .= '<button type="button" class="btn btn-class-type inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="columns" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->creations->class_type) ? $cp->creations->class_type : 'Class Type').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem rdItemHalf evens enrollmentKey inputFields" data-id="'.(!empty($cp->module_enrollment_key) ? $cp->module_enrollment_key : 0).'" data-label="Enrollment">';
                                                        $html .= '<button type="button" class=" btn btn-ekey inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="key" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->module_enrollment_key) ? $cp->module_enrollment_key : 'Enrollment').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem rdItemHalf odds timePicker inputFields" data-id="'.$times.'" data-label="Time">';
                                                        $html .= '<button type="button" class=" btn btn-time inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="clock" class="w-4 h-4 mr-1"></i> <span>'.(!empty($times) ? $times : 'Time').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem rdItemHalf evens submissionDate inputFields" data-id="'.(!empty($cp->submission_date) ? date('d-m-Y', strtotime($cp->submission_date)) : 0).'" data-label="Submission">';
                                                        $html .= '<button type="button" class=" btn btn-submission inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="calendar" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->submission_date) ? date('d-m-Y', strtotime($cp->submission_date)) : 'Submission').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem virtualRoom inputFields" data-id="'.(!empty($cp->virtual_room) ? $cp->virtual_room : '0').'" data-label="Virtual Room">';
                                                        $html .= '<button type="button" class=" btn btn-vroom inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="video" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->virtual_room) ? $cp->virtual_room : 'Virtual Room').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="rdbItem notes inputFields" data-id="'.(!empty($cp->note) ? $cp->note : '0').'" data-label="Note">';
                                                        $html .= '<button type="button" class="inputToggles btn btn-note inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="edit-2" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->note) ? $cp->note : 'Note').'</span></button>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="clear-both"></div>';
                                                    
                                                    $html .= '<input type="hidden" name="existing_id" class="existing_id" value="'.$cp->id.'"/>';
                                                $html .= '</div>';
                                            endforeach;
                                        endif;
                                    $html .= '</div>';
                                $html .= '</td>';
                            endforeach;
                        endif;
                    $html .= '</tr>';
                endforeach;
            $html .= '</tbody>';
        $html .= '</table>';

        return response()->json(['htm' => $html], 200);
    }

    public function add()
    {
        
        return view('pages.course-management.plan.add', [
            'title' => 'Plans - LCC Data Future Managment',
            'subtitle' => 'Add Class Plans',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Add Plan', 'href' => 'javascript:void(0);']
            ],
            'courses' => Course::all(),
            'academic_years' =>AcademicYear::orderBy('to_date','desc')->get(),
            'termDeclaration' => TermDeclaration::all(),
            'termType' => TermType::all(),
            'terms' => InstanceTerm::all(),
        ]);
    }

    public function classPlanBuilder($academic, $term, $creation, $group){
        $creations = CourseCreation::find($creation);
        $creationInstance = CourseCreationInstance::where('course_creation_id', $creation)->where('academic_year_id', $academic)->orderBy('id', 'DESC')->get()->first();
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $creationInstance->id)->where('term_declaration_id', $term)->orderBy('id', 'DESC')->get()->first();
        $groups = Group::find($group);
        $sameNameGroupIds = Group::where('term_declaration_id', $term)->where('course_id', $creations->course_id)
                            ->where('name', $groups->name)->pluck('id')->unique()->toArray();
        $modules = Plan::where('term_declaration_id', $term)->where('academic_year_id', $academic)->where('course_creation_id', $creation)
                    ->where('instance_term_id', $instanceTerm->id)->where('course_id', $creations->course_id)->whereIn('group_id', $sameNameGroupIds)
                    ->pluck('module_creation_id')->unique()->toArray();

        return view('pages.course-management.plan.builder', [
            'title' => 'Plans - LCC Data Future Managment',
            'subtitle' => 'Class Plan Builder',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Builder', 'href' => 'javascript:void(0);']
            ],
            'academic' => AcademicYear::find($academic),
            'termDec' => TermDeclaration::find($term),
            'creation' => $creations,
            'group' => $groups,

            'instanceTerm' => $instanceTerm,
            'moduleCount' => (!empty($modules) ? count($modules) : 0),

            //'moduleCreation' => ModuleCreation::where('id', $modulecreation)->first(),
            'venues' => Venue::orderBy('name', 'ASC')->get(),
            'rooms' => Room::with('venue')->get(),
            'plans' => $this->getExistClassPlanBox($academic, $term, $creation, $group)
        ]);
    }

    public function store(Request $request){
        $routineData = $request->routineData;
        $term_declaration_id = $request->term_declaration_id;
        $academic_year_id = $request->academic_year_id; 
        $course_creation_id = $request->course_creation_id;
        $instance_term_id = $request->instance_term_id;
        $course_id = $request->course_id;
        $group_id = $request->group_id;


        $days = [ 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $insertCount = 0;
        $updateCount = 0;

        if(!empty($routineData)){
            foreach($routineData as $day => $rooms):
                foreach($rooms as $venueRoom => $boxes):
                    $venueRooms = explode('_', $venueRoom);
                    $venue = (isset($venueRooms[0]) && $venueRooms[0] > 0 ? $venueRooms[0] : 0);
                    $room = (isset($venueRooms[1]) && $venueRooms[1] > 0 ? $venueRooms[1] : 0);
                    if(!empty($boxes)):
                        foreach($boxes as $box):
                            $existing_id = (isset($box['existing_id']) && $box['existing_id'] > 0 ? $box['existing_id'] : 0);
                            $times = (isset($box['time']) && !empty($box['time']) ? explode(' - ', $box['time']) : []);
                            
                            $data = [];
                            $data['term_declaration_id'] = $term_declaration_id;
                            $data['academic_year_id'] = $academic_year_id;
                            $data['course_creation_id'] = $course_creation_id;
                            $data['instance_term_id'] = $instance_term_id;
                            $data['course_id'] = (isset($box['course']) ? $box['course'] : $course_id);
                            $data['module_creation_id'] = (isset($box['module']) ? $box['module'] : null);
                            $data['venue_id'] = $venue;
                            $data['rooms_id'] = $room;
                            $data['group_id'] = (isset($box['group']) ? $box['group'] : $group_id);
                            $data['name'] = null;
                            $data['start_time'] = (isset($times[0]) && !empty($times[0]) ? $times[0].':00' : null);
                            $data['end_time'] = (isset($times[1]) && !empty($times[1]) ? $times[1].':00' : null);
                            $data['label'] = null;
                            $data[strtolower($days[$day])] = 1;
                            $data['module_enrollment_key'] = null;
                            $data['submission_date'] = (isset($box['submission']) && !empty($box['submission']) ? date('Y-m-d', strtotime($box['submission'])) : null);
                            $data['tutor_id'] = (isset($box['tutor']) ? $box['tutor'] : null);
                            $data['personal_tutor_id'] = (isset($box['personal_tutor']) ? $box['personal_tutor'] : null);
                            $data['virtual_room'] = (isset($box['virtual_room']) && !empty($box['virtual_room']) ? $box['virtual_room'] : '');
                            $data['note'] = (isset($box['note']) && !empty($box['note']) ? $box['note'] : '');

                            if($existing_id > 0):
                                $data['updated_by'] = auth()->user()->id;
                                $plans = Plan::where('id', $existing_id)->update($data);
                                if($plans):
                                    $updateCount += 1;
                                endif;
                            else:
                                $data['created_by'] = auth()->user()->id;
                            
                                $plans = Plan::create($data);
                                if($plans):
                                    $insertCount += 1;
                                endif;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endforeach;
            if($insertCount > 0 || $updateCount > 0):
                $msg = '';
                $msg .= ($insertCount > 0 ? '<strong>'.$insertCount.'</strong> Class Plan successfully inserted. ' : '');
                $msg .= ($updateCount > 0 ? '<strong>'.$updateCount.'</strong> Class Plan successfully updated. ' : '');
                return response()->json(['msg' => $msg, 'red' => route('class.plan')], 200);
            else:
                return response()->json(['msg' => 'Data not inserted'], 304);
            endif;
        }else{
            return response()->json(['Message' => 'Module not selected'], 422);
        }
    }

    public function edit($id){
        $plan = Plan::where('id', $id)->first();
        $start_time = (!empty($plan->start_time) ? substr($plan->start_time, 0, 5) : '');
        $end_time = (!empty($plan->end_time) ? substr($plan->end_time, 0, 5) : '');
        $moduleCreations = ModuleCreation::where('instance_term_id', $plan->instance_term_id)->orderBy('module_name', 'ASC')->get();
        $modules = '<option value="">Please Select</option>';
        if(!empty($moduleCreations)):
            foreach($moduleCreations as $mods):
                $modules .= '<option '.($plan->module_creation_id == $mods->id ? 'selected' : '').' value="'.$mods->id.'">'.$mods->module_name.'</option>';
            endforeach;
        endif;

        $data = [];
        $data['term'] = (isset($plan->attenTerm->name) && !empty($plan->attenTerm->name) ? $plan->attenTerm->name : '---');
        $data['course'] = (isset($plan->course->name) ? $plan->course->name : '---');
        $data['group'] = (isset($plan->group->name) ? $plan->group->name : '---');
        $data['module'] = $plan->creations->module_name;
        $data['venue_id'] = $plan->venue_id;
        $data['rooms_id'] = $plan->rooms_id;
        $data['group_id'] = $plan->group_id;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        $data['module_enrollment_key'] = $plan->module_enrollment_key;
        $data['submission_date'] = $plan->submission_date;
        $data['tutor_id'] = $plan->tutor_id;
        $data['personal_tutor_id'] = $plan->personal_tutor_id;
        $data['virtual_room'] = $plan->virtual_room;
        $data['note'] = $plan->note;
        $data['class_type'] = $plan->creations->class_type;
        $data['sat'] = $plan->sat;
        $data['sun'] = $plan->sun;
        $data['mon'] = $plan->mon;
        $data['tue'] = $plan->tue;
        $data['wed'] = $plan->wed;
        $data['thu'] = $plan->thu;
        $data['fri'] = $plan->fri;
        $data['modules'] = $modules;

        return response()->json(['plan' => $data], 200);
    }

    public function destroy($id){
        $plan = Plan::find($id)->delete();
        return response()->json($plan);
    }

    public function restore($id) {
        $data = Plan::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function getExistClassPlanBox($academic, $term, $creation, $group){
        $plans = [];

        $courseCreation = CourseCreation::find($creation);
        $creationInstance = CourseCreationInstance::where('course_creation_id', $creation)->where('academic_year_id', $academic)->orderBy('id', 'DESC')->get()->first();
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $creationInstance->id)->where('term_declaration_id', $term)->orderBy('id', 'DESC')->get()->first();
        $moduleCreations = ModuleCreation::where('instance_term_id', $instanceTerm->id)->orderBy('module_name', 'ASC')->get();
        $groups = Group::find($group);
        $sameNameGroupIds = Group::where('term_declaration_id', $term)->where('course_id', $courseCreation->course_id)->where('name', $groups->name)
                            ->pluck('id')->unique()->toArray();
        $users = User::where('active', 1)->orderBy('name', 'ASC')->get();

        $days = [ 1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'];
        $rooms = Room::all();
        foreach($days as $key => $day){
            if(!empty($rooms)){
                foreach($rooms as $rms){
                    $cps = Plan::where('term_declaration_id', $term)->where('academic_year_id', $academic)
                           ->where('course_creation_id', $creation)->where('instance_term_id', $instanceTerm->id)
                           ->where('course_id', $courseCreation->course_id)
                           ->whereIn('group_id', $sameNameGroupIds)
                           ->where($day, 1)->where('rooms_id', $rms->id)
                           ->get();
                    if(!empty($cps)):
                        $r = 1;
                        foreach($cps as $cp):
                            $times = (isset($cp->start_time) && !empty($cp->start_time) ? substr($cp->start_time, 0, 5) : '');
                            $times .= (isset($cp->end_time) && !empty($cp->end_time) ? ' - '.substr($cp->end_time, 0, 5) : '');
                            $html = '';
                            $html .= '<div class="routineDayBox" data-day="'.$key.'" data-venue="'.$cp->venue_id.'" data-room="'.$cp->rooms_id.'">';
                                $html .= '<div class="rdbItem course" data-id="'.$cp->course_id.'" data-label="Course">';
                                    $html .= '<button type="button" class="btn btn-course inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="book" class="w-4 h-4 mr-1"></i> '.$cp->course->name.'</button>';
                                $html .= '</div>';
                                
                                /*$html .= '<div class="rdbItem module" data-id="'.$cp->module_creation_id.'" data-label="Module">';
                                    $html .= '<button type="button" class="btn btn-module inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> '.(isset($cp->creations->module_name) ? $cp->creations->module_name : '').'</button>';
                                $html .= '</div>';*/
                                
                                $html .= '<div class="rdbItem group" data-id="'.$groups->id.'" data-label="Group">';
                                    $html .= '<button type="button" class="btn btn-group inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="tag" class="w-4 h-4 mr-1"></i> '.$groups->name.'</button>';
                                $html .= '</div>';

                                $html .= '<div class="rdbItem module dropdownMenus" data-id="'.(isset($cp->module_creation_id) && $cp->module_creation_id > 0 ? $cp->module_creation_id : 0).'" data-label="Module">';
                                    $html .= '<button type="button" class="DMToggle btn btn-module inline-flex items-start justify-start w-full px-3 py-2 text-left text-white" ><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> <span>'.(isset($cp->creations->module_name) ? $cp->creations->module_name : '').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="dropdownMenuBox">';
                                        $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                                        $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                                            if(!empty($moduleCreations) && $moduleCreations->count() > 0):
                                                foreach($moduleCreations as $mc):
                                                    $html .= '<li data-value="'.$mc->id.'">'.$mc->module_name.'</li>';
                                                endforeach;
                                            endif;
                                        $html .= '</ul>';
                                    $html .= '</div>';
                                $html .= '</div>';

                                /*$html .= '<div class="rdbItem group dropdownMenus" data-id="'.$cp->group_id.'" data-label="Group">';
                                    $html .= '<button type="button" class="DMToggle btn btn-group inline-flex items-start justify-start w-full px-3 py-2 text-left text-white" ><i data-lucide="tag" class="w-4 h-4 mr-1"></i> <span>'.(isset($cp->group->name) ? $cp->group->name : '').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="dropdownMenuBox">';
                                        $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                                        $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                                            if(!empty($groups) && $groups->count() > 0):
                                                foreach($groups as $gr):
                                                    $html .= '<li data-value="'.$gr->id.'">'.$gr->name.'</li>';
                                                endforeach;
                                            endif;
                                        $html .= '</ul>';
                                    $html .= '</div>';
                                $html .= '</div>';*/

                                $html .= '<div class="rdbItem tutor dropdownMenus" data-id="'.$cp->tutor_id.'" data-label="Tutor">';
                                    $html .= '<button type="button" class="DMToggle btn btn-tutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user" class="w-4 h-4 mr-1"></i> <span>'.(isset($cp->tutor->name) ? $cp->tutor->name : '').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="dropdownMenuBox">';
                                        $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                                        $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                                            if(!empty($users)):
                                                foreach($users as $u):
                                                    $html .= '<li data-value="'.$u->id.'">'.$u->name.'</li>';
                                                endforeach;
                                            endif;
                                        $html .= '</ul>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="rdbItem personalTutor dropdownMenus" data-id="'.$cp->personal_tutor_id.'" data-label="Personal Tutor">';
                                    $html .= '<button type="button" class="DMToggle btn btn-ptutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user-check" class="w-4 h-4 mr-1"></i> <span>'.(isset($cp->personalTutor->name) ? $cp->personalTutor->name : '').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="dropdownMenuBox">';
                                        $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                                        $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                                            if(!empty($users)):
                                                foreach($users as $u):
                                                    $html .= '<li data-value="'.$u->id.'">'.$u->name.'</li>';
                                                endforeach;
                                            endif;
                                        $html .= '</ul>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="rdbItem classType dropdownMenus" data-id="'.(!empty($cp->creations->class_type) ? $cp->creations->class_type : 0).'" data-label="Class Type">';
                                    $html .= '<button type="button" class="DMToggle btn btn-class-type inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="columns" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->creations->class_type) ? $cp->creations->class_type : 'Class Type').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="dropdownMenuBox">';
                                        $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                                        $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                                            $html .= '<li data-value="Theory">Theory</li>';
                                            $html .= '<li data-value="Practical">Practical</li>';
                                            $html .= '<li data-value="Tutorial">Tutorial</li>';
                                            $html .= '<li data-value="Seminar">Seminar</li>';
                                        $html .= '</ul>';
                                    $html .= '</div>';
                                $html .= '</div>';

                                /*$html .= '<div class="rdbItem rdItemHalf evens enrollmentKey inputFields" data-id="'.(!empty($cp->module_enrollment_key) ? $cp->module_enrollment_key : 0).'" data-label="Enrollment">';
                                    $html .= '<button type="button" class="inputToggles btn btn-ekey inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="key" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->module_enrollment_key) ? $cp->module_enrollment_key : 'Enrollment').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="inputWraps">';
                                        $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="Enrollment Key">';
                                        $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                                    $html .= '</div>';
                                $html .= '</div>';*/

                                $html .= '<div class="rdbItem rdItemHalf odds timePicker inputFields" data-id="'.$times.'" data-label="Time">';
                                    $html .= '<button type="button" class="inputToggles btn btn-time inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="clock" class="w-4 h-4 mr-1"></i> <span>'.(!empty($times) ? $times : 'Time').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="inputWraps">';
                                        $html .= '<input type="text" class="form-control inputFieldsInput timeMask" placeholder="10:15 - 11:15">';
                                        $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="rdbItem rdItemHalf evens submissionDate inputFields" data-id="'.(!empty($cp->submission_date) ? date('d-m-Y', strtotime($cp->submission_date)) : 0).'" data-label="Submission">';
                                    $html .= '<button type="button" class="inputToggles btn btn-submission inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="calendar" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->submission_date) ? date('d-m-Y', strtotime($cp->submission_date)) : 'Submission').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="inputWraps">';
                                        $html .= '<input type="text" class="form-control inputFieldsInput dateMask" placeholder="DD-MM-YYYY">';
                                        $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="rdbItem virtualRoom inputFields" data-id="'.(!empty($cp->virtual_room) ? $cp->virtual_room : '0').'" data-label="Virtual Room">';
                                    $html .= '<button type="button" class="inputToggles btn btn-vroom inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="video" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->virtual_room) ? $cp->virtual_room : 'Virtual Room').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="inputWraps">';
                                        $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="https://virtualroom.com">';
                                        $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="rdbItem notes inputFields" data-id="'.(!empty($cp->note) ? $cp->note : '0').'" data-label="Note">';
                                    $html .= '<button type="button" class="inputToggles btn btn-note inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="pencil" class="w-4 h-4 mr-1"></i> <span>'.(!empty($cp->note) ? $cp->note : 'Note').'</span></button>';
                                    $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                                    $html .= '<div class="inputWraps">';
                                        $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="Note">';
                                        $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="clear-both"></div>';
                                
                                $html .= '<a href="javascript:void(0);" class="btn btn-danger text-white w-5 h-5 removePlanBTN"><i data-lucide="x-circle" class="w-5 h-5"></i></a>';
                                $html .= '<input type="hidden" name="existing_id" class="existing_id" value="'.$cp->id.'"/>';
                            $html .= '</div>';
                            $plans[$key][$rms->id][$r] = $html;
                            $r++;
                        endforeach;
                    endif;
                }
            }
        }

        return $plans;
    }

    public function getClassPlanBox(Request $request){
        $term_declaration_id = $request->term_declaration_id;
        $academic_year_id = $request->academic_year_id;
        $course_creation_id = $request->course_creation_id;
        $instance_term_id = $request->instance_term_id; 
        $course_id = $request->course_id;
        $group_id = $request->group_id;

        $day = $request->day;
        $venue = $request->venue; 
        $room = $request->room;


        $course_id = $request->course_id;
        $course = Course::find($course_id);

        $group = Group::find($group_id);
        $users = User::where('active', 1)->orderBy('name', 'ASC')->get();

        $moduleCreations = ModuleCreation::where('instance_term_id', $instance_term_id)->orderBy('module_name', 'ASC')->get();

        $html = '';
        $html .= '<div class="routineDayBox" data-day="'.$day.'" data-venue="'.$venue.'" data-room="'.$room.'">';
            $html .= '<div class="rdbItem course" data-id="'.$course->id.'" data-label="Course">';
                $html .= '<button type="button" class="btn btn-course inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="book" class="w-4 h-4 mr-1"></i> '.$course->name.'</button>';
            $html .= '</div>';
            /*$html .= '<div class="rdbItem module" data-id="'.$moduleCreation->id.'" data-label="Module">';
                $html .= '<button type="button" class="btn btn-module inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> '.$moduleCreation->module_name.'</button>';
            $html .= '</div>';*/
            $html .= '<div class="rdbItem group" data-id="'.$group->id.'" data-label="Group">';
                $html .= '<button type="button" class="btn btn-group inline-flex items-start justify-start w-full px-3 py-1 text-left text-white"><i data-lucide="tag" class="w-4 h-4 mr-1"></i> '.$group->name.'</button>';
            $html .= '</div>';

            $html .= '<div class="rdbItem module dropdownMenus" data-id="0" data-label="Module">';
                $html .= '<button type="button" class="DMToggle btn btn-module inline-flex items-start justify-start w-full px-3 py-2 text-left text-white" ><i data-lucide="git-branch" class="w-4 h-4 mr-1"></i> <span>Module</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="dropdownMenuBox">';
                    $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                    $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                        if(!empty($moduleCreations) && $moduleCreations->count() > 0):
                            foreach($moduleCreations as $mc):
                                $html .= '<li data-value="'.$mc->id.'">'.$mc->module_name.'</li>';
                            endforeach;
                        endif;
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</div>';

            /*$html .= '<div class="rdbItem group dropdownMenus" data-id="0" data-label="Group">';
                $html .= '<button type="button" class="DMToggle btn btn-group inline-flex items-start justify-start w-full px-3 py-2 text-left text-white" ><i data-lucide="tag" class="w-4 h-4 mr-1"></i> <span>Group</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="dropdownMenuBox">';
                    $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                    $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                        if(!empty($groups) && $groups->count() > 0):
                            foreach($groups as $gr):
                                $html .= '<li data-value="'.$gr->id.'">'.$gr->name.'</li>';
                            endforeach;
                        endif;
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</div>';*/

            $html .= '<div class="rdbItem tutor dropdownMenus" data-id="0" data-label="Tutor">';
                $html .= '<button type="button" class="DMToggle btn btn-tutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user" class="w-4 h-4 mr-1"></i> <span>Tutor</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="dropdownMenuBox">';
                    $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                    $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                        if(!empty($users)):
                            foreach($users as $u):
                                $html .= '<li data-value="'.$u->id.'">'.$u->name.'</li>';
                            endforeach;
                        endif;
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="rdbItem personalTutor dropdownMenus" data-id="0" data-label="Personal Tutor">';
                $html .= '<button type="button" class="DMToggle btn btn-ptutor inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="user-check" class="w-4 h-4 mr-1"></i> <span>Personal Tutor</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="dropdownMenuBox">';
                    $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                    $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                        if(!empty($users)):
                            foreach($users as $u):
                                $html .= '<li data-value="'.$u->id.'">'.$u->name.'</li>';
                            endforeach;
                        endif;
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="rdbItem classType dropdownMenus" data-id="0" data-label="Class Type">';
                $html .= '<button type="button" class="DMToggle btn btn-class-type inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="columns" class="w-4 h-4 mr-1"></i> <span>Class Type</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionDropdown"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="dropdownMenuBox">';
                    $html .= '<input type="text" class="form-control form-control-sm dropdownMenuSearch" placeholder="Search here...">';
                    $html .= '<ul class="dropdownMenus overflow-y-auto mh-32">';
                        $html .= '<li data-value="Theory">Theory</li>';
                        $html .= '<li data-value="Practical">Practical</li>';
                        $html .= '<li data-value="Tutorial">Tutorial</li>';
                        $html .= '<li data-value="Seminar">Seminar</li>';
                    $html .= '</ul>';
                $html .= '</div>';
            $html .= '</div>';

            /*$html .= '<div class="rdbItem rdItemHalf evens enrollmentKey inputFields" data-id="0" data-label="Enrollment">';
                $html .= '<button type="button" class="inputToggles btn btn-ekey inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="key" class="w-4 h-4 mr-1"></i> <span>Enrollment</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="inputWraps">';
                    $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="Enrollment Key">';
                    $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                $html .= '</div>';
            $html .= '</div>';*/

            $html .= '<div class="rdbItem rdItemHalf odds timePicker inputFields" data-id="0" data-label="Time">';
                $html .= '<button type="button" class="inputToggles btn btn-time inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="clock" class="w-4 h-4 mr-1"></i> <span>Time</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="inputWraps">';
                    $html .= '<input type="text" class="form-control inputFieldsInput timeMask" placeholder="10:15 - 11:15">';
                    $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="rdbItem rdItemHalf evens submissionDate inputFields" data-id="0" data-label="Submission">';
                $html .= '<button type="button" class="inputToggles btn btn-submission inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="calendar" class="w-4 h-4 mr-1"></i> <span>Submission</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="inputWraps">';
                    $html .= '<input type="text" class="form-control inputFieldsInput dateMask" placeholder="DD-MM-YYYY">';
                    $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="rdbItem virtualRoom inputFields" data-id="0" data-label="Virtual Room">';
                $html .= '<button type="button" class="inputToggles btn btn-vroom inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="video" class="w-4 h-4 mr-1"></i> <span>Virtual Room</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="inputWraps">';
                    $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="https://virtualroom.com">';
                    $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="rdbItem notes inputFields" data-id="0" data-label="Note">';
                $html .= '<button type="button" class="inputToggles btn btn-note inline-flex items-start justify-start w-full px-3 py-1 text-left text-white" ><i data-lucide="pencil" class="w-4 h-4 mr-1"></i> <span>Note</span></button>';
                $html .= '<a href="javascript:void(0);" class="clearSelection clearSelectionInput"><i data-lucide="x-circle" class="w-4 h-4"></i></a>';
                $html .= '<div class="inputWraps">';
                    $html .= '<input type="text" class="form-control inputFieldsInput" placeholder="Note">';
                    $html .= '<button type="button" class="okInputValue btn btn-success text-white inline-flex items-start justify-start"><i data-lucide="thumbs-up" class="w-4 h-4"></i></button>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="clear-both"></div>';
            
            $html .= '<a href="javascript:void(0);" class="btn btn-danger text-white w-5 h-5 removePlanBTN"><i data-lucide="x-circle" class="w-5 h-5"></i></a>';
            $html .= '<input type="hidden" name="existing_id" class="existing_id" value="0"/>';
        $html .= '</div>';

        return response()->json(['htmls' => $html], 200);
    }

    public function getModuleDetails(Request $request){
        $id = $request->id;
        $moduleCreation = ModuleCreation::find($id);

        return response()->json(['res' => $moduleCreation], 200);
    }

    public function getModulesByCourseTerms(Request $request){
        $courseID = $request->courseID;
        $instanceTermId = $request->instanceTermId;
        if($courseID > 0 && $instanceTermId > 0){
            $termRow = InstanceTerm::find($instanceTermId);

            $module_creations = DB::table('module_creations')
                ->select('module_creations.*')
                ->rightJoin('course_modules', function($join) use ($courseID){
                    $join->on('module_creations.course_module_id', '=', 'course_modules.id');
                    $join->on('course_modules.course_id', '=', DB::raw("'".$courseID."'"));
                })
                ->where('module_creations.instance_term_id', $instanceTermId)
                ->get();

            $mHtml = '';
            $tHtml = '';
            if(!empty($termRow)):
                $tHtml .= '<div class="grid grid-cols-12 gap-4">';
                    $tHtml .= '<div class="col-span-12">';
                        $tHtml .= '<h2 class="text-xl font-medium mb-5 text-left"><u>'.$termRow->name.'</u></h2>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Term:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold"><u>'.$termRow->term .'</u></div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Session Term:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->session_term) ? 'Term '.$termRow->session_term : 'Unknown').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Start Date:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->start_date) ? $termRow->start_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">End Date:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->end_date) ? $termRow->end_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching Weeks:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->total_teaching_weeks) ? $termRow->total_teaching_weeks : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching Start:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->teaching_start_date) ? $termRow->teaching_start_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Teaching End:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->teaching_end_date) ? $termRow->teaching_end_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Revision Start:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->revision_start_date) ? $termRow->revision_start_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                    $tHtml .= '<div class="col-span-4">';
                        $tHtml .= '<div class="grid grid-cols-12 gap-0">';
                            $tHtml .= '<div class="col-span-4"><div class="text-left text-slate-500 font-medium">Revision End:</div></div>';
                            $tHtml .= '<div class="col-span-8"><div class="text-left font-medium font-bold">'.(!empty($termRow->revision_end_date) ? $termRow->revision_end_date : '---').'</div></div>';
                        $tHtml .= '</div>';
                    $tHtml .= '</div>';
                $tHtml .= '</div>';
            endif;
            if(!empty($module_creations)):
                $mHtml .= '<div class="grid grid-cols-12 gap-4">';
                    $mHtml .= '<div class="col-span-12">';
                        $mHtml .= '<h2 class="font-medium text-base mt-0 mb-0">Available Module List:</h2>';
                        $mHtml .= '<div class="moduleSelectionError text-danger mt-0" style="display: none;"></div>';
                    $mHtml .= '</div>';
                    $mHtml .= '<div class="col-span-12">';
                        $mHtml .= '<div class="overflow-x-auto">';
                            $mHtml .= '<table class="table  table-striped border-t">';
                                $mHtml .= '<thead>';
                                    $mHtml .= '<tr>';
                                        $mHtml .= '<th class="whitespace-nowrap">#</th>';
                                        $mHtml .= '<th class="whitespace-nowrap">Name</th>';
                                        $mHtml .= '<th class="whitespace-nowrap">Code</th>';
                                        $mHtml .= '<th class="whitespace-nowrap">Status</th>';
                                        $mHtml .= '<th class="whitespace-nowrap">&nbsp;</th>';
                                    $mHtml .= '</tr>';
                                $mHtml .= '</thead>';
                                $mHtml .= '<tbody>';
                                    $i = 1;
                                    foreach($module_creations as $mc):
                                        $mHtml .= '<tr>';
                                            $mHtml .= '<td class="whitespace-nowrap">';
                                                $mHtml .= $i;
                                            $mHtml .= '</td>';
                                            $mHtml .= '<td class="whitespace-nowrap">';
                                                $mHtml .= $mc->module_name;
                                            $mHtml .= '</td>';
                                            $mHtml .= '<td class="whitespace-nowrap">';
                                                $mHtml .= $mc->code;
                                            $mHtml .= '</td>';
                                            $mHtml .= '<td class="whitespace-nowrap">';
                                                $mHtml .= ucfirst($mc->status);
                                            $mHtml .= '</td>';
                                            $mHtml .= '<td class="whitespace-nowrap">';
                                                $mHtml .= '<div class="form-check">';
                                                    $mHtml .= '<input id="radio-switch-'.$mc->id.'" class="form-check-input w-5 h-5" type="radio" name="module_creation_id" value="'.$mc->id.'">';
                                                    $mHtml .= '<label class="form-check-label" for="radio-switch-1">&nbsp;</label>';
                                                $mHtml .= '</div>';
                                            $mHtml .= '</td>';
                                        $mHtml .= '</tr>';
                                        $i++;
                                    endforeach;
                                $mHtml .= '</tbody>';
                            $mHtml .= '</table>';
                        $mHtml .= '</div>';
                    $mHtml .= '</div>';
                $mHtml .= '</div>';
            endif;
            if($mHtml == ''):
                $mHtml = '<div class="alert alert-danger-soft show flex items-center" role="alert">
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp; No module found under selected arguments. Please insert some module under that Instance Terms.
                          </div>';
            endif;
            return response()->json(['thtml' => $tHtml, 'mhtml' => $mHtml], 200);
        }else{
            return response()->json(['Message' => 'Something went wrong. Please try latter.'], 422);
        }
    }


    public function getTermDeclarationByAcademicYear(Request $request){
        $academicYear = $request->academicYear;
        $data = [];

        $termDeclarations = TermDeclaration::where('academic_year_id', $academicYear)->orderBy('id', 'ASC')->get();
        if(!empty($termDeclarations)):
            $i = 1;
            foreach($termDeclarations as $td):
                $data[$i]['id'] = $td->id;
                $data[$i]['name'] = $td->name;
                $i++;
            endforeach;
        endif;

        if(!empty($data)):
            return response()->json(['res' => $data], 200);
        else:
            return response()->json(['res' => ''], 304);
        endif;
    }

    public function getCourseByAcademicTerm(Request $request){
        $academicYear = $request->academicYear;
        $term_declaration_id = $request->term_declaration_id;
        $data = [];

        $courseCreationInstanceIds = InstanceTerm::where('term_declaration_id', $term_declaration_id)->pluck('course_creation_instance_id')->unique()->toArray();
        if(!empty($courseCreationInstanceIds)):
            $courseCreationIds = CourseCreationInstance::whereIn('id', $courseCreationInstanceIds)->where('academic_year_id', $academicYear)->pluck('course_creation_id')->unique()->toArray();
            if(!empty($courseCreationIds)):
                $courseCreations = DB::table('course_creations as cc') 
                    ->select('cc.id', 'cc.course_id', 'cr.name')
                    ->leftJoin('courses as cr', 'cr.id', 'cc.course_id')
                    ->whereRaw('cc.id IN (SELECT MAX(id) FROM course_creations WHERE id IN ('.implode(',', $courseCreationIds).') GROUP BY (course_id))')
                    ->get();
                if(!empty($courseCreations)):
                    $i = 1;
                    foreach($courseCreations as $ccrs):
                        $data[$i]['id'] = $ccrs->id;
                        $data[$i]['name'] = $ccrs->name;
                        $i++;
                    endforeach;
                endif;
            endif;
        endif;

        if(!empty($data)):
            return response()->json(['res' => $data], 200);
        else:
            return response()->json(['res' => ''], 304);
        endif;
    }

    public function getGroupByAcademicTermCourse(Request $request){
        $academicYear = $request->academicYear;
        $term_declaration_id = $request->term_declaration_id;
        $course_creation_id = $request->course_creation_id;
        $course_id = CourseCreation::find($course_creation_id)->course_id;
        $data = [];

        //$groups = Group::where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->orderBy('name', 'ASC')->get();
        $groups = DB::table('groups')->select('name')->where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)
                  ->where('active', 1)
                  ->groupBy('name')->orderBy('name', 'ASC')->get();
        if(!empty($groups)):
            $i = 1;
            foreach($groups as $gr):
                $group = Group::where('name', $gr->name)->where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                if(!empty($group)):
                    $data[$i]['id'] = $group->id;
                    $data[$i]['name'] = $group->name;
                $i++;   
                endif;
            endforeach;
        endif;

        if(!empty($data)):
            return response()->json(['res' => $data], 200);
        else:
            return response()->json(['res' => ''], 304);
        endif;
    }


    public function getCourseListByAcademicYear(Request $request) {
        
        $academicYear = $request->academicYear;
        $data = [];
        
        $courseCreationId = isset($request->course) ? $request->course : null;
        $termDeclarationId = isset($request->termDeclarationId) ? $request->termDeclarationId : null;
        $termTypeID = isset($request->termTypeID) ? $request->termTypeID : null;
        if(!$courseCreationId) {
            $academicYear = AcademicYear::find($request->academicYear);
            $crcInstances = $academicYear->crc_instance()->orderBy('end_date','desc')->get();
            foreach($crcInstances as $courseData):
                $courseCreationFind = CourseCreation::find($courseData->course_creation_id);
                // $data["courses"][$courseCreationFind->course->id] = ["id"=>$courseCreationFind->course->id, "name"=> $courseCreationFind->course->name ];
                $data["semesters"][$courseCreationFind->semester->id] = ["id"=>$courseCreationFind->semester->id, "name"=> $courseCreationFind->semester->name ];
            
                $data["optionsGroups"][$courseData->course_creation_id] = ["id"=>$courseData->course_creation_id, "name"=> $courseCreationFind->course->name,"class"=>$courseCreationFind->semester->id, ];

            endforeach;
            
        } else {
            
            $query = DB::table('instance_terms as termlist')
            ->select('termlist.*')
            ->leftJoin('course_creation_instances as ci', 'ci.id', '=', 'termlist.course_creation_instance_id')
            ->leftJoin('course_creations as cc', 'cc.id', '=', 'ci.course_creation_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'ci.academic_year_id')
            ->leftJoin('courses as course', 'course.id', '=', 'cc.course_id')
            ->where('ay.id', '=', $academicYear)
            ->where('cc.id', '=', $courseCreationId);

            if($termDeclarationId) {
                $query = $query->where('termlist.term_declaration_id', '=', $termDeclarationId);
                //  
            }
            
            $Query = $query->get();

            if($termDeclarationId) {
                foreach($Query as $list):
                    $courseId =CourseCreation::find($courseCreationId)->course->id;
                    $data = ["instancetermId" => $list->id,"courseId"=>$courseId];
                endforeach;

            } else {

                foreach($Query as $list):
                    if(!isset($data[$list->term_declaration_id])) {
                        $declaredTerm = TermDeclaration::find($list->term_declaration_id);
                        
                        $data[$declaredTerm->id] = ["id"=>$declaredTerm->id, "name"=> $declaredTerm->name . " - " .$declaredTerm->termType->name  ];
                    }
                endforeach;

            }
                
        }

        

        return response()->json([$data]);
    }

    public function getFilteredGroup(Request $request){
        $course = (isset($request->course) && $request->course > 0 ? $request->course : null);
        $term = (isset($request->term) && $request->term > 0 ? $request->term : null);

        $res = [];
        $groups = DB::table('groups')->select('name')->where('course_id', $course)->where('term_declaration_id', $term)
                  ->where('active', 1)
                  ->groupBy('name')->orderBy('name', 'ASC')->get();
        if(!empty($groups)):
            $i = 1;
            foreach($groups as $gr):
                $group = Group::where('name', $gr->name)->where('course_id', $course)->where('term_declaration_id', $term)
                         ->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                if(!empty($group)):
                    $res[$i]['id'] = $group->id;
                    $res[$i]['name'] = $group->name;
                $i++;   
                endif;
            endforeach;
        endif;

        return response()->json(['res' => $res], 200);
    }
}
