<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\HolidayYearRequest;
use App\Models\HrHolidayYear;
use App\Models\HrHolidayYearLeaveOption;
use Illuminate\Http\Request;

class HolidayYearController extends Controller
{
    public function index(){
        return view('pages.settings.holiday-year.index', [
            'title' => 'Holiday Years - London Churchill College',
            'subtitle' => 'HR Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Holiday Years', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $total_rows = $count = HrHolidayYear::count();
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

        $query = HrHolidayYear::orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where('start_date','LIKE','%'.$queryStr.'%');
            $query->orWhere('end_date','LIKE','%'.$queryStr.'%');
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
                    'year' => date('Y', strtotime($list->start_date)).' - '.date('Y', strtotime($list->end_date)),
                    'start_date' => $list->start_date,
                    'end_date' => $list->end_date,
                    'notice_period' => $list->notice_period,
                    'active' => $list->active,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function store(HolidayYearRequest $request){
        $data = HrHolidayYear::create([
            'start_date'=> $request->start_date,
            'end_date'=> $request->end_date,
            'notice_period'=> $request->notice_period,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'created_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Holiday year successfully created'], 200);
    }

    public function edit(Request $request){
        $data = HrHolidayYear::find($request->rowID);
        $data['start_date_modified'] = date('Y-m-d', strtotime($data->start_date));

        return response()->json($data);
    }

    public function update(HolidayYearRequest $request){
        $data = HrHolidayYear::where('id', $request->id)->update([
            'start_date'=> (isset($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null),
            'end_date'=> (isset($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null),
            'notice_period'=> $request->notice_period,
            'active'=> (isset($request->active) && $request->active > 0 ? $request->active : 0),
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['msg' => 'Holiday year successfully created'], 200);
    }

    public function destroy($id){
        $data = HrHolidayYear::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = HrHolidayYear::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    public function updateStatus(Request $request){
        $HrHolidayYear = HrHolidayYear::find($request->recordID);
        $active = (isset($HrHolidayYear->active) && $HrHolidayYear->active == 1 ? 0 : 1);

        HrHolidayYear::where('id', $request->recordID)->update([
            'active'=> $active,
            'updated_by' => auth()->user()->id
        ]);

        return response()->json(['message' => 'Status successfully updated'], 200);
    }

    public function leaveOptions($id){
        return view('pages.settings.holiday-year.leave-options', [
            'title' => 'Holiday Year Leave Option - London Churchill College',
            'subtitle' => 'HR Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Holiday Years', 'href' => 'javascript:void(0);']
            ],
            'holidayYear' => HrHolidayYear::find($id),
            'leaveOptions' => HrHolidayYearLeaveOption::where('hr_holiday_year_id', $id)->pluck('leave_option')->toArray()
        ]);
    }

    public function updateLeaveOptions(Request $request){
        $hr_holiday_year_id = $request->hr_holiday_year_id;
        $leave_options = (isset($request->leave_options) && !empty($request->leave_options) ? $request->leave_options : []);

        HrHolidayYearLeaveOption::where('hr_holiday_year_id', $hr_holiday_year_id)->forceDelete();
        if(!empty($leave_options)):
            foreach($leave_options as $lo):
                $data = [];
                $data['hr_holiday_year_id'] = $hr_holiday_year_id;
                $data['leave_option'] = $lo;
                $data['created_by'] = auth()->user()->id;

                HrHolidayYearLeaveOption::create($data);
            endforeach;
        endif;

        return response()->json(['message' => 'Holiday Year Leave Option Successfully Updated.'], 200);
    }
}
