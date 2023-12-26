<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Ethnicity;
use App\Models\Department;
use App\Models\EmployeeWorkType;
use App\Models\SexIdentifier;

use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiversityReportController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.diversityreport', [
            'title' => 'Diversity Information - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Diversity Information', 'href' => 'javascript:void(0);']
            ],
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'employeeWorkType' => EmployeeWorkType::all(),
            'departments' => Department::all(),
            'gender' => SexIdentifier::all()
        ]);
    }

    public function list(Request $request){
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
                if(!empty($enddate)): $qs->whereDate('started_on', '>=', $enddate); endif;
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'name' => $list->first_name.' '.$list->last_name,
                    'works_no' => $list->employment->works_number,
                    'gender' => $list->sex->name,
                    'ethnicity' => $list->ethnicity->name,
                    'nationality' => $list->nationality->name,
                    'status' => $list->disability_status
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generatePDF(Request $request)
    {
        $items = Employee::where('status', '=', 1)->get();
        $items->load(['sex','ethnicity','nationality','employment']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $sex = $item->sex;
            $ethnicity = $item->ethnicity;
            $nationality = $item->nationality;
            $employment = $item->employment;
            //dd($item->first_name);
            $dataList[$i++] = [
                'name' => $item->first_name.' '.$item->last_name,
                'works_no' => $employment->works_number,
                'gender' => $sex->name,
                'ethnicity' => $ethnicity->name,
                'nationality' => $nationality->name,
                'status' => $item->disability_status
            ];
        } 
        
        //view()->share('items',$items,'sex',$sex);

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.diversitypdf',compact('dataList'));
        return $pdf->download('Diversity Information.pdf');

        //return view('pages.hr.portal.reports.diversitypdf', compact('dataList'));
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
        
        $data = $this->list($request);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.diversitybysearchpdf',compact('returnData'));
        return $pdf->download('Diversity Information.pdf');
    }
}
