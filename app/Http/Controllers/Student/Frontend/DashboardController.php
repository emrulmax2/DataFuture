<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\Ethnicity;
use App\Models\HesaGender;
use App\Models\Religion;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Student;
use App\Models\StudentConsent;
use App\Models\TermTimeAccommodationType;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userData = auth('student')->user();
        $countries = Country::all();
        $ethnicities = Ethnicity::all();
        $religions = Religion::all();
        $sexualOrientations = SexualOrientation::all();
        $sexIdentifiers = SexIdentifier::all();
        $genderIdentities = HesaGender::all();
        $studentData = Student::where("student_user_id",$userData->id)->get()->first();
        $studentContact = $studentData->contact;
        $studentOtherDetails = $studentData->other;
        $currentAddress = Address::find($studentContact->term_time_address_id);
        $permanentAddress = Address::find($studentContact->permanent_address_id);

        $terTimeAccomadtionType = TermTimeAccommodationType::all();
        
        

        $consentList = ConsentPolicy::all();

        $data = [
            "student_id" => $studentData->id,
            "nationality" => $studentData->nationality_id,
            "permanent_country" => $studentData->country_id,
            "ethnicity" => $studentData->nationality_id,
            "religion" => $studentData->nationality_id,
            "sex_identifier_id" => $studentData->sex_identifier_id,
            "sexualOrientation" => "",
            "current_address" => $currentAddress,
            "permanent_address" => $permanentAddress,
            "consents" => $consentList,
            "term_time_accommodation_type_id" => $studentContact->term_time_accommodation_type_id
        ];

        if($studentData->users->first_login==1)
        return view('pages.students.frontend.index', [
            'title' => 'Student Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            "countries" =>$countries,
            "ethnicities" => $ethnicities,
            "religions" => $religions,
            "sexualOrientations" => $sexualOrientations,
            "sexIdentifiers" => $sexIdentifiers,
            "genderIdentities" => $genderIdentities,
            "studentData" => $data,
            "consents" =>$consentList,
            "termTimeAccomadtionTypes" => $terTimeAccomadtionType
        ]);
        else
        return view('pages.students.frontend.dashboard.index', [
            'title' => 'Student Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
        ]);

    }

}
