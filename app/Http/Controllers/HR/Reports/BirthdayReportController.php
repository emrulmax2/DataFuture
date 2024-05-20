<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeWorkType;
use App\Models\Employment;

use PDF;
use DateTime;
use App\Exports\BirthdayListExport;
use App\Exports\BirthdayListBySearchExport;
use Maatwebsite\Excel\Facades\Excel;


class BirthdayReportController extends Controller
{
    public function index(Request $request){
        $employeeWorkType = EmployeeWorkType::all();
        $departments = Department::all();
        return view('pages.hr.portal.reports.birthdaylist', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Birthday List', 'href' => 'javascript:void(0);']
            ],
            'employeeWorkType' => $employeeWorkType,
            'departments' => $departments
        ]);
    }

    public function searchlist(Request $request, $paginationOn=true){
        $birthmonth = (isset($request->birthmonth) && !empty($request->birthmonth) ? $request->birthmonth : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $status = $request->status;
     
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if(!empty($birthmonth)): $query->whereMonth('date_of_birth', date('m', strtotime($birthmonth))); endif;
        if(($status)==0): 
            $query->where('status', $status); 
        elseif(($status)==1):  
            $query->where('status', $status); 
        else:
            $query->whereIn('status', [0,1]);  
        endif;
        if(!empty($type) || !empty($department)):

            $query->whereHas('employment', function($qs) use($type, $department){
                if(!empty($type)): $qs->where('employee_work_type_id', $type); endif;
                if(!empty($department)): $qs->where('department_id', $department); endif;
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
            $i = 0;
            
            $monthArray = ["","January","February","March","April","May","June","July","August","September","October","November","December"];
            
            for($j=1;$j<=count($monthArray);$j++) {
                
                $dataArray = [];
                foreach($Query as $list):
                    $birthDate = strtotime($list->date_of_birth);
                    $today = strtotime(date('Y-m-d'));
                    $secs = $today - $birthDate;
    
                    $ageCalVar = new DateTime("@0");
                    $ageDiffSec = new DateTime("@$secs");
                    $ageDiff =  date_diff($ageCalVar, $ageDiffSec);
                    $age = $ageDiff->format('%y Years, %m months and %d days');
                  
                    $foundMonth = date('m', strtotime($list->date_of_birth));
                    $firstName = isset($list->first_name) ? $list->first_name : '';
                    $lastName = isset($list->last_name) ? $list->last_name : '';
                    
                    if($foundMonth==$j):
                        $dataArray[$j][] = [
                            'name' => $firstName.' '.$lastName,
                            'works_no' => isset($list->employment->works_number) ? $list->employment->works_number : '',
                            'gender' => isset($list->sex->name) ? $list->sex->name : '',
                            'date_of_birth' => isset($list->date_of_birth) ? date('F m Y', strtotime($list->date_of_birth)) : '',
                            'age' => isset($list->date_of_birth) ? $age : ''
                        ];
                        
                    endif;
                endforeach;
                if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "month" =>$monthArray[$j], "dataArray" => $dataArray[$j]];
                    $i++;
                }

            }         
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generatePDF(Request $request){
        set_time_limit(300);
        $query = Employee::where('status', '=', 1)->get();

        $data = array();

        $i = 0;
        
        $monthArray = ["","January","February","March","April","May","June","July","August","September","October","November","December"];
        
        for($j=1;$j<=count($monthArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $birthDate = strtotime($list->date_of_birth);
                $today = strtotime(date('Y-m-d'));
                $secs = $today - $birthDate;

                $ageCalVar = new DateTime("@0");
                $ageDiffSec = new DateTime("@$secs");
                $ageDiff =  date_diff($ageCalVar, $ageDiffSec);
                $age = $ageDiff->format('%y Years, %m months and %d days');
                
                $foundMonth = date('m', strtotime($list->date_of_birth));
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';

                if($foundMonth==$j):
                    $dataArray[$j][] = [
                        'name' => $firstName.' '.$lastName,
                        'works_no' => isset($list->employment->works_number) ? $list->employment->works_number : '',
                        'gender' => isset($list->sex->name) ? $list->sex->name : '',
                        'date_of_birth' => isset($list->date_of_birth) ? date('F m Y', strtotime($list->date_of_birth)) : '',
                        'age' => isset($list->date_of_birth) ? $age : ''
                    ];
                    
                endif;
            endforeach;
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "month" =>$monthArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }
        }     
        $dataList = $data;
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.birthdaypdf',compact('dataList'));
        return $pdf->download('Birthday Information.pdf');
    }

    public function generateBirthdayExcel(Request $request)
    {   
        return Excel::download(new BirthdayListExport(), 'Birthday_List.xlsx');
    }

    public function generateBirthdayListbySearchExcel(Request $request)
    {         
        $birthmonth = (isset($request->birthmonth) && !empty($request->birthmonth) ? $request->birthmonth : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $status = $request->status;
        
        $data = $this->searchlist($request,false);
        
        $returnData = json_decode($data->getContent(), true);
                
        return Excel::download(new BirthdayListBySearchExport($returnData), 'Birthday_List.xlsx');
    }

    public function generateSearchPDF(Request $request){
        set_time_limit(300);
        $birthmonth = (isset($request->birthmonth) && !empty($request->birthmonth) ? $request->birthmonth : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $status = $request->status;
        
        $data = $this->searchlist($request,false);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.birthdaylistbysearchpdf',compact('returnData'));
        return $pdf->download('Birthday Information.pdf');
    }
}