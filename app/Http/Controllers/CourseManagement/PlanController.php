<?php

namespace App\Http\Controllers\CourseManagement;

use App\Exports\ArrayCollectionExport;
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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    public function index()
    {
        return view('pages.course-management.plan.index', [
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Plans - London Churchill College',
            'subtitle' => 'Class Plans',
            'cmPageTitle' => 'Class Plans',
            'cmBackUrl' => route('course.management'),
            'cmBackLabel' => 'Back to Course Management',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
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
        // 1, not '' — an empty string reaches Tabulator as NaN and breaks the pager.
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

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
                    // `room` stays for anything reading the flat label; the split
                    // pair is what the list renders, room over venue.
                    'room'=> (isset($list->venu->name) ? $list->venu->name : '').' - '.(isset($list->room->name) ? $list->room->name : ''),
                    'room_name'=> (isset($list->room->name) ? $list->room->name : ''),
                    'venue_name'=> (isset($list->venu->name) ? $list->venu->name : ''),
                    'time'=> (!empty($list->start_time) ? date('H:i', strtotime($list->start_time)) : '').' - '.(!empty($list->end_time) ? date('H:i', strtotime($list->end_time)) : ''),
                    'module_enrollment_key'=> $list->module_enrollment_key,
                    'submission_date'=> $list->submission_date,
                    'tutor'=> (isset($list->tutor->name) ? $list->tutor->name : ''),
                    'personalTutor'=>  (isset($list->personalTutor->name) && !empty($list->personalTutor->name) ? $list->personalTutor->name : ''),
                    'virtual_room'=> $list->virtual_room,
                    'group'=> (isset($list->group->name) ? $list->group->name : ''),
                    'day'=> $day,
                    'deleted_at' => $list->deleted_at,
                    'dates' => $list->dates->count() > 0 ? 1 : 0,
                    'class_type' => (isset($list->class_type) && !empty($list->class_type) ? $list->class_type : (isset($list->creations->class_type) && !empty($list->creations->class_type) ? $list->creations->class_type : '')),
                ];
                $i++;
            endforeach;
        endif;
        // `total` feeds the header count and the "1-10 of N" readout.
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
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
        $data['class_type'] = (isset($request->class_type) ? $request->class_type : null);
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
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Plans - London Churchill College',
            'subtitle' => 'Add Class Plans',
            'cmPageTitle' => 'Add Class Plans',
            'cmBackUrl' => route('class.plan'),
            'cmBackLabel' => 'Back to Class Plans',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
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
            // Opts this screen into the redesigned module shell.
            'layout' => 'course-top-menu',
            'title' => 'Plans - London Churchill College',
            'subtitle' => 'Class Plan Builder',
            'cmPageTitle' => 'Class Plan Builder',
            'cmBackUrl' => route('class.plan'),
            'cmBackLabel' => 'Back to Class Plans',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => route('course.management')],
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

    /**
     * Saves the class plan builder sheet.
     *
     * Two passes on purpose: every row is built and checked first, and nothing
     * touches the database until all of them are valid. The write then runs in
     * a transaction, so a sheet is saved completely or not at all — a failure
     * half way through used to leave some classes written and the rest lost.
     *
     * The checking is not cosmetic. This connection runs with strict mode off,
     * so MySQL silently coerces rather than complaining: `plans.course_id` and
     * `plans.module_creation_id` are NOT NULL, and a card saved without a module
     * was writing 0 into both. `class_type` is an ENUM, and anything outside it
     * was being stored as an empty string.
     */
    public function store(Request $request){
        $routineData = $request->routineData;

        if (empty($routineData) || !is_array($routineData)) {
            return response()->json(['message' => 'Add at least one class before saving.'], 422);
        }

        // The combination the whole sheet is written against.
        $scope = [
            'term_declaration_id' => (int) $request->term_declaration_id,
            'academic_year_id' => (int) $request->academic_year_id,
            'course_creation_id' => (int) $request->course_creation_id,
            'instance_term_id' => (int) $request->instance_term_id,
            'course_id' => (int) $request->course_id,
            'group_id' => (int) $request->group_id,
        ];
        foreach ($scope as $key => $value) {
            if ($value <= 0) {
                return response()->json([
                    'message' => 'This sheet is missing its '.str_replace('_', ' ', $key).'. Reload the page and try again.',
                ], 422);
            }
        }

        $group = Group::find($scope['group_id']);
        if (empty($group)) {
            return response()->json(['message' => 'That group no longer exists. Reload the page and try again.'], 422);
        }

        $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $classTypes = ['Theory', 'Practical', 'Seminar'];

        // What this sheet is allowed to reference, resolved once.
        $moduleTypes = ModuleCreation::where('instance_term_id', $scope['instance_term_id'])
            ->pluck('class_type', 'id')->toArray();
        $rooms = Room::pluck('venue_id', 'id')->toArray();
        $roomNames = Room::pluck('name', 'id')->toArray();

        // Groups repeat by name across intakes, so the sheet legitimately holds
        // plans on any of the same-named groups.
        $sameNameGroupIds = Group::where('term_declaration_id', $scope['term_declaration_id'])
            ->where('course_id', $scope['course_id'])
            ->where('name', $group->name)->pluck('id')->unique()->toArray();

        // Ids this sheet may update — the same set it was rendered from, so an
        // `existing_id` from elsewhere cannot be used to overwrite another sheet.
        $ownedPlanIds = Plan::where('term_declaration_id', $scope['term_declaration_id'])
            ->where('academic_year_id', $scope['academic_year_id'])
            ->where('course_creation_id', $scope['course_creation_id'])
            ->where('instance_term_id', $scope['instance_term_id'])
            ->where('course_id', $scope['course_id'])
            ->whereIn('group_id', $sameNameGroupIds)
            ->pluck('id')->toArray();

        $rows = [];
        $problems = [];
        $seenIds = [];

        foreach ($routineData as $day => $roomBoxes) {
            $day = (int) $day;
            if (!isset($days[$day]) || !is_array($roomBoxes)) {
                $problems[] = 'One of the classes is on a day this sheet does not have. Reload the page and try again.';
                continue;
            }

            foreach ($roomBoxes as $venueRoom => $boxes) {
                $parts = explode('_', (string) $venueRoom);
                $room = (int) ($parts[1] ?? 0);

                if ($room <= 0 || !isset($rooms[$room])) {
                    $problems[] = $days[$day].': one of the classes is in a room that no longer exists.';
                    continue;
                }
                if (!is_array($boxes)) {
                    continue;
                }

                // Taken from the room itself, so the pair can never disagree.
                // Null stays null: the column is nullable and 0 is not a venue.
                $venue = $rooms[$room] !== null ? (int) $rooms[$room] : null;
                $where = $days[$day].' · '.($roomNames[$room] ?? ('Room '.$room));

                foreach ($boxes as $box) {
                    if (!is_array($box)) {
                        continue;
                    }

                    $existingId = (int) ($box['existing_id'] ?? 0);
                    if ($existingId > 0 && !in_array($existingId, $ownedPlanIds)) {
                        $problems[] = $where.': that class is no longer part of this sheet. Reload the page and try again.';
                        continue;
                    }

                    // Module — NOT NULL in the table, and the identity of the class.
                    $module = (int) ($box['module'] ?? 0);
                    if ($module <= 0) {
                        $problems[] = $where.': choose a module.';
                        continue;
                    }
                    if (!array_key_exists($module, $moduleTypes)) {
                        $problems[] = $where.': that module is not part of this term.';
                        continue;
                    }

                    // Class type — an ENUM column; blank falls back to the module's own.
                    $type = trim((string) ($box['class_type'] ?? ''));
                    if ($type !== '' && !in_array($type, $classTypes, true)) {
                        $problems[] = $where.': "'.$type.'" is not a class type.';
                        continue;
                    }
                    if ($type === '') {
                        $moduleType = (string) ($moduleTypes[$module] ?? '');
                        $type = in_array($moduleType, ['Theory', 'Practical', 'Tutorial', 'Seminar'], true) ? $moduleType : null;
                    }

                    // Time — one `HH:MM - HH:MM` range, split into two TIME columns.
                    $startTime = null;
                    $endTime = null;
                    $time = trim((string) ($box['time'] ?? ''));
                    if ($time !== '') {
                        if (!preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9]) - ([01][0-9]|2[0-3]):([0-5][0-9])$/', $time, $m)) {
                            $problems[] = $where.': the time must read HH:MM - HH:MM.';
                            continue;
                        }
                        $startTime = $m[1].':'.$m[2].':00';
                        $endTime = $m[3].':'.$m[4].':00';
                    }

                    // Submission — a DATE column, entered as DD-MM-YYYY.
                    $submission = null;
                    $submissionRaw = trim((string) ($box['submission'] ?? ''));
                    if ($submissionRaw !== '') {
                        $parsed = \DateTime::createFromFormat('d-m-Y', $submissionRaw);
                        if (!$parsed || $parsed->format('d-m-Y') !== $submissionRaw) {
                            $problems[] = $where.': the submission date must read DD-MM-YYYY.';
                            continue;
                        }
                        $submission = $parsed->format('Y-m-d');
                    }

                    // TEXT columns hold 65535 bytes and strict mode is off, so
                    // anything longer is truncated without a word. Say so instead.
                    $virtualRoom = (string) ($box['virtual_room'] ?? '');
                    $note = (string) ($box['note'] ?? '');
                    if (strlen($virtualRoom) > 65535) {
                        $problems[] = $where.': the virtual room link is too long.';
                        continue;
                    }
                    if (strlen($note) > 65535) {
                        $problems[] = $where.': the note is too long.';
                        continue;
                    }

                    $tutor = (int) ($box['tutor'] ?? 0);
                    $personalTutor = (int) ($box['personal_tutor'] ?? 0);
                    $boxGroup = (int) ($box['group'] ?? 0);

                    $data = [
                        'term_declaration_id' => $scope['term_declaration_id'],
                        'academic_year_id' => $scope['academic_year_id'],
                        'course_creation_id' => $scope['course_creation_id'],
                        'instance_term_id' => $scope['instance_term_id'],
                        'course_id' => $scope['course_id'],
                        'module_creation_id' => $module,
                        'venue_id' => $venue,
                        'rooms_id' => $room,
                        // A stray group id falls back to the sheet's own rather
                        // than moving the class to another group.
                        'group_id' => in_array($boxGroup, $sameNameGroupIds) ? $boxGroup : $scope['group_id'],
                        'name' => null,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'label' => null,
                        'submission_date' => $submission,
                        // 0 is not a user; the columns are nullable.
                        'tutor_id' => $tutor > 0 ? $tutor : null,
                        'personal_tutor_id' => $personalTutor > 0 ? $personalTutor : null,
                        'virtual_room' => $virtualRoom,
                        'note' => $note,
                        'class_type' => $type,
                    ];

                    // Exactly one day set. Writing only the new one left the old
                    // column at 1, so a class moved from Mon to Tue showed on both.
                    foreach ($days as $dayName) {
                        $data[strtolower($dayName)] = (strtolower($dayName) === strtolower($days[$day]) ? 1 : 0);
                    }

                    if ($existingId > 0 && isset($seenIds[$existingId])) {
                        $problems[] = $where.': the same class appears twice on this sheet. Reload the page and try again.';
                        continue;
                    }
                    if ($existingId > 0) {
                        $seenIds[$existingId] = true;
                    }

                    $rows[] = ['existing_id' => $existingId, 'data' => $data];
                }
            }
        }

        if (!empty($problems)) {
            $problems = array_values(array_unique($problems));

            return response()->json([
                'message' => count($problems) === 1
                    ? $problems[0]
                    : 'Nothing was saved. '.count($problems).' classes need attention.',
                'errors' => $problems,
            ], 422);
        }

        if (empty($rows)) {
            return response()->json(['message' => 'Add at least one class before saving.'], 422);
        }

        $insertCount = 0;
        $updateCount = 0;

        try {
            DB::transaction(function () use ($rows, &$insertCount, &$updateCount) {
                foreach ($rows as $row) {
                    if ($row['existing_id'] > 0) {
                        // `module_enrollment_key` is deliberately absent from the
                        // update: it is not editable here, and including it as
                        // null wiped the stored key on every save.
                        Plan::where('id', $row['existing_id'])->update(array_merge($row['data'], [
                            'updated_by' => auth()->user()->id,
                        ]));
                        $updateCount++;
                    } else {
                        Plan::create(array_merge($row['data'], [
                            'module_enrollment_key' => null,
                            'created_by' => auth()->user()->id,
                        ]));
                        $insertCount++;
                    }
                }
            });
        } catch (\Throwable $e) {
            Log::error('Class plan save failed', [
                'scope' => $scope,
                'rows' => count($rows),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Nothing was saved. The sheet could not be written — please try again.',
            ], 500);
        }

        $msg = '';
        $msg .= ($insertCount > 0 ? '<strong>'.$insertCount.'</strong> Class Plan successfully inserted. ' : '');
        $msg .= ($updateCount > 0 ? '<strong>'.$updateCount.'</strong> Class Plan successfully updated. ' : '');

        return response()->json(['msg' => $msg, 'red' => route('class.plan')], 200);
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
        $data['class_type'] = (isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : $plan->creations->class_type);
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

    public function destroy($id)
    {
        // Find the plan by ID
        $plan = Plan::with(['attendances', 'plansDateList','plansDateList.attendanceInformation'])->find($id);
    
        if ($plan) {


            // Delete related attendance information
            if (isset($plan->plansDateList->attendanceInformation)) {

                foreach ($plan->plansDateList->attendanceInformation as $atnInf) {
                    $atnInf->delete();
                }
            }
    
            // Delete related attendances
            if (isset($plan->attendances)) {
                foreach ($plan->attendances as $attendance) {
                    $attendance->delete();
                }
            }
    
            // Delete related plans date lists
            if (isset($plan->plansDateList)) {
                foreach ($plan->plansDateList as $plansDateList) {
                    $plansDateList->delete();
                }
            }
    
            // Delete the plan itself
            $plan->delete();
    
            return response()->json($plan, 200);
        } else {
            return response()->json(['message' => 'Plan not found.'], 404);
        }
    }

    public function restore($id) {
        $data = Plan::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    /**
     * Renders one class card. Both the page and `class.plan.get.box` go through
     * here, so there is a single definition of the markup and of the save
     * contract it carries. The legacy code built this HTML twice by string
     * concatenation, and the two copies had already drifted.
     */
    private function renderClassCard(array $card, $modules, $users)
    {
        return view('pages.course-management.plan.partials.class-card', [
            'card' => $card,
            'cardModules' => $modules,
            'cardUsers' => $users,
        ])->render();
    }

    /** The combination this builder is scoped to, resolved once. */
    private function builderContext($academic, $term, $creation, $group)
    {
        $courseCreation = CourseCreation::find($creation);
        $creationInstance = CourseCreationInstance::where('course_creation_id', $creation)
            ->where('academic_year_id', $academic)->orderBy('id', 'DESC')->get()->first();
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $creationInstance->id)
            ->where('term_declaration_id', $term)->orderBy('id', 'DESC')->get()->first();
        $groups = Group::find($group);

        return [
            'courseCreation' => $courseCreation,
            'instanceTerm' => $instanceTerm,
            'group' => $groups,
            // Groups repeat by name across intakes; a plan on any of the
            // same-named groups belongs to this sheet.
            'sameNameGroupIds' => Group::where('term_declaration_id', $term)
                ->where('course_id', $courseCreation->course_id)
                ->where('name', $groups->name)->pluck('id')->unique()->toArray(),
            'modules' => ModuleCreation::where('instance_term_id', $instanceTerm->id)
                ->orderBy('module_name', 'ASC')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
        ];
    }

    /**
     * Existing plans for this combination, as rendered cards keyed
     * [day][room][n] — the shape the builder view iterates.
     */
    public function getExistClassPlanBox($academic, $term, $creation, $group){
        $ctx = $this->builderContext($academic, $term, $creation, $group);
        $days = [1 => 'mon', 2 => 'tue', 3 => 'wed', 4 => 'thu', 5 => 'fri', 6 => 'sat', 7 => 'sun'];

        // One query for the whole sheet rather than 7 x rooms queries; the
        // legacy version ran `7 * Room::count()` (294 here) selects per load.
        $cps = Plan::where('term_declaration_id', $term)
            ->where('academic_year_id', $academic)
            ->where('course_creation_id', $creation)
            ->where('instance_term_id', $ctx['instanceTerm']->id)
            ->where('course_id', $ctx['courseCreation']->course_id)
            ->whereIn('group_id', $ctx['sameNameGroupIds'])
            ->whereNot('class_type', 'Tutorial')
            ->orderBy('id', 'ASC')
            ->get();

        $plans = [];
        foreach ($days as $key => $day) {
            foreach ($cps as $cp) {
                if ((int) $cp->{$day} !== 1) {
                    continue;
                }

                $times = (isset($cp->start_time) && !empty($cp->start_time) ? substr($cp->start_time, 0, 5) : '');
                $times .= (isset($cp->end_time) && !empty($cp->end_time) ? ' - '.substr($cp->end_time, 0, 5) : '');

                // Cards stacked in one room are tinted by slot so they read
                // apart at a glance; the palette repeats every six.
                $plans[$key][$cp->rooms_id][] = $this->renderClassCard([
                    'tint' => count($plans[$key][$cp->rooms_id] ?? []),
                    'day' => $key,
                    'venue_id' => $cp->venue_id,
                    'room_id' => $cp->rooms_id,
                    'existing_id' => $cp->id,
                    'course_id' => $cp->course_id,
                    'group_id' => $ctx['group']->id,
                    'module_id' => $cp->module_creation_id,
                    // Names travel with the ids so the card can keep showing a
                    // tutor who has since been deactivated, or a module that has
                    // left this term, instead of silently clearing the field.
                    'module_name' => $cp->creations->module_name ?? '',
                    'class_type' => $cp->class_type ?: ($cp->creations->class_type ?? ''),
                    'tutor_id' => $cp->tutor_id,
                    'tutor_name' => $cp->tutor->name ?? '',
                    'personal_tutor_id' => $cp->personal_tutor_id,
                    'personal_tutor_name' => $cp->personalTutor->name ?? '',
                    'time' => $times,
                    'submission' => (!empty($cp->submission_date) && $cp->submission_date != '0000-00-00' ? date('d-m-Y', strtotime($cp->submission_date)) : ''),
                    'virtual_room' => $cp->virtual_room,
                    'note' => $cp->note,
                ], $ctx['modules'], $ctx['users']);
            }
        }

        return $plans;
    }

    /** A blank card for the day/venue/room the user clicked "Add class" on. */
    public function getClassPlanBox(Request $request){
        $ctx = $this->builderContext(
            $request->academic_year_id,
            $request->term_declaration_id,
            $request->course_creation_id,
            $request->group_id
        );

        $html = $this->renderClassCard([
            'day' => $request->day,
            'venue_id' => $request->venue,
            'room_id' => $request->room,
            'existing_id' => 0,
            'course_id' => $request->course_id,
            'group_id' => $ctx['group']->id,
            'module_id' => '',
            'module_name' => '',
            'class_type' => '',
            'tutor_id' => '',
            'tutor_name' => '',
            'personal_tutor_id' => '',
            'personal_tutor_name' => '',
            'time' => '',
            'submission' => '',
            'virtual_room' => '',
            'note' => '',
        ], $ctx['modules'], $ctx['users']);

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
        Log::info('PlanController@getCourseByAcademicTerm started', [
            'academic_year_id' => $academicYear,
            'term_declaration_id' => $term_declaration_id,
        ]);

        try {
            //check
            $courseCreationInstanceIds = InstanceTerm::where('term_declaration_id', $term_declaration_id)
                ->pluck('course_creation_instance_id')
                ->unique()
                ->toArray();

            Log::debug('Fetched course creation instance IDs', [
                'count' => count($courseCreationInstanceIds),
            ]);

            if(!empty($courseCreationInstanceIds)):
                $courseCreationIds = CourseCreationInstance::whereIn('id', $courseCreationInstanceIds)
                    ->where('academic_year_id', $academicYear)
                    ->pluck('course_creation_id')
                    ->unique()
                    ->toArray();

                Log::debug('Filtered course creation IDs for academic year', [
                    'count' => count($courseCreationIds),
                ]);

                if(!empty($courseCreationIds)):
                    $sanitizedCourseCreationIds = array_map('intval', $courseCreationIds);
                    $placeholders = implode(',', array_fill(0, count($sanitizedCourseCreationIds), '?'));
                    $rawSql = "SELECT cc.id, cc.course_id, cr.name
                               FROM course_creations cc
                               LEFT JOIN courses cr ON cr.id = cc.course_id
                               INNER JOIN (
                                   SELECT MAX(id) AS id
                                   FROM course_creations
                                   WHERE id IN ($placeholders)
                                   AND deleted_at IS NULL
                                   GROUP BY course_id
                               ) latest ON latest.id = cc.id
                               WHERE cc.deleted_at IS NULL";

                    Log::debug('Preparing latest course creation query', [
                        'course_creation_ids_count' => count($sanitizedCourseCreationIds),
                        'course_creation_ids_sample' => array_slice($sanitizedCourseCreationIds, 0, 20),
                        'raw_sql' => $rawSql,
                    ]);

                    $courseCreations = collect(DB::select($rawSql, $sanitizedCourseCreationIds));

                    Log::info('Course creations fetched successfully', [
                        'result_count' => $courseCreations->count(),
                    ]);

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
        } catch (\Throwable $exception) {
            Log::error('PlanController@getCourseByAcademicTerm failed', [
                'academic_year_id' => $academicYear,
                'term_declaration_id' => $term_declaration_id,
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return response()->json(['res' => '', 'message' => 'Unable to load course list at the moment.'], 500);
        }

        if(!empty($data)):
            return response()->json(['res' => $data], 200);
        else:
            return response()->json(['res' => ''], 304);
        endif;
    }

    public function getGroupByAcademicTermCourse(Request $request) {
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


    
    public function getInstanceTermsListByAcademicTermCourse(Request $request){

        // get the correct instance instance_term is the correct result

        $academicYear = $request->academicYear;
        $term_declaration_id = $request->term_declaration_id;
        $course_creation_id = $request->course_creation_id;
        $course_id = CourseCreation::find($course_creation_id)->course_id;
        $data = [];
        $i = 0;
        $CourseCreationInstanceData = CourseCreationInstance::where('course_creation_id', $course_creation_id)->where('academic_year_id', $academicYear)->get();
        if(!empty($CourseCreationInstanceData)):
            foreach ($CourseCreationInstanceData as $courseCreationInstance):
                $courseCreationInstanceTerms = InstanceTerm::where('term_declaration_id', $term_declaration_id)->where('course_creation_instance_id',$courseCreationInstance->id)->get();
                if(!empty($courseCreationInstanceTerms))
                    foreach ($courseCreationInstanceTerms as $courseCreationInstanceTerm):
                       
                        $data[$i++] = $courseCreationInstanceTerm->id;
                    endforeach;
            endforeach;
        endif;    
        
        sort($data);
        //$groups = Group::where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->orderBy('name', 'ASC')->get();
        // $groups = DB::table('groups')->select('name')->where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)
        //           ->where('active', 1)
        //           ->groupBy('name')->orderBy('name', 'ASC')->get();
        // if(!empty($groups)):
        //     $i = 1;
        //     foreach($groups as $gr):
        //         $group = Group::where('name', $gr->name)->where('course_id', $course_id)->where('term_declaration_id', $term_declaration_id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
        //         if(!empty($group)):
        //             $data[$i]['id'] = $group->id;
        //             $data[$i]['name'] = $group->name;
        //         $i++;   
        //         endif;
        //     endforeach;
        // endif;

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

    public function exportPlans(Request $request){
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : 0);
        $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
        $group = (isset($request->groups) && !empty($request->groups) ? $request->groups : 0);
        $room = (isset($request->rooms) && !empty($request->rooms) ? $request->rooms : []);
        $tutor = (isset($request->tutors) && !empty($request->tutors) ? $request->tutors : []);
        $ptutor = (isset($request->ptutors) && !empty($request->ptutors) ? $request->ptutors : []);
        $days = (isset($request->days) && !empty($request->days) ? $request->days : []);
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sameNameGroupIds = [];
        if($group > 0):
            $groups = Group::find($group);
            $samegroups = Group::where('name', $groups->name);
            if($term_declaration_id > 0):
                $samegroups->where('term_declaration_id', $term_declaration_id);
            else:
                if(isset($groups->term_declaration_id) && $groups->term_declaration_id > 0):
                    $samegroups->where('term_declaration_id', $groups->term_declaration_id);
                endif;
            endif;
            if($courses > 0):
                $samegroups->where('course_id', $courses);
            else:
                if(isset($groups->course_id) && $groups->course_id > 0):
                    $samegroups->where('course_id', $groups->course_id);
                endif;
            endif;
            $sameNameGroupIds = $samegroups->pluck('id')->unique()->toArray();
        endif;

        $datesCPIds = [];
        if(isset($request->date_cpl) && !empty($request->date_cpl)):
            $datesCPIds = PlansDateList::where('date', date('Y-m-d', strtotime($request->date_cpl)))->pluck('plan_id')->unique()->toArray();
        endif;
        $query = Plan::orderBy('id', 'DESC');
        if($courses > 0): $query->where('course_id', $courses); endif;
        if($term_declaration_id > 0): $query->where('term_declaration_id', $term_declaration_id); endif;
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

        $Query = $query->get();
        //$theQuery = $query->toSql();
        //dd($theQuery);

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = "ID";
        $theCollection[$row][] = "Attendance Term";
        $theCollection[$row][] = "Course";
        $theCollection[$row][] = "Semester";
        $theCollection[$row][] = "Module";
        $theCollection[$row][] = "Venue";
        $theCollection[$row][] = "Room";
        $theCollection[$row][] = "Group";
        $theCollection[$row][] = "Start Time";
        $theCollection[$row][] = "End Time";
        $theCollection[$row][] = "Day";
        $theCollection[$row][] = "Submission Date";
        $theCollection[$row][] = "Class Type";
        $theCollection[$row][] = "Tutor";
        $theCollection[$row][] = "Personal Tutor";
        $theCollection[$row][] = "Virtual Room";
        $theCollection[$row][] = "Note";

        $row = 2;
        if(!empty($Query)):
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

                $theCollection[$row][] = $list->id;
                $theCollection[$row][] = (isset($list->attenTerm->name) && !empty($list->attenTerm->name) ? $list->attenTerm->name : '');
                $theCollection[$row][] = (isset($list->course->name) && !empty($list->course->name) ? $list->course->name : '');
                $theCollection[$row][] = (isset($list->cCreation->semester->name) && !empty($list->cCreation->semester->name) ? $list->cCreation->semester->name : '');
                $theCollection[$row][] = (isset($list->creations->module_name) ? $list->creations->module_name : '');
                $theCollection[$row][] = (isset($list->venu->name) && !empty($list->venu->name) ? $list->venu->name : '');
                $theCollection[$row][] = (isset($list->room->name) && !empty($list->room->name) ? $list->room->name : '');
                $theCollection[$row][] = (isset($list->group->name) && !empty($list->group->name) ? $list->group->name : '');
                $theCollection[$row][] = (!empty($list->start_time) ? date('H:i', strtotime($list->start_time)) : '');
                $theCollection[$row][] = (!empty($list->end_time) ? date('H:i', strtotime($list->end_time)) : '');
                $theCollection[$row][] = $day;
                $theCollection[$row][] = (isset($list->submission_date) && !empty($list->submission_date) ? date('jS F, Y', strtotime($list->submission_date)) : '');
                $theCollection[$row][] = (isset($list->class_type) && !empty($list->class_type) ? $list->class_type : (isset($list->creations->class_type) && !empty($list->creations->class_type) ? $list->creations->class_type : ''));
                $theCollection[$row][] = (isset($list->tutor->name) && !empty($list->tutor->name) ? $list->tutor->name : '');
                $theCollection[$row][] = (isset($list->personalTutor->name) && !empty($list->personalTutor->name) ? $list->personalTutor->name : '');
                $theCollection[$row][] = (isset($list->virtual_room) && !empty($list->virtual_room) ? $list->virtual_room : '');
                $theCollection[$row][] = (isset($list->note) && !empty($list->note) ? $list->note : '');

                $row++;
            endforeach;
        endif;

        $report_title = 'Plan_list.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }
}
