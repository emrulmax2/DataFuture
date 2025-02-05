<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccTransaction;
use App\Models\SlcMoneyReceipt;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayCollectionExport;
use App\Models\AccTransactionTag;

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

            $connect = 0;
            foreach($slc_money_receipt_ids as $receipt):
                $theMoneyReceipt = SlcMoneyReceipt::with('student')->find($receipt);
                $registration_no = (isset($theMoneyReceipt->student->registration_no) && !empty($theMoneyReceipt->student->registration_no) ? $theMoneyReceipt->student->registration_no : '');
                $tagCount = AccTransactionTag::where('acc_transaction_id', $acc_transaction_id)->where('registration_no', $registration_no)->get()->count();
                if($tagCount == 0):
                    AccTransactionTag::create([
                        'acc_transaction_id' => $acc_transaction_id,
                        'registration_no' => $registration_no
                    ]);
                endif;
                $slcMoneyReceipt = SlcMoneyReceipt::where('id', $receipt)->update(['acc_transaction_id' => $acc_transaction_id]);

                $connect += 1;
            endforeach;

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
}
