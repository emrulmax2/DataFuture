<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class LoginController extends Controller
{


    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to find the user
        $studentUser = StudentUser::where('email', $request->email)->first();
        
        // Check if user exists and password is correct
        if (!$studentUser || !Hash::check($request->password, $studentUser->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $studentUser->update([
            'last_login_ip' => $request->getClientIp(),
            'last_login_at' => Carbon::now()
        ]);


        $token = $studentUser->createToken('student-token')->accessToken;


        
        // Return the token in the response
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => new \App\Http\Resources\StudentUserResource($studentUser),
            'redirect_url' => route('api.user.dashboard'),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Logged out successfully']);
    }
}