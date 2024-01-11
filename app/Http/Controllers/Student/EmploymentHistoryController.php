<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEmploymentRequest;
use App\Http\Requests\StudentEmploymentStatusRequest;
use App\Models\StudentArchive;
use App\Models\StudentEmployment;
use App\Models\StudentEmploymentReference;
use App\Models\StudentOtherDetail;
use Illuminate\Http\Request;

class EmploymentHistoryController extends Controller
{
    public function updateStudentEmploymentStatus(StudentEmploymentStatusRequest $request){
        $student_id = $request->student_id;
        $student_other_detail_id = $request->student_other_detail_id;
        $employment_status = $request->employment_status;

        $otherDetailOld = StudentOtherDetail::where('student_id', $student_id)->where('id', $student_other_detail_id)->first();

        $student = StudentOtherDetail::find($student_other_detail_id);
        $student->fill([
            'employment_status' => $employment_status
        ]);
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailOld->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Student employment status successfully updated.'], 200);
    }


    public function list(Request $request){
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : '0');
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentEmployment::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('company_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('company_phone','LIKE','%'.$queryStr.'%');
            $query->orWhere('position','LIKE','%'.$queryStr.'%');
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

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $address = '';
                $address .= (isset($list->address->address_line_1) ? $list->address->address_line_1.'<br/>' : '');
                $address .= (isset($list->address->address_line_2) && $list->address->address_line_2 != '' ? $list->address->address_line_2.'<br/>' : '');
                $address .= (isset($list->address->city) && $list->address->city != '' ? $list->address->city.', ' : '');
                $address .= (isset($list->address->state) && $list->address->state != '' ? $list->address->state.', ' : '');
                $address .= (isset($list->address->post_code) && $list->address->post_code != '' ? $list->address->post_code.', ' : '');
                $address .= (isset($list->address->country) && $list->address->country != '' ? '<br/>'.$list->address->country : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'company_name' => $list->company_name,
                    'company_phone' => $list->company_phone,
                    'position' => $list->position,
                    'start_date' => $list->start_date,
                    'continuing' => $list->continuing,
                    'end_date' => ($list->continuing == 1 ? 'Continue' : $list->end_date),
                    'address' => $address,
                    'name' => (isset($list->reference[0]->name) && !empty($list->reference[0]->name) ? $list->reference[0]->name : ''),
                    'contact_position' => (isset($list->reference[0]->position) && !empty($list->reference[0]->position) ? $list->reference[0]->position : ''),
                    'contact_phone' => (isset($list->reference[0]->phone) && !empty($list->reference[0]->phone) ? $list->reference[0]->phone : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(StudentEmploymentRequest $request){
        $continuing = (isset($request->continuing) && $request->continuing > 0 ? $request->continuing : 0);
        $employment = StudentEmployment::create([
            'student_id'=> $request->student_id,
            'company_name'=> $request->company_name,
            'company_phone'=> $request->company_phone,
            'position'=> $request->position,
            'start_date'=> $request->start_date,
            'continuing'=> $continuing,
            'end_date'=> ($continuing != 1 && isset($request->end_date) && !empty($request->end_date) ? $request->end_date : ''),
            'address_id' => (isset($request->address_id) ? $request->address_id : null),
            'created_by' => auth()->user()->id
        ]);
        if($employment):
            $reference = StudentEmploymentReference::create([
                'student_employment_id' => $employment->id,
                'name' => $request->contact_name,
                'position' => $request->contact_position,
                'phone' => $request->contact_phone,
                'email' => (isset($request->contact_email) ? $request->contact_email : null),
                'created_by' => auth()->user()->id
            ]);
        endif;

        return response()->json($employment);
    }

    public function edit($id){
        $data = StudentEmployment::with(['reference', 'address'])->where('id', $id)->first();

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(StudentEmploymentRequest $request){
        $employmentID = $request->id;
        $referenceID = $request->ref_id;   
        $employment = StudentEmployment::with(['reference'])->where('id', $employmentID)->first();

        $continuing = (isset($request->continuing) && $request->continuing > 0 ? $request->continuing : 0);
        $employment = StudentEmployment::where('id', $employmentID)->update([
            'company_name'=> $request->company_name,
            'company_phone'=> $request->company_phone,
            'position'=> $request->position,
            'start_date'=> $request->start_date,
            'continuing'=> $continuing,
            'end_date'=> ($continuing != 1 && isset($request->end_date) && !empty($request->end_date) ? $request->end_date : ''),
            'address_id' => (isset($request->address_id) ? $request->address_id : null),
            'updated_by' => auth()->user()->id
        ]);
        $reference = StudentEmploymentReference::where('id', $referenceID)->update([
            'name' => $request->contact_name,
            'position' => $request->contact_position,
            'phone' => $request->contact_phone,
            'email' => (isset($request->contact_email) ? $request->contact_email : null),
            'updated_by' => auth()->user()->id
        ]);


        if($employment){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = StudentEmployment::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = StudentEmployment::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
