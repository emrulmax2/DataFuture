<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequisitionItemStoreRequest;
use App\Models\BudgetRequisitionItem;
use Illuminate\Http\Request;

class RequisitionItemController extends Controller
{
    public function list(Request $request){
        $requisition_id = (isset($request->requisition_id) && $request->requisition_id > 0 ? $request->requisition_id : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = BudgetRequisitionItem::where('budget_requisition_id', $requisition_id)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('description','LIKE','%'.$queryStr.'%');
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
                    'description' => $list->description,
                    'quantity' => $list->quantity,
                    'price' => (isset($list->price) && $list->price > 0 ? '£'.number_format($list->price, 2) : '£0.00'),
                    'total' => (isset($list->total) && $list->total > 0 ? '£'.number_format($list->total, 2) : '£0.00'),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(RequisitionItemStoreRequest $request){
        $budget_requisition_id = $request->budget_requisition_id;

        BudgetRequisitionItem::create([
            'budget_requisition_id' => $budget_requisition_id,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->total,
            'active' => 1,
            'created_by' => auth()->user()->id,
        ]);

        return response()->json(['msg' => 'Successfully inserted.'], 200);
    }

    public function edit(BudgetRequisitionItem $item){
        return response()->json($item);
    }

    public function update(RequisitionItemStoreRequest $request){
        $budget_requisition_id = $request->budget_requisition_id;
        $id = $request->id;

        BudgetRequisitionItem::where('id', $id)->where('budget_requisition_id', $budget_requisition_id)->update([
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total' => $request->total,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['msg' => 'Successfully updated.'], 200);
    }

    public function destroy($id){
        $data = BudgetRequisitionItem::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = BudgetRequisitionItem::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = BudgetRequisitionItem::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        BudgetRequisitionItem::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
