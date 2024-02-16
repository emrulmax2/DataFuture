<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentWblProfile;
use App\Models\StudentWorkPlacement;
use Illuminate\Http\Request;

class WblProfileController extends Controller
{
    public function list(Request $request){
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $student_id = (isset($request->student_id) && $request->student_id > 0 ? $request->student_id : 0);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = StudentWblProfile::orderByRaw(implode(',', $sorts))->where('student_id', $student_id);
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'company' => (isset($list->company->name) && !empty($list->company->name) ? $list->company->name : ''),

                    'weif_form_provided_date' => (!empty($list->weif_form_provided_date) ? date('jS M, Y', strtotime($list->weif_form_provided_date)) : ''),
                    'weif_form_provided_status' => ($list->weif_form_provided_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->weif_form_provided_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'received_completed_weif_form_date' => (!empty($list->received_completed_weif_form_date) ? date('jS M, Y', strtotime($list->received_completed_weif_form_date)) : ''),
                    'received_completed_weif_form_status' => ($list->received_completed_weif_form_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->received_completed_weif_form_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'work_hour_update_term_date' => (!empty($list->work_hour_update_term_date) ? date('jS M, Y', strtotime($list->work_hour_update_term_date)) : ''),
                    'work_hour_update_term_status' => ($list->work_hour_update_term_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->work_hour_update_term_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'work_exp_handbook_complete_date' => (!empty($list->work_exp_handbook_complete_date) ? date('jS M, Y', strtotime($list->work_exp_handbook_complete_date)) : ''),
                    'work_exp_handbook_complete_status' => ($list->work_exp_handbook_complete_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->work_exp_handbook_complete_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'work_exp_handbook_checked_date' => (!empty($list->work_exp_handbook_checked_date) ? date('jS M, Y', strtotime($list->work_exp_handbook_checked_date)) : ''),
                    'work_exp_handbook_checked_status' => ($list->work_exp_handbook_checked_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->work_exp_handbook_checked_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'emp_handbook_sent_date' => (!empty($list->emp_handbook_sent_date) ? date('jS M, Y', strtotime($list->emp_handbook_sent_date)) : ''),
                    'emp_handbook_sent_status' => ($list->emp_handbook_sent_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->emp_handbook_sent_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'emp_letter_sent_date' => (!empty($list->emp_letter_sent_date) ? date('jS M, Y', strtotime($list->emp_letter_sent_date)) : ''),
                    'emp_letter_sent_status' => ($list->emp_letter_sent_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->emp_letter_sent_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'emp_confirm_rec_date' => (!empty($list->emp_confirm_rec_date) ? date('jS M, Y', strtotime($list->emp_confirm_rec_date)) : ''),
                    'emp_confirm_rec_status' => ($list->emp_confirm_rec_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->emp_confirm_rec_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'company_visit_date' => (!empty($list->company_visit_date) ? date('jS M, Y', strtotime($list->company_visit_date)) : ''),
                    'company_visit_status' => ($list->company_visit_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->company_visit_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'record_std_meeting_date' => (!empty($list->record_std_meeting_date) ? date('jS M, Y', strtotime($list->record_std_meeting_date)) : ''),
                    'record_std_meeting_status' => ($list->record_std_meeting_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->record_std_meeting_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'record_all_contact_student_date' => (!empty($list->record_all_contact_student_date) ? date('jS M, Y', strtotime($list->record_all_contact_student_date)) : ''),
                    'record_all_contact_student_status' => ($list->record_all_contact_student_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->record_all_contact_student_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'email_sent_emp_date' => (!empty($list->email_sent_emp_date) ? date('jS M, Y', strtotime($list->email_sent_emp_date)) : ''),
                    'email_sent_emp_status' => ($list->email_sent_emp_status == 1 ? '<span class="text-success text-xs whitespace-nowrap">Yes</span>' : ($list->email_sent_emp_status == '0' ? '<span class="text-danger text-xs whitespace-nowrap">No</span>' : '')),

                    'created_by'=> (isset($list->user->employee->full_name) && !empty($list->user->employee->full_name) ? $list->user->employee->full_name : 'Unknown Employee'),
                    'created_at'=> (isset($list->created_at) && !empty($list->created_at) ? date('jS M, Y', strtotime($list->created_at)) : ''),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }


    public function store(Request $request){
        $student_id = $request->student_id;
        $student_work_placement_id = $request->student_work_placement_id;
        $workPlacement = StudentWorkPlacement::find($student_work_placement_id);

        $wblProfile = StudentWblProfile::create([
            'student_id' => $student_id,
            'student_work_placement_id' => $student_work_placement_id,
            'company_id' => $workPlacement->company_id,
            'weif_form_provided_date' => (!empty($request->weif_form_provided_date) ? date('Y-m-d', strtotime($request->weif_form_provided_date)) : null),
            'weif_form_provided_status' => (isset($request->weif_form_provided_status) ? $request->weif_form_provided_status : null),
            'received_completed_weif_form_date' => (!empty($request->received_completed_weif_form_date) ? date('Y-m-d', strtotime($request->received_completed_weif_form_date)) : null),
            'received_completed_weif_form_status' => (isset($request->received_completed_weif_form_status) ? $request->received_completed_weif_form_status : null),
            'work_hour_update_term_date' => (!empty($request->work_hour_update_term_date) ? date('Y-m-d', strtotime($request->work_hour_update_term_date)) : null),
            'work_hour_update_term_status' => (isset($request->work_hour_update_term_status) ? $request->work_hour_update_term_status : null),
            'work_exp_handbook_complete_date' => (!empty($request->work_exp_handbook_complete_date) ? date('Y-m-d', strtotime($request->work_exp_handbook_complete_date)) : null),
            'work_exp_handbook_complete_status' => (isset($request->work_exp_handbook_complete_status) ? $request->work_exp_handbook_complete_status : null),
            'work_exp_handbook_checked_date' => (!empty($request->work_exp_handbook_checked_date) ? date('Y-m-d', strtotime($request->work_exp_handbook_checked_date)) : null),
            'work_exp_handbook_checked_status' => (isset($request->work_exp_handbook_checked_status) ? $request->work_exp_handbook_checked_status : null),
            'emp_handbook_sent_date' => (!empty($request->emp_handbook_sent_date) ? date('Y-m-d', strtotime($request->emp_handbook_sent_date)) : null),
            'emp_handbook_sent_status' => (isset($request->emp_handbook_sent_status) ? $request->emp_handbook_sent_status : null),
            'emp_letter_sent_date' => (!empty($request->emp_letter_sent_date) ? date('Y-m-d', strtotime($request->emp_letter_sent_date)) : null),
            'emp_letter_sent_status' => (isset($request->emp_letter_sent_status) ? $request->emp_letter_sent_status : null),
            'emp_confirm_rec_date' => (!empty($request->emp_confirm_rec_date) ? date('Y-m-d', strtotime($request->emp_confirm_rec_date)) : null),
            'emp_confirm_rec_status' => (isset($request->emp_confirm_rec_status) ? $request->emp_confirm_rec_status : null),
            'company_visit_date' => (!empty($request->company_visit_date) ? date('Y-m-d', strtotime($request->company_visit_date)) : null),
            'company_visit_status' => (isset($request->company_visit_status) ? $request->company_visit_status : null),
            'record_std_meeting_date' => (!empty($request->record_std_meeting_date) ? date('Y-m-d', strtotime($request->record_std_meeting_date)) : null),
            'record_std_meeting_status' => (isset($request->record_std_meeting_status) ? $request->record_std_meeting_status : null),
            'record_all_contact_student_date' => (!empty($request->record_all_contact_student_date) ? date('Y-m-d', strtotime($request->record_all_contact_student_date)) : null),
            'record_all_contact_student_status' => (isset($request->record_all_contact_student_status) ? $request->record_all_contact_student_status : null),
            'email_sent_emp_date' => (!empty($request->email_sent_emp_date) ? date('Y-m-d', strtotime($request->email_sent_emp_date)) : null),
            'email_sent_emp_status' => (isset($request->email_sent_emp_status) ? $request->email_sent_emp_status : null),

            'created_by' => auth()->user()->id,
        ]);

        return response()->json(['res' => 'Success'], 200);
    }

    public function edit($id){
        $wblProfile = StudentWblProfile::find($id);
        return response()->json(['res' => $wblProfile], 200);
    }

    public function update(Request $request){
        $student_id = $request->student_id;
        $id = $request->id;

        $wblProfile = StudentWblProfile::where('id', $id)->update([
            'weif_form_provided_date' => (!empty($request->weif_form_provided_date) ? date('Y-m-d', strtotime($request->weif_form_provided_date)) : null),
            'weif_form_provided_status' => (isset($request->weif_form_provided_status) ? $request->weif_form_provided_status : null),
            'received_completed_weif_form_date' => (!empty($request->received_completed_weif_form_date) ? date('Y-m-d', strtotime($request->received_completed_weif_form_date)) : null),
            'received_completed_weif_form_status' => (isset($request->received_completed_weif_form_status) ? $request->received_completed_weif_form_status : null),
            'work_hour_update_term_date' => (!empty($request->work_hour_update_term_date) ? date('Y-m-d', strtotime($request->work_hour_update_term_date)) : null),
            'work_hour_update_term_status' => (isset($request->work_hour_update_term_status) ? $request->work_hour_update_term_status : null),
            'work_exp_handbook_complete_date' => (!empty($request->work_exp_handbook_complete_date) ? date('Y-m-d', strtotime($request->work_exp_handbook_complete_date)) : null),
            'work_exp_handbook_complete_status' => (isset($request->work_exp_handbook_complete_status) ? $request->work_exp_handbook_complete_status : null),
            'work_exp_handbook_checked_date' => (!empty($request->work_exp_handbook_checked_date) ? date('Y-m-d', strtotime($request->work_exp_handbook_checked_date)) : null),
            'work_exp_handbook_checked_status' => (isset($request->work_exp_handbook_checked_status) ? $request->work_exp_handbook_checked_status : null),
            'emp_handbook_sent_date' => (!empty($request->emp_handbook_sent_date) ? date('Y-m-d', strtotime($request->emp_handbook_sent_date)) : null),
            'emp_handbook_sent_status' => (isset($request->emp_handbook_sent_status) ? $request->emp_handbook_sent_status : null),
            'emp_letter_sent_date' => (!empty($request->emp_letter_sent_date) ? date('Y-m-d', strtotime($request->emp_letter_sent_date)) : null),
            'emp_letter_sent_status' => (isset($request->emp_letter_sent_status) ? $request->emp_letter_sent_status : null),
            'emp_confirm_rec_date' => (!empty($request->emp_confirm_rec_date) ? date('Y-m-d', strtotime($request->emp_confirm_rec_date)) : null),
            'emp_confirm_rec_status' => (isset($request->emp_confirm_rec_status) ? $request->emp_confirm_rec_status : null),
            'company_visit_date' => (!empty($request->company_visit_date) ? date('Y-m-d', strtotime($request->company_visit_date)) : null),
            'company_visit_status' => (isset($request->company_visit_status) ? $request->company_visit_status : null),
            'record_std_meeting_date' => (!empty($request->record_std_meeting_date) ? date('Y-m-d', strtotime($request->record_std_meeting_date)) : null),
            'record_std_meeting_status' => (isset($request->record_std_meeting_status) ? $request->record_std_meeting_status : null),
            'record_all_contact_student_date' => (!empty($request->record_all_contact_student_date) ? date('Y-m-d', strtotime($request->record_all_contact_student_date)) : null),
            'record_all_contact_student_status' => (isset($request->record_all_contact_student_status) ? $request->record_all_contact_student_status : null),
            'email_sent_emp_date' => (!empty($request->email_sent_emp_date) ? date('Y-m-d', strtotime($request->email_sent_emp_date)) : null),
            'email_sent_emp_status' => (isset($request->email_sent_emp_status) ? $request->email_sent_emp_status : null),

            'updated_by' => auth()->user()->id,
        ]);

        return response()->json(['res' => 'Success'], 200);
    }

    public function destroy($id) {
        $wblProfile = StudentWblProfile::find($id)->delete();
        return response()->json(['res' => 'Success'], 200);
    }

    public function restore(Request $request) {
        $data = StudentWblProfile::where('id', $request->row_id)->withTrashed()->restore();

        return response()->json(['res' => 'Success'], 200);
    }
}
