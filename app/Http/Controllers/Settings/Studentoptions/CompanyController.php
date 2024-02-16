<?php

namespace App\Http\Controllers\Settings\Studentoptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Company::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('email','LIKE','%'.$queryStr.'%');
            $query->orWhere('phone','LIKE','%'.$queryStr.'%');
            $query->orWhere('address','LIKE','%'.$queryStr.'%');
            $query->orWhere('other_info','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 3):
            $query->onlyTrashed();
        else:
            $query->where('active', $status);
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
                    'name' => $list->name,
                    'phone' => $list->phone,
                    'email' => $list->email,
                    'address' => $list->address,
                    'fax' => $list->fax,
                    'other_info' => $list->other_info,

                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'deleted_at' => $list->deleted_at,
                    'has_supervisor' => (isset($list->supervisor) && $list->supervisor->count() > 0 ? $list->supervisor->count() : 0)
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(CompanyRequest $request){
        $data = Company::create([
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'fax'=> (isset($request->fax) && !empty($request->fax) ? $request->fax : null),
            'website'=> (isset($request->website) && !empty($request->website) ? $request->website : null),
            'address'=> (isset($request->address) && !empty($request->address) ? $request->address : null),
            'other_info'=> (isset($request->other_info) && !empty($request->other_info) ? $request->other_info : null),

            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'created_by' => auth()->user()->id
        ]);
        return response()->json($data);
    }

    public function edit($id){
        $data = Company::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function update(CompanyRequest $request){      
        $data = Company::where('id', $request->id)->update([
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'fax'=> (isset($request->fax) && !empty($request->fax) ? $request->fax : null),
            'website'=> (isset($request->website) && !empty($request->website) ? $request->website : null),
            'address'=> (isset($request->address) && !empty($request->address) ? $request->address : null),
            'other_info'=> (isset($request->other_info) && !empty($request->other_info) ? $request->other_info : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'updated_by' => auth()->user()->id
        ]);


        if($data){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 422);
        }
    }

    public function destroy($id){
        $data = Company::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Company::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $title = Company::find($id);
        $active = (isset($title->active) && $title->active == 1 ? 0 : 1);

        Company::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
