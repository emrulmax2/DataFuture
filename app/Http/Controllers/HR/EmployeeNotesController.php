<?php

namespace App\Http\Controllers\HR;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeNoteRequest;
use App\Models\EmployeeNotes;
use App\Models\Employee;
use App\Models\EmployeeDocuments;
use App\Models\User;
use App\Models\Employment;
use Illuminate\Http\Request;
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

        return view('pages.employee.profile.notes',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "employee" => $employee,
            "employment" => $employment
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeNoteRequest $request){
        $employee_id = $request->employee_id;
        $note = EmployeeNotes::create([
            'employee_id'=> $employee_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Live',
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
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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
        // if(isset($theNote->employee_document_id) && isset($theNote->document) && asset('/storage/employees/notes/'.$theNote->document->current_file_name)):
        //     $docURL = (isset($theNote->document->current_file_name) && !empty($theNote->document->current_file_name) ? asset('/storage/employees/notes/'.$theNote->document->current_file_name) : '');
        // endif;
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

        $note = EmployeeNotes::where('id', $noteId)->where('employee_id', $employee_id)->Update([
            'employee_id'=> $employee_id,
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : ''),
            'note'=> $request->content,
            'phase'=> 'Admission',
            'updated_by' => auth()->user()->id
        ]);
        if($request->hasFile('document')):
            if($employeeDocumentId > 0 && isset($oleNote->document->current_file_name) && !empty($oleNote->document->current_file_name)):
                if (Storage::disk('s3')->exists('public/employees/notes/'.$oleNote->document->current_file_name)):
                    Storage::disk('s3')->delete('public/employees/notes/'.$oleNote->document->current_file_name);
                endif;
                // if (asset('storage/employees/notes/'.$oleNote->document->current_file_name)):
                //     Storage::delete(asset('storage/employees/notes/'.$oleNote->document->current_file_name));
                // endif;

                $ad = EmployeeDocuments::where('id', $employeeDocumentId)->forceDelete();
            endif;

            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/employees/notes/', $documentName, 's3');
            //$path = $document->storeAs('public/employees/notes/', $documentName);

            $data = [];
            $data['employee_id'] = $employee_id;
            $data['hard_copy_check'] = 0;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = null; //Storage::disk('public')->url($path);
            //$data['path'] = asset('public/employees/notes/'.$documentName);
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
        $tmpURL = Storage::disk('s3')->temporaryUrl('public/employees/notes/'.$empDoc->current_file_name, now()->addMinutes(5));
        return response()->json(['res' => $tmpURL], 200);
    }
}
