<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Country;
use App\Models\Employee;
use App\Models\Ethnicity;
use App\Models\Department;
use App\Models\EmployeeWorkType;
use App\Models\SexIdentifier;
use PDF;

class StarterReportController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.starterreport', [
            'title' => 'Employee Starter Report - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Employee Starter', 'href' => 'javascript:void(0);']
            ],
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'employeeWorkType' => EmployeeWorkType::all(),
            'departments' => Department::all(),
            'gender' => SexIdentifier::all()
        ]);
    }

    public function list(Request $request, $paginationOn=false){
        $startdate = (isset($request->startdate) && !empty($request->startdate) ? $request->startdate : '');
        $enddate = (isset($request->enddate) && !empty($request->enddate) ? $request->enddate : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $ethnicity = (isset($request->ethnicity) && !empty($request->ethnicity) ? $request->ethnicity : '');
        $nationality = (isset($request->nationality) && !empty($request->nationality) ? $request->nationality : '');
        $gender = (isset($request->gender) && !empty($request->gender) ? $request->gender : '');
        $status = $request->status;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if(!empty($ethnicity)): $query->where('ethnicity_id', 'LIKE', '%'.$ethnicity.'%'); endif;
        if(!empty($nationality)): $query->where('nationality_id', 'LIKE', '%'.$nationality.'%'); endif;
        if(!empty($gender)): $query->where('sex_identifier_id', 'LIKE', '%'.$gender.'%'); endif;
        if(($status)==0): $query->where('status', $status); else: $query->where('status', '>', 0); endif;

        if(!empty($type) || !empty($department) || !empty($startdate) || !empty($enddate)):
            $query->whereHas('employment', function($qs) use($type, $department, $startdate, $enddate){
                if(!empty($type)): $qs->where('employee_work_type_id', $type); endif;
                if(!empty($department)): $qs->where('department_id', $department); endif;
                if(!empty($startdate)): $qs->whereDate('started_on', '<=', $startdate); endif;
                if(!empty($enddate)): $qs->whereDate('ended_on', '>=', $enddate); endif;
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        if($paginationOn==true)
            $Query = $query->skip($offset)
                ->take($limit)
                ->get();
        else
            $Query = $query->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'last_name' => isset($list->last_name) ? $list->last_name : '',
                    'first_name' => isset($list->first_name) ? $list->first_name : '',
                    'works_number' => isset($list->employment->works_number) ? $list->employment->works_number : '',
                    'started_on' => isset($list->employment->started_on) ? date('F d, Y',strtotime($list->employment->started_on)) : '',
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generatePDF(Request $request)
    {
        $items = Employee::where('status', '=', 1)->get();
        $items->load(['employment']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $employment = $item->employment;
            $dataList[$i++] = [
                'last_name' => isset($item->last_name) ? $item->last_name : '',
                'first_name' => isset($item->first_name) ? $item->first_name : '',
                'works_number' => isset($employment->works_number) ? $employment->works_number : '',
                'started_on' => isset($employment->started_on) ? date('F d, Y',strtotime($employment->started_on)) : '',
            ];
        } 

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.starterpdf',compact('dataList'));
        return $pdf->download('Employee Starter.pdf');

        //return view('pages.hr.portal.reports.starterpdf', compact('dataList'));
    }

    public function generateSearchPDF(Request $request){
        $startdate = (isset($request->startdate) && !empty($request->startdate) ? $request->startdate : '');
        $enddate = (isset($request->enddate) && !empty($request->enddate) ? $request->enddate : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $ethnicity = (isset($request->ethnicity) && !empty($request->ethnicity) ? $request->ethnicity : '');
        $nationality = (isset($request->nationality) && !empty($request->nationality) ? $request->nationality : '');
        $gender = (isset($request->gender) && !empty($request->gender) ? $request->gender : '');
        $status = $request->status;
        
        $data = $this->list($request,false);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.starterbysearchpdf',compact('returnData'));
        return $pdf->download('Employee Starter.pdf');
    }
}
