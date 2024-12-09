<?php

namespace App\Http\Controllers\Budget\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetNameStoreRequest;
use App\Models\BudgetName;
use App\Models\BudgetNameApprover;
use App\Models\BudgetNameHolder;
use App\Models\BudgetNameRequester;
use App\Models\User;
use Illuminate\Http\Request;

class BudgetNameController extends Controller
{
    public function index()
    {
        return view('pages.budget.settings.budget-name', [
            'title' => 'Budget Settings - London Churchill College',
            'subtitle' => 'Budget Name Settings',
            'breadcrumbs' => [
                ['label' => 'Budget Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Budget Name', 'href' => 'javascript:void(0);'],
            ],
            'users' => User::with('employee')->where('active', 1)->orderBy('name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = BudgetName::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('name','LIKE','%'.$queryStr.'%')->orWhere('code','LIKE','%'.$queryStr.'%');
            });
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
                    'name' => $list->name,
                    'code' => $list->code,
                    'holders_html' => (isset($list->holder_html) && !empty($list->holder_html) ? $list->holder_html : ''),
                    'requester_html' => (isset($list->requester_html) && !empty($list->requester_html) ? $list->requester_html : ''),
                    'approvers_html' => (isset($list->approvers_html) && !empty($list->approvers_html) ? $list->approvers_html : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(BudgetNameStoreRequest $request){
        $budgetName = BudgetName::create([
            'name'=> $request->name,
            'code'=> (isset($request->code) && !empty($request->code) ? $request->code : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'created_by' => auth()->user()->id
        ]);

        if($budgetName->id):
            if(isset($request->budget_holder_ids) && !empty($request->budget_holder_ids)):
                foreach($request->budget_holder_ids as $user_id):
                    BudgetNameHolder::create([
                        'budget_name_id' => $budgetName->id,
                        'user_id' => $user_id
                    ]);
                endforeach;
            endif;
            if(isset($request->budget_requester_ids) && !empty($request->budget_requester_ids)):
                foreach($request->budget_requester_ids as $user_id):
                    BudgetNameRequester::create([
                        'budget_name_id' => $budgetName->id,
                        'user_id' => $user_id
                    ]);
                endforeach;
            endif;
            if(isset($request->budget_approver_ids) && !empty($request->budget_approver_ids)):
                foreach($request->budget_approver_ids as $user_id):
                    BudgetNameApprover::create([
                        'budget_name_id' => $budgetName->id,
                        'user_id' => $user_id
                    ]);
                endforeach;
            endif;
            return response()->json(['msg' => 'Inserted successfully'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or conatact with the administrator.'], 422);
        endif;
    }

    public function edit($id){
        $data = BudgetName::with('holders', 'requesters', 'approvers')->find($id);

        return response()->json($data);
    }

    public function update(BudgetNameStoreRequest $request){   
        $id = $request->id;   
        $data = BudgetName::where('id', $id)->update([
            'name'=> $request->name,
            'code'=> (isset($request->code) && !empty($request->code) ? $request->code : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'updated_by' => auth()->user()->id
        ]);
        
        BudgetNameHolder::where('budget_name_id', $id)->forceDelete();
        if(isset($request->budget_holder_ids) && !empty($request->budget_holder_ids)):
            foreach($request->budget_holder_ids as $user_id):
                BudgetNameHolder::create([
                    'budget_name_id' => $id,
                    'user_id' => $user_id
                ]);
            endforeach;
        endif;

        BudgetNameRequester::where('budget_name_id', $id)->forceDelete();
        if(isset($request->budget_requester_ids) && !empty($request->budget_requester_ids)):
            foreach($request->budget_requester_ids as $user_id):
                BudgetNameRequester::create([
                    'budget_name_id' => $id,
                    'user_id' => $user_id
                ]);
            endforeach;
        endif;

        BudgetNameApprover::where('budget_name_id', $id)->forceDelete();
        if(isset($request->budget_approver_ids) && !empty($request->budget_approver_ids)):
            foreach($request->budget_approver_ids as $user_id):
                BudgetNameApprover::create([
                    'budget_name_id' => $id,
                    'user_id' => $user_id
                ]);
            endforeach;
        endif;

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id){
        $data = BudgetName::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = BudgetName::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $year = BudgetName::find($id);
        $active = (isset($year->active) && $year->active == 1 ? 0 : 1);

        BudgetName::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
