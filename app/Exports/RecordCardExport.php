<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Address;
use App\Models\EmployeeEmergencyContact;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RecordCardExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $query = Employee::where('status', '=', 1)->get();

        $i = 0;

        $dataList =[];

        foreach($query as $item) {
            $address = Address::find($item->address_id);
            $addressOne = isset($address->address_line_1) ? $address->address_line_1 : '';
            $addressTwo = isset($address->address_line_2) ? $address->address_line_2 : '';
            $firstName = isset($item->first_name) ? $item->first_name : '';
            $lastName = isset($item->last_name) ? $item->last_name : '';
            $emergencyContact= EmployeeEmergencyContact::find($item->id);
            $dataList[$i++] = [
                'title' => $item->title->name,
                'first_name' => $firstName,
                'last_name' => isset($item->last_name) ? $item->last_name : '',
                'full_name' => $lastName,
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

        return view('pages.hr.portal.reports.excel.recordcardexcel', [
            'dataList' => $dataList
        ]);
    }
}
