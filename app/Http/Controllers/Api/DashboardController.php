<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentUser;
use App\Http\Resources\StudentResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    
    public function index(Request $request) 
    {

        $theUser = $request->user();
        if (!$theUser) {
            return response()->json(['success' => false, 'error' => 'No authenticated user found.'], 401);
        }
        $cacheKey = 'dashboard_data_student_user_' . $theUser->id;
        $data = Cache::remember($cacheKey, now()->addHours(1), function () use ($theUser) {
            $student = Student::with(['title','status','contact','contact.termaddress','contact.permaddress','contact.ttacom'])->where('student_user_id', $theUser->id)->first();
            return new StudentResource($student);
        });
            
        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully.',
            'data' => $data,
        ], 200);
    }
}
