<?php

namespace App\Http\Controllers\Student\Result;

use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use App\Models\ExamResultPrev;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\ModuleLevel;
use App\Models\OtherAcademicQualification;
use App\Models\Plan;
use App\Models\QualAwardResult;
use App\Models\ReasonForEngagementEnding;
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
            $QueryPart = Plan::with('attenTerm')->whereIn('id',$planList);
            
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
                        
                    }
                }
            endforeach;


            // Adjusted query and mapping logic
            $termDeclarationIds = Plan::whereHas('assign', function($query) use ($student) {
                $query->where('student_id', $student->id);
            })->with('creations.module')->get()->groupBy(function ($plan) {
                return $plan->creations->module->name;
            })->map(function ($group) {
                return $group->pluck('term_declaration_id')->unique()->map(function ($termDeclarationId) {
                    return TermDeclaration::find($termDeclarationId);
                });
            });
            $termSet = $termDeclarationIds;

            
        $subQuery = ExamResultPrev::select('id')->where('student_id', $student->id)->groupBy('student_id', 'course_module_id')->havingRaw('MAX(created_at)');
        $prevResultCount = ExamResultPrev::whereIn('id', $subQuery)->where('student_id', $student->id)->get()->count();
        
        return view('pages.students.live.result.index', [
            'title' => 'Students - Results',
            'layout' => 'student-top-menu',
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
            'otherAcademicQualifications' => OtherAcademicQualification::where('active', 1)->orderBy('id', 'ASC')->get(),
            'reasonEndings' => ReasonForEngagementEnding::where('active', 1)->orderBy('id', 'ASC')->get(),
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
        $courseCreationStart = "";
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

        $logo = public_path('build/assets/images/LCC-logo.png');
        $gradient = $this->buildGradientBar();
        $pdf = PDF::loadView('pages.students.live.result.pdf.index', compact('data', 'termSet', 'student', 'grades','level_list','dataPrevious','termSetPrevious','courseCreationStart','logo','gradient'))
                    ->setPaper('a4');
        return $pdf->download('student_result.pdf');

    }

    /**
     * Build the gold -> red -> teal gradient rule used on the transcript header
     * as a base64 PNG. dompdf's CSS linear-gradient support is unreliable, so we
     * render an actual image and stretch it to full width in the view.
     */
    protected function buildGradientBar()
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $width = 900;
        $height = 6;
        $img = imagecreatetruecolor($width, $height);

        // Stops: position (0..1) => [r, g, b]
        $stops = [
            [0.00, [0xc9, 0x99, 0x2e]], // gold
            [0.55, [0xa3, 0x16, 0x21]], // red
            [1.00, [0x0b, 0x6b, 0x66]], // teal
        ];

        for ($x = 0; $x < $width; $x++) {
            $t = $x / ($width - 1);
            // Locate the surrounding stops.
            $lo = $stops[0];
            $hi = $stops[count($stops) - 1];
            for ($i = 0; $i < count($stops) - 1; $i++) {
                if ($t >= $stops[$i][0] && $t <= $stops[$i + 1][0]) {
                    $lo = $stops[$i];
                    $hi = $stops[$i + 1];
                    break;
                }
            }
            $span = ($hi[0] - $lo[0]) ?: 1;
            $f = ($t - $lo[0]) / $span;
            $r = (int) round($lo[1][0] + ($hi[1][0] - $lo[1][0]) * $f);
            $g = (int) round($lo[1][1] + ($hi[1][1] - $lo[1][1]) * $f);
            $b = (int) round($lo[1][2] + ($hi[1][2] - $lo[1][2]) * $f);
            $col = imagecolorallocate($img, $r, $g, $b);
            imageline($img, $x, 0, $x, $height - 1, $col);
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($png);
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
