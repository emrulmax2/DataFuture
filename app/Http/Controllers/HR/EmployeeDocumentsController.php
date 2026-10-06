<?php

namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeSentMailRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\DocumentSettings;
use App\Models\EmailTemplate;
use App\Models\EmployeeDocumentAccessLog;
use App\Models\EmployeeDocuments;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\HrHolidayYear;
use App\Models\PaySlipUploadSync;
use App\Services\StaffDocumentVaultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;

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
        $holidayList = PaySlipUploadSync::where('employee_id', $id)->whereNotNull('file_transffered_at')->pluck('holiday_year_id')->toArray();
        if(count($holidayList) > 0):
            $holidayList = array_unique($holidayList);
            $holidayDetails = HrHolidayYear::whereIn('id', $holidayList)->orderBy('id', 'DESC')->get();
        else:
            $holidayDetails = [];
        endif;
        return view('pages.employee.profile.documents',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            "employee" => $employee,
            "holidayDetails" => $holidayDetails,
            "employment" => $employment,
            'docSettings' => DocumentSettings::where('staff', '1')->get(),
            'emailTemplates' => EmailTemplate::where('hr', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
            'vault' => $this->vaultState(),
        ]);
    }

    /**
     * What the page needs to know before asking for a PIN. The server checks
     * all of it again when a document is actually opened.
     *
     * The permission and the PIN are the holder's: in an impersonated
     * session that is the impersonator, not the account they are signed in as.
     */
    protected function vaultState(){
        $holder = StaffDocumentVaultService::holder();
        $impersonating = StaffDocumentVaultService::isImpersonating();

        return [
            'can_use' => ($holder ? StaffDocumentVaultService::canUse($holder) : false),
            'has_pin' => ($holder ? app(StaffDocumentVaultService::class)->hasPin($holder) : false),
            'impersonating' => $impersonating,
            'holder_name' => ($impersonating && $holder ? $holder->full_name : ''),
            'signed_in_as' => ($impersonating ? auth()->user()->full_name : ''),
            'pin_max_length' => StaffDocumentVaultService::PIN_MAX_LENGTH,
        ];
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
                    'deleted_at' => $list->deleted_at,
                    'hasNote' => (isset($list->note->id) && $list->note->id > 0 ? 1 : 0),
                    'is_encrypted' => ($list->is_encrypted == 1 ? 1 : 0),
                    'viewable' => (StaffDocumentVaultService::isViewable($list) ? 1 : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json([
            'current_page' => $page,
            'last_page' => $last_page,
            'per_page' => $perpage,
            'total_rows' => $total_rows,
            'data' => $data
        ]);
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
        return response()->json([
            'current_page' => $page,
            'last_page' => $last_page,
            'per_page' => $perpage,
            'total_rows' => $total_rows,
            'data' => $data
        ]);
    }

    public function employeeUploadDocument(Request $request){
        $employee_id = $request->employee_id;
        $document_setting_id = $request->document_setting_id;
        $documentSetting = DocumentSettings::find($document_setting_id);
        $hard_copy_check = $request->hard_copy_check;
        $display_file_name = (isset($request->display_file_name) && !empty($request->display_file_name) ? $request->display_file_name : '');
        $is_encrypted = (isset($request->is_encrypted) && $request->is_encrypted == 1 ? 1 : 0);

        $document = $request->file('file');
        $imageName = time().'_'.$document->getClientOriginalName();
        if($is_encrypted):
            // Stored encrypted, under a name that says so. Nothing readable
            // ever reaches the bucket, so no storage link can leak the file.
            $imageName .= StaffDocumentVaultService::EXTENSION;
            $stored = Storage::disk('s3')->put(
                'public/employees/'.$employee_id.'/documents/'.$imageName,
                app(StaffDocumentVaultService::class)->encrypt(file_get_contents($document->getRealPath()))
            );
            if(!$stored):
                return response()->json(['message' => 'The encrypted document could not be stored. Please try again.'], 500);
            endif;
        else:
            $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $imageName, 's3');
        endif;
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
        $data['is_encrypted'] = $is_encrypted;
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

        return response()->json($data);
    }

    public function downloadUrl(Request $request){
        $row_id = $request->row_id;
        $has_note = (isset($request->has_note) && $request->has_note > 0 ? $request->has_note : 0);

        // Archived rows keep their download button, so they are looked up too.
        $empDoc = EmployeeDocuments::withTrashed()->find($row_id);
        if(!$empDoc):
            return response()->json(['message' => 'Document not found.'], 404);
        endif;

        // An encrypted document never gets a storage link: the stored file is
        // ciphertext, and it is only handed over by openEncrypted() below.
        if($empDoc->is_encrypted == 1):
            return response()->json(['message' => 'This document is encrypted. Enter your document PIN to open it.'], 423);
        endif;

        if($has_note):
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/employees/notes/'.$empDoc->current_file_name, now()->addMinutes(5));
        else:
            $tmpURL = Storage::disk('s3')->temporaryUrl('public/employees/'.$empDoc->employee_id.'/documents/'.$empDoc->current_file_name, now()->addMinutes(5));
        endif;

        // Every open goes on record, every time. The link is only handed
        // over once it has: no trail, no document.
        if(!app(StaffDocumentVaultService::class)->log(StaffDocumentVaultService::EVENT_DOWNLOAD, $empDoc)):
            return response()->json(['message' => StaffDocumentVaultService::NOT_RECORDED], 500);
        endif;

        return response()->json(['res' => $tmpURL], 200);
    }

    /**
     * Decrypt an encrypted document and hand it over, once the person at the
     * keyboard has proved who they are with their own document PIN. Someone
     * signed in as another user enters their own PIN, not that user's, and
     * the open is logged as theirs, through impersonation.
     *
     * The file is streamed from here rather than linked to: there is no URL
     * that would still work for somebody else, or after this request.
     */
    public function openEncrypted(Request $request){
        $vault = app(StaffDocumentVaultService::class);
        $mode = (isset($request->mode) && $request->mode == 'view' ? 'view' : 'download');

        $empDoc = EmployeeDocuments::withTrashed()->find($request->row_id);
        if(!$empDoc || $empDoc->is_encrypted != 1):
            return response()->json(['message' => 'Document not found.'], 404);
        endif;

        $challenge = $vault->challengeForDocument($request->pin, $empDoc);
        if(!$challenge['ok']):
            return response()->json(['message' => $challenge['message']], $challenge['status']);
        endif;

        $payload = Storage::disk('s3')->get('public/employees/'.$empDoc->employee_id.'/documents/'.$empDoc->current_file_name);
        $contents = (!empty($payload) ? $vault->decrypt($payload) : null);
        if($contents === null):
            Log::error('Encrypted employee document '.$empDoc->id.' could not be read or decrypted.');
            return response()->json(['message' => 'This document could not be opened. Please contact the administrator.'], 500);
        endif;

        // Only what a browser can show safely is ever served inline.
        $viewable = StaffDocumentVaultService::isViewable($empDoc);
        if($mode == 'view' && !$viewable):
            $mode = 'download';
        endif;

        // No trail, no document.
        if(!$vault->log(($mode == 'view' ? StaffDocumentVaultService::EVENT_VIEW : StaffDocumentVaultService::EVENT_DOWNLOAD), $empDoc)):
            return response()->json(['message' => StaffDocumentVaultService::NOT_RECORDED], 500);
        endif;

        $name = str_replace(['/', '\\', '%'], '-', StaffDocumentVaultService::downloadName($empDoc));
        $fallback = preg_replace('/[^A-Za-z0-9._-]+/', '_', Str::ascii($name));

        return response($contents, 200, [
            'Content-Type' => ($viewable ? StaffDocumentVaultService::VIEWABLE[strtolower($empDoc->doc_type)] : 'application/octet-stream'),
            'Content-Disposition' => HeaderUtils::makeDisposition(($mode == 'view' ? 'inline' : 'attachment'), $name, ($fallback !== '' ? $fallback : 'document')),
            'X-Document-Name' => rawurlencode($name),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Who opened this employee's documents, and what happened to their PIN.
     */
    public function accessLogList(Request $request){
        $employeeId = (isset($request->employeeId) && !empty($request->employeeId) ? $request->employeeId : 0);
        $event = (isset($request->event) && !empty($request->event) ? $request->event : '');

        $query = EmployeeDocumentAccessLog::with(['user.employee', 'impersonator.employee'])->where('employee_id', $employeeId)->orderBy('id', 'DESC');
        if(isset(StaffDocumentVaultService::EVENT_GROUPS[$event])):
            $query->whereIn('event', StaffDocumentVaultService::EVENT_GROUPS[$event]);
        elseif($event == 'impersonated'):
            $query->whereNotNull('impersonator_id');
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
            foreach($Query as $list):
                $meta = (isset(StaffDocumentVaultService::EVENTS[$list->event]) ? StaffDocumentVaultService::EVENTS[$list->event] : [ucfirst(str_replace('_', ' ', $list->event)), 'info']);
                $impersonated = ($list->impersonator_id > 0);
                $account = (isset($list->user->full_name) ? $list->user->full_name : 'Unknown');

                // An open made while signed in as somebody else is not an
                // ordinary one: say so, and stop it reading as routine.
                if($impersonated && in_array($list->event, StaffDocumentVaultService::EVENT_GROUPS['opened'])):
                    $meta = [$meta[0].' through impersonation', 'warning'];
                endif;

                $data[] = [
                    'id' => $list->id,
                    'event' => $meta[0],
                    'tone' => $meta[1],
                    'document' => (!empty($list->document_name) ? $list->document_name : ''),
                    'is_encrypted' => ($list->is_encrypted == 1 ? 1 : 0),
                    // Who really did it: the impersonator when there was one.
                    'user' => ($impersonated ? (isset($list->impersonator->full_name) ? $list->impersonator->full_name : 'User #'.$list->impersonator_id) : $account),
                    // The account they were signed in as at the time.
                    'signed_in_as' => ($impersonated ? $account : ''),
                    'ip_address' => (!empty($list->ip_address) ? $list->ip_address : ''),
                    'date' => (!empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'time' => (!empty($list->created_at) ? date('h:i:s A', strtotime($list->created_at)) : ''),
                ];
            endforeach;
        endif;
        return response()->json([
            'current_page' => $page,
            'last_page' => $last_page,
            'per_page' => $perpage,
            'total_rows' => $total_rows,
            'data' => $data
        ]);
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
