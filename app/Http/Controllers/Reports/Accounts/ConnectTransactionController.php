<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccTransaction;
use App\Models\SlcMoneyReceipt;
use Illuminate\Http\Request;

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
                $slcMoneyReceipt = SlcMoneyReceipt::where('id', $receipt)->update(['acc_transaction_id' => $acc_transaction_id]);
                $connect += 1;
            endforeach;

            return response()->json(['msg' => $connect.' Money Receipts successfully connected to '.$code.' transaction.'], 200);
        else:
            return response()->json(['msg' => 'Transaction ID or Money receipts not foud. Please validate and submit again.'], 422);
        endif;
    }
}
