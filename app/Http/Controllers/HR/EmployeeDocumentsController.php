<?php

namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeSentMailRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\EmployeeDocuments;
use App\Models\Employee;
use App\Models\Employment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id){
        $employee = Employee::find($id);
        $employment = Employment::where("employee_id",$id)->get()->first();

        return view('pages.employee.profile.documents',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            "employee" => $employee,
            "employment" => $employment,
            'docSettings' => DocumentSettings::where('staff', '1')->get(),
            'emailTemplates' => EmailTemplate::where('hr', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
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

        $query = EmployeeDocuments::orderByRaw(implode(',', $sorts))->where('employee_id', $employeeId)->where('type', 1);
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

        $query = EmployeeDocuments::orderByRaw(implode(',', $sorts))->where('employee_id', $employeeId)->where('type', 2);
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
        $employee_id = $request->employee_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $hard_copy_check = $request->hard_copy_check;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $imageName, 's3');
        $displayName = (isset($documentSetting->name) && !empty($documentSetting->name) ? $documentSetting->name.(!empty($display_file_name) ? ' - '.$display_file_name : '') : (!empty($display_file_name) ? $display_file_name : $imageName));
        
        $data = [];
        $data['employee_id'] = $employee_id;
        $data['document_setting_id'] = ($document_setting_id > 0 ? $document_setting_id : 0);
        $data['hard_copy_check'] = ($hard_copy_check > 0 ? $hard_copy_check : 0);
        $data['doc_type'] = $document->getClientOriginalExtension();
        $data['path'] = null; //Storage::disk('s3')->url($path);
        
        $data['display_file_name'] = $displayName;
        $data['current_file_name'] = $imageName;
        $data['type'] = 1;
        $data['created_by'] = auth()->user()->id;
        $data['created_at'] = date('Y-m-d H:i:s');
        $employeeDoc = EmployeeDocuments::create($data);

        return response()->json(['message' => 'Document successfully uploaded.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeDocuments  $employeeDocuments
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $employee = $request->employee;
        $recordid = $request->recordid;
        $data = EmployeeDocuments::find($recordid)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $employee = $request->employee;
        $recordid = $request->recordid;
        $data = EmployeeDocuments::where('id', $recordid)->withTrashed()->restore();

        response()->json($data);
    }

    public function downloadUrl(Request $request){
        $row_id = $request->row_id;

        $empDoc = EmployeeDocuments::find($row_id);
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/employees/'.$empDoc->employee_id.'/documents/'.$empDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }

    public function employeeSentMail(EmployeeSentMailRequest $request){
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        $email_body = $request->email_body;
        $document_name = $request->document_name;
        $hard_copy_check_status = (isset($request->hard_copy_check_status) && $request->hard_copy_check_status > 0 ? $request->hard_copy_check_status : 0);

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
            'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
            'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
            'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
            'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
            
            'from_email'    => 'hr@lcc.ac.uk',
            'from_name'    =>  'HR London Churchill College',
        ];

        $mailTo = [];
        $mailTo[] = $employee->email;
        if(isset($employee->employment->email) && !empty($employee->employment->email)):
            $mailTo[] = $employee->employment->email;
        endif;

        if($request->hasFile('document')):
            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $documentName, 's3');

            $data = [];
            $data['employee_id'] = $employee_id;
            $data['document_setting_id'] = null;
            $data['hard_copy_check'] = $hard_copy_check_status;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('s3')->url($path);
            $data['display_file_name'] = (!empty($document_name) ? $document_name : $documentName);
            $data['current_file_name'] = $documentName;
            $data['type'] = 2;
            $data['mail_content'] = $email_body;
            $data['created_by'] = auth()->user()->id;
            $insert = EmployeeDocuments::create($data);

            if($insert->id):
                $MAILBODY = $email_body;
                $MAILBODY = str_replace('[EMPLOYEE_FULL_NAME]', $employee->full_name, $MAILBODY);

                $attachmentFiles = [];
                $attachmentFiles[] = [
                    "pathinfo" => 'public/employees/'.$employee_id.'/documents/'.$documentName,
                    "nameinfo" => $documentName,
                    "mimeinfo" => $document->getMimeType(),
                    "disk" => 's3'
                ];

                UserMailerJob::dispatch($configuration, $mailTo, new CommunicationSendMail($document_name, $MAILBODY, $attachmentFiles));
            endif;

            return response()->json(['suc' => 1, 'msg' => 'Employee maill successfully sent'], 200);
        else:
            return response()->json(['suc' => 2, 'msg' => 'Something went wrong. Please try later.'], 200);
        endif;
    }

    public function employeeGetTemplate(Request $request){
        $the_template_id = $request->the_template_id;
        $template = EmailTemplate::find($the_template_id);

        return response()->json(['row' => $template], 200);
    }
}
