<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentProfileResource;
use App\Models\ReferralCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StudentProfileController extends Controller
{
    public function index(Request $request)
    {
        $theUser = $request->user();

        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }

        $cacheKey = 'profile_data_student_user_' . $theUser->id;

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($theUser) {

            $student = Student::with([
                'title',
                'status',
                'sexid',
                'nation',
                'country',
                'users',
                'other',
                'other.ethnicity',
                'other.leaver',
                'other.sexori',
                'other.gender',
                'other.religion',
                'other.mode',
                'disability',
                'disability.disabilities',
                'residency',
                'residency.residencyStatus',
                'criminalConviction',
                'contact',
                'contact.termaddress',
                'contact.permaddress',
                'contact.ttacom',
                'contact.pcountry',
                'kin',
                'kin.relation',
                'kin.address',
                'consents',
                'consents.consent',
            ])->where('student_user_id', $theUser->id)->first();

            if (!$student) {
                return null;
            }

            // Resolve referral and attach to the model so the resource can access it
            $student->referralData = null;
            if (
                isset($student->referral_code) && !empty($student->referral_code) &&
                isset($student->is_referral_varified) && $student->is_referral_varified == 1
            ) {
                $student->referralData = ReferralCode::with(['user', 'student', 'student.users', 'student.contact'])
                    ->where('code', $student->referral_code)
                    ->first();
            }

            return new StudentProfileResource($student);
        });

        if (!$data) {
            return response()->json(['success' => false, 'error' => 'Student record not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile data retrieved successfully.',
            'data'    => $data,
        ], 200);
    }
}
