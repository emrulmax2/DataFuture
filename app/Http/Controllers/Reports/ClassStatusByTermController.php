<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\TermDeclaration;
use Barryvdh\Debugbar\Facades\Debugbar;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
            
            
            foreach($data as $key => $value):
                $data[$key]['id'] = $i++;
                $plans = $value['plans'];
                $i = 1;
                $groupsList = array_unique($groupSet[$key]);
                sort($groupsList);
                
                 foreach ($groupsList as $group_id):
                    $data[$key]['_children'][] = [
                                    'id' => $i++,
                                    'course_name' => $groupInfo[$key][$group_id]['name'] ? $groupInfo[$key][$group_id]['name'] : '',
                                    'term_name' => '',
                                    'schedule' => $groupInfo[$key][$group_id]['schedule'] ? $groupInfo[$key][$group_id]['schedule'] : '',
                                    'future_schedule' => $groupInfo[$key][$group_id]['future_schedule'] ? $groupInfo[$key][$group_id]['future_schedule'] : '',
                                    'held' => $groupInfo[$key][$group_id]['held'] ? $groupInfo[$key][$group_id]['held'] : '',
                                    'cancelled' => $groupInfo[$key][$group_id]['cancelled'] ? $groupInfo[$key][$group_id]['cancelled'] : '',
                                    'unknown' => $groupInfo[$key][$group_id]['unknown'] ? $groupInfo[$key][$group_id]['unknown'] : '',
                                    'proxy' => $groupInfo[$key][$group_id]['proxy'] ? $groupInfo[$key][$group_id]['proxy'] : '',
                                ];
                  endforeach;
                  usort($data[$key]['_children'], function($a, $b) {
                    return strcmp($a['course_name'], $b['course_name']);
                });
            endforeach;

            $data = array_values($data);
        endif;
        
        return response()->json($data);
    }
    
}
