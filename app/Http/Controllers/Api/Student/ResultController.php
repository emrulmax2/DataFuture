<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\ExamResultPrev;
use App\Models\Grade;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;
use App\Models\Result;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\Cache;

class ResultController extends Controller
{
    public function index(Request $request) 
    {
        $theUser = $request->user();
        $cacheKey = 'resultlist_data_student_user_' . $theUser->id;
        $cacheSecondaryKey = 'prev_resultlist_data_student_user_' . $theUser->id;
        $student = Student::where('student_user_id', $theUser->id)->first();


        $prevResultCount = Cache::remember($cacheSecondaryKey, now()->addHours(1), function () use ($student) {
            $subQuery = ExamResultPrev::select('id')->where('student_id', $student->id)->groupBy('student_id', 'course_module_id')->havingRaw('MAX(created_at)');
           return ExamResultPrev::whereIn('id', $subQuery)->where('student_id', $student->id)->get()->count();
        });
        $resultData = Cache::remember($cacheKey, now()->addHours(1), function () use ($student) {
            // Fetch and process result data for the student user
            
            //$grades = Grade::all();
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
            // $termDeclarationIds = Plan::whereHas('assign', function($query) use ($student) {
            //     $query->where('student_id', $student->id);
            // })->with('creations.module')->get()->groupBy(function ($plan) {
            //     return $plan->creations->module->name;
            // })->map(function ($group) {
            //     return $group->pluck('term_declaration_id')->unique()->map(function ($termDeclarationId) {
            //         return TermDeclaration::find($termDeclarationId);
            //     });
            // });
            // $termSet = $termDeclarationIds;


                


            $serial = 0;
            $results = [];
            $dataContainer = $data;
            foreach($dataContainer as $moduleDetails => $resultSet) {
                $currentResult = $resultSet[0];
                $results[$serial]["sn"] = $serial+1;
                if($currentResult->term_declaration_id == Null):
                    $results[$serial]["term"] = $currentResult->plan->attenTerm->name;
                else:
                    $results[$serial]["term"] = $currentResult->term->name;
                endif;
                $results[$serial]["code"] = $currentResult->plan->creations->code;
                $results[$serial]["module"] = $currentResult->plan->creations->module_name ."-". $currentResult->plan->creations->level->name;
                $results[$serial]["grade"] = $currentResult->grade->code;
                $results[$serial]["merit"] = $currentResult->grade->name;
                $results[$serial]["body"] = $currentResult->plan->course->body->name;
                $results[$serial]["date"] = date('d F, Y h:i a',strtotime($currentResult->published_at));
                $results[$serial]["attempted"] = count($resultSet);
                $results[$serial]["updated_by"] = isset($currentResult->updatedBy->employee->full_name)  ? $currentResult->updatedBy->employee->full_name : (isset($currentResult->createdBy->employee->full_name) ? $currentResult->createdBy->employee->full_name : $currentResult->createdBy->name);

                foreach($resultSet as $result):
                    if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                    $results[$serial]["attemptData"]["term"] = $result->term->name;
                    else
                        $results[$serial]["attemptData"]["term"] = $result->plan->attenTerm->name;

                    $results[$serial]["attemptData"]["code"] = ($result->module_code)? $result->module_code :$result->plan->creations->code;
                    $results[$serial]["attemptData"]["created_at"] = date('d F,Y h:i a',strtotime($result->created_at));
                    $results[$serial]["attemptData"]["published_at"] = date('d F,Y h:i a',strtotime($result->published_at));
                    $results[$serial]["attemptData"]["grade"] = $result->grade->code;
                    $results[$serial]["attemptData"]["merit"] = $result->grade->name;
                    $results[$serial]["attemptData"]["updated_by"] = isset($result->updatedBy->employee->full_name)  ? $result->updatedBy->employee->full_name : (isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name);
                endforeach;
                $serial++;
            }
            return new ResultResource($results);
        });
        
        return response()->json([
                'status' => 'success',
                'data' => $resultData,
                'previous_result_count' => $prevResultCount,
            ]);
        
    }
}