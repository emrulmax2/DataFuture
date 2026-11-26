<?php

namespace App\Http\Controllers\HR\portal\reports;

use App\Exports\HR\EmployeeDataReportExport;
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
use App\Services\HR\EmployeeDataReport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class DataReportController extends Controller
{
    public function index(){
        return view('pages.hr.portal.reports.datareport', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Employee Data Report', 'href' => 'javascript:void(0);']
            ],
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'employeeWorkType' => EmployeeWorkType::all(),
            'departments' => Department::orderBy('name', 'asc')->get(),
            'gender' => SexIdentifier::all()
        ]);
    }

    /** Search (step 1): how many employees the filters keep. */
    public function count(Request $request, EmployeeDataReport $report)
    {
        return response()->json(['count' => $report->count(EmployeeDataReport::filters($request->all()))]);
    }

    /**
     * Generate XLSX (user's employee-data-report design, 26 Nov 2026): the ticked fields, in the order they
     * were ticked, one row per employee the filters keep. The old export's bugs are listed on EmployeeDataReport.
     */
    public function genrateDataReport(Request $request, EmployeeDataReport $report)
    {
        set_time_limit(300);
        $fields = EmployeeDataReport::fields($request->input('fields', []));
        if (!$fields) {
            return response()->json(['message' => 'Select at least one field.'], 422);
        }

        return Excel::download(
            new EmployeeDataReportExport($report->sheet(EmployeeDataReport::filters($request->all()), $fields)),
            'Employee_data_report_'.date('Y-m-d').'.xlsx'
        );
    }
}
