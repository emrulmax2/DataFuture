<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantCourseDetailsRequest;
use App\Models\AwardingBody;
use Illuminate\Http\Request;
use App\Models\Title;
use App\Models\Country;
use App\Models\CourseCreation;
use App\Models\Disability;
use App\Models\Ethnicity;
use App\Models\KinsRelation;
use App\Models\Semester;
use App\Models\User;
use App\Http\Requests\ApplicationPersonalDetailsRequest;
use App\Models\Address;
use App\Models\AgentApplicationCheck;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\ApplicantContact;
use App\Models\ApplicantDisability;
use App\Models\ApplicantEmployment;
use App\Models\ApplicantKin;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProposedCourse;
use App\Models\ApplicantQualification;
use App\Models\ApplicantUser;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationInstance;
use App\Models\EmploymentReference;
use App\Models\ReferralCode;
use App\Models\SexIdentifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    protected $Application;

    public function index( AgentApplicationCheck $checkedApplication ) {
        
        $applicantUser = ApplicantUser::where("email",$checkedApplication->email)->where("phone",$checkedApplication->mobile)->get()->first();

        if(!$applicantUser) {

            $applicantUser = ApplicantUser::create([
                "email" => $checkedApplication->email,
                "phone" =>	$checkedApplication->mobile,
                "email_verified_at" => 	$checkedApplication->email_verified_at,
                "phone_verified_at" =>	$checkedApplication->mobile_verified_at,
                "password" =>	Str::random(16),
                "active" =>	1,
                "created_at" => date("Y-m-d H:i:s"),
            ]);
        }
        // if($applicantUser) {
        //     Auth::guard('applicant')->loginUsingId($applicantUser->id);
        // }
        return view('pages.applicant.application.index', [
            'title' => 'Application Form - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Application Form', 'href' => 'javascript:void(0);']
            ],
            'titles' => Title::all(),
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'disability' => Disability::all(),
            'relations' => KinsRelation::all(),
            'bodies' => AwardingBody::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sexid' => SexIdentifier::all(),
            'agentApplicant' => $checkedApplication,
            'applicant' => $applicantUser,
            'apply' => Applicant::where('applicant_user_id',$applicantUser->id)->whereNull('submission_date')->orderBy('id', 'DESC')->first(),
            'courseCreationAvailibility' => CourseCreationAvailability::all()->filter(function($item) {
                if (Carbon::now()->between($item->admission_date, $item->admission_end_date)) {
                  return $item;
                }
            })
        ]);
    }
    
    public function show($id){
        return view('pages.applicant.application.show', [
            'title' => 'Application View - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Application View', 'href' => 'javascript:void(0);']
            ],
            'applicant' => Applicant::where('id', $id)->first(),
        ]);
    }

    public function create(ApplicantUser $applicant_user) {
        $applicantUser = $applicant_user;
        $prevApplicantInformation = Applicant::where('applicant_user_id',$applicantUser->id)->orderBy('id', 'DESC')->first();
        
        if(isset($prevApplicantInformation->id)) {

            $applicant = Applicant::Create([
                'applicant_user_id' => $applicantUser->id,
                'title_id' => $prevApplicantInformation->title_id,
                'first_name' => $prevApplicantInformation->first_name,
                'last_name' => $prevApplicantInformation->last_name,
                'date_of_birth' => $prevApplicantInformation->date_of_birth,
                'sex_identifier_id' => $prevApplicantInformation->sex_identifier_id,
                'agent_user_id' =>  Auth::guard('agent')->user()->id,
                'status_id' => 1,
                'nationality_id' => $prevApplicantInformation->nationality_id,
                'country_id' => $prevApplicantInformation->country_id,
                'created_by' => Auth::guard('agent')->user()->id,
                'updated_by' => Auth::guard('agent')->user()->id,
            ]);

            $agentApplicationCheck = AgentApplicationCheck::create([
                'agent_user_id' => Auth::guard('agent')->user()->id,
                'first_name'=>$prevApplicantInformation->first_name,
                'last_name'=>$prevApplicantInformation->last_name,
                'email' => $applicantUser->email,
                'mobile' => $applicantUser->phone,
                'verify_code' => '4454',
                'email_verify_code' => '4454',
                'email_verified_at' => date("Y-m-d H:i:s"),
                'mobile_verified_at' => date("Y-m-d H:i:s"),
                'active' => 1,
                'created_by' => Auth::guard('agent')->user()->id,
            ]);
            
            return redirect()->route('agent.application',$agentApplicationCheck->id);
        }
        return redirect()->back()->with('errors',"No application can be created. started with a new email and mobile number");

    }

    
}
