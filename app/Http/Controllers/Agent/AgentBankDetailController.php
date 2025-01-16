<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentBankStoreRequest;
use App\Models\AgentBankDetail;
use Illuminate\Http\Request;

class AgentBankDetailController extends Controller
{
    public function list(Request $request){
        $agent_id = (isset($request->agent_id) && $request->agent_id > 0 ? $request->agent_id : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentBankDetail::orderByRaw(implode(',', $sorts))->where('agent_id', $agent_id);
        if(!empty($queryStr)):
            $query->where('beneficiary','LIKE','%'.$queryStr.'%');
            $query->where('sort_code','LIKE','%'.$queryStr.'%');
            $query->where('ac_no','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        elseif($status == 1 || $status == 0):
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
                    'beneficiary' => $list->beneficiary,
                    'sort_code' => $list->sort_code,
                    'ac_no' => $list->ac_no,
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(AgentBankStoreRequest $request){
        $agent_id = $request->agent_id;
        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);

        $request->request->remove('active');
        $request->request->add(['active' => $active, 'created_by' => auth()->user()->id]);

        $bank = AgentBankDetail::create($request->all());
        $bankId = $bank->id;

        if($active == 1){
            AgentBankDetail::where('id', '!=', $bankId)->where('agent_id', $agent_id)->where('active', 1)->update(['active' => 0]);
        }

        return response()->json(['msg' => 'Bank Successfully inserted'], 200);
    }

    public function edit(Request $request){
        $editId = $request->editId;
        $bank = AgentBankDetail::find($editId);

        return response()->json(['res' => $bank], 200);
    }

    public function update(AgentBankStoreRequest $request){
        $id = $request->id;
        $agent_id = $request->agent_id;
        $active = (isset($request->active) && $request->active > 0 ? $request->active : 0);
        $bankOld = AgentBankDetail::find($id);

        $request->request->remove('active');
        $request->request->add(['active' => $active, 'updated_by' => auth()->user()->id]);

        $bank = AgentBankDetail::find($id);
        $bank->fill($request->input());
        $bank->save();

        if($active == 1){
            AgentBankDetail::where('id', '!=', $id)->where('agent_id', $agent_id)->where('active', 1)->update(['active' => 0]);
        }

        return response()->json(['msg' => 'Agent Bank Details Successfully updated'], 200);
    }

    public function destroy($id){
        $bankDetails = AgentBankDetail::find($id);
        $bankDetails->fill(['active' => 0]);
        $bankDetails->save();
        $bankDetails->delete();
        return response()->json($bankDetails);
    }

    public function restore($id) {
        $data = AgentBankDetail::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function changeStatus($id){
        $title = AgentBankDetail::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        AgentBankDetail::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        if($active == 1):
            AgentBankDetail::where('id', '!=', $id)->where('active', 1)->update(['active' => 0]);
        endif;

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
