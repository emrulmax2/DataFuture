<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentProofOfIdCheckRequest;
use App\Models\StudentProofOfId;
use Illuminate\Http\Request;

class ProofIdCheckController extends Controller
{
    public function list(Request $request){
        $student_id = (isset($request->student) && $request->student > 0 ? $request->student : 0);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentProofOfId::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if(!empty($queryStr)):
            $query->where('proof_type','LIKE','%'.$queryStr.'%');
            $query->orWhere('proof_id','LIKE','%'.$queryStr.'%');
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'proof_type' => ucfirst($list->proof_type),
                    'proof_id' => $list->proof_id,
                    'proof_expiredate' => $list->proof_expiredate,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(StudentProofOfIdCheckRequest $request){
        $data = StudentProofOfId::create([
            'student_id'=> $request->student_id,
            'proof_type'=> $request->proof_type,
            'proof_id'=> $request->proof_id,
            'proof_expiredate'=> $request->proof_expiredate,
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = StudentProofOfId::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(StudentProofOfIdCheckRequest $request){      
        $data = StudentProofOfId::where('id', $request->id)->update([
            'student_id'=> $request->student_id,
            'proof_type'=> $request->proof_type,
            'proof_id'=> $request->proof_id,
            'proof_expiredate'=> (isset($request->proof_expiredate) && !empty($request->proof_expiredate) ? date('Y-m-d', strtotime($request->proof_expiredate)) : null),
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = StudentProofOfId::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = StudentProofOfId::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
}
