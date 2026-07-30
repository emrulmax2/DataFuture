<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AgentBankStoreRequest;
use App\Models\Agent;
use App\Models\AgentBankDetail;
use App\Models\AgentDocuments;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\Option;
use Illuminate\Http\Request;

class AgentPaymentSettingController extends Controller
{
    public function index($id){
        $employee = Agent::find($id);
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();
        $agentUserList = AgentUser::where('id', $userData->id)->orWhere('parent_id', $userData->id)->get()->pluck('id')->toArray();

        return view('pages.agent.profile.payment.index', [
            'title' => 'Agent Management - London Churchill College',
            'layout' => 'agent-management-top-menu',
            'breadcrumbs' => [
                ['label' => 'Agent Creations', 'href' => route('agent-user.index')],
                ['label' => 'Payment Settings', 'href' => 'javascript:void(0);']
            ],
            "employee" => $employee,
            "userData" => $userData,
            "postcodeApi" => $PostCodeAPI,
            'profileTabCounts' => [
                'applicants' => Applicant::whereIn('agent_user_id', $agentUserList)->where('status_id', '>', 1)->count(),
                'sub' => AgentUser::where('parent_id', $userData->id)->count(),
                'docs' => AgentDocuments::where('agent_id', $employee->id)->where('type', 1)->count(),
                'pay' => AgentBankDetail::where('agent_id', $employee->id)->count(),
            ],
        ]);
    }
}
