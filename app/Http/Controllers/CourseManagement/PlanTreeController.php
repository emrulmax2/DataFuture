<?php

namespace App\Http\Controllers\CourseManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanAssignParticipantRequest;
use App\Http\Requests\PlansUpdateRequest;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\Group;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\PlanParticipant;
use App\Models\Room;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanTreeController extends Controller
{
    public function index()
    {
        $academicYears = DB::table('plans')
                ->select('academic_year_id')
                ->groupBy('academic_year_id')
                ->distinct()
                ->get();
        $yearPush = [];
        foreach($academicYears as $year):
            $yearPush[] = $year->academic_year_id;
        endforeach;       
        return view('pages.course-management.plan.tree.index', [
            'title' => 'Plans - LCC Data Future Managment',
            'subtitle' => 'Class Plan - Tree View',
            'breadcrumbs' => [
                ['label' => 'Course Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Tree', 'href' => 'javascript:void(0);']
            ],
            'acyers' => AcademicYear::orderBy('from_date', 'DESC')->whereIn("id",$yearPush)->get(),
            'courses' => Course::all(),
            'terms' => InstanceTerm::all(),
            'room' => Room::all(),
            'group' => Group::all(),
            'tutor' => User::all(),
            'ptutor' => User::all(),
            'users' => User::all(),
        ]);
    }


    public function getAttenDanceSemester(Request $request){
        $academicYear = $request->academicyear;
        $years = AcademicYear::find($academicYear);
        $Query = DB::table('plans')
                ->select('term_declaration_id as id')
                ->groupBy('term_declaration_id')
                ->where('academic_year_id', $academicYear)
                ->distinct()
                ->get();

        $html = '';
        if(!empty($Query)):
            $html .= '<ul class="theChild">';
            foreach($Query as $list):
                $TermDeclaration = TermDeclaration::find($list->id);
                $visibility = $this->getTermVisibility($academicYear, $list->id);

                $html .= '<li class="hasChildren relative">';
                    $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYear.'" data-attendanceSemester="'.$list->id.'" class="theTerm flex items-center text-primary font-medium">'.$TermDeclaration->name.' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                    $html .= '<div class="settingBtns flex justify-end items-center absolute">';  
                        $html .= '<button data-yearid="'.$academicYear.'" data-attendanceSemester="'.$list->id.'" data-courseid="" data-groupid="" data-visibility="'.($visibility == 1 ? 0 : 1).'" class="p-0 border-0 rounded-0 text-slate-500 inline-flex visibilityBtn visibility_'.$visibility.'"><i class="w-4 h-4" data-lucide="eye"></i></button>';
                    $html .= '</div>';
                $html .= '</li>';
            endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Terms not foudn!</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getCourses(Request $request){
        $academicYearId = $request->academicYearId;
        $attendanceSemester = $request->attendanceSemester;
        
        $query = DB::table('courses')
                ->select('courses.id as id' , 'courses.name as name')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $academicYearId)
                ->where('plans.term_declaration_id', '=', $attendanceSemester);
        $Query = $query->distinct()->get();

        $html = '';
        if(!$Query->isEmpty()):
            $html .= '<ul class="theChild">';

            foreach($Query as $list):
                $visibility = $this->getCourseVisibility($academicYearId, $attendanceSemester, $list->id);
                $html .= '<li class="hasChildren courseItems">';
                    $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$attendanceSemester.'" data-courseid="'.$list->id.'" class="theCourse flex items-start text-primary font-medium">'.$list->name.' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                    $html .= '<div class="settingBtns flex justify-end items-center absolute">';  
                        $html .= '<button data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$attendanceSemester.'" data-courseid="'.$list->id.'" data-groupid="" data-visibility="'.($visibility == 1 ? 0 : 1).'" class="p-0 border-0 rounded-0 text-slate-500 inline-flex visibilityBtn visibility_'.$visibility.'"><i class="w-4 h-4" data-lucide="eye"></i></button>';
                    $html .= '</div>';
                $html .= '</li>';
            endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Course not foudn!</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getGroups(Request $request){
        $courseId = $request->courseId;
        $termDeclaredId = $request->attendanceSemester;
        $academicYearId = $request->academicYearId;
        $course = Course::find($courseId);

        $query = DB::table('plans')->select('groups.name')
            ->leftJoin('groups', 'plans.group_id', '=', 'groups.id')
            ->groupBy('groups.name')
            ->where('plans.academic_year_id', '=', $academicYearId)
            ->where('plans.term_declaration_id', '=', $termDeclaredId)
            ->where('plans.course_id', '=', $courseId)
            ->where('groups.course_id', '=', $courseId)
            ->where('groups.term_declaration_id', '=', $termDeclaredId)
            ->orderBy('groups.name','ASC')->get();

        $html = '';
        if(!$query->isEmpty()):
            $html .= '<ul class="theChild" data-total-group="'.count($query).'">';
                foreach($query as $list):
                    $groupName = $list->name;
                    $theGroup = Group::where('name', $groupName)->where('course_id', $courseId)->where('term_declaration_id', $termDeclaredId)->orderBy('id', 'DESC')->get()->first();
                    $visibility = $this->getGroupVisibility($academicYearId, $termDeclaredId, $courseId, $theGroup->id);
                    
                    $html .= '<li class="hasChildren">';
                        $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$theGroup->id.'" class="theGroup flex items-center text-primary font-medium">'.$theGroup->name.($theGroup->evening_and_weekend ? " - [ Eve/Week ]" : "").'<i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                        $html .= '<div class="settingBtns flex justify-end items-center absolute">';  
                            $html .= '<button data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$theGroup->id.'" data-visibility="'.($visibility == 1 ? 0 : 1).'" class="p-0 border-0 rounded-0 text-slate-500 inline-flex visibilityBtn mr-2 visibility_'.$visibility.'"><i class="w-4 h-4" data-lucide="eye"></i></button>';
                            $html .= '<div class="dropdown">';
                                $html .= '<button class="dropdown-toggle p-0 border-0 rounded-0 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="settings" class="w-4 h4"></i></button>';
                                $html .= '<div class="dropdown-menu w-48">';
                                    $html .= '<ul class="dropdown-content">';
                                        $html .= '<li>';
                                            $html .= '<a data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$theGroup->id.'" href="javascript:void(0);" class="dropdown-item assignManager">';
                                                $html .= '<i data-lucide="user-plus-2" class="w-4 h-4 mr-2"></i> Assign Manager';
                                            $html .= '</a>';
                                        $html .= '</li>';
                                        $html .= '<li>';
                                            $html .= '<a data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$theGroup->id.'" href="javascript:void(0);" class="dropdown-item assignCoOrdinator">';
                                                $html .= '<i data-lucide="user-plus-2" class="w-4 h-4 mr-2"></i> Audit User';
                                            $html .= '</a>';
                                        $html .= '</li>';
                                    $html .= '</ul>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</li>';
                endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Group not foudn!</div></li>';
            $html .= '</ul>';
        endif;
        return response()->json(['htm' => $html], 200);


        /*$query = DB::table('plans')
            ->select('plans.group_id')
            ->leftJoin('groups', 'plans.group_id', '=', 'groups.id')
            ->groupBy('plans.group_id')
            ->where('plans.academic_year_id', '=', $academicYearId)
            ->where('plans.term_declaration_id', '=', $termDeclaredId)
            ->where('plans.course_id', '=', $courseId);
        $Query = $query->orderBy('groups.name','ASC')->get();

        $html = '';
        
        if(!$Query->isEmpty()):
            $html .= '<ul class="theChild" data-total-group="'.count($Query).'">';
                foreach($Query as $list):
                        
                        $query = DB::table('plans')
                        ->select('instance_term_id')
                        ->where('academic_year_id', '=', $academicYearId)
                        ->where('term_declaration_id', '=', $termDeclaredId)
                        ->where('group_id', '=', $list->group_id)
                        ->where('course_id', '=', $courseId);
                        $instance_term = $query->distinct()->get();
                        
                        
                        foreach($instance_term as $term):
                            $termId = $term->instance_term_id;
                            $visibility = $this->getGroupVisibility($academicYearId, $termId, $courseId, $list->group_id);
                            $theGroup = Group::find($list->group_id);
                            $html .= '<li class="hasChildren">';
                                $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-termid="'.$termId.'" data-courseid="'.$courseId.'" data-groupid="'.$list->group_id.'" class="theGroup flex items-center text-primary font-medium">'.$theGroup->name.($theGroup->evening_and_weekend ? " - [ Eve/Week ]" : "").'<i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                                $html .= '<div class="settingBtns flex justify-end items-center absolute">';  
                                    $html .= '<button data-yearid="'.$academicYearId.'" data-termid="'.$termId.'" data-attendanceSemester="'.$termDeclaredId.'" data-courseid="'.$courseId.'" data-groupid="'.$list->group_id.'" data-visibility="'.($visibility == 1 ? 0 : 1).'" class="p-0 border-0 rounded-0 text-slate-500 inline-flex visibilityBtn mr-2 visibility_'.$visibility.'"><i class="w-4 h-4" data-lucide="eye"></i></button>';
                                    $html .= '<div class="dropdown">';
                                        $html .= '<button class="dropdown-toggle p-0 border-0 rounded-0 text-slate-500" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="settings" class="w-4 h4"></i></button>';
                                        $html .= '<div class="dropdown-menu w-48">';
                                            $html .= '<ul class="dropdown-content">';
                                                $html .= '<li>';
                                                    $html .= '<a data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-termid="'.$termId.'" data-courseid="'.$courseId.'" data-groupid="'.$list->group_id.'" href="javascript:void(0);" class="dropdown-item assignManager">';
                                                        $html .= '<i data-lucide="user-plus-2" class="w-4 h-4 mr-2"></i> Assign Manager';
                                                    $html .= '</a>';
                                                $html .= '</li>';
                                                $html .= '<li>';
                                                    $html .= '<a data-yearid="'.$academicYearId.'" data-attendanceSemester="'.$termDeclaredId.'" data-termid="'.$termId.'" data-courseid="'.$courseId.'" data-groupid="'.$list->group_id.'" href="javascript:void(0);" class="dropdown-item assignCoOrdinator">';
                                                        $html .= '<i data-lucide="user-plus-2" class="w-4 h-4 mr-2"></i> Audit User';
                                                    $html .= '</a>';
                                                $html .= '</li>';
                                            $html .= '</ul>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</li>';
                        endforeach;
                    
                endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Group not foudn!</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);*/
    }

    public function getModule(Request $request) {
        $courseId = $request->courseId;
        //$termId = $request->termId;
        $termDeclaredData = $request->attendancesemester;
        $academicYearId = $request->academicYearId;
        $groupId = $request->groupId;
        
        //$term = InstanceTerm::find($termId);
        $course = Course::find($courseId);
        $group = Group::find($groupId);
        $sameNameGroupIds = Group::where('term_declaration_id', $termDeclaredData)->where('course_id', $courseId)
                            ->where('name', $group->name)->pluck('id')->unique()->toArray();

        $termDeclaraion = TermDeclaration::find($termDeclaredData);
        //$termsModuleCreations = ModuleCreation::where('instance_term_id', $termId)->pluck('id')->unique()->toArray();
        $plans = Plan::where('course_id', $courseId)->where('term_declaration_id', $termDeclaredData)->where('academic_year_id', $academicYearId)
                        ->whereIn('group_id', $sameNameGroupIds)->get();
        
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            $html .= '<div class="col-span-12 sm:col-span-4">';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Term</div>';
                    $html .= '<div class="col-span-8 font-medium">'.$termDeclaraion->name."-".$termDeclaraion->termType->name.'</div>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-12 sm:col-span-4">';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Course</div>';
                    $html .= '<div class="col-span-8 font-medium">'.$course->name.'</div>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="col-span-12 sm:col-span-4">';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Group</div>';
                    $html .= '<div class="col-span-8 font-medium">'.$group->name.'</div>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        if($plans->count() > 0):
            $html .= '<div class="grid grid-cols-12 gap-0 gap-x-4">';
                $html .= '<div class="col-span-6"></div>';
                $html .= '<div class="col-span-6 text-right">';
                    $html .= '<div class="flex mt-5 sm:mt-0 justify-end">';
                        $html .= '<button id="generateDaysBtn" style="display: none;" type="button" class="btn btn-primary shadow-md mr-2 w-auto">
                            Generate Days
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>';
                        $html .= '<a href="'.route('assign', [$academicYearId, $termDeclaredData, $courseId, $group->id]).'" id="assignStudent" class="btn btn-success shadow-md mr-2 w-auto text-white"><i data-lucide="user-cog" class="w-4 h-4 mr-2"></i> Assign / Deassignned Students</a>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
            
            //data-term="'.$term.'"
            $html .= '<div class="overflow-x-auto scrollbar-hidden">';
                $html .= '<div id="classPlanTreeListTable" data-course="'.$courseId.'" data-attendanceSemester="'.$termDeclaredData.'" data-group="'.(!empty($sameNameGroupIds) ? implode(',', $sameNameGroupIds) : '0').'" data-year="'.$academicYearId.'" class="mt-5 table-report table-report--tabulator"></div>';
            $html .= '</div>';
        else:
            $html .= '<div class="grid grid-cols-12 gap-4 mt-5">';
                $html .= '<div class="col-span-12">';
                    $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                        $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Class plans not found under those selected parameters.';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function list(Request $request){
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : 0);
        $group = (isset($request->group) && !empty($request->group) ? explode(',', $request->group) : [0]);
        $year = (isset($request->year) && !empty($request->year) ? $request->year : 0);
        $termDeclarion = (isset($request->attendanceSemester) && !empty($request->attendanceSemester) ? $request->attendanceSemester : 0);


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Plan::orderByRaw(implode(',', $sorts))->where('course_id', $courses)
                ->where('academic_year_id', $year)->where('term_declaration_id', $termDeclarion)
                ->whereIn('group_id', $group);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
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
                    'dates' => $list->dates->count() > 0 ? $list->dates->count() : 0,
                    'on_of_student' => '0/0'
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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

    public function destroy($id){
        $plan = Plan::find($id)->delete();
        return response()->json($plan);
    }

    public function restore($id) {
        $data = Plan::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function getAssignDetails(Request $request){
        $type = $request->type;

        $yearid = $request->yearid;
        $ACYear = AcademicYear::find($yearid);

        $termid = $request->termid;
        $term = TermDeclaration::find($termid);

        $courseid = $request->courseid;
        $course = Course::find($courseid);

        $groupid = $request->groupid;
        $group = Group::find($groupid);
        $sameNameGroupIds = Group::where('term_declaration_id', $termid)->where('course_id', $courseid)
                            ->where('name', $group->name)->pluck('id')->unique()->toArray();

        $title = '';
        $title .= '<u>'.$ACYear->name.'</u> > ';
        $title .= '<u>'.$term->name.'</u> > ';
        $title .= '<u>'.$course->name.'</u>';
        $title .= (isset($group->name) && !empty($group->name) ? ' > <u>'.$group->name.'</u>' : '');

        $planIds = Plan::orderBy('id', 'ASC')->where('course_id', $courseid)->where('academic_year_id', $yearid)
                ->where('term_declaration_id', $termid)
                ->whereIn('group_id', $sameNameGroupIds)
                ->pluck('id')->unique()->toArray();

        $userIds = [];
        if(!empty($planIds)):
            $userIds = PlanParticipant::whereIn('plan_id', $planIds)->where('type', $type)->pluck('user_id')->unique()->toArray();
        endif;

        $title .= ' > Assign <u>'.($type == 'Auditor' ? 'Audit User' : 'Manager').'</u>';
        return response()->json(['plans' => $planIds, 'participants' => $userIds, 'title' => $title], 200);
    }

    public function assignParticipants(PlanAssignParticipantRequest $request){
        $assigned_user_ids = $request->assigned_user_ids;
        $plan_ids = !empty($request->plan_ids) ? explode(',', $request->plan_ids) : [];
        $type = (isset($request->type) && !empty($request->type) ? $request->type : 'Manager');

        if(!empty($plan_ids) && !empty($assigned_user_ids)):
            foreach($plan_ids as $pid):
                $deleteParticipants = PlanParticipant::where('plan_id', $pid)->where('type', $type)->forceDelete();

                foreach($assigned_user_ids as $uid):
                    $data = [];
                    $data['plan_id'] = $pid;
                    $data['user_id'] = $uid;
                    $data['type'] = $type;
                    $data['created_by'] = auth()->user()->id;

                    PlanParticipant::create($data);
                endforeach;
            endforeach;
            return response()->json(['message' => 'Participants successfully assigned.'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        endif;
    }

    public function getTermVisibility($academicYear, $termDeclarationId){
        $query = DB::table('courses')
                ->select('courses.id as id')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $academicYear)
                ->where('plans.term_declaration_id', '=', $termDeclarationId)
                ->distinct()->get();
        $courseid = [];
        if(!empty($query)):
            foreach($query as $q):
                $courseid[] = $q->id;
            endforeach;
        endif;

        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclarationId);
        if(!empty($courseid)):
            $query->whereIn('course_id', $courseid);
        endif;
        $Query = $query->where('visibility', 1)->get();

        return ($Query->count() > 0 ? 1 : 0);
    }

    public function getCourseVisibility($academicYear, $termDeclarationId, $courseid){
        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclarationId)
                ->where('course_id', $courseid)->where('visibility', 1)->get();

        return ($query->count() > 0 ? 1 : 0);
    }

    public function getGroupVisibility($academicYear, $termDeclaredId, $courseid, $groupid){
        $group_ids = [];
        if($groupid && $groupid > 0):
            $group = Group::find($groupid);
            $group_ids = Group::where('term_declaration_id', $termDeclaredId)->where('course_id', $courseid)
                        ->where('name', $group->name)->pluck('id')->unique()->toArray();
        endif;


        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $academicYear)->where('term_declaration_id', $termDeclaredId);
        if($courseid && $courseid > 0): $query->where('course_id', $courseid); endif;
        if(!empty($group_ids)): $query->whereIn('group_id', $group_ids); endif;
        $query->where('visibility', 1)->get();

        return ($query->count() > 0 ? 1 : 0);
    }

    public function updateVisibility(Request $request){
        $yearid = $request->yearid;
        $attendancesemester = $request->attendancesemester;
        $courseid = $request->courseid;
        $groupid = $request->groupid;
        $visibility = $request->visibility;

        $courseids = [];
        if(!$courseid || empty($courseid)):
            $query = DB::table('courses')->select('courses.id as id')
                ->leftJoin('plans', 'plans.course_id', '=', 'courses.id')
                ->where('plans.academic_year_id', '=', $yearid)
                ->where('plans.term_declaration_id', '=', $attendancesemester)
                ->distinct()->get();
            if(!empty($query)):
                foreach($query as $q):
                    $courseid[] = $q->id;
                endforeach;
            endif;
        else:
            $courseids[] = (int) $courseid;
        endif;
        if(!$groupid || empty($groupid)):
            $query = Group::where('term_declaration_id', $attendancesemester);
            if(!empty($courseids)): $query->whereIn('course_id', $courseids); endif;
            $groupids = $query->pluck('id')->unique()->toArray();
        else:
            $group = Group::find($groupid);
            $groupids = Group::where('term_declaration_id', $attendancesemester)->whereIn('course_id', $courseids)
                                ->where('name', $group->name)->pluck('id')->unique()->toArray();
        endif;

        
        $query = Plan::orderBy('id', 'ASC')->where('academic_year_id', $yearid)->where('term_declaration_id', $attendancesemester);
        if(!empty($courseids)): $query->whereIn('course_id', $courseids); endif;
        if(!empty($courseids)): $query->whereIn('group_id', $groupids); endif;
        $planIds = $query->pluck('id')->unique()->toArray();

        if(!empty($planIds)):
            foreach($planIds as $pid):
                $plan = Plan::find($pid);

                $data = [];
                $data['visibility'] = $visibility;
                $data['updated_by'] = auth()->user()->id;

                Plan::where('id', $pid)->update($data);
            endforeach;
            $message = 'Plans visibility successfully updated.';
            $suc = 1;
        else:
            $message = 'Plans not found under selected criteria.';
            $suc = 2;
        endif;

        return response()->json(['message' => $message, 'suc' => $suc, 'visibility' => ($visibility == 1 ? 0 : 1)], 200);
    }
}
