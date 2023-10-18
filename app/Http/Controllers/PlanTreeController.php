<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlansUpdateRequest;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Group;
use App\Models\InstanceTerm;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class PlanTreeController extends Controller
{
    public function index()
    {
        return view('pages.plan.tree.index', [
            'title' => 'Class Plans Tree - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Class Plans Tree', 'href' => 'javascript:void(0);']
            ],
            'acyers' => AcademicYear::orderBy('from_date', 'ASC')->get(),
            'courses' => Course::all(),
            'terms' => InstanceTerm::all(),
            'room' => Room::all(),
            'group' => Group::all(),
            'tutor' => User::all(),
            'ptutor' => User::all(),
        ]);
    }

    public function getTerm(Request $request){
        $academicYear = $request->academicyear;
        $years = AcademicYear::find($academicYear);

        $html = '';
        if($years->terms->count() > 0):
            $html .= '<ul class="theChild">';
            foreach($years->terms as $term):
                $html .= '<li class="hasChildren">';
                    $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYear.'" data-termid="'.$term->id.'" class="theTerm flex items-center text-primary font-medium">'.$term->name.' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
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
        $instanceTermId = $request->instanceTermId;

        $term = InstanceTerm::find($instanceTermId);
        $courseCreation = (isset($term->instance->creation) ? $term->instance->creation : []);

        $html = '';
        if(!empty($courseCreation)):
            $html .= '<ul class="theChild">';
                $html .= '<li class="hasChildren">';
                    $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYearId.'" data-termid="'.$instanceTermId.'" data-courseid="'.$courseCreation->course_id.'" class="theCourse flex items-center text-primary font-medium">'.$courseCreation->course->name.' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                $html .= '</li>';
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
        $termId = $request->termId;
        $academicYearId = $request->academicYearId;

        $course = Course::find($courseId);

        
        $html = '';
        if(isset($course->groups) && $course->groups->count() > 0):
            $html .= '<ul class="theChild">';
                foreach($course->groups as $grp):
                    $html .= '<li class="hasChildren">';
                        $html .= '<a href="javascript:void(0);" data-yearid="'.$academicYearId.'" data-termid="'.$termId.'" data-courseid="'.$courseId.'" data-groupid="'.$grp->id.'" class="theGroup flex items-center text-primary font-medium">'.$grp->name.' <i data-loading-icon="oval" class="w-4 h-4 ml-2"></i></a>';
                    $html .= '</li>';
                endforeach;
            $html .= '</ul>';
        else:
            $html .= '<ul class="errorUL theChild">';
                $html .= '<li><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Group not foudn!</div></li>';
            $html .= '</ul>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function getModule(Request $request){
        $courseId = $request->courseId;
        $termId = $request->termId;
        $academicYearId = $request->academicYearId;
        $groupId = $request->groupId;

        $term = InstanceTerm::find($termId);
        $course = Course::find($courseId);
        $group = Group::find($groupId);

        $termsModuleCreations = ModuleCreation::where('instance_term_id', $termId)->pluck('id')->unique()->toArray();
        $plans = Plan::where('course_id', $courseId)->where('group_id', $groupId)->whereIn('module_creation_id', $termsModuleCreations)->get();
        
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            $html .= '<div class="col-span-12 sm:col-span-4">';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Term</div>';
                    $html .= '<div class="col-span-8 font-medium">'.$term->name.'</div>';
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
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="overflow-x-auto scrollbar-hidden">';
                $html .= '<div id="classPlanTreeListTable" data-course="'.$courseId.'" data-term="'.$term.'" data-group="'.$groupId.'" data-year="'.$academicYearId.'" class="mt-5 table-report table-report--tabulator"></div>';
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
        $instance_term = (isset($request->instance_term) && !empty($request->instance_term) ? $request->instance_term : 0);
        $group = (isset($request->group) && !empty($request->group) ? $request->group : 0);
        $year = (isset($request->year) && !empty($request->year) ? $request->year : 0);

        $moduleCreationIds = ModuleCreation::where('instance_term_id', $instance_term)->pluck('id')->unique()->toArray();

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Plan::orderByRaw(implode(',', $sorts));
        if(!empty($courses)): $query->where('course_id', $courses); endif;
        if(!empty($moduleCreationIds)): $query->whereIn('module_creation_id', $moduleCreationIds); endif;
        if(!empty($group)): $query->where('group_id', $group); endif;

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

        $data = [];
        $data['course'] = $plan->course->name;
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
        $data['group_id'] = $request->group_id;
        $data['start_time'] = $start_time;
        $data['end_time'] = $end_time;
        foreach($day as $d):
            $data[$d] = ($d == $classDay ? 1 : 0);
        endforeach;
        $data['tutor_id'] = (isset($request->tutor_id) ? $request->tutor_id : null);
        $data['personal_tutor_id'] = (isset($request->personal_tutor_id) ? $request->personal_tutor_id : null);
        $data['module_enrollment_key'] = (isset($request->module_enrollment_key) ? $request->module_enrollment_key : null);
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
}
