<?php

namespace App\Http\Controllers\Budget\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetYearStoreRequest;
use App\Models\BudgetYear;
use Illuminate\Http\Request;

class BudgetYearController extends Controller
{
    public function index()
    {
        return view('pages.budget.settings.budget-year', [
            'title' => 'Budget Settings - London Churchill College',
            'subtitle' => 'Budget Year Settings',
            'breadcrumbs' => [
                ['label' => 'Budget Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Budget Year', 'href' => 'javascript:void(0);'],
            ],
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

        $query = BudgetYear::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('title','LIKE','%'.$queryStr.'%');
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
                    'title' => $list->title,
                    'start_date' => (!empty($list->start_date) ? date('jS F, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (!empty($list->end_date) ? date('jS F, Y', strtotime($list->end_date)) : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(BudgetYearStoreRequest $request){
        $data = BudgetYear::create([
            'title'=> $request->title,
            'start_date'=> (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date'=> (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = BudgetYear::find($id);

        return response()->json($data);
    }

    public function update(BudgetYearStoreRequest $request){      
        $data = BudgetYear::where('id', $request->id)->update([
            'title'=> $request->title,
            'start_date'=> (isset($request->start_date) && !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date'=> (isset($request->end_date) && !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id){
        $data = BudgetYear::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = BudgetYear::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $year = BudgetYear::find($id);
        $active = (isset($year->active) && $year->active == 1 ? 0 : 1);

        BudgetYear::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
