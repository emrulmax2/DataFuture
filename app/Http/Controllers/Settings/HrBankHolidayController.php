<?php

namespace App\Http\Controllers\Settings;

use App\Exports\HrBankHolidayExport;
use App\Http\Controllers\Controller;
use App\Imports\HrBankHolidayImport;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HrBankHolidayController extends Controller
{
    public function index($id){
        return view('pages.settings.holiday-year.bank-holiday.index', [
            'title' => 'Bank Holiday - London Churchill College',
            'subtitle' => 'HR Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Bank Holidays', 'href' => 'javascript:void(0);']
            ],
            'theYear' => HrHolidayYear::where('id', $id)->get()->first()
        ]);
    }


    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $holidayYear = (isset($request->holidayyear) && $request->holidayyear > 0 ? $request->holidayyear : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = HrBankHoliday::orderByRaw(implode(',', $sorts))->where('hr_holiday_year_id', $holidayYear);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
            $query->orWhere('start_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('end_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('description','LIKE','%'.$queryStr.'%');
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
                    'year' => (isset($list->year->start_date) && !empty($list->year->start_date) ? date('Y', strtotime($list->year->start_date)).' - ' : '').(isset($list->year->end_date) && !empty($list->year->end_date) ? date('Y', strtotime($list->year->end_date)) : ''),
                    'name' => $list->name,
                    'start_date' => $list->start_date,
                    'end_date' => $list->end_date,
                    'duration' => $list->duration,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;

        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function edit(Request $request){
        $data = HrBankHoliday::find($request->rowID);
        $data['start_date_modified'] = date('Y-m-d', strtotime($data->start_date));

        return response()->json($data);
    }

    public function update(Request $request){
        $data = HrBankHoliday::where('id', $request->id)->update([
            'start_date'=> (isset($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date'=> (isset($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'name'=> $request->name,
            'duration'=> $request->duration,
            'description'=> (isset($request->description) && !empty($request->description) ? $request->description : null),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Bank Holiday successfully created'], 200);
    }

    public function destroy($id){
        $data = HrBankHoliday::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = HrBankHoliday::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }


    public function export($id){
        return Excel::download(new HrBankHolidayExport($id), 'bank_holidays_sample.csv');        
    }

    public function import(Request $request) {
        $file = $request->file('file');
        $hr_holiday_year_id = $request->hr_holiday_year_id;
        
        Excel::import(new HrBankHolidayImport($hr_holiday_year_id), $file);
        return response()->json(['message' => 'Data Uploaded!'], 202);
    }
}
