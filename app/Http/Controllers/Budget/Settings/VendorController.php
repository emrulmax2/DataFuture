<?php

namespace App\Http\Controllers\Budget\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorStoreRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        return view('pages.budget.settings.vendor', [
            'title' => 'Budget Settings - London Churchill College',
            'subtitle' => 'Budget Year Settings',
            'breadcrumbs' => [
                ['label' => 'Budget Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Budget Year', 'href' => 'javascript:void(0);'],
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Vendor::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('name','LIKE','%'.$queryStr.'%')->orWhere('phone','LIKE','%'.$queryStr.'%')->orWhere('email','LIKE','%'.$queryStr.'%')
                    ->orWhere('address','LIKE','%'.$queryStr.'%');
            });
        endif;
        if($status == 2):
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
                    'email' => (!empty($list->email) ? $list->email : ''),
                    'phone' => (!empty($list->phone) ? $list->phone : ''),
                    'address' => (!empty($list->address) ? $list->address : ''),
                    'active' => ($list->active == 1 ? $list->active : '0'),
                    'vendor_for' => ($list->vendor_for == 2 ? 'University Commission' : 'Budget'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(VendorStoreRequest $request){
        $data = Vendor::create([
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'address'=> (isset($request->address) && !empty($request->address) ? $request->address : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'vendor_for'=> (isset($request->vendor_for) && $request->vendor_for > 0 ? $request->vendor_for : 1),
            'created_by' => auth()->user()->id
        ]);
        $row = [];
        if(isset($request->active) && $request->active == 1):
            $row['id'] = $data->id;
            $row['name'] = $request->name;
            $row['email'] = (isset($request->email) && !empty($request->email) ? $request->email : '');
            $row['phone'] = (isset($request->phone) && !empty($request->phone) ? $request->phone : '');
            $row['address'] = (isset($request->address) && !empty($request->address) ? $request->address : '');
        endif;
        return response()->json(['msg' => 'Inserted Successfully.', 'row' => $row]);
    }

    public function edit($id){
        $data = Vendor::find($id);

        return response()->json($data);
    }

    public function update(VendorStoreRequest $request){      
        $data = Vendor::where('id', $request->id)->update([
            'name'=> $request->name,
            'email'=> (isset($request->email) && !empty($request->email) ? $request->email : null),
            'phone'=> (isset($request->phone) && !empty($request->phone) ? $request->phone : null),
            'address'=> (isset($request->address) && !empty($request->address) ? $request->address : null),
            'active'=> (isset($request->active) && $request->active == 1 ? $request->active : '0'),
            'vendor_for'=> (isset($request->vendor_for) && $request->vendor_for > 0 ? $request->vendor_for : 1),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Data updated'], 200);
    }

    public function destroy($id){
        $data = Vendor::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = Vendor::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $year = Vendor::find($id);
        $active = (isset($year->active) && $year->active == 1 ? 0 : 1);

        Vendor::where('id', $id)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }
}
