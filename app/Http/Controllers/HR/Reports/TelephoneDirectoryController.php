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
use App\Exports\TelephoneDirectoryExport;
use App\Exports\TelephoneDirectoryBySearchExport;
use Maatwebsite\Excel\Facades\Excel;

class TelephoneDirectoryController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.telephonedirectory', [
            'title' => 'Telephone Directory - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Telephone Directory', 'href' => 'javascript:void(0);']
            ],
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'employeeWorkType' => EmployeeWorkType::all(),
            'departments' => Department::all(),
            'gender' => SexIdentifier::all()
        ]);
    }

    public function searchlist(Request $request, $paginationOn=true){
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
        if(!empty($ethnicity)): $query->where('ethnicity_id', $ethnicity); endif;
        if(!empty($nationality)): $query->where('nationality_id', $nationality); endif;
        if(!empty($gender)): $query->where('sex_identifier_id',$gender); endif;
        if(($status)==0): 
            $query->where('status', $status); 
        elseif(($status)==1):  
            $query->where('status', $status); 
        else:
            $query->whereIn('status', [0,1]);  
        endif;

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
            $i = 0;
            
            $alphabetArray = ["","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
            
            for($j=1;$j<=count($alphabetArray);$j++) {
                
                $dataArray = [];
                foreach($Query as $list):
                    $string = strtoupper(($list->first_name)[0]);
                    $length = strlen($string);
                    $number = 0;
                    $level = 1;
                    while ($length >= $level ) {
                        $char = $string[$length - $level];
                        $c = ord($char) - 64;        
                        $number += $c * (26 ** ($level-1));
                        $level++;
                    }
                    $firstName = isset($list->first_name) ? $list->first_name : '';
                    $lastName = isset($list->last_name) ? $list->last_name : '';
        
                    if($number==$j):
                        
                        $dataArray[$j][] = [
                            'name' => $firstName.' '.$lastName,
                            'telephone' => isset($list->telephone) ? $list->telephone : '',
                            'mobile' => isset($list->mobile) ? $list->mobile : '',
                            'email' => isset($list->email) ? $list->email : '',
                        ];
                        
                    endif;
                endforeach;
                if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                    $data[$i] = ["id"=>$j, "firstcha" =>$alphabetArray[$j], "dataArray" => $dataArray[$j]];
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
            
        $alphabetArray = ["","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
        
        for($j=1;$j<=count($alphabetArray);$j++) {
            
            $dataArray = [];
            foreach($query as $list):
                $string = strtoupper(($list->first_name)[0]);
                $length = strlen($string);
                $number = 0;
                $level = 1;
                while ($length >= $level ) {
                    $char = $string[$length - $level];
                    $c = ord($char) - 64;        
                    $number += $c * (26 ** ($level-1));
                    $level++;
                }
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';
       
                if($number==$j):
                    $dataArray[$j][] = [
                        'name' => $firstName.' '.$lastName,
                        'telephone' => isset($list->telephone) ? $list->telephone : '',
                        'mobile' => isset($list->mobile) ? $list->mobile : '',
                        'email' => isset($list->email) ? $list->email : '',
                    ];
                    
                endif;
            endforeach;
            if(isset($dataArray[$j]) && count($dataArray[$j])>0) {
                $data[$i] = ["id"=>$j, "firstcha" =>$alphabetArray[$j], "dataArray" => $dataArray[$j]];
                $i++;
            }

        }     
        $dataList = $data;
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.telephonedirectorypdf',compact('dataList'));
        return $pdf->download('Employee Telephone Directory.pdf');
    }

    public function generateTelephoneDirectoryExcel(Request $request)
    {   
        return Excel::download(new TelephoneDirectoryExport(), 'Employee_Telephone_Directory.xlsx');
    }

    public function generateTelephoneDirectorybySearchExcel(Request $request)
    {         
        $startdate = (isset($request->startdate) && !empty($request->startdate) ? $request->startdate : '');
        $enddate = (isset($request->enddate) && !empty($request->enddate) ? $request->enddate : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $ethnicity = (isset($request->ethnicity) && !empty($request->ethnicity) ? $request->ethnicity : '');
        $nationality = (isset($request->nationality) && !empty($request->nationality) ? $request->nationality : '');
        $gender = (isset($request->gender) && !empty($request->gender) ? $request->gender : '');
        $status = $request->status;
        
        $data = $this->searchlist($request,false);
        
        $returnData = json_decode($data->getContent(), true);
                
        return Excel::download(new TelephoneDirectoryBySearchExport($returnData), 'Employee_Telephone_Directory.xlsx');
    }

    public function generateSearchPDF(Request $request){
        set_time_limit(300);
        $startdate = (isset($request->startdate) && !empty($request->startdate) ? $request->startdate : '');
        $enddate = (isset($request->enddate) && !empty($request->enddate) ? $request->enddate : '');
        $type = (isset($request->worktype) && !empty($request->worktype) ? $request->worktype : '');
        $department = (isset($request->department) && !empty($request->department) ? $request->department : '');
        $ethnicity = (isset($request->ethnicity) && !empty($request->ethnicity) ? $request->ethnicity : '');
        $nationality = (isset($request->nationality) && !empty($request->nationality) ? $request->nationality : '');
        $gender = (isset($request->gender) && !empty($request->gender) ? $request->gender : '');
        $status = $request->status;
        
        $data = $this->searchlist($request,false);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.telephonedirectorybysearchpdf',compact('returnData'));
        return $pdf->download('Employee Telephone Directory.pdf');
    }
}
