<?php

namespace App\Http\Controllers\Settings\Studentoptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySupervisorRequest;
use App\Models\CompanySupervisor;
use Illuminate\Http\Request;

class CompanySupervisorController extends Controller
{
    public function list(Request $request){
        $company_id = (isset($request->company_id) && !empty($request->company_id) ? $request->company_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = CompanySupervisor::where('company_id', $company_id)->orderByRaw(implode(',', $sorts));

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
                    'name' => $list->name,
                    'email' => $list->email,
                    'phone' => $list->phone,
                    'other_info' => $list->other_info,

                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(CompanySupervisorRequest $request){
        $data = CompanySupervisor::create([
            'company_id'=> $request->company_id,
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'other_info'=> (isset($request->other_info) && !empty($request->other_info) ? $request->other_info : null),

            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit(Request $request){
        $data = CompanySupervisor::find($request->row_id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CompanySupervisorRequest $request){      
        $data = CompanySupervisor::where('id', $request->id)->update([
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'other_info'=> (isset($request->other_info) && !empty($request->other_info) ? $request->other_info : null),
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = CompanySupervisor::find($id)->forceDelete();
        return response()->json(['res' => 'Success'], 200);
    }
}
