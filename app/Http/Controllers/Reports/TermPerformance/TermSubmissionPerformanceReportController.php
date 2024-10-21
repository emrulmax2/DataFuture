<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Plan;
use App\Models\Result;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermSubmissionPerformanceReportController extends Controller
{
    public function generateReport(Request $request){
        $term_declaration_id = (isset($request->sub_perf_term_id) && !empty($request->sub_perf_term_id) ? $request->sub_perf_term_id : 0);
        $html = $this->getHtml($term_declaration_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($term_declaration_id = 0){
        
    }

    public function exportReport($term_declaration_id = 0){

    }

    public function getHtml($term_declaration_id = 0){
        $res = $this->refineResult($term_declaration_id);

        $html = '';

        return $html;
    }

    public function refineResult($term_declaration_id = 0){
        $res = [];
        $term = TermDeclaration::find($term_declaration_id);
        $term_plans = Plan::where('term_declaration_id', $term_declaration_id)->get();
        $term_courses = $term_plans->pluck('course_id')->unique()->toArray();
        $term_creations = $term_plans->pluck('course_creation_id')->unique()->toArray();
        $term_plan_ids = $term_plans->pluck('id')->unique()->toArray();

        if(!empty($term_courses)):
            $t_exp_submission = $t_student_status = $t_no_of_submission = $t_total_pass = $t_grade_pass = $t_grade_merit = $t_grade_distinction = $t_grade_referred = $t_grade_plagiarised = $t_grade_absent = 0;
            foreach($term_courses as $course_id):
                $exp_submission = $student_status = $no_of_submission = $total_pass = $grade_pass = $grade_merit = $grade_distinction = $grade_referred = $grade_plagiarised = $grade_absent = 0;
                $course = Course::find($course_id);
                $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
                if(!empty($plan_ids)):
                    $exam_students = DB::table('results as rs')
                                    ->select(
                                        'rs.student_id', DB::raw('GROUP_CONCAT(DISTINCT(rs.plan_id)) as std_rs_cp_ids')
                                    )->whereIn('rs.plan_id', $plan_ids)
                                    ->groupBy('rs.student_id')->orderBy('rs.student_id', 'ASC')->get();
                    if($exam_students->count() > 0):
                        foreach($exam_students as $estd):
                            $student_id = $estd->student_id;
                            $student_plan_ids = (isset($estd->std_rs_cp_ids) && !empty($estd->std_rs_cp_ids) ? explode(',', str_replace(' ', '', $estd->std_rs_cp_ids)) : [0]);
                            $total_class = Assign::where('student_id', $student_id)->whereIn('plan_id', $student_plan_ids)->where(function($q){
                                                $q->whereNull('attendance')->orWhere('attendance', 1);
                                            })->get()->count();
                            if($total_class > 0):
                                $student_status += 1;
                                $t_student_status += 1;
                                
                                if(!empty($student_plan_ids)):
                                    foreach($student_plan_ids as $std_plan_id):
                                        $stdResult = Result::where('plan_id', $std_plan_id)->where('student_id', $student_id)->orderBy('id', 'ASC')->get()->first();
                                        if(isset($stdResult->grade_id) && $stdResult->grade_id > 0):
                                            $exp_submission += 1;
                                            $t_exp_submission += 1;

                                            if ($stdResult->grade_id == 3 || $stdResult->grade_id == 7 || $stdResult->grade_id == 6 || $stdResult->grade_id == 5 || $stdResult->grade_id == 4 || $stdResult->grade_id == 8) {
                                                $no_of_submission += 1;
                                                $t_no_of_submission += 1;
                                            }
                                            if ($stdResult->grade_id == 6 || $stdResult->grade_id == 5 || $stdResult->grade_id == 4) {
                                                $total_pass += 1;
                                                $t_total_pass += 1;
                                            }
                                            if ($stdResult->grade_id == 6) {
                                                $grade_pass += 1;
                                                $t_grade_pass += 1;
                                            }
                                            if ($stdResult->grade_id == 5) {
                                                $grade_merit += 1;
                                                $t_grade_merit += 1;
                                            }
                                            if ($stdResult->grade_id == 4) {
                                                $grade_distinction += 1;
                                                $t_grade_distinction += 1;
                                            }
                                            if ($stdResult->grade_id == 7) {
                                                $grade_referred += 1;
                                                $t_grade_referred += 1;
                                            }
                                            if ($stdResult->grade_id == 3) {
                                                $grade_plagiarised += 1;
                                                $t_grade_plagiarised += 1;
                                            }
                                            if ($stdResult->grade_id == 2) {
                                                $grade_absent += 1;
                                                $t_grade_absent += 1;
                                            }
                                        endif;
                                    endforeach;
                                endif;
                            endif;
                        endforeach;
                    endif;
                endif;
                $res['result'][$term->id]['course'][$course_id]['name'] = $course->name;
                $res['result'][$term->id]['course'][$course_id]['exp_submission'] = $exp_submission;
                $res['result'][$term->id]['course'][$course_id]['no_of_submission'] = $no_of_submission;
                $res['result'][$term->id]['course'][$course_id]['total_pass'] = $total_pass;
                $res['result'][$term->id]['course'][$course_id]['grade_pass'] = $grade_pass;
                $res['result'][$term->id]['course'][$course_id]['grade_merit'] = $grade_merit;
                $res['result'][$term->id]['course'][$course_id]['grade_distinction'] = $grade_distinction;
                $res['result'][$term->id]['course'][$course_id]['grade_reffered'] = $grade_referred;
                $res['result'][$term->id]['course'][$course_id]['grade_plagiarised'] = $grade_plagiarised;
                $res['result'][$term->id]['course'][$course_id]['grade_absent'] = $grade_absent;
                $res['result'][$term->id]['course'][$course_id]['total_pass_rate'] = (($total_pass > 0 && $no_of_submission > 0) ? number_format(($total_pass / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['grade_pass_rate'] = (($grade_pass > 0 && $no_of_submission > 0) ? number_format(($grade_pass / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['grade_merit_rate'] = (($grade_merit > 0 && $no_of_submission > 0) ? number_format(($grade_merit / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['grade_distinction_rate'] = (($grade_distinction > 0 && $no_of_submission > 0) ? number_format(($grade_distinction / $no_of_submission) * 100, 2) : '0');
            endforeach;

            $res['result'][$term->id]['name'] = $term->name;
            $res['result'][$term->id]['exp_submission'] = $t_exp_submission;
            $res['result'][$term->id]['no_of_submission'] = $t_no_of_submission;
            $res['result'][$term->id]['total_pass'] = $t_total_pass;
            $res['result'][$term->id]['grade_pass'] = $t_grade_pass;
            $res['result'][$term->id]['grade_merit'] = $t_grade_merit;
            $res['result'][$term->id]['grade_distinction'] = $t_grade_distinction;
            $res['result'][$term->id]['grade_reffered'] = $t_grade_referred;
            $res['result'][$term->id]['grade_plagiarised'] = $t_grade_plagiarised;
            $res['result'][$term->id]['grade_absent'] = $t_grade_absent;
            $res['result'][$term->id]['total_pass_rate'] = (($t_total_pass > 0 && $t_no_of_submission > 0) ? number_format(($t_total_pass / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['grade_pass_rate'] = (($t_grade_pass > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_pass / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['grade_merit_rate'] = (($t_grade_merit > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_merit / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['grade_distinction_rate'] = (($t_grade_distinction > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_distinction / $t_no_of_submission) * 100, 2) : '0');
        endif;

        //dd($res);
        return $res;
    }

}
