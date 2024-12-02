<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccCsvUploadRequest;
use App\Models\AccAssetRegister;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccCsvFile;
use App\Models\AccCsvTransaction;
use App\Models\AccTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AccCsvTransactionController extends Controller
{
    public function index($bank, $id = 0){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if($id > 0):
            $file = AccCsvFile::find($id);
        else:
            $file = AccCsvFile::where('acc_bank_id', $bank)->get()->first();
        endif;
        return view('pages.accounts.storage.csv.index', [
            'title' => 'Accounts Storage - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Storage', 'href' => 'javascript:void(0);']
            ],
            //'banks' => AccBank::where('status', 1)->orderBy('bank_name', 'ASC')->get(),
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'bank' => AccBank::find($bank),
            'csv_file_id' => $id,
            'csv_file' => $file,
            'csv_files' => AccCsvFile::where('acc_bank_id', $bank)->orderBy('id', 'DESC')->get(),
            'csv_transactions' => AccCsvTransaction::where('acc_csv_file_id', $file->id)->orderBy('id', 'ASC')->get(),
            'inCategories' => $this->catTreeInc(),
            'outCategories' => $this->catTreeExp(),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count(),
        ]);
    }
    
    public function csvStore(AccCsvUploadRequest $request){
        $acc_bank_id = $request->acc_bank_id;
        if($request->hasFile('csv_doc')):
            $csv_doc = $request->file('csv_doc');
            $csvFileName = str_replace(' ', '_', $csv_doc->getClientOriginalName());
            $csvTmpPath = $csv_doc->getPathname();

            $existFiles = AccCsvFile::where('name', $csvFileName)->get()->count();
            if($existFiles > 0):
                Session::flash('csv_error', '<strong>'.$csvFileName.'</strong> file aready exist in the system.'); 
                return redirect('/accounts/storage/transactions/'.$acc_bank_id);
            else:
                //$csvData = array_map('str_getcsv', file($csvTmpPath));
                $csvData = [];
                $theCSVFile = fopen($csvTmpPath, 'r');
                while (($line = fgetcsv($theCSVFile)) !== FALSE) {
                    $csvData[] = $line;
                }
                fclose($theCSVFile);
                //dd($csvData);
                if(!empty($csvData) && count($csvData) > 0):
                    $data = [];
                    $data['acc_bank_id'] = $acc_bank_id;
                    $data['name'] = $csvFileName;
                    $data['created_by'] = auth()->user()->id;
                    $accCsvFile = AccCsvFile::create($data);

                    if($accCsvFile->id):
                        $i = 1;
                        foreach($csvData as $row):
                            if($i > 1):
                                $transaction_type = (isset($row[3]) && $row[3] >= 0 ? 0 : 1);
                                $flow = $transaction_type;
                                $trans_amount =  (isset($row[3]) && !empty($row[3]) ? str_replace('-', '', $row[3]) : '0.00');
                                $data = [];
                                $data['acc_csv_file_id'] = $accCsvFile->id;
                                $data['trans_date'] = (isset($row[0]) && !empty($row[0]) ? date('Y-m-d', strtotime($row[0])) : null);
                                $data['description'] = (isset($row[2]) && !empty($row[2]) ? trim(str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array(' ', ' ', ' ', ' ', "\\'", '\\"', ' '), $row[2])) : null);
                                $data['amount'] = $trans_amount;
                                $data['transaction_type'] = $transaction_type;
                                $data['flow'] = $flow;
                                $data['created_by'] = auth()->user()->id;

                                AccCsvTransaction::create($data);
                            endif;
                            $i++;
                        endforeach;
                        return redirect('accounts/csv/transactions/'.$acc_bank_id.'/'.$accCsvFile->id);
                    else:
                        Session::flash('csv_error', 'Something went wrong. Can not read the <strong>'.$csvFileName.'</strong> file.'); 
                        return redirect('/accounts/storage/transactions/'.$acc_bank_id);
                    endif;
                else:
                    Session::flash('csv_error', '<strong>'.$csvFileName.'</strong> does not have any transactions. Please upload a valid file.'); 
                    return redirect('/accounts/storage/transactions/'.$acc_bank_id);
                endif;
            endif;
        else:
            Session::flash('csv_error', '<strong>Oops!</strong> Something went wrong. File does not found. Please upload a valid file.'); 
            return redirect('/accounts/storage/transactions/'.$acc_bank_id);
        endif;
    }


    public function catTreeInc($id = 0, $type = 0){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->orderBy('category_name', 'ASC')->get();

        if($categories):
            foreach ($categories as $cat):
                $categs[$cat['id']]['category_name'] = str_repeat('|&nbsp;&nbsp;&nbsp;', $level-1) . '|__'. $cat['category_name'];
                $categs[$cat['id']]['id'] = $cat['id'];
                $categs[$cat['id']]['status'] = $cat['status'];
                $categs[$cat['id']]['disabled'] = (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 1 : 0);
    
                $this->catTreeInc($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }

    public function catTreeExp($id = 0, $type = 1){
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->orderBy('category_name', 'ASC')->get();

        if($categories):
            foreach ($categories as $cat):
                $categs[$cat['id']]['category_name'] = str_repeat('|&nbsp;&nbsp;&nbsp;', $level-1) . '|__'. $cat['category_name'];
                $categs[$cat['id']]['id'] = $cat['id'];
                $categs[$cat['id']]['status'] = $cat['status'];
                $categs[$cat['id']]['disabled'] = (isset($cat->activechildrens) && $cat->activechildrens->count() > 0 ? 1 : 0);
    
                $this->catTreeExp($cat['id'], $type);
            endforeach;
        endif;

        $level --;
        return $categs;
    }

    public function csvUpdate(Request $request){
        $fileid = $request->acc_csv_file_id;
        $csvFile = AccCsvFile::find($fileid);
        $csvFileStorage = $csvFile->acc_bank_id;

        $transid = $request->acc_csv_transaction_id;
        $csvTrans = AccCsvTransaction::find($transid);

        $expense = (isset($request->expense) && $request->expense > 0 ? $request->expense : 0);
        $income = (isset($request->income) && $request->income > 0 ? $request->income : 0);

        $flow = (!empty($expense) && $expense > 0 ? 1 : 0);
        $transaction_amount = ($flow == 1 ? $expense : $income);

        $transaction_date = (isset($request->transdate) && !empty($request->transdate) ? date('Y-m-d', strtotime($request->transdate)) : date('Y-m-d'));
        $invoice_no = (isset($request->invoiceno) && !empty($request->invoiceno) ? $request->invoiceno : null);
        $invoice_date = (isset($request->invoicedate) && !empty($request->invoicedate) ? date('Y-m-d', strtotime($request->invoicedate)) : null);
        $detail = (isset($request->detail) && !empty($request->detail) ? $request->detail : null);
        $description = (isset($request->description) && !empty($request->description) ? $request->description : null);
        $acc_category_id_in = (isset($request->inccategory) && $request->inccategory > 0 ? $request->inccategory : null);
        $acc_category_id_out = (isset($request->expcategory) && $request->expcategory > 0 ? $request->expcategory : null);
        $transfer_bank_id = (isset($request->transstorage) && $request->transstorage > 0 ? $request->transstorage : null);
        $audit_status = (isset($request->auditstatus) && $request->auditstatus > 0 ? $request->auditstatus : 0);
        $trans_type = (isset($request->transactiontype) && $request->transactiontype > 0 ? $request->transactiontype : 0);

        $lastRow = AccTransaction::orderBy('id', 'DESC')->get()->first();
        $transaction_code = (isset($lastRow->transaction_code)) ? str_replace('TC', '', $lastRow->transaction_code) : '00000';
        $transaction_code = 'TC'.($transaction_code + 1);

        $transfer_type = ($csvTrans->transactiontype > 0 ? 1 : 0);

        $data = [];
        $data['transaction_code'] = $transaction_code;
        $data['transaction_date'] = strtotime($transaction_date);
        $data['transaction_date_2'] = $transaction_date;
        $data['invoice_no'] = $invoice_no;
        $data['invoice_date'] = $invoice_date;
        $data['acc_category_id'] = ($trans_type == 1 ? $acc_category_id_out : ($trans_type == 0 ? $acc_category_id_in : null));
        $data['acc_bank_id'] = $csvFile->acc_bank_id;
        $data['transaction_type'] = $trans_type;
        $data['flow'] = $flow;
        $data['detail'] = $detail;
        $data['description'] = $description;
        $data['transaction_amount'] = $transaction_amount;
        $data['audit_status'] = $audit_status;
        if($trans_type == 2):
            $data['transfer_bank_id'] = $transfer_bank_id;
        endif;
        $data['created_by'] = auth()->user()->id;

        $transaction = AccTransaction::create($data);
        $docURL = null;
        $documentName = null;
        if($request->hasFile('document')):
            $document = $request->file('document');
            $documentName = $transaction_code.'.'.$document->getClientOriginalExtension();
            $path = $document->storeAs('public/transactions', $documentName, 's3');
            //$docURL = Storage::disk('s3')->url($path);

            $userUpdate = AccTransaction::where('id', $transaction->id)->update([
                'transaction_doc_name' => $documentName,
                //'transaction_doc_url' => $docURL,
            ]);
        endif;

        $redirect = 'NONE';
        if($transaction->id):
            if($trans_type == 2):
                $lastRow = AccTransaction::orderBy('id', 'DESC')->get()->first();
                $transaction_code = (isset($lastRow->transaction_code)) ? str_replace('TC', '', $lastRow->transaction_code) : '00000';
                $transaction_code = 'TC'.($transaction_code + 1);

                unset($data['transaction_code']);
                $data['transaction_code'] = $transaction_code;
                unset($data['acc_bank_id']);
                $data['acc_bank_id'] = $transfer_bank_id;
                $data['transfer_id'] = $transaction->id;
                unset($data['flow']);
                $data['flow'] = ($flow == 1 ? 0 : 1);
                unset($data['transfer_bank_id']);
                $data['transfer_bank_id'] = $csvFile->acc_bank_id;
                unset($data['transaction_amount']);
                $data['transaction_amount'] = $transaction_amount;
                $data['transaction_doc_name'] = $documentName;
                //$data['transaction_doc_url'] = $docURL;

                $trnfTrans = AccTransaction::create($data);
            endif;

            AccCsvTransaction::where('id', $transid)->forceDelete();
            $transactionCount = AccCsvTransaction::where('acc_csv_file_id', $fileid)->get()->count();
            if($transactionCount == 0):
                AccCsvFile::where('id', $fileid)->forceDelete();
                $redirect = route('accounts.storage', $csvFileStorage);
            endif;
        endif;

        if($transaction->id && isset($request->isassets) && $request->isassets == 1):
            AccAssetRegister::create([
                'acc_transaction_id' => $transaction->id,
                'description' => null,
                'active' => 1,
                'created_by' => auth()->user()->id,
            ]);
        endif;

        return response()->json(['msg' => 'CSV transaction successfully inserted to transaction.', 'red' => $redirect], 200);
    }

}
