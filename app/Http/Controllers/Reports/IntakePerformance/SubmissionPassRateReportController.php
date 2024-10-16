<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubmissionPassRateReportController extends Controller
{
    public function getSubmissionPassRatReport(Request $request){
        $sub_pass_semester_id = (isset($request->sub_pass_semester_id) && !empty($request->sub_pass_semester_id) ? $request->sub_pass_semester_id : []);
        $html = $this->getHtml($sub_pass_semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function getHtml(){

    }
}
