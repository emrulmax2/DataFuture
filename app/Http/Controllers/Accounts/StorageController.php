<?php

namespace App\Http\Controllers\Accounts;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorageRequest;
use App\Models\AccAssetRegister;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccCsvFile;
use App\Models\AccCsvTransaction;
use App\Models\AccTransaction;
use App\Models\SlcMoneyReceipt;
use Faker\Core\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StorageController extends Controller
{
    public function index($bank){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $csvfiles = AccCsvFile::where('acc_bank_id', $bank)->pluck('id')->unique()->toArray();
        return view('pages.accounts.storage.index', [
            'title' => 'Accounts Storage - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Storage', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'bank' => AccBank::find($bank),
            'in_categories' => $this->catTreeInc(0, 0),
            'out_categories' => $this->catTreeExp(0, 1),
            'csf_trans' => (!empty($csvfiles) ? AccCsvTransaction::whereIn('acc_csv_file_id', $csvfiles)->get()->count() : 0),
            'csv_file' => AccCsvFile::where('acc_bank_id', $bank)->get()->first(),
            'is_auditor' => (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count()
        ]);
    }

    public function store(StorageRequest $request){
        $is_auditor = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);
        $storage_id = $request->storage_id;
        $transaction_date = (isset($request->transaction_date) && !empty($request->transaction_date) ? date('Y-m-d', strtotime($request->transaction_date)) : date('Y-m-d'));
        $detail = (isset($request->detail) && !empty($request->detail) ? $request->detail : null);
        $expense = (isset($request->expense) && $request->expense != '' ? $request->expense : '');
        $income = (isset($request->income) && $request->income != '' ? $request->income : '');
        
        $trans_type = (isset($request->trans_type) && $request->trans_type > 0 ? $request->trans_type : 0);
        $acc_category_id_in = (isset($request->acc_category_id_in) && $request->acc_category_id_in > 0 ? $request->acc_category_id_in : null);
        $acc_category_id_out = (isset($request->acc_category_id_out) && $request->acc_category_id_out > 0 ? $request->acc_category_id_out : null);
        $transfer_bank_id = (isset($request->acc_bank_id) && $request->acc_bank_id > 0 ? $request->acc_bank_id : null);
        $invoice_no = (isset($request->invoice_no) && !empty($request->invoice_no) ? $request->invoice_no : null);
        $invoice_date = (isset($request->invoice_date) && !empty($request->invoice_date) ? date('Y-m-d', strtotime($request->invoice_date)) : null);
        $description = (isset($request->description) && !empty($request->description) ? $request->description : null);
        $audit_status = (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : 0);

        $lastRow = AccTransaction::orderBy('id', 'DESC')->get()->first();
        $transaction_code = (isset($lastRow->transaction_code)) ? str_replace('TC', '', $lastRow->transaction_code) : '00000';
        $transaction_code = 'TC'.($transaction_code + 1);

        $flow = (!empty($expense) && $expense > 0 ? 1 : 0);
        $transaction_amount = ($flow == 1 ? $expense : $income);

        $data = [];
        $data['transaction_code'] = $transaction_code;
        $data['transaction_date'] = strtotime($transaction_date);
        $data['transaction_date_2'] = $transaction_date;
        $data['invoice_no'] = $invoice_no;
        $data['invoice_date'] = $invoice_date;
        $data['acc_category_id'] = ($trans_type == 1 ? $acc_category_id_out : ($trans_type == 0 ? $acc_category_id_in : null));
        $data['acc_bank_id'] = $storage_id;
        $data['transaction_type'] = $trans_type;
        $data['flow'] = $flow;
        $data['detail'] = $detail;
        $data['description'] = $description;
        $data['transaction_amount'] = $transaction_amount;
        $data['audit_status'] = ($is_auditor ? 1 : $audit_status);
        if($trans_type == 2):
            $data['transfer_bank_id'] = ($transfer_bank_id > 0 ? $transfer_bank_id : null);
        endif;
        $data['created_by'] = auth()->user()->id;

        $transaction = AccTransaction::create($data);
        $documentName = null;
        $docURL = null;
        if($transaction->id && $request->hasFile('document')):
            $document = $request->file('document');
            $documentName = $transaction_code.'.' . $document->getClientOriginalExtension();
            $path = $document->storeAs('public/transactions', $documentName, 's3');
            //$docURL = Storage::disk('s3')->url($path);

            $userUpdate = AccTransaction::where('id', $transaction->id)->update([
                'transaction_doc_name' => $documentName,
                //'transaction_doc_url' => $docURL,
            ]);
        endif;

        if($transaction->id && $trans_type == 2):
            $lastRow = AccTransaction::orderBy('id', 'DESC')->get()->first();
            $transaction_code = (isset($lastRow->transaction_code)) ? str_replace('TC', '', $lastRow->transaction_code) : '00000';
            $transaction_code = 'TC'.($transaction_code + 1);

            unset($data['transaction_code']);
            $data['transaction_code'] = $transaction_code;
            unset($data['acc_bank_id']);
            $data['acc_bank_id'] = $transfer_bank_id;
            unset($data['flow']);
            $data['flow'] = ($flow == 1 ? 0 : 1);
            $data['transfer_id'] = $transaction->id;
            unset($data['transfer_bank_id']);
            $data['transfer_bank_id'] = $storage_id;
            unset($data['transaction_amount']);
            $data['transaction_amount'] = $transaction_amount;
            $data['transaction_doc_name'] = $documentName;
            //$data['transaction_doc_url'] = $docURL;

            $trnfTrans = AccTransaction::create($data);
        endif;

        if($transaction->id && isset($request->is_assets) && $request->is_assets == 1):
            AccAssetRegister::create([
                'acc_transaction_id' => $transaction->id,
                'description' => null,
                'active' => 1,
                'created_by' => auth()->user()->id,
            ]);
        endif;

        return response()->json(['msg' => 'Storage transaction successfully inserted.'], 200);
    }

    public function list(Request $request){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        
        $canEdit = ((auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3])) ? 1 : 0);
        
        $queryStr = (isset($request->queryStr) && !empty($request->queryStr) ? $request->queryStr : '');
        $storage = (isset($request->storage) && $request->storage > 0 ? $request->storage : 0);
        $bank = AccBank::find($storage);
        $openingDate = (isset($bank->opening_date) && !empty($bank->opening_date) ? date('Y-m-d', strtotime($bank->opening_date)) : '');
        $openingBalance = (isset($bank->opening_balance) && $bank->opening_balance > 0 ? $bank->opening_balance : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AccTransaction::with('category', 'bank', 'assets', 'tbank', 'requisition')->orderByRaw(implode(',', $sorts))->where('acc_bank_id', $storage)->where('parent', 0)->whereIn('audit_status', $audit_status);
        if(!empty($openingDate)):
            $query->where('transaction_date_2', '>=', $openingDate);
        endif;
        if(!empty($queryStr)):
            $categoryIds = AccCategory::where('category_name', 'LIKE', '%'.$queryStr.'%')->whereIn('audit_status', $audit_status)->pluck('id')->unique()->toArray();
            $query->where(function($q) use($queryStr, $categoryIds){
                $q->orWhere('detail','LIKE','%'.$queryStr.'%')
                    ->orWhere('description','LIKE','%'.$queryStr.'%')
                    ->orWhere('transaction_date_2','LIKE','%'.$queryStr.'%')
                    ->orWhere('transaction_code','LIKE','%'.$queryStr.'%')
                    ->orWhere('transaction_amount','LIKE','%'.$queryStr.'%')
                    ->orWhere('invoice_no','LIKE','%'.$queryStr.'%')
                    ->orWhere('taged_students','LIKE','%'.$queryStr.'%');
                if(!empty($categoryIds)):
                    $q->orWhereIn('acc_category_id', $categoryIds);
                endif;
            });
        endif;
        /*if($status == 2):
            $query->onlyTrashed();
        endif;*/


        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $transaction_type = ($list->transaction_type > 0 ? $list->transaction_type : 0);
                $flow = (isset($list->flow) && $list->flow != '' ? $list->flow : 0);
                $transaction_amount = (isset($list->transaction_amount) && $list->transaction_amount > 0 ? $list->transaction_amount : 0);
                
                $balance = (empty($queryStr) ? $this->getBalance($storage, $list->id,$audit_status) : 0);

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'transaction_code' => $list->transaction_code,
                    'connected' => ($list->has_receipts > 0 ? $list->has_receipts : (isset($list->receipts) && $list->receipts->count() > 0 ? 1 : 0)),
                    'transaction_date_2' => (!empty($list->transaction_date_2) ? date('jS F, Y', strtotime($list->transaction_date_2)) : ''),
                    'invoice_no' => (!empty($list->invoice_no) ? $list->invoice_no : ''),
                    'detail' => (!empty($list->detail) ? $list->detail : ''),
                    'description' => (!empty($list->description) ? $list->description : ''),
                    'acc_category_id' => ($list->acc_category_id > 0 ? $list->acc_category_id : ''),
                    'category_name' => (isset($list->category->category_name) && !empty($list->category->category_name) ? $list->category->category_name : ''),
                    'acc_bank_id' => ($list->acc_bank_id > 0 ? $list->acc_bank_id : ''),
                    'bank_name' => (isset($list->bank->bank_name) && !empty($list->bank->bank_name) ? $list->bank->bank_name : ''),
                    'audit_status' => ($list->audit_status > 0 ? $list->audit_status : '0'),
                    'transaction_type' => $transaction_type,
                    'flow' => $flow,
                    'transfer_bank_id' => ($list->transfer_bank_id > 0 ? $list->transfer_bank_id : ''),
                    'transfer_bank_name' => (isset($list->tbank->bank_name) && !empty($list->tbank->bank_name) ? $list->tbank->bank_name : ''),
                    'transaction_amount' => ($transaction_amount > 0 ? '£'.number_format($transaction_amount, 2) : ''),
                    'balance' => (empty($queryStr) ? ($balance >= 0 ? '£'.$balance : '-£'.str_replace('-', '', $balance)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'doc_url' => (isset($list->transaction_doc_name) && !empty($list->transaction_doc_name) ? $list->transaction_doc_name : ''),
                    'has_assets' => (isset($list->assets->id) && $list->assets->id > 0 ? 1 : 0),
                    'has_payments' => (isset($list->has_payments) && $list->has_payments > 0 ? 1 : 0),
                    'can_eidt' => $canEdit,
                    'has_requisition' => (isset($list->requisition->id) && $list->requisition->id > 0 ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function getBalance($storage, $transaction_id , $audit_status = []) {
        $bank = AccBank::find($storage);
        $openingDate = (isset($bank->opening_date) && !empty($bank->opening_date) ? date('Y-m-d', strtotime($bank->opening_date)) : '');
        $openingBalance = (isset($bank->opening_balance) && $bank->opening_balance > 0 ? $bank->opening_balance : 0);

        $inQuery = AccTransaction::where('id', '<=', $transaction_id)->where('acc_bank_id', $storage)->where('flow', 0)->where('parent', 0)->whereIn('audit_status', $audit_status)->orderBy('id', 'DESC');
        if(!empty($openingDate)): $inQuery->where('transaction_date_2', '>=', $openingDate); endif;
        $incomes = (float) $inQuery->get()->sum('transaction_amount');

        $exQuery = AccTransaction::where('id', '<=', $transaction_id)->where('acc_bank_id', $storage)->where('flow', 1)->where('parent', 0)->whereIn('audit_status', $audit_status)->orderBy('id', 'DESC');
        if(!empty($openingDate)): $exQuery->where('transaction_date_2', '>=', $openingDate); endif;
        $expenses = (float) $exQuery->get()->sum('transaction_amount');

        $balance = ((float) $openingBalance + (float) $incomes) - (float) $expenses;
        return number_format($balance, 2);
    }

    public function catTreeInc($id = 0, $type = 0){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();

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
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        static $categs = array ();
        static $level = 0;
        $level ++;

        $categories = AccCategory::where('trans_type', $type)->where('parent_id', $id)->where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();

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

    public function edit(Request $request){
        $transaction_id = $request->transaction_id;
        $transaction = AccTransaction::find($transaction_id);
        $transaction['transaction_date_2'] = (isset($transaction->transaction_date_2) && !empty($transaction->transaction_date_2) ? date('d-m-Y', strtotime($transaction->transaction_date_2)) : date('d-m-Y'));
        $transaction['invoice_date'] = (isset($transaction->invoice_date) && !empty($transaction->invoice_date) ? date('d-m-Y', strtotime($transaction->invoice_date)) : '');
        $transaction['has_assets'] = (isset($transaction->assets->id) && $transaction->assets->id > 0 ? 1 : 0);

        return response()->json(['res' => $transaction], 200);
    }

    public function update(StorageRequest $request){
        $is_auditor = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);
        $transaction_id = $request->transaction_id;
        $storage_id = $request->storage_id;
        $oleTransaction = AccTransaction::find($transaction_id);

        $expense = (isset($request->expense) && $request->expense > 0 ? $request->expense : '');
        $income = (isset($request->income) && $request->income > 0 ? $request->income : '');

        $transaction_date = (isset($request->transaction_date) && !empty($request->transaction_date) ? date('Y-m-d', strtotime($request->transaction_date)) : date('Y-m-d'));
        $detail = (isset($request->detail) && !empty($request->detail) ? $request->detail : null);
        $trans_type = (isset($request->trans_type) && $request->trans_type > 0 ? $request->trans_type : 0);
        $acc_category_id_in = (isset($request->acc_category_id_in) && $request->acc_category_id_in > 0 ? $request->acc_category_id_in : null);
        $acc_category_id_out = (isset($request->acc_category_id_out) && $request->acc_category_id_out > 0 ? $request->acc_category_id_out : null);
        $transfer_bank_id = (isset($request->acc_bank_id) && $request->acc_bank_id > 0 ? $request->acc_bank_id : null);
        $invoice_no = (isset($request->invoice_no) && !empty($request->invoice_no) ? $request->invoice_no : null);
        $invoice_date = (isset($request->invoice_date) && !empty($request->invoice_date) ? date('Y-m-d', strtotime($request->invoice_date)) : null);
        $description = (isset($request->description) && !empty($request->description) ? $request->description : null);
        $audit_status = (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : 0);

        $flow = (!empty($expense) && $expense > 0 ? 1 : 0);
        $transaction_amount = ($flow == 1 ? $expense : $income);

        $data = [];
        $data['transaction_date'] = strtotime($transaction_date);
        $data['transaction_date_2'] = $transaction_date;
        $data['invoice_no'] = $invoice_no;
        $data['invoice_date'] = $invoice_date;
        $data['acc_category_id'] = ($trans_type == 1 ? $acc_category_id_out : ($trans_type == 0 ? $acc_category_id_in : null));
        $data['acc_bank_id'] = $storage_id;
        $data['transaction_type'] = $trans_type;
        $data['flow'] = $flow;
        $data['detail'] = $detail;
        $data['description'] = $description;
        $data['transaction_amount'] = $transaction_amount;
        $data['audit_status'] = ($is_auditor ? 1 : $audit_status);
        if($trans_type == 2):
            $data['transfer_bank_id'] = ($transfer_bank_id > 0 ? $transfer_bank_id : null);
        endif;
        $data['updated_by'] = auth()->user()->id;

        $transaction = AccTransaction::where('id', $transaction_id)->update($data);
        $docURL = null;
        $documentName = null;
        if($request->hasFile('document')):
            if(isset($oleTransaction->transaction_doc_url) && !empty($oleTransaction->transaction_doc_url) && !empty($oleTransaction->transaction_doc_name)):
                Storage::disk('s3')->delete('public/transactions/'.$oleTransaction->transaction_doc_name);
            endif;

            $document = $request->file('document');
            $documentName = $oleTransaction->transaction_code.'.' . $document->getClientOriginalExtension();
            $path = $document->storeAs('public/transactions', $documentName, 's3');
            //$docURL = Storage::disk('s3')->url($path);

            $userUpdate = AccTransaction::where('id', $transaction_id)->update([
                'transaction_doc_name' => $documentName,
                //'transaction_doc_url' => $docURL,
            ]);
        endif;

        if($trans_type == 2):
            $lastRow = AccTransaction::orderBy('id', 'DESC')->get()->first();
            $transaction_code = (isset($lastRow->transaction_code)) ? str_replace('TC', '', $lastRow->transaction_code) : '00000';
            $transaction_code = 'TC'.($transaction_code + 1);

            unset($data['transaction_code']);
            $data['transaction_code'] = $transaction_code;
            unset($data['acc_bank_id']);
            $data['acc_bank_id'] = $transfer_bank_id;
            $data['transfer_id'] = $transaction_id;
            unset($data['flow']);
            $data['flow'] = ($flow == 1 ? 0 : 1);
            unset($data['transfer_bank_id']);
            $data['transfer_bank_id'] = $storage_id;
            unset($data['transaction_amount']);
            $data['transaction_amount'] = $transaction_amount;
            $data['transaction_doc_name'] = $documentName;
            //$data['transaction_doc_url'] = $docURL;

            $trnfTrans = AccTransaction::create($data);
        endif;

        if(isset($request->is_assets) && $request->is_assets == 1):
            $asset = AccAssetRegister::where('acc_transaction_id', $transaction_id)->withTrashed()->get()->first();
            
            if(isset($asset->id) && $asset->id > 0):
                if($asset->trashed()):
                    AccAssetRegister::where('id', $asset->id)->withTrashed()->restore();
                endif;
            else:
                AccAssetRegister::create([
                    'acc_transaction_id' => $transaction_id,
                    'description' => null,
                    'active' => 1,
                    'created_by' => auth()->user()->id,
                ]);
            endif;
        else:
            AccAssetRegister::where('acc_transaction_id', $transaction_id)->delete();
        endif;

        return response()->json(['msg' => 'Storage transaction successfully updated.'], 200);
    }

    public function destroy($id){
        $transaction = AccTransaction::find($id);
        $isIntraTransfer = (isset($transaction->transaction_type) && $transaction->transaction_type == 2 ? true : false);
        $isParentTransfer = ($isIntraTransfer && empty($transaction->transfer_id) ? true : false);
        $parentTransId = (!$isParentTransfer && !empty($transaction->transfer_id) ? $transaction->transfer_id : 0);

        AccTransaction::where('id', $id)->delete();
        if(!$isParentTransfer && $parentTransId > 0):
            AccTransaction::where('id', $parentTransId)->delete();
        else:
            AccTransaction::where('transfer_id', $id)->delete();
        endif;

        return response()->json(['msg' => 'Storage transaction successfully deleted.'], 200);
    }

    public function export($querystr, $storage_id){
        $is_auditor = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $storage = AccBank::find($storage_id);
        $openingDate = (isset($storage->opening_date) && !empty($storage->opening_date) ? date('Y-m-d', strtotime($storage->opening_date)) : '');
        $openingBalance = (isset($storage->opening_balance) && $storage->opening_balance > 0 ? $storage->opening_balance : 0);


        $theCollection = [];
        $theCollection[1][] = 'Date';
        $theCollection[1][] = 'Transaction Code';
        $theCollection[1][] = 'Invoice No';
        $theCollection[1][] = 'Invoice Date';
        $theCollection[1][] = 'Detail';
        if(!$is_auditor):
            $theCollection[1][] = 'Description';
        endif;
        $theCollection[1][] = 'Category';
        $theCollection[1][] = 'Withdrawl (£)';
        $theCollection[1][] = 'Deposit (£)';

        $query = AccTransaction::orderBy('transaction_date_2', 'DESC')->where('acc_bank_id', $storage_id)->where('parent', 0)->whereIn('audit_status', $audit_status);
        if(!empty($openingDate)):
            $query->where('transaction_date_2', '>=', $openingDate);
        endif;
        if(!empty($querystr)):
            $categoryIds = AccCategory::where('category_name', 'LIKE', '%'.$querystr.'%')->whereIn('audit_status', $audit_status)->pluck('id')->unique()->toArray();
            $query->where(function($q) use($querystr, $categoryIds){
                $q->orWhere('detail','LIKE','%'.$querystr.'%')
                    ->orWhere('description','LIKE','%'.$querystr.'%')
                    ->orWhere('transaction_date_2','LIKE','%'.$querystr.'%')
                    ->orWhere('transaction_code','LIKE','%'.$querystr.'%')
                    ->orWhere('transaction_amount','LIKE','%'.$querystr.'%')
                    ->orWhere('invoice_no','LIKE','%'.$querystr.'%');
                if(!empty($categoryIds)):
                    $q->orWhereIn('acc_category_id', $categoryIds);
                endif;
            });
        endif;
        $transactions = $query->get();

        $row = 2;
        if(!empty($transactions)):
            foreach($transactions as $trns):
                $trans_type = ($trns->transaction_type > 0 ? $trns->transaction_type : 0);
                $flow = ($trns->flow == 1 ? 1 : 0);
                $transaction_amount = (isset($trns->transaction_amount) && $trns->transaction_amount > 0 ? $trns->transaction_amount : 0);

                $category_html = '';
                if($trns->transfer_bank_id > 0 && $trns->transaction_type == 2):
                    if($flow == 0):
                        $category_html .= '-> ';
                    elseif($flow == 1):
                        $category_html .= '<- ';
                    endif;
                    $category_html .= (isset($trns->tbank->bank_name) ? $trns->tbank->bank_name : '');
                elseif($trns->acc_category_id > 0 && $trns->transaction_type != 2):
                    $category_html .= (isset($trns->category->category_name) ? $trns->category->category_name : '');
                endif;

                $theCollection[$row][] = date('d-m-Y', strtotime($trns->transaction_date_2));
                $theCollection[$row][] = $trns->transaction_code;
                $theCollection[$row][] = $trns->invoice_no;
                $theCollection[$row][] = (!empty($trns->invoice_date) ? date('d-m-Y', strtotime($trns->invoice_date)) : '');
                $theCollection[$row][] = $trns->detail;
                if(!$is_auditor):
                    $theCollection[$row][] = $trns->description;
                endif;
                $theCollection[$row][] = $category_html;
                $theCollection[$row][] = ($flow == 1 ? number_format($transaction_amount, 2, '.', '') : '');
                $theCollection[$row][] = ($flow != 1 ? number_format($transaction_amount, 2, '.', '') : '');

                $row++;
            endforeach;
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), str_replace(' ', '_', $storage->bank_name).'_transactions.xlsx');
    }

    public function updateDocumentUrl(){
        $ids = [];
        $transactions = AccTransaction::whereNotNull('transaction_doc_name')->whereNull('transaction_doc_url')->orderBy('id', 'DESC')->take(100)->get();
        if($transactions->count() > 0):
            foreach($transactions as $trns):
                if((isset($trns->transaction_doc_name) && !empty($trns->transaction_doc_name))):
                    if(Storage::disk('s3')->exists('public/transactions/'.$trns->transaction_doc_name)):
                        $data = [];
                        $data['transaction_doc_url'] = Storage::disk('s3')->url('public/transactions/'.$trns->transaction_doc_name);
                        AccTransaction::where('id', $trns->id)->update($data);
                    else:
                        $data = [];
                        $data['transaction_doc_name'] = null;
                        $data['transaction_doc_url'] = null;

                        AccTransaction::where('id', $trns->id)->update($data);
                    endif;
                endif;
            endforeach;

            $transactions = AccTransaction::whereNotNull('transaction_doc_name')->whereNull('transaction_doc_url')->orderBy('id', 'DESC')->get()->count();
            return '<a href="'.route('accounts.storage.update.doc.url').'">Reload '.$transactions.' more available</a>';
        else:
            return '<a href="'.route('accounts').'">Back To Dashboard</a>';
        endif;
    }

    public function documentDownloadUrl(Request $request){
        $trans = AccTransaction::find($request->row_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/transactions/'.$trans->transaction_doc_name, now()->addMinutes(15));
        return response()->json(['res' => $tmpURL], 200);
    }
}
