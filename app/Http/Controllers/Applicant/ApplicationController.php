<?php

namespace App\Http\Controllers\Applicant;

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
use App\Models\Applicant;
use App\Models\ApplicantContact;
use App\Models\ApplicantDisability;
use App\Models\ApplicantEmployment;
use App\Models\ApplicantKin;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProposedCourse;
use App\Models\ApplicantQualification;
use App\Models\CourseCreationAvailability;
use App\Models\CourseCreationInstance;
use App\Models\EmploymentReference;
use Illuminate\Support\Carbon;

class ApplicationController extends Controller
{
    public function index(){
        return view('pages.applicant.application.index', [
            'title' => 'Application Form - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Application Form', 'href' => 'javascript:void(0);']
            ],
            'titles' => Title::all(),
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'disability' => Disability::all(),
            'relations' => KinsRelation::all(),
            'bodies' => AwardingBody::all(),
            'users' => User::all(),
            'applicant' => \Auth::guard('applicant')->user(),
            'apply' => Applicant::where('applicant_user_id', \Auth::guard('applicant')->user()->id)->whereNull('submission_date')->orderBy('id', 'DESC')->first(),
            'courseCreationAvailibility' => CourseCreationAvailability::all()->filter(function($item) {
                if (Carbon::now()->between($item->admission_date, $item->admission_end_date)) {
                  return $item;
                }
            })
        ]);
    }

    public function storePersonalDetails(ApplicationPersonalDetailsRequest $request){
        $lastApplicantRow = Applicant::orderBy('id', 'DESC')->get()->first();
        $lastApplicantId = (isset($lastApplicantRow->id) && !empty($lastApplicantRow->id));
        $applicantUserId = $request->applicant_user_id;
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::updateOrCreate([ 'applicant_user_id' => $applicantUserId, 'id' => $applicant_id ], [
            'applicant_id' => $applicantUserId,
            'title_id' => $request->title_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'status_id' => 1,
            'nationality_id' => $request->nationality_id,
            'country_id' => $request->country_id,
            'created_by' => \Auth::guard('applicant')->user()->id,
            'updated_by' => \Auth::guard('applicant')->user()->id,
        ]);
        if($applicant){
            if(isset($applicant->application_no) && empty($applicant->application_no)):
                $theApplicantId = $applicant->id;
                $appNo = '100'.sprintf('%05d', $theApplicantId);
                Applicant::where('id', $theApplicantId)->update(['application_no' => $appNo]);
            endif;
            $disabilityStatus = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
            $otherDetails = ApplicantOtherDetail::updateOrCreate(['applicant_id' => $applicant->id], [
                    'ethnicity_id' => $request->ethnicity_id,
                    'disability_status' => $disabilityStatus,
                    'disabilty_allowance' => ($disabilityStatus == 1 && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0),
                    'created_by' => \Auth::guard('applicant')->user()->id,
                    'updated_by' => \Auth::guard('applicant')->user()->id,
                ]
            );
            if($disabilityStatus == 1 && isset($request->disability_id) && !empty($request->disability_id)):
                $applicantDisablity = ApplicantDisability::where('applicant_id', $applicant->id)->forceDelete();
                foreach($request->disability_id as $disabilityID):
                    $applicantDisabilities = ApplicantDisability::create([
                        'applicant_id' => $applicant->id,
                        'disabilitiy_id' => $disabilityID,
                        'created_by' => \Auth::guard('applicant')->user()->id,
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
                'created_by' => \Auth::guard('applicant')->user()->id,
                'updated_by' => \Auth::guard('applicant')->user()->id,
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
                'created_by' => \Auth::guard('applicant')->user()->id,
                'updated_by' => \Auth::guard('applicant')->user()->id,
            ]);
            return response()->json(['message' => 'WOW! Data successfully inserted.', 'applicant_id' => $applicant->id], 200);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    public function storeCourseDetails(ApplicantCourseDetailsRequest $request){
        $applicant_id = $request->applicant_id;
        $course_creation_id = $request->course_creation_id;
        $courseCreation = CourseCreation::find($course_creation_id);
        $studentLoan = $request->student_loan;
        $studentFinanceEngland = ($studentLoan == 'Student Loan' && isset($request->student_finance_england) && $request->student_finance_england > 0 ? $request->student_finance_england : null);
        $appliedReceivedFund = ($studentLoan == 'Student Loan' && isset($request->applied_received_fund) && $request->applied_received_fund > 0 ? $request->applied_received_fund : null);
        $fundReceipt = ($studentFinanceEngland == 1 && isset($request->fund_receipt) && $request->fund_receipt > 0 ? $request->fund_receipt : null);

        $course = ApplicantProposedCourse::updateOrCreate(['applicant_id' => $applicant_id], [
            'course_creation_id' => $course_creation_id,
            'semester_id' => $courseCreation->semester_id,
            'student_loan' => $studentLoan,
            'student_finance_england' => $studentFinanceEngland,
            'applied_received_fund' => $appliedReceivedFund,
            'fund_receipt' => $fundReceipt,
            'other_funding' => ($studentLoan == 'Others' && isset($request->other_funding) && !empty($request->other_funding) ? $request->other_funding : null),
            'full_time' => (isset($request->full_time) && $request->full_time > 0 ? $request->full_time : 0),
            'created_by' => \Auth::guard('applicant')->user()->id,
            'updated_by' => \Auth::guard('applicant')->user()->id,
        ]);
        if($course):
            $isEducationQualification = (isset($request->is_edication_qualification) && $request->is_edication_qualification > 0 ? $request->is_edication_qualification : 0);
            $employmentStatus = (isset($request->employment_status) && !empty($request->employment_status) ? $request->employment_status : '');
            $otherDetails = ApplicantOtherDetail::updateOrCreate(['applicant_id' => $applicant_id], [
                'is_edication_qualification' => $isEducationQualification,
                'employment_status' => $employmentStatus,
                'updated_by' => \Auth::guard('applicant')->user()->id,
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

            if(isset($request->referral_code) && !empty($request->referral_code)):
                $ref = Applicant::where('id', $applicant_id)->update([
                    'referral_code' => $request->referral_code,
                    'is_referral_varified' => 0,
                    'updated_by' => \Auth::guard('applicant')->user()->id,
                ]);
            endif;
            return response()->json(['message' => 'Course details successfully inserted or updated', 'applicant_id' => $applicant_id], 200);
        else:
            return response()->json(['message' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function storeApplicantSubmission(Request $request){
        $applicant_id = $request->applicant_id;
        $applicant = Applicant::where('id', $applicant_id)->update([
            'status_id' => 2,
            'submission_date' => date('Y-m-d'),
            'updated_by' => \Auth::guard('applicant')->user()->id,
        ]);
        session(['applicantSubmission' => 'Application successfully submitted.']);
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
                                    $html .= '<div class="col-span-8 font-medium">'.$applicant->gender.'</div>';
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
                            $html .= '<div class="col-span-12 sm:col-span-12">';
                                $html .= '<div class="grid grid-cols-12 gap-0">';
                                    $html .= '<div class="col-span-4 text-slate-500 font-medium">Are you applying for evening and weekend classes (Full Time)</div>';
                                    $html .= '<div class="col-span-8 font-medium">'.(isset($applicant->course->full_time) && $applicant->course->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white">No</span>').'</div>';
                                $html .= '</div>';
                            $html .= '</div>';

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
                                                        $html .= '<td>'.($continuing == 1 ? 'Continue' : $qual->end_date).'</td>';
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

            $html .= '<div class="accordion-item mb-1">';
                $html .= '<div id="applicantReviewAccordion-c-7" class="accordion-header">';
                    $html .= '<button class="accordion-button collapsed px-5 relative w-full btn-primary-soft text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#applicantReviewAccordion-col-7" aria-expanded="false" aria-controls="applicantReviewAccordion-col-7">';
                        $html .= 'Others';
                        $html .= '<span class="accordionCollaps"></span>';
                    $html .= '</button>';
                $html .= '</div>';
                $html .= '<div id="applicantReviewAccordion-col-7" class="accordion-collapse collapse" aria-labelledby="applicantReviewAccordion-c-7" data-tw-parent="#applicantReviewAccordion">';
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
        return view('pages.applicant.application.show', [
            'title' => 'Application View - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Application View', 'href' => 'javascript:void(0);']
            ],
            'applicant' => Applicant::where('id', $id)->first(),
        ]);
    }
}
