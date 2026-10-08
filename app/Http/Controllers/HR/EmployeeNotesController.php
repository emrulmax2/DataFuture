<?php

namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeNoteRequest;
use App\Models\EmployeeNotes;
use App\Models\Employee;
use App\Models\EmployeeDocuments;
use App\Models\User;
use App\Models\Employment;
use App\Services\OperationsServiceDeskClient;
use App\Services\StaffDocumentVaultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeNotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id){
        $employee = Employee::find($id);
        $employment = Employment::where("employee_id",$id)->get()->first();

        /* Tickets Operations staff have tagged this employee on. Null when
           that system cannot be reached, which the view says out loud rather
           than showing an empty list that reads as "no tickets". An employee
           with no staff account cannot have been tagged, so that is simply an
           empty list and no call is made. */
        $email = $this->serviceDeskEmail($employee);
        $serviceDeskTickets = $email ? app(OperationsServiceDeskClient::class)->ticketsForEmployee($email) : [];

        return view('pages.employee.profile.notes',[
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [],
            "employee" => $employee,
            "employment" => $employment,
            'serviceDeskTickets' => $serviceDeskTickets,
            // What the page needs to know before asking for a PIN (confidential tickets).
            'vault' => app(StaffDocumentVaultService::class)->pageState(),
        ]);
    }

    /**
     * The address Operations knows this employee by.
     *
     * Their staff account's, not the personal one on the employee record: the
     * account in Operations was made from the account here, by that address.
     */
    private function serviceDeskEmail(?Employee $employee): ?string
    {
        $email = trim((string) ($employee?->user?->email ?? ''));

        return $email !== '' ? $email : null;
    }

    /**
     * One Service Desk ticket, for the panel that opens from the notes page.
     *
     * The employee-profile twin of StudentController::serviceDeskTicket. The
     * employee is part of the address so Operations can be told whose profile
     * is asking — it serves an employee profile only a ticket tagged with that
     * employee, which this cannot be talked out of by changing a number.
     *
     * Internal notes are stripped at the Operations end. This only renders what
     * it is given.
     */
    public function serviceDeskTicket($id, $ticketId)
    {
        $email = $this->serviceDeskEmail(Employee::find($id));
        abort_if($email === null, 404);

        /* Already unlocked in this session, so it opens without asking again. */
        $unlocked = ! empty(session('sd_unlocked_tickets')[(int) $ticketId]);
        $operations = app(OperationsServiceDeskClient::class);

        $ticket = $operations->ticket((int) $ticketId, $unlocked, $email);

        if (! $ticket):
            /* Operations refuses a confidential ticket outright, so a 403 here
               means "ask for the PIN", not "something went wrong". */
            if ($operations->lastStatus() === 403):
                return response()->json([
                    'status' => false,
                    'confidential' => true,
                    'message' => 'This ticket is confidential. Enter your document PIN to open it.',
                ], 403);
            endif;

            return response()->json([
                'status' => false,
                'message' => 'That ticket could not be loaded from Operations.',
            ], 502);
        endif;

        return response()->json([
            'status' => true,
            'html'   => $this->serviceDeskPanel($ticket, $id),
        ]);
    }

    /**
     * Open a confidential ticket, after the reader's own document PIN.
     *
     * The same door as on a student's record: the person is challenged with
     * the PIN that opens an encrypted document, the attempt is counted and
     * locked out like any other, and the unlock lasts for this session and
     * this ticket only.
     */
    public function serviceDeskTicketUnlock(Request $request, $id, $ticketId)
    {
        $email = $this->serviceDeskEmail(Employee::find($id));
        abort_if($email === null, 404);

        $result = app(StaffDocumentVaultService::class)->challengeOwner(auth()->user(), $request->input('pin'));

        if (! $result['ok']):
            return response()->json([
                'status' => false,
                'message' => $result['message'],
            ], $result['status']);
        endif;

        $ticket = app(OperationsServiceDeskClient::class)->ticket((int) $ticketId, true, $email);

        if (! $ticket):
            return response()->json([
                'status' => false,
                'message' => 'That ticket could not be loaded from Operations.',
            ], 502);
        endif;

        /* Remembered so the attachments on it can be fetched too, and so a
           reader is not asked again while they are reading. Per session, per
           ticket - closing the browser locks it again. */
        $unlocked = (array) $request->session()->get('sd_unlocked_tickets', []);
        $unlocked[(int) $ticketId] = true;
        $request->session()->put('sd_unlocked_tickets', $unlocked);

        Log::info('[Service Desk] Confidential ticket opened.', [
            'ticket' => $ticket['ref'] ?? $ticketId,
            'employee' => (int) $id,
            'by' => auth()->id(),
        ]);

        return response()->json([
            'status' => true,
            'html'   => $this->serviceDeskPanel($ticket, $id),
        ]);
    }

    /**
     * Hand a ticket attachment to a member of staff.
     *
     * Served through this app rather than linked straight to Operations: that
     * endpoint answers to a shared key no browser can present. Operations still
     * decides whether the file may be released — nothing on an internal note,
     * nothing off a ticket that does not name this employee.
     */
    public function serviceDeskAttachment($id, $attachmentId)
    {
        $email = $this->serviceDeskEmail(Employee::find($id));
        abort_if($email === null, 404);

        /* A file on a confidential ticket comes only after that ticket has
           been unlocked in this session, the same as its conversation. */
        $file = app(OperationsServiceDeskClient::class)->attachment(
            (int) $attachmentId,
            ! empty(session('sd_unlocked_tickets')),
            $email
        );

        if (! $file):
            abort(404, 'That file could not be fetched from Operations.');
        endif;

        return response($file['body'], 200, [
            'Content-Type'        => $file['type'],
            'Content-Disposition' => 'attachment; filename="'.addslashes($file['name']).'"',
        ]);
    }

    /**
     * The conversation, drawn by the same template the student record uses so
     * a ticket reads the same wherever it is opened. Only the attachment links
     * differ: they have to come back through this profile's own route.
     */
    private function serviceDeskPanel(array $ticket, $employeeId): string
    {
        return view('pages.students.live.service-desk-ticket', [
            'ticket' => $ticket,
            'attachmentUrl' => fn ($fileId) => route('employee.service-desk.attachment', [$employeeId, $fileId]),
        ])->render();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeNoteRequest $request){
        $employee_id = $request->employee_id;
        $reminder = (isset($request->reminder) && $request->reminder > 0 ? $request->reminder : 0);
        $reminderDate = ($reminder == 1 && isset($request->reminder_date) && !empty($request->reminder_date) ? date('Y-m-d', strtotime($request->reminder_date)) : null);
        $note = EmployeeNotes::create([
            'employee_id'=> $employee_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
            'employee_appraisal_id'=> (isset($request->employee_appraisal_id) && $request->employee_appraisal_id > 0 ? $request->employee_appraisal_id : null),
            'reminder' => $reminder,
            'reminder_date' => $reminderDate,
            'created_by' => auth()->user()->id
        ]);
        if($note):
            if($request->hasFile('document')):
                $document = $request->file('document');
                $documentName = time().'_'.$document->getClientOriginalName();
                //$path = $document->storeAs('public/employees/notes/', $documentName);

                $path = $document->storeAs('public/employees/notes/', $documentName, 's3');

                $data = [];
                $data['employee_id'] = $employee_id;
                $data['hard_copy_check'] = 0;
                $data['doc_type'] = $document->getClientOriginalExtension();
                //$data['path'] = asset('public/employees/notes/'.$documentName);
                $data['path'] = null; //Storage::disk('s3')->url($path);
                $data['display_file_name'] = $documentName;
                $data['current_file_name'] = $documentName;
                $data['created_by'] = auth()->user()->id;
                $employeeDocument = EmployeeDocuments::create($data);

                if($employeeDocument):
                    $noteUpdate = EmployeeNotes::where('id', $note->id)->update([
                        'employee_document_id' => $employeeDocument->id
                    ]);
                endif;
            endif;
            return response()->json(['message' => 'Employee Note successfully created'], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function list(Request $request){

        $employee_id = (isset($request->employeeId) && !empty($request->employeeId) ? $request->employeeId : 0);
        $appraisal_id = (isset($request->appraisalId) && !empty($request->appraisalId) ? $request->appraisalId : 0);
        
        $queryStr = (isset($request->queryStr) && $request->queryStr != '' ? $request->queryStr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeNotes::orderByRaw(implode(',', $sorts))->where('employee_id', $employee_id);
        if(!empty($queryStr)):
            $query->where('note','LIKE','%'.$queryStr.'%');
        endif;
        if($appraisal_id > 0):
            $query->where('employee_appraisal_id', $appraisal_id);
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
        //dd($Query);
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $docURL = '';
                if(isset($list->employee_document_id) && isset($list->document)):
                    $docURL = (isset($list->document->current_file_name) && !empty($list->document->current_file_name) ? Storage::disk('s3')->url('public/employees/notes/'.$list->document->current_file_name) : '');
                endif;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'note' => (strlen(strip_tags($list->note)) > 40 ? substr(strip_tags($list->note), 0, 40).'...' : strip_tags($list->note)),
                    'url' => (isset($list->employee_document_id) && $list->employee_document_id > 0 && (isset($list->document->current_file_name) && !empty($list->document->current_file_name)) ? $list->current_file_name : ''),
                    'employee_document_id' => (isset($list->employee_document_id) && $list->employee_document_id > 0 && (isset($list->document->current_file_name) && !empty($list->document->current_file_name)) ? $list->employee_document_id : 0),
                    //'url' => isset($list->document) ? asset('storage/employees/notes/'.$list->document->current_file_name) : null,
                    'created_by'=> (isset($list->user->name) ? $list->user->name : 'Unknown'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS F, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at,
                    'reminder' => $list->reminder,
                    'reminder_date' => !empty($list->reminder_date) ? date('jS F, Y', strtotime($list->reminder_date)) : '',
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EmployeeNotes  $employeeNotes
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        $noteId = $request->noteId;
        $note = EmployeeNotes::find($noteId);
        $html = '';
        $btns = '';
        if(!empty($note) && !empty($note->note)):
            $html .= '<div>';
                $html .= $note->note;
            $html .= '</div>';
            if(isset($note->student_document_id) && isset($note->document)):
                $docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) ? Storage::disk('s3')->url('public/employees/notes/'.$note->document->current_file_name) : '');
                //$docURL = (isset($note->document->current_file_name) && !empty($note->document->current_file_name) ? asset('storage/employees/notes/'.$note->document->current_file_name) : '');
                if(!empty($docURL)):
                    $btns .= '<a download href="'.$docURL.'" class="btn btn-primary w-auto inline-flex"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i>Download Attachment</a>';
                endif;
            endif;
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! No data foudn for this note.
                    </div>';
        endif;

        return response()->json(['message' => $html, 'btns' => $btns], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EmployeeNotes  $employeeNotes
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request){
        $noteId = $request->noteId;
        $theNote = EmployeeNotes::find($noteId);
        
        $docURL = '';                               
        if(isset($theNote->employee_document_id) && isset($theNote->document) && Storage::disk('s3')->exists('public/employees/notes/'.$theNote->document->current_file_name)):
            $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) ? Storage::disk('s3')->url('public/employees/notes/'.$theNote->document->current_file_name) : '');
        endif;
        $theNote['docURL'] = $docURL;

        return response()->json(['res' => $theNote], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EmployeeNotes  $employeeNotes
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeNoteRequest $request){
        $employee_id = $request->employee_id;
        $noteId = $request->id;
        $oleNote = EmployeeNotes::find($noteId);
        $employeeDocumentId = (isset($oleNote->employee_document_id) && $oleNote->employee_document_id > 0 ? $oleNote->employee_document_id : 0);

        $reminder = (isset($request->reminder) && $request->reminder > 0 ? $request->reminder : 0);
        $reminderDate = ($reminder == 1 && isset($request->reminder_date) && !empty($request->reminder_date) ? date('Y-m-d', strtotime($request->reminder_date)) : null);
        //dd($reminderDate);
        $note = EmployeeNotes::where('id', $noteId)->where('employee_id', $employee_id)->Update([
            'employee_id'=> $employee_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
            'employee_appraisal_id'=> (isset($request->employee_appraisal_id) && $request->employee_appraisal_id > 0 ? $request->employee_appraisal_id : null),
            'reminder' => $reminder,
            'reminder_date' => $reminderDate,
            'updated_by' => auth()->user()->id
        ]);
        if($request->hasFile('document')):
            if($employeeDocumentId > 0 && isset($oleNote->document->current_file_name) && !empty($oleNote->document->current_file_name)):
                if (Storage::disk('s3')->exists('public/employees/notes/'.$oleNote->document->current_file_name)):
                    Storage::disk('s3')->delete('public/employees/notes/'.$oleNote->document->current_file_name);
                endif;

                $ad = EmployeeDocuments::where('id', $employeeDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/employees/notes/', $documentName, 's3');

            $data = [];
            $data['employee_id'] = $employee_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = null; //Storage::disk('public')->url($documentPath);
            $data['display_file_name'] = $documentName;
            $data['current_file_name'] = $documentName;
            $data['created_by'] = auth()->user()->id;
            $employeeDocument = EmployeeDocuments::create($data);

            if($employeeDocument):
                $noteUpdate = EmployeeNotes::where('id', $noteId)->update([
                    'employee_document_id' => $employeeDocument->id
                ]);
            endif;
        endif;
        return response()->json(['message' => 'Employee Note successfully updated'], 200);
    }

    public function destroy(Request $request){
        $employee = $request->employee;
        $recordid = $request->recordid;
        $employeeNote = EmployeeNotes::find($recordid);
        $employeeDocumentID = (isset($employeeNote->employee_document_id) && $employeeNote->employee_document_id > 0 ? $employeeNote->employee_document_id : 0);
        EmployeeNotes::find($recordid)->delete();

        if($employeeDocumentID > 0):
            EmployeeDocuments::find($employeeDocumentID)->delete();
        endif;

        return response()->json(['message' => 'Successfully deleted'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EmployeeNotes  $employeeNotes
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request) {
        $recordid = $request->recordid;
        $data = EmployeeNotes::where('id', $recordid)->withTrashed()->restore();
        $employeeNote = EmployeeNotes::find($recordid);
        $employeeDocumentID = (isset($employeeNote->employee_document_id) && $employeeNote->employee_document_id > 0 ? $employeeNote->employee_document_id : 0);
        if($employeeDocumentID > 0):
            EmployeeDocuments::where('id', $employeeDocumentID)->withTrashed()->restore();
        endif;
        return response()->json(['message' => 'Successfully restored'], 200);
    }

    public function downloadUrl(Request $request){
        $row_id = $request->row_id;

        $empDoc = EmployeeDocuments::find($row_id);
        if(!$empDoc):
            return response()->json(['message' => 'Document not found.'], 404);
        endif;

        // Encrypted documents are only ever opened from the Documents tab, with a PIN.
        if($empDoc->is_encrypted == 1):
            return response()->json(['message' => 'This document is encrypted. Open it from the Documents tab with your document PIN.'], 423);
        endif;

        $tmpURL = Storage::disk('s3')->temporaryUrl('public/employees/notes/'.$empDoc->current_file_name, now()->addMinutes(5));

        // A note's attachment is one of the employee's documents, so opening
        // it goes on the same record as the Documents tab: no trail, no document.
        if(!app(StaffDocumentVaultService::class)->log(StaffDocumentVaultService::EVENT_DOWNLOAD, $empDoc)):
            return response()->json(['message' => StaffDocumentVaultService::NOT_RECORDED], 500);
        endif;

        return response()->json(['res' => $tmpURL], 200);
    }
}
