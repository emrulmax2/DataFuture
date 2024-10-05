<?php

namespace App\Http\Controllers\Student\Result;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Plan;
use App\Models\Result as ModelsResult;
use App\Models\Status;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Student $student)
    {
        $grades = Grade::all();
        //$AssessmentPlans = AssessmentPlan::where('plan_id',$student->id)->get();
        $termData = [];

            $QueryInner = DB::table('plans as plan')
                        ->select('td.id as term_id','td.name as term_name','instance_terms.start_date','instance_terms.end_date', 'plan.module_creation_id as module_creation_id' , 'mc.module_name','mc.code as module_code', 'plan.id as plan_id' )
                        ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
                        ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
                        ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
                        ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
                        ->where('assign.student_id', $student->id)
                        ->orderBy("td.id",'desc')
                        ->get();

            foreach($QueryInner as $list):

                $resultByPlanGroup[$list->plan_id] = ModelsResult::with(["assementPlan","grade","createdBy","updatedBy"])->where("student_id", $student->id)->where("plan_id",$list->plan_id)->orderBy('id','DESC')->get()->groupBy(function($data) {
                    return $data->assessment_plan_id;
                });
                
                //$resultFinal = $resultByPlanGroup[$list->plan_id]->first();
                
                if(isset($resultByPlanGroup) && count($resultByPlanGroup[$list->plan_id])>0) {
                    
                    $data[$list->term_id][$list->plan_id] = [
                            "term_id"=> $list->term_id,
                            "module_creation_id"=>$list->module_creation_id,
                            "id" => $list->plan_id,
                            "results" => ($resultByPlanGroup[$list->plan_id]) ?? null
                    ];
                    
                    $termData[$list->term_id] = [
                        "name" => $list->term_name,
                        "start_date" => $list->start_date,
                        "end_date" => $list->end_date,
                    ];
                    $planDetails[$list->term_id][$list->plan_id] = Plan::with(["tutor","personalTutor"])->where('id',$list->plan_id)->get()->first();
                    

                    //total code list and total class list

                }
            endforeach;
        return view('pages.students.live.result.index', [
            'title' => 'Live Students - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Accounts', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            "term" =>$termData,
            "grades" =>$grades,
            "planDetails" => $planDetails ?? null,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
