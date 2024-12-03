<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccTransaction;
use Illuminate\Http\Request;

use App\Exports\ArrayCollectionExport;
use App\Models\AccAssetRegister;
use App\Models\Option;
use App\Models\User;
use Google\Service\Books\Category;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ManagementReportController extends Controller
{
    public function index($startDate, $endDate){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        return view('pages.accounts.management-report', [
            'title' => 'Accounts Report - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Report', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,

            'all_sales' => $this->getAllIncomes($startDate, $endDate, $audit_status, [112]),
            'cos' => $this->getCostOfSales($startDate, $endDate, $audit_status),
            'all_other_income' => $this->getAllIncomes($startDate, $endDate, $audit_status, [], [112]),
            'expenditure' => $this->getAllExpenditure($startDate, $endDate, $audit_status),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count()
        ]);
    }

    public function getAllIncomes($startDate, $endDate, $audit_status, $catsNotIn = array(), $catsIn = array()){
        $allIncomes = [];
        $sales_total = 0;
        $query = AccCategory::where('trans_type', 0)->where('status', 1)->where('parent_id', 0)->whereIn('audit_status', $audit_status);
        if(!empty($catsNotIn)):
            $query->whereNotIn('id', $catsNotIn);
        endif;
        if(!empty($catsIn)):
            $query->whereIn('id', $catsIn);
        endif;
        $parentCategories = $query->orderBy('category_name', 'ASC')->get();
        if($parentCategories->count() > 0):
            foreach($parentCategories as $pcat):
                $childCategories = $this->getAllChildsIncomes($pcat->id, $audit_status);
                $exist = 0;
                $theParentTotal = 0;

                if(!empty($childCategories)):
                    foreach($childCategories as $ccat):
                        $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        if($inflows->count() > 0 || $outflows->count() > 0):
                            $inf = $inflows->sum('transaction_amount');
                            $otf = $outflows->sum('transaction_amount');
                            $theChildAmount = ($inf - $otf);
                            $theParentTotal += $theChildAmount;

                            $allIncomes[$pcat->id]['childs'][$ccat['id']]['name'] = $ccat['name'];
                            $allIncomes[$pcat->id]['childs'][$ccat['id']]['amount'] = $theChildAmount;
                            $exist += 1;
                        endif;
                    endforeach;
                endif;
                $ownInflow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                $ownOutfolow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                if($ownInflow->count() > 0 || $ownOutfolow->count() > 0):
                    $inf = $ownInflow->sum('transaction_amount');
                    $otf = $ownOutfolow->sum('transaction_amount');
                    $theParentAmount = ($inf - $otf);
                    $theParentTotal += $theParentAmount;

                    //$allIncomes[$pcat->id]['childs'][$pcat->id]['name'] = $pcat->category_name;
                    //$allIncomes[$pcat->id]['childs'][$pcat->id]['amount'] = $theParentAmount;
                    $exist += 1;
                endif;
                if($exist > 0):
                    $sales_total += $theParentTotal;
                    $allIncomes[$pcat->id]['name'] = $pcat->category_name;
                    $allIncomes[$pcat->id]['amount'] = $theParentTotal;
                    $allIncomes[$pcat->id]['has_children'] = (!empty($childCategories) ? 1 : 0);
                endif;
            endforeach;
        endif;
        //dd($allIncomes);
        return ['total_sale' => $sales_total, 'incomes' => $allIncomes];
    }

    public function getAllChildsIncomes($parent_id, $audit_status){
        static $incms = array ();
	    static $levs = 0;
        static $cnt = 0;
	    $levs ++;

        $categories = AccCategory::where('parent_id', $parent_id)->where('trans_type', 0)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            foreach($categories as $cat):
                $incms[$cnt]['id'] = $cat->id;
                $incms[$cnt]['name'] = $cat->category_name;
                $incms[$cnt]['status'] = $cat->status;
                $incms[$cnt]['trans_type'] = $cat->trans_type;
                $incms[$cnt]['parent_id'] = $cat->parent_id;

                $cnt += 1;
                $this->getAllChildsIncomes($cat->id, $audit_status);
            endforeach;
        endif;

        $levs --;
        $tmp = $incms;
        if($levs == 0):
            $incms = array();
            $cnt = 0;
        endif;

	    return $tmp;
    }

    public function getCostOfSales($startDate, $endDate, $audit_status){
        $cos_cate_ids = [16, 72, 81, 21, 29];

        $costOfSlaes = [];
        $categories = AccCategory::whereIn('id', $cos_cate_ids)->where('status', 1)->whereIn('audit_status', $audit_status)->get();//->where('trans_type', 0)
        if($categories->count() > 0):
            foreach($categories as $cat):
                $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();
                $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();

                $inf = $inflows->sum('transaction_amount');
                $otf = $outflows->sum('transaction_amount');

                if(($otf - $inf) != 0):
                    $costOfSlaes[$cat->id]['name'] = $cat->category_name;
                    $costOfSlaes[$cat->id]['amount'] = ($otf - $inf);
                endif;
            endforeach;
        endif;

        return $costOfSlaes;
    }

    public function getAllExpenditure($startDate, $endDate, $audit_status){
        $catsNotIn = [16, 72, 81, 21, 29];

        $costOfSlaes = [];
        $parentCategories = AccCategory::whereNotIn('id', $catsNotIn)->where('trans_type', 1)->where('status', 1)->where('parent_id', 0)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();
        if($parentCategories->count() > 0):
            foreach($parentCategories as $pcat):
                $childCategories = $this->getAllChildsExpenditure($pcat->id, $audit_status);
                $exist = 0;
                $theParentTotal = 0;

                if(!empty($childCategories)):
                    foreach($childCategories as $ccat):
                        $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        if($inflows->count() > 0 || $outflows->count() > 0):
                            $inf = $inflows->sum('transaction_amount');
                            $otf = $outflows->sum('transaction_amount');
                            $theChildAmount = ($otf - $inf);
                            $theParentTotal += $theChildAmount;

                            $costOfSlaes[$pcat->id]['childs'][$ccat['id']]['name'] = $ccat['name'];
                            $costOfSlaes[$pcat->id]['childs'][$ccat['id']]['amount'] = $theChildAmount;
                            $exist += 1;
                        endif;
                    endforeach;
                endif;
                $ownInflow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                $ownOutfolow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                if($ownInflow->count() > 0 || $ownOutfolow->count() > 0):
                    $inf = $ownInflow->sum('transaction_amount');
                    $otf = $ownOutfolow->sum('transaction_amount');
                    $theParentAmount = ($otf - $inf);
                    $theParentTotal += $theParentAmount;

                    $costOfSlaes[$pcat->id]['childs'][$pcat->id]['name'] = $pcat->category_name;
                    $costOfSlaes[$pcat->id]['childs'][$pcat->id]['amount'] = $theParentAmount;
                    $exist += 1;
                endif;
                if($exist > 0):
                    $costOfSlaes[$pcat->id]['name'] = $pcat->category_name;
                    $costOfSlaes[$pcat->id]['amount'] = $theParentTotal;
                endif;
            endforeach;
        endif;
        //dd($costOfSlaes);
        return $costOfSlaes;
    }

    public function getAllChildsExpenditure($parent_id, $audit_status){
        $catsNotIn = [16, 72, 81, 21, 29];
        static $exps = array ();
	    static $levs = 0;
        static $cnt = 0;
	    $levs ++;

        $categories = AccCategory::whereNotIn('id', $catsNotIn)->where('parent_id', $parent_id)->where('trans_type', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            foreach($categories as $cat):
                $exps[$cnt]['id'] = $cat->id;
                $exps[$cnt]['name'] = $cat->category_name;
                $exps[$cnt]['status'] = $cat->status;
                $exps[$cnt]['trans_type'] = $cat->trans_type;
                $exps[$cnt]['parent_id'] = $cat->parent_id;

                $cnt += 1;
                $this->getAllChildsExpenditure($cat->id, $audit_status);
            endforeach;
        endif;

        $levs --;
        $tmp = $exps;
        if($levs == 0):
            $exps = array();
            $cnt = 0;
        endif;

	    return $tmp;
    }

    public function show($startDate, $endDate, AccCategory $category){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);

        return view('pages.accounts.management-report-details', [
            'title' => 'Accounts Report Details - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Report', 'href' => 'javascript:void(0);'],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'in_categories' => $this->catTreeInc(0, 0),
            'out_categories' => $this->catTreeExp(0, 1),

            'startDate' => $startDate,
            'endDate' => $endDate,
            'category' => $category,
            'transactions' => AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('acc_category_id', $category->id)->whereIn('audit_status', $audit_status)->get(),
            'is_auditor' => (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false),
            'can_edit' => ((auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3])) ? 1 : 0),
            'openedAssets' => AccAssetRegister::where('active', 1)->get()->count()
        ]);
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

    public function exportIncomes($startDate, $endDate){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $is_audior = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $income_categories = $this->getAllIncomeCategories($startDate, $endDate, $audit_status, [112]);
        $other_income_categories = $this->getAllIncomeCategories($startDate, $endDate, $audit_status, [], [112]);
        $cost_of_sale_categories = [16, 72, 81, 21, 29];

        $gross_profit_categories = array_merge($income_categories, $cost_of_sale_categories, $other_income_categories);
        //dd($other_income_categories);

        $theCollection = [];
        $theCollection[1][] = "TC No";
        $theCollection[1][] = "Date";
        $theCollection[1][] = "Details";
        $theCollection[1][] = "Invoice";
        $theCollection[1][] = "Invoice Date";
        if(!$is_audior):
            $theCollection[1][] = "Description";
        endif;
        $theCollection[1][] = "Category";
        $theCollection[1][] = "Code";
        $theCollection[1][] = "Storage";
        $theCollection[1][] = "Deposit";
        $theCollection[1][] = "Withdraw";

        $row = 2;
        $transactions = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->whereIn('acc_category_id', $gross_profit_categories)->whereIn('audit_status', $audit_status)->get();
        if($transactions->count() > 0):
            foreach($transactions as $trans):
                $transaction_type = ($trans->transaction_type > 0 ? $trans->transaction_type : 0);
                $flow = (isset($trans->flow) && $trans->flow != '' ? $trans->flow : 0);
                $transaction_amount = (isset($trans->transaction_amount) && $trans->transaction_amount > 0 ? $trans->transaction_amount : 0);

                $theCollection[$row][] = $trans->transaction_code;
                $theCollection[$row][] = date('d-m-Y', strtotime($trans->transaction_date_2));
                $theCollection[$row][] = !empty($trans->detail) ? $trans->detail : '';
                $theCollection[$row][] = !empty($trans->invoice_no) ? $trans->invoice_no : '';
                $theCollection[$row][] = !empty($trans->invoice_date) ? date('d-m-Y', strtotime($trans->invoice_date)) : '';
                if(!$is_audior):
                    $theCollection[$row][] = !empty($trans->description) ? $trans->description : '';
                endif;
                $theCollection[$row][] = isset($trans->category->category_name) && !empty($trans->category->category_name) ? $trans->category->category_name : '';
                $theCollection[$row][] = isset($trans->category->code) && !empty($trans->category->code) ? $trans->category->code : '';
                $theCollection[$row][] = isset($trans->bank->bank_name) && !empty($trans->bank->bank_name) ? $trans->bank->bank_name : '';
                $theCollection[$row][] = ($flow != 1 ? $transaction_amount : '');
                $theCollection[$row][] = ($flow == 1 ? $transaction_amount : '');

                $row += 1;
            endforeach;
        endif;

        $report_title = 'Transactions_'.date('d_m_Y', strtotime($startDate)).'_to_'.date('d_m_Y', strtotime($endDate)).'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function exportExpenses($startDate, $endDate){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $is_audior = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $expense_categories = $this->getAllExpenseCategories($startDate, $endDate, $audit_status, [16, 72, 81, 21, 29]);
        
        //dd($other_income_categories);

        $theCollection = [];
        $theCollection[1][] = "TC No";
        $theCollection[1][] = "Date";
        $theCollection[1][] = "Details";
        $theCollection[1][] = "Invoice";
        $theCollection[1][] = "Invoice Date";
        if(!$is_audior):
            $theCollection[1][] = "Description";
        endif;
        $theCollection[1][] = "Category";
        $theCollection[1][] = "Code";
        $theCollection[1][] = "Storage";
        $theCollection[1][] = "Deposit";
        $theCollection[1][] = "Withdraw";

        $row = 2;
        $transactions = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->whereIn('acc_category_id', $expense_categories)->whereIn('audit_status', $audit_status)->get();
        if($transactions->count() > 0):
            foreach($transactions as $trans):
                $transaction_type = ($trans->transaction_type > 0 ? $trans->transaction_type : 0);
                $flow = (isset($trans->flow) && $trans->flow != '' ? $trans->flow : 0);
                $transaction_amount = (isset($trans->transaction_amount) && $trans->transaction_amount > 0 ? $trans->transaction_amount : 0);

                $theCollection[$row][] = $trans->transaction_code;
                $theCollection[$row][] = date('d-m-Y', strtotime($trans->transaction_date_2));
                $theCollection[$row][] = !empty($trans->detail) ? $trans->detail : '';
                $theCollection[$row][] = !empty($trans->invoice_no) ? $trans->invoice_no : '';
                $theCollection[$row][] = !empty($trans->invoice_date) ? date('d-m-Y', strtotime($trans->invoice_date)) : '';
                if(!$is_audior):
                    $theCollection[$row][] = !empty($trans->description) ? $trans->description : '';
                endif;
                $theCollection[$row][] = isset($trans->category->category_name) && !empty($trans->category->category_name) ? $trans->category->category_name : '';
                $theCollection[$row][] = isset($trans->category->code) && !empty($trans->category->code) ? $trans->category->code : '';
                $theCollection[$row][] = isset($trans->bank->bank_name) && !empty($trans->bank->bank_name) ? $trans->bank->bank_name : '';
                $theCollection[$row][] = ($flow != 1 ? $transaction_amount : '');
                $theCollection[$row][] = ($flow == 1 ? $transaction_amount : '');

                $row += 1;
            endforeach;
        endif;

        $report_title = 'Transactions_'.date('d_m_Y', strtotime($startDate)).'_to_'.date('d_m_Y', strtotime($endDate)).'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function getAllIncomeCategories($startDate, $endDate, $audit_status, $catsNotIn = array(), $catsIn = array()){
        $ids = [];
        $query = AccCategory::where('trans_type', 0)->where('status', 1)->where('parent_id', 0)->whereIn('audit_status', $audit_status);
        if(!empty($catsNotIn)):
            $query->whereNotIn('id', $catsNotIn);
        endif;
        if(!empty($catsIn)):
            $query->whereIn('id', $catsIn);
        endif;
        $parentCategories = $query->orderBy('category_name', 'ASC')->get();
        if($parentCategories->count() > 0):
            foreach($parentCategories as $pcat):
                $ids[] = $pcat->id;
                $childCategories = $this->getAllChildsIncomeCategories($pcat->id, $audit_status);
                if(!empty($childCategories)):
                    $ids = array_merge($ids, $childCategories);
                endif;
            endforeach;
        endif;
        
        return $ids;
    }

    public function getAllChildsIncomeCategories($parent_id, $audit_status){
        static $incmsc = array ();
	    static $levs = 0;
	    $levs ++;

        $categories = AccCategory::where('parent_id', $parent_id)->where('trans_type', 0)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            foreach($categories as $cat):
                $incmsc[] = $cat->id;

                $this->getAllChildsIncomeCategories($cat->id, $audit_status);
            endforeach;
        endif;

        $levs --;
        $tmp = $incmsc;
        if($levs == 0):
            $incmsc = array();
        endif;

	    return $tmp;
    }

    public function getAllExpenseCategories($startDate, $endDate, $audit_status, $catsNotIn = array(), $catsIn = array()){
        $ids = [];
        $query = AccCategory::where('trans_type', 1)->where('status', 1)->where('parent_id', 0)->whereIn('audit_status', $audit_status);
        if(!empty($catsNotIn)):
            $query->whereNotIn('id', $catsNotIn);
        endif;
        if(!empty($catsIn)):
            $query->whereIn('id', $catsIn);
        endif;
        $parentCategories = $query->orderBy('category_name', 'ASC')->get();
        if($parentCategories->count() > 0):
            foreach($parentCategories as $pcat):
                $ids[] = $pcat->id;
                $childCategories = $this->getAllChildsExpenseCategories($pcat->id, $audit_status, $catsNotIn, $catsIn);
                if(!empty($childCategories)):
                    $ids = array_merge($ids, $childCategories);
                endif;
            endforeach;
        endif;
        
        return $ids;
    }

    public function getAllChildsExpenseCategories($parent_id, $audit_status, $catsNotIn = array(), $catsIn = array()){
        static $expnse = array ();
	    static $levs = 0;
	    $levs ++;

        $categories = AccCategory::where('parent_id', $parent_id)->where('trans_type', 1)->whereIn('audit_status', $audit_status);
        if(!empty($catsNotIn)):
            $categories->whereNotIn('id', $catsNotIn);
        endif;
        if(!empty($catsIn)):
            $categories->whereIn('id', $catsIn);
        endif;
        $categories = $categories->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            foreach($categories as $cat):
                $expnse[] = $cat->id;

                $this->getAllChildsExpenseCategories($cat->id, $audit_status, $catsNotIn = array(), $catsIn = array());
            endforeach;
        endif;

        $levs --;
        $tmp = $expnse;
        if($levs == 0):
            $expnse = array();
        endif;

	    return $tmp;
    }

    public function exportDetails($startDate, $endDate, AccCategory $category){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $is_audior = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);

        $theCollection = [];
        $theCollection[1][] = "TC No";
        $theCollection[1][] = "Date";
        $theCollection[1][] = "Details";
        $theCollection[1][] = "Invoice";
        $theCollection[1][] = "Invoice Date";
        if(!$is_audior):
            $theCollection[1][] = "Description";
        endif;
        $theCollection[1][] = "Category";
        $theCollection[1][] = "Code";
        $theCollection[1][] = "Storage";
        $theCollection[1][] = "Deposit";
        $theCollection[1][] = "Withdraw";

        $row = 2;
        $transactions = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('acc_category_id', $category->id)->whereIn('audit_status', $audit_status)->orderBy('transaction_date_2', 'DESC')->get();
        if($transactions->count() > 0):
            foreach($transactions as $trans):
                $transaction_type = ($trans->transaction_type > 0 ? $trans->transaction_type : 0);
                $flow = (isset($trans->flow) && $trans->flow != '' ? $trans->flow : 0);
                $transaction_amount = (isset($trans->transaction_amount) && $trans->transaction_amount > 0 ? $trans->transaction_amount : 0);

                $theCollection[$row][] = $trans->transaction_code;
                $theCollection[$row][] = date('d-m-Y', strtotime($trans->transaction_date_2));
                $theCollection[$row][] = !empty($trans->detail) ? $trans->detail : '';
                $theCollection[$row][] = !empty($trans->invoice_no) ? $trans->invoice_no : '';
                $theCollection[$row][] = !empty($trans->invoice_date) ? date('d-m-Y', strtotime($trans->invoice_date)) : '';
                if(!$is_audior):
                    $theCollection[$row][] = !empty($trans->description) ? $trans->description : '';
                endif;
                $theCollection[$row][] = isset($trans->category->category_name) && !empty($trans->category->category_name) ? $trans->category->category_name : '';
                $theCollection[$row][] = isset($trans->category->code) && !empty($trans->category->code) ? $trans->category->code : '';
                $theCollection[$row][] = isset($trans->bank->bank_name) && !empty($trans->bank->bank_name) ? $trans->bank->bank_name : '';
                $theCollection[$row][] = ($flow != 1 ? $transaction_amount : '');
                $theCollection[$row][] = ($flow == 1 ? $transaction_amount : '');

                $row += 1;
            endforeach;
        endif;

        $report_title = 'Transactions_'.date('d_m_Y', strtotime($startDate)).'_to_'.date('d_m_Y', strtotime($endDate)).'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function printDetails($startDate, $endDate, AccCategory $category){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $is_audior = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? true : false);

        $user = User::find(auth()->user()->id);
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $subTotal = 0;
        $report_title = 'Category: '.preg_replace("/[^a-z0-9\_\-\.\ ]/i", '', $category->category_name);
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 65px !important; margin-right:65px !important; }

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
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
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
                            $PDFHTML .= '<td>Date</td>';
                            $PDFHTML .= '<td>'.date('jS M, Y', strtotime($startDate)).' - '.date('jS M, Y', strtotime($endDate)).'</td>';
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

                $PDFHTML .= '<table class="table table-bordered table-sm attenRateReportTable">';
                    $PDFHTML .= '<thead>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th>Transaction</th>';
                            $PDFHTML .= '<th>Details</th>';
                            $PDFHTML .= '<th>Invoice</th>';
                            if(!$is_audior):
                                $PDFHTML .= '<th>Description</th>';
                            endif;
                            $PDFHTML .= '<th>Category</th>';
                            $PDFHTML .= '<th>Code</th>';
                            $PDFHTML .= '<th>Storage</th>';
                            $PDFHTML .= '<th>Deposit</th>';
                            $PDFHTML .= '<th>Withdraw</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</thead>';
                    $PDFHTML .= '<tbody>';
                        $transactions = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('acc_category_id', $category->id)->whereIn('audit_status', $audit_status)->orderBy('transaction_date_2', 'DESC')->get();
                        if($transactions->count() > 0):
                            foreach($transactions as $trans):
                                $transaction_type = ($trans->transaction_type > 0 ? $trans->transaction_type : 0);
                                $flow = (isset($trans->flow) && $trans->flow != '' ? $trans->flow : 0);
                                $transaction_amount = (isset($trans->transaction_amount) && $trans->transaction_amount > 0 ? $trans->transaction_amount : 0);
                
                                if($trans->flow == 1):
                                    $subTotal -= $trans->transaction_amount;
                                else:
                                    $subTotal += $trans->transaction_amount;
                                endif;

                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td>';
                                        $PDFHTML .= $trans->transaction_code;
                                        $PDFHTML .= (!empty($trans->transaction_code) && !empty($trans->transaction_date_2) ? '<br/>' : '');
                                        $PDFHTML .= date('d-m-Y', strtotime($trans->transaction_date_2));
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>'.(!empty($trans->detail) ? $trans->detail : '').'</td>';
                                    $PDFHTML .= '<td style="word-break: break-all; white-space: normal;">';
                                        $PDFHTML .= (!empty($trans->invoice_no) ? $trans->invoice_no : '');
                                        $PDFHTML .= (!empty($trans->invoice_no) && !empty($trans->invoice_date) ? '<br/>' : '');
                                        $PDFHTML .= (!empty($trans->invoice_date) ? date('d-m-Y', strtotime($trans->invoice_date)) : '');
                                    $PDFHTML .= '</td>';
                                    if(!$is_audior):
                                        $PDFHTML .= '<td>'.(!empty($trans->description) ? $trans->description : '').'</td>';
                                    endif;
                                    $PDFHTML .= '<td>'.(isset($trans->category->category_name) && !empty($trans->category->category_name) ? $trans->category->category_name : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($trans->category->code) && !empty($trans->category->code) ? $trans->category->code : '').'</td>';
                                    $PDFHTML .= '<td>'.(isset($trans->bank->bank_name) && !empty($trans->bank->bank_name) ? $trans->bank->bank_name : '').'</td>';
                                    $PDFHTML .= '<td>'.($flow != 1 ? $transaction_amount : '').'</td>';
                                    $PDFHTML .= '<td>'.($flow == 1 ? $transaction_amount : '').'</td>';
                                $PDFHTML .= '<tr>';
                            endforeach;
                        endif;
                    $PDFHTML .= '</tbody>';
                    $PDFHTML .= '<tfoot>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th colspan="'.(!$is_audior ? 7 : 6).'">Total</th>';
                            $PDFHTML .= '<th colspan="2" class="text-right" style="text-align: right;">'.($subTotal >= 0 ? '£'.number_format($subTotal, 2) : '-£'.number_format(str_replace('-', '', $subTotal), 2)).'</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</tfoot>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function printReport($startDate, $endDate){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

        $all_sales = $this->getAllIncomes($startDate, $endDate, $audit_status, [112]);
        $cos = $this->getCostOfSales($startDate, $endDate, $audit_status);
        $all_other_income = $this->getAllIncomes($startDate, $endDate, $audit_status, [], [112]);
        $expenditure = $this->getAllExpenditure($startDate, $endDate, $audit_status);

        $user = User::find(auth()->user()->id);
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Management Report';
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
                                .underline{text-decoration: underline;}
                                .text-primary{color: #164e63;}
                                .font-medium{font-weight: 700;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.managementReportTable tr td{padding: 5px 0;}
                                .table.managementReportTable tr td.w-150px{ width: 120px; }
                                .cursor-pointer{ cursor: pointer; }

                                .table.table-borderless.managementReportTable .cosHeadingRow td{padding-top: 1.25rem;}
                                .table.table-borderless.managementReportTable .gpHeadingRow td:last-child{ border-top: 1px solid #000; }
                                .table.table-borderless.managementReportTable .oiHeadingRow td,
                                .table.table-borderless.managementReportTable .oiFirstHeadingRow td{ padding-top: 1.25rem; }
                                .table.table-borderless.managementReportTable .aoiHeadingRow td:last-child{ border-top: 1px solid #000; }
                                .table.table-borderless.managementReportTable .expdHeadingRow td{ padding-top: 1.25rem; }
                                .table.table-borderless.managementReportTable .texpdHeadingRow td:last-child{ border-bottom: 1px solid #000;}
                                .table.table-borderless.managementReportTable .npHeadingRow td:last-child{ border-bottom: 4px double #000; }
                                .table.table-borderless.managementReportTable .child_row td:first-child{ padding-left: 1rem; }
                                .table.table-borderless.managementReportTable .sales_parent_row td:first-child{ padding-left: 1rem; }
                                .table.table-borderless.managementReportTable .sales_child_row td:first-child{ padding-left: 2rem; }
                                .table.table-borderless.managementReportTable .other_child_row td:first-child{ padding-left: 1rem; }
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
                            $PDFHTML .= '<td>Date</td>';
                            $PDFHTML .= '<td>'.date('jS M, Y', strtotime($startDate)).' - '.date('jS M, Y', strtotime($endDate)).'</td>';
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

                $PDFHTML .= '<table class="table table-borderless table-sm managementReportTable" id="managementReportTable">';
                    $PROFIT = $all_sales['total_sale'];
                    $COS_TOTAL = 0;
                    $GROSS_PROFIT = 0;
                    $EXPENDITURE_TOTAL = 0;
                    $PDFHTML .= '<tbody>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="3">';
                                $PDFHTML .= '<span class="font-medium"><span style="font-family: DejaVu Sans, sans-serif;">&dArr;</span> Sales</span>';
                            $PDFHTML .= '</td>';
                            $PDFHTML .= '<td class="w-150px text-right">';
                                $PDFHTML .= number_format($all_sales['total_sale'], 2);
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                        if(!empty($all_sales['incomes'])):
                            foreach($all_sales['incomes'] as $perent_id => $sale):
                                $PDFHTML .= '<tr class="sales_parent_row">';
                                    $PDFHTML .= '<td colspan="2">';
                                        //$PDFHTML .= '<a href="javascript:void(0);" class="cursor-pointer text-primary underline">';
                                            if(isset($sale['has_children']) && $sale['has_children'] == 1):
                                                $PDFHTML .= '<span style="font-family: DejaVu Sans, sans-serif;">&dArr;</span> ';
                                            endif;
                                            $PDFHTML .= $sale['name'];
                                        //$PDFHTML .= '</a>';
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td class="w-150px text-right">'.number_format($sale['amount'], 2).'</td>';
                                    $PDFHTML .= '<td class="w-150px text-right"></td>';
                                $PDFHTML .= '</tr>';
                                if(isset($sale['childs']) && !empty($sale['childs'])):
                                    foreach($sale['childs'] as $sale_id => $child):
                                        $PDFHTML .= '<tr class="sales_child_row sales_child_of_'.$perent_id.'">';
                                            $PDFHTML .= '<td>'.$child['name'].'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right">'.number_format($child['amount'], 2).'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                                        $PDFHTML .= '</tr>';
                                    endforeach;
                                endif;
                            endforeach;
                        endif;

                        if(!empty($cos)):
                            $PDFHTML .= '<tr class="cosHeadingRow">';
                                $PDFHTML .= '<td colspan="3" class="font-medium">Cose Of Sales</td>';
                                $PDFHTML .= '<td></td>';
                            $PDFHTML .= '</tr>';
                            foreach($cos as $cs_id => $cs):
                                $COS_TOTAL += $cs['amount'];
                                $PDFHTML .= '<tr>';
                                    $PDFHTML .= '<td colspan="3">'.$cs['name'].'</td>';
                                    $PDFHTML .= '<td class="w-150px text-right">'.number_format($cs['amount'], 2).'</td>';
                                $PDFHTML .= '</tr>';
                            endforeach;
                        endif; 
                        $GROSS_PROFIT = ($PROFIT - $COS_TOTAL);
                        $PDFHTML .= '<tr class="gpHeadingRow">';
                            $PDFHTML .= '<td colspan="3" class="font-medium uppercase">Gross Profit</td>';
                            $PDFHTML .= '<td class="w-150px text-right">'.number_format($GROSS_PROFIT, 2).'</td>';
                        $PDFHTML .= '</tr>';

                        if(!empty($all_other_income['incomes'])):
                            $lp = 1;
                            foreach($all_other_income['incomes'] as $perent_id => $sale):
                                $PDFHTML .= '<tr class="other_income_parent_row '.($lp == 1 ? 'oiFirstHeadingRow' : '').'">';
                                    $PDFHTML .= '<td colspan="3">';
                                        //$PDFHTML .= '<a href="javascript:void(0);" class="cursor-pointer text-primary font-medium underline">';
                                            if(isset($sale['has_children']) && $sale['has_children'] == 1):
                                                $PDFHTML .= '<span style="font-family: DejaVu Sans, sans-serif;">&dArr;</span> ';
                                            endif;
                                            $PDFHTML .= $sale['name'];
                                        //$PDFHTML .= '</a>';
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td class="w-150px text-right">'.number_format($sale['amount'], 2).'</td>';
                                $PDFHTML .= '</tr>';
                                if(isset($sale['childs']) && !empty($sale['childs'])):
                                    foreach($sale['childs'] as $sale_id => $child):
                                        $PDFHTML .= '<tr class="other_child_row">';
                                            $PDFHTML .= '<td colspan="2">'.$child['name'].'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right">'.number_format($child['amount'], 2).'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                                        $PDFHTML .= '</tr>';
                                    endforeach;
                                endif;
                                $lp++;
                            endforeach;
                        endif;

                        $GROSS_PROFIT += $all_other_income['total_sale'];
                        $PDFHTML .= '<tr class="aoiHeadingRow">';
                            $PDFHTML .= '<td colspan="3" class="font-medium"></td>';
                            $PDFHTML .= '<td class="w-150px text-right">'.number_format($GROSS_PROFIT, 2).'</td>';
                        $PDFHTML .= '</tr>';

                        if(!empty($expenditure)):
                        $PDFHTML .= '<tr class="expdHeadingRow">';
                            $PDFHTML .= '<td colspan="3" class="font-medium">Expenditure</td>';
                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                        $PDFHTML .= '</tr>';
                            foreach($expenditure as $perent_id => $expd):
                                $EXPENDITURE_TOTAL += $expd['amount'];
                                $PDFHTML .= '<tr class="parent_row">';
                                    $PDFHTML .= '<td colspan="2"><span style="font-family: DejaVu Sans, sans-serif;">&dArr;</span> '.$expd['name'].'</td>';
                                    $PDFHTML .= '<td class="w-150px text-right">'.number_format($expd['amount'], 2).'</td>';
                                    $PDFHTML .= '<td class="w-150px text-right"></td>';
                                $PDFHTML .= '</tr>';
                                if($expd['childs'] && !empty($expd['childs'])):
                                    foreach($expd['childs'] as $exped_id => $child):
                                        $PDFHTML .= '<tr class="child_row">';
                                            $PDFHTML .= '<td>'.$child['name'].'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right">'.number_format($child['amount'], 2).'</td>';
                                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                                            $PDFHTML .= '<td class="w-150px text-right"></td>';
                                        $PDFHTML .= '</tr>';
                                    endforeach;
                                endif;
                            endforeach;
                            $GROSS_PROFIT -= $EXPENDITURE_TOTAL;
                            $PDFHTML .= '<tr class="texpdHeadingRow">';
                                $PDFHTML .= '<td colspan="3" class="font-medium"></td>';
                                $PDFHTML .= '<td class="w-150px text-right">'.number_format($EXPENDITURE_TOTAL, 2).'</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr class="npHeadingRow">';
                                $PDFHTML .= '<td colspan="3" class="font-medium">NET PROFIT</td>';
                                $PDFHTML .= '<td class="w-150px text-right">'.number_format($GROSS_PROFIT, 2).'</td>';
                            $PDFHTML .= '</tr>';
                        endif;
                    $PDFHTML .= '</tbody>';
                $PDFHTML .= '</table>';

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')//landscape
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
