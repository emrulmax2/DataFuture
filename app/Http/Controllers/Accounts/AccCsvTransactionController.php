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
use Carbon\Carbon;
use Illuminate\Http\File;
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
            'csv_transactions' => AccCsvTransaction::with('files')->where('acc_csv_file_id', $file->id)->orderBy('id', 'ASC')->get(),
            'inCategories' => $this->catTreeInc(),
            'outCategories' => $this->catTreeExp(),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count(),
        ]);
    }
    
    public function csvStore(AccCsvUploadRequest $request){
        $acc_bank_id = $request->acc_bank_id;
        $has_cto_receipts = (isset($request->has_cto_receipts) && $request->has_cto_receipts == 1 ? true : false);
        $files = $request->file('cto_receipts');

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
                    $data['has_cto_receipts'] = ($files ? 1 : 0);
                    $data['created_by'] = auth()->user()->id;
                    $accCsvFile = AccCsvFile::create($data);

                    if($accCsvFile->id):
                        $i = 1;
                        $usedFiles = [];
                        foreach($csvData as $row):
                            if($i > 1):
                                $has_receipt = false;
                                if($has_cto_receipts):
                                    $has_receipt = (isset($row['12']) && $row['12'] == 'Yes' ? true : false);
                                    $transaction_type = 1;
                                    $flow = $transaction_type;
                                    $trans_amount = (isset($row[3]) && !empty($row[3]) ? str_replace('-', '', $row[3]) : '0.00');
                                    $trans_date = (isset($row[0]) && !empty($row[0]) ? Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d') : null);
                                    $description = (isset($row[6]) && !empty($row[6]) ? trim(str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array(' ', ' ', ' ', ' ', "\\'", '\\"', ' '), $row[6])) : null);
                                else:
                                    $transaction_type = (isset($row[3]) && $row[3] >= 0 ? 0 : 1);
                                    $flow = $transaction_type;
                                    $trans_amount =  (isset($row[3]) && !empty($row[3]) ? str_replace('-', '', $row[3]) : '0.00');
                                    $trans_date = (isset($row[0]) && !empty($row[0]) ? date('Y-m-d', strtotime($row[0])) : null);
                                    $description = (isset($row[2]) && !empty($row[2]) ? trim(str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array(' ', ' ', ' ', ' ', "\\'", '\\"', ' '), $row[2])) : null);
                                endif;

                                $data = [];
                                $data['acc_csv_file_id'] = $accCsvFile->id;
                                $data['trans_date'] = $trans_date;
                                $data['description'] = $description;
                                $data['amount'] = $trans_amount;
                                $data['transaction_type'] = $transaction_type;
                                $data['flow'] = $flow;
                                $data['has_receipts'] = $has_receipt;
                                $data['created_by'] = auth()->user()->id;

                                $csvTrans = AccCsvTransaction::create($data);
                                if($csvTrans->id && $has_cto_receipts && (isset($row['12']) && $row['12'] == 'Yes')):
                                    $file = $this->matchReceiptFile($row, $files, $usedFiles);
                                    if($file):
                                        $documentName = $file->getClientOriginalName();
                                        $path = $file->storeAs('public/receipts', $documentName, 'local');
                                        $csvTrans->update(['cto_receipt_name' => $documentName]);
                                    else:
                                        $csvTrans->update(['cto_receipt_error' => 1]);
                                    endif;
                                endif;
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
        $has_receipts = (isset($csvTrans->has_receipts) && $csvTrans->has_receipts == 1 ? true : false);
        $cto_receipt_name = (isset($csvTrans->cto_receipt_name) && !empty($csvTrans->cto_receipt_name) ? $csvTrans->cto_receipt_name : false);

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
            ]);
        elseif(isset($csvFile->has_cto_receipts) && $csvFile->has_cto_receipts == 1 && $has_receipts && !empty($cto_receipt_name)):
            $localPath = 'public/receipts/'.$cto_receipt_name;
            if (Storage::disk('local')->exists($localPath)):
                $existingFile = new File(storage_path('app/' . $localPath));
                $newFileName = $transaction_code.'.'.$existingFile->getExtension();
                
                $fileContent = Storage::disk('local')->get($localPath);
                $s3Path = 'public/transactions/' . $newFileName;
                Storage::disk('s3')->put($s3Path, $fileContent);

                Storage::disk('local')->delete($localPath);

                $userUpdate = AccTransaction::where('id', $transaction->id)->update([
                    'transaction_doc_name' => $newFileName,
                ]);
            endif;
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

    public function matchReceiptFile(array $row, array $files, array &$usedFiles = []){
        $bestMatch = null;
        $bestScore = 0;

        $dateRaw     = $row[0] ?? null;
        $description = $row[6] ?? $row[2] ?? '';
        $amount      = (float) ($row[3] ?? 0);
        $cardLast4   = (string) ($row[7] ?? '');

        $normalizedDesc = $this->normalizeDescription($description);
        $date = $this->normalizeDate($dateRaw);

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            if (in_array($originalName, $usedFiles)) {
                continue;
            }

            $filename = $originalName; // keep original (case-sensitive not needed)
            $score = 0;

            // 1. Date match (VERY IMPORTANT)
            if ($date && str_contains($filename, $date)) {
                $score += 4;
            }

            // 2. Description match
            if (str_contains($filename, $normalizedDesc)) {
                $score += 3;
            }

            // 3. Amount match (handle £ and decimals)
            if (preg_match('/£\s*' . number_format($amount, 2, '.', '') . '/', $filename)) {
                $score += 4;
            }

            // fallback: match integer amount
            elseif (str_contains($filename, (string) (int) $amount)) {
                $score += 2;
            }

            // 4. Card match
            if ($cardLast4 && str_contains($filename, $cardLast4)) {
                $score += 1;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $file;
            }
        }

        if ($bestScore >= 5 && $bestMatch) {
            $usedFiles[] = $bestMatch->getClientOriginalName();
            return $bestMatch;
        }

        return null;
    }

    public function normalizeDescription($text){
        // Remove unwanted symbols but KEEP dot
        $text = preg_replace('/[^A-Za-z0-9\.]/', '', $text);

        return $text;
    }

    public function normalizeDate($date){
        try {
            return \Carbon\Carbon::createFromFormat('j/n/y', $date)->format('d-m-Y');
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('d-m-Y');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    public function matchByWords($desc, $filename){
        $words = explode(' ', strtolower($desc));
        $score = 0;

        foreach ($words as $word) {
            if (strlen($word) > 2 && str_contains($filename, $word)) {
                $score += 1;
            }
        }

        return $score;
    }


    public function csvUpdateBulk(Request $request){
        $updateRowCount = 0;
        $rows = $request->input('rows', []);

        $redirect = 'NONE';
        if(!empty($rows)){
            foreach ($rows as $index => $row) {
                $id = $row['acc_csv_transaction_id'];
                $id = $row['acc_csv_file_id'];

                $fileid = $row['acc_csv_file_id'];;
                $csvFile = AccCsvFile::find($fileid);
                $csvFileStorage = $csvFile->acc_bank_id;

                $transid = $row['acc_csv_transaction_id'];
                $csvTrans = AccCsvTransaction::find($transid);
                $has_receipts = (isset($csvTrans->has_receipts) && $csvTrans->has_receipts == 1 ? true : false);
                $cto_receipt_name = (isset($csvTrans->cto_receipt_name) && !empty($csvTrans->cto_receipt_name) ? $csvTrans->cto_receipt_name : false);

                $expense = (isset($row['expense']) && $row['expense'] > 0 ? $row['expense'] : 0);
                $income = (isset($row['income']) && $row['income'] > 0 ? $row['income'] : 0);

                $flow = (!empty($expense) && $expense > 0 ? 1 : 0);
                $transaction_amount = ($flow == 1 ? $expense : $income);

                $transaction_date = (isset($row['transdate']) && !empty($row['transdate']) ? date('Y-m-d', strtotime($row['transdate'])) : date('Y-m-d'));
                $invoice_no = (isset($row['invoiceno']) && !empty($row['invoiceno']) ? $row['invoiceno'] : null);
                $invoice_date = (isset($row['invoicedate']) && !empty($row['invoicedate']) ? date('Y-m-d', strtotime($row['invoicedate'])) : null);
                $detail = (isset($row['detail']) && !empty($row['detail']) ? $row['detail'] : null);
                $description = (isset($row['description']) && !empty($row['description']) ? $row['description'] : null);
                $acc_category_id_in = (isset($row['inccategory']) && $row['inccategory'] > 0 ? $row['inccategory'] : null);
                $acc_category_id_out = (isset($row['expcategory']) && $row['expcategory'] > 0 ? $row['expcategory'] : null);
                $transfer_bank_id = (isset($row['transstorage']) && $row['transstorage'] > 0 ? $row['transstorage'] : null);
                $audit_status = (isset($row['auditstatus']) && $row['auditstatus'] > 0 ? $row['auditstatus'] : 0);
                $trans_type = (isset($row['transactiontype']) && $row['transactiontype'] > 0 ? $row['transactiontype'] : 0);

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
                if($request->hasFile("rows.$index.document")):
                    $document = $request->file("rows.$index.document");
                    $documentName = $transaction_code.'.'.$document->getClientOriginalExtension();
                    $path = $document->storeAs('public/transactions', $documentName, 's3');

                    $userUpdate = AccTransaction::where('id', $transaction->id)->update([
                        'transaction_doc_name' => $documentName,
                    ]);
                elseif(isset($csvFile->has_cto_receipts) && $csvFile->has_cto_receipts == 1 && $has_receipts && !empty($cto_receipt_name)):
                    $localPath = 'public/receipts/'.$cto_receipt_name;
                    if (Storage::disk('local')->exists($localPath)):
                        $existingFile = new File(storage_path('app/' . $localPath));
                        $newFileName = $transaction_code.'.'.$existingFile->getExtension();
                        
                        $fileContent = Storage::disk('local')->get($localPath);
                        $s3Path = 'public/transactions/' . $newFileName;
                        Storage::disk('s3')->put($s3Path, $fileContent);

                        Storage::disk('local')->delete($localPath);

                        $userUpdate = AccTransaction::where('id', $transaction->id)->update([
                            'transaction_doc_name' => $newFileName,
                        ]);
                    endif;
                endif;

                if($transaction->id):
                    $updateRowCount += 1;

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
            }
        }

        return response()->json([
            'success' => true,
            'message' => $updateRowCount.' Out of '. count($rows).' rows are inserted',
            'redirect' => $redirect
        ]);
    }

}
