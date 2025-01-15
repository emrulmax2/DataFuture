<?php

namespace App\Http\Controllers\Agent;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentComissionRuleStoreRequest;
use App\Models\AgentComissionRule;
use App\Models\AgentUser;
use App\Models\CourseCreation;
use App\Models\ReferralCode;
use App\Models\Semester;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Student;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AgentManagementController extends Controller
{
    public function index(){
        return view('pages.agent.management.index', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Management', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::orderBy('id', 'DESC')->get()
        ]);
    }

    public function list(Request $request){
        $semester_id = (isset($request->semester_id) && $request->semester_id > 0 ? $request->semester_id : 0);
        $creation_ids = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();

        $html = '';
        if(!empty($creation_ids)):
            $student = Student::whereHas('activeCR', function($q) use($creation_ids){
                            $q->whereIn('course_creation_id', $creation_ids);
                        })->where(function($q){
                            $q->whereNotNull('referral_code')->orWhere('referral_code', '!=', '');
                        })->where('is_referral_varified', 1)->get();
            if($student->count() > 0):
                $reff_codes = $student->pluck('referral_code')->unique()->toArray();
                $student_ids = $student->pluck('id')->unique()->toArray();
                $html .= '<table class="table table-bordered table-sm" id="referralCountTable">';
                    $html .= '<tr class="cursor-pointer result_row font-medium" data-semester="'.$semester_id.'">';
                        $html .= '<td>No of referral found</td>';
                        $html .= '<td class="w-[150px]">'.(!empty($reff_codes) && count($reff_codes) > 0 ? count($reff_codes) : 0).'</td>';
                        $html .= '<td>Total no of Student</td>';
                        $html .= '<td class="w-[150px]">'.(!empty($student_ids) && count($student_ids) > 0 ? count($student_ids) : 0).'</td>';
                    $html .= '</tr>';
                $html .= '</table>';
            else:
                $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Student not found for the Semester.</div>';
            endif;
        else:
            $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Semester does not started yet.</div>';
        endif;

        return response()->json(['html' => $html], 200);
    }

    public function listDetails(Request $request){
        $semester_id = (isset($request->semester_id) && $request->semester_id > 0 ? $request->semester_id : 0);
        $creation_ids = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();

        $html = '';
        if(!empty($creation_ids)):
            $student = Student::whereHas('activeCR', function($q) use($creation_ids){
                            $q->whereIn('course_creation_id', $creation_ids);
                        })->where(function($q){
                            $q->whereNotNull('referral_code')->orWhere('referral_code', '!=', '');
                        })->where('is_referral_varified', 1)->get();
            if($student->count() > 0):
                $reff_codes = $student->pluck('referral_code')->unique()->toArray();
                $html .= '<table class="table table-bordered table-sm" id="referralCountTable">';
                    $html .= '<thead>'; 
                        $html .= '<tr>';
                            $html .= '<th class="text-left">Referral Name</th>';
                            $html .= '<th class="text-left">Referral Code</th>';
                            $html .= '<th class="text-left">Type</th>';
                            $html .= '<th class="text-left">No of Student</th>';
                            $html .= '<th class="text-right w-[120px]">&nbsp;</th>';
                        $html .= '</tr>';
                    $html .= '</thead>'; 
                    $html .= '<tbody>'; 
                        if(!empty($reff_codes)):
                            foreach($reff_codes as $code):
                                $theCode = ReferralCode::where('code', $code)->get()->first();
                                $student_ids = Student::whereHas('activeCR', function($q) use($creation_ids){
                                                $q->whereIn('course_creation_id', $creation_ids);
                                            })->where('referral_code', $code)->where('is_referral_varified', 1)->pluck('id')->unique()->toArray();
                                $rules = AgentComissionRule::where('agent_user_id', $theCode->agent_user_id)->where('semester_id', $semester_id)->get()->first();
                                $html .= '<tr class="cursor-pointer code_row font-medium" data-code="'.$code.'" data-semester="'.$semester_id.'">';
                                    $html .= '<td>';
                                        if($theCode->type == 'Agent'):
                                            $html .= '<div>';
                                                $html .= '<div class="font-medium whitespace-nowrap">';
                                                    $html .= (isset($theCode->agent_user->agent->full_name) && !empty($theCode->agent_user->agent->full_name) ? $theCode->agent_user->agent->full_name : '');
                                                    $html .= (isset($theCode->agent_user->agent->organization) && !empty($theCode->agent_user->agent->organization) ? ' ('.$theCode->agent_user->agent->organization.')' : '');
                                                $html .= '</div>';
                                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.(isset($theCode->agent_user->email) && !empty($theCode->agent_user->email) ? $theCode->agent_user->email : '').'</div>';
                                            $html .= '</div>';
                                        elseif($theCode->type == 'Student'):
                                            $html .= '<div>';
                                                $html .= '<div class="font-medium whitespace-nowrap">'.(isset($theCode->student->full_name) && !empty($theCode->student->full_name) ? $theCode->student->full_name : '').'</div>';
                                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.(isset($theCode->student->contact->institutional_email) && !empty($theCode->student->contact->institutional_email) ? $theCode->student->contact->institutional_email : '').'</div>';
                                            $html .= '</div>';
                                        endif;
                                    $html .= '</td>';
                                    $html .= '<td>'.$code.'</td>';
                                    $html .= '<td>'.$theCode->type.'</td>';
                                    $html .= '<td class="w-[150px]">'.(!empty($student_ids) && count($student_ids) > 0 ? count($student_ids) : 0).'</td>';
                                    $html .= '<td class="text-right w-[150px]">';
                                        $html .= '<a href="'.route('agent.management.comission', [$semester_id, $theCode->agent_user_id]).'" id="comission_view_'.$semester_id.'_'.$theCode->agent_user_id.'" class="'.(isset($rules->id) && $rules->id > 0 ? '' : 'hidden').' mr-2 btn btn-linkedin text-white rounded-full p-0 w-[32px] h-[32px]"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                                        $html .= '<button data-code="'.$code.'" data-agent="'.$theCode->agent_user_id.'" data-semester="'.$semester_id.'" type="button" class="theRuleBtn btn btn-success text-white rounded-full p-0 w-[32px] h-[32px]"><i data-lucide="settings" class="w-4 h-4"></i></button>';
                                    $html .= '</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
            else:
                $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Student not found for the Semester.</div>';
            endif;
        else:
            $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Semester does not started yet.</div>';
        endif;

        return response()->json(['html' => $html], 200);
    }

    public function getRule(Request $request){
        $code = $request->code;
        $agent_user_id = $request->agent_user_id;
        $semester_id = $request->semester_id;

        $rule = AgentComissionRule::where('agent_user_id', $agent_user_id)->where('semester_id', $semester_id)->get()->first();

        return response()->json(['row' => (isset($rule->id) && $rule->id > 0 ? $rule : [])], 200);
    }

    public function storeRules(AgentComissionRuleStoreRequest $request){
        $agent_user_id = $request->agent_user_id;
        $code = $request->code;
        $semester_id = $request->semester_id;
        $comission_mode = $request->comission_mode;

        $existRule = AgentComissionRule::where('agent_user_id', $agent_user_id)->where('semester_id', $semester_id)->get()->first();
        $data = [
            'agent_user_id' => $agent_user_id,
            'semester_id' => $semester_id,
            'code' => $code,
            'comission_mode' => $comission_mode,
            'percentage' => ($comission_mode == 1 && !empty($request->percentage) ? $request->percentage : null),
            'amount' => ($comission_mode == 2 && !empty($request->amount) ? $request->amount : null),
            'period' => (!empty($request->period) ? $request->period : null),
            'payment_type' => (!empty($request->payment_type) ? $request->payment_type : null),
        ];
        if(isset($existRule->id) && $existRule->id > 0):
            $data['updated_by'] = auth()->user()->id;
            AgentComissionRule::where('id', $existRule->id)->update($data);
        else:
            $data['created_by'] = auth()->user()->id;
            AgentComissionRule::create($data);
        endif;

        return response()->json(['msg' => 'Data successfully stored.'], 200);
    }

    public function comission(Semester $semester, AgentUser $agent_user){
        $rule = AgentComissionRule::where('agent_user_id', $agent_user->id)->where('semester_id', $semester->id)->get()->first();
        $theCode = ReferralCode::where('code', $rule->code)->where('agent_user_id', $agent_user->id)->get()->first();
        return view('pages.agent.management.comission', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Management', 'href' => 'javascript:void(0);']
            ],
            'semester' => $semester,
            'agentuser' => $agent_user,
            'rule' => $rule,
            'referral_code' => $theCode
        ]);
    }

    public function comissionList(Request $request){
        $semester_id = (isset($request->semester_id) && $request->semester_id > 0 ? $request->semester_id : 0);
        $agent_user_id = (isset($request->agent_id) && $request->agent_id > 0 ? $request->agent_id : 0);
        $code = (isset($request->code) && !empty($request->code) ? $request->code : '');

        $creation_ids = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $theRule = AgentComissionRule::where('agent_user_id', $agent_user_id)->where('semester_id', $semester_id)->get()->first();
        $period = (isset($theRule->period) && $theRule->period > 0 ? $theRule->period : 2);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::whereHas('activeCR', function($q) use($creation_ids){
                    $q->whereIn('course_creation_id', $creation_ids);
                })->where('referral_code', $code)->where('is_referral_varified', 1);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $std_course_relation_id = (isset($list->activeCR->id) && $list->activeCR->id > 0 ? $list->activeCR->id : 0);
                $installments = SlcInstallment::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id);
                if($period == 2):
                    $installments->whereHas('agreement', function($q){
                        $q->where('year', 1);
                    });
                endif;
                $installments = $installments->get();

                $moneyReceipts = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id);
                if($period == 2):
                    $moneyReceipts->whereHas('agreement', function($q){
                        $q->where('year', 1);
                    });
                endif;
                $moneyReceipts = $moneyReceipts->get();
                $refundReceipts = $moneyReceipts->filter(function ($value, $key) {
                                    return $value['payment_type'] == 'Refund';
                                });
                $courseFeesReceipts = $moneyReceipts->filter(function ($value, $key) {
                                    return $value['payment_type'] == 'Course Fee';
                                });
                $refunds = $refundReceipts->sum('amount');
                $courseFees = $courseFeesReceipts->sum('amount');
                $allReceiptsCount = $moneyReceipts->count();

                $receivedAmount = ($refunds > $courseFees ? '-£'.number_format(($refunds - $courseFees), 2) : '£'.number_format(($courseFees - $refunds), 2));
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => $list->application_no,
                    'registration_no' => $list->registration_no,
                    'ssn_no' => $list->ssn_no,
                    'full_name' => $list->full_name,
                    'date_of_birth' => (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('jS M, Y', strtotime($list->date_of_birth)) : ''),
                    'course' => (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'course_fees' => (isset($list->activeCR->creation->fees) && $list->activeCR->creation->fees > 0 ? '£'.number_format($list->activeCR->creation->fees, 2) : '£0.00'),
                    'status' => (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'claimed_amount' => ($installments->count() > 0 && $installments->sum('amount') > 0 ? '£'.number_format($installments->sum('amount'), 2) : '£0.00'),
                    'claimed_count' => ($installments->count() > 0 ? $installments->count() : '0'),
                    'receipt_amount' => $receivedAmount,
                    'receipt_count' => ($allReceiptsCount > 0 ? $allReceiptsCount : '0'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function exportComissionList(Semester $semester, AgentUser $agent_user, $code){
        $creation_ids = CourseCreation::where('semester_id', $semester->id)->pluck('id')->unique()->toArray();
        $theRule = AgentComissionRule::where('agent_user_id', $agent_user->id)->where('semester_id', $semester->id)->get()->first();
        $period = (isset($theRule->period) && $theRule->period > 0 ? $theRule->period : 2);

        $students = Student::whereHas('activeCR', function($q) use($creation_ids){
                    $q->whereIn('course_creation_id', $creation_ids);
                })->where('referral_code', $code)->where('is_referral_varified', 1)
                ->orderBy('id', 'ASC')->get();

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = 'Application No';
        $theCollection[$row][] = 'Registration No';
        $theCollection[$row][] = 'Name';
        $theCollection[$row][] = 'Date of Birth';
        $theCollection[$row][] = 'SSN';
        $theCollection[$row][] = 'Course';
        $theCollection[$row][] = 'Semester';
        $theCollection[$row][] = 'Status';
        $theCollection[$row][] = 'Course Fee';
        $theCollection[$row][] = 'Claimed';
        $theCollection[$row][] = 'No of Claimed';
        $theCollection[$row][] = 'Received';

        $row = 2;
        if($students->count() > 0):
            foreach($students as $list):
                $std_course_relation_id = (isset($list->activeCR->id) && $list->activeCR->id > 0 ? $list->activeCR->id : 0);
                $installments = SlcInstallment::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id);
                if($period == 2):
                    $installments->whereHas('agreement', function($q){
                        $q->where('year', 1);
                    });
                endif;
                $installments = $installments->get();

                $moneyReceipts = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id);
                if($period == 2):
                    $moneyReceipts->whereHas('agreement', function($q){
                        $q->where('year', 1);
                    });
                endif;
                $moneyReceipts = $moneyReceipts->get();
                $refundReceipts = $moneyReceipts->filter(function ($value, $key) {
                                    return $value['payment_type'] == 'Refund';
                                });
                $courseFeesReceipts = $moneyReceipts->filter(function ($value, $key) {
                                    return $value['payment_type'] == 'Course Fee';
                                });
                $refunds = $refundReceipts->sum('amount');
                $courseFees = $courseFeesReceipts->sum('amount');
                $allReceiptsCount = $moneyReceipts->count();

                $receivedAmount = ($refunds > $courseFees ? '-'.number_format(($refunds - $courseFees), 2, '.', '') : number_format(($courseFees - $refunds), 2, '.', ''));

                $theCollection[$row][] = $list->application_no;
                $theCollection[$row][] = $list->registration_no;
                $theCollection[$row][] = $list->full_name;
                $theCollection[$row][] = (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('Y-m-d', strtotime($list->date_of_birth)) : '');
                $theCollection[$row][] = $list->ssn_no;
                $theCollection[$row][] = (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : '');
                $theCollection[$row][] = (isset($list->activeCR->creation->fees) && $list->activeCR->creation->fees > 0 ? number_format($list->activeCR->creation->fees, 2, '.', '') : '0.00');
                $theCollection[$row][] = ($installments->count() > 0 && $installments->sum('amount') > 0 ? number_format($installments->sum('amount'), 2, '.', '') : '0.00');
                $theCollection[$row][] = ($installments->count() > 0 ? $installments->count() : '0');
                $theCollection[$row][] = $receivedAmount;

                $row += 1;
            endforeach;
        endif;

        $report_title = str_replace(' ', '_', $semester->name).'_'.$code.'.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function payableComissions(Request $request){
        $rule_id = $request->agentcomissionruleid;
        $theRule = AgentComissionRule::find($rule_id);
        $creation_ids = CourseCreation::where('semester_id', $theRule->semester_id)->pluck('id')->unique()->toArray();
        $comission_mode = (isset($theRule->comission_mode) && $theRule->comission_mode > 0 ? $theRule->comission_mode : 2);
        $period = (isset($theRule->period) && $theRule->period > 0 ? $theRule->period : 2);
        $percentage = (isset($theRule->percentage) && $theRule->percentage > 0 ? $theRule->percentage : 0);
        $fixedAmount = (isset($theRule->amount) && $theRule->amount > 0 ? $theRule->amount : 0);
        $code = (isset($request->code) && !empty($request->code) ? $request->code : '');
        $studentids = (isset($request->studentids) && !empty($request->studentids) ? $request->studentids : []);
        $remittanceRef = random_int(100000, 999999);

        $html = '';
        if(!empty($studentids)):
            $students = Student::whereIn('id', $studentids)->get();
            if($students->count() > 0):
                foreach($students as $std):
                    $std_course_relation_id = (isset($std->activeCR->id) && $std->activeCR->id > 0 ? $std->activeCR->id : 0);
                    $moneyReceipts = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                    ->whereIn('payment_type', ['Course Fee', 'Refund']);
                    if($period == 2):
                        $moneyReceipts->whereHas('agreement', function($q){
                            $q->where('year', 1);
                        });
                    endif;
                    $moneyReceipts = $moneyReceipts->get();
                    if($moneyReceipts->count() > 0):
                        foreach($moneyReceipts as $mr):
                            $amount = (isset($mr->amount) && $mr->amount > 0 ? $mr->amount : 0);
                            if($comission_mode == 2):
                                $comission = $fixedAmount;
                            else:
                                $comission = $amount * $percentage / 100;
                            endif;
                            $html .= '<tr>';
                                $html .= '<td>';
                                    $html .= $mr->id;
                                $html .= '</td>';
                                $html .= '<td>'.(isset($mr->payment_date) && !empty($mr->payment_date) ? date('jS M, Y', strtotime($mr->payment_date)) : '').'</td>';
                                $html .= '<td>'.(isset($mr->agreement->year) && $mr->agreement->year > 0 ? $mr->agreement->year : '').'</td>';
                                $html .= '<td>£'.number_format($amount, 2).'</td>';
                                $html .= '<td><input type="number" step="any" value="'.number_format($comission, 2).'" name="comission['.$std->id.']['.$mr->id.'][comission]" class="w-full form-control"/></td>';
                                $html .= '<td><input type="text" value="" name="comission['.$std->id.']['.$mr->id.'][p_date]" class="w-full form-control datepickers"/></td>';
                                $html .= '<td><input type="number" step="any" value="" name="comission['.$std->id.']['.$mr->id.'][p_amount]" class="w-full form-control"/></td>';
                                $html .= '<td><input type="text" readonly value="'.$remittanceRef.'" name="comission['.$std->id.']['.$mr->id.'][remittance_ref]" class="w-full form-control"/></td>';
                            $html .= '</tr>';
                        endforeach;
                    endif;
                endforeach;
            endif;
        endif;

        return response()->json(['html' => $html], 200);
    }
}
