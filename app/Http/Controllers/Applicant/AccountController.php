<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * The applicant's own sign-in details.
 *
 * Password changes live here rather than on the existing
 * `applicant/change-password` endpoint: that one sits behind the
 * `applicant.loggedin` middleware, which redirects a signed-in applicant away,
 * and it takes the account id from the request body. This one works from the
 * authenticated session and never trusts an id it was handed.
 */
class AccountController extends Controller
{
    public function index()
    {
        $user = \Auth::guard('applicant')->user();

        /* The mobile belongs to the application, not to the sign-in account, so
           it is shown read-only from the most recent one the applicant filed. */
        $latest = Applicant::with('contact')
            ->where('applicant_user_id', $user->id)
            ->orderBy('id', 'DESC')
            ->first();

        return view('pages.applicant.account', [
            'title' => 'My Account - London Churchill College',
            'layout' => 'applicant-portal',
            'breadcrumbs' => [],
            'user' => $user,
            'mobile' => optional(optional($latest)->contact)->mobile,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = \Auth::guard('applicant')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Enter your current password.',
            'password.required' => 'Enter a new password.',
            'password.confirmed' => 'The two new passwords do not match.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()
                ->withErrors(['current_password' => 'That is not your current password.'])
                ->withInput();
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('account_status', 'Your password has been changed.');
    }
}
