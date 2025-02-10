<?php

namespace App\Http\Controllers\Agent;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentComissionRuleStoreRequest;
use App\Http\Requests\RemittanceLinkedRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AccTransaction;
use App\Models\Agent;
use App\Models\AgentComission;
use App\Models\AgentComissionDetail;
use App\Models\AgentComissionPayment;
use App\Models\AgentComissionRule;
use App\Models\AgentUser;
use App\Models\ComonSmtp;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\ReferralCode;
use App\Models\Semester;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Number;
use App\Traits\GenerateAgentComissionPdfTrait;

class AgentManagementController extends Controller
{
    use GenerateAgentComissionPdfTrait;

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
        $defaultAgentsCodes = Agent::where('is_default', 1)->pluck('code')->unique()->toArray();

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
                        $all_student_ids = [];
                        if(!empty($reff_codes)):
                            foreach($reff_codes as $code):
                                $theCode = ReferralCode::where('code', $code)->get()->first();
                                $student_ids = Student::whereHas('activeCR', function($q) use($creation_ids){
                                                $q->whereIn('course_creation_id', $creation_ids);
                                            })->where('referral_code', $code)->where('is_referral_varified', 1)->pluck('id')->unique()->toArray();
                                $all_student_ids = array_merge($all_student_ids, $student_ids);
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
                                        $html .= '<button data-isdefault="0" data-code="'.$code.'" data-agent="'.$theCode->agent_user_id.'" data-semester="'.$semester_id.'" type="button" class="theRuleBtn btn btn-success text-white rounded-full p-0 w-[32px] h-[32px]"><i data-lucide="settings" class="w-4 h-4"></i></button>';
                                    $html .= '</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                        if(!empty($defaultAgentsCodes)):
                            foreach($defaultAgentsCodes as $code):
                                $theCode = ReferralCode::where('code', $code)->get()->first();
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
                                    $html .= '<td class="w-[150px]">'.(!empty($all_student_ids) && count($all_student_ids) > 0 ? count($all_student_ids) : 0).'</td>';
                                    $html .= '<td class="text-right w-[150px]">';
                                        $html .= '<a href="'.route('agent.management.comission', [$semester_id, $theCode->agent_user_id]).'" id="comission_view_'.$semester_id.'_'.$theCode->agent_user_id.'" class="'.(isset($rules->id) && $rules->id > 0 ? '' : 'hidden').' mr-2 btn btn-linkedin text-white rounded-full p-0 w-[32px] h-[32px]"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                                        $html .= '<button data-isdefault="1" data-code="'.$code.'" data-agent="'.$theCode->agent_user_id.'" data-semester="'.$semester_id.'" type="button" class="theRuleBtn btn btn-success text-white rounded-full p-0 w-[32px] h-[32px]"><i data-lucide="settings" class="w-4 h-4"></i></button>';
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
                ['label' => 'Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Comission', 'href' => 'javascript:void(0);'],
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

        $agent = Agent::where('agent_user_id', $agent_user_id)->orderBy('id', 'DESC')->get()->first();
        $is_default = (isset($agent->is_default) && $agent->is_default == 1 ? true : false);
        $creation_ids = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $theRule = AgentComissionRule::where('agent_user_id', $agent_user_id)->where('semester_id', $semester_id)->get()->first();
        $comission_mode = (isset($theRule->comission_mode) && $theRule->comission_mode > 0 ? $theRule->comission_mode : 2);
        $period = (isset($theRule->period) && $theRule->period > 0 ? $theRule->period : 2);

        //Fixed -2         Amount	        Year1 -2 	        single payment
        //Fixed -2	       Amount	        Every Year -1	    single payment
                      
        //Percentage -1	   Percentage	    Year1 -2	        on receipt
        //Percentage -1	   Percentage	    Every Year -1	    on receipt

        $student_ids = [];
        if($is_default):
            $student_ids = Student::whereHas('activeCR', function($q) use($creation_ids){
                                $q->whereIn('course_creation_id', $creation_ids);
                            })->where(function($q){
                                $q->whereNotNull('referral_code')->orWhere('referral_code', '!=', '');
                            })->where('is_referral_varified', 1)->pluck('id')->unique()->toArray();
        endif;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::whereHas('activeCR', function($q) use($creation_ids){
                    $q->whereIn('course_creation_id', $creation_ids);
                });
        if($is_default):
            $query->whereIn('id', (!empty($student_ids) ? $student_ids : ['0000']));
        else:
            $query->where('referral_code', $code)->where('is_referral_varified', 1);
        endif;

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
                $years = SlcAgreement::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)->whereNotNull('year')->orderBy('year', 'ASC')->pluck('year')->unique()->toArray();
                $instCount = 0;
                $instTotal = 0;
                $receiptCount = 0;
                $receiptTotal = 0;
                $refundCount = 0;
                $refundTotal = 0;
                if($comission_mode == 2):
                    if(!empty($years)):
                        foreach($years as $year):
                            if($period == 2 && $year > 1): break; endif;
                            $installment = SlcInstallment::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->whereHas('agreement', function($q) use($year){
                                                $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get()->first();
                            if(isset($installment->id) && $installment->id > 0):
                                $instCount += 1;
                                $instTotal += $installment->amount;
                            endif;

                            $moneyReceipt = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                    $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get()->first();
                            if(isset($moneyReceipt->id) && $moneyReceipt->id > 0):
                                $receiptCount += 1;
                                $receiptTotal += $moneyReceipt->amount;
                            endif;

                            $refundReceipt = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->where('payment_type', 'Refund')->whereHas('agreement', function($q) use($year){
                                                    $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get()->first();
                            if(isset($refundReceipt->id) && $refundReceipt->id > 0):
                                $refundCount += 1;
                                $refundTotal += $refundReceipt->amount;
                            endif;
                        endforeach;
                    endif;
                elseif($comission_mode == 1):
                    if(!empty($years)):
                        foreach($years as $year):
                            if($period == 2 && $year > 1): break; endif;
                            $installments = SlcInstallment::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->whereHas('agreement', function($q) use($year){
                                                $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get();
                            $instCount += ($installments->count() > 0 ? $installments->count() : 0);
                            $instTotal += ($installments->count() > 0 ? $installments->sum('amount') : 0);

                            $moneyReceipts = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                    $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get();
                            if($moneyReceipts->count() > 0):
                                $receiptCount += $moneyReceipts->count();
                                $receiptTotal += $moneyReceipts->sum('amount');
                            endif;

                            $refundReceipts = SlcMoneyReceipt::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id)
                                            ->where('payment_type', 'Refund')->whereHas('agreement', function($q) use($year){
                                                    $q->where('year', $year);
                                            })->orderBy('id', 'ASC')->get();
                            if(isset($refundReceipts->id) && $refundReceipts->id > 0):
                                $refundCount += $refundReceipts->count();
                                $refundTotal += $refundReceipts->amount;
                            endif;
                        endforeach;
                    endif;
                endif;

                /*$installments = SlcInstallment::where('student_id', $list->id)->where('student_course_relation_id', $std_course_relation_id);
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

                $receivedAmount = ($refunds > $courseFees ? '-£'.number_format(($refunds - $courseFees), 2) : '£'.number_format(($courseFees - $refunds), 2));*/

                $receivedAmount = ($refundTotal > $receiptTotal ? ($refundTotal - $receiptTotal) : ($receiptTotal - $refundTotal));
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => $list->application_no,
                    'registration_no' => $list->registration_no,
                    'ssn_no' => $list->ssn_no,
                    'full_name' => $list->full_name,
                    'date_of_birth' => (isset($list->date_of_birth) && !empty($list->date_of_birth) ? date('jS M, Y', strtotime($list->date_of_birth)) : ''),
                    'course' => (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'course_fees' => (isset($list->activeCR->creation->fees) && $list->activeCR->creation->fees > 0 ? Number::currency($list->activeCR->creation->fees, in: 'GBP') : '£0.00'),
                    'status' => (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    //'claimed_amount' => ($installments->count() > 0 && $installments->sum('amount') > 0 ? Number::currency($installments->sum('amount'), in: 'GBP') : '£0.00'),
                    //'claimed_count' => ($installments->count() > 0 ? $installments->count() : '0'),
                    //'receipt_amount' => $receivedAmount,
                    //'receipt_count' => ($allReceiptsCount > 0 ? $allReceiptsCount : '0'),
                    'deleted_at' => $list->deleted_at,

                    'claimed_amount' => ($instTotal > 0 ? Number::currency($instTotal, in: 'GBP') : '£0.00'),
                    'claimed_count' => ($instCount > 0 ? $instCount : '0'),

                    'receipt_amount' => Number::currency($receivedAmount, in: 'GBP'),
                    'receipt_count' => ($receiptCount + $refundCount),
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

    public function payableComissions_BACKUP(Request $request){
        $rule_id = $request->agentcomissionruleid;
        $theRule = AgentComissionRule::find($rule_id);
        $creation_ids = CourseCreation::where('semester_id', $theRule->semester_id)->pluck('id')->unique()->toArray();
        $comission_mode = (isset($theRule->comission_mode) && $theRule->comission_mode > 0 ? $theRule->comission_mode : 2);
        $period = (isset($theRule->period) && $theRule->period > 0 ? $theRule->period : 2);
        $percentage = (isset($theRule->percentage) && $theRule->percentage > 0 ? $theRule->percentage : 0);
        $fixedAmount = (isset($theRule->amount) && $theRule->amount > 0 ? $theRule->amount : 0);
        $code = (isset($request->code) && !empty($request->code) ? $request->code : '');
        $studentids = (isset($request->studentids) && !empty($request->studentids) ? $request->studentids : []);

        $existComission = AgentComission::where('agent_user_id', $theRule->agent_user_id)->where('agent_comission_rule_id', $rule_id)->where('semester_id', $theRule->semester_id)->orderBy('id', 'DESC')->get()->first();
        if(isset($existComission->id) && $existComission->id > 0):
            $remittanceRef = $existComission->remittance_ref;
            $agent_comission_id = $existComission->id;
        else:
            $remittanceRef = random_int(100000, 999999);
            $agent_comission = AgentComission::create([
                'agent_id' => (isset($theRule->agentuser->agent->id) && $theRule->agentuser->agent->id > 0 ? $theRule->agentuser->agent->id : null),
                'agent_user_id' => (isset($theRule->agent_user_id) && $theRule->agent_user_id > 0 ? $theRule->agent_user_id : null),
                'agent_comission_rule_id' => $theRule->id,
                'semester_id' => $theRule->semester_id,
                'remittance_ref' => $remittanceRef,
                'entry_date' => date('Y-m-d'),
                'status' => 1,
                'created_by' => auth()->user()->id
            ]);
            $agent_comission_id = $agent_comission->id;
        endif;
        //Fixed -2         Amount	        Year1 -2 	        single payment
        //Fixed -2	       Amount	        Every Year -1	    single payment
                      
        //Percentage -1	   Percentage	    Year1 -2	        on receipt
        //Percentage -1	   Percentage	    Every Year -1	    on receipt

        if($agent_comission_id):
            $students = Student::whereIn('id', $studentids)->get();
            if($students->count() > 0):
                foreach($students as $std):
                    $std_course_relation_id = (isset($std->activeCR->id) && $std->activeCR->id > 0 ? $std->activeCR->id : 0);
                    $years = SlcAgreement::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)->whereNotNull('year')->orderBy('year', 'ASC')->pluck('year')->unique()->toArray();
                    if($comission_mode == 2): // Fided Comission
                        if(!empty($years)):
                            foreach($years as $year):
                                if($period == 2 && $year > 1): break; endif;
                                $moneyReceipts = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get()->first();
                                if(isset($moneyReceipts->id) && $moneyReceipts->id > 0):
                                    if($year == 1):
                                        /* BEGIN: Year 1 Comission */
                                        $comissionExist = AgentComissionDetail::where('student_id', $std->id)->where('agent_comission_id', $agent_comission_id)
                                                            ->where('slc_money_receipt_id', $moneyReceipts->id)->orderBy('id', 'desc')->get()->first();
                                        if(!isset($comissionExist->id)):
                                            $comission_details = AgentComissionDetail::create([
                                                'student_id' => $std->id,
                                                'agent_comission_id' => $agent_comission_id,
                                                'slc_money_receipt_id' => $moneyReceipts->id,
                                                'amount' => $fixedAmount,
                                                'status' => 1,
                                                'created_by' => auth()->user()->id,
                                            ]);
                                        endif;
                                        /* END: Year 1 Comission */
                                    endif;
                                    if($period == 1 && $year > 1):
                                        /* BEGIN: Year > 1 Comission */
                                        $comissionExist = AgentComissionDetail::where('student_id', $std->id)->where('agent_comission_id', $agent_comission_id)
                                                            ->where('slc_money_receipt_id', $moneyReceipts->id)->orderBy('id', 'desc')->get()->first();
                                        if(!isset($comissionExist->id)):
                                            $comission_details = AgentComissionDetail::create([
                                                'student_id' => $std->id,
                                                'agent_comission_id' => $agent_comission_id,
                                                'slc_money_receipt_id' => $moneyReceipts->id,
                                                'amount' => $fixedAmount,
                                                'status' => 1,
                                                'created_by' => auth()->user()->id,
                                            ]);
                                        endif;
                                        /* END: Year > 1 Comission */
                                    endif;
                                endif;
                            endforeach;
                        endif;
                    elseif($comission_mode == 1): // Percentage Comission
                        if(!empty($years)):
                            foreach($years as $year):
                                if($period == 2 && $year > 1): break; endif;
                                $moneyReceipts = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get();
                                if($moneyReceipts->count() > 0):
                                    if($year == 1):
                                        /* BEGIN: Year 1 Comission */
                                        foreach($moneyReceipts as $recipt):
                                            $comissionExist = AgentComissionDetail::where('student_id', $std->id)->where('agent_comission_id', $agent_comission_id)
                                                                ->where('slc_money_receipt_id', $recipt->id)->orderBy('id', 'desc')->get()->first();
                                            if(!isset($comissionExist->id)):
                                                $comissionAmount = (isset($recipt->amount) && $recipt->amount > 0 && $percentage > 0 ? ($recipt->amount * $percentage) / 100 : 0);
                                                $comission_details = AgentComissionDetail::create([
                                                    'student_id' => $std->id,
                                                    'agent_comission_id' => $agent_comission_id,
                                                    'slc_money_receipt_id' => $recipt->id,
                                                    'amount' => $comissionAmount,
                                                    'status' => 1,
                                                    'created_by' => auth()->user()->id,
                                                ]);
                                            endif;
                                        endforeach;
                                        /* END: Year 1 Comission */
                                    endif;
                                    if($period == 1 && $year > 1):
                                        /* BEGIN: Year > 1 Comission */
                                        foreach($moneyReceipts as $recipt):
                                            $comissionExist = AgentComissionDetail::where('student_id', $std->id)->where('agent_comission_id', $agent_comission_id)
                                                                ->where('slc_money_receipt_id', $recipt->id)->orderBy('id', 'desc')->get()->first();
                                            if(!isset($comissionExist->id)):
                                                $comissionAmount = (isset($recipt->amount) && $recipt->amount > 0 && $percentage > 0 ? ($recipt->amount * $percentage) / 100 : 0);
                                                $comission_details = AgentComissionDetail::create([
                                                    'student_id' => $std->id,
                                                    'agent_comission_id' => $agent_comission_id,
                                                    'slc_money_receipt_id' => $recipt->id,
                                                    'amount' => $fixedAmount,
                                                    'status' => 1,
                                                    'created_by' => auth()->user()->id,
                                                ]);
                                            endif;
                                        endforeach;
                                        /* END: Year > 1 Comission */
                                    endif;
                                endif;
                            endforeach;
                        endif;
                    endif;
                endforeach;
            endif;
        endif;

        return response()->json(['url' => route('agent.management.comission.details', $agent_comission_id) ], 200);
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

        /*$existComission = AgentComission::where('agent_user_id', $theRule->agent_user_id)->where('agent_comission_rule_id', $rule_id)->where('semester_id', $theRule->semester_id)->orderBy('id', 'DESC')->get()->first();
        if(isset($existComission->id) && $existComission->id > 0):
            $remittanceRef = $existComission->remittance_ref;
            $agent_comission_id = $existComission->id;
        else:*/
        $remittanceRef = random_int(100000, 999999);
        $agent_comission = AgentComission::create([
            'agent_id' => (isset($theRule->agentuser->agent->id) && $theRule->agentuser->agent->id > 0 ? $theRule->agentuser->agent->id : null),
            'agent_user_id' => (isset($theRule->agent_user_id) && $theRule->agent_user_id > 0 ? $theRule->agent_user_id : null),
            'agent_comission_rule_id' => $theRule->id,
            'semester_id' => $theRule->semester_id,
            'remittance_ref' => $remittanceRef,
            'entry_date' => date('Y-m-d'),
            'status' => 1,
            'created_by' => auth()->user()->id
        ]);
        $agent_comission_id = $agent_comission->id;
        //endif;
        //Fixed -2         Amount	        Year1 -2 	        single payment
        //Fixed -2	       Amount	        Every Year -1	    single payment
                      
        //Percentage -1	   Percentage	    Year1 -2	        on receipt
        //Percentage -1	   Percentage	    Every Year -1	    on receipt

        if($agent_comission_id):
            $entryCount = 0;
            $students = Student::whereIn('id', $studentids)->get();
            if($students->count() > 0):
                foreach($students as $std):
                    $std_course_relation_id = (isset($std->activeCR->id) && $std->activeCR->id > 0 ? $std->activeCR->id : 0);
                    $years = SlcAgreement::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)->whereNotNull('year')->orderBy('year', 'ASC')->pluck('year')->unique()->toArray();
                    if($comission_mode == 2): // Fided Comission
                        if(!empty($years)):
                            foreach($years as $year):
                                if($period == 2 && $year > 1): break; endif;
                                $moneyReceipts = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get()->first();
                                if(isset($moneyReceipts->id) && $moneyReceipts->id > 0):
                                    /* BEGIN: Year's Comission */
                                    $comissionExist = AgentComissionDetail::where('student_id', $std->id)//->where('agent_comission_id', $agent_comission_id)
                                                      ->where('slc_money_receipt_id', $moneyReceipts->id)->where('comission_for', 'Course Fee')
                                                      ->whereHas('comission', function($q) use($rule_id){
                                                            $q->where('agent_comission_rule_id', $rule_id);
                                                      })->orderBy('id', 'desc')->get()->first();
                                    if(!isset($comissionExist->id)):
                                        $comission_details = AgentComissionDetail::create([
                                            'student_id' => $std->id,
                                            'agent_comission_id' => $agent_comission_id,
                                            'slc_money_receipt_id' => $moneyReceipts->id,
                                            'comission_for' => 'Course Fee',
                                            'amount' => $fixedAmount,
                                            'status' => 1,
                                            'created_by' => auth()->user()->id,
                                        ]);
                                        $entryCount += 1;
                                    endif;
                                    /* END: Year's Comission */
                                endif;
                                $moneyRefunds = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Refund')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get()->first();
                                if(isset($moneyRefunds->id) && $moneyRefunds->id > 0):
                                    $comissionExist = AgentComissionDetail::where('student_id', $std->id)//->where('agent_comission_id', $agent_comission_id)
                                                      ->where('slc_money_receipt_id', $moneyRefunds->id)->where('comission_for', 'Refund')
                                                      ->whereHas('comission', function($q) use($rule_id){
                                                            $q->where('agent_comission_rule_id', $rule_id);
                                                      })->orderBy('id', 'desc')->get()->first();
                                    if(!isset($comissionExist->id)):
                                        $comission_details = AgentComissionDetail::create([
                                            'student_id' => $std->id,
                                            'agent_comission_id' => $agent_comission_id,
                                            'slc_money_receipt_id' => $moneyRefunds->id,
                                            'comission_for' => 'Refund',
                                            'amount' => abs($fixedAmount) * -1,
                                            'status' => 1,
                                            'created_by' => auth()->user()->id,
                                        ]);
                                        $entryCount += 1;
                                    endif;
                                endif;
                            endforeach;
                        endif;
                    elseif($comission_mode == 1): // Percentage Comission
                        if(!empty($years)):
                            foreach($years as $year):
                                if($period == 2 && $year > 1): break; endif;
                                $moneyReceipts = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Course Fee')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get();
                                if($moneyReceipts->count() > 0):
                                    /* BEGIN: Year's Comission */
                                    foreach($moneyReceipts as $recipt):
                                        $comissionExist = AgentComissionDetail::where('student_id', $std->id)//->where('agent_comission_id', $agent_comission_id)
                                                            ->where('slc_money_receipt_id', $recipt->id)->where('comission_for', 'Course Fee')
                                                            ->whereHas('comission', function($q) use($rule_id){
                                                                  $q->where('agent_comission_rule_id', $rule_id);
                                                            })->orderBy('id', 'desc')->get()->first();
                                        if(!isset($comissionExist->id)):
                                            $comissionAmount = (isset($recipt->amount) && $recipt->amount > 0 && $percentage > 0 ? ($recipt->amount * $percentage) / 100 : 0);
                                            $comission_details = AgentComissionDetail::create([
                                                'student_id' => $std->id,
                                                'agent_comission_id' => $agent_comission_id,
                                                'slc_money_receipt_id' => $recipt->id,
                                                'amount' => $comissionAmount,
                                                'status' => 1,
                                                'created_by' => auth()->user()->id,
                                            ]);
                                            $entryCount += 1;
                                        endif;
                                    endforeach;
                                    /* END: Year's Comission */
                                endif;
                                $moneyRefunds = SlcMoneyReceipt::where('student_id', $std->id)->where('student_course_relation_id', $std_course_relation_id)
                                                    ->where('payment_type', 'Refund')->whereHas('agreement', function($q) use($year){
                                                            $q->where('year', $year);
                                                    })->orderBy('id', 'ASC')->get();
                                if($moneyRefunds->count() > 0):
                                    /* BEGIN: Year's Comission */
                                    foreach($moneyRefunds as $recipt):
                                        $comissionExist = AgentComissionDetail::where('student_id', $std->id)//->where('agent_comission_id', $agent_comission_id)
                                                            ->where('slc_money_receipt_id', $recipt->id)->where('comission_for', 'Refund')
                                                            ->whereHas('comission', function($q) use($rule_id){
                                                                  $q->where('agent_comission_rule_id', $rule_id);
                                                            })->orderBy('id', 'desc')->get()->first();
                                        if(!isset($comissionExist->id)):
                                            $comissionAmount = (isset($recipt->amount) && $recipt->amount > 0 && $percentage > 0 ? ($recipt->amount * $percentage) / 100 : 0);
                                            $comission_details = AgentComissionDetail::create([
                                                'student_id' => $std->id,
                                                'agent_comission_id' => $agent_comission_id,
                                                'slc_money_receipt_id' => $recipt->id,
                                                'amount' => abs($comissionAmount) * -1,
                                                'status' => 1,
                                                'created_by' => auth()->user()->id,
                                            ]);
                                            $entryCount += 1;
                                        endif;
                                    endforeach;
                                    /* END: Year's Comission */
                                endif;
                            endforeach;
                        endif;
                    endif;
                endforeach;
            endif;
            if($entryCount > 0):
                return response()->json(['url' => route('agent.management.comission.details', $agent_comission_id) ], 200);
            else:
                AgentComission::where('id', $agent_comission_id)->forceDelete();
                return response()->json(['msg' => 'New money receipt not for the selected students. Please generate comission once you have some new Money receipt.'], 422);
            endif;
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 422);
        endif;
    }

    public function comissionDetails(AgentComission $comission){
        $comission->load(['agent', 'agentuser', 'rule', 'semester']);
        return view('pages.agent.management.comission-details', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Comission Details', 'href' => 'javascript:void(0);'],
            ],
            'comission' => $comission
        ]);
    }

    public function comissionDetailsList(Request $request){
        $comission_id = (isset($request->comission_id) && $request->comission_id > 0 ? $request->comission_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentComissionDetail::with('student', 'receipt')->where('agent_comission_id', $comission_id);

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
                $comissionFor = (isset($list->comission_for) && !empty($list->comission_for) ? $list->comission_for : 'Course Fee');
                $receiptAmount = (isset($list->receipt->amount) && $list->receipt->amount > 0 ? ($comissionFor == 'Refund' ? abs($list->receipt->amount) * -1 : $list->receipt->amount) : 0);
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'application_no' => (isset($list->student->application_no) ? $list->student->application_no : ''),
                    'registration_no' => (isset($list->student->registration_no) ? $list->student->registration_no : ''),
                    'full_name' => (isset($list->student->full_name) ? $list->student->full_name : $list->student->full_name),
                    'course' => (isset($list->student->activeCR->creation->course->name) && !empty($list->student->activeCR->creation->course->name) ? $list->student->activeCR->creation->course->name : ''),
                    'amount' => Number::currency($list->amount, in: 'GBP'),//(isset($list->amount) && $list->amount ? '£'.number_format($list->amount, 2) : '£0.00'),
                    'invoice_no' => (isset($list->receipt->invoice_no) && !empty($list->receipt->invoice_no) ? $list->receipt->invoice_no : ''),
                    'receipt_amount' => Number::currency($receiptAmount, in: 'GBP'),
                    'payment_date' => (isset($list->receipt->payment_date) && !empty($list->receipt->payment_date) ? date('jS M, Y', strtotime($list->receipt->payment_date)) : ''),
                    'comission_for' => (isset($list->comission_for) && !empty($list->comission_for) ? $list->comission_for : 'Course Fee'),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function remittance(){
        return view('pages.agent.management.remittance', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Remittance', 'href' => 'javascript:void(0);'],
            ]
        ]);
    }

    public function remittanceList(Request $request){
        $querystr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentComission::with('comissions');
        if(!empty($querystr)):
            $query->where(function($q) use($querystr){
                $q->where('remittance_ref','LIKE','%'.$querystr.'%');
            });
        endif;
        if($status > 0):
            $query->whereHas('payment', function($q) use($status){
                $q->where('status', $status);
            });
        endif;

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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'agent_id' => (isset($list->agent_id) && $list->agent_id > 0 ? $list->agent_id : 0),
                    'semester' => (isset($list->rule->semester->name) && !empty($list->rule->semester->name) ? $list->rule->semester->name : ''),
                    'remittance_ref' => $list->remittance_ref,
                    'entry_date' => (!empty($list->entry_date) ? date('jS M, Y', strtotime($list->entry_date)) : ''),
                    'agent_name' => (isset($list->agent->full_name) ? $list->agent->full_name : ''),
                    'organization' => (isset($list->agent->organization) ? $list->agent->organization : ''),
                    'amount_html' => (isset($list->comissions) && $list->comissions->count() > 0 ? Number::currency($list->comissions->sum('amount'), in: 'GBP') : '£0.00'),
                    'amount' => (isset($list->comissions) && $list->comissions->count() > 0 ? $list->comissions->sum('amount') : 0),
                    'status' => $list->status,
                    'payment_status' => (isset($list->payment->status) && $list->payment->status > 0 ? $list->payment->status : 0),
                    'payment_date' => (isset($list->payment->date) && !empty($list->payment->date) ? date('jS M, Y', strtotime($list->payment->date)) : ''),
                    'url' => route('agent.management.comission.details', $list->id),

                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function exportRemittance($comission_id){
        $comission = AgentComission::find($comission_id);
        $comission_details = AgentComissionDetail::with('student')->where('agent_comission_id', $comission_id)->get();
        $remittanceRef = (isset($comission->remittance_ref) && !empty($comission->remittance_ref) ? $comission->remittance_ref : '');

        $theCollection = [];
        $row = 1;
        $theCollection[$row][] = 'Intake Semester';
        $theCollection[$row][] = (isset($comission->semester->name) && !empty($comission->semester->name) ? $comission->semester->name : '');
        $theCollection[$row][] = 'Remittance Ref';
        $theCollection[$row][] = (isset($comission->remittance_ref) && !empty($comission->remittance_ref) ? $comission->remittance_ref : '');
        $theCollection[$row][] = 'Generate Date';
        $theCollection[$row][] = (isset($comission->entry_date) && !empty($comission->entry_date) ? date('jS F, Y', strtotime($comission->entry_date)) : '');
        $theCollection[$row][] = 'Remittance Total';
        $theCollection[$row][] = Number::currency($comission->comissions->sum('amount'), in: 'GBP'); //(isset($comission->comissions) && $comission->comissions->count() > 0 ?  : '£0.00');
        $row += 1;

        $theCollection[$row][] = 'Agent Name';
        $theCollection[$row][] = (isset($comission->agent->full_name) && !empty($comission->agent->full_name) ? $comission->agent->full_name : '');
        $theCollection[$row][] = 'Agent Organization';
        $theCollection[$row][] = (isset($comission->agent->organization) && !empty($comission->agent->organization) ? ' ('.$comission->agent->organization.')' : '');
        $theCollection[$row][] = 'Agent Email';
        $theCollection[$row][] = (isset($comission->agent->email) && !empty($comission->agent->email) ? $comission->agent->email : '');
        $row += 1;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;

        $theCollection[$row][] = 'Student Ref';
        $theCollection[$row][] = 'LCC ID';
        $theCollection[$row][] = 'Name';
        $theCollection[$row][] = 'Course';
        $theCollection[$row][] = 'Comission Amount';
        $theCollection[$row][] = 'Money Receipt Ref.';
        $theCollection[$row][] = 'Receipt Amount';
        $theCollection[$row][] = 'Receipt Date';
        $row += 1;

        if($comission_details->count() > 0):
            foreach($comission_details as $list):
                $comissionFor = (isset($list->comission_for) && !empty($list->comission_for) ? $list->comission_for : 'Course Fee');
                $receiptAmount = (isset($list->receipt->amount) && $list->receipt->amount > 0 ? ($comissionFor == 'Refund' ? abs($list->receipt->amount) * -1 : $list->receipt->amount) : 0);

                $theCollection[$row][] = (isset($list->student->application_no) ? $list->student->application_no : '');
                $theCollection[$row][] = (isset($list->student->registration_no) ? $list->student->registration_no : '');
                $theCollection[$row][] = (isset($list->student->full_name) ? $list->student->full_name : $list->student->full_name);
                $theCollection[$row][] = (isset($list->student->activeCR->creation->course->name) && !empty($list->student->activeCR->creation->course->name) ? $list->student->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($list->amount) && !empty($list->amount) ? $list->amount : '0.00');
                $theCollection[$row][] = (isset($list->receipt->invoice_no) && !empty($list->receipt->invoice_no) ? $list->receipt->invoice_no : '');
                $theCollection[$row][] = $receiptAmount;
                $theCollection[$row][] = (isset($list->receipt->payment_date) && !empty($list->receipt->payment_date) ? date('jS M, Y', strtotime($list->receipt->payment_date)) : '');

                $row += 1;
            endforeach;
        endif;

        $file_name = 'Agent_Remittance'.(!empty($remittanceRef) ? '_('.$remittanceRef.')' : '').'_Details.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $file_name);
    }

    public function printRemittance($comission_id){
        $comission = AgentComission::find($comission_id);
        $comission_details = AgentComissionDetail::with('student')->where('agent_comission_id', $comission_id)->get();
        $remittanceRef = (isset($comission->remittance_ref) && !empty($comission->remittance_ref) ? $comission->remittance_ref : '');

        $user = User::find(auth()->user()->id);
        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Agent Remittance ('.$remittanceRef.') Report';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                header{position: fixed;left: 0px;right: 0px; height: 80px; margin-top: -90px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.headerRightCol{ width: 200px;}
                                .headerTable tr td{vertical-align: top; padding: 0;}
                                .headerTable tr td.headerRightCol{font-size: 12px; line-height: 14px;}
                                .headerTable tr td.headerRightCol table tr.headerHeadingRow td{font-size: 16px; text-transform: uppercase; font-weight: bold; padding-bottom: 2px;}
                                .headerTable tr td.headerRightCol table tr.headerBodyRow td{padding-top: 3px;}
                                .headerTable tr td.headerRightCol table tr td.htd2{ text-align: right;}

                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.attenRateReportTable tr th{ background: #0d9488; color: #FFF; font-size: 12px; text-transform: uppercase; font-weight: bold; padding-top: 10px; padding-bottom: 10px;}
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
                                .table.attenRateReportTable tr th.amountHeading, .table.attenRateReportTable tr td.amountColumn{width: 90px; text-align: center;}
                                .attenRateReportTable.table {border-collapse: separate;}
                                .attenRateReportTable.table tr th, .attenRateReportTable.table tr td{border-spacing: 3px;}
                                .attenRateReportTable.table tr:nth-child(even) td{background: rgba(241, 245, 249, .9);}
                                .table.attenRateReportTable tfoot tr th.amountHeading{font-size: 14px;}
                                .table.attenRateReportTable thead tr td.serialHeading, .table.attenRateReportTable tbody tr td.serialColumn{width: 30px;}

                                .invInfoTable{margin-top: 30px; margin-bottom: 50px;}
                                .invToTable{width: 300px;}
                                .invToTable.payInfoTable{margin-top: 30px;}
                                .invInfoTableRight{width: 200px; vertical-align: top;}
                                .invInfoTableRight .invToTable{width: 100%;}
                                .invToLabel{ font-size: 12px; font-weight: bold; text-transform: uppercase; color: #0d9488; line-height: 1; padding-bottom: 10px;}
                                .invToName{ font-size: 18px; font-weight: bold; text-transform: capitalize; line-height: 1; padding-bottom: 2px;}
                                .invToOrg{ font-size: 13px; line-height: 1; padding-bottom: 7px;}
                                .invInfoRow td{ vertical-align: top; font-size: 13px; line-height: 16px; padding-bottom: 5px;}
                                .invInfoRow td:first-child{ width: 80px;}
                                .invInfoRow td.addressCol{ width: 220px; line-height: 16px;}
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td class="text-left"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                            $PDFHTML .= '<td class="headerRightCol">';
                                
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="invInfoTable">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="invInfoTableLeft">';
                            $PDFHTML .= '<table class="invToTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Remit To</td></tr>';
                                $PDFHTML .= '<tr><td colspan="2" class="invToName">'.(isset($comission->agent->organization) && !empty($comission->agent->organization) ? $comission->agent->organization : '').'</td></tr>';
                                //$PDFHTML .= '<tr><td colspan="2" class="invToOrg">'.(isset($comission->agent->organization) && !empty($comission->agent->organization) ? $comission->agent->organization : '').'</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td colspan="2">'.(isset($comission->agent->email) && !empty($comission->agent->email) ? $comission->agent->email : '').'</td>';
                                $PDFHTML .= '</tr>';
                                if(isset($comission->agent->address->full_address_pdf) && !empty($comission->agent->address->full_address_pdf)):
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Address</td>';
                                    $PDFHTML .= '<td class="addressCol">'.$comission->agent->address->full_address_pdf.'</td>';
                                $PDFHTML .= '</tr>';
                                endif;
                            $PDFHTML .= '</table>';

                            $PDFHTML .= '<table class="invToTable payInfoTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Payment Information</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Sort Code</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->sort_code) && !empty($comission->agent->bank->sort_code) ? $comission->agent->bank->sort_code : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Account No</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->ac_no) && !empty($comission->agent->bank->ac_no) ? $comission->agent->bank->ac_no : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Beneficiary</td>';
                                    $PDFHTML .= '<td class="text-left">'.(isset($comission->agent->bank->beneficiary) && !empty($comission->agent->bank->beneficiary) ? $comission->agent->bank->beneficiary : '').'</td>';
                                $PDFHTML .= '</tr>';
                            $PDFHTML .= '</table>';

                        $PDFHTML .= '</td>';
                        $PDFHTML .= '<td class="invInfoTableRight text-right" style="vertical-align: top;">';
                            $PDFHTML .= '<table class="invToTable">';
                                $PDFHTML .= '<tr><td colspan="2" class="invToLabel">Remit Report</td></tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Reference</td>';
                                    $PDFHTML .= '<td class="text-right">'.(!empty($remittanceRef) ? '#'.$remittanceRef : '---').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Date</td>';
                                    $PDFHTML .= '<td class="text-right">'.(isset($comission->entry_date) && !empty($comission->entry_date) ? date('jS M, Y', strtotime($comission->entry_date)) : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>Semester</td>';
                                    $PDFHTML .= '<td class="text-right">'.(isset($comission->semester->name) && !empty($comission->semester->name) ? $comission->semester->name : '').'</td>';
                                $PDFHTML .= '</tr>';
                                $PDFHTML .= '<tr class="invInfoRow">';
                                    $PDFHTML .= '<td>No of Student</td>';
                                    $PDFHTML .= '<td class="text-right">'.(!empty($comission_details) && $comission_details->count() > 0 ? $comission_details->count() : 0).'</td>';
                                $PDFHTML .= '</tr>';
                            $PDFHTML .= '</table>';

                            
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';

                

                $PDFHTML .= '<table class="table attenRateReportTable table-sm" id="continuationListTable">';
                    $PDFHTML .= '<thead>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th class="serialHeading">SL</th>';
                            $PDFHTML .= '<th>Reference</th>';
                            $PDFHTML .= '<th>Name</th>';
                            $PDFHTML .= '<th class="amountHeading">Amount</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</thead>';
                    $PDFHTML .= '<tbody>';
                    if($comission_details->count() > 0):
                        $sl = 1;
                        foreach($comission_details as $list):
                            $comissionFor = (isset($list->comission_for) && !empty($list->comission_for) ? $list->comission_for : 'Course Fee');
                            $receiptAmount = (isset($list->receipt->amount) && $list->receipt->amount > 0 ? ($comissionFor == 'Refund' ? abs($list->receipt->amount) * -1 : $list->receipt->amount) : 0);
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="serialColumn">'.$sl.'</td>';
                                $PDFHTML .= '<td style="font-weight: bold; font-size: 12px;">';
                                    $PDFHTML .= (isset($list->student->application_no) ? $list->student->application_no : '');
                                    $PDFHTML .= (isset($list->student->registration_no) ? '<br/><span style="display: block; padding-top: 3px; font-weight: normal; font-size: 11px; color: #64748b;">'.$list->student->registration_no.'</span>' : '');
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td>';
                                    $PDFHTML .= (isset($list->student->full_name) ? $list->student->full_name : $list->student->full_name);
                                    $PDFHTML .= (isset($list->student->activeCR->creation->course->name) && !empty($list->student->activeCR->creation->course->name) ? '<span style="display: block; padding-top: 3px; font-weight: normal; font-size: 11px; color: #64748b;">'.$list->student->activeCR->creation->course->name.'</span>' : '');
                                $PDFHTML .= '</td>';
                                $PDFHTML .= '<td class="amountColumn" style="'.($list->amount < 0 ? 'color: red;' : '').'">'.Number::currency($list->amount, in: 'GBP').'</td>';
                            $PDFHTML .= '</tr>';
                            $sl++;
                        endforeach;
                    endif;
                    $PDFHTML .= '</tbody>';
                    $PDFHTML .= '<tfoot>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<th colspan="3">Total</th>';
                            $PDFHTML .= '<th class="amountHeading">'.Number::currency($comission->comissions->sum('amount'), in: 'GBP').'</th>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</tfoot>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')//landscape portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function getRemittancesDetail(Request $request){
        $agent_comission_ids = (isset($request->agent_comission_ids) && !empty($request->agent_comission_ids) ? $request->agent_comission_ids : []);
        
        $lastRow = AgentComissionPayment::orderBy('id', 'DESC')->get()->first();
        $reference = (isset($lastRow->reference)) ? str_replace('P', '', $lastRow->reference) : '00000';
        $reference_code = 'P'.sprintf('%05d', ($reference + 1));

        if(!empty($agent_comission_ids)):
            $agentComissions = AgentComission::whereIn('id', $agent_comission_ids)->whereNull('agent_comission_payment_id')->get();
            $refine_agent_comission_ids = $agentComissions->pluck('id')->unique()->toArray();
            $remittance_ref = $agentComissions->pluck('remittance_ref')->unique()->toArray();
            $agent_ids = $agentComissions->pluck('agent_id')->unique()->toArray();
            $semester_ids = $agentComissions->pluck('semester_id')->unique()->toArray();
            $semester_names = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
            $comissions = AgentComissionDetail::whereIn('agent_comission_id', $refine_agent_comission_ids)->get();
            if(!empty($agent_ids) && count($agent_ids) == 1):
                $agent = Agent::whereIn('id', $agent_ids)->orderBy('id', 'DESC')->get()->first();
                $html = '';
                $html .= '<table class="table table-bordered table-sm">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th>Ref</th>';
                            $html .= '<th>Date</th>';
                            $html .= '<th>Agent</th>';
                            $html .= '<th>Terms</th>';
                            $html .= '<th>Remittance Ref.</th>';
                            $html .= '<th>Amount</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        $html .= '<tr>';
                            $html .= '<td>'.$reference_code.'</td>';
                            $html .= '<td><input type="text" class="form-control datepickers w-full" name="date" placeholder="DD-MM-YYYY"/><div class="acc__input-error text-danger mt-1"></div></td>';
                            $html .= '<td>'.(isset($agent->full_name) && !empty($agent->full_name) ? $agent->full_name : '').(isset($agent->organization) && !empty($agent->organization) ? ' ('.$agent->organization.')' : '').'</td>';
                            $html .= '<td>'.(!empty($semester_names) ? implode(', ', $semester_names) : '').'</td>';
                            $html .= '<td>'.(!empty($remittance_ref) ? implode(', ', $remittance_ref) : '').'</td>';
                            $html .= '<td>'.($comissions->count() > 0 ? Number::currency($comissions->sum('amount'), in: 'GBP') : '').'</td>';
                        $html .= '</tr>';
                    $html .= '</tbody>';
                $html .= '</table>';
                $html .= '<input type="hidden" name="reference" value="'.$reference_code.'"/>';
                $html .= '<input type="hidden" name="agent_id" value="'.(isset($agent->id) && $agent->id > 0 ? $agent->id : 0).'"/>';
                $html .= '<input type="hidden" name="agent_user_id" value="'.(isset($agent->agent_user_id) && $agent->agent_user_id > 0 ? $agent->agent_user_id : 0).'"/>';
                $html .= '<input type="hidden" name="agent_comission_ids" value="'.implode(',', $refine_agent_comission_ids).'"/>';
                $html .= '<input type="hidden" name="amount" value="'.($comissions->count() > 0 ? $comissions->sum('amount') : 0).'"/>';

                return response()->json(['msg' => 'success', 'html' => $html], 200);
            else:
                return response()->json(['msg' => 'You can not select multiple agents remittance at a time.'], 422);
            endif;
        else:
            return response()->json(['msg' => 'Please select some remittance to generate the schedule.'], 422);
        endif;
    }

    public function storePayment(Request $request){
        $agent_comission_ids = (isset($request->agent_comission_ids) && !empty($request->agent_comission_ids) ? explode(',', $request->agent_comission_ids) : []);
        $reference = $request->reference;
        $date = (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d'));
        $agent_id = (isset($request->agent_id) && $request->agent_id > 0 ? $request->agent_id : null);
        $agent_user_id = (isset($request->agent_user_id) && $request->agent_user_id > 0 ? $request->agent_user_id : null);
        $amount = (isset($request->amount) && $request->amount > 0 ? $request->amount : 0);

        $data = [];
        $data['agent_id'] = $agent_id;
        $data['agent_user_id'] = $agent_user_id;
        $data['reference'] = $reference;
        $data['date'] = $date;
        $data['amount'] = $amount;
        $data['status'] = 1;
        $data['created_by'] = auth()->user()->id;
        $payment = AgentComissionPayment::create($data);

        if($payment->id):
            if(!empty($agent_comission_ids)):
                AgentComission::whereIn('id', $agent_comission_ids)->update(['agent_comission_payment_id' => $payment->id]);
            endif;
            return response()->json(['msg' => 'Agent comission remittances successfully secheduled for payment.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 422);
        endif;
    }

    public function payments(){
        return view('pages.agent.management.payments', [
            'title' => 'Agent Management - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Agent', 'href' => route('agent-user.index')],
                ['label' => 'Management', 'href' => 'javascript:void(0);'],
                ['label' => 'Remittance', 'href' => 'javascript:void(0);'],
                ['label' => 'Payments', 'href' => 'javascript:void(0);'],
            ]
        ]);
    }

    public function paymentList(Request $request){
        $querystr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = AgentComissionPayment::with('comissions')->where('status', $status);
        if(!empty($querystr)):
            $query->where(function($q) use($querystr){
                $q->where('reference','LIKE','%'.$querystr.'%');
            });
        endif;

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
                $terms = [];
                $remit_ref = [];
                if(isset($list->comissions) && $list->comissions->count() > 0):
                    foreach($list->comissions as $comission):
                        if(!array_key_exists($comission->id, $remit_ref)):
                            $remit_ref[$comission->id] = '<a class="text-primary font-medium underline" href="'.route('agent.management.remittance.print', $comission->id).'">'.$comission->remittance_ref.'</a>';
                        endif;
                        if(isset($comission->rule->semester->name) && !empty($comission->rule->semester->name)):
                            $terms[] = $comission->rule->semester->name;
                        endif;
                    endforeach;
                endif;
                $terms = array_unique($terms);
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'reference' => $list->reference,
                    'date' => (!empty($list->date) ? date('jS M, Y', strtotime($list->date)) : ''),
                    'agent_name' => (isset($list->agent->full_name) ? $list->agent->full_name : ''),
                    'organization' => (isset($list->agent->organization) ? $list->agent->organization : ''),
                    'amount_html' => (!empty($list->amount) ? Number::currency($list->amount, in: 'GBP') : '£0.00'),
                    'amount' => (!empty($list->amount) ? $list->amount : 0),
                    'status' => $list->status,
                    'semsters' => (!empty($terms) ? implode(', ', $terms) : ''),
                    'remittance_refs' => (!empty($remit_ref) ? implode(', ', $remit_ref) : ''),
                    'acc_transaction_id' => ($list->acc_transaction_id > 0 ? $list->acc_transaction_id : 0),
                    'transaction_code' => (isset($list->transaction->transaction_code) && !empty($list->transaction->transaction_code) ? $list->transaction->transaction_code : ''),
                    'transaction_date' => (isset($list->transaction->transaction_date_2) && !empty($list->transaction->transaction_date_2) ? date('jS M, Y', strtotime($list->transaction->transaction_date_2)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function searchTransactions(Request $request){
        $SearchVal = (isset($request->SearchVal) && !empty($request->SearchVal) ? trim($request->SearchVal) : '');
        $html = '';
        $Query = AccTransaction::where('transaction_code', 'LIKE', '%'.$SearchVal.'%')->orderBy('transaction_date_2', 'DESC')->get();
        
        if($Query->count() > 0):
            foreach($Query as $qr):
                $html .= '<li>';
                    $html .= '<a href="'.$qr->transaction_code.'" data-id="'.$qr->id.'" data-amount="'.$qr->transaction_amount.'" class="dropdown-item">'.$qr->transaction_code.'</a>';
                $html .= '</li>';
            endforeach;
        else:
            $html .= '<li>';
                $html .= '<a href="javascript:void(0);" class="dropdown-item">Nothing found!</a>';
            $html .= '</li>';
        endif;

        return response()->json(['htm' => $html], 200);
    }

    public function linkedTransaction(RemittanceLinkedRequest $request){
        $transaction_id = $request->transaction_id;
        $agent_comission_payment_id = $request->agent_comission_payment_id;
        $agent_comission_total = $request->agent_comission_total;

        if($transaction_id > 0 && $agent_comission_payment_id > 0):
            AgentComissionPayment::where('id', $agent_comission_payment_id)->update(['acc_transaction_id' => $transaction_id, 'status' => 2]);
            return response()->json(['msg' => 'Remittance Payment successfully linked with the transaction.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 422);
        endif;
    }

    public function paymentSendMail(Request $request){
        $payment_id = $request->payment_id;
        $payment = AgentComissionPayment::find($payment_id);

        $to = [];
        if(isset($payment->agent->email) && !empty($payment->agent->email)):
            $to[] = $payment->agent->email;
        endif;

        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        if(isset($commonSmtp->id) && $commonSmtp->id > 0 && $payment_id > 0 && !empty($to)):
            $attachmentFiles = [];
            if(isset($payment->comissions) && $payment->comissions->count() > 0):
                $i = 0;
                foreach($payment->comissions as $comission):
                    $thePDF = $this->generagePdf($payment_id, $comission->id);
                    $attachmentFiles[$i] = [
                        "pathinfo" => 'public/agents/payment/'.$payment_id.'/'.$thePDF['filename'],
                        "nameinfo" => $thePDF['filename'],
                        "mimeinfo" => 'application/pdf',
                        "disk" => 's3'
                    ];
                    $i++;
                endforeach;
            endif;
            $configuration = [
                'smtp_host'    => $commonSmtp->smtp_host,
                'smtp_port'    => $commonSmtp->smtp_port,
                'smtp_username'  => $commonSmtp->smtp_user,
                'smtp_password'  => $commonSmtp->smtp_pass,
                'smtp_encryption'  => $commonSmtp->smtp_encryption,
                
                'from_email'    => 'accounts@lcc.ac.uk',
                'from_name'    =>  'Accounts Department LCC',
            ];

            $subject = 'Remittance Advice';
            $message = 'Dear Concern,<br/><br/>';
            $message .= '<p>Please find attached the remittance advice for your reference. Below are the key details:</p>'; 

            $message .= '<p>Date: '.(isset($payment->date) && !empty($payment->date) ? date('jS F, Y', strtotime($payment->date)) : '').'</p>';  
            $message .= '<p>Total Amount: '.Number::currency($payment->amount, in: 'GBP').'</p>';   

            $message .= '<p>For any further queries, feel free to reach out.</p><br/>';

            $message .= 'Best regards,<br/>'; 
            $message .= 'Accounts<br/>';  
            $message .= 'London Churchill College';

            UserMailerJob::dispatch($configuration, $to, new CommunicationSendMail($subject, $message, $attachmentFiles));

            return response()->json(['msg' => 'Mail successfully sent to the agent.'], 200);
        else:
            return response()->json(['msg' => 'Something went wrong. Please try again later or contact with the administrator.'], 422);
        endif;
    }
}
