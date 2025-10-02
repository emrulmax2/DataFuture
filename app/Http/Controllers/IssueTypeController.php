<?php

namespace App\Http\Controllers;
use App\Models\IssueType;
use App\Http\Requests\StoreIssueTypeRequest;
use App\Http\Requests\UpdateIssueTypeRequest;
use App\Http\Requests\UpdatereportItStudentRequest;
use App\Models\ComonSmtp;
use Illuminate\Http\Request;

class IssueTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        $smtps = ComonSmtp::all();

        return view('pages.settings.issue_type.index', [
            'title' => 'Report IT issue Type - London Churchill College',
            'subtitle' => 'Report IT issue Type',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Report IT issue Type', 'href' => 'javascript:void(0);']
            ],
            'smtps' => $smtps
        ]);
    }
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = IssueType::count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = IssueType::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

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
                    'availability' => $list->availability,
                    'smtp_user' => isset($list->smtp) ? $list->smtp->smtp_user : '',
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(StoreIssueTypeRequest $request){

        $data = IssueType::create([
            'name'=> $request->name,
            'availability' => $request->availability,
            'comon_smtp_id' => $request->comon_smtp_id,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit(IssueType $issueType){
        $data = $issueType;

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(UpdateIssueTypeRequest $request, IssueType $issueType){      
        $data = $issueType->update([
            'name'=> $request->name,
            'availability' => $request->availability,
            'comon_smtp_id' => $request->comon_smtp_id,
            'updated_by' => auth()->user()->id
        ]);
        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    public function destroy($id){

        $data = IssueType::find($id)->delete();
        return response()->json($data);
    }

    public function forceDelete($id) 
    {
        $data = IssueType::where('id', $id)->withTrashed()->forceDelete();

        return response()->json($data);
    }

    public function restore($id) {
        $data = IssueType::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }

}
