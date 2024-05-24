<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunicationTemplateRequest;
use App\Models\CommunicationTemplate;
use Illuminate\Http\Request;

class CommunicationTemplateController extends Controller
{
    public function index()
    {
        return view('pages.settings.communication.index', [
            'title' => 'System Communication Templates - London Churchill College',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Communication Templates', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && $request->querystr != '' ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = CommunicationTemplate::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('content','LIKE','%'.$queryStr.'%');
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

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'type' => ($list->type == 2 ? 'SMS' : 'Email'),
                    'name' => $list->name,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    
    public function store(CommunicationTemplateRequest $request)
    {
        $type = (isset($request->type) && $request->type > 0 ? $request->type : 1);
        $content = ($type == 2 ? $request->sms_content : $request->email_content);
        $letterSet = CommunicationTemplate::create([
            'type' => $request->type,
            'name' => $request->name,
            'content' => $content,
            'status' => 1,
            'created_by' => auth()->user()->id
        ]);
        
        return response()->json(['message' => 'System communication template successfully created.'], 200);
    }

    
    public function edit($id){
        $letterSet = CommunicationTemplate::find($id);
        return response()->json($letterSet);
    }

    
    public function update(CommunicationTemplateRequest $request)
    {
        $id = $request->id;
        $type = (isset($request->type) && $request->type > 0 ? $request->type : 1);
        $content = ($type == 2 ? $request->sms_content : $request->email_content);

        $letterSet = CommunicationTemplate::where('id', $id)->update([
            'type' => $request->type,
            'name' => $request->name,
            'content' => $content,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['message' => 'System Communication Template successfully updated'], 200);
    }

    
    public function destroy($id)
    {
        $data = CommunicationTemplate::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = CommunicationTemplate::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
