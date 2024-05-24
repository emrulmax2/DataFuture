<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Country;
use App\Models\Ethnicity;
use App\Models\Department;
use App\Models\EmployeeWorkType;
use App\Models\SexIdentifier;
use App\Models\EmployeeEmergencyContact;
use App\Exports\ContactDetailExport;
use App\Exports\ContactDetailBySearchExport;
use Maatwebsite\Excel\Facades\Excel;

use PDF;

class EmployeeContactDetailController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.contactdetail', [
            'title' => 'Employee Contact Details - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Employee Contact Details', 'href' => 'javascript:void(0);']
            ],
           'country' => Country::all(),
           'ethnicity' => Ethnicity::all(),
           'employeeWorkType' => EmployeeWorkType::all(),
           'departments' => Department::all(),
           'gender' => SexIdentifier::all()
        ]);
    }

    public function list(Request $request, $paginationOn=true){
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
            $i = 1;
            foreach($Query as $list):
                $addressOne = isset($list->address->address_line_1) ? $list->address->address_line_1 : '';
                $addressTwo = isset($list->address->address_line_2) ? $list->address->address_line_2 : '';
                $data[] = [
                    'name' => $list->first_name.' '.$list->last_name,
                    'address' => $addressOne.','.$addressTwo,
                    'post_code' => isset($list->post_code) ? $list->post_code : '',
                    'telephone' => isset($list->telephone) ? $list->telephone : '',
                    'mobile' => isset($list->mobile) ? $list->mobile : '',
                    'email' => isset($list->email) ? $list->email : '',
                    'emergency_telephone' => isset($list->emergencyContact->emergency_contact_telephone) ? $list->emergencyContact->emergency_contact_telephone : '',
                    'emergency_mobile' => isset($list->emergencyContact->emergency_contact_mobile) ? $list->emergencyContact->emergency_contact_mobile : '',
                    'emergency_email' => isset($list->emergencyContact->emergency_contact_email) ? $list->emergencyContact->emergency_contact_email : ''
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generatePDF(Request $request)
    {
        set_time_limit(300);
        $items = Employee::where('status', '=', 1)->get();
        $items->load(['address','emergencyContact']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $emergencyContact = $item->emergencyContact;
            $address = $item->address;
            $addressOne = isset($address->address_line_1) ? $address->address_line_1 : '';
            $addressTwo = isset($address->address_line_2) ? $address->address_line_2 : '';
            $dataList[$i++] = [
                'name' => $item->first_name.' '.$item->last_name,
                'address' => $addressOne.','.$addressTwo,
                'post_code' => isset($item->post_code) ? $item->post_code : '',
                'telephone' => isset($item->telephone) ? $item->telephone : '',
                'mobile' => isset($item->mobile) ? $item->mobile : '',
                'email' => isset($item->email) ? $item->email : '',
                'emergency_telephone' => isset($emergencyContact->emergency_contact_telephone) ? $emergencyContact->emergency_contact_telephone : '',
                'emergency_mobile' => isset($emergencyContact->emergency_contact_mobile) ? $emergencyContact->emergency_contact_mobile : '',
                'emergency_email' => isset($emergencyContact->emergency_contact_email) ? $emergencyContact->emergency_contact_email : ''
            ];
        } 

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.contactpdf',compact('dataList'));
        return $pdf->download('Employee Contact Details.pdf');

        //return view('pages.hr.portal.reports.pdf.contactpdf', compact('dataList'));
    }

    public function generateContactExcel(Request $request)
    {   
        return Excel::download(new ContactDetailExport(), 'Contact_Detail.xlsx');
    }

    public function generateSearchExcel(Request $request)
    {          
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
                
        return Excel::download(new ContactDetailBySearchExport($returnData), 'Contact_Details.xlsx');
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
        
        $data = $this->list($request,false);
        
        $returnData = json_decode($data->getContent(), true);
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.contactbysearchpdf',compact('returnData'));
        return $pdf->download('Contact Details.pdf');
    }

    public function searchlist(Request $request){
        $data = $this->list($request,false);
        $returnData = json_decode($data->getContent(), true);
        
        $i = 0;
        $dataList =[];
        foreach($returnData['data'] as $data){
            
            $dataList[$i++] = [
                'Name' => $data['name'],
                'Address' => $data['address'],
                'Post Code' => $data['post_code'],
                'Telephone' => isset($data['telephone']) ? $data['telephone'] : '',
                'Mobile' => isset($data['mobile'] ) ? $data['mobile'] : '',
                'Email' => isset($data['email']) ? $data['email']  : '',
                'Emergency Telephone' =>  isset($data['emergency_telephone']) ? $data['emergency_telephone'] : '',
                'Emergency Mobile' => isset($data['emergency_mobile']) ? $data['emergency_mobile'] : '',
                'Emergency Email' => isset($data['emergency_email']) ? $data['emergency_email'] : ''
            ];
        }
        //dd($dataList);
        return response()->json(['res' => $dataList], 200);
    }
}
