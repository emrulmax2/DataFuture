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
use DateTime;
use App\Exports\LengthServiceExport;
use App\Exports\LengthServiceBySearchExport;
use Maatwebsite\Excel\Facades\Excel;

class LengthServiceController extends Controller
{
    public function index(){
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 0;
            
        $yearArray = ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53"];
        
        for($j=0;$j<=count($yearArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $startedOn = strtotime($list->employment->started_on);
                $today = strtotime(date('Y-m-d'));
                if(isset($list->workingPattern->end_to) && $list->workingPattern->end_to != ''){
                    $endedOn = strtotime($list->workingPattern->end_to);
                    $secs = $endedOn - $startedOn;                    
                }else
                    $secs = $today - $startedOn;

                $lenCalVar = new DateTime("@0");
                $lenDiffSec = new DateTime("@$secs");
                $lenDiff =  date_diff($lenCalVar, $lenDiffSec);
                $length = $lenDiff->format('%y Years, %m months and %d days');
                $yearLength = $lenDiff->format('%y');
                
                if($yearLength==$j):
                    $dataArray[$j][] = [
                        'name' => $list->first_name.' '.$list->last_name,
                        'started_on' => $list->employment->started_on,
                        'ended_on' => isset($list->workingPattern->end_to) ? $list->workingPattern->end_to : '',
                        'length' => $length,
                    ];
                    
                endif;
            endforeach;
            
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "year" =>$yearArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }
        }
        return view('pages.hr.portal.reports.lengthservice', [
            'title' => 'Service Lengths - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Service Lengths', 'href' => 'javascript:void(0);']
            ],
            'dataList' => $data,
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'employeeWorkType' => EmployeeWorkType::all(),
            'departments' => Department::all(),
            'gender' => SexIdentifier::all()
        ]);
    }

    public function searchlist(Request $request){
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
            $i = 0;
            
            $yearArray = ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53"];
            
            for($j=0;$j<=count($yearArray);$j++) {
                
                $dataArray = [];
                foreach($Query as $list):
                    $startedOn = strtotime($list->employment->started_on);
                    $today = strtotime(date('Y-m-d'));
                    if(isset($list->workingPattern->end_to) && $list->workingPattern->end_to != ''){
                        $endedOn = strtotime($list->workingPattern->end_to);
                        $secs = $endedOn - $startedOn;                    
                    }else
                        $secs = $today - $startedOn;

                    $lenCalVar = new DateTime("@0");
                    $lenDiffSec = new DateTime("@$secs");
                    $lenDiff =  date_diff($lenCalVar, $lenDiffSec);
                    $length = $lenDiff->format('%y Years, %m months and %d days');
                
                    $yearLength = $lenDiff->format('%y');

                    if($yearLength==$j):
                        $dataArray[$j][] = [
                            'name' => $list->first_name.' '.$list->last_name,
                            'started_on' => $list->employment->started_on,
                            'ended_on' => isset($list->workingPattern->end_to) ? $list->workingPattern->end_to : '',
                            'length' => $length,
                        ];
                        
                    endif;
                endforeach;
                if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                    $data[$i] = ["id"=>$j, "year" =>$yearArray[$j], "dataArray" => $dataArray[$j]];
                    $i++;
                }

            }         
        endif;
        
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generatePDF(Request $request){
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 1;
        
        $yearArray = ["0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31","32","33","34","35","36","37","38","39","40","41","42","43","44","45","46","47","48","49","50","51","52","53"];
        
            for($j=0;$j<=count($yearArray);$j++) {
                
                $dataArray = [];
                foreach($query as $list):
                    $startedOn = strtotime($list->employment->started_on);
                    $today = strtotime(date('Y-m-d'));
                    if(isset($list->workingPattern->end_to) && $list->workingPattern->end_to != ''){
                        $endedOn = strtotime($list->workingPattern->end_to);
                        $secs = $endedOn - $startedOn;                    
                    }else
                        $secs = $today - $startedOn;

                    $lenCalVar = new DateTime("@0");
                    $lenDiffSec = new DateTime("@$secs");
                    $lenDiff =  date_diff($lenCalVar, $lenDiffSec);
                    $length = $lenDiff->format('%y Years, %m months and %d days');
                
                    $yearLength = $lenDiff->format('%y');
                
                    if($yearLength==$j):
                        $dataArray[$j][] = [
                            'name' => $list->first_name.' '.$list->last_name,
                            'started_on' => $list->employment->started_on,
                            'ended_on' => isset($list->workingPattern->end_to) ? $list->workingPattern->end_to : '',
                            'length' => $length,
                        ];
                        
                    endif;
                endforeach;
                if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                    $data[$i] = ["id"=>$j, "year" =>$yearArray[$j], "dataArray" => $dataArray[$j]];
                    $i++;
                }

            }     
        $dataList = $data;
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.lengthservicepdf',compact('dataList'));
        return $pdf->download('Employee Service Lengths.pdf');
    }

    public function generateBirthdayExcel(Request $request)
    {   
        return Excel::download(new LengthServiceExport(), 'Employee_Service_Lengths.xlsx');
    }

    public function generateLengthServicebySearchExcel(Request $request)
    {         
        $startdate = (isset($request->startdate) && !empty($request->startdate) ? $request->startdate : '');
        $enddate = (isset($request->enddate) && !empty($request->enddate) ? $request->enddate : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $ethnicity = (isset($request->ethnicity) && !empty($request->ethnicity) ? $request->ethnicity : '');
        $nationality = (isset($request->nationality) && !empty($request->nationality) ? $request->nationality : '');
        $gender = (isset($request->gender) && !empty($request->gender) ? $request->gender : '');
        $status = $request->status;
        
        $data = $this->searchlist($request);
        
        $returnData = json_decode($data->getContent(), true);
                
        return Excel::download(new LengthServiceBySearchExport($returnData), 'Employee_Service_Lengths.xlsx');
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
        
        $data = $this->searchlist($request);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.lengthservicebysearchpdf',compact('returnData'));
        return $pdf->download('Employee Service Lengths.pdf');
    }
}
