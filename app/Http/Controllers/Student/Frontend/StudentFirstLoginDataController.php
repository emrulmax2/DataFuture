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
use App\Models\StudentContact;
use App\Models\StudentOtherDetail;
use App\Models\StudentUser;
use Illuminate\Http\Request;

class StudentFirstLoginDataController extends Controller
{
    public function firstData(Request $request)
    { 

        $sexIdentifier = SexIdentifier::find($request->gender);

        $StudentData = Student::find($request->student_id);
        $StudentData->nationality_id = $request->nationality; 
        $StudentData->country_id = $request->birth_country; 
        $StudentData->sex_identifier_id = $request->sex_identifier_id;
        $StudentData->save();

        $otherDetailsId = StudentOtherDetail::where('student_id',$request->student_id)->get()->first()->pluck('id')->toArray();
        
        $studentOtherDetails = StudentOtherDetail::find($otherDetailsId[0]);
        $studentOtherDetails->religion_id  = $request->ethnicity;
        $studentOtherDetails->ethnicity_id = $request->religion;
        $studentOtherDetails->sexual_orientation_id = $request->sexual_orientation;
        $studentOtherDetails->hesa_gender_id = $request->gender;
        $studentOtherDetails->save();

        return response()->json("Data Updated");
         
    }
    public function addressesConfirm(Request $request)
    {
        // "address_line_1" => null
        // "address_line_2" => null
        // "post_code" => null
        // "city" => null
        // "state" => null
        // "country" => null
        // "current_address_id" => "23"
        // "permanent_address_line_1" => "House#335,Road-15,Block-K"
        // "permanent_address_line_2" => "South Banasree, Khilgaon"
        // "permanent_post_code" => "1219"
        // "permanent_city" => "Dhaka"
        // "permanent_state" => "New York"
        // "permanent_country" => "Bangladesh"
        // "permanent_address_id" => null
        // "student_id" => "52"
        // "term_time_accommodation_type_id" => something
        if($request->current_address_id==null) {
            // New Address insert then connect link
                $address = new Address();
                $addressData = [
                    "address_line_1" =>$request->address_line_1,
                    "address_line_2" =>$request->address_line_2,
                    "state" =>$request->state,
                    "post_code" =>$request->post_code,
                     "city" =>$request->city,
                     "country" =>$request->country,
                     "active" =>1,
                     "created_by" =>1
                ];
                $address->fill($addressData);
                $address->save();
                
                $studentContactId = StudentContact::where("student_id", $request->student_id)->get()->first()->pluck('id')->toArray();
                $studentContact = StudentContact::find($studentContactId[0]);
                $studentContact->term_time_address_id = $address->id;
                $studentContact->term_time_accommodation_type_id = $request->term_time_accommodation_type_id;
                $studentContact->save();

        } 
        if($request->permanent_address_id==null && $request->permanent_address_line_1 == null)  {
            // get the current address data and use it for permanent address

            $studentContactId = StudentContact::where("student_id", $request->student_id)->get()->first()->pluck('id')->toArray();
            $studentContact = StudentContact::find($studentContactId[0]);
            $studentContact->permanent_address_id  = $request->current_address_id;
            $studentContact->term_time_accommodation_type_id = $request->term_time_accommodation_type_id;
            $studentContact->save();

        } else if($request->permanent_address_id==null && $request->permanent_address_line_1 != null) {
            //get the permanent address data and insert it
                $address = new Address();
                $addressData = [
                    "address_line_1" =>$request->permanent_address_line_1,
                    "address_line_2" =>$request->permanent_address_line_2,
                    "state" =>$request->permanent_state,
                    "post_code" =>$request->permanent_post_code,
                     "city" =>$request->permanent_city,
                     "country" =>$request->permanent_country,
                     "active" =>1,
                     "created_by" =>1
                ];
                $address->fill($addressData);
                $address->save();
                
                $studentContactId = StudentContact::where("student_id", $request->student_id)->get()->first()->pluck('id')->toArray();
                $studentContact = StudentContact::find($studentContactId[0]);
                $studentContact->permanent_address_id = $address->id;
                $studentContact->term_time_accommodation_type_id = $request->term_time_accommodation_type_id;
                $studentContact->save();

        }else if($request->permanent_address_id==null && $request->permanent_address_line_1 == null && $request->current_address_id==null && $request->address_line_1!=null)  {
            //get the permanent address data and insert it
                $address = new Address();
                $addressData = [
                    "address_line_1" =>$request->address_line_1,
                    "address_line_2" =>$request->address_line_2,
                    "state" =>$request->state,
                    "post_code" =>$request->post_code,
                    "city" =>$request->city,
                    "country" =>$request->country,
                    "active" =>1,
                    "created_by" =>1
                ];
                $address->fill($addressData);
                $address->save();
                
                $studentContactId = StudentContact::where("student_id", $request->student_id)->get()->first()->pluck('id')->toArray();
                $studentContact = StudentContact::find($studentContactId[0]);
                $studentContact->permanent_address_id = $address->id;
                $studentContact->term_time_accommodation_type_id = $request->term_time_accommodation_type_id;
                $studentContact->save();

        } else if($request->permanent_address_id!=null) {
            // insert the permanent address Id to student contacts table
            $studentContactId = StudentContact::where("student_id", $request->student_id)->get()->first()->pluck('id')->toArray();
            $studentContact = StudentContact::find($studentContactId[0]);
            $studentContact->permanent_address_id  = $request->permanent_address_id;
            $studentContact->term_time_accommodation_type_id = $request->term_time_accommodation_type_id;
            $studentContact->save();
        }

        return response()->json(["Address Updated"]);
    }
    public function consentConfirm(Request $request)
    {
        $consentIds = [];
        foreach($request->consent_number as $consent) {
                $studentConsent = new StudentConsent();
                $data = [
                    "student_id" => $request->student_id,
                    "consent_policy_id" => $consent,
                    "status" => "Agree",
                    "created_by" => 1
                ];
                $studentConsent->fill($data);
                $studentConsent->save();
                $consentIds[] = $consent;
        }

        $consentPolicies = ConsentPolicy::all();
        if(!empty($consentPolicies)):
            foreach($consentPolicies as $csp):
                if(!in_array($csp->id, $consentIds)):
                    $studentConsent = new StudentConsent();
                    $data = [
                        "student_id" => $request->student_id,
                        "consent_policy_id" => $csp->id,
                        "status" => "Disagree",
                        "created_by" => 1
                    ];
                    $studentConsent->fill($data);
                    $studentConsent->save();
                endif;
            endforeach;
        endif;

        $student = Student::find($request->student_id);
        
        $studentUser = StudentUser::find($student->users->id);
        $studentUser->first_login = 0;
        $studentUser->save();

        return response()->json(["Consent Updated"]);
    }
    public function reviewShows()
    {

    }
    public function reviewDone()
    {

    }
}
