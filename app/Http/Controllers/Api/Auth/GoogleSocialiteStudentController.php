<?php

namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;

use Google\Client as GoogleClient;
use Exception;
use Carbon\Carbon;
use App\Models\StudentUser;
use Illuminate\Http\Request;

class GoogleSocialiteStudentController extends Controller
{
    

    public function redirectToGoogleAPI()
    {
        config(['services.google.redirect' => env('GOOGLE_STUDENT_REDIRECT_URL_API')]);
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallbackAPI(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {

            $client = new GoogleClient([
                'client_id' => config('services.google.client_id'),
            ]);

            // Verify Google ID token
            $payload = $client->verifyIdToken(
                $request->id_token
            );

            if (!$payload) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google ID token',
                ], 401);
            }
        
            $user = new stdClass();
            $user->id = $payload['sub'] ?? null;
            $user->email = $payload['email'] ?? null;
            
            $finduser = isset($user->id) ? StudentUser::where('social_id', $user->id)->first() : null;

            if($finduser) {

                $token = $finduser->createToken('student-token')->accessToken;
                
                $finduser->update([
                    'last_login_ip' => $request->getClientIp(),
                    'last_login_at' => Carbon::now()
                ]);

                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => new \App\Http\Resources\StudentUserResource($finduser),
                    'redirect_url' => route('api.user.dashboard'),
                ], 200);

            } else {

                $finduser = StudentUser::where('email', $user->email)->first();
                
                if (!$finduser) {
                    return response()->json([
                        'message' => 'No user found with this email.',
                    ], 404);
                }

                $finduser->social_id = $user->id;
                $finduser->social_type = 'google';
                $finduser->last_login_ip = $request->getClientIp();
                $finduser->last_login_at = Carbon::now();
                $finduser->save();

                $token = $finduser->createToken('student-token')->accessToken;
                
                return response()->json([
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => new \App\Http\Resources\StudentUserResource($finduser),
                    'redirect_url' => route('api.user.dashboard'),
                ], 200);

            }

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Google authentication failed',
                'error' => $e->getMessage(),
            ], 500);

        }
    }
}