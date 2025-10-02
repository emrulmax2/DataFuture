<?php

namespace App\Http\Controllers;

use App\Models\ReportItAllLog;
use App\Http\Requests\StoreReportItAllLogRequest;
use App\Http\Requests\UpdateReportItAllLogRequest;
use App\Models\ReportItAll;
use App\Models\ReportItAllUpload;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;

use Carbon\Carbon;
class ReportItAllLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    
    public function list(HttpRequest $request){
        $reportItAll = ReportItAll::find($request->report_it_all_id);
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = ReportItAllLog::where('report_it_all_id', $reportItAll->id)->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = ReportItAllLog::where('report_it_all_id', $reportItAll->id)->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            // search in employee name and student name
            $query->where(function($q) use ($queryStr) {
                // this will search via createdBy relationship to get employee name

            });


        endif;
        // if(!empty($reportFrom)):
        //     $query->where('report_form', $reportFrom);
        // endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        
        if($Query->isNotEmpty()):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'description' => $list->description,
                    'created_at' => isset($list->updated_at) ? Carbon::parse($list->updated_at)->format('d M, Y h:i A'): Carbon::parse($list->created_at)->format('d M, Y h:i A'),
                    
                    'created_by' => $list->employee_name,
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportItAllLogRequest $request)
    {
        $reportItAll = ReportItAll::find($request->report_it_all_id);
        $reportItAll->status = 'In Progress';
        $reportItAll->updated_by = auth()->user()->id;
        $reportItAll->save();

        ReportItAllLog::create($request->all());
        return response()->json(['message' => 'Log created successfully']);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(ReportItAllLog $reportItAllLog)
    {
        
    
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReportItAllLog $reportItAllLog)
    {
        return response()->json($reportItAllLog);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportItAllLogRequest $request, ReportItAllLog $reportItAllLog)
    {
        
        $reportItAllLog->update($request->all());
        return response()->json(['message' => 'Log updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReportItAllLog $reportItAllLog)
    {
        $reportItAllLog->delete();
        return response()->json(['message' => 'Log deleted successfully']);
    }


    public function forceDelete($id) 
    {
        $data = ReportItAllLog::where('id', $id)->withTrashed()->forceDelete();

        return response()->json(['status' => 'success', 'message' => 'Report IT Log entry permanently deleted successfully.']);
    }
}
