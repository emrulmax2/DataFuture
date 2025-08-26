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
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    protected $Application;

    public function index( AgentApplicationCheck $checkedApplication ) {

        $agentUser = Auth::guard('agent')->user();
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
        $appliedApplication = Applicant::where('applicant_user_id', $applicantUser->id)->whereNull('submission_date')->orderBy('id', 'DESC')->first();
        if(!isset($appliedApplication)) {
            $userData =  $applicantUser;
            $statusArray = Applicant::where('applicant_user_id', $userData->id)->pluck('status_id')->toArray();
            $applicant = Applicant::with('status')->orderBy('id','DESC')->where('applicant_user_id', $userData->id)->get()->first();
            $appliedApplication = $applicant;
            if(isset($applicant)) {
                // 7 Offer Accepted
                // 8 Rejected
                // 9 Offer Rejected
                if(in_array(7, $statusArray)) {

                    $applicant = Applicant::with('status')
                    ->orderBy('id','DESC')
                    ->where('applicant_user_id', $userData->id)
                    ->where('status_id', 7)
                    ->get()
                    ->first();
        
                    $appliedApplication = $this->createAnewApplicationFromPreviousStudent($applicant, $applicantUser,$agentUser);
                } else {

                    $appliedApplication = $this->createAnewApplicationFromPreviousApplication($applicant, $applicantUser,$agentUser);
                }
            }elseif(isset($applicantUser->student_id)) {

                $appliedApplication = $this->createFromOnlyStudent($applicantUser->student_id, $applicantUser);
            }
            

        }
        
        return view('pages.applicant.application.index', [
            'title' => 'Application Form - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Application Form', 'href' => 'javascript:void(0);']
            ],
            'titles' => Title::where('active', 1)->get(),
            'country' => Country::where('active', 1)->get(),
            'ethnicity' => Ethnicity::where('active', 1)->get(),
            'disability' => Disability::where('active', 1)->get(),
            'relations' => KinsRelation::where('active', 1)->get(),
            'bodies' => AwardingBody::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sexid' => SexIdentifier::where('active', 1)->get(),
            'agentApplicant' => $checkedApplication,
            'applicant' => $applicantUser,
            'apply' => $appliedApplication,
            'courseCreationAvailibility' => CourseCreationAvailability::all()->filter(function($item) {
                if (Carbon::now()->between($item->admission_date, $item->admission_end_date)) {
                  return $item;
                }
            })
        ]);
    }
    private function createFromOnlyStudent($studentId, ApplicantUser $applicantUser) {

        $student = Student::with('other','employment', 'employment.referenceSingle')->where('id', $studentId)->orderBy('id','DESC')->get()->first();

            // Student Data Matched and student found correctly
            
            if(isset($student)) {
                // Do something with $student
                $applicant = new Applicant();
                $applicant->applicant_user_id = $applicantUser->id;
                $applicant->previous_student_id = $student->id;
                $applicant->photo = $student->photo;
                $applicant->first_name = $student->first_name;
                $applicant->last_name = $student->last_name;
                $applicant->date_of_birth = $student->date_of_birth;
                $applicant->title_id = $student->title_id;
                $applicant->nationality_id = $student->nationality_id;
                $applicant->country_id = $student->country_id;
                $applicant->sex_identifier_id = $student->sex_identifier_id;
                $applicant->referral_code = $student->referral_code ?? NULL;
                if(isset($student->referral_code)) {
                    $referral = ReferralCode::where('code',$student->referral_code)->get()->first();
                    if(isset($referral)) {
                        if($referral->type=="Agent") {
                         $applicant->agent_user_id = $referral->agent_user_id;
                         $applicant->is_referral_varified = 1;
                        }
                        $applicant->referral_code = $student->referral_code ?? NULL;
                    }
                    
                }
                $applicant->proof_id = isset($student->ProofOfIdLatest->proof_id) ? $student->ProofOfIdLatest->proof_id : null;
                $applicant->proof_type = isset($student->ProofOfIdLatest->proof_type) ? $student->ProofOfIdLatest->proof_type : null;
                $applicant->proof_expiredate = isset($student->ProofOfIdLatest->proof_expiredate) ? date('Y-m-d', strtotime($student->ProofOfIdLatest->proof_expiredate)) : null;
                $applicant->status_id = 1;
                $applicant->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicant->save();

                $applicantOther = new ApplicantOtherDetail();
                $applicantOther->applicant_id = $applicant->id;
                $applicantOther->ethnicity_id = $student->other->ethnicity_id;
                $applicantOther->disability_status = $student->other->disability_status;
                $applicantOther->disabilty_allowance = $student->other->disabilty_allowance;
                $applicantOther->is_edication_qualification = $student->other->is_education_qualification;
                $applicantOther->employment_status = $student->other->employment_status;
                $applicantOther->college_introduction = $student->other->college_introduction;
                $applicantOther->hesa_gender_id  = $student->other->hesa_gender_id;
                $applicantOther->sexual_orientation_id = $student->other->sexual_orientation_id;
                $applicantOther->religion_id = $student->other->religion_id;
                $applicantOther->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantOther->save();

                // I want to map the disability information

                $disabilityIds = isset($student->disabilitiy) ? $student->disabilitiy->pluck('disabilitiy_id')->toArray() : [];

                if(!empty($disabilityIds)) {
                    foreach($disabilityIds as $disabilityId) {
                        $disability = new ApplicantDisability();
                        $disability->applicant_id = $applicant->id;
                        $disability->disabilitiy_id = $disabilityId;

                        $disability->created_by = $applicantUser->id;
                        $disability->save();
                    }
                }


                $applicantContact = new ApplicantContact();
                $applicantContact->applicant_id = $applicant->id;
                $applicantContact->home = $student->contact->home;
                $applicantContact->mobile = $student->contact->mobile;
                $applicantContact->mobile_verification = $student->contact->mobile_verification;
                $applicantContact->country_id = $student->contact->country_id;
                $applicantContact->permanent_country_id = $student->contact->permanent_country_id;
                $applicantContact->address_line_1 = $student->contact->termaddress->address_line_1;
                $applicantContact->address_line_2 = $student->contact->termaddress->address_line_2;
                $applicantContact->state = $student->contact->termaddress->state;
                $applicantContact->post_code = $student->contact->termaddress->post_code;
                $applicantContact->permanent_post_code = $student->contact->permanent_post_code;
                $applicantContact->city = $student->contact->termaddress->city;
                $applicantContact->country = $student->contact->termaddress->country;
                $applicantContact->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantContact->save();

                $applicantKin = new ApplicantKin();
                $applicantKin->applicant_id = $applicant->id;
                $applicantKin->name = $student->kin->name;
                $applicantKin->kins_relation_id = $student->kin->kins_relation_id;
                $applicantKin->mobile = $student->kin->mobile;
                $applicantKin->email = $student->kin->email;
                $applicantKin->address_line_1 = $student->kin->address->address_line_1;
                $applicantKin->address_line_2 = $student->kin->address->address_line_2;
                $applicantKin->state = $student->kin->address->state;
                $applicantKin->post_code = $student->kin->address->post_code;
                $applicantKin->city = $student->kin->address->city;
                $applicantKin->country = $student->kin->address->country;
                $applicantKin->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantKin->save();

                if(isset($student->qualHighest) && $applicantOther->is_edication_qualification ==1){
                    $qual = $student->qualHighest;
                    $applicantQualification = new ApplicantQualification();
                    $applicantQualification->applicant_id = $applicant->id;
                    $applicantQualification->awarding_body = isset($qual->awarding_body) ? $qual->awarding_body : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->awarding_body : null);
                    $applicantQualification->highest_academic = isset($qual->highest_academic) ? $qual->highest_academic : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->highest_academic : null);
                    $applicantQualification->subjects = isset($qual->subjects) ? $qual->subjects : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->subjects : null);
                    $applicantQualification->result = isset($qual->result) ? $qual->result : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->result : null);
                    $applicantQualification->degree_award_date = isset($qual->degree_award_date) ? $qual->degree_award_date : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->degree_award_date : null);
                    $applicantQualification->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;

                    if(isset($applicantQualification->awarding_body)) {
                        $applicantQualification->save();
                    }else {
                        $applicantQualification=null;
                    } 
                    
                }
                $applicantEmp = [];
                if(isset($student->employment))
                foreach($student->employment as $emp):
                    
                    $applicantEmployment = new ApplicantEmployment();
                    $applicantEmployment->applicant_id = $applicant->id;
                    $applicantEmployment->position = isset($emp->position) ? $emp->position : null;
                    $applicantEmployment->company_name = isset($emp->company_name) ? $emp->company_name : null;
                    $applicantEmployment->company_phone = isset($emp->company_phone) ? $emp->company_phone : null;
                    $applicantEmployment->start_date = isset($emp->start_date) ? $emp->start_date : null;
                    $applicantEmployment->end_date = isset($emp->end_date) ? $emp->end_date : null;
                    $applicantEmployment->continuing = isset($emp->continuing) ? $emp->continuing : null;
                    $applicantEmployment->address_line_1 = isset($emp->address->address_line_1) ? $emp->address->address_line_1 : null;
                    $applicantEmployment->address_line_2 = isset($emp->address->address_line_2) ? $emp->address->address_line_2 : null;
                    $applicantEmployment->state = isset($emp->address->state) ? $emp->address->state : null;
                    $applicantEmployment->post_code = isset($emp->address->post_code) ? $emp->address->post_code : null;
                    $applicantEmployment->city = isset($emp->address->city) ? $emp->address->city : null;
                    $applicantEmployment->country = isset($emp->address->country) ? $emp->address->country : null;
                    $applicantEmployment->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                    $applicantEmployment->save();
                    $emp->referenceSingle;
                    $applicantEmp[$applicantEmployment->id] = $emp;
                endforeach;

                if(!empty($applicantEmp)):
                    foreach($applicantEmp as $applicantEmploymentSingleId => $ref):
                        $employmentReference = new EmploymentReference();
                        $employmentReference->applicant_employment_id = $applicantEmploymentSingleId;
                        $employmentReference->name = isset($ref->referenceSingle->name) ?$ref->referenceSingle->name : null;
                        $employmentReference->email = isset($ref->referenceSingle->email) ?$ref->referenceSingle->email : null;
                        $employmentReference->phone = isset($ref->referenceSingle->phone) ?$ref->referenceSingle->phone : null;
                        $employmentReference->position = isset($ref->referenceSingle->position) ?$ref->referenceSingle->position : null;
                        $employmentReference->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                        $employmentReference->save();
                    endforeach;
                endif;
                    
                return $applicant;
            }
        return null;
        
    }
    private function createAnewApplicationFromPreviousStudent(Applicant $prevApplicant, ApplicantUser $applicantUser ,$agentUser=Null) {
        
        $student = Student::with('other','employment', 'employment.referenceSingle')->where('applicant_id', $prevApplicant->id)->orderBy('id','DESC')->get()->first();
        $confirmApplicant = Applicant::find($prevApplicant->id);

        if(isset($confirmApplicant) && 
        ($confirmApplicant->last_name == $prevApplicant->last_name) && 
        ($confirmApplicant->first_name == $prevApplicant->first_name) && 
        ($confirmApplicant->date_of_birth == $prevApplicant->date_of_birth)) {
            // Student Data Matched and student found correctly
            
            if(isset($student)) {
                // Do something with $student
                $applicant = new Applicant();
                $applicant->applicant_user_id = $applicantUser->id;
                $applicant->previous_student_id = $student->id;
                $applicant->photo = $student->photo;
                $applicant->first_name = $student->first_name;
                $applicant->last_name = $student->last_name;
                $applicant->date_of_birth = $student->date_of_birth;
                $applicant->title_id = $student->title_id;
                $applicant->nationality_id = $student->nationality_id;
                $applicant->country_id = $student->country_id;
                $applicant->sex_identifier_id = $student->sex_identifier_id;
                $applicant->referral_code = $student->referral_code ?? NULL;
                if(isset($student->referral_code)) {
                    $referral = ReferralCode::where('code',$student->referral_code)->get()->first();
                    if(isset($referral)) {
                        if($referral->type=="Agent") {
                         $applicant->agent_user_id = $referral->agent_user_id;
                         $applicant->is_referral_varified = 1;
                        }
                        $applicant->referral_code = $student->referral_code ?? NULL;
                    }
                    
                }
                $applicant->proof_id = isset($student->ProofOfIdLatest->proof_id) ? $student->ProofOfIdLatest->proof_id : null;
                $applicant->proof_type = isset($student->ProofOfIdLatest->proof_type) ? $student->ProofOfIdLatest->proof_type : null;
                $applicant->proof_expiredate = isset($student->ProofOfIdLatest->proof_expiredate) ? date('Y-m-d', strtotime($student->ProofOfIdLatest->proof_expiredate)) : null;
                $applicant->status_id = 1;
                $applicant->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicant->save();

                $applicantOther = new ApplicantOtherDetail();
                $applicantOther->applicant_id = $applicant->id;
                $applicantOther->ethnicity_id = $student->other->ethnicity_id;
                $applicantOther->disability_status = $student->other->disability_status;
                $applicantOther->disabilty_allowance = $student->other->disabilty_allowance;
                $applicantOther->is_edication_qualification = $student->other->is_education_qualification;
                $applicantOther->employment_status = $student->other->employment_status;
                $applicantOther->college_introduction = $student->other->college_introduction;
                $applicantOther->hesa_gender_id  = $student->other->hesa_gender_id;
                $applicantOther->sexual_orientation_id = $student->other->sexual_orientation_id;
                $applicantOther->religion_id = $student->other->religion_id;
                $applicantOther->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantOther->save();

                // I want to map the disability information

                $disabilityIds = isset($student->disabilitiy) ? $student->disabilitiy->pluck('disabilitiy_id')->toArray() : [];

                if(!empty($disabilityIds)) {
                    foreach($disabilityIds as $disabilityId) {
                        $disability = new ApplicantDisability();
                        $disability->applicant_id = $applicant->id;
                        $disability->disabilitiy_id = $disabilityId;

                        $disability->created_by = $applicantUser->id;
                        $disability->save();
                    }
                }


                $applicantContact = new ApplicantContact();
                $applicantContact->applicant_id = $applicant->id;
                $applicantContact->home = $student->contact->home;
                $applicantContact->mobile = $student->contact->mobile;
                $applicantContact->mobile_verification = $student->contact->mobile_verification;
                $applicantContact->country_id = $student->contact->country_id;
                $applicantContact->permanent_country_id = $student->contact->permanent_country_id;
                $applicantContact->address_line_1 = $student->contact->termaddress->address_line_1;
                $applicantContact->address_line_2 = $student->contact->termaddress->address_line_2;
                $applicantContact->state = $student->contact->termaddress->state;
                $applicantContact->post_code = $student->contact->termaddress->post_code;
                $applicantContact->permanent_post_code = $student->contact->permanent_post_code;
                $applicantContact->city = $student->contact->termaddress->city;
                $applicantContact->country = $student->contact->termaddress->country;
                $applicantContact->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantContact->save();

                $applicantKin = new ApplicantKin();
                $applicantKin->applicant_id = $applicant->id;
                $applicantKin->name = $student->kin->name;
                $applicantKin->kins_relation_id = $student->kin->kins_relation_id;
                $applicantKin->mobile = $student->kin->mobile;
                $applicantKin->email = $student->kin->email;
                $applicantKin->address_line_1 = $student->kin->address->address_line_1;
                $applicantKin->address_line_2 = $student->kin->address->address_line_2;
                $applicantKin->state = $student->kin->address->state;
                $applicantKin->post_code = $student->kin->address->post_code;
                $applicantKin->city = $student->kin->address->city;
                $applicantKin->country = $student->kin->address->country;
                $applicantKin->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantKin->save();

                if(isset($student->qualHighest) && $applicantOther->is_edication_qualification ==1){
                    $qual = $student->qualHighest;
                    $applicantQualification = new ApplicantQualification();
                    $applicantQualification->applicant_id = $applicant->id;
                    $applicantQualification->awarding_body = isset($qual->awarding_body) ? $qual->awarding_body : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->awarding_body : null);
                    $applicantQualification->highest_academic = isset($qual->highest_academic) ? $qual->highest_academic : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->highest_academic : null);
                    $applicantQualification->subjects = isset($qual->subjects) ? $qual->subjects : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->subjects : null);
                    $applicantQualification->result = isset($qual->result) ? $qual->result : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->result : null);
                    $applicantQualification->degree_award_date = isset($qual->degree_award_date) ? $qual->degree_award_date : (isset($prevApplicant->HighestQualification) ? $prevApplicant->HighestQualification->degree_award_date : null);
                    $applicantQualification->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;

                    if(isset($applicantQualification->awarding_body)) {
                        $applicantQualification->save();
                    }else {
                        $applicantQualification=null;
                    } 
                    
                }
                $applicantEmp = [];
                if(isset($student->employment))
                foreach($student->employment as $emp):
                    
                    $applicantEmployment = new ApplicantEmployment();
                    $applicantEmployment->applicant_id = $applicant->id;
                    $applicantEmployment->position = $emp->position;
                    $applicantEmployment->company_name = $emp->company_name;
                    $applicantEmployment->company_phone = $emp->company_phone;
                    $applicantEmployment->start_date = $emp->start_date;
                    $applicantEmployment->end_date = $emp->end_date;
                    $applicantEmployment->continuing = $emp->continuing;
                    $applicantEmployment->address_line_1 = $emp->address->address_line_1;
                    $applicantEmployment->address_line_2 = $emp->address->address_line_2;
                    $applicantEmployment->state = $emp->address->state;
                    $applicantEmployment->post_code = $emp->address->post_code;
                    $applicantEmployment->city = $emp->address->city;
                    $applicantEmployment->country = $emp->address->country;
                    $applicantEmployment->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                    $applicantEmployment->save();
                    $emp->referenceSingle;
                    $applicantEmp[$applicantEmployment->id] = $emp;
                endforeach;

                if(!empty($applicantEmp)):
                    foreach($applicantEmp as $applicantEmploymentSingleId => $ref):
                        $employmentReference = new EmploymentReference();
                        $employmentReference->applicant_employment_id = $applicantEmploymentSingleId;
                        $employmentReference->name = $ref->referenceSingle->name;
                        $employmentReference->email = $ref->referenceSingle->email;
                        $employmentReference->phone = $ref->referenceSingle->phone;
                        $employmentReference->position = $ref->referenceSingle->position;
                        $employmentReference->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                        $employmentReference->save();
                    endforeach;
                endif;
                    
                return $applicant;
            }
            
        }
        return false;
    }

    private function createAnewApplicationFromPreviousApplication(Applicant $prevApplicant, ApplicantUser $applicantUser, $agentUser=Null) {
        $newApplication = $prevApplicant->replicate();
        $newApplication->id = null;
        $newApplication->application_no = null;
        $newApplication->status_id = 1;
        $newApplication->submission_date = null;
        $newApplication->created_at = now();
        $newApplication->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
        $newApplication->save();

        // I want duplicate related model ApplicantOtherDetail for new Application
        // it will 1to1 mapping
        if(isset($prevApplicant->other)) {
            $newOther = $prevApplicant->other->replicate();
            $newOther->id = null;
            $newOther->applicant_id = $newApplication->id;
            $newOther->created_at = now();
            $newOther->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
            $newOther->save();
        }

        if(isset($prevApplicant->contact)) {
            $newContact = $prevApplicant->contact->replicate();
            $newContact->id = null;
            $newContact->applicant_id = $newApplication->id;
            $newContact->created_at = now();
            $newContact->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
            $newContact->save();
        }

        if(isset($prevApplicant->kin)) {
            $newKin = $prevApplicant->kin->replicate();
            $newKin->id = null;
            $newKin->applicant_id = $newApplication->id;
            $newKin->created_at = now();
            $newKin->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
            $newKin->save();
        }
        //ApplicantQualification
        if(isset($prevApplicant->quals)) {
            foreach ($prevApplicant->quals as $qualification)
            {
                $newQualification = $qualification->replicate();
                $newQualification->id = null;
                $newQualification->applicant_id = $newApplication->id;
                $newQualification->created_at = now();
                $newQualification->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newQualification->save();
            }
        }

        if(isset($prevApplicant->disability)) {
            foreach ($prevApplicant->disability as $disability)
            {
                $newDisability = $disability->replicate();
                $newDisability->id = null;
                $newDisability->applicant_id = $newApplication->id;
                $newDisability->created_at = now();
                $newDisability->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newDisability->save();
            }
        }

        if(isset($prevApplicant->employment)) {
            $applicantEmp = [];
            foreach ($prevApplicant->employment as $employment)
            {
                $employment->referenceSingle;
                
                $emp = $employment;

                $newEmployment = $employment->replicate();
                $newEmployment->id = null;
                $newEmployment->applicant_id = $newApplication->id;
                $newEmployment->created_at = now();
                $newEmployment->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newEmployment->save();
                
                $applicantEmp[$newEmployment->id] = $emp;
                
            }
            if(!empty($applicantEmp)):
                foreach($applicantEmp as $applicantEmploymentSingleId => $emp):
                    if ($emp->referenceSingle) {

                        $newReference = $emp->referenceSingle->replicate();
                        $newReference->id = null;
                        $newReference->applicant_employment_id = $applicantEmploymentSingleId;
                        
                        $newReference->created_at = now();
                        $newReference->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                        $newReference->save();

                    }
                    endforeach;
            endif;
        }

        return $newApplication;
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

        
        $agentUser = Auth::guard('agent')->user();

        $applicantUser = $applicant_user;

        $appliedApplication = Applicant::where('applicant_user_id', $applicantUser->id)->whereNull('submission_date')->orderBy('id', 'DESC')->first();
        
        if(!isset($appliedApplication)) {

            $userData =  $applicantUser;
            $statusArray = Applicant::where('applicant_user_id', $userData->id)->pluck('status_id')->toArray();
            $applicant = Applicant::with('status')->orderBy('id','DESC')->where('applicant_user_id', $userData->id)->get()->first();
            $appliedApplication = $applicant;
            if(isset($applicant)) {
                // 7 Offer Accepted
                // 8 Rejected
                // 9 Offer Rejected
                if(in_array(7, $statusArray)) {

                    $applicant = Applicant::with('status')
                    ->orderBy('id','DESC')
                    ->where('applicant_user_id', $userData->id)
                    ->where('status_id', 7)
                    ->get()
                    ->first();
        
                    $appliedApplication = $this->createAnewApplicationFromPreviousStudent($applicant, $applicantUser,$agentUser);
                } else {

                    $appliedApplication = $this->createAnewApplicationFromPreviousApplication($applicant, $applicantUser,$agentUser);
                }

            $agentApplicationCheck = AgentApplicationCheck::create([
                'agent_user_id' => Auth::guard('agent')->user()->id,
                'first_name'=>$appliedApplication->first_name,
                'last_name'=>$appliedApplication->last_name,
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
            

        }
        return redirect()->back()->with('errors',"No application can be created. started with a new email and mobile number");

    }


    
}
