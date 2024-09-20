<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountCollectionReportRequest;
use App\Models\SlcMoneyReceipt;
use Illuminate\Http\Request;

use App\Exports\ArrayCollectionExport;
use App\Models\SlcPaymentMethod;
use Maatwebsite\Excel\Facades\Excel;

class CollectionReportController extends Controller
{
    public function index(){
        return view('pages.reports.accounts.collection', [
            'title' => 'Collection Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Accounts', 'href' => route('reports.accounts')],
                ['label' => 'Collection Reports', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function exportCollectionReport(AccountCollectionReportRequest $request){
        $from_date = isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : date('Y-m-d');
        $to_date = isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : date('Y-m-d');
        $date_type = isset($request->date_type) && empty($request->date_type) ? $request->date_type : 'entry_date';
        $slcPaymentMethods = SlcPaymentMethod::orderBy('id', 'asc')->get();

        /* Collection Part Start */
        $overAllTotal = 0;
        $coursesTotal = 0;
        $othersTotal = 0;
        $overall = [];
        $courses = [];
        $others = [];
        if($slcPaymentMethods->count()):
            foreach($slcPaymentMethods as $method):
                $overall[$method->id]['name'] = $method->name;
                $overall[$method->id]['amount'] = 0;
                $courses[$method->id]['name'] = $method->name;
                $courses[$method->id]['amount'] = 0;
                $others[$method->id]['name'] = $method->name;
                $others[$method->id]['amount'] = 0;
            endforeach;
        endif;

        $theCollection = [];
        $theCollection[1][0] = 'COLLECTIONS:';

        $theCollection[2][0] = '';

        $theCollection[3][0] = 'Invoice Date';
        $theCollection[3][1] = 'Created Date';
        $theCollection[3][2] = 'Invice No';
        $theCollection[3][3] = 'Student ID';
        $theCollection[3][4] = 'SSN No';
        $theCollection[3][5] = 'Name';
        $theCollection[3][6] = 'Course';
        $theCollection[3][7] = 'Semester';
        $theCollection[3][8] = 'Status';
        $theCollection[3][9] = 'Agreement Date';
        $theCollection[3][10] = 'Agreement Year';
        $theCollection[3][11] = 'Amount';
        $theCollection[3][12] = 'Payment For';
        $theCollection[3][13] = 'Method';
        $theCollection[3][14] = 'Received By';

        $row = 4;
        $monyReceipts = SlcMoneyReceipt::with('student', 'agreement', 'crel', 'received', 'method')->whereNot('payment_type', 'Refund')->whereBetween($date_type, [$from_date, $to_date])->orderBy('id', 'DESC')->get();
        $totalInflow = 0;
        if($monyReceipts->count() > 0):
            foreach($monyReceipts as $recpt):
                $amount = (isset($recpt->amount) && $recpt->amount > 0 ? $recpt->amount : 0);
                $slc_payment_method_id = (isset($recpt->slc_payment_method_id) && $recpt->slc_payment_method_id > 0 ? $recpt->slc_payment_method_id : 0);
                $overall[$slc_payment_method_id]['amount'] += $amount;
                if($recpt->payment_type == 'Course Fee'):
                    $courses[$slc_payment_method_id]['amount'] += $amount;
                    $coursesTotal += $amount;
                else:
                    $others[$slc_payment_method_id]['amount'] += $amount;
                    $othersTotal += $amount;
                endif;
                $overAllTotal += $amount;

                $theCollection[$row][0] = (isset($recpt->payment_date) && !empty($recpt->payment_date) ? $recpt->payment_date : '');
                $theCollection[$row][1] = (isset($recpt->entry_date) && !empty($recpt->entry_date) ? $recpt->entry_date : (isset($recpt->created_at) && !empty($recpt->created_at) ? $recpt->created_at : ''));
                $theCollection[$row][2] = (isset($recpt->invoice_no) && !empty($recpt->invoice_no) ? $recpt->invoice_no : '');
                $theCollection[$row][3] = (isset($recpt->student->registration_no) && !empty($recpt->student->registration_no) ? $recpt->student->registration_no : '');
                $theCollection[$row][4] = (isset($recpt->student->ssn_no) && !empty($recpt->student->ssn_no) ? $recpt->student->ssn_no : '');
                $theCollection[$row][5] = (isset($recpt->student->full_name) && !empty($recpt->student->full_name) ? $recpt->student->full_name : '');
                $theCollection[$row][6] = (isset($recpt->crel->creation->course->name) && !empty($recpt->crel->creation->course->name) ? $recpt->crel->creation->course->name : '');
                $theCollection[$row][7] = (isset($recpt->crel->creation->semester->name) && !empty($recpt->crel->creation->semester->name) ? $recpt->crel->creation->semester->name : '');
                $theCollection[$row][8] = (isset($recpt->student->status->name) && !empty($recpt->student->status->name) ? $recpt->student->status->name : '');
                $theCollection[$row][9] = (isset($recpt->agreement->date) && !empty($recpt->agreement->date) ? $recpt->agreement->date : '');
                $theCollection[$row][10] = (isset($recpt->agreement->year) && !empty($recpt->agreement->year) ? $recpt->agreement->year : '');
                $theCollection[$row][11] = (isset($recpt->amount) && !empty($recpt->amount) ? $recpt->amount : '0');
                $theCollection[$row][12] = (isset($recpt->payment_type) && !empty($recpt->payment_type) ? $recpt->payment_type : '');
                $theCollection[$row][13] = (isset($recpt->method->name) && !empty($recpt->method->name) ? $recpt->method->name : '');
                $theCollection[$row][14] = (isset($recpt->received->employee->full_name) && !empty($recpt->received->employee->full_name) ? $recpt->received->employee->full_name : (isset($recpt->received->name) ? $recpt->received->name : ''));

                $row += 1;
            endforeach;
        endif;

        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;

        $theCollection[$row][0] = '';
        $theCollection[$row][1] = '';
        $theCollection[$row][2] = '';
        $theCollection[$row][3] = 'Course Fees Only';
        $theCollection[$row][4] = '';
        $theCollection[$row][5] = '';
        $theCollection[$row][6] = 'Others Fees';
        $row += 1;

        $theCollection[$row][0] = 'Total';
        $theCollection[$row][1] = $overAllTotal;
        $theCollection[$row][2] = '';
        $theCollection[$row][3] = 'Total';
        $theCollection[$row][4] = $coursesTotal;
        $theCollection[$row][5] = '';
        $theCollection[$row][6] = 'Total';
        $theCollection[$row][7] = $othersTotal;
        $theCollection[$row][8] = '';
        $row += 1;

        if(!empty($overall)):
            foreach($overall as $method => $detail):
                $theCollection[$row][0] = $detail['name'];
                $theCollection[$row][1] = (isset($detail['amount']) && $detail['amount'] > 0 ? $detail['amount'] : 0);
                $theCollection[$row][2] = '';
                $theCollection[$row][3] = $courses[$method]['name'];
                $theCollection[$row][4] = (isset($courses[$method]['amount']) && $courses[$method]['amount'] > 0 ? $courses[$method]['amount'] : 0);
                $theCollection[$row][5] = '';
                $theCollection[$row][6] = $others[$method]['name'];
                $theCollection[$row][7] = (isset($others[$method]['amount']) && $others[$method]['amount'] > 0 ? $others[$method]['amount'] : 0);
                $theCollection[$row][8] = '';

                $row += 1;
            endforeach;
        endif;
        /* Collection Part End */

        /* Refund Part Start */
        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;

        $overAllTotal = 0;
        $coursesTotal = 0;
        $othersTotal = 0;
        $overall = [];
        $courses = [];
        $others = [];
        if($slcPaymentMethods->count()):
            foreach($slcPaymentMethods as $method):
                $overall[$method->id]['name'] = $method->name;
                $overall[$method->id]['amount'] = 0;
                $courses[$method->id]['name'] = $method->name;
                $courses[$method->id]['amount'] = 0;
                $others[$method->id]['name'] = $method->name;
                $others[$method->id]['amount'] = 0;
            endforeach;
        endif;

        $theCollection[$row][0] = 'REFUNDS:';
        $row += 1;

        $theCollection[$row][0] = '';
        $row += 1;

        $theCollection[$row][0] = 'Invoice Date';
        $theCollection[$row][1] = 'Created Date';
        $theCollection[$row][2] = 'Invice No';
        $theCollection[$row][3] = 'Student ID';
        $theCollection[$row][4] = 'SSN No';
        $theCollection[$row][5] = 'Name';
        $theCollection[$row][6] = 'Course';
        $theCollection[$row][7] = 'Semester';
        $theCollection[$row][8] = 'Status';
        $theCollection[$row][9] = 'Agreement Date';
        $theCollection[$row][10] = 'Agreement Year';
        $theCollection[$row][11] = 'Amount';
        $theCollection[$row][12] = 'Payment For';
        $theCollection[$row][13] = 'Method';
        $theCollection[$row][14] = 'Received By';
        $row += 1;

        $monyReceipts = SlcMoneyReceipt::with('student', 'agreement', 'crel', 'received', 'method')->where('payment_type', 'Refund')->whereBetween($date_type, [$from_date, $to_date])->orderBy('id', 'DESC')->get();
        $totalInflow = 0;
        if($monyReceipts->count() > 0):
            foreach($monyReceipts as $recpt):
                $amount = (isset($recpt->amount) && $recpt->amount > 0 ? $recpt->amount : 0);
                $slc_payment_method_id = (isset($recpt->slc_payment_method_id) && $recpt->slc_payment_method_id > 0 ? $recpt->slc_payment_method_id : 0);
                $overall[$slc_payment_method_id]['amount'] += $amount;
                if($recpt->payment_type == 'Refund'):
                    $courses[$slc_payment_method_id]['amount'] += $amount;
                    $coursesTotal += $amount;
                else:
                    $others[$slc_payment_method_id]['amount'] += $amount;
                    $othersTotal += $amount;
                endif;
                $overAllTotal += $amount;

                $theCollection[$row][0] = (isset($recpt->payment_date) && !empty($recpt->payment_date) ? $recpt->payment_date : '');
                $theCollection[$row][1] = (isset($recpt->entry_date) && !empty($recpt->entry_date) ? $recpt->entry_date : (isset($recpt->created_at) && !empty($recpt->created_at) ? $recpt->created_at : ''));
                $theCollection[$row][2] = (isset($recpt->invoice_no) && !empty($recpt->invoice_no) ? $recpt->invoice_no : '');
                $theCollection[$row][3] = (isset($recpt->student->registration_no) && !empty($recpt->student->registration_no) ? $recpt->student->registration_no : '');
                $theCollection[$row][4] = (isset($recpt->student->ssn_no) && !empty($recpt->student->ssn_no) ? $recpt->student->ssn_no : '');
                $theCollection[$row][5] = (isset($recpt->student->full_name) && !empty($recpt->student->full_name) ? $recpt->student->full_name : '');
                $theCollection[$row][6] = (isset($recpt->crel->creation->course->name) && !empty($recpt->crel->creation->course->name) ? $recpt->crel->creation->course->name : '');
                $theCollection[$row][7] = (isset($recpt->crel->creation->semester->name) && !empty($recpt->crel->creation->semester->name) ? $recpt->crel->creation->semester->name : '');
                $theCollection[$row][8] = (isset($recpt->student->status->name) && !empty($recpt->student->status->name) ? $recpt->student->status->name : '');
                $theCollection[$row][9] = (isset($recpt->agreement->date) && !empty($recpt->agreement->date) ? $recpt->agreement->date : '');
                $theCollection[$row][10] = (isset($recpt->agreement->year) && !empty($recpt->agreement->year) ? $recpt->agreement->year : '');
                $theCollection[$row][11] = (isset($recpt->amount) && !empty($recpt->amount) ? $recpt->amount : '0');
                $theCollection[$row][12] = (isset($recpt->payment_type) && !empty($recpt->payment_type) ? $recpt->payment_type : '');
                $theCollection[$row][13] = (isset($recpt->method->name) && !empty($recpt->method->name) ? $recpt->method->name : '');
                $theCollection[$row][14] = (isset($recpt->received->employee->full_name) && !empty($recpt->received->employee->full_name) ? $recpt->received->employee->full_name : (isset($recpt->received->name) ? $recpt->received->name : ''));

                $row += 1;
            endforeach;
        endif;

        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;
        $theCollection[$row][0] = '';
        $row += 1;

        $theCollection[$row][0] = '';
        $theCollection[$row][1] = '';
        $theCollection[$row][2] = '';
        $theCollection[$row][3] = 'Course Fees Only';
        $theCollection[$row][4] = '';
        $theCollection[$row][5] = '';
        $theCollection[$row][6] = 'Others Fees';
        $row += 1;

        $theCollection[$row][0] = 'Total';
        $theCollection[$row][1] = $overAllTotal;
        $theCollection[$row][2] = '';
        $theCollection[$row][3] = 'Total';
        $theCollection[$row][4] = $coursesTotal;
        $theCollection[$row][5] = '';
        $theCollection[$row][6] = 'Total';
        $theCollection[$row][7] = $othersTotal;
        $theCollection[$row][8] = '';
        $row += 1;

        if(!empty($overall)):
            foreach($overall as $method => $detail):
                $theCollection[$row][0] = $detail['name'];
                $theCollection[$row][1] = (isset($detail['amount']) && $detail['amount'] > 0 ? $detail['amount'] : 0);
                $theCollection[$row][2] = '';
                $theCollection[$row][3] = $courses[$method]['name'];
                $theCollection[$row][4] = (isset($courses[$method]['amount']) && $courses[$method]['amount'] > 0 ? $courses[$method]['amount'] : 0);
                $theCollection[$row][5] = '';
                $theCollection[$row][6] = $others[$method]['name'];
                $theCollection[$row][7] = (isset($others[$method]['amount']) && $others[$method]['amount'] > 0 ? $others[$method]['amount'] : 0);
                $theCollection[$row][8] = '';

                $row += 1;
            endforeach;
        endif;


        $fileName = 'Collection_report_'.date('Y_m_d', strtotime($from_date)).'_to_'.date('Y_m_d', strtotime($to_date)).'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $fileName);
    }
}
