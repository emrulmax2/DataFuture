<?php

namespace App\Http\Controllers\Student\Result;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Result;
use App\Models\Status;
use App\Models\Student;
use FontLib\Table\Type\name;
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
        
            $termData = [];
            // $QueryInner = DB::table('plans as plan')
            //             ->select('td.id as term_id',
            //                 'td.name as term_name',
            //                 'instance_terms.start_date',
            //                 'instance_terms.end_date', 
            //                 'plan.module_creation_id as module_creation_id' , 
            //                 'mc.module_name',
            //                 'mc.code as module_code', 
            //                 'plan.id as plan_id',
            //                 'abody.name as award' )
            //             ->leftJoin('instance_terms', 'instance_terms.id', 'plan.instance_term_id')
            //             ->leftJoin('assigns as assign', 'plan.id', 'assign.plan_id')
            //             ->leftJoin('term_declarations as td', 'td.id', 'plan.term_declaration_id')
            //             ->leftJoin('module_creations as mc', 'mc.id', 'plan.module_creation_id')
            //             ->leftJoin('courses', 'courses.id', 'plan.course_id')
            //             ->leftJoin('awarding_bodies as abody', 'abody.id', 'courses.awarding_body_id')
            //             ->where('assign.student_id', $student->id)
            //             ->orderBy("td.id",'desc')
            //             ->get();
            $data = [];
            $resultPrimarySet = [];

            $QueryInner = Plan::with('assign')->whereHas('assign', function($query) use ($student) {
                $query->where('student_id', $student->id);
            })->orderBy('id','DESC')->get();
            
            foreach($QueryInner as $list):
                $moduleCreation = ModuleCreation::with('module','level')->where('id',$list->module_creation_id)->get()->first();
                $checkPrimaryResult = Result::with([
                "grade",
                "createdBy",
                "updatedBy",
                "plan",
                "plan.creations",
                "plan.course.body",
                "plan.creations.module"])->where("student_id", $student->id)
                ->whereHas('plan', function($query) use ($list) {
                    $query->where('module_creation_id', $list->module_creation_id)->where('id', $list->id);
                })
                ->orderBy('id','DESC')->get();
                
                if($checkPrimaryResult->isNotEmpty()) {
                    foreach ($checkPrimaryResult as $key => $result) {
                        $data[$moduleCreation->module->name][] = $result;
                        
                        if($result->is_primary == "Yes") {
                            $resultPrimarySet[$result->id] = "Yes";
                        }
                    }

                }
                
            endforeach;
        return view('pages.students.live.result.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            "grades" =>$grades,
            "resultPrimarySet" =>$resultPrimarySet,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

 
}
