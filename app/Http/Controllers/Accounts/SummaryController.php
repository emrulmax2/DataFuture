<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\AccTransaction;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        return view('pages.accounts.summary', [
            'title' => 'Accounts - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get(),
            'categories' => AccCategory::orderBy('category_name', 'ASC')->whereIn('audit_status', $audit_status)->where('status', 1)->get(),
            'chartData' => $this->chartData()
        ]);
    }

    public function search(Request $request){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $theRangeDate = (!empty($request->theRangeDate) && strlen($request->theRangeDate) == 23 ? explode(' - ', $request->theRangeDate) : []);
        $theQueryText = (!empty($request->theQueryText) ? $request->theQueryText : '');
        $theMinAmount = ($request->theMinAmount != '' ? $request->theMinAmount : 0);
        $theMaxAmount = ($request->theMaxAmount != '' ? $request->theMaxAmount : 0);
        $summary_categories = (!empty($request->summary_categories) ? $request->summary_categories : []);
        $summary_storages = (!empty($request->summary_storages) ? $request->summary_storages : []);

        $startDate = (!empty($theRangeDate) && count($theRangeDate) == 2 & isset($theRangeDate[0]) && !empty($theRangeDate[0]) ? date('Y-m-d', strtotime($theRangeDate[0])) : '');
        $endDate = (!empty($theRangeDate) && count($theRangeDate) == 2 & isset($theRangeDate[1]) && !empty($theRangeDate[1]) ? date('Y-m-d', strtotime($theRangeDate[1])) : '');

        if(!empty($summary_storages)):
            $banksids = $summary_storages;
        else:
            $query = AccTransaction::orderBy('id', 'desc')->where('parent', 0)->whereIn('audit_status', $audit_status);
            if($theMinAmount > 0): $query->where('transaction_amount', '>=', $theMinAmount); endif;
            if($theMaxAmount > 0): $query->where('transaction_amount', '<=', $theMaxAmount); endif;
            if(!empty($startDate) && $endDate):
                $query->whereBetween('transaction_date_2', [$startDate, $endDate]);
            endif;
            if(!empty($summary_categories) && count($summary_categories) > 0):
                $query->whereIn('acc_category_id', $summary_categories);
            endif;
            if(!empty($theQueryText)):
                $query->where(function($q) use($theQueryText){
                    $q->orWhere('detail','LIKE','%'.$theQueryText.'%')
                        ->orWhere('description','LIKE','%'.$theQueryText.'%')
                        ->orWhere('transaction_code','LIKE','%'.$theQueryText.'%')
                        ->orWhere('transaction_amount','LIKE','%'.$theQueryText.'%')
                        ->orWhere('invoice_no','LIKE','%'.$theQueryText.'%');
                });
            endif;
            $banksids = $query->pluck('acc_bank_id')->unique()->toArray();
        endif;

        $HTML = '';
        if(!empty($banksids)):
            $banks = AccBank::whereIn('id', $banksids)->where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('bank_name', 'ASC')->get();
            if(!empty($banks)):
                foreach($banks as $bank):
                    $query = AccTransaction::orderBy('id', 'desc')->where('parent', 0)->whereIn('audit_status', $audit_status);
                    if($theMinAmount > 0): $query->where('transaction_amount', '>=', $theMinAmount); endif;
                    if($theMaxAmount > 0): $query->where('transaction_amount', '<=', $theMaxAmount); endif;
                    if(!empty($startDate) && $endDate):
                        $query->whereBetween('transaction_date_2', [$startDate, $endDate]);
                    endif;
                    if(!empty($summary_categories) && count($summary_categories) > 0):
                        $query->whereIn('acc_category_id', $summary_categories);
                    endif;
                    if(!empty($theQueryText)):
                        $query->where(function($q) use($theQueryText){
                            $q->orWhere('detail','LIKE','%'.$theQueryText.'%')
                                ->orWhere('description','LIKE','%'.$theQueryText.'%')
                                ->orWhere('transaction_code','LIKE','%'.$theQueryText.'%')
                                ->orWhere('transaction_amount','LIKE','%'.$theQueryText.'%')
                                ->orWhere('invoice_no','LIKE','%'.$theQueryText.'%');
                        });
                    endif;
                    $bankTransactions = $query->where('acc_bank_id', $bank->id)->get();
                    $HTML .= '<div class="overflow-x-auto bg-white mb-5">';
                        $HTML .= '<table class="table table-striped table-sm">';
                            $HTML .= '<tr><td colspan="5" class="text-left font-medium text-lg">'.$bank->bank_name.'</td></tr>';
                            foreach($bankTransactions as $bt):
                                $HTML .= '<tr>';
                                    $HTML .= '<td>';
                                        $HTML .= '<div class="block relative">';
                                            $HTML .= '<div class="font-medium whitespace-nowrap">'.date('jS F, Y', strtotime($bt->transaction_date_2)).'</div>';
                                            $HTML .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5 flex justify-start items-center">';
                                                if($bt->transaction_doc_name != ''):
                                                    $HTML .= '<a href="javascript:void(0);" data-id="'.$bt->id.'" class="text-success mr-2 downloadDoc" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>';
                                                endif;
                                                $HTML .= $bt->transaction_code;
                                            $HTML .= '</div>';
                                        $HTML .= '</div>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= '<div class="relative">';
                                            if($bt->detail != ''):
                                                $HTML .= '<div class="whitespace-normal">'.$bt->detail.'</div>';
                                            endif;
                                            if($bt->description != '' || $bt->invoice_no != ''):
                                                $HTML .= '<div class="whitespace-normal">';
                                                    $HTML .= ($bt->invoice_no != '' ? $bt->invoice_no : '');
                                                    $HTML .= ($bt->invoice_no != '' && $bt->description != '' ? ' - ' : '');
                                                    $HTML .= ($bt->description != '' ? $bt->description : '');
                                                $HTML .= '</div>';
                                            endif;
                                        $HTML .= '</div>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="w-60">';
                                        if($bt->transaction_type == 2):
                                            $HTML .= '<div class="relative">';
                                                $HTML .= '<div class="font-medium whitespace-normal">';
                                                    if($bt->flow == 0):
                                                        $HTML .= '<span class="btn btn-linkedin p-0 rounded-0 mr-2"><i data-lucide="arrow-right" class="w-3 h-3"></i></span>';
                                                    elseif($bt->flow == 1):
                                                        $HTML .= '<span class="btn btn-linkedin p-0 rounded-0 mr-2"><i data-lucide="arrow-left" class="w-3 h-3"></i></span>';
                                                    endif;
                                                    $HTML .= (isset($bt->tbank->bank_name) ? $bt->tbank->bank_name : '');
                                                $HTML .= '</div>';
                                            $HTML .= '</div>';
                                        elseif($bt->transaction_type != 2):
                                            $HTML .= '<div class="relative">';
                                                $HTML .= '<div class="font-medium whitespace-normal">'.(isset($bt->category->category_name) ? $bt->category->category_name : '').'</div>';
                                            $HTML .= '</div>';
                                        endif;
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="w-40">';
                                        if($bt->flow == 1):
                                            $HTML .= '<div class="block relative">';
                                                $HTML .= '<div class="font-medium whitespace-nowrap">£'.number_format($bt->transaction_amount, 2).'</div>';
                                            $HTML .= '</div>';
                                        endif;
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="w-40">';
                                        if($bt->flow == 0):
                                            $HTML .= '<div class="block relative">';
                                                $HTML .= '<div class="font-medium whitespace-nowrap">£'.number_format($bt->transaction_amount, 2).'</div>';
                                            $HTML .= '</div>';
                                        endif;
                                    $HTML .= '</td>';
                                $HTML .= '</tr>';
                            endforeach;
                        $HTML .= '</table>';
                    $HTML .= '</div>';
                endforeach;
            else:
                $HTML .= '<div class="alert alert-dark-soft show flex items-center mb-2" role="alert">';
                    $HTML .= '<i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Nothing found as per your search criteria.';
                $HTML .= '</div>';
            endif;
        else:
            $HTML .= '<div class="alert alert-dark-soft show flex items-center mb-2" role="alert">';
                $HTML .= '<i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Nothing found as per your search criteria.';
            $HTML .= '</div>';
        endif;

        return response()->json(['res' => $HTML], 200);
    }

    public function chartData(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $thisMonth = date('Y-m').'-01';
        $months = [];
        $months[] = $thisMonth;
        for ($i = 1; $i <= 11; $i++):
            $date = date('Y-m-01', strtotime($thisMonth. ' -'.$i.' months'));
            $months[] = $date;
        endfor;
        $all_months = array_reverse($months);
        
        $res = [];
        $totalExpense = 0;
        $totalIncomes = 0;
        if(!empty($all_months)):
            foreach($all_months as $month):
                $monthStart = date('Y-m-01', strtotime($month));
                $monthEnd = date('Y-m-t', strtotime($month));

                $incomes = AccTransaction::whereBetween('transaction_date_2', [$monthStart, $monthEnd])->whereNotIn('acc_category_id', [41, 42])->whereNot('transaction_type', 2)->where('flow', 0)->where('parent', 0)->whereIn('audit_status', $audit_status)->sum('transaction_amount');
                $expense = AccTransaction::whereBetween('transaction_date_2', [$monthStart, $monthEnd])->whereNotIn('acc_category_id', [41, 42])->whereNot('transaction_type', 2)->where('flow', 1)->where('parent', 0)->whereIn('audit_status', $audit_status)->sum('transaction_amount');
                //$deposit = AccTransaction::whereBetween('transaction_date_2', [$monthStart, $monthEnd])->where('transaction_type', 2)->where('transfer_type', 0)->where('parent', 0)->sum('transaction_amount');
                //$withdrawl = AccTransaction::whereBetween('transaction_date_2', [$monthStart, $monthEnd])->where('transaction_type', 2)->where('transfer_type', 1)->where('parent', 0)->sum('transaction_amount');

                $totalIncomes += $incomes;
                $totalExpense += $expense;
                $res['incomes'][] = round($incomes);
                $res['expense'][] = round($expense);
                $res['months'][] = date('M', strtotime($monthStart));
            endforeach;
        endif;
        $res['totalInc'] = ($totalIncomes > 0 ? '£'.number_format($totalIncomes, 2) : '£0.00');
        $res['totalExp'] = ($totalExpense > 0 ? '£'.number_format($totalExpense, 2) : '£0.00');

        return $res;
    }

    public function report($startDate, $endDate){
        return view('pages.accounts.report', [
            'title' => 'Accounts Report - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Accounts Summary', 'href' => route('accounts')],
                ['label' => 'Report', 'href' => 'javascript:void(0);']
            ],
            'banks' => AccBank::where('status', 1)->orderBy('bank_name', 'ASC')->get(),
            'startDate' => date('Y-m-d', strtotime($startDate)),
            'endDate' => date('Y-m-d', strtotime($endDate)),
            'inflows' => $this->inflowReport(date('Y-m-d', strtotime($startDate)), date('Y-m-d', strtotime($endDate))),
            'outflows' => $this->outflowReport(date('Y-m-d', strtotime($startDate)), date('Y-m-d', strtotime($endDate)))
        ]);
    }

    public function inflowReport($startDate, $endDate){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $categories = AccCategory::where('trans_type', 0)->where('status', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();

        $res = [];
        if(!empty($categories)):
            foreach($categories as $cat):
                $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();
                $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $cat->id)->whereIn('audit_status', $audit_status)->get();
                if($inflows->count() > 0 || $outflows->count() > 0):
                    $inf = $inflows->sum('transaction_amount');
                    $otf = $outflows->sum('transaction_amount');
                    $res[$cat->id]['name'] = $cat->category_name;
                    $res[$cat->id]['no_of'] = $inflows->count() + $outflows->count();
                    $res[$cat->id]['sub_total'] = ($inf - $otf);
                endif;
            endforeach;
        endif;

        return $res;
    }

    public function outflowReport($startDate, $endDate){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $parentCategories = AccCategory::where('trans_type', 1)->where('status', 1)->where('parent_id', 0)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();

        $res = [];
        if($parentCategories->count() > 0):
            foreach($parentCategories as $pcat):
                $childCategories = $this->outflowChildCategories($pcat->id);
                $exist = 0;
                if(!empty($childCategories)):
                    foreach($childCategories as $ccat):
                        $inflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        $outflows = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $ccat['id'])->whereIn('audit_status', $audit_status)->get();
                        if($inflows->count() > 0 || $outflows->count() > 0):
                            $inf = $inflows->sum('transaction_amount');
                            $otf = $outflows->sum('transaction_amount');
                            $res[$pcat->id]['childs'][$ccat['id']]['name'] = $ccat['name'];
                            $res[$pcat->id]['childs'][$ccat['id']]['no_of'] = $outflows->count() + $inflows->count();
                            $res[$pcat->id]['childs'][$ccat['id']]['sub_total'] = ($inf - $otf);
                            $exist += 1;
                        endif;
                    endforeach;
                endif;
                $ownInflow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 0)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                $ownOutfolow = AccTransaction::whereBetween('transaction_date_2', [$startDate, $endDate])->where('parent', 0)->where('flow', 1)->where('acc_category_id', $pcat->id)->whereIn('audit_status', $audit_status)->get();
                if($ownInflow->count() > 0 || $ownOutfolow->count() > 0):
                    $inf = $ownInflow->sum('transaction_amount');
                    $otf = $ownOutfolow->sum('transaction_amount');
                    $res[$pcat->id]['childs'][$pcat->id]['name'] = $pcat->category_name;
                    $res[$pcat->id]['childs'][$pcat->id]['no_of'] = $ownInflow->count() + $ownOutfolow->count();
                    $res[$pcat->id]['childs'][$pcat->id]['sub_total'] = ($inf - $otf);
                    $exist += 1;
                endif;
                if($exist > 0):
                    $res[$pcat->id]['name'] = $pcat->category_name;
                endif;
            endforeach;
        endif;

        return $res;
    }

    public function outflowChildCategories($parent_id){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        static $exps = array ();
	    static $levs = 0;
        static $cnt = 0;
	    $levs ++;

        $categories = AccCategory::where('parent_id', $parent_id)->where('trans_type', 1)->whereIn('audit_status', $audit_status)->orderBy('category_name', 'ASC')->get();
        if($categories->count() > 0):
            foreach($categories as $cat):
                $exps[$cnt]['id'] = $cat->id;
                $exps[$cnt]['name'] = $cat->category_name;
                $exps[$cnt]['status'] = $cat->status;
                $exps[$cnt]['trans_type'] = $cat->trans_type;
                $exps[$cnt]['parent_id'] = $cat->parent_id;

                $cnt += 1;
                $this->outflowChildCategories($cat->id);
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

    public function reportDetails(Request $request){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $category_id = $request->category_id;
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));

        $transactions = AccTransaction::whereBetween('transaction_date_2', [$start_date, $end_date])->where('parent', 0)->where('acc_category_id', $category_id)->whereIn('audit_status', $audit_status)->get();
        $subTotal = 0;

        $html = '';
        $html .= '<table class="table table-bordered table-striped table-sm">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>Date</th>';
                    $html .= '<th>TC</th>';
                    $html .= '<th>Invoice</th>';
                    $html .= '<th>Storage</th>';
                    $html .= '<th>Details</th>';
                    $html .= '<th>Description</th>';
                    $html .= '<th class="text-right">Withdrawl</th>';
                    $html .= '<th class="text-right">Deposit</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if($transactions->count() > 0):
                    foreach($transactions as $trns):
                        $html .= '<tr>';
                            $html .= '<td>'.date('jS F, Y', strtotime($trns->transaction_date_2)).'</td>';
                            $html .= '<td>'.$trns->transaction_code.'</td>';
                            $html .= '<td>'.$trns->invoice_no.(!empty($trns->invoice_date) ? '<br/>'.date('jS M, Y', strtotime($trns->invoice_date)) : '').'</td>';
                            $html .= '<td>'.(isset($trns->bank->bank_name) ? $trns->bank->bank_name : '').'</td>';
                            $html .= '<td>'.$trns->detail.'</td>';
                            $html .= '<td>'.$trns->description.'</td>';
                            $html .= '<td class="text-right">'.($trns->flow == 1 ? '£'.number_format($trns->transaction_amount, 2) : '').'</td>';
                            $html .= '<td class="text-right">'.($trns->flow != 1 ? '£'.number_format($trns->transaction_amount, 2) : '').'</td>';
                        $html .= '</tr>';
                        if($trns->flow == 1):
                            $subTotal -= $trns->transaction_amount;
                        else:
                            $subTotal += $trns->transaction_amount;
                        endif;
                    endforeach;
                else:
                    $html .= '<tr>';
                        $html .= '<td colspan="8">';
                            $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                                $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Transactions not found';
                            $html .= '</div>';
                        $html .= '</td>';
                    $html .= '</tr>';
                endif;
            $html .= '</tbody>';
            if($transactions->count() > 0):
                $html .= '<tfoot>';
                    $html .= '<tr>';
                        $html .= '<th colspan="6">Sub Total</th>';
                        $html .= '<th colspan="2" class="text-right">'.($subTotal >= 0 ? '£'.number_format($subTotal, 2) : '-£'.number_format(str_replace('-', '', $subTotal), 2)).'</th>';
                    $html .= '</tr>';
                $html .= '</tfoot>';
            endif;
        $html .= '</table>';

        return response()->json(['res' => $html], 200);
    }
}
