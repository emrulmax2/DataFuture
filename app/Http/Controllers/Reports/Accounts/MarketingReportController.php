<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\MarketingReportGenerateRequest;
use App\Models\AgentComission;
use App\Models\CourseCreation;
use App\Models\SemesterComissionRate;
use App\Models\SlcMoneyReceipt;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

class MarketingReportController extends Controller
{
    public function generateReport(MarketingReportGenerateRequest $request){
        $semester_id = (isset($request->marketing_semester_id) && $request->marketing_semester_id > 0 ? $request->marketing_semester_id : 0);
        $comissionRate = SemesterComissionRate::where('semester_id', $semester_id)->orderBy('id', 'DESC')->get()->first();
        $theRate = (isset($comissionRate->rate) && $comissionRate->rate > 0 ? $comissionRate->rate : 0);
        $courseCreationIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $sutdentIds = StudentCourseRelation::whereIn('course_creation_id', $courseCreationIds)->where('active', 1)->pluck('student_id')->unique()->toArray();

        $monyReceipts = SlcMoneyReceipt::whereNot('payment_type', 'Refund')->whereIn('student_id', $sutdentIds)->orderBy('id', 'DESC')->get();
        $totalReceived = ($monyReceipts->count() > 0 ? $monyReceipts->sum('amount') : 0);

        $refundReceipts = SlcMoneyReceipt::where('payment_type', 'Refund')->whereIn('student_id', $sutdentIds)->orderBy('id', 'DESC')->get();
        $totaRefund = ($refundReceipts->count() > 0 ? $refundReceipts->sum('amount') : 0);

        $comissionPaid = AgentComission::with('comissions', 'payment')->where('semester_id', $semester_id)->whereHas('payment', function($q){
                            $q->where('status', 2);
                        })->orderBy('id', 'ASC')->get();
        $totalComissionPaid = 0;
        if($comissionPaid->count() > 0):
            foreach($comissionPaid as $cp):
                $totalComissionPaid += (isset($cp->comissions) && $cp->comissions->count() > 0 ? $cp->comissions->sum('amount') : 0);
            endforeach;
        endif;

        $comissionScheduled = AgentComission::with('comissions', 'payment')->where('semester_id', $semester_id)->whereHas('payment', function($q){
                            $q->where('status', 1);
                        })->orderBy('id', 'ASC')->get();
        $totalComissionScheduled = 0;
        if($comissionScheduled->count() > 0):
            foreach($comissionScheduled as $cs):
                $totalComissionScheduled += (isset($cs->comissions) && $cs->comissions->count() > 0 ? $cs->comissions->sum('amount') : 0);
            endforeach;
        endif;
        $total = ($totalReceived - $totaRefund);
        $comissionDue = ($total > 0 ? ($total * $theRate) / 100 : 0);

        $balance = ($comissionDue - ($totalComissionPaid + $totalComissionScheduled));

        $html = '';
        $html .= '<table class="table table-sm table-bordered">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="text-left">Descriptions</th>';
                    $html .= '<th class="text-right w-36">Amount</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Total Collection</td>';
                    $html .= '<td class="text-right w-36">'.Number::currency($totalReceived, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Refund</td>';
                    $html .= '<td class="text-right w-36">-'.Number::currency($totaRefund, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left font-medium">Total</td>';
                    $html .= '<td class="text-right w-36 font-medium">'.Number::currency($total, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Rate</td>';
                    $html .= '<td class="text-right w-36">'.Number::percentage($theRate, 2).'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Comission Due</td>';
                    $html .= '<td class="text-right w-36">'.Number::currency($comissionDue, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Comission Already Paid</td>';
                    $html .= '<td class="text-right w-36">'.Number::currency($totalComissionPaid, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left">Comission Scheduled</td>';
                    $html .= '<td class="text-right w-36">'.Number::currency($totalComissionScheduled, 'GBP').'</td>';
                $html .= '</tr>';
                $html .= '<tr>';
                    $html .= '<td class="text-left font-medium">Balance</td>';
                    $html .= '<td class="text-right w-36 font-medium">'.Number::currency($balance, 'GBP').'</td>';
                $html .= '</tr>';
            $html .= '</tbody>';
        $html .= '</table>';

        return response()->json(['html' => $html], 200);
    }
}
