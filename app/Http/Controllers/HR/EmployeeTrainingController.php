<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeTrainingRequest;
use App\Models\EmployeeDocuments;
use App\Models\EmployeeTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeTrainingController extends Controller
{
    public function store(EmployeeTrainingRequest $request){
        $employee_id = $request->employee_id;
        $training_date = (isset($request->training_date) && !empty($request->training_date) ? explode(' - ', $request->training_date) : []);
        $start_date = (isset($training_date[0]) && !empty($training_date[0]) ? date('Y-m-d', strtotime($training_date[0])) : '');
        $end_date = (isset($training_date[1]) && !empty($training_date[1]) ? date('Y-m-d', strtotime($training_date[1])) : '');

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['name'] = $request->name;
        $data['provider'] = $request->provider;
        $data['location'] = $request->location;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['cost'] = (isset($request->cost) && !empty($request->cost) ? $request->cost : null);
        $data['expire_date'] = (isset($request->expire_date) && !empty($request->expire_date) ? date('Y-m-d', strtotime($request->expire_date)) : null);
        $data['created_by'] = auth()->user()->id;

        $training = EmployeeTraining::create($data);
        if($training->id && $request->hasFile('document')):
            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $documentName, 's3');

            $data = [];
            $data['employee_id'] = $employee_id;
            $data['document_setting_id'] = null;
            $data['hard_copy_check'] = 1;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('s3')->url($path);
            $data['display_file_name'] = $request->name;
            $data['current_file_name'] = $documentName;
            $data['type'] = 1;
            $data['created_by'] = auth()->user()->id;
            $insert = EmployeeDocuments::create($data);

            if($insert):
                $trainingUpdate = EmployeeTraining::where('id', $training->id)->update([
                    'employee_document_id' => $insert->id
                ]);
            endif;
        endif;

        return response()->json(['res' => 'Training successfully inserted'], 200);
    }

    public function list(Request $request){
        $employee = (isset($request->employee) && $request->employee > 0 ? $request->employee : 0);
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = EmployeeTraining::where('employee_id', $employee)->orderByRaw(implode(',', $sorts));
        if($status == 2):
            $query->onlyTrashed();
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
                $dueOn = date('Y-m-d', strtotime($list->due_on));
                $completed_on = (isset($list->completed_on) && !empty($list->completed_on) ? date('Y-m-d', strtotime($list->completed_on)) : '');
                $data[] = [
                    'id' => $list->id,
                    'employee_id' => $list->employee_id,
                    'sl' => $i,
                    'name' => $list->name,
                    'provider' => $list->provider,
                    'location' => $list->location,
                    'start_date' => (!empty($list->start_date) ? date('jS M, Y', strtotime($list->start_date)) : ''),
                    'end_date' => (!empty($list->end_date) ? date('jS M, Y', strtotime($list->end_date)) : ''),
                    'cost' => (!empty($list->cost) && $list->cost > 0 ? '£'.number_format($list->cost, 2) : ''),
                    'expire_date' => (!empty($list->expire_date) ? date('jS M, Y', strtotime($list->expire_date)) : ''),
                    'employee_document_id' => (isset($list->employee_document_id) && $list->employee_document_id > 0 && isset($list->document->current_file_name) && !empty($list->document->current_file_name) ? $list->employee_document_id : 0),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function update(EmployeeTrainingRequest $request){
        $id = $request->id;
        $trainingOld = EmployeeTraining::find($id);
        $employee_id = $request->employee_id;
        $training_date = (isset($request->training_date) && !empty($request->training_date) ? explode(' - ', $request->training_date) : []);
        $start_date = (isset($training_date[0]) && !empty($training_date[0]) ? date('Y-m-d', strtotime($training_date[0])) : '');
        $end_date = (isset($training_date[1]) && !empty($training_date[1]) ? date('Y-m-d', strtotime($training_date[1])) : '');

        $data = [];
        $data['employee_id'] = $employee_id;
        $data['name'] = $request->name;
        $data['provider'] = $request->provider;
        $data['location'] = $request->location;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['cost'] = (isset($request->cost) && !empty($request->cost) ? $request->cost : null);
        $data['expire_date'] = (isset($request->expire_date) && !empty($request->expire_date) ? date('Y-m-d', strtotime($request->expire_date)) : null);
        $data['updated_by'] = auth()->user()->id;

        EmployeeTraining::where('id', $id)->update($data);
        if($request->hasFile('document')):
            $document = $request->file('document');
            $documentName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/employees/'.$employee_id.'/documents', $documentName, 's3');

            if(isset($trainingOld->employee_document_id) && $trainingOld->employee_document_id > 0 && isset($trainingOld->document->current_file_name) && !empty($trainingOld->document->current_file_name)):
                if (Storage::disk('s3')->exists('public/employees/'.$employee_id.'/documents/'.$trainingOld->document->current_file_name)):
                    Storage::disk('s3')->delete('public/employees/'.$employee_id.'/documents/'.$trainingOld->document->current_file_name);
                endif;
                EmployeeDocuments::where('id', $trainingOld->employee_document_id)->forceDelete();
            endif;

            $data = [];
            $data['employee_id'] = $employee_id;
            $data['document_setting_id'] = null;
            $data['hard_copy_check'] = 1;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('s3')->url($path);
            $data['display_file_name'] = $request->name;
            $data['current_file_name'] = $documentName;
            $data['type'] = 1;
            $data['created_by'] = auth()->user()->id;
            $insert = EmployeeDocuments::create($data);

            if($insert):
                $trainingUpdate = EmployeeTraining::where('id', $id)->update([
                    'employee_document_id' => $insert->id
                ]);
            endif;
        endif;

        return response()->json(['res' => 'Employee Training successfully updated.'], 200);
    }

    public function edit(Request $request){
        $id = $request->editId;
        $empTraining = EmployeeTraining::find($id);

        return response()->json(['res' => $empTraining], 200);
    }

    public function destroy(Request $request){
        $id = $request->recordID;
        $data = EmployeeTraining::find($id)->delete();

        return response()->json($data);
    }

    public function restore(Request $request) {
        $id = $request->recordID;
        $data = EmployeeTraining::where('id', $id)->withTrashed()->restore();

        return response()->json($data);
    }
}
