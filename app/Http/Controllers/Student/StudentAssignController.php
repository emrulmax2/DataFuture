<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function list(Request $request){
        $planid = (isset($request->planid) && !empty($request->planid) ? $request->planid : 0);
        //$dates = (isset($request->dates) && !empty($request->dates) ? date('Y-m-d', strtotime($request->dates)) : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '1');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Assign::orderByRaw(implode(',', $sorts));
        if(!empty($planid)): $query->where('plan_id', $planid); endif;
        //if(!empty($dates)): $query->where('date', $dates); endif;
        if($status == 2): $query->onlyTrashed(); endif;

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
                if(isset($list->student)) {
                    $list2 = $list->student;
                    $list2Other = $list2->other;
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'disability' =>  (isset($list2Other->disability_status) && $list2Other->disability_status > 0 ? $list2Other->disability_status : 0),
                        'registration_no' => (!empty($list2->registration_no) ? $list2->registration_no : $list2->application_no),
                        'first_name' => $list2->first_name,
                        'last_name' => $list2->last_name,
                        'status_id'=> (isset($list2->status->name) && !empty($list2->status->name) ? $list2->status->name : ''),
                        'url' => route('student.show', $list2->id),
                        'photo_url' => $list2->photo_url,
                        'deleted_at' => $list->deleted_at,
                        'student_id' => $list->student_id,
                        'evening_and_weekend' => (isset($list2->activeCR->propose->full_time) && $list2->activeCR->propose->full_time > 0 ? $list2->activeCR->propose->full_time : 0)
                    ];
                    $i++;
                }
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
