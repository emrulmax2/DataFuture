<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrMachineStoreRequest;
use App\Http\Requests\HrMachineUpdateRequest;
use App\Models\EmployeeAttendanceMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HrMachineController extends Controller
{
    public function index(){
        return view('pages.settings.hr-machine.index', [
            'title' => 'HR Attendance Machine - LCC Data Future Managment',
            'subtitle' => 'HR Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'HR Machines', 'href' => 'javascript:void(0);']
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

        $query = EmployeeAttendanceMachine::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('username','LIKE','%'.$queryStr.'%');
            $query->orWhere('location','LIKE','%'.$queryStr.'%');
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
                    'name' => $list->name,
                    'username' => $list->username,
                    'location' => $list->location,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(HrMachineStoreRequest $request){
        $data = EmployeeAttendanceMachine::create([
            'name'=> $request->name,
            'username'=> $request->username,
            'password'=> Hash::make($request->password),
            'location'=> (isset($request->location) && !empty($request->location) ? $request->location : null),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Successfully created'], 200);
    }

    public function edit(Request $request){
        $data = EmployeeAttendanceMachine::find($request->rowID);

        return response()->json($data);
    }

    public function update(HrMachineUpdateRequest $request){
        $data = [
            'name'=> $request->name,
            'username'=> $request->username,
            'location'=> (isset($request->location) && !empty($request->location) ? $request->location : null),
            'created_by' => auth()->user()->id
        ];
        if(!empty($request->password)):
            $data['password'] = Hash::make($request->password);
        endif;
        $machine = EmployeeAttendanceMachine::where('id', $request->id)->update($data);

        return response()->json(['msg' => 'Successfully Updated'], 200);
    }

    public function destroy($id){
        $data = EmployeeAttendanceMachine::find($id)->delete();
        return response()->json($data);
    }

    public function restore(Request $request) {
        $data = EmployeeAttendanceMachine::where('id', $request->recordID)->withTrashed()->restore();

        response()->json($data);
    }
}
