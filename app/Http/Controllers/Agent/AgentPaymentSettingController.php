<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentBankStoreRequest;
use App\Models\Agent;
use App\Models\AgentBankDetail;
use App\Models\AgentUser;
use App\Models\Option;
use Illuminate\Http\Request;

class AgentPaymentSettingController extends Controller
{
    public function index($id){
        $employee = Agent::find($id);
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();

        return view('pages.agent.profile.payment.index', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Payment Settings', 'href' => 'javascript:void(0);']
            ],
            "employee" => $employee,
            "userData" => $userData,
            "postcodeApi" => $PostCodeAPI,
        ]);
    }
}
