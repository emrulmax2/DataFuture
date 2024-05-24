<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccBankStoreRequest;
use App\Models\AccBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccBankController extends Controller
{
    public function index(){
        return view('pages.settings.accounts.banks', [
            'title' => 'Account Settings - London Churchill College',
            'subtitle' => 'Banks Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Account Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Banks', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AccBank::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('method_name','LIKE','%'.$queryStr.'%');
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
                    'bank_name' => $list->bank_name,
                    'image_url' => $list->image_url,
                    'opening_balance' => (isset($list->opening_balance) && $list->opening_balance > 0 ? '£'.number_format($list->opening_balance, 2) : '£0.00'),
                    'opening_date' => (isset($list->opening_date) && !empty($list->opening_date) ? date('jS F, Y', strtotime($list->opening_date)) : ''),
                    'audit_status' => ($list->audit_status > 0 ? $list->audit_status : '0'),
                    'status' => ($list->status == 1 ? $list->status : '2'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(AccBankStoreRequest $request){
        $bank = AccBank::create([
            'bank_name'=> $request->bank_name,
            'opening_balance'=> ($request->opening_balance > 0 ? $request->opening_balance : 0),
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : null),
            'audit_status'=> (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : 0),
            'status'=> (isset($request->status) && $request->status > 0 ? $request->status : 2),
            'created_by' => auth()->user()->id
        ]);

        if($request->hasFile('photo') && $bank->id):
            $photo = $request->file('photo');
            $imageName = 'Bank_'.$bank->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $photo->storeAs('public/banks', $imageName, 'local');

            $bankUpdate = AccBank::where('id', $bank->id)->update([
                'bank_image' => $imageName
            ]);
        endif;

        return response()->json(['msg' => 'Method successfully created'], 200);
    }

    public function edit(Request $request){
        $data = AccBank::find($request->row_id);

        return response()->json($data);
    }

    public function update(AccBankStoreRequest $request){
        $bankOldRow = AccBank::find($request->id);
        $bank = AccBank::where('id', $request->id)->update([
            'bank_name'=> $request->bank_name,
            'opening_balance'=> ($request->opening_balance > 0 ? $request->opening_balance : 0),
            'opening_date'=> (isset($request->opening_date) && !empty($request->opening_date) ? date('Y-m-d', strtotime($request->opening_date)) : null),
            'audit_status'=> (isset($request->audit_status) && $request->audit_status > 0 ? $request->audit_status : '0'),
            'status'=> (isset($request->status) && $request->status > 0 ? $request->status : '2'),
            'updated_by' => auth()->user()->id
        ]);

        if($request->hasFile('photo')):
            $imageName = 'Bank_'.$request->id.'_'.time() . '.' . $request->photo->getClientOriginalExtension();
            $path = $request->file('photo')->storeAs('public/banks', $imageName, 'local');
            if(isset($bankOldRow->bank_image) && !empty($bankOldRow->bank_image)):
                if (Storage::disk('local')->exists('public/banks/'.$bankOldRow->bank_image)):
                    Storage::disk('local')->delete('public/banks/'.$bankOldRow->bank_image);
                endif;
            endif;
            
            $bankUpdate = AccBank::where('id', $request->id)->update([
                'bank_image' => $imageName
            ]);
        endif;

        return response()->json(['msg' => 'Bank data successfully updated'], 200);
    }

    public function destroy($id){
        $data = AccBank::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = AccBank::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus($id){
        $accMethod = AccBank::find($id);
        $status = (isset($accMethod->status) && $accMethod->status == 1 ? 2 : 1);

        AccBank::where('id', $id)->update([
            'status'=> $status,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Bank Status successfully updated'], 200);
    }
}
