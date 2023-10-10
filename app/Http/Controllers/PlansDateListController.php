<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\BankHoliday;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PlansDatesRequest;
use Carbon\Carbon;

class PlansDateListController extends Controller
{
    public function index($planId){
        return view('pages.plandates.index', [
            'title' => 'Class Plan Dates - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Class Plans', 'href' => route('class.plan')],
                ['label' => 'Dates', 'href' => 'javascript:void(0);']
            ],
            'planid' => $planId
        ]);
    }

    public function list(Request $request){
        $planid = (isset($request->planid) && !empty($request->planid) ? $request->planid : 0);
        $dates = (isset($request->dates) && !empty($request->dates) ? date('Y-m-d', strtotime($request->dates)) : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '1');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'date', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);

        $query = PlansDateList::orderByRaw(implode(',', $sorts));
        if(!empty($planid)): $query->where('plan_id', $planid); endif;
        if(!empty($dates)): $query->where('date', $dates); endif;
        if($status == 2): $query->onlyTrashed(); endif;

        $total_rows = $query->count();
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => (isset($list->plan->virtual_room) && !empty($list->plan->virtual_room) ? 'Virtual - ' : 'Physical - ').$list->name,
                    'date'=> date('l jS M, Y', strtotime($list->date)),
                    'room' => (isset($list->plan->room->name) && !empty($list->plan->room->name) ? $list->plan->room->name : ''),
                    'time' => (isset($list->plan->start_time) && !empty($list->plan->start_time) ? date('H:i', strtotime($list->plan->start_time)) : 'Unknown').' - '.(isset($list->plan->end_time) && !empty($list->plan->end_time) ? date('H:i', strtotime($list->plan->end_time)) : 'Unknown'),
                    'status' => '',
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(PlansDatesRequest $request){
        $request->request->add(['created_by' => auth()->user()->id]);
        $data = PlansDateList::create($request->all());
        
        return response()->json($data);
    }

    

    public function destroy($id){
        $data = PlansDateList::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = PlansDateList::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function generate(Request $request){
        $plan_ids = $request->classPlansIds;

        $errorIDs = [];
        $insertIDs = [];
        if(!empty($plan_ids)):
            foreach($plan_ids as $cp_id):
                $plan = Plan::find($cp_id);
                $creation = $plan->creations;
                $term = $plan->creations->term;
                $courseCreationInstance = CourseCreationInstance::find($term->course_creation_instance_id);
                $academic_year_id = $courseCreationInstance->academic_year_id;
                $bankHolidays = BankHoliday::where('academic_year_id', $academic_year_id)->get();

                $submission_date = (isset($plan->submission_date) ? $plan->submission_date : '');
                $teaching_start_date = $start = (isset($term->teaching_start_date) && !empty($term->teaching_start_date) ? date('Y-m-d', strtotime($term->teaching_start_date)) : '');
                $teaching_end_date = $end = (isset($term->teaching_end_date) && !empty($term->teaching_end_date) ? date('Y-m-d', strtotime($term->teaching_end_date)) : '');
                $revision_start_date = $rstart = (isset($term->revision_start_date) && !empty($term->revision_start_date) ? date('Y-m-d', strtotime($term->revision_start_date)) : '');
                $revision_end_date = $rend = (isset($term->revision_end_date) && !empty($term->revision_end_date) ? date('Y-m-d', strtotime($term->revision_end_date)) : '');
                
                if($plan->dates->count() > 0):
                    $errorIDs[] = $cp_id;
                else:
                    if($teaching_start_date != '' && $teaching_end_date != ''):
                        $start = $teaching_start_date;
                        $end = $teaching_end_date;
                        while(strtotime($start) <= strtotime($end)):
                            $dayName = strtolower(date('D', strtotime($start)));
                            $bankHolidays = BankHoliday::where('academic_year_id', $academic_year_id)->where('start_date', '>=', $start)->where('end_date', '<=', $start)->get();
                            if(isset($plan->$dayName) && $plan->$dayName == 1 && $bankHolidays->count() == 0):
                                $name = '';
                                if($start == $submission_date):
                                    $name = 'Submission';
                                elseif($start >= $revision_start_date && $start <= $revision_end_date):
                                    $name = 'Revision';
                                else:
                                    $name = 'Teaching';
                                endif;
                                $data = [];
                                $data['plan_id'] = $cp_id;
                                $data['name'] = $name;
                                $data['date'] = $start;
                                $data['created_by'] = auth()->user()->id;

                                PlansDateList::create($data);
                            endif;
                            $start = date("Y-m-d", strtotime("+1 day", strtotime($start)));
                        endwhile;
                        $insertIDs[] = $cp_id;
                    else: 
                        $errorIDs[] = $cp_id;
                    endif;
                endif;
            endforeach;
            
            $message = '';
            $title = '';
            if(empty($errorIDs) &&  !empty($insertIDs)):
                return response()->json(['Message' => 'All selected plans date list successfully generated.', 'title' => 'Congratulations!'], 200);
            elseif(empty($insertIDs) && !empty($errorIDs)):
                return response()->json(['Message' => 'Selected plans date can not be generated due to date existence or technical errors.', 'title' => 'Oops!'], 422);
            elseif(!empty($insertIDs) && !empty($errorIDs)):
                return response()->json(['Message' => 'Some of them ('.count($insertIDs).') are successfully generated and rest of team ('.count($errorIDs).') can not be generated because of date existence.', 'title' => 'Congratulations!'], 200);
            endif;
        else:
            return response()->json(['Message' => 'Plan ID not found. Please select some of the plan from the list table after that submit again.', 'title' => 'Error!'], 422);
        endif;
    }
}
