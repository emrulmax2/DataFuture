<?php

namespace App\Http\Controllers\Agent;


use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentDocuments;
use App\Models\AgentUser;
use App\Models\DocumentSettings;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgentDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id){
        $employee = Agent::find($id);
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();

        return view('pages.agent.profile.documents.index',[
            'title' => 'Welcome - London Churchill College',
            'breadcrumbs' => [],
            "employee" => $employee,
            "userData" => $userData,
            "postcodeApi" => $PostCodeAPI,
            'docSettings' => DocumentSettings::where('agent', '1')->get(),
        ]);
    }

    public function list(Request $request){
        $employeeId = (isset($request->employeeId) && !empty($request->employeeId) ? $request->employeeId : 0);
        
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentDocuments::orderByRaw(implode(',', $sorts))->where('agent_id', $employeeId)->where('type', 1);
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

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();
        
        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $url = '';
                /*if(isset($list->current_file_name) && !empty($list->current_file_name) && Storage::disk('s3')->exists('public/employees/'.$list->employee_id.'/documents/'.$list->current_file_name)):
                    $disk = Storage::disk('s3');
                    $url = $disk->url('public/employees/'.$list->employee_id.'/documents/'.$list->current_file_name);
                endif;*/
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (!empty($list->display_file_name) ? $list->display_file_name : 'Unknown'),
                    'hard_copy_check' => $list->hard_copy_check,    
                    'url' => (isset($list->current_file_name) && !empty($list->current_file_name) ? $list->current_file_name : ''),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function communicationList(Request $request){
        $employeeId = (isset($request->employeeId) && !empty($request->employeeId) ? $request->employeeId : 0);
        
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentDocuments::orderByRaw(implode(',', $sorts))->where('agent_id', $employeeId)->where('type', 2);
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

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();
        
        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $url = '';
                /*if(isset($list->current_file_name) && !empty($list->current_file_name) && Storage::disk('s3')->exists('public/employees/'.$list->employee_id.'/documents/'.$list->current_file_name)):
                    $disk = Storage::disk('s3');
                    $url = $disk->url('public/employees/'.$list->employee_id.'/documents/'.$list->current_file_name);
                endif;*/
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (!empty($list->display_file_name) ? $list->display_file_name : 'Unknown'),
                    'hard_copy_check' => $list->hard_copy_check,    
                    'url' => (isset($list->current_file_name) && !empty($list->current_file_name) ? $list->current_file_name : ''),
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function employeeUploadDocument(Request $request){
        
        $employee_id = $request->agent_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $hard_copy_check = $request->hard_copy_check;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/agents/'.$employee_id.'/documents', $imageName, 's3');
        $displayName = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name.(!empty($display_file_name) ? ' - '.$display_file_name : '') : (!empty($display_file_name) ? $display_file_name : $imageName));
        
        $data = [];
        $data['agent_id'] = $employee_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = null; //Storage::disk('s3')->url($path);
        
        $data['display_file_name'] = $displayName;
        $data['current_file_name'] = $imageName;
        $data['type'] = 1;
        $data['created_by'] = auth()->user()->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $employeeDoc = AgentDocuments::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AgentDocuments  $employeeDocuments
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $employee = $request->employee;
        $recordid = $request->recordid;
        $data = AgentDocuments::find($recordid)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $employee = $request->employee;
        $recordid = $request->recordid;
        $data = AgentDocuments::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }

    public function downloadUrl(Request $request){
        $row_id = $request->row_id;

        $empDoc = AgentDocuments::find($row_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/agents/'.$empDoc->agent_id.'/documents/'.$empDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }
}