<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Department;
use App\Models\Disability;
use App\Models\EmployeeJobTitle;
use App\Models\EmployeeNoticePeriod;
use App\Models\EmployeeWorkDocumentType;
use App\Models\EmployeeWorkPermitType;
use App\Models\EmployeeWorkType;
use App\Models\EmploymentPeriod;
use App\Models\EmploymentSspTerm;
use App\Models\Ethnicity;
use App\Models\KinsRelation;
use App\Models\SexIdentifier;
use App\Models\Title;
use App\Models\Venue;
use Illuminate\Http\Request;

class EmployeeFormController extends Controller
{
    public function index($employee_id = null){
        $titles = Title::all();
        // $gender = HesaGender::all();
        $sexIdentifier = SexIdentifier::orderBy('name', 'ASC')->get();
        $country = Country::orderBy('name', 'ASC')->get();
        $relation = KinsRelation::orderBy('name', 'ASC')->get();
        $ethnicity = Ethnicity::orderBy('name', 'ASC')->get();
        $disability = Disability::orderBy('name', 'ASC')->get();
        $venues = Venue::orderBy('name', 'asc')->get();
        $workTypes = EmployeeWorkType::orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'ASC')->get();
        $noticePeriods = EmployeeNoticePeriod::all();
        $employmentPeriods = EmploymentPeriod::all();
        $sspTerms = EmploymentSspTerm::all();
        $jobTitles = EmployeeJobTitle::orderBy('name', 'ASC')->get();
        $documentTypes = EmployeeWorkDocumentType::all();
        $workPermitTypes = EmployeeWorkPermitType::all();

        return view('pages.forms.employee.index', [
            'title' => 'Employee or Contractor Data Collection - London Churchill College',
            'breadcrumbs' => [],
            'titles' => $titles,
            'country' => $country,
            'relation' => $relation,
            'ethnicity' => $ethnicity,
            'disability' => $disability,
            'venues' => $venues,
            'workTypes' => $workTypes,
            'departments' => $departments,
            'noticePeriods' => $noticePeriods,
            'employmentPeriods' => $employmentPeriods,
            'sspTerms' => $sspTerms,
            'jobTitles' => $jobTitles,
            'documentTypes' => $documentTypes,
            'workPermitTypes' => $workPermitTypes,
            'sexIdentifier' => $sexIdentifier,
        ]);
    }
}
