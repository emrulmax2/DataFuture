<?php

namespace App\Http\Controllers;

use App\Models\ReportItAll;
use App\Http\Requests\StorereportItAllRequest;
use App\Http\Requests\UpdatereportItAllRequest;
use App\Jobs\UserMailerJob;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\IssueType;
use App\Models\ReportItAllUpload;
use App\Models\Student;
use App\Models\Venue;
use Google\Service\FirebaseRules\Issue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


use App\Mail\CommunicationSendMail;
use App\Models\TaskList;

class ReportItAllController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){

        $issueList = IssueType::all();
        $venues = Venue::where('active',1)->get();
        $employee = Employee::where('user_id', auth()->user()->id)->first();
        return view('pages.students.report-it.index', [
            'title' => 'Report IT - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Report IT', 'href' => 'javascript:void(0);']
            ],
            'issueList' => $issueList,
            'employee' => $employee,
            'venues' => $venues,
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $reportFrom = (isset($request->reportFrom) && !empty($request->reportFrom) ? $request->reportFrom : '');
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : '');

        $total_rows = $count = ReportItAll::count();
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
        if($statuses=="")
            $query = ReportItAll::with('employee', 'issueType', 'student')->whereIn('status',["Pending","In Progress"])->orderByRaw(implode(',', $sorts));
        else
            $query = ReportItAll::with('employee', 'issueType', 'student')->whereIn('status',$statuses)->orderByRaw(implode(',', $sorts));

        if(!empty($queryStr)):
            // search in employee name and student name
            $query->where(function($q) use ($queryStr) {
                $q->whereHas('employee', function($q) use ($queryStr) {
                    //concat first_name and last_name
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", '%'.$queryStr.'%');
                })->orWhereHas('student', function($q) use ($queryStr) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", '%'.$queryStr.'%');
                });
            });


        endif;
        if(!empty($reportFrom)):
            $query->where('report_form', $reportFrom);
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        
        if($Query->isNotEmpty()):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'issue_type' => (isset($list->issueType->name) ? $list->issueType->name : ''),
                    'report_form' => (isset($list->student->first_name) ? 'Student' : (isset($list->employee->first_name) ? 'Employee' : '')),
                    'status' => $list->status,
                    'ejt_name' => (isset($list->student->first_name) ? $list->student->registration_no : (isset($list->employee->employment->employeeJobTitle) ? $list->employee->employment->employeeJobTitle->name : '')),
                    'full_name' => (isset($list->student->first_name) ? $list->student->full_name : $list->employee->full_name),
                    'photourl' => (isset($list->student->photo_url) ? $list->student->photo_url : (isset($list->employee->photo_url) ? $list->employee->photo_url : '')),
                    'deleted_at' => $list->deleted_at,
                    'description' => $list->description,
                    'location' => $list->location,
                    'venue' => isset($list->venue) ? $list->venue->name : ''
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorereportItAllRequest $request)
    {

        // collect all files

        $documents = $request->input('documents');

        $reportItAll = new ReportItAll();
        // Example: merge a value for 'task_list_id' if needed
        $taskList = TaskList::where('name','Report IT')->get()->first();
        $request->merge(['task_list_id' => $taskList->id]);
        $reportItAll->fill($request->all());

        $reportItAll->save();

        if(isset($documents) && !empty($documents)){
            
            foreach($documents as $document_id):
                if(isset($document_id) && !empty($document_id)):
                    $reportItALl = ReportItAllUpload::find($document_id)->update(['report_it_all_id' => $reportItAll->id]);
                endif;
            endforeach;
        }
        //after successful store we need to send a email to the staff
        if(isset($reportItAll->issue_type_id)) {
            $newInsertedReportIt = ReportItAll::find($reportItAll->id);
            $issueTypeMailInfo = ComonSmtp::find($newInsertedReportIt->issueType->comon_smtp_id);
            $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
            $configuration = [
                'smtp_host' => (isset($commonSmtp->smtp_host) && !empty($commonSmtp->smtp_host) ? $commonSmtp->smtp_host : 'smtp.gmail.com'),
                'smtp_port' => (isset($commonSmtp->smtp_port) && !empty($commonSmtp->smtp_port) ? $commonSmtp->smtp_port : '587'),
                'smtp_username' => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'smtp_password' => (isset($commonSmtp->smtp_pass) && !empty($commonSmtp->smtp_pass) ? $commonSmtp->smtp_pass : 'churchill1'),
                'smtp_encryption' => (isset($commonSmtp->smtp_encryption) && !empty($commonSmtp->smtp_encryption) ? $commonSmtp->smtp_encryption : 'tls'),
                
                'from_email'    => (isset($commonSmtp->smtp_user) && !empty($commonSmtp->smtp_user) ? $commonSmtp->smtp_user : 'no-reply@lcc.ac.uk'),
                'from_name'    =>  'London Churchill College',
            ];

        

            // $configuration = [
            //     'smtp_host' => 'sandbox.smtp.mailtrap.io',
            //     'smtp_port' => '2525',
            //     'smtp_username' => 'e8ae09cfefd325',
            //     'smtp_password' => 'ce7fa44b28281d',
            //     'smtp_encryption' => 'tls',
                
            //     'from_email'    => 'no-reply@lcc.ac.uk',
            //     'from_name'    =>  'London Churchill College',
            // ];


            $statusClasses = [
                "Pending" => "display:inline-block;padding:0.5rem 0.25rem;font-size:0.75rem;font-weight:600;color:#854d0e;background-color:#fef9c3;border-radius:0.5rem;",
                "In Progress" => "display:inline-block;padding:0.5rem 0.25rem;font-size:0.75rem;font-weight:600;color:#1e40af;background-color:#dbeafe;border-radius:0.5rem;",
                "Resolved" => "display:inline-block;padding:0.5rem 0.25rem;font-size:0.75rem;font-weight:600;color:#166534;background-color:#bbf7d0;border-radius:0.5rem;",
                "Rejected" => "display:inline-block;padding:0.5rem 0.25rem;font-size:0.75rem;font-weight:600;color:#991b1b;background-color:#fecaca;border-radius:0.5rem;",
            ];

            $MAILBODY = "<p style=\"margin:0 0 12px;color:#263238;font-size:14px;line-height:1.5;\">
                Dear <strong>LCC STAFF</strong>,<br>
                An issue has been submitted By " . $newInsertedReportIt->Issue_raised_by . " that may require your attention. See the key details below.
            </p>
            <!-- Issue Info Table -->
            <table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-collapse:collapse;font-size:14px;margin:0 auto;max-width:600px;\">
            <tr>
                <td style=\"padding:8px;font-weight:600;color:#374151;width:30%;\">Type of Issue</td>
                <td style=\"padding:8px;color:#475569;\">" . $newInsertedReportIt->issueType->name . "</td>
            </tr>
            <tr>
                <td style=\"padding:8px;font-weight:600;color:#374151;width:30%;\">Description</td>
                <td style=\"padding:8px;color:#475569;\">" . $newInsertedReportIt->description . "</td>
            </tr>
            <tr style=\"background:#f9fafb;\">
                <td style=\"padding:8px;font-weight:600;color:#374151;\">Venue</td>
                <td style=\"padding:8px;color:#475569;\">" . $newInsertedReportIt->venue->name . "</td>
            </tr>
            <tr>
                <td style=\"padding:8px;font-weight:600;color:#374151;\">Location</td>
                <td style=\"padding:8px;color:#475569;\">" . $newInsertedReportIt->location . "</td>
            </tr>
            <tr style=\"background:#f9fafb;\">
                <td style=\"padding:8px;font-weight:600;color:#374151;\">Status</td>
                <td style=\"padding:8px;\">
                    <span style=\"" . $statusClasses[$newInsertedReportIt->status] . "\">" . $newInsertedReportIt->status . "</span>
                </td>
            </tr>
            </table>
            <!-- CTA Button -->
            <div style=\"text-align:center;margin:20px 0;\">
                <a href=\"" . route('report.it.all.show', $newInsertedReportIt->id) . "\" style=\"display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;font-weight:600;font-size:14px;\">
                    View Issue
                </a>
            </div>
            Best regards,<br/>
            London Churchill College";

            UserMailerJob::dispatch($configuration, [$issueTypeMailInfo->smtp_user], new CommunicationSendMail('A new Report IT issue arised', $MAILBODY, []));

        }

        return response()->json(['status' => 'success', 'message' => 'Report IT entry created successfully.']);
    }

    public function upload(Request $request)
    {
        // collect all files
        $uploadedIds = "";
        $document = $request->file('file');

        $request->input('employee_id') ? $employee = Employee::find($request->input('employee_id')) : $employee = null;
        $request->input('student_id') ? $student = Student::find($request->input('student_id')) : $student = null;
        $imageName = time().'_'.$document->getClientOriginalName();
        $uploadedTo = 'local';
        if(isset($student) && !empty($student)) 
        $path = $document->storeAs('public/students/report_it/'.$student->id, $imageName, $uploadedTo);
        else 
        $path = $document->storeAs('public/employees/report_it/'.$employee->id, $imageName, $uploadedTo);
        $data = [];
        //find file type 'image', 'video', 'document', 'other'
        if(in_array($document->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'])){
            $data['file_type'] = 'image';
        } elseif(in_array($document->getClientOriginalExtension(), ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'])){
            $data['file_type'] = 'video';
        } elseif(in_array($document->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'])){
            $data['file_type'] = 'document';
        } else {
            $data['file_type'] = 'other';
        }
        //find mime type
        $data['mime_type'] = $document->getClientMimeType();
        $data['file_extension'] = $document->getClientOriginalExtension();
        $data['file_path'] = Storage::disk($uploadedTo)->url($path);
        $data['original_name'] = $document->getClientOriginalName();
        $data['file_name'] = $imageName;
        $data['uploaded_by'] =  isset($student) ? auth('student')->user()->id : auth()->user()->id;
        $data['file_size'] = $document->getSize();
        $data['uploaded_to'] = $uploadedTo;
        //$data['report_it_all_id'] = $request->input('report_it_all_id');

        //get the file temporary url for local and S3
        if($uploadedTo == 's3'){

            $fileUrl = Storage::disk($uploadedTo)->temporaryUrl($path, now()->addMinutes(15));
        } else {

            $fileUrl = Storage::disk($uploadedTo)->url($path);
        }
        $reportItUpload = ReportItAllUpload::create($data);
        if($reportItUpload){
            $uploadedIds = $reportItUpload;
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error in uploading the file. Please try again.']);
        }
        

        return response()->json(['status' => 'success', 'message' => 'Files uploaded successfully.', 'reportItUpload' => $uploadedIds, 'fileUrl' => $fileUrl]);
    }
    /**
     * Display the specified resource.
     */
    public function show(ReportItAll $reportItAll)
    {
        
        if(isset($reportItAll->employee_id)){
            $reportItAll->load('employee', 'issueType', 'venue','uploads');
        } elseif(isset($reportItAll->student_id)){
            $reportItAll->load('student', 'issueType', 'venue','uploads');
        }
        // implementing a show blade view for detailed view
        return view('pages.students.report-it.show', [
            'title' => 'Report IT Details - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Report IT', 'href' => route('report.it.all')],
                ['label' => 'Details', 'href' => 'javascript:void(0);']
            ],
            'reportItAll' => $reportItAll,
            'employee' => isset($reportItAll->employee) ? Employee::find($reportItAll->employee->id) : null,
            'student' => isset($reportItAll->student) ? Student::find($reportItAll->student->id) : null,
        ]);

        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportItAll $reportItAll)
    {
        $reportItAll->load('uploads');
        if(isset($reportItAll->uploads) && $reportItAll->uploads->isNotEmpty()){
            foreach($reportItAll->uploads as $upload){
                if($upload->uploaded_to == 's3'){
                    $fileUrl = Storage::disk('s3')->temporaryUrl(str_replace(Storage::disk('s3')->url(''), '', $upload->file_path), now()->addMinutes(15));
                } else {
                    $fileUrl = Storage::disk('local')->url(str_replace(Storage::disk('local')->url(''), '', $upload->file_path));
                }
                //fileUrl was not part of the model so adding it to $reportItAll->uploads
                $upload->fileUrl = $fileUrl;
            }
            
        }
        return response()->json($reportItAll);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatereportItAllRequest $request, ReportItAll $reportItAll)
    {
        
        $documents = $request->input('documents');
        
        $reportItAll->fill($request->all());
        $reportItAll->load('uploads');
        $reportItAll->save();
        
        $reportItALLUPdate = FALSE;
        if(isset($documents) && !empty($documents)){
            
            foreach($documents as $document_id):
                if(isset($document_id) && !empty($document_id)):
                    ReportItAllUpload::find($document_id)->update(['report_it_all_id' => $reportItAll->id]);
                    $reportItALLUPdate = TRUE;
                endif;
            endforeach;
        }

        if($reportItAll->wasChanged() || ($reportItALLUPdate)){
            return response()->json(['status' => 'success', 'message' => 'Report IT entry updated successfully.']);
        } else {
            return response()->json(['status' => 'info', 'message' => 'No changes were made to the Report IT entry.']);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportItAll $reportItAll)
    {
        $reportItAll->delete();
        return response()->json(['status' => 'success', 'message' => 'Report IT entry deleted successfully.']);
    }

    public function forceDelete($id) 
    {
        $data = ReportItAll::where('id', $id)->withTrashed()->forceDelete();

        return response()->json(['status' => 'success', 'message' => 'Report IT entry permanently deleted successfully.']);
    }

    public function restore($id) {
        $data = ReportItAll::where('id', $id)->withTrashed()->restore();

        return response()->json(['status' => 'success', 'message' => 'Report IT entry restored successfully.']);
    }

    public function close(ReportItAll $reportItAll)
    {
        $reportItAll->status = 'Resolved';
        $reportItAll->updated_by = auth()->user()->id;
        $reportItAll->save();
        return response()->json(['message' => 'Report IT entry closed successfully']);
    }

    public function reopen(ReportItAll $reportItAll)
    {
        $reportItAll->status = 'In Progress';
        $reportItAll->updated_by = auth()->user()->id;
        $reportItAll->save();

        return response()->json(['message' => 'Report IT entry reopened successfully']);
    }

    public function removeFileIcon(Request $request)
    {
        $file_id = $request->input('file_id');
        $file = ReportItAllUpload::find($file_id);
        if($file){
            // delete the file from storage
            if($file->uploaded_to == 's3'){
                Storage::disk('s3')->delete(str_replace(Storage::disk('s3')->url(''), '', $file->file_path));
            } else {
                Storage::disk('local')->delete(str_replace(Storage::disk('local')->url(''), '', $file->file_path));
            }
            // delete the record from database
            $file->delete();
            return response()->json(['status' => 'success', 'message' => 'File deleted successfully.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'File not found.']);
        }
    }
}
