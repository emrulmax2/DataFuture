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
use App\Models\TermDeclaration;
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

            $data = [];
            $resultPrimarySet = [];
            $planList = Result::where('student_id', $student->id)->get()->pluck('plan_id')->unique()->toArray();
            $QueryInner = Plan::whereIn('id',$planList)->orderBy('id','DESC')->get();
            
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
            "terms" =>TermDeclaration::orderBy('id','DESC')->get(),
            "resultPrimarySet" =>$resultPrimarySet,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get()
        ]);
    }

 
}
