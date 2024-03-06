<?php

namespace App\Http\Controllers\HR\portal\reports;

use App\Exports\StudentEmailIdTaskExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Country;
use App\Models\Employee;
use App\Models\Ethnicity;
use App\Models\Department;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\EmployeeWorkType;
use App\Models\SexIdentifier;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class DataReportController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.datareport', [
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

    public function genrateDataReport(Request $request) {
        
        $data['employee_work_type_id'] = $request->input('employee_work_type_id');
        $data['department_id'] = $request->input('department_id');
        $data['start_date'] = $request->input('start_date');
        $data['end_date'] = $request->input('end_date');
        $data['sex_identifier_id'] = $request->input('sex_identifier_id');
        $data['fields'] = $fields = $request->input('fields');
        $data['labels'] = $request->input('labels');
        $data['tables'] = $request->input('tables');
        $data['status_id'] = $request->input('status_id');

        $query = Employee::with(['venues','title','address','ethnicity','nationality','disability','sex','user','payment','employment','workingPattern','eligibilities','emergencyContact'])->orderBy('first_name','ASC');

        if(isset($data['status_id']) && $data['status_id']!=2) {
            
            $query->where('status',$data['status_id']);
        }
        if(isset($data['employee_work_type_id'])) {

            $employee_work_type_id = $data['employee_work_type_id'];

            $query->whereHas('employment', function($qs) use($employee_work_type_id){

                 $qs->where('employee_work_type_id', $employee_work_type_id); 

            });
            
        }
        if(isset($data['start_date']) && isset($data['end_date'])) {

            $start_date = date("Y-m-d",strtotime($data['start_date']));
            $end_date = date("Y-m-d",strtotime($data['end_date']));

            $query->whereHas('employment', function($qs) use($start_date,$end_date){

               $qs->whereBetween('started_on', [$start_date, $end_date]);
               $qs->whereBetween('ended_on', [$start_date, $end_date]);

            });
        }

        if(isset($data['sex_identifier_id'])) {

            $sex_identifier_id = $data['sex_identifier_id'];

            $query->whereHas('sex', function($qs) use($sex_identifier_id){

                $qs->where('sex_identifier_id', $sex_identifier_id); 

            });
        }
        if(!empty($data['labels'])):
            $theCollection = [];
            $theCollection[1][0] = "Serial";
            foreach($data['labels'] as $key => $label) {
                foreach($data['tables'] as $keyTable => $table) {
                    if(isset($data['fields'][$keyTable][$key]) && ($data['fields'][$keyTable][$key]==1))
                        $theCollection[1][] = $label;
                }
            }
            $row = 2;
            $serial = 1;
            foreach($query->get() as $employee):
                $listCount = $query->count();
                
                if($employee):
                    if($listCount):
                        $theCollection[$row][] = $serial++;
                        /* Excel Data Array */
                        
                        foreach($data['tables'] as $keyTable => $table) {
                            
                            if($table=="Employee") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    if($fieldValue)
                                        switch($fieldName) {
                                            case 'title_id':
                                                $theCollection[$row][] = ($employee->title) ? $employee->title->name : "";
                                                break;
                                            case 'sex_identifier_id':
                                                $theCollection[$row][] = ($employee->sex) ? $employee->sex->name : "";
                                                break;
                                            case 'nationality_id':
                                                $theCollection[$row][] = ($employee->nationality) ? $employee->nationality->name : "";
                                                break;
                                            case 'ethnicity_id':
                                                $theCollection[$row][] = ($employee->ethnicity) ? $employee->ethnicity->name : "";
                                                break;
                                            case 'status':
                                                    $theCollection[$row][] = ($employee->status) ? "Active": "Inactive";
                                                    break;
                                            case 'address_id':
                                                
                                                $theCollection[$row][] = ($employee->address) ? $employee->address->address_line_1.", ".$employee->address->address_line_2.", ".$employee->address->state.","
                                                                        .$employee->address->post_code.",".$employee->address->city.",".ucwords($employee->address->country) : "";
                                                break;
                                            default:
                                                $theCollection[$row][] = ($employee->$fieldName) ? $employee->$fieldName : "";
                                        }
                                }
                                
                            }
                            
                            if($table=="Employment") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    if($fieldValue)
                                        switch($fieldName) {
                                            case 'employee_work_type_id':
                                                $theCollection[$row][] = ($employee->employment->employeeWorkType) ? $employee->employment->employeeWorkType->name : "";
                                                break;
                                            case 'employee_job_title_id':
                                                $theCollection[$row][] = ($employee->employment->employeeJobTitle) ? $employee->employment->employeeJobTitle->name : "";
                                                break;
                                            case 'department_id':
                                                $theCollection[$row][] = ($employee->employment->department) ? $employee->employment->department->name : "";
                                                break;
                                            case 'site_location':
                                                $siteLocation = "";
                                                foreach($employee->venues as $venue) {
                                                    $siteLocation.= $venue->name.", ";
                                                }
                                                $theCollection[$row][] = $siteLocation;
                                                break;
                                            case 'office_telephone':
                                                $theCollection[$row][] = ($employee->employment->office_telephone) ? $employee->employment->office_telephone : "";
                                                break;
                                            case 'office_mobile':
                                                $theCollection[$row][] = ($employee->employment->mobile) ? $employee->employment->mobile : "";
                                                break;
                                            case 'office_email':
                                                $theCollection[$row][] = ($employee->employment->email) ? $employee->employment->email : "";
                                                break;
                                            default:
                                                $theCollection[$row][] = ($employee->employment->$fieldName) ? $employee->employment->$fieldName : "";
                                        }
                                }
                                
                                
                            }

                            if($table=="EmployeeEligibilites") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                            
                                    if($fieldValue)
                                        switch($fieldName) {
                                            case 'employee_work_permit_type_id':
                                                $theCollection[$row][] = ($employee->eligibilities->employeeDocType)? $employee->eligibilities->employeeWorkPermitType->name :"";
                                                break;
                                            case 'document_type':
                                                $theCollection[$row][] = ($employee->eligibilities->employeeDocType) ? $employee->eligibilities->employeeDocType->name : "";
                                                break;
                                            case 'doc_issue_country':
                                                $theCollection[$row][] = ($employee->eligibilities->docIssueCountry) ? $employee->eligibilities->docIssueCountry->name : "";
                                                
                                                break;
                                            default:
                                                $theCollection[$row][] = ($employee->eligibilities->$fieldName) ? $employee->eligibilities->$fieldName : "";
                                        }
                                }
                                
                                
                            }

                            

                            if($table=="EmployeeEmergencyContact") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    if($fieldValue)
                                        switch($fieldName) {
                                            case 'kins_relation_id':
                                                $theCollection[$row][] = ($employee->emergencyContact->kin) ? $employee->emergencyContact->kin->name : "";
                                                break;
                                            case 'emergency_address_id':
                                                $theCollection[$row][] = ($employee->emergencyContact->address!=null) ? $employee->emergencyContact->address->address_line_1.", ".$employee->emergencyContact->address->address_line_2.", ".$employee->emergencyContact->address->state.","
                                                                        .$employee->emergencyContact->address->post_code.",".$employee->emergencyContact->address->city.",".ucwords($employee->emergencyContact->address->country) : "";
                                                break;
                                            default:
                                                $theCollection[$row][] = ($employee->emergencyContact->$fieldName) ? $employee->emergencyContact->$fieldName : "";
                                        }
                                }
                                
                                
                            }

                            if($table=="EmployeePaymentSetting") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    
                                    if($fieldValue)
                                        $theCollection[$row][] = ($employee->payment==null) ? "": $employee->payment->$fieldName;
                                }
                                
                                
                            }

                            if($table=="EmployeeBankDetail") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    if($employee->payment!=null && $employee->payment->payment_method=="Bank Transfer") {
                                        $employeeBankDetail = EmployeeBankDetail::where("employee_id",$employee->id)->where("active",1)->get()->first();
                                        if($fieldValue)
                                        $theCollection[$row][] =  ($employeeBankDetail) ? $employeeBankDetail->$fieldName : "";
                                    }
                                    
                                }
                                
                                
                            }
                            $employeeWorkingPattern = "";
                            if($table=="EmployeeWorkingPattern") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    
                                    $employeeWorkingPattern = EmployeeWorkingPattern::where('employee_id',$employee->id)->where('active',1)->whereNull('end_to')->get()->first();
                                    if($fieldValue)
                                        $theCollection[$row][] = ($employeeWorkingPattern==null) ? "": $employeeWorkingPattern->$fieldName;
                                    
                                }
                                
                                
                            }

                            if($table=="EmployeeWorkingPatternPay") {
                                foreach($data['fields'][$keyTable] as $fieldName => $fieldValue) {
                                    
                                    if($employeeWorkingPattern!="") {
                                        $patternPay = EmployeeWorkingPatternPay::where("employee_working_pattern_id",$employeeWorkingPattern->id)->where("active",1)->get()->first();

                                        if($fieldValue)
                                        $theCollection[$row][] = ($patternPay==null) ? "": $patternPay->$fieldName;
                                    }
                                }
                                
                                
                            }


                        }
                        
                        
                        $row++;
                    endif;
                endif;
            endforeach;
        endif;

        return Excel::download(new StudentEmailIdTaskExport($theCollection), 'Employee_data_report.xlsx');
    }
}
