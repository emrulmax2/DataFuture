<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContinuationReportController extends Controller
{
    public function getContinuationReport(Request $request){
        $cr_semester_id = (isset($request->cr_semester_id) && !empty($request->cr_semester_id) ? $request->cr_semester_id : []);
    }
}
