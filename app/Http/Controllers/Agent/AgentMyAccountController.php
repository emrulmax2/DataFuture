<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentUser;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * The agent's own account: who they are to the college, their referral code
 * and sub-agents, and a password change that works from the signed-in session
 * (the older `change-password` endpoint took the account id from the request
 * body and never checked the current password).
 */
class AgentMyAccountController extends Controller
{
    public function index()
    {
        $user = auth('agent')->user();
        $agent = $user->agent;

        /* One line per sub-agent: its organisation (or contact name) and referral code. */
        $subs = AgentUser::where('parent_id', $user->id)->with('agent')->orderBy('id')->get();
        $codes = ReferralCode::whereIn('agent_user_id', $subs->pluck('id')->push($user->id))->pluck('code', 'agent_user_id');
        $label = fn($u) => $u->agent ? ($u->agent->organization ?: trim($u->agent->first_name.' '.$u->agent->last_name)) : $u->email;

        return view('pages.agent.my-account.index', [
            'title' => 'My Account - London Churchill College',
            'layout' => 'agent-portal',
            'breadcrumbs' => [],
            'user' => $user,
            'verified' => !empty($user->email_verified_at),
            'company' => $agent ? ($agent->organization ?: trim($agent->first_name.' '.$agent->last_name)) : null,
            'code' => $codes[$user->id] ?? null,
            'subs' => $subs->map(fn($u) => ['name' => $label($u), 'code' => $codes[$u->id] ?? null])->values(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth('agent')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Enter your current password.',
            'password.required' => 'Enter a new password.',
            'password.min' => 'Use at least 8 characters.',
            'password.confirmed' => 'The two new passwords do not match.',
        ]);

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'That is not your current password.']);
        }

        /* AgentUser hashes the password in its mutator: assign it plain, or it
           is hashed twice and the agent is locked out. */
        $user->password = $request->input('password');
        $user->save();

        return back()->with('account_status', 'Your password has been changed.');
    }
}
