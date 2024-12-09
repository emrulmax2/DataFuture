<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequisitionDocumentStoreRequest;
use App\Models\BudgetRequisitionDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequisitionDocumentController extends Controller
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

        $query = BudgetRequisitionDocument::where('budget_requisition_id', $requisition_id)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('display_file_name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
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
                    'display_file_name' => $list->display_file_name,
                    'current_file_name' => $list->current_file_name,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(RequisitionDocumentStoreRequest $request){
        $budget_requisition_id = $request->budget_requisition_id;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');

        if($request->hasFile('document')):
            foreach($request->file('document') as $file):
                $documentName = 'REQ_'.$budget_requisition_id.'_'.time().'.'.$file->getClientOriginalExtension();
                $path = $file->storeAs('public/requisitions/'.$budget_requisition_id, $documentName, 'local');

                $data = [];
                $data['budget_requisition_id'] = $budget_requisition_id;
                $data['display_file_name'] = (!empty($display_file_name) ? $display_file_name : $documentName);
                $data['hard_copy_check'] = 1;
                $data['doc_type'] = $file->getClientOriginalExtension();
                $data['disk_type'] = 'local';
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                BudgetRequisitionDocument::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Budget requisition document successfully inserted.'], 200);
    }

    public function destroy($id){
        $data = BudgetRequisitionDocument::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = BudgetRequisitionDocument::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = BudgetRequisitionDocument::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        BudgetRequisitionDocument::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function downloadDoc(Request $request){ 
        $row_id = $request->row_id;

        $requisitionDoc = BudgetRequisitionDocument::where('id',$row_id)->withTrashed()->get()->first();
        $budget_requisition_id = $requisitionDoc->budget_requisition_id;
        //$tmpURL = Storage::disk('s3')->temporaryUrl('public/requisitions/'.$budget_requisition_id.'/'.$requisitionDoc->current_file_name, now()->addMinutes(5));
        $tmpURL = Storage::disk('local')->url('public/requisitions/'.$budget_requisition_id.'/'.$requisitionDoc->current_file_name);
        return response()->json(['res' => $tmpURL], 200);
    }
}
