<?php

namespace App\Http\Controllers;

use App\Models\BankHoliday;
use Illuminate\Http\Request;
use App\Http\Requests\BankHolidayRequests;
use App\Http\Requests\BankHolidayUpdateRequests;
use App\Models\AcademicYear;
use App\Models\User;

use App\Exports\HolidayExport;
use App\Imports\HolidayImport;
use Maatwebsite\Excel\Facades\Excel;

class BankHolidayController extends Controller
{
    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $academicyear = (isset($request->academicyear) && $request->academicyear > 0 ? $request->academicyear : '');

        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
        $query = BankHoliday::where('academic_year_id', $academicyear);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        $total_rows = $query->count();
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = BankHoliday::where('academic_year_id', $academicyear)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('title','LIKE','%'.$queryStr.'%');
            $query->orWhere('type','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
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
                    'start_date' => $list->start_date,
                    'end_date' => $list->end_date,
                    'duration' => $list->duration,
                    'title' => $list->title,
                    'type'=> $list->type,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BankHolidayRequests $request)
    {
        $request->request->add(['created_by' => auth()->user()->id]);
        $data = BankHoliday::create($request->all());
        
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankHoliday  $bankHoliday
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BankHoliday  $bankHoliday
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = BankHoliday::find($id);

        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankHoliday  $bankHoliday
     * @return \Illuminate\Http\Response
     */
    public function update(BankHolidayUpdateRequests $request, BankHoliday $dataId)
    {
        $data = BankHoliday::where('id', $request->id)->update([
            'start_date' => date('Y-m-d', strtotime($request->start_date)),
            'end_date' => date('Y-m-d', strtotime($request->end_date)),
            'duration' => $request->duration,
            'title' => $request->title,
            'type'=> $request->type,       
            'updated_by' => auth()->user()->id
        ]);

        return response()->json($data);


        if($data->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'No data Modified'], 304);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankHoliday  $bankHoliday
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = BankHoliday::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = BankHoliday::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }
    public function import(Request $request) {
        $file = $request->file('file');
        
        Excel::import(new HolidayImport($request->input('academic_year_id')),$file);
        return back()->with('success', 'Holiday Data Uploaded');
    }

    public function export(Request $request)
    {

        return Excel::download(new HolidayExport(), 'bankholidays.xlsx');        
    }
}
