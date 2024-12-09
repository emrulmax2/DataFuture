<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CourseCreation;
use App\Models\ReferralCode;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;

class AgentManagementController extends Controller
{
    public function index(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        return view('pages.agent.management.index', [
            'title' => 'Accounts Assets Register - London Churchill College',
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
                        $html .= '</tr>';
                    $html .= '</thead>'; 
                    $html .= '<tbody>'; 
                        if(!empty($reff_codes)):
                            foreach($reff_codes as $code):
                                $theCode = ReferralCode::where('code', $code)->get()->first();
                                $student_ids = Student::whereHas('activeCR', function($q) use($creation_ids){
                                                $q->whereIn('course_creation_id', $creation_ids);
                                            })->where('referral_code', $code)->where('is_referral_varified', 1)->pluck('id')->unique()->toArray();
                                $html .= '<tr class="cursor-pointer code_row font-medium" data-code="'.$code.'" data-semester="'.$semester_id.'">';
                                    $html .= '<td>';
                                        if($theCode->type == 'Agent'):
                                            $html .= (isset($theCode->agent_user->email) && !empty($theCode->agent_user->email) ? $theCode->agent_user->email : '');
                                        elseif($theCode->type == 'Student'):
                                            $html .= (isset($theCode->student->full_name) && !empty($theCode->student->full_name) ? $theCode->student->full_name : '');
                                        endif;
                                    $html .= '</td>';
                                    $html .= '<td>'.$code.'</td>';
                                    $html .= '<td>'.$theCode->type.'</td>';
                                    $html .= '<td class="w-[150px]">'.(!empty($student_ids) && count($student_ids) > 0 ? count($student_ids) : 0).'</td>';
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
}
