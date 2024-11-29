<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccTransaction;
use Illuminate\Http\Request;

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

            'sales' => $this->getSlaes($startDate, $endDate, $audit_status),
            'cos' => $this->getCostOfSales($startDate, $endDate, $audit_status),
            'otherincome' => $this->getOtherIncome($startDate, $endDate, $audit_status),
            'expenditure' => $this->getAllExpenditure($startDate, $endDate, $audit_status),
        ]);
    }

    public function getSlaes($startDate, $endDate, $audit_status){
        $categories = AccCategory::whereNot('id', 52)->where('trans_type', 0)->where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();

        $totalSales = 0;
        $res = [];
        if($categories->count() > 0):
            foreach($categories as $cat):
                $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();
                $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();

                $inf = $inflows->sum('transaction_amount');
                $otf = $outflows->sum('transaction_amount');

                $res['categories'][$cat->id]['name'] = $cat->category_name;
                $res['categories'][$cat->id]['amount'] = ($inf - $otf);
                $totalSales += ($inf - $otf);
            endforeach;
        endif;
        $res['total_sale'] = $totalSales;

        return $res;
    }

    public function getOtherIncome($startDate, $endDate, $audit_status){
        
        $otherIncome = 0;
        $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', 52)->whereIn('audit_status', $audit_status)->get();
        $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', 52)->whereIn('audit_status', $audit_status)->get();

        $inf = $inflows->sum('transaction_amount');
        $otf = $outflows->sum('transaction_amount');
        $otherIncome = ($inf - $otf);

        return $otherIncome;
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
            'can_edit' => ((auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3])) ? 1 : 0)
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
    }

    public function exportExpenses($startDate, $endDate){
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));

    }
}
