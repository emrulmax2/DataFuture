<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantCourseDetailsRequest;
use App\Http\Requests\ApplicantResidencyAndCriminalConvictionRequest;
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
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Address;
use App\Models\Agent;
use App\Models\AgentApplicationCheck;
use App\Models\Applicant;
use App\Models\ApplicantContact;
use App\Models\ApplicantCriminalConviction;
use App\Models\ApplicantDisability;
use App\Models\ApplicantEmployment;
use App\Models\ApplicantFeeEligibility;
use App\Models\ApplicantKin;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProofOfId;
use App\Models\ApplicantProposedCourse;
use App\Models\ApplicantQualification;
use App\Models\ApplicantResidency;
use App\Models\ApplicantUser;
use App\Models\ComonSmtp;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationInstance;
use App\Models\CourseCreationVenue;
use App\Models\EmploymentReference;
use App\Models\FeeEligibility;
use App\Models\Option;
use App\Models\ReferralCode;
use App\Models\SexIdentifier;
use App\Models\Student;
use App\Models\Venue;
use Barryvdh\Debugbar\Facades\Debugbar as FacadesDebugbar;
use DebugBar\DebugBar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(){
        $applicantUser = Auth::guard('applicant')->user();
        
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
                    $student = Student::where('applicant_id', $applicant->id)->orderBy('id','DESC')->get()->first();

                    $appliedApplication = $this->createFromOnlyStudent($student->id, $applicantUser);
                } else {

                    $appliedApplication = $this->createAnewApplicationFromPreviousApplication($applicant, $applicantUser);
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
            'venues' => Venue::all(),
            'country' => Country::where('active', 1)->get(),
            'ethnicity' => Ethnicity::where('active', 1)->get(),
            'disability' => Disability::where('active', 1)->get(),
            'relations' => KinsRelation::where('active', 1)->get(),
            'bodies' => AwardingBody::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
            'sexid' => SexIdentifier::where('active', 1)->get(),
            'applicant' => \Auth::guard('applicant')->user(),
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
                $applicant->first_name = $student->first_name;
                $applicant->last_name = $student->last_name;
                $applicant->date_of_birth = $student->date_of_birth;
                $applicant->title_id = $student->title_id;
                $applicant->nationality_id = $student->nationality_id;
                $applicant->country_id = $student->country_id;
                $applicant->sex_identifier_id = $student->sex_identifier_id;
                
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

                //check file exists in local storage
                if(Storage::disk('local')->exists('public/students/'.$student->id.'/'.$student->photo)) {
                    //put this photo in applicant photo folder
                    $applicantPhotoPath = 'public/applicants/'.$applicant->id.'/';
                    
                    Storage::makeDirectory($applicantPhotoPath);

                    $applicantPhotoName = $student->photo;

                    Storage::copy('public/students/'.$student->id.'/'.$student->photo, $applicantPhotoPath.$applicantPhotoName);
                    
                    $applicant->photo = $applicantPhotoName;

                    $applicant->save();
                }
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
                FacadesDebugbar::info('Here is the proof of id: ' . $student->ProofOfIdLatest);
                $applicantProofofId = new ApplicantProofOfId();
                $applicantProofofId->applicant_id = $applicant->id;
                $applicantProofofId->proof_type = isset($student->ProofOfIdLatest->proof_type) ? $student->ProofOfIdLatest->proof_type : null;
                $applicantProofofId->proof_id = isset($student->ProofOfIdLatest->proof_id) ? $student->ProofOfIdLatest->proof_id : null;
                $applicantProofofId->proof_expiredate = isset($student->ProofOfIdLatest->proof_expiredate) ? date('Y-m-d', strtotime($student->ProofOfIdLatest->proof_expiredate)) : null;
                $applicantProofofId->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantProofofId->save();

                $applicantFeeEligibility = new ApplicantFeeEligibility();
                $applicantFeeEligibility->applicant_id = $applicant->id;
                $applicantFeeEligibility->fee_eligibility_id = isset($student->crel->feeeligibility->fee_eligibility_id) ? $student->crel->feeeligibility->fee_eligibility_id : null;
                $applicantFeeEligibility->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $applicantFeeEligibility->save();

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

    private function createAnewApplicationFromPreviousApplication(Applicant $prevApplicant, ApplicantUser $applicantUser, $agentUser=Null) {
        $newApplication = $prevApplicant->replicate();
        $newApplication->id = null;
        $newApplication->application_no = null;
        $newApplication->status_id = 1;
        $newApplication->submission_date = null;
        $newApplication->created_at = now();
        $newApplication->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
        $newApplication->save();


        //check file exists in local storage
        if(Storage::disk('local')->exists('public/students/'.$prevApplicant->id.'/'.$prevApplicant->photo)) {
            //put this photo in applicant photo folder
            $applicantPhotoPath = 'public/applicants/'.$newApplication->id.'/';
            
            Storage::makeDirectory($applicantPhotoPath);

            $applicantPhotoName = $prevApplicant->photo;

            Storage::copy('public/students/'.$prevApplicant->id.'/'.$prevApplicant->photo, $applicantPhotoPath.$applicantPhotoName);

            $newApplication->photo = $applicantPhotoName;

            $newApplication->save();
        }
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

        if(isset($prevApplicant->proofs)) {
            foreach ($prevApplicant->proofs as $proof)
            {
                $newProof = $proof->replicate();
                $newProof->id = null;
                $newProof->applicant_id = $newApplication->id;
                $newProof->created_at = now();
                $newProof->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newProof->save();
            }
        }

        if(isset($prevApplicant->feeeligibilities)) {
            foreach ($prevApplicant->feeeligibilities as $feeeligibility)
            {
                $newFeeEligibility = $feeeligibility->replicate();
                $newFeeEligibility->id = null;
                $newFeeEligibility->applicant_id = $newApplication->id;
                $newFeeEligibility->created_at = now();
                $newFeeEligibility->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newFeeEligibility->save();
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

            if(isset($prevApplicant->residency)) { 
                $newApplicantResidency = $prevApplicant->residency->replicate();
                $newApplicantResidency->id = null;
                $newApplicantResidency->applicant_id = $newApplication->id;
                $newApplicantResidency->created_at = now();
                $newApplicantResidency->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newApplicantResidency->save();
            }

            if(isset($prevApplicant->criminalConviction)) { 
                $newApplicantCriminalConviction = $prevApplicant->criminalConviction->replicate();
                $newApplicantCriminalConviction->id = null;
                $newApplicantCriminalConviction->applicant_id = $newApplication->id;
                $newApplicantCriminalConviction->created_at = now();
                $newApplicantCriminalConviction->created_by = isset($agentUser) ? $agentUser->id : $applicantUser->id;
                $newApplicantCriminalConviction->save();
            }

        }

        return $newApplication;
    }

    public function CourseCreationList($id) {
       
        $data = CourseCreation::with('venues')->where('id',$id)->get()->first();
        
        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function storePersonalDetails(ApplicationPersonalDetailsRequest $request){
        $lastApplicantRow = Applicant::orderBy('id', 'DESC')->get()->first();
        $lastApplicantId = (isset($lastApplicantRow->id) && !empty($lastApplicantRow->id));
        $applicantUserId = $request->applicant_user_id;
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::updateOrCreate([ 'applicant_user_id' => $applicantUserId, 'id' => $applicant_id ], [
            'applicant_user_id' => $applicantUserId,
            'title_id' => $request->title_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'sex_identifier_id' => $request->sex_identifier_id,
            'agent_user_id' => (isset($request->agent_user_id) && !empty($request->agent_user_id) ? $request->agent_user_id : null),
            'status_id' => 1,
            'nationality_id' => $request->nationality_id,
            'country_id' => $request->country_id,
            'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
        ]);
        if($applicant){
            if(!isset($applicant->application_no) || is_null($applicant->application_no)):
                $theApplicantId = $applicant->id;
                $appNo = '2'.sprintf('%05d', $theApplicantId);
                Applicant::where('id', $theApplicantId)->update(['application_no' => $appNo]);
            endif;
            $disabilityStatus = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
            $otherDetails = ApplicantOtherDetail::updateOrCreate(['applicant_id' => $applicant->id], [
                    'ethnicity_id' => $request->ethnicity_id,
                    'disability_status' => $disabilityStatus,
                    'disabilty_allowance' => ($disabilityStatus == 1 && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0),
                    'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                    'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                ]
            );
            if($disabilityStatus == 1 && isset($request->disability_id) && !empty($request->disability_id)):
                $applicantDisablity = ApplicantDisability::where('applicant_id', $applicant->id)->forceDelete();
                foreach($request->disability_id as $disabilityID):
                    $applicantDisabilities = ApplicantDisability::create([
                        'applicant_id' => $applicant->id,
                        'disabilitiy_id' => $disabilityID,
                        'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                    ]);
                endforeach;
            else:
                $applicantDisablity = ApplicantDisability::where('applicant_id', $applicant->id)->forceDelete();
            endif;

            $contacts = ApplicantContact::updateOrCreate(['applicant_id' => $applicant->id], [
                'home' => $request->phone,
                'mobile' => $request->mobile,
                'address_line_1' => (isset($request->applicant_address_line_1) && !empty($request->applicant_address_line_1) ? $request->applicant_address_line_1 : null),
                'address_line_2' => (isset($request->applicant_address_line_2) && !empty($request->applicant_address_line_2) ? $request->applicant_address_line_2 : null),
                'state' => (isset($request->applicant_address_state) && !empty($request->applicant_address_state) ? $request->applicant_address_state : null),
                'post_code' => (isset($request->applicant_address_postal_zip_code) && !empty($request->applicant_address_postal_zip_code) ? $request->applicant_address_postal_zip_code : null),
                'city' => (isset($request->applicant_address_city) && !empty($request->applicant_address_city) ? $request->applicant_address_city : null),
                'country' => (isset($request->applicant_address_country) && !empty($request->applicant_address_country) ? $request->applicant_address_country : null),
                'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            ]);

            $kin = ApplicantKin::updateOrCreate(['applicant_id' => $applicant->id], [
                'name' => $request->name,
                'kins_relation_id' => $request->kins_relation_id,
                'mobile' => $request->kins_mobile,
                'email' => (isset($request->kins_email) && !empty($request->kins_email) ? $request->kins_email : null),
                'address_line_1' => (isset($request->kin_address_line_1) && !empty($request->kin_address_line_1) ? $request->kin_address_line_1 : null),
                'address_line_2' => (isset($request->kin_address_line_2) && !empty($request->kin_address_line_2) ? $request->kin_address_line_2 : null),
                'state' => (isset($request->kin_address_state) && !empty($request->kin_address_state) ? $request->kin_address_state : null),
                'post_code' => (isset($request->kin_address_postal_zip_code) && !empty($request->kin_address_postal_zip_code) ? $request->kin_address_postal_zip_code : null),
                'city' => (isset($request->kin_address_city) && !empty($request->kin_address_city) ? $request->kin_address_city : null),
                'country' => (isset($request->kin_address_country) && !empty($request->kin_address_country) ? $request->kin_address_country : null),
                'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            ]);
            return response()->json(['message' => 'WOW! Data successfully inserted.', 'applicant_id' => $applicant->id], 200);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function storeCourseDetails(ApplicantCourseDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $course_creation_id = $request->course_creation_id;
        $venue_id = (isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : 0);
        $courseCreation = CourseCreation::find($course_creation_id);
        $studentLoan = $request->student_loan;
        $studentFinanceEngland = ($studentLoan == 'Student Loan' && isset($request->student_finance_england) && $request->student_finance_england > 0 ? $request->student_finance_england : null);
        $appliedReceivedFund = ($studentLoan == 'Student Loan' && isset($request->applied_received_fund) && $request->applied_received_fund > 0 ? $request->applied_received_fund : null);
        $fundReceipt = ($studentFinanceEngland == 1 && isset($request->fund_receipt) && $request->fund_receipt > 0 ? $request->fund_receipt : null);
        $crsCrnInstance = CourseCreationInstance::where('course_creation_id', $course_creation_id)->orderBy('id', 'ASC')->get()->first();

        $courseVenue = CourseCreationVenue::where('course_creation_id', $course_creation_id)->where('venue_id', $venue_id)->get()->first();
        $venueEW = ((isset($courseVenue->evening_and_weekend) && $courseVenue->evening_and_weekend == 1) && (isset($courseVenue->weekends) && $courseVenue->weekends > 0) ? true : false );

        $course = ApplicantProposedCourse::updateOrCreate(['applicant_id' => $applicant_id], [
            'course_creation_id' => $course_creation_id,
            'semester_id' => $courseCreation->semester_id,
            'academic_year_id' => (isset($crsCrnInstance->academic_year_id) && $crsCrnInstance->academic_year_id > 0 ? $crsCrnInstance->academic_year_id : null),
            'venue_id' => (isset($request->venue_id) && $request->venue_id > 0 ? $request->venue_id : null),
            'student_loan' => $studentLoan,
            'student_finance_england' => $studentFinanceEngland,
            'applied_received_fund' => $appliedReceivedFund,
            'fund_receipt' => $fundReceipt,
            'other_funding' => ($studentLoan == 'Others' && isset($request->other_funding) && !empty($request->other_funding) ? $request->other_funding : null),
            'full_time' => ($venueEW && (isset($request->full_time) && $request->full_time > 0) ? $request->full_time : 0),
            'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
        ]);
        if($course):
            $isEducationQualification = (isset($request->is_edication_qualification) && $request->is_edication_qualification > 0 ? $request->is_edication_qualification : 0);
            $employmentStatus = (isset($request->employment_status) && !empty($request->employment_status) ? $request->employment_status : '');
            $otherDetails = ApplicantOtherDetail::updateOrCreate(['applicant_id' => $applicant_id], [
                'is_edication_qualification' => $isEducationQualification,
                'employment_status' => $employmentStatus,
                'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            ]);
            if($isEducationQualification == 0):
                $educationQualifications = ApplicantQualification::where('applicant_id', $applicant_id)->forceDelete();
            endif;
            if($employmentStatus == ''):
                $employments = ApplicantEmployment::where('applicant_id', $applicant_id)->get();
                if(!empty($employments)):
                    foreach($employments as $empt):
                        $emptRef = EmploymentReference::where('applicant_employment_id', $empt->id)->forceDelete();
                    endforeach;
                endif;
                $applicantEmployments = ApplicantEmployment::where('applicant_id', $applicant_id)->forceDelete();
            endif;

            /*if(isset($request->referral_code) && !empty($request->referral_code)):
                $ref = Applicant::where('id', $applicant_id)->update([
                    'referral_code' => $request->referral_code,
                    'is_referral_varified' => 0,
                    'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                ]);
            endif;*/
            
            return response()->json(['message' => 'Course details successfully inserted or updated', 'applicant_id' => $applicant_id], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }


    public function storeResidencyAndCriminalConvictionDetails(ApplicantResidencyAndCriminalConvictionRequest $request){
        $applicant_id = $request->applicant_id;
        $residency = ApplicantResidency::updateOrCreate(['applicant_id' => $applicant_id], [
            'residency_status_id' => $request->residency_status_id,
            'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
        ]);
        if($residency){
            $criminalConviction = ApplicantCriminalConviction::updateOrCreate(['applicant_id' => $applicant_id], [
                'have_you_been_convicted' => $request->have_you_been_convicted,
                'criminal_conviction_details' => (isset($request->criminal_conviction_details) && !empty($request->criminal_conviction_details) ? $request->criminal_conviction_details : null),
                'criminal_declaration' => ($request->has('criminal_declaration') && $request->criminal_declaration > 0 ? 1 : 0),
                'created_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
                'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
            ]);
            return response()->json(['message' => 'Residency and Criminal Conviction details successfully inserted or updated', 'applicant_id' => $applicant_id], 200);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        }
    }

    public function storeApplicantSubmission(Request $request){

        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteName = (!empty($siteName) ? $siteName : 'London Churchill College');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  (!empty($siteName) ? $siteName : 'London Churchill College'),
        ];
        // $configuration = [
        //             'smtp_host' => 'sandbox.smtp.mailtrap.io',
        //             'smtp_port' => '2525',
        //             'smtp_username' => 'e8ae09cfefd325',
        //             'smtp_password' => 'ce7fa44b28281d',
        //             'smtp_encryption' => 'tls',
                    
        //             'from_email'    => 'no-reply@lcc.ac.uk',
        //             'from_name'    =>  'London Churchill College',
        //         ];

        $applicant_id = $request->applicant_id;
        Applicant::where('id', $applicant_id)->update([
            'status_id' => 2,
            'is_agree' => 1,
            'submission_date' => date('Y-m-d'),
            'updated_by' => isset(Auth::guard('agent')->user()->id) ? Auth::guard('agent')->user()->id : Auth::guard('applicant')->user()->id,
        ]);

        if(auth('agent')->user()) {
            $applicant = Applicant::find($applicant_id);
            $agentUserId = auth('agent')->user()->id;
            
            $referral = ReferralCode::where('code',$applicant->referral_code)->get()->first();
            $applicant->agent_user_id = $referral->agent_user_id;
            $applicant->updated_by = auth('agent')->user()->id;
            $applicant->save();
            if(isset($applicant->contact)) {
                $applicantContact = ApplicantContact::find($applicant->contact->id);

                $applicantContact->mobile_verification = 1;
                $applicantContact->save();
            }
            
            $application = AgentApplicationCheck::where('agent_user_id',$agentUserId)
                                ->where("email", $applicant->users->email)
                                ->where("mobile",$applicant->users->phone)
                                ->orderBy('id', 'desc')
                                ->get()
                                ->first();
            $application->applicant_id = $applicant_id;
            $application->updated_by = auth('agent')->user()->id;
            $application->save();
            //Auth::guard('applicant')->logout();

            FacadesDebugbar::info("Application updated with agent: ".$referral->agent_user_id );
        }
        session(['applicantSubmission' => 'Application successfully submitted.']);

        $theApplicant = Applicant::find($applicant_id);
        if(isset($theApplicant->contact->mobile) && !empty($theApplicant->contact->mobile)):
            $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
            $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
            $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
            $sms = 'Thank you for applying at '. $siteName.'. Please find your application reference number '.$theApplicant->application_no.' for all future correspondence.';
            if(in_array(env('APP_ENV'), ['development', 'local'])) {

                    //\Log::info('SMS OTP: '.$sms.' sent to '.$theApplicant->contact->mobile);
                    //FacadesDebugbar::info('SMS OTP: '.$sms.' sent to '.$theApplicant->contact->mobile);

            } else {
                if($active_api == 1 && !empty($textlocal_api)):
                    $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                        'apikey' => $textlocal_api, 
                        'message' => $sms, 
                        'sender' => 'London Churchill College', 
                        'numbers' => $theApplicant->contact->mobile
                    ]);
                elseif($active_api == 2 && !empty($smseagle_api)):
                    $response = Http::withHeaders([
                            'access-token' => $smseagle_api,
                            'Content-Type' => 'application/json',
                        ])->withoutVerifying()->withOptions([
                            "verify" => false
                        ])->post('https://79.171.153.104/api/v2/messages/sms', [
                            'to' => [$theApplicant->contact->mobile],
                            'text' => $sms
                        ]);
                endif;
            }
        endif;
        if(isset($commonSmtp->id) && $commonSmtp->id > 0):
            $theApplicantEmail = (isset($theApplicant->users->email) && !empty($theApplicant->users->email) ? $theApplicant->users->email : '');
            if(!empty($theApplicantEmail)):
                $theSubject = 'Application confirmation email from ' . $siteName;
                $message = '';
                $message .= 'Dear '.$theApplicant->first_name.' '.$theApplicant->last_name.'<br /><br />';
                $message .= 'Thank you for applying to study at '. $siteName .'. <br /><br />';
                $message .= 'Please find your application reference number below. Please use this number for all future correspondence. <br /><br />';
                $message .= '<span style="font-size: 40px;"><strong>'.$theApplicant->application_no.'</strong></span> <br /><br />';
                $message .= 'Thank you, <br />'.$siteName;

                UserMailerJob::dispatch($configuration, [$theApplicantEmail], new CommunicationSendMail($theSubject, $message, []));
            endif;
        endif;

        return response()->json(['message' => 'Application successfully submitted.'], 200);
    }

    public function review(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::find($applicant_id);

        $html = '';
        $html .= '<div id="applicantReviewAccordion" class="accordion">';
            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-1" class="accordion-header">';
                    $html .= '<button class="accordion-button px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-1" aria-expanded="true" aria-controls="applicantReviewAccordion-col-1">';
                        $html .= 'Personal Details';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-1" class="accordion-collapse collapse show" aria-labelledby="applicantReviewAccordion-c-1" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Name</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Date of Birth</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->date_of_birth.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Gender</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->sexid->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Nationality</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->nation->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Country of Birth</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->country->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Ethnicity</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->other->ethnicity->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Disability Status</div>';
                                    $html .= '<div class="col-span-8 font-medium">';
                                        $html .= (isset($applicant->other->disability_status) && $applicant->other->disability_status == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>');
                                    $html .='</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            if(isset($applicant->other->disability_status) && $applicant->other->disability_status == 1):
                                $html .= '<div class="col-span-12 sm:col-span-3">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-4 text-slate-500 font-medium">Allowance Claimed?</div>';
                                        $html .= '<div class="col-span-8 font-medium">';
                                            $html .= (isset($applicant->other->disabilty_allowance) && $applicant->other->disabilty_allowance == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>');
                                        $html .='</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-span-12 sm:col-span-3">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-12 text-slate-500 font-medium">Disabilities</div>';
                                        $html .= '<div class="col-span-12 font-medium">';
                                            if(isset($applicant->disability) && !empty($applicant->disability)):
                                                $html .= '<ul class="m-0 p-0">';
                                                    foreach($applicant->disability as $dis):
                                                        $html .= '<li class="text-left font-normal mb-1 flex pl-5 relative"><i data-lucide="check-circle" class="w-3 h-3 text-success absolute" style="left: 0; top: 4px;"></i>'.$dis->disabilities->name.'</li>';
                                                    endforeach;
                                                $html .'</ul>';
                                            endif;
                                        $html .='</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            endif;

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-2" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-2" aria-expanded="false" aria-controls="applicantReviewAccordion-col-2">';
                        $html .= 'Contact Details';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-2" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-2" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Home Phone</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->contact->home.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Mobile</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->contact->mobile.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-6">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-12 text-slate-500 font-medium">Address</div>';
                                    $html .= '<div class="col-span-12 font-medium">';
                                        if(isset($applicant->contact->address_line_1) && !empty($applicant->contact->address_line_1)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->address_line_1.'</span><br/>';
                                        endif;
                                        if(isset($applicant->contact->address_line_2) && !empty($applicant->contact->address_line_2)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->address_line_2.'</span><br/>';
                                        endif;
                                        if(isset($applicant->contact->city) && !empty($applicant->contact->city)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->city.'</span>, ';
                                        endif;
                                        if(isset($applicant->contact->state) && !empty($applicant->contact->state)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->state.'</span>, <br/>';
                                        endif;
                                        if(isset($applicant->contact->post_code) && !empty($applicant->contact->post_code)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->post_code.'</span>, ';
                                        endif;
                                        if(isset($applicant->contact->country) && !empty($applicant->contact->country)):
                                            $html .= '<span class="font-medium">'.$applicant->contact->country.'</span><br/>';
                                        endif;
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-3" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-3" aria-expanded="false" aria-controls="applicantReviewAccordion-col-3">';
                        $html .= 'Next of Kin';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-3" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-3" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Name</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->kin->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Relation</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->kin->relation->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Mobile</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->kin->mobile.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-3">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Email</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->kin->email) && !empty($applicant->kin->email) ? $applicant->kin->email : '---').'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-6">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-12 text-slate-500 font-medium">Address</div>';
                                    $html .= '<div class="col-span-12 font-medium">';
                                        if(isset($applicant->kin->address_line_1) && !empty($applicant->kin->address_line_1)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->address_line_1.'</span><br/>';
                                        endif;
                                        if(isset($applicant->kin->address_line_2) && !empty($applicant->kin->address_line_2)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->address_line_2.'</span><br/>';
                                        endif;
                                        if(isset($applicant->kin->city) && !empty($applicant->kin->city)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->city.'</span>, ';
                                        endif;
                                        if(isset($applicant->kin->state) && !empty($applicant->kin->state)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->state.'</span>, <br/>';
                                        endif;
                                        if(isset($applicant->kin->post_code) && !empty($applicant->kin->post_code)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->post_code.'</span>, ';
                                        endif;
                                        if(isset($applicant->kin->country) && !empty($applicant->kin->country)):
                                            $html .= '<span class="font-medium">'.$applicant->kin->country.'</span><br/>';
                                        endif;
                                    $html .='</div>';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-4" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-4" aria-expanded="false" aria-controls="applicantReviewAccordion-col-4">';
                        $html .= 'Proposed Course & Programme';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-4" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-4" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->course->creation->course->name.' - '.$applicant->course->semester->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Venue</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->course->venue->name.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">How are you funding your education at London Churchill College?</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->course->student_loan.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            if($applicant->course->student_loan == 'Student Loan'):
                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-4 text-slate-500 font-medium">If your funding is through Student Finance England, please choose from the following. Have you applied for the proposed course?</div>';
                                        $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                                if(isset($applicant->course->student_finance_england) && $applicant->course->student_finance_england == 1):
                                    $html .= '<div class="col-span-12 sm:col-span-12">';
                                        $html .= '<div class="grid grid-cols-12 gap-0">';
                                            $html .= '<div class="col-span-4 text-slate-500 font-medium">Are you already in receipt of funds?</div>';
                                            $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->fund_receipt) && $applicant->course->fund_receipt == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                endif;
                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-4 text-slate-500 font-medium">Have you ever apply/Received any fund/Loan from SLC/government Loan for any other programme/institution?</div>';
                                        $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->applied_received_fund) && $applicant->course->applied_received_fund == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            elseif($applicant->course->student_loan == 'Others'):
                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-4 text-slate-500 font-medium">Other Funding</div>';
                                        $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->other_funding) && $applicant->course->other_funding == '' ? $applicant->course->other_funding : '').'</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            endif;
                            if(isset($applicant->course->creation->has_evening_and_weekend) && $applicant->course->creation->has_evening_and_weekend == 1):
                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Are you applying for evening and weekend classes (Full Time)</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            endif;

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-5" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-5" aria-expanded="false" aria-controls="applicantReviewAccordion-col-5">';
                        $html .= 'Education Qualifications';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-5" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-5" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Do you have any formal academic qualification? </div>';
                                    $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            if(isset($applicant->other->is_edication_qualification) && $applicant->other->is_edication_qualification == 1):
                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<table class="table table-bordered">';
                                        $html .= '<thead>';
                                            $html .= '<tr>';
                                                $html .= '<th class="whitespace-nowrap">#</th>';
                                                $html .= '<th class="whitespace-nowrap">Awarding Body</th>';
                                                $html .= '<th class="whitespace-nowrap">Highest Academic Qualification</th>';
                                                $html .= '<th class="whitespace-nowrap">Subjects</th>';
                                                $html .= '<th class="whitespace-nowrap">Result</th>';
                                                $html .= '<th class="whitespace-nowrap">Award Date</th>';
                                            $html .= '</tr>';
                                        $html .= '</thead>';
                                        $html .= '<tbody>';
                                            if(!empty($applicant->quals)):
                                                $i = 1;
                                                foreach($applicant->quals as $qual):
                                                    $html .= '<tr>'; 
                                                        $html .= '<td>'.$i.'</td>';
                                                        $html .= '<td>'.$qual->awarding_body.'</td>';
                                                        $html .= '<td>'.$qual->highest_academic.'</td>';
                                                        $html .= '<td>'.$qual->subjects.'</td>';
                                                        $html .= '<td>'.$qual->result.'</td>';
                                                        $html .= '<td>'.$qual->degree_award_date.'</td>';
                                                    $html .= '</tr>';
                                                    $i++;
                                                endforeach;
                                            else:
                                                $html .= '<tr>'; 
                                                    $html .= '<td colspan="6" class="text-center">No Record Found!</td>';
                                                $html .= '</tr>';
                                            endif;
                                        $html .= '</tbody>';
                                    $html .= '</table>';
                                $html .= '</div>';
                            endif;

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-6" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-6" aria-expanded="false" aria-controls="applicantReviewAccordion-col-6">';
                        $html .= 'Employment History';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-6" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-6" data-tw-parent="#applicantReviewAccordion">';
                    $html .= '<div class="accordion-body px-5 pt-6">';
                        $html .= '<div class="grid grid-cols-12 gap-4">'; 

                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">What is your current employment status?</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->other->employment_status.'</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                            if(isset($applicant->other->employment_status) && ($applicant->other->employment_status != 'Unemployed' && $applicant->other->employment_status != 'Contractor' && $applicant->other->employment_status != 'Consultant' && $applicant->other->employment_status != 'Office Holder')):
                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<table class="table table-bordered">';
                                        $html .= '<thead>';
                                            $html .= '<tr>';
                                                $html .= '<th class="whitespace-nowrap">#</th>';
                                                $html .= '<th class="whitespace-nowrap">Company</th>';
                                                $html .= '<th class="whitespace-nowrap">Phone</th>';
                                                $html .= '<th class="whitespace-nowrap">Position</th>';
                                                $html .= '<th class="whitespace-nowrap">Start</th>';
                                                $html .= '<th class="whitespace-nowrap">End</th>';
                                                $html .= '<th class="whitespace-nowrap">Address</th>';
                                                $html .= '<th class="whitespace-nowrap">Contact Person</th>';
                                                $html .= '<th class="whitespace-nowrap">Position</th>';
                                                $html .= '<th class="whitespace-nowrap">Phone</th>';
                                            $html .= '</tr>';
                                        $html .= '</thead>';
                                        $html .= '<tbody>';
                                            if(!empty($applicant->employment)):
                                                $i = 1;
                                                foreach($applicant->employment as $emps):
                                                    $continuing = (isset($emps->continuing) && $emps->continuing > 0 ? $emps->continuing : 0);
                                                    $address = '';
                                                    if(isset($emps->address_line_1) && !empty($emps->address_line_1)):
                                                        $address .= '<span class="font-medium">'.$emps->address_line_1.'</span><br/>';
                                                    endif;
                                                    if(isset($emps->address_line_2) && !empty($emps->address_line_2)):
                                                        $address .= '<span class="font-medium">'.$emps->address_line_2.'</span><br/>';
                                                    endif;
                                                    if(isset($emps->city) && !empty($emps->city)):
                                                        $address .= '<span class="font-medium">'.$emps->city.'</span>, ';
                                                    endif;
                                                    if(isset($emps->state) && !empty($emps->state)):
                                                        $address .= '<span class="font-medium">'.$emps->state.'</span>, <br/>';
                                                    endif;
                                                    if(isset($emps->post_code) && !empty($emps->post_code)):
                                                        $address .= '<span class="font-medium">'.$emps->post_code.'</span>, ';
                                                    endif;
                                                    if(isset($emps->country) && !empty($emps->country)):
                                                        $address .= '<span class="font-medium">'.$emps->country.'</span><br/>';
                                                    endif;
                                                    $html .= '<tr>'; 
                                                        $html .= '<td>'.$i.'</td>';
                                                        $html .= '<td>'.$emps->company_name.'</td>';
                                                        $html .= '<td>'.$emps->company_phone.'</td>';
                                                        $html .= '<td>'.$emps->position.'</td>';
                                                        $html .= '<td>'.$emps->start_date.'</td>';
                                                        $html .= '<td>'.($continuing == 1 ? 'Continue' : $emps->end_date).'</td>';
                                                        $html .= '<td>'.$address.'</td>';
                                                        $html .= '<td>'.$emps->reference[0]->name.'</td>';
                                                        $html .= '<td>'.$emps->reference[0]->position.'</td>';
                                                        $html .= '<td>'.$emps->reference[0]->phone.'</td>';
                                                    $html .= '</tr>';
                                                    $i++;
                                                endforeach;
                                            else:
                                                $html .= '<tr>'; 
                                                    $html .= '<td colspan="6" class="text-center">No Record Found!</td>';
                                                $html .= '</tr>';
                                            endif;
                                        $html .= '</tbody>';
                                    $html .= '</table>';
                                $html .= '</div>';
                            endif;

                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
            //implement residency status and criminal conviction sections below here
                $residencyStatusName = (isset($applicant->residency->residencyStatus->name) ? $applicant->residency->residencyStatus->name : '---');
                $criminalDeclarationHtml = (isset($applicant->criminalConviction->criminal_declaration) && (int) $applicant->criminalConviction->criminal_declaration === 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>');
                $criminalConvictionHtml = (isset($applicant->criminalConviction->have_you_been_convicted) && (int) $applicant->criminalConviction->have_you_been_convicted === 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>');
                $criminalConvictionDetails = (isset($applicant->criminalConviction->criminal_conviction_details) && $applicant->criminalConviction->criminal_conviction_details != '' ? $applicant->criminalConviction->criminal_conviction_details : '---');

                $html .= '<div class="accordion-item mb-1">';
                    $html .= '<div id="applicantReviewAccordion-c-7" class="accordion-header">';
                        $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-7" aria-expanded="false" aria-controls="applicantReviewAccordion-col-7">';
                            $html .= 'Residency Status & Criminal Convictions';
                            $html .= '<span class="accordionCollaps"></span>';
                        $html .= '</button>';
                    $html .= '</div>';
                    $html .= '<div id="applicantReviewAccordion-col-7" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-7" data-tw-parent="#applicantReviewAccordion">';
                        $html .= '<div class="accordion-body px-5 pt-6">';
                            $html .= '<div class="grid grid-cols-12 gap-4">';
                                $html .= '<div class="col-span-6">';
                                    $html .= '<div class="col-span-12">';
                                        $html .= '<div class="grid grid-cols-12 gap-0">';
                                            $html .= '<div class="col-span-5 text-slate-500 font-medium">Residency Status</div>';
                                            $html .= '<div class="col-span-7 font-medium">'.$residencyStatusName.'</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                    // $html .= '<div class="col-span-12">';
                                    //     $html .= '<div class="grid grid-cols-12 gap-0">';
                                    //         $html .= '<div class="col-span-5 text-slate-500 font-medium">Declaration Accepted</div>';
                                    //         $html .= '<div class="col-span-7 font-medium">'.$criminalDeclarationHtml.'</div>';
                                    //     $html .= '</div>';
                                    // $html .= '</div>';
                                $html .= '</div>';
                                $html .= '<div class="col-span-6">';
                                    $html .= '<div class="col-span-12">';
                                        $html .= '<div class="grid grid-cols-12 gap-0">';
                                            $html .= '<div class="col-span-5 text-slate-500 font-medium">Criminal Conviction</div>';
                                            $html .= '<div class="col-span-7 font-medium">'.$criminalConvictionHtml.'</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                    $html .= '<div class="col-span-12">';
                                        $html .= '<div class="grid grid-cols-12 gap-0">';
                                            $html .= '<div class="col-span-5 text-slate-500 font-medium">Conviction Details</div>';
                                            $html .= '<div class="col-span-7 font-medium">'.$criminalConvictionDetails.'</div>';
                                        $html .= '</div>';
                                    $html .= '</div>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="accordion-item mb-1">';
                    $html .= '<div id="applicantReviewAccordion-c-8" class="accordion-header">';
                        $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-8" aria-expanded="false" aria-controls="applicantReviewAccordion-col-8">';
                            $html .= 'Others';
                            $html .= '<span class="accordionCollaps"></span>';
                        $html .= '</button>';
                    $html .= '</div>';
                    $html .= '<div id="applicantReviewAccordion-col-8" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-8" data-tw-parent="#applicantReviewAccordion">';
                        $html .= '<div class="accordion-body px-5 pt-6">';
                            $html .= '<div class="grid grid-cols-12 gap-4">';

                                $html .= '<div class="col-span-12 sm:col-span-12">';
                                    $html .= '<div class="grid grid-cols-12 gap-0">';
                                        $html .= '<div class="col-span-4 text-slate-500 font-medium">If you referred by Somone/ Agent, Please enter the Referral Code.</div>';
                                        $html .= '<div class="col-span-8 font-medium">'.($applicant->referral_code != '' ? $applicant->referral_code : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                    $html .= '</div>';
                                $html .= '</div>';

                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';

        $html .= '</div>';

        return response()->json(['htmls' => $html], 200);
    }

    public function show($id){
        $applicant = Applicant::where('id', $id)->first();
        if(\Auth::guard('applicant')->user()->id != $applicant->applicant_user_id):
            redirect('applicant.dashboard');
        endif;
        return view('pages.applicant.application.show', [
            'title' => 'Application View - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Application View', 'href' => 'javascript:void(0);']
            ],
            'applicant' => $applicant
        ]);
    }

    public function verifyReferralCode(Request $request){
        $applicantId = $request->applicantId;
        $code = $request->code;
        $applicant = Applicant::find($applicantId);

        $res = [];
        $referralCodes = ReferralCode::where('code', $code)->first();
        if(isset($referralCodes->code) && !empty($referralCodes->code) && $referralCodes->code == $code){
            $applicantUpdate = Applicant::where('id', $applicantId)->update([
                'referral_code' => $code,
                'is_referral_varified' => 1
            ]);

            $res['suc'] = 1;
            $res['code'] = $code;
            $res['is_referral_varified'] = 1;
        }else{
            $res['suc'] = 2;
            $res['code'] = $applicant->referral_code;
            $res['is_referral_varified'] = $applicant->is_referral_varified;
        }

        return response()->json(['msg' => $res], 200);
    }

    public function getEveningWeekendStatus(Request $request){
        $course_creation_id = $request->course_creation_id;
        $venue_id = $request->venue_id;

        $creationVenue = CourseCreationVenue::where('course_creation_id', $course_creation_id)->where('venue_id', $venue_id)->get()->first();
        if((isset($creationVenue->evening_and_weekend) && $creationVenue->evening_and_weekend == 1) && (isset($creationVenue->weekends) && $creationVenue->weekends > 0)):
            if($creationVenue->weekdays > 0):
                return response()->json(['weekends' => 1], 200);
            else:
                return response()->json(['weekends' => 2], 200);
            endif;
        else:
            return response()->json(['weekends' => 0], 200);
        endif;
    }
}
