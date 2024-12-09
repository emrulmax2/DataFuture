<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetRequisition;
use App\Models\BudgetSet;
use App\Models\BudgetSetDetail;
use App\Models\BudgetYear;
use Illuminate\Http\Request;

class BudgetReportController extends Controller
{
    public function index(){
        return view('pages.budget.report.index', [
            'title' => 'Budget Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Budget Management', 'href' => route('budget.management')],
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
            ],
            'years' => BudgetYear::orderBy('start_date', 'DESC')->get(),
        ]);
    }

    public function details(BudgetYear $year, BudgetSet $set, BudgetSetDetail $set_detail){
        return view('pages.budget.report.details', [
            'title' => 'Budget Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Budget Management', 'href' => route('budget.management')],
                ['label' => 'Reports', 'href' => route('budget.management.reports')],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'year' => $year,
            'set' => $set,
            'set_details' => $set_detail,
            'requisitions' => BudgetRequisition::where('budget_year_id', $year)->where('budget_set_id', $set->id)->where('budget_set_detail_id', $set_detail->id)->where('active', '>', 0)->get()
        ]);
    }

    public function detailsList(Request $request){
        $date_range = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        $start_date = (isset($date_range[0]) && !empty($date_range[0]) ? date('Y-m-d', strtotime($date_range[0])) : '');
        $end_date = (isset($date_range[1]) && !empty($date_range[1]) ? date('Y-m-d', strtotime($date_range[1])) : '');
        $budget_year_ids = (isset($request->budget_year_ids) && $request->budget_year_ids > 0 ? $request->budget_year_ids : 0);
        $budget_name_ids = (isset($request->budget_name_ids) && $request->budget_name_ids > 0 ? $request->budget_name_ids : 0);
        $active = (isset($request->req_active) ? $request->req_active : 6);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = BudgetRequisition::with('year', 'budget', 'requisitioners', 'vendor')->orderByRaw(implode(',', $sorts));
        if($budget_year_ids > 0):
            $query->where('budget_year_id', $budget_year_ids);
        endif;
        if($budget_name_ids > 0):
            $query->whereHas('budget', function($q) use($budget_name_ids){
                $q->where('budget_name_id', $budget_name_ids);
            });
        endif;
        if(!empty($start_date) && !empty($end_date)):
            $query->where(function($q) use($start_date, $end_date){
                $q->whereBetween('date', [$start_date, $end_date])->orWhereBetween('required_by', [$start_date, $end_date]);
            });
        endif;
        if($active == 5):
            $query->onlyTrashed();
        elseif($active < 5):
            $query->where('active', $active);
        endif;

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'date' => (!empty($list->date) ? date('jS M, Y', strtotime($list->date)) : ''),
                    'required_by' => (!empty($list->required_by) ? date('jS M, Y', strtotime($list->required_by)) : ''),
                    'year' => (isset($list->year->title) && !empty($list->year->title) ? $list->year->title : ''),
                    'budget' => (isset($list->budget->names->name) && !empty($list->budget->names->name) ? $list->budget->names->name.(isset($list->budget->names->code) && !empty($list->budget->names->code) ? ' ('.$list->budget->names->code.')' : '') : ''),
                    'total' => (isset($list->items) && $list->items->count() > 0 ? '£'.number_format($list->items->sum('total'), 2) : '£0.00'),
                    'paid' => ($list->active == 4 && isset($list->transanctions_total) && $list->transanctions_total > 0 ? '£'.number_format($list->transanctions_total, 2) : ''),
                    'requisitioners' => (isset($list->requisitioners->employee->full_name) && !empty($list->requisitioners->employee->full_name) ? $list->requisitioners->employee->full_name : $list->requisitioners->name),
                    'vendor' => (isset($list->vendor->name) && !empty($list->vendor->name) ? $list->vendor->name : ''),
                    'venue' => (isset($list->venue->name) && !empty($list->venue->name) ? $list->venue->name : ''),
                    'active' => $list->active,
                    'deleted_at' => $list->deleted_at,
                    'url' => route('budget.management.show.req', $list->id)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generate(Request $request){
        $budget_year_id = $request->budget_year_id;
        $budgetYear = BudgetYear::find($budget_year_id);
        $startDate = date('jS F, Y', strtotime($budgetYear->start_date));
        $endDate = date('jS F, Y', strtotime($budgetYear->end_date));
        $budget_set = BudgetSet::where('budget_year_id', $budget_year_id)->get()->first();

        $html = '';
        $totalbudget = $totalRequisition = $totalBalance = $TotalPaid = 0;
        if(isset($budget_set->id) && $budget_set->id > 0):
            $html .= '<table class="table table-bordered table-striped">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th colspan="6" class="text-lg font-medium text-center uppercase font-semibold">Delegated budget '.$startDate.' - '.$endDate.'</th>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                        $html .= '<th>SL</th>';
                        $html .= '<th>Budget</th>';
                        $html .= '<th>Total</th>';
                        $html .= '<th>Requisition</th>';
                        $html .= '<th>Paid</th>';
                        $html .= '<th>Balance</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    if(isset($budget_set->details) && $budget_set->details->count() > 0):
                        $sl = 1;
                        foreach($budget_set->details as $budget):
                            $requisitions = BudgetRequisition::where('budget_year_id', $budget_year_id)->where('budget_set_id', $budget_set->id)->where('budget_set_detail_id', $budget->id)->where('active', '>', 0)->get();
                            $requisitionTotal = ($requisitions->count() > 0 ? $requisitions->sum('requisition_total') : 0);
                            $paidTotal = ($requisitions->count() > 0 ? $requisitions->sum('transanctions_total') : 0);
                            
                            $budgetAmount = (isset($budget->amount) && $budget->amount > 0 ? $budget->amount : 0);
                            $balance = ($budgetAmount - $paidTotal);
                            $html .= '<tr class="cursor-pointer budgetReportRow" data-url="'.route('budget.management.reports.details', [$budget_year_id, $budget_set->id, $budget->id]).'">';
                                $html .= '<td>'.$sl.'</td>';
                                $html .= '<td>'.(isset($budget->names->name) && !empty($budget->names->name) ? $budget->names->name : '').'</td>';
                                $html .= '<td>£'.number_format($budgetAmount, 2).'</td>';
                                $html .= '<td>£'.number_format($requisitionTotal, 2).'</td>';
                                $html .= '<td>£'.number_format($paidTotal, 2).'</td>';
                                $html .= '<td>£'.number_format($balance, 2).'</td>';
                            $html .= '</tr>';

                            $totalbudget += $budgetAmount;
                            $totalRequisition += $requisitionTotal;
                            $totalBalance += $balance;
                            $TotalPaid += $paidTotal;

                            $sl++;
                        endforeach;
                    else:
                        $html .= '<tr>';
                            $html .= '<td>';
                                $html .= '<div class="transAlert alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Budgets not for the selected year. </div>';
                            $html .= '</td>';
                        $html .= '</tr>';
                    endif;
                $html .= '</tbody>';
                if(isset($budget_set->details) && $budget_set->details->count() > 0):
                $html .= '<tfoot>';
                    $html .= '<tr>';
                        $html .= '<th colspan="2">Total</th>';
                        $html .= '<th>£'.number_format($totalbudget, 2).'</th>';
                        $html .= '<th>£'.number_format($totalRequisition, 2).'</th>';
                        $html .= '<th>£'.number_format($TotalPaid, 2).'</th>';
                        $html .= '<th>£'.number_format($totalBalance, 2).'</th>';
                    $html .= '</tr>';
                $html .= '</tfoot>';
                endif;
            $html .= '</table>';
        else:
            $html .= '<div class="transAlert alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Budget Set not for the selected year. </div>';
        endif;

        return response()->json(['htm' => $html], 200);
    }
}
