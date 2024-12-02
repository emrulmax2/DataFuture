<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccTransaction;
use Illuminate\Http\Request;

use App\Exports\ArrayCollectionExport;
use App\Models\AccAssetRegister;
use Maatwebsite\Excel\Facades\Excel;

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

                $costOfSlaes[$cat->id]['name'] = $cat->category_name;
                $costOfSlaes[$cat->id]['amount'] = ($otf - $inf);
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
}
