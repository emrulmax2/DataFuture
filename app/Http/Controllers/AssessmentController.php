<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Http\Requests\AssessmentRequest;
use App\Models\CourseModuleBaseAssesment;

class AssessmentController extends Controller
{

    public function store(Request $request){
        $module_creation_id = $request->module_creation_id;
        $cmb_assessment = (isset($request->cmb_assessment) && !empty($request->cmb_assessment) ? $request->cmb_assessment : []);
        
        if(!empty($cmb_assessment) && $module_creation_id > 0):
            foreach($cmb_assessment as $assementID):
                $moduleBaseAssessment = CourseModuleBaseAssesment::find($assementID);

                $data = [];
                $data['module_creation_id'] = $module_creation_id;
                $data['course_module_base_assesment_id'] = $assementID;
                $data['assessment_name'] = $moduleBaseAssessment->assesment_name;
                $data['assessment_code'] = $moduleBaseAssessment->assesment_code;
                $data['created_by'] = auth()->user()->id;
                
                Assessment::create($data);
            endforeach;
            return response()->json(['Message' => 'Assessments successfully inserted', 'red' => route('term.module.creation')], 200);
        else:
            return response()->json(['Message' => 'Something went wrong. Please try later or contact with the Administrator.']);
        endif;
    }

    public function update(Request $request){
        $module_creation_id = $request->module_creation_id;
        $cmb_assessment = (isset($request->cmb_assessment) && !empty($request->cmb_assessment) ? $request->cmb_assessment : []);

        $existingAssessments = Assessment::where('module_creation_id', $module_creation_id)->get();
        $existIDs = [];
        if(!empty($existingAssessments)):
            foreach($existingAssessments as $ea):
                $existIDs[] = $ea->id;
            endforeach;
        endif;


        if(!empty($cmb_assessment)):
            foreach($cmb_assessment as $assementID):
                $moduleBaseAssessment = CourseModuleBaseAssesment::find($assementID);
                $assessment = Assessment::where('module_creation_id', $module_creation_id)->where('course_module_base_assesment_id', $assementID)->first();
                if(empty($assessment)):
                    $data = [];
                    $data['module_creation_id'] = $module_creation_id;
                    $data['course_module_base_assesment_id'] = $assementID;
                    $data['assessment_name'] = $moduleBaseAssessment->assesment_name;
                    $data['assessment_code'] = $moduleBaseAssessment->assesment_code;
                    $data['created_by'] = auth()->user()->id;
                    
                    Assessment::create($data);
                else:
                    if (($key = array_search($assessment->id, $existIDs)) !== false) {
                        unset($existIDs[$key]);
                    }
                endif;
            endforeach;
        endif;

        if(!empty($existIDs)):
            foreach($existIDs as $id):
                Assessment::where('id', $id)->where('module_creation_id', $module_creation_id)->forceDelete();
            endforeach;
        endif;
        
        return response()->json(['message' => 'Data updated'], 200);
    }
}
