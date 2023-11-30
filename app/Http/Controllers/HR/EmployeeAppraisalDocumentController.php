<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeApprisalDocument;
use App\Models\EmployeeDocuments;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeAppraisalDocumentController extends Controller
{
    public function index($id, $appraisalid){
        $employee = Employee::find($id);
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id", $id)->get()->first();

        return view('pages.employee.profile.appraisal-document', [
            'title' => 'Employee Appraisals Document - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            'appraisal' => EmployeeAppraisal::find($appraisalid)
        ]);
    }

    public function list(Request $request){
        $employee_id = (isset($request->employee_id) && !empty($request->employee_id) ? $request->employee_id : 0);
        $appraisal_id = (isset($request->appraisal_id) && !empty($request->appraisal_id) ? $request->appraisal_id : 0);
        
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeApprisalDocument::orderByRaw(implode(',', $sorts))->where('employee_appraisal_id', $appraisal_id);
        if(!empty($queryStr)):
            $query->whereHas('document', function($q) use($queryStr){
                $q->where('display_file_name','LIKE','%'.$queryStr.'%');
            });
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
                if(isset($list->document->current_file_name) && !empty($list->document->current_file_name) && Storage::disk('google')->exists('public/employees/'.$employee_id.'/documents/'.$list->document->current_file_name)):
                    $disk = Storage::disk('google');
                    $url = $disk->url('public/employees/'.$employee_id.'/documents/'.$list->document->current_file_name);
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'display_file_name' => (isset($list->document->display_file_name) ? $list->document->display_file_name : 'Unknown'),
                    'hard_copy_check' => (isset($list->document->hard_copy_check) ? $list->document->hard_copy_check : 0),    
                    'url' => $url,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function uploadDocuments(Request $request){
        $employee_id = $request->employee_id;
        $employee_appraisal_id = $request->employee_appraisal_id;
        $hard_copy_check = (isset($request->hard_copy_check) && $request->hard_copy_check > 0 ? $request->hard_copy_check : 0);
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : 'Employee Appraisal Document');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $imageName, 'google');
        $data = [];
        $data['employee_id'] = $employee_id;
        $data['document_setting_id'] = null;
        $data['hard_copy_check'] = $hard_copy_check;
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = Storage::disk('google')->url($path);
        $data['display_file_name'] = $display_file_name;
        $data['current_file_name'] = $imageName;
        $data['created_by'] = auth()->user()->id;
        $employeeDocument = EmployeeDocuments::create($data);

        if($employeeDocument->id):
            $data = [];
            $data['employee_appraisal_id'] = $employee_appraisal_id;
            $data['employee_document_id'] = $employeeDocument->id;
            $data['created_by'] = auth()->user()->id;
            EmployeeApprisalDocument::create($data);
        endif;

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }


    public function destroy(Request $request){
        $recordid = $request->recordid;
        $apprisal = EmployeeApprisalDocument::find($recordid);
        $employee_document_id = $apprisal->employee_document_id;

        EmployeeDocuments::find($employee_document_id)->delete();
        $data = EmployeeApprisalDocument::find($recordid)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $recordid = $request->recordid;
        $EmployeeDocuments = EmployeeApprisalDocument::where('id', $recordid)->withTrashed()->get()->first();
        $employee_document_id = $EmployeeDocuments->employee_document_id;
        EmployeeDocuments::where('id', $employee_document_id)->withTrashed()->restore();

        $data = EmployeeApprisalDocument::where('id', $recordid)->withTrashed()->restore();
        
        response()->json($data);
    }
}
