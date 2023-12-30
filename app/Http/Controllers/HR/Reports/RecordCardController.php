<?php

namespace App\Http\Controllers\HR\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Address;
use App\Models\Country;
use App\Models\Ethnicity;
use App\Models\Department;
use App\Models\EmployeeWorkType;
use App\Models\SexIdentifier;
use App\Models\EmployeeEmergencyContact;
use App\Exports\RecordCardExport;
use App\Exports\RecordCardBySearchExport;
use Maatwebsite\Excel\Facades\Excel;

use PDF;

class RecordCardController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.recordcard', [
            'title' => 'Employee Record Card - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Employee Record Card', 'href' => 'javascript:void(0);']
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
                $addressOne = isset($list->address->address_line_1) ? $list->address->address_line_1 : '';
                $addressTwo = isset($list->address->address_line_2) ? $list->address->address_line_2 : '';
                $firstName = isset($list->first_name) ? $list->first_name : '';
                $lastName = isset($list->last_name) ? $list->last_name : '';
                $data[] = [
                    'title' => isset($list->title->name) ? $list->title->name : '',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'full_name' => $firstName.' '.$lastName,
                    'dob' => isset($list->date_of_birth) ? $list->date_of_birth : '',
                    'ethnicity' => isset($list->ethnicity_id) ? $list->ethnicity->name : '',
                    'nationality' => isset($list->nationality_id) ? $list->nationality->name : '',
                    'ni_number' => isset($list->ni_number) ? $list->ni_number : '',
                    'gender' => isset($list->sex_identifier_id) ? $list->sex->name : '',

                    'started_on' => isset($list->employment->started_on) ? $list->employment->started_on : '',
                    'works_number' => isset($list->employment->works_number) ? $list->employment->works_number : '',
                    'end_to' => isset($list->workingPattern->end_to) ? $list->workingPattern->end_to : '',
                    'job_title' => isset($list->employment->employee_job_title_id) ? $list->employment->employeeJobTitle->name : '',
                    'job_status' => ($list->status== 1) ? 'Active' : 'Inactive',

                    'address' => $addressOne.','.$addressTwo,
                    'telephone' => isset($list->telephone) ? $list->telephone : '',
                    'mobile' => isset($list->mobile) ? $list->mobile : '',
                    'email' => isset($list->email) ? $list->email : '',
                    'emergency_telephone' => isset($list->emergencyContact->emergency_contact_telephone) ? $list->emergencyContact->emergency_contact_telephone : '',
                    'emergency_mobile' => isset($list->emergencyContact->emergency_contact_mobile) ? $list->emergencyContact->emergency_contact_mobile : '',
                    'emergency_email' => isset($list->emergencyContact->emergency_contact_email) ? $list->emergencyContact->emergency_contact_email : '',
                    
                    'disability' => $list->disability_status,
                    'car_reg' => isset($list->car_reg_number) ? $list->car_reg_number : '',
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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

        $data = $this->list($request,false);
        $returnData = json_decode($data->getContent(), true);
        
        $i = 0;
        $dataList =[];
        foreach($returnData['data'] as $data){
            
            $dataList[$i++] = [
                'Record Card For '.$data['title'].' '.$data['full_name'] => '',
                'Personal Details' => '',
                'Title' => $data['title'],
                'Date of Birth' => $data['dob'],
                'Surname' => $data['first_name'],
                'Ethinic Origin' => $data['ethnicity'],
                'Forename' => $data['last_name'],
                'Nationality' => $data['nationality'],
                'Gender' => $data['gender'],
                'Ni Number' => $data['ni_number'],

                'Employment Details' => '',
                'Company Name' => 'London Churchill College',
                'Started On' => $data['started_on'],
                'Work No' => $data['works_number'],
                'Ended On' => $data['end_to'],
                'Job Title' => $data['job_title'],
                'Grade' => $data['job_title'],
                'Emergency Telephone' =>  isset($data['emergency_telephone']) ? $data['emergency_telephone'] : '',
                'Emergency Mobile' => isset($data['emergency_mobile']) ? $data['emergency_mobile'] : '',
                'Current Status' => $data['job_status'],
                'Emergency Email' => isset($data['emergency_email']) ? $data['emergency_email'] : '', 

                'Contact Information' => '',
                'Address' => $data['address'],
                'Telephone' => isset($data['telephone']) ? $data['telephone'] : '',
                'Mobile' => isset($data['mobile'] ) ? $data['mobile'] : '',
                'Email' => isset($data['email']) ? $data['email']  : '',

                'Other Details' => '',
                'Disabled' => $data['disability'],      
                'Car Reg.' => $data['car_reg'],
            ];
        }
        //dd($dataList);
        return response()->json(['res' => $dataList], 200);
    }

    public function generatePDF(Request $request)
    {
        $items = Employee::where('status', '=', 1)->get();
        $items->load(['address','emergencyContact']);

        $i = 0;
        $dataList =[];

        foreach($items as $item) {
            $emergencyContact = $item->emergencyContact;
            $address = $item->address;
            $addressOne = isset($address->address_line_1) ? $address->address_line_1 : '';
            $addressTwo = isset($address->address_line_2) ? $address->address_line_2 : '';
            $firstName = isset($item->first_name) ? $item->first_name : '';
            $lastName = isset($item->last_name) ? $item->last_name : '';
            $dataList[$i++] = [
                'title' => isset($item->title->name) ? $item->title->name : '',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $firstName.' '.$lastName,
                'dob' => isset($item->date_of_birth) ? $item->date_of_birth : '',
                'ethnicity' => isset($item->ethnicity_id) ? $item->ethnicity->name : '',
                'nationality' => isset($item->nationality_id) ? $item->nationality->name : '',
                'ni_number' => isset($item->ni_number) ? $item->ni_number : '',
                'gender' => isset($item->sex_identifier_id) ? $item->sex->name : '',

                'started_on' => isset($item->employment->started_on) ? $item->employment->started_on : '',
                'works_number' => isset($item->employment->works_number) ? $item->employment->works_number : '',
                'end_to' => isset($item->workingPattern->end_to) ? $item->workingPattern->end_to : '',
                'job_title' => isset($item->employment->employee_job_title_id) ? $item->employment->employeeJobTitle->name : '',
                'job_status' => ($item->status== 1) ? 'Active' : 'Inactive',
                'address' => $addressOne.','.$addressTwo,
                'post_code' => isset($item->post_code) ? $item->post_code : '',
                'telephone' => isset($item->telephone) ? $item->telephone : '',
                'mobile' => isset($item->mobile) ? $item->mobile : '',
                'email' => isset($item->email) ? $item->email : '',
                'emergency_telephone' => isset($emergencyContact->emergency_contact_telephone) ? $emergencyContact->emergency_contact_telephone : '',
                'emergency_mobile' => isset($emergencyContact->emergency_contact_mobile) ? $emergencyContact->emergency_contact_mobile : '',
                'emergency_email' => isset($emergencyContact->emergency_contact_email) ? $emergencyContact->emergency_contact_email : '',
                'disability' => $item->disability_status,
                'car_reg' => isset($item->car_reg_number) ? $item->car_reg_number : '',
            ];
        } 

        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.recordcardpdf',compact('dataList'));
        return $pdf->download('Employee Record Card.pdf');

        //return view('pages.hr.portal.reports.pdf.contactpdf', compact('dataList'));
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
        
        $pdf = PDF::loadView('pages.hr.portal.reports.pdf.recordcardbysearchpdf',compact('returnData'));
        return $pdf->download('Employee Record Card.pdf');
    }

    public function generateRecordCardExcel(Request $request)
    {   
        return Excel::download(new RecordCardExport(), 'Employee Record Card.xlsx');
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
                
        return Excel::download(new RecordCardBySearchExport($returnData), 'Employee Record Card.xlsx');
    }
}
