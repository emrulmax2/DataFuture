<?php

namespace App\Http\Controllers\Budget\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetSetStoreRequest;
use App\Models\BudgetName;
use App\Models\BudgetSet;
use App\Models\BudgetSetDetail;
use App\Models\BudgetYear;
use App\Models\User;
use Illuminate\Http\Request;

class BudgetSetController extends Controller
{
    public function index()
    {
        return view('pages.budget.settings.budget-set', [
            'title' => 'Budget Settings - London Churchill College',
            'subtitle' => 'Budget Year Settings',
            'breadcrumbs' => [
                ['label' => 'Budget Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Budget Year', 'href' => 'javascript:void(0);'],
            ],
            'years' => BudgetYear::with('budget')->where('active', 1)->orderBy('start_date', 'DESC')->get(),
            'names' => BudgetName::where('active', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
        $budget_year_id = (isset($request->budget_year_id) && $request->budget_year_id > 0 ? $request->budget_year_id : 0);
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = BudgetSet::with('year')->orderByRaw(implode(',', $sorts));
        if($budget_year_id > 0):
            $query->where('budget_year_id', $budget_year_id);
        endif;
        if($status == 2):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
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
                    'budget_year_id' => (isset($list->year->title) && !empty($list->year->title) ? $list->year->title : ''),
                    'amount' => (isset($list->details) && $list->details->count() > 0 ? '£'.number_format($list->details->sum('amount'), 2) : '£0.00'),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(BudgetSetStoreRequest $request){
        $budget_year_id = $request->budget_year_id;
        $budget_name_ids = (isset($request->budget_name_ids) && !empty($request->budget_name_ids) ? $request->budget_name_ids : []);
        $budgets = (isset($request->budgets) && !empty($request->budgets) ? $request->budgets : []);

        $budgetSet = BudgetSet::create([
            'budget_year_id' => $budget_year_id,
            'active' => 1,
            'created_by' => auth()->user()->id
        ]);
        if($budgetSet->id && !empty($budget_name_ids)):
            foreach($budget_name_ids as $budget_name_id):
                if(isset($budgets[$budget_name_id]) && !empty($budgets[$budget_name_id])):
                    BudgetSetDetail::create([
                        'budget_set_id' => $budgetSet->id,
                        'budget_name_id' => $budget_name_id,
                        'amount' => (isset($budgets[$budget_name_id]['amount']) && $budgets[$budget_name_id]['amount'] > 0 ? $budgets[$budget_name_id]['amount'] : 0),
                        'created_by' => auth()->user()->id
                    ]);
                endif;
            endforeach;
            return response()->json(['msg' => 'Budget details successfully inserted.'], 200);
        else:
            return response()->json(['msg' => 'Seomething went wrong. Please try again later or contact with the administrator'], 422);
        endif;
    }

    public function edit($id){
        $budgetSet = BudgetSet::with('details')->find($id);
        $html = '';
        if(isset($budgetSet->details) && $budgetSet->details->count() > 0):
            foreach($budgetSet->details as $dt):
                $html .= '<tr class="budget_row" id="budget_row_'.$dt->budget_name_id.'">';
                    $html .= '<td>';
                        $html .= $dt->names->name.(!empty($dt->names->code) ? ' ('.$dt->names->code.')' : '');
                        $html .= '<input type="hidden" step="any" value="'.$dt->budget_name_id.'" name="budgets['.$dt->budget_name_id.'][id]" class="w-full form-control"/>';
                    $html .= '</td>';
                    $html .= '<td>';
                        $html .= '<input type="number" step="any" value="'.($dt->amount > 0 ? $dt->amount : '').'" name="budgets['.$dt->budget_name_id.'][amount]" class="w-full form-control budget_amount"/>';
                    $html .= '</td>';
                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr class="noticeRow"><td colspan="2"><div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Budget details not available.</div></td></tr>';
        endif;
        $budgetSet['details_html'] = $html;

        return response()->json($budgetSet);
    }

    public function update(BudgetSetStoreRequest $request){
        $id = $request->id;
        $budget_year_id = $request->budget_year_id;
        $budget_name_ids = (isset($request->budget_name_ids) && !empty($request->budget_name_ids) ? $request->budget_name_ids : []);
        $budgets = (isset($request->budgets) && !empty($request->budgets) ? $request->budgets : []);
        $existingBNI = BudgetSetDetail::where('budget_set_id', $id)->pluck('budget_name_id')->unique()->toArray();
        $removedBNI = array_diff($existingBNI, $budget_name_ids);

        $budgetSet = BudgetSet::where('id', $id)->update([
            'budget_year_id' => $budget_year_id,
            'updated_by' => auth()->user()->id
        ]);

        if(!empty($removedBNI)):
            BudgetSetDetail::where('budget_set_id', $id)->whereIn('budget_name_id', $removedBNI)->forceDelete();
        endif;
        if(!empty($budget_name_ids)):
            foreach($budget_name_ids as $budget_name_id):
                if(isset($budgets[$budget_name_id]) && !empty($budgets[$budget_name_id])):
                    $exist = BudgetSetDetail::where('budget_set_id', $id)->where('budget_name_id', $budget_name_id)->get()->count();
                    $data = [];
                    $data = [
                        'budget_set_id' => $id,
                        'budget_name_id' => $budget_name_id,
                        'amount' => (isset($budgets[$budget_name_id]['amount']) && $budgets[$budget_name_id]['amount'] > 0 ? $budgets[$budget_name_id]['amount'] : 0),
                    ]; 

                    if($exist > 0):
                        $data['updated_by'] = auth()->user()->id;
                        BudgetSetDetail::where('budget_set_id', $id)->where('budget_name_id', $budget_name_id)->update($data);
                    esle:
                        $data['created_by'] = auth()->user()->id;
                        BudgetSetDetail::create($data);
                    endif;
                endif;
            endforeach;
        endif;
        
        return response()->json(['msg' => 'Updated successfully'], 200);
    }

    public function destroy($id){
        $budget = BudgetSet::find($id)->delete();
        $budgetDetails = BudgetSetDetail::where('budget_set_id', $id)->delete();
        return response()->json($budget);
    }

    public function restore($id) {
        $budgetDetails = BudgetSetDetail::where('budget_set_id', $id)->withTrashed()->restore();
        $budget = BudgetSet::where('id', $id)->withTrashed()->restore();

        response()->json($budget);
    }

    public function updateStatus($id){
        $year = BudgetSet::find($id);
        $active = (isset($year->active) && $year->active == 1 ? 0 : 1);

        BudgetSet::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function getBudgetRow(Request $request){
        $theYear = $request->theYear;
        $budgetSet = BudgetSet::where('budget_year_id', $theYear)->get()->first();
        $theBudgetNameId = $request->theBudgetNameId;
        $theBudgetName = BudgetName::find($theBudgetNameId);

        $amount = '';
        if(isset($budgetSet->id) && $budgetSet->id > 0):
            $budgetSetDetail = BudgetSetDetail::where('budget_set_id', $budgetSet->id)->where('budget_name_id', $theBudgetNameId)->get()->first();
            if(isset($budgetSetDetail->id) && $budgetSetDetail->id > 0):
                $amount = (isset($budgetSetDetail->amount) && $budgetSetDetail->amount > 0 ? $budgetSetDetail->amount : '');
            endif;
        endif;

        $html = '';
        $html .= '<tr class="budget_row" id="budget_row_'.$theBudgetNameId.'">';
            $html .= '<td>';
                $html .= $theBudgetName->name.(!empty($theBudgetName->code) ? ' ('.$theBudgetName->code.')' : '');
                $html .= '<input type="hidden" step="any" value="'.$theBudgetNameId.'" name="budgets['.$theBudgetNameId.'][id]" class="w-full form-control"/>';
            $html .= '</td>';
            $html .= '<td>';
                $html .= '<input type="number" step="any" value="'.$amount.'" name="budgets['.$theBudgetNameId.'][amount]" class="w-full form-control budget_amount"/>';
            $html .= '</td>';
        $html .= '</tr>';

        return response()->json(['html' => $html], 200);
    }
}
