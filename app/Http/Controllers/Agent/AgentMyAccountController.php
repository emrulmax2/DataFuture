<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentUser;
use Illuminate\Http\Request;

class AgentMyAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userData = Agent::with('AgentUser')->where('agent_user_id', auth('agent')->user()->id)->get()->first();
        return view('pages.agent.my-account.index', [
            'title' => 'My Account - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My Account', 'href' => 'javascript:void(0);']
            ],
            'user' => AgentUser::find(auth('agent')->user()->id),
            'userData' => $userData,
        ]);
    }

}
