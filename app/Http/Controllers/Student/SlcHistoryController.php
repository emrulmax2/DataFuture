<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use Illuminate\Http\Request;

class SlcHistoryController extends Controller
{
    public function getRegistrationConfirmationDetails(Request $request){
        $studen_id = $request->studen_id;
        $academic_year_id = $request->academic_year_id;
        $course_creation_instance_id = $request->course_creation_instance_id;

        $instance = CourseCreationInstance::find($course_creation_instance_id);
        $fees = (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0);

        $html = '';
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $course_creation_instance_id)->orderBy('session_term', 'ASC')->get();
        if(!empty($instanceTerm) && $instanceTerm->count() > 0):
            $html .= '<option value="">Please Selects</option>';
            foreach($instanceTerm as $insTerm):
                $html .= '<option value="'.$insTerm->session_term.'">Term 0'.$insTerm->session_term.'</option>';
            endforeach;
        endif;

        return response()->json(['fees' => $fees, 'session_term_html' => $html], 200);
    }
}
