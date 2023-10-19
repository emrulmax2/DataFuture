<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Exports\FeeEligibilityExport;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AwardingBody;
use App\Models\ConsentPolicy;
use App\Models\Country;
use App\Models\CourseCreationInstance;
use App\Models\Disability;
use App\Models\DocumentSettings;
use App\Models\Ethnicity;
use App\Models\FeeEligibility;
use App\Models\HesaGender;
use App\Models\KinsRelation;
use App\Models\ReferralCode;
use App\Models\Religion;
use App\Models\SexIdentifier;
use App\Models\SexualOrientation;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentConsent;
use App\Models\TermTimeAccommodationType;
use App\Models\Title;
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
        $studentData = Student::where("student_user_id", $userData->id)->get()->first();
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

        if($studentData->users->first_login==1):
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
        else:
            return view('pages.students.frontend.dashboard.index', [
                'title' => 'Student Dashboard - LCC Data Future Managment',
                'breadcrumbs' => [],
                'user' => $userData,
                'student' => Student::where('student_user_id', $userData->id)->get()->first()
            ]);
        endif;

    }

    public function profileView($studentId){
        $student = Student::find($studentId);
        return view('pages.students.frontend.dashboard.profile', [
            'title' => 'Live Students - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Profile View', 'href' => 'javascript:void(0);'],
            ],
            'student' => $student,
            'allStatuses' => Status::where('type', 'Student')->get(),
            'titles' => Title::where('active', 1)->get(),
            'country' => Country::where('active', 1)->get(),
            'ethnicity' => Ethnicity::where('active', 1)->get(),
            'disability' => Disability::where('active', 1)->get(),
            'relations' => KinsRelation::where('active', 1)->get(),
            'bodies' => AwardingBody::all(),
            'instance' => CourseCreationInstance::all(),
            'feeelegibility' => FeeEligibility::where('active', 1)->get(),
            'sexualOrientation' => SexualOrientation::where('active', 1)->get(),
            'sexid' => SexIdentifier::where('active', 1)->get(),
            'hesaGender' => HesaGender::where('active', 1)->get(),
            'religion' => Religion::where('active', 1)->get(),
            'stdConsentIds' => StudentConsent::where('student_id', $studentId)->where('status', 'Agree')->pluck('consent_policy_id')->toArray(),
            'consent' => ConsentPolicy::all(),
            'ttacom' => TermTimeAccommodationType::where('active', 1)->get()
        ]);
    }

}
