<?php

namespace App\Http\Controllers\Student\Result;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\ExamResultPrev;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\ModuleLevel;
use App\Models\Plan;
use App\Models\QualAwardResult;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\TermDeclaration;
use App\Models\User;
use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Carbon\Carbon;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
class StudentResultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Student $student)
    {
        $grades = Grade::all();
        $courseCreationIds = StudentCourseRelation::where('student_id', $student->id)->get()->pluck('course_creation_id')->toArray();
        sort($courseCreationIds);
        $courseRelationActiveCourseId = $student->crel->creation->id;
        $maxCourseCreationId = max($courseCreationIds);
        $minCourseCreationId = min($courseCreationIds);
            
            $data = [];
            $planList = Result::where('student_id', $student->id)->get()->pluck('plan_id')->unique()->toArray();
            $QueryPart = Plan::whereIn('id',$planList);
            
            $QueryPart->where('course_creation_id','>=',$courseRelationActiveCourseId);

            if($courseRelationActiveCourseId < $maxCourseCreationId && $courseRelationActiveCourseId >= $minCourseCreationId) {

                $arrayCurrentKey = array_search($courseRelationActiveCourseId, $courseCreationIds);
                $nextCourseCreationId = $courseCreationIds[$arrayCurrentKey+1];
                $QueryPart->where('course_creation_id','<',$nextCourseCreationId);

            }
            $QueryPart->orderBy('id','DESC');
            $QueryInner = $QueryPart->get();


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
                        $termSet[$moduleCreation->module->name][] = isset($result->term_declaration_id) ? TermDeclaration::where('id',$result->term_declaration_id)->first() : $result->plan->attenTerm;
                    }
                }
            endforeach;

        $subQuery = ExamResultPrev::select('id')->where('student_id', $student->id)->groupBy('student_id', 'course_module_id')->havingRaw('MAX(created_at)');
        $prevResultCount = ExamResultPrev::whereIn('id', $subQuery)->where('student_id', $student->id)->get()->count();
        
        return view('pages.students.live.result.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            'termSet'=> ($termSet) ?? null,
            "grades" =>$grades,
            "terms" =>TermDeclaration::orderBy('id','DESC')->get(),
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'prev_result_count' => $prevResultCount,
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'award' => isset($student->awarded) && isset($student->awarded->id) && $student->awarded->id > 0 ? $student->awarded : null
        ]);
    }

    public function print(Student $student) {

        
        //$signature = Signatory::all();
        $grades = Grade::all();
        
        $termData = [];

        $data = [];
        $resultPrimarySet = [];
        $planList = Result::where('student_id', $student->id)->get()->pluck('plan_id')->unique()->toArray();
        $QueryInner = Plan::whereIn('id',$planList)->orderBy('id','DESC')->get();
        
        $level_list = [];
        $dataPrevious = [];
        $termSetPrevious = [];
        $courseCreation = "";
        foreach($QueryInner as $list):
            $moduleCreation = ModuleCreation::with('module','level')->where('id',$list->module_creation_id)->get()->first();
            $uniqueLevelArry = $moduleCreation->pluck('module_level_id')->unique()->toArray();
            $courseModuleArry = $moduleCreation->pluck('course_module_id')->unique()->toArray();
            $uniqueLevelSet = ModuleLevel::whereIn('id', $uniqueLevelArry)->get();
            foreach($uniqueLevelSet as $level):
                $level_list[$level->id] = $level->name;
            endforeach;
            
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
                    $courseCreationStart = $result->plan->cCreation->available->course_start_date;
                    $data[$moduleCreation->module->name][] = $result;
                    $termSet[$moduleCreation->module->name][] = isset($result->term_declaration_id) ? TermDeclaration::where('id',$result->term_declaration_id)->first() : $result->plan->attenTerm;
                }

            }
            
        endforeach;
        
        $checkPreviousResult = ExamResultPrev::where("student_id", $student->id)->orderBy('id','DESC')->get();
        if($checkPreviousResult->isNotEmpty()) {

            foreach ($checkPreviousResult as $key => $result) {

                $dataPrevious[$result->courseModule->name][] = $result;
                $termSetPrevious[$result->courseModule->name][] = isset($result->semester_id) ? Semester::where('id',$result->semester_id)->first()->name : '';
            }

        }

        $pdf = PDF::loadView('pages.students.live.result.pdf.index', compact('data', 'termSet', 'student', 'grades','level_list','dataPrevious','termSetPrevious','courseCreationStart'));
        return $pdf->download('student_result.pdf');
        
    }

 /**
     * Display a listing of the resource.
     */
    public function frontEndIndex(Student $student)
    {
        $grades = Grade::all();
        $courseCreationIds = StudentCourseRelation::where('student_id', $student->id)->get()->pluck('course_creation_id')->toArray();
        sort($courseCreationIds);
        $courseRelationActiveCourseId = $student->crel->creation->id;
        $maxCourseCreationId = max($courseCreationIds);
        $minCourseCreationId = min($courseCreationIds);
            
            $termData = [];
            $data = [];
            $resultPrimarySet = [];
            $planList = Result::where('student_id', $student->id)->get()->pluck('plan_id')->unique()->toArray();
            $QueryPart = Plan::whereIn('id',$planList);
            
            $QueryPart->where('course_creation_id','>=',$courseRelationActiveCourseId);

            if($courseRelationActiveCourseId < $maxCourseCreationId && $courseRelationActiveCourseId >= $minCourseCreationId) {

                $arrayCurrentKey = array_search($courseRelationActiveCourseId, $courseCreationIds);
                $nextCourseCreationId = $courseCreationIds[$arrayCurrentKey+1];
                $QueryPart->where('course_creation_id','<',$nextCourseCreationId);

            }
            $QueryPart->orderBy('id','DESC');
            $QueryInner = $QueryPart->get();

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
                ->where('published_at','<',Carbon::now())
                ->orderBy('id','DESC')->get();
                
                if($checkPrimaryResult->isNotEmpty()) {
                    foreach ($checkPrimaryResult as $key => $result) {
                        $data[$moduleCreation->module->name][] = $result;
                        $termSet[$moduleCreation->module->name][] = isset($result->term_declaration_id) ? TermDeclaration::where('id',$result->term_declaration_id)->first() : $result->plan->attenTerm;
                    }
                }
                if(isset($result->id))
                    $resultPrimarySet[$result->id] = null;
                
            endforeach;


        $subQuery = ExamResultPrev::select('id')->where('student_id', $student->id)->groupBy('student_id', 'course_module_id')->havingRaw('MAX(created_at)');
        $prevResultCount = ExamResultPrev::whereIn('id', $subQuery)->where('student_id', $student->id)->get()->count();
        
        return view('pages.students.frontend.resultset.index', [
            'title' => 'Students - Results',
            'breadcrumbs' => [
                ['label' => 'Live Student', 'href' => route('student')],
                ['label' => 'Results', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'dataSet' => ($data) ?? null,
            'termSet'=> ($termSet) ?? null,
            "grades" =>$grades,
            "terms" =>TermDeclaration::orderBy('id','DESC')->get(),
            "resultPrimarySet" =>$resultPrimarySet,
            'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
            'prev_result_count' => $prevResultCount,
            'qualAwards' => QualAwardResult::orderBy('id', 'ASC')->get(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'award' => isset($student->awarded) && isset($student->awarded->id) && $student->awarded->id > 0 ? $student->awarded : null
        ]);
    }
}
