<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccTransaction;
use App\Models\SlcMoneyReceipt;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayCollectionExport;
use App\Models\Option;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Number;

class ConnectTransactionController extends Controller
{
    
    public function searchTransactions(Request $request){
        $SearchVal = (isset($request->SearchVal) && !empty($request->SearchVal) ? trim($request->SearchVal) : '');
        $html = '';
        $Query = AccTransaction::where('transaction_code', 'LIKE', '%'.$SearchVal.'%')->orderBy('transaction_date_2', 'DESC')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="'.route('reports.accounts.transaction.connection', $qr->id).'" class="dropdown-item">'.$qr->transaction_code.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" class="dropdown-item">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function transactionConnection($transaction_id){
        $transaction = AccTransaction::find($transaction_id);
        $transDate = (!empty($transaction->transaction_date_2) ? date('Y-m-d', strtotime($transaction->transaction_date_2)) : '');
        $moneyReceipts = SlcMoneyReceipt::where('payment_date', $transDate)->where(function($q) use($transaction_id){
                            $q->where('acc_transaction_id', $transaction_id)->orWhereNull('acc_transaction_id');
                        })->orderBy('id', 'ASC')->get();
        return view('pages.reports.accounts.connection', [
            'title' => 'Site Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Reports', 'href' => route('reports.accounts')],
                ['label' => 'Transaction Connections', 'href' => 'javascript:void(0);']
            ],
            'transaction' => $transaction,
            'moneyReceipt' => $moneyReceipts
        ]);
    }

    public function store(Request $request){
        $acc_transaction_id = $request->acc_transaction_id;
        $slc_money_receipt_ids = (isset($request->slc_money_receipt_ids) && !empty($request->slc_money_receipt_ids) ? $request->slc_money_receipt_ids : []);
        if($acc_transaction_id > 0 && !empty($slc_money_receipt_ids) && count($slc_money_receipt_ids) > 0):
            $transaction = AccTransaction::find($acc_transaction_id);
            $code = $transaction->transaction_code;
            $taged_students = (isset($transaction->taged_students) && !empty($transaction->taged_students) ? explode(',', $transaction->taged_students) : []);
            $has_receipts = 0;

            $connect = 0;
            $reg_nos = [];
            foreach($slc_money_receipt_ids as $receipt):
                $theMoneyReceipt = SlcMoneyReceipt::with('student')->find($receipt);
                if(isset($theMoneyReceipt->student->registration_no) && !empty($theMoneyReceipt->student->registration_no)):
                    $reg_nos[] = $theMoneyReceipt->student->registration_no;
                endif;
                
                $slcMoneyReceipt = SlcMoneyReceipt::where('id', $receipt)->update(['acc_transaction_id' => $acc_transaction_id]);

                $connect += 1;
            endforeach;
            if(!empty($reg_nos)):
                $taged_students = array_merge($taged_students, $reg_nos);
                $has_receipts = 1;
            endif;
            AccTransaction::where('id', $acc_transaction_id)->update(['taged_students' => implode(',', $taged_students), 'has_receipts' => $has_receipts]);

            return response()->json(['msg' => $connect.' Money Receipts successfully connected to '.$code.' transaction.'], 200);
        else:
            return response()->json(['msg' => 'Transaction ID or Money receipts not foud. Please validate and submit again.'], 422);
        endif;
    }

    public function exportList($transaction_id){
        $transaction = AccTransaction::find($transaction_id);
        $code = $transaction->transaction_code;

        $transDate = (!empty($transaction->transaction_date_2) ? date('Y-m-d', strtotime($transaction->transaction_date_2)) : '');
        $moneyReceipts = SlcMoneyReceipt::where('payment_date', $transDate)->where(function($q) use($transaction_id){
                            $q->where('acc_transaction_id', $transaction_id)->orWhereNull('acc_transaction_id');
                        })->orderBy('id', 'ASC')->get();

        $theCollection[1][] = 'Date';
        $theCollection[1][] = 'Invoice No';
        $theCollection[1][] = 'Student ID';
        $theCollection[1][] = 'SSN';
        $theCollection[1][] = 'Name';
        $theCollection[1][] = 'Payment Type';
        $theCollection[1][] = 'Amount';
        $theCollection[1][] = 'Indicator';

        $row = 2;
        if($moneyReceipts->count() > 0):
            foreach($moneyReceipts as $rec):
                $theCollection[$row][] = (isset($rec->payment_date) && !empty($rec->payment_date) ? date('d-m-Y', strtotime($rec->payment_date)) : '');
                $theCollection[$row][] = (isset($rec->invoice_no) && !empty($rec->invoice_no) ? $rec->invoice_no : '');
                $theCollection[$row][] = (isset($rec->student->registration_no) && !empty($rec->student->registration_no) ? $rec->student->registration_no : '');
                $theCollection[$row][] = (isset($rec->student->ssn_no) && !empty($rec->student->ssn_no) ? $rec->student->ssn_no : '');
                $theCollection[$row][] = (isset($rec->student->full_name) && !empty($rec->student->full_name) ? $rec->student->full_name : '');
                $theCollection[$row][] = $rec->payment_type;
                $theCollection[$row][] = number_format($rec->amount, 2);
                $theCollection[$row][] = (isset($rec->agreement->is_self_funded) && $rec->agreement->is_self_funded == 1 ? 'Yes' : '');

                $row++;
            endforeach;
        endif;

        $fileName = $code.'_Money_Receipts.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $fileName);
    }

    public function printList($transaction_id){
        $transaction = AccTransaction::find($transaction_id);
        $code = $transaction->transaction_code;

        $transDate = (!empty($transaction->transaction_date_2) ? date('Y-m-d', strtotime($transaction->transaction_date_2)) : '');
        $moneyReceipts = SlcMoneyReceipt::where('payment_date', $transDate)->where(function($q) use($transaction_id){
                            $q->where('acc_transaction_id', $transaction_id)->orWhereNull('acc_transaction_id');
                        })->orderBy('id', 'ASC')->get();
        $courseFees = $moneyReceipts->filter(function($moneyReceipts) {
            return $moneyReceipts->payment_type == 'Course Fee';
        });
        $refunds = $moneyReceipts->filter(function($moneyReceipts) {
            return $moneyReceipts->payment_type == 'Refund';
        });

        $user = User::find(auth()->user()->id);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = $code.' Money Receipts';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                .headerTable tr td{vertical-align: top; padding: 0; line-height: 13px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.reportTitle{font-size: 16px; line-height: 16px; font-weight: bold;}

                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-20{margin-bottom: 20px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
                                .table.summaryTable tr:first-child th{padding-bottom: 7px}
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="2" class="reportTitle">'.$report_title.'</td>';
                            $PDFHTML .= '<td rowspan="3" class="text-right"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Transaction</td>';
                            $PDFHTML .= '<td>'.$code.'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Cereated By</td>';
                            $PDFHTML .= '<td>';
                                $PDFHTML .= (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
                                $PDFHTML .= '<br/>'.date('jS M, Y').' at '.date('h:i A');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="table table-bordered table-sm summaryTable mb-20">';
                    $PDFHTML .= '<tbody>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th class="text-left">Transaction of '.(!empty($transaction->transaction_date_2) ? '('.date('d-m-Y', strtotime($transaction->transaction_date_2)).')' : '').'</th>';
                            $PDFHTML .= '<th class="text-left">Amount</th>';
                            $PDFHTML .= '<th class="text-left">Course Fees Received</th>';
                            $PDFHTML .= '<th class="text-left">Refund</th>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th class="text-left">'.$transaction->transaction_code.'</th>';
                            $PDFHTML .= '<th class="text-left">'.Number::currency($transaction->transaction_amount, in: 'GBP').'</th>';
                            $PDFHTML .= '<th class="text-left">'.Number::currency($courseFees->sum('amount'), in: 'GBP').'</th>';
                            $PDFHTML .= '<th class="text-left">'.Number::currency($refunds->sum('amount'), in: 'GBP').'</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</tbody>';
                $PDFHTML .= '</table>';
                $PDFHTML .= '<table class="table table-bordered table-sm attenRateReportTable">';
                    $PDFHTML .= '<thead>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th>Date</th>';
                            $PDFHTML .= '<th>Invoice No</th>';
                            $PDFHTML .= '<th>Student ID</th>';
                            $PDFHTML .= '<th>SSN</th>';
                            $PDFHTML .= '<th>Name</th>';
                            $PDFHTML .= '<th>Payment Type</th>';
                            $PDFHTML .= '<th>Amount</th>';
                            $PDFHTML .= '<th>Indicator</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</thead>';
                    $PDFHTML .= '<tbody>';
                    if($moneyReceipts->count() > 0):
                        foreach($moneyReceipts as $rec):
                            $amount = ($rec->payment_type == 'Refund' ? ($rec->amount * -1) : $rec->amount);
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td>'.(isset($rec->payment_date) && !empty($rec->payment_date) ? date('d-m-Y', strtotime($rec->payment_date)) : '').'</td>';
                                $PDFHTML .= '<td>'.(isset($rec->invoice_no) && !empty($rec->invoice_no) ? $rec->invoice_no : '').'</td>';
                                $PDFHTML .= '<td>'.(isset($rec->student->registration_no) && !empty($rec->student->registration_no) ? $rec->student->registration_no : '').'</td>';
                                $PDFHTML .= '<td>'.(isset($rec->student->ssn_no) && !empty($rec->student->ssn_no) ? $rec->student->ssn_no : '').'</td>';
                                $PDFHTML .= '<td>'.(isset($rec->student->full_name) && !empty($rec->student->full_name) ? $rec->student->full_name : '').'</td>';
                                $PDFHTML .= '<td>'.$rec->payment_type.'</td>';
                                $PDFHTML .= '<td style="'.($rec->payment_type == 'Refund' ? 'color: red;' : '').'">';
                                    $PDFHTML .= ($rec->payment_type == 'Refund' ? '(' : '').Number::currency($rec->amount, in: 'GBP').($rec->payment_type == 'Refund' ? ')' : '');
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td>'.(isset($rec->agreement->is_self_funded) && $rec->agreement->is_self_funded == 1 ? 'Yes' : '').'</td>';
                            $PDFHTML .= '</tr>';
                        endforeach;
                    endif;
                    $PDFHTML .= '</tbody>';
                $PDFHTML .= '</table>';

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
